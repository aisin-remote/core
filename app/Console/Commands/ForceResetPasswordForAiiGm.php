<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ForceResetPasswordForAiiGm extends Command
{
    protected $signature = 'users:force-reset-aii-gm
                            {--dry-run : Hanya tampilkan jumlah user tanpa eksekusi}
                            {--limit=0 : (Opsional) batasi proses untuk N user pertama (untuk uji coba)}';

    protected $description = "Set password = aii+tanggal_lahir(dmY) untuk user dgn employee.company_name='AII' & employee.position IN ('GM','Act GM'), log yang dilewati";

    public function handle()
    {
        $limit  = (int) $this->option('limit');

        $ts = now()->format('Ymd_His');
        $csvPath = "aii_gm_reset/skipped_{$ts}.csv";
        $sumPath = "aii_gm_reset/summary_{$ts}.log";
        Storage::makeDirectory('aii_gm_reset');

        $skippedRows = [
            ['user_id', 'email', 'name', 'npk', 'missing_field', 'notes']
        ];

        // 🔧 Pindahkan seluruh filter ke relasi employee
        $query = User::with('employee')
            ->whereHas('employee', function ($q) {
                $q->where('company_name', 'AII')
                  ->where(function ($qq) {
                      $qq->where('position', 'GM')
                         ->orWhere('position', 'Act GM')
                         ->orWhere('position', 'act GM'); // antisipasi variasi penulisan
                  });
            })
            ->orderBy('id');

        if ($limit > 0) {
            $query->limit($limit);
        }

        $count = (clone $query)->count();
        $this->info("Ditemukan {$count} user (employee.company_name='AII' & position GM/Act GM)"
            . ($limit > 0 ? " (dibatasi {$limit})" : ''));

        if ($this->option('dry-run') || $count === 0) {
            $this->info('Dry run / tidak ada user. Tidak ada perubahan.');
            return;
        }

        $processed = 0;
        $skipped   = 0;

        $bar = $this->output->createProgressBar($count);
        $bar->start();

        $query->chunkById(200, function ($users) use (&$processed, &$skipped, &$skippedRows, $bar) {
            foreach ($users as $user) {
                $emp = $user->employee;

                // Minimal: harus ada employee + birthday_date
                $missing = [];
                if (!$emp)                 $missing[] = 'employee';
                if (!$emp?->birthday_date) $missing[] = 'birthday_date';

                if ($missing) {
                    $skipped++;
                    $skippedRows[] = [
                        $user->id,
                        $user->email,
                        $user->name,
                        $emp->npk ?? '-',
                        implode('|', $missing),
                        'Data employee tidak lengkap'
                    ];
                    Log::warning('AII GM reset: dilewati', [
                        'user_id' => $user->id,
                        'email'   => $user->email,
                        'missing' => $missing,
                    ]);
                    $bar->advance();
                    continue;
                }

                // Password: 'aii' + tanggal lahir format dmY
                $plain = 'aii' . Carbon::parse($emp->birthday_date)->format('dmY');

                $user->forceFill([
                    'password'            => Hash::make($plain),
                    'is_first_login'      => 1,
                    'password_changed_at' => null, // sesuaikan jika kolom di DB berbeda
                    'remember_token'      => Str::random(60),
                ])->save();

                // Hapus sesi aktif (driver database)
                try {
                    DB::table('sessions')->where('user_id', $user->id)->delete();
                } catch (\Throwable $e) {
                    // abaikan jika bukan driver database
                }

                // Revoke API tokens (Sanctum/Passport) bila ada
                if (method_exists($user, 'tokens')) {
                    $user->tokens()->delete();
                }

                $processed++;
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine();

        if ($skipped > 0) {
            $fh = fopen(Storage::path($csvPath), 'w');
            foreach ($skippedRows as $row) fputcsv($fh, $row);
            fclose($fh);
        }

        $summary = "AII GM Password Reset @ " . now()->toDateTimeString() . PHP_EOL
            . "Kriteria     : employee.company_name='AII', position IN ('GM','Act GM')" . PHP_EOL
            . "Processed    : $processed" . PHP_EOL
            . "Skipped      : $skipped" . PHP_EOL
            . "CSV Skipped  : " . ($skipped > 0 ? Storage::path($csvPath) : '-') . PHP_EOL;
        Storage::put($sumPath, $summary);

        $this->info("Selesai. Processed: {$processed}, Skipped: {$skipped}");
        if ($skipped > 0) $this->warn("Daftar yang dilewati: " . Storage::path($csvPath));
        $this->line("Ringkasan eksekusi: " . Storage::path($sumPath));
        $this->info("Detail juga tercatat di storage/logs/laravel.log");
    }
}
