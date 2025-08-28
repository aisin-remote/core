<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    public function boot()
    {
        $this->registerPolicies();

        Gate::define('view-dashboard', function (User $user) {
            // HRD selalu boleh
            if (($user->role ?? '') === 'HRD') {
                return true;
            }

            // jika tak ada relasi employee, tolak
            $emp = $user->employee;
            if (!$emp) return false;

            // normalized position (tanpa argumen)
            $norm = method_exists($emp, 'getNormalizedPosition')
                ? $emp->getNormalizedPosition()
                : null;

            // daftar level yang kita anggap >= manager
            $allowedNormalized = ['manager', 'gm', 'vpd', 'pd', 'director', 'president'];

            if ($norm && in_array(Str::lower($norm), $allowedNormalized, true)) {
                return true;
            }

            // fallback string matching jika belum ada normalized
            $pos = Str::lower($emp->position ?? '');
            return Str::contains($pos, [
                'manager',              // manager / acting manager
                'gm',
                'general manager',
                'vpd',
                'vice president',
                'pd',
                'president director',
                'president-director',
                'presdir',
                'director',
                'direktur',
                'president',
            ]);
        });
    }
}
