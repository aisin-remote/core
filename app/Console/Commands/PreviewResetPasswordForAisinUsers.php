<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Carbon;

class PreviewResetPasswordForAisinUsers extends Command
{
    protected $signature = 'users:preview-reset-aisin
                            {--limit=5 : Berapa banyak sample user yang mau ditampilkan}';

    protected $description = 'Preview password baru (birthday_date+npk) untuk user domain @aisin-indonesia.co.id tanpa update DB';

    public function handle()
    {
        $domain = 'aisin-indonesia.co.id';
        $limit = (int) $this->option('limit');

        $users = User::with('employee')
            ->where('role', 'User')
            ->where('email', 'like', "%@$domain")
            ->take($limit)
            ->get();

        if ($users->isEmpty()) {
            $this->warn("Tidak ada user dengan domain @$domain");
            return;
        }

        $this->table(
            ['ID', 'Email', 'Nama', 'Birthday', 'NPK', 'Password (plain)'],
            $users->map(function ($user) {
                $emp = $user->employee;
                if (!$emp || empty($emp->birthday_date) || empty($emp->npk)) {
                    return [
                        $user->id,
                        $user->email,
                        $user->name,
                        $emp?->birthday_date,
                        $emp?->npk,
                        '❌ Data employee tidak lengkap'
                    ];
                }

                // contoh password gabungan
                $plain =  (string) $emp->npk . Carbon::parse($emp->birthday_date)->format('dmY');

                return [
                    $user->id,
                    $user->email,
                    $user->name,
                    $emp->birthday_date,
                    $emp->npk,
                    $plain
                ];
            })
        );

        $this->info("⚠️ Ini hanya preview, tidak ada data yang diubah di DB.");
    }
}
