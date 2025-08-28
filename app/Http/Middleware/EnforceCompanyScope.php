<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EnforceCompanyScope
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (!$user) {
            return $next($request);
        }

        $role = $user->role ?? '';
        $emp  = $user->employee;

        // Coba pakai normalized position kalau ada
        if ($emp && method_exists($emp, 'getNormalizedPosition')) {
            $norm = Str::lower($emp->getNormalizedPosition());
        } else {
            // Fallback: pakai string posisi mentah
            $norm = Str::lower($emp->position ?? '');
        }

        // HRD/PD/VPD boleh melihat semua company (boleh filter bebas)
        $canPickCompany = $role === 'HRD' || in_array($norm, ['pd', 'president', 'vpd'], true);

        if ($canPickCompany) {
            // Jangan paksa 'company' ⇒ biarkan query ?company=... (atau kosong = all)
            return $next($request);
        }

        // Selain HRD/PD/VPD: paksa terikat ke company miliknya
        $company = optional($emp)->company_name;
        if (!$company) {
            abort(403, 'No company bound to this account.');
        }

        // Timpa parameter 'company' agar backend selalu konsisten dengan company user
        $request->merge(['company' => $company]);

        return $next($request);
    }
}
