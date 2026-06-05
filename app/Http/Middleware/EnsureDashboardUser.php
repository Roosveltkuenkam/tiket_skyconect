<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureDashboardUser
{
    public function handle(Request $request, Closure $next)
    {
        if (! $request->user() || ! $request->user()->isAdminLike()) {
            abort(403, 'Acces reserve aux utilisateurs SkyConnect.');
        }

        return $next($request);
    }
}
