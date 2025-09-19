<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

class ForceResetPasswordForAisinUsers extends Command
{
    protected $signature = 'users:force-reset-aisin
                            {--dry-run : Hanya tampilkan jumlah user tanpa eksekusi}
                            {--limit=0 : (Opsional) batasi proses untuk N user pertama (untuk uji coba)}';

    protected $description = 'Set password = NPK+tanggal_lahir(dmY) untuk semua user @aisin-indonesia.co.id, log user yang dilewati';

    public function handle()
    {
        $domain = 'aisin-indonesia.co.id';
        $limit  = (int) $this->option('limit');

        // File log & CSV (di storage/)
        $ts = now()->format('Ymd_His');
        $csvPath = "aisin_reset/skipped_{$ts}.csv";
        $sumPath = "aisin_reset/summary_{$ts}.log";

        // siapkan folder
        Storage::makeDirectory('aisin_reset');

        // header CSV
        $skippedRows = [
            ['user_id', 'email', 'name', 'missing_field', 'notes']
        ];

        $query = User::with('employee')
            ->where('role', 'User')                // perhatikan kapital 'User'
            ->where('email', 'like', "%@$domain")
            ->orderBy('id');

        if ($limit > 0) {
            $query->limit($limit);
        }

        $count = (clone $query)->count();
        $this->info("Ditemukan {$count} user dengan domain @$domain"
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

                // validasi field
                $missing = [];
                if (!$emp)                      $missing[] = 'employee';
                if (!$emp?->npk)                $missing[] = 'npk';
                if (!$emp?->birthday_date)      $missing[] = 'birthday_date';

                if ($missing) {
                    $skipped++;
                    // tulis ke CSV
                    $skippedRows[] = [
                        $user->id,
                        $user->email,
                        $user->name,
                        implode('|', $missing),
                        'Data employee tidak lengkap'
                    ];
                    // catat ke laravel.log
                    Log::warning('Aisin reset: dilewati', [
                        'user_id' => $user->id,
                        'email'   => $user->email,
                        'missing' => $missing,
                    ]);
                    $bar->advance();
                    continue;
                }

                // Password: NPK + tanggal lahir format dmY
                $plain = (string) $emp->npk . Carbon::parse($emp->birthday_date)->format('dmY');

                $user->forceFill([
                    'password'            => Hash::make($plain),
                    'is_first_login'      => 1,
                    // NOTE: jika di DB kolomnya "password_change", ganti key di bawah ini.
                    'password_changed_at' => null,
                    'remember_token'      => Str::random(60),
                ])->save();

                // hapus sesi aktif (driver database)
                try {
                    DB::table('sessions')->where('user_id', $user->id)->delete();
                } catch (\Throwable $e) {
                    // abaikan jika bukan driver database
                }

                if (method_exists($user, 'tokens')) {
                    $user->tokens()->delete();
                }

                $processed++;
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine();

        // Simpan CSV skipped (jika ada)
        if ($skipped > 0) {
            // tulis CSV
            $fh = fopen(Storage::path($csvPath), 'w');
            foreach ($skippedRows as $row) {
                fputcsv($fh, $row);
            }
            fclose($fh);
        }

        // tulis summary ringkas
        $summary = "Aisin Password Reset @ " . now()->toDateTimeString() . PHP_EOL
            . "Domain       : @$domain" . PHP_EOL
            . "Processed    : $processed" . PHP_EOL
            . "Skipped      : $skipped" . PHP_EOL
            . "CSV Skipped  : " . ($skipped > 0 ? Storage::path($csvPath) : '-') . PHP_EOL;
        Storage::put($sumPath, $summary);

        $this->info("Selesai. Processed: {$processed}, Skipped: {$skipped}");
        if ($skipped > 0) {
            $this->warn("Daftar yang dilewati tersimpan di: " . Storage::path($csvPath));
        }
        $this->line("Ringkasan eksekusi: " . Storage::path($sumPath));
        $this->info("Detail juga tercatat di storage/logs/laravel.log");
    }
}
