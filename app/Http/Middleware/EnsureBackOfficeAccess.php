<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureBackOfficeAccess
{
    public function handle(Request $request, Closure $next, $permission = null)
    {
        if (! $request->user() || ! $request->user()->canAccessBackOffice($permission)) {
            abort(403, 'Acces back-office non autorise.');
        }

        return $next($request);
    }
}
