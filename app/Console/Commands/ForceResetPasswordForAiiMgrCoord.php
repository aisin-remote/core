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

class ForceResetPasswordForAiiMgrCoord extends Command
{
    protected $signature = 'users:force-reset-aii-mgr-coord
                            {--dry-run : Hanya tampilkan jumlah user tanpa eksekusi}
                            {--limit=0 : (Opsional) batasi proses untuk N user pertama (untuk uji coba)}';

    protected $description = "Set password = aii+tanggal_lahir(dmY) untuk user employee.company_name='AII' & position Manager/Act Manager/Coordinator/Act Coordinator; log yang dilewati";

    public function handle()
    {
        $limit = (int) $this->option('limit');

        $ts = now()->format('Ymd_His');
        $dir = 'aii_mgrcoord_reset';
        $csvPath = "$dir/skipped_{$ts}.csv";
        $sumPath = "$dir/summary_{$ts}.log";
        Storage::makeDirectory($dir);

        $skippedRows = [
            ['user_id', 'email', 'name', 'npk', 'missing_field', 'notes']
        ];

        // Filter: company_name = AII + posisi Manager/Act Manager/Coordinator/Act Coordinator (case-insensitive)
        $query = User::with('employee')
            ->whereHas('employee', function ($q) {
                $q->where('company_name', 'AII')
                  ->whereIn(DB::raw('LOWER(position)'), [
                      'manager', 'act manager', 'coordinator', 'act coordinator'
                  ]);
            })
            ->orderBy('id');

        if ($limit > 0) {
            $query->limit($limit);
        }

        $count = (clone $query)->count();
        $this->info("Ditemukan {$count} user (employee.company_name='AII' & position Manager/Act Manager/Coordinator/Act Coordinator)"
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
                    Log::warning('AII Mgr/Coord reset: dilewati', [
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
                    'password_changed_at' => null,
                    'remember_token'      => Str::random(60),
                ])->save();

                // Hapus sesi aktif (jika driver database)
                try {
                    DB::table('sessions')->where('user_id', $user->id)->delete();
                } catch (\Throwable $e) {
                    // ignore
                }

                // Revoke API tokens bila ada (Sanctum/Passport)
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

        $summary = "AII Manager/Coordinator Password Reset @ " . now()->toDateTimeString() . PHP_EOL
            . "Kriteria     : employee.company_name='AII', position IN ('Manager','Act Manager','Coordinator','Act Coordinator')" . PHP_EOL
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
