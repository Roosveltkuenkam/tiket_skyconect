<?php

namespace App\Http\Controllers;

use App\Models\Router;

class PortalController extends Controller
{
    public function show(Router $router)
    {
        abort_unless($router->status === 'active', 404);

        $router->load('user');

        $plans = $router->plans()
            ->where('is_active', true)
            ->withCount([
                'tickets as available_tickets_count' => function ($query) {
                    $query->where('status', 'available');
                },
            ])
            ->orderBy('price')
            ->orderBy('duration')
            ->get();

        return view('portal.show', compact('router', 'plans'));
    }
}
