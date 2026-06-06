<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Router;
use App\Models\Ticket;
use App\Models\Order;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class DashboardPlanController extends Controller
{
    public function index(Request $request)
    {
        $plans = Plan::with('router')
            ->withCount([
                'tickets as available_tickets_count' => function ($query) {
                    $query->where('status', 'available');
                },
            ])
            ->whereHas('router', function ($query) use ($request) {
                $query->where('user_id', $request->user()->id);
            })
            ->latest()
            ->get();
        $ticketsAvailableCount = Ticket::where('status', 'available')
            ->whereHas('plan.router', function ($query) use ($request) {
                $query->where('user_id', $request->user()->id);
            })
            ->count();

        return view('admin.plans.index', compact('plans', 'ticketsAvailableCount'));
    }

    public function create(Request $request)
    {
        $routers = Router::where('user_id', $request->user()->id)
            ->where('status', 'active')
            ->get();

        return view('admin.plans.create', compact('routers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'router_id' => 'required|exists:routers,id',
            'name' => 'required',
            'duration' => 'required',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable',
            'is_active' => 'nullable',
        ]);

        abort_unless(
            Router::where('id', $request->router_id)
                ->where('user_id', $request->user()->id)
                ->exists(),
            403
        );

        $plan = Plan::create([
            'router_id' => $request->router_id,
            'name' => $request->name,
            'duration' => $request->duration,
            'price' => $request->price,
            'description' => $request->description,
            'is_active' => $request->has('is_active'),
        ]);

        ActivityLogger::log('plan.created', $plan, [
            'name' => $plan->name,
            'router_id' => $plan->router_id,
            'price' => $plan->price,
            'duration' => $plan->duration,
            'is_active' => $plan->is_active,
        ], $request);

        return redirect()->route('dashboard.plans.index')
            ->with('success', 'Forfait ajoute avec succes.');
    }

    public function edit(Request $request, Plan $plan)
    {
        $this->ensurePlanAccess($request, $plan);

        $routers = Router::where('user_id', $request->user()->id)
            ->orderBy('name')
            ->get();

        return view('admin.plans.create', compact('plan', 'routers'));
    }

    public function update(Request $request, Plan $plan)
    {
        $this->ensurePlanAccess($request, $plan);

        $request->validate([
            'router_id' => 'required|exists:routers,id',
            'name' => 'required',
            'duration' => 'required',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable',
            'is_active' => 'nullable',
        ]);

        abort_unless(
            Router::where('id', $request->router_id)
                ->where('user_id', $request->user()->id)
                ->exists(),
            403
        );

        $plan->update([
            'router_id' => $request->router_id,
            'name' => $request->name,
            'duration' => $request->duration,
            'price' => $request->price,
            'description' => $request->description,
            'is_active' => $request->has('is_active'),
        ]);

        ActivityLogger::log('plan.updated', $plan, [
            'name' => $plan->name,
            'router_id' => $plan->router_id,
            'price' => $plan->price,
            'is_active' => $plan->is_active,
        ], $request);

        return redirect()->route('dashboard.plans.index')
            ->with('success', 'Forfait mis a jour.');
    }

    public function toggle(Request $request, Plan $plan)
    {
        $this->ensurePlanAccess($request, $plan);

        $plan->update(['is_active' => ! $plan->is_active]);

        ActivityLogger::log('plan.status_changed', $plan, [
            'is_active' => $plan->is_active,
        ], $request);

        return back()->with('success', $plan->is_active ? 'Forfait active.' : 'Forfait desactive.');
    }

    public function destroy(Request $request, Plan $plan)
    {
        $this->ensurePlanAccess($request, $plan);

        if ($plan->tickets()->exists() || Order::where('plan_id', $plan->id)->exists()) {
            return back()->with('error', 'Impossible de supprimer ce forfait car il contient deja des tickets ou ventes. Desactivez-le plutot.');
        }

        ActivityLogger::log('plan.deleted', $plan, [
            'name' => $plan->name,
        ], $request);

        $plan->delete();

        return back()->with('success', 'Forfait supprime.');
    }

    private function ensurePlanAccess(Request $request, Plan $plan): void
    {
        $plan->loadMissing('router');

        abort_unless($plan->router && $plan->router->user_id === $request->user()->id, 403);
    }
}
