<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Router;
use App\Models\Ticket;
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
}
