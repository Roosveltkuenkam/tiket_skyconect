<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\Http\Request;
use App\Models\Router;

class AdminPlanController extends Controller
{
    public function index()
    {
        $plans = Plan::latest()->get();
        $plans = Plan::with('router')->latest()->get();

        return view('admin.plans.index', compact('plans'));
    }

    public function create()
    {
        $routers = Router::where('status', 'active')->get();

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

    Plan::create([
        'router_id' => $request->router_id,
        'name' => $request->name,
        'duration' => $request->duration,
        'price' => $request->price,
        'description' => $request->description,
        'is_active' => $request->has('is_active'),
    ]);

        return redirect()->route('admin.plans.index')
            ->with('success', 'Tarif ajouté avec succès.');
    }
}