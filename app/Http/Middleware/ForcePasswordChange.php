<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ForcePasswordChange
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();

            // Skip middleware for change password or logout routes
            if ($request->routeIs('change-password.*') || $request->routeIs('logout.auth')) {
                return $next($request);
            }

            // Check if first login or password never changed
            if ($user->is_first_login || is_null($user->password_changed_at)) {
                // Add session flag for first login alert
                if ($user->is_first_login && !session()->has('first_login_alert_shown')) {
                    session()->put('first_login_alert_shown', true);
                    return redirect()->route('change-password.auth')
                        ->with('show_first_login_alert', true);
                }

                return redirect()->route('change-password.auth')
                    ->with('warning', 'You must change your password before proceeding.');
            }
        }

        return $next($request);
    }
}
