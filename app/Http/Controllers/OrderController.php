<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function create(Plan $plan)
    {
        if (!$plan->is_active) {
            abort(403, 'Ce forfait n’est pas disponible pour le moment.');
        }

        return view('orders.create', compact('plan'));
    }

    public function store(Request $request, Plan $plan)
    {
        if (!$plan->is_active) {
        abort(403, 'Ce forfait n’est pas disponible pour le moment.');
        }
        $request->validate([
            'customer_phone' => 'required|min:9',
        ]);

        $order = Order::create([
            'reference' => 'SKY-' . strtoupper(Str::random(8)),
            'plan_id' => $plan->id,
            'customer_phone' => $request->customer_phone,
            'amount' => $plan->price,
            'status' => 'pending',
        ]);

        return redirect()->route('orders.show', $order);
    }

    public function show(Order $order)
    {
        return view('orders.show', compact('order'));
    }
}