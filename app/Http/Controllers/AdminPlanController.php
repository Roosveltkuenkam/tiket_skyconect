<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Router;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class AdminPlanController extends Controller
{
    private function routeName(Request $request, $name)
    {
        return ($request->is('dashboard*') ? 'dashboard' : 'admin') . '.plans.' . $name;
    }

    public function index()
    {
        $plans = Plan::with('router')
            ->when(auth()->check() && auth()->user()->isClientOwner(), function ($query) {
                $query->whereHas('router', function ($routerQuery) {
                    $routerQuery->where('user_id', auth()->id());
                });
            })
            ->latest()
            ->get();

        return view('admin.plans.index', compact('plans'));
    }

    public function create()
    {
        $routers = Router::where('status', 'active')
            ->when(auth()->check() && auth()->user()->isClientOwner(), function ($query) {
                $query->where('user_id', auth()->id());
            })
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

        if ($request->user()->isClientOwner()) {
            abort_unless(
                Router::where('id', $request->router_id)->where('user_id', $request->user()->id)->exists(),
                403
            );
        }

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

        return redirect()->route($this->routeName($request, 'index'))
            ->with('success', 'Forfait ajoute avec succes.');
    }
}
