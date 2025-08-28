<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class RedirectIfCannotViewDashboard
{
    public function handle(Request $request, Closure $next)
    {
        if (! Gate::allows('view-dashboard')) {
            return redirect()->route('todolist.index')
                ->with('warning', 'You do not have access to the Dashboard.');
        }
        return $next($request);
    }
}
