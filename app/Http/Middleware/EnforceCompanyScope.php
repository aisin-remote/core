<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnforceCompanyScope
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (!$user) return $next($request);

        // HRD bebas; user biasa terkunci ke company dari relasi employee
        if ($user->role !== 'HRD') {
            $company = optional($user->employee)->company_name;
            if (!$company) {
                abort(403, 'No company bound to this account.');
            }
            // paksa parameter 'company' agar backend selalu konsisten
            $request->merge(['company' => $company]);
        }

        return $next($request);
    }
}
