<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class SetInterfaceContext
{
    public function handle(Request $request, Closure $next)
    {
        View::share('routeArea', $request->is('dashboard*') ? 'dashboard' : 'admin');

        return $next($request);
    }
}
