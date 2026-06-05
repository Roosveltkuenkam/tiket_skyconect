<?php

namespace App\Http\Controllers;

use App\Models\AdminNotification;
use App\Models\Router;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Services\ActivityLogger;

class AdminRouterController extends Controller
{
    private function routeName(Request $request, $name)
    {
        return ($request->is('dashboard*') ? 'dashboard' : 'admin') . '.routers.' . $name;
    }

    public function index()
    {
        $routers = Router::when(auth()->check() && auth()->user()->isClientOwner(), function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->latest()
            ->get();

        return view('admin.routers.index', compact('routers'));
    }

    public function create()
    {
        return view('admin.routers.create');
    }

    public function store(Request $request)
    {
        if ($request->user()->isClientOwner()) {
            if (! $request->user()->canUseSubscription()) {
                AdminNotification::notify(
                    'account_suspended',
                    'Compte client suspendu ou expire',
                    $request->user()->name . ' a tente de creer un routeur avec un abonnement non actif.',
                    'warning',
                    ['user_id' => $request->user()->id]
                );

                return back()->with('error', "Votre abonnement n'est pas actif.");
            }

            if ($request->user()->subscriptionLimitReached('routers')) {
                return back()->with('error', 'Limite de routeurs atteinte pour votre abonnement.');
            }
        }

        $request->validate([
            'name' => 'required',
            'location' => 'nullable',
            'dns' => 'nullable',
            'assistance_phone' => 'nullable',
            'platform' => 'required',
            'status' => 'required',
        ]);

        $router = Router::create([
            'user_id' => $request->user()->isClientOwner() ? $request->user()->id : null,
            'name' => $request->name,
            'location' => $request->location,
            'dns' => $request->dns,
            'assistance_phone' => $request->assistance_phone,
            'platform' => $request->platform,
            'status' => $request->status,
            'integration_key' => strtoupper(Str::random(8)),
        ]);

        ActivityLogger::log('router.created', $router, [
            'name' => $router->name,
            'owner_id' => $router->user_id,
            'status' => $router->status,
            'platform' => $router->platform,
        ], $request);

        return redirect()->route($this->routeName($request, 'index'))
            ->with('success', 'Routeur ajoute avec succes.');
    }
}
