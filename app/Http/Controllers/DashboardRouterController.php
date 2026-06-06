<?php

namespace App\Http\Controllers;

use App\Models\AdminNotification;
use App\Models\Order;
use App\Models\Router;
use App\Models\Ticket;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DashboardRouterController extends Controller
{
    public function index(Request $request)
    {
        $routers = Router::where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return view('admin.routers.index', compact('routers'));
    }

    public function create()
    {
        return view('admin.routers.create', ['router' => null]);
    }

    public function store(Request $request)
    {
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

        $request->validate([
            'name' => 'required',
            'location' => 'nullable',
            'dns' => 'nullable',
            'assistance_phone' => 'nullable',
            'platform' => 'required',
            'status' => 'required',
        ]);

        $router = Router::create([
            'user_id' => $request->user()->id,
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

        return redirect()->route('dashboard.routers.index')
            ->with('success', 'Routeur ajoute avec succes.');
    }

    public function edit(Request $request, Router $router)
    {
        $this->ensureRouterAccess($request, $router);

        return view('admin.routers.create', compact('router'));
    }

    public function update(Request $request, Router $router)
    {
        $this->ensureRouterAccess($request, $router);

        $request->validate([
            'name' => 'required',
            'location' => 'nullable',
            'dns' => 'nullable',
            'assistance_phone' => 'nullable',
            'platform' => 'required',
            'status' => 'required|in:active,inactive',
        ]);

        $router->update($request->only([
            'name',
            'location',
            'dns',
            'assistance_phone',
            'platform',
            'status',
        ]));

        ActivityLogger::log('router.updated', $router, [
            'name' => $router->name,
            'status' => $router->status,
        ], $request);

        return redirect()->route('dashboard.routers.index')
            ->with('success', 'Routeur mis a jour.');
    }

    public function deactivate(Request $request, Router $router)
    {
        $this->ensureRouterAccess($request, $router);

        $router->update(['status' => 'inactive']);

        ActivityLogger::log('router.deactivated', $router, [
            'name' => $router->name,
        ], $request);

        return back()->with('success', 'Routeur desactive.');
    }

    public function destroy(Request $request, Router $router)
    {
        $this->ensureRouterAccess($request, $router);

        if ($this->routerHasBusinessData($router)) {
            return back()->with('error', 'Impossible de supprimer ce routeur car il contient deja des forfaits, tickets ou ventes. Desactivez-le plutot.');
        }

        ActivityLogger::log('router.deleted', $router, [
            'name' => $router->name,
        ], $request);

        $router->delete();

        return back()->with('success', 'Routeur supprime.');
    }

    private function ensureRouterAccess(Request $request, Router $router): void
    {
        abort_unless($router->user_id === $request->user()->id, 403);
    }

    private function routerHasBusinessData(Router $router): bool
    {
        return $router->plans()->exists()
            || Ticket::whereHas('plan', function ($query) use ($router) {
                $query->where('router_id', $router->id);
            })->exists()
            || Order::whereHas('plan', function ($query) use ($router) {
                $query->where('router_id', $router->id);
            })->exists();
    }
}
