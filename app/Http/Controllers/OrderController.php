<?php

namespace App\Http\Controllers;

use App\Models\AdminNotification;
use App\Models\Order;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function create(Plan $plan)
    {
        if (! $plan->is_active) {
            abort(403, "Ce forfait n'est pas disponible pour le moment.");
        }

        $this->ensureOwnerCanSell($plan);

        return view('orders.create', compact('plan'));
    }

    public function store(Request $request, Plan $plan)
    {
        if (! $plan->is_active) {
            abort(403, "Ce forfait n'est pas disponible pour le moment.");
        }

        $this->ensureOwnerCanSell($plan);

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

    private function ensureOwnerCanSell(Plan $plan)
    {
        $plan->load('router.user');

        $owner = optional($plan->router)->user;

        if (! $owner) {
            return;
        }

        if (! $owner->canUseSubscription() || $owner->subscriptionLimitReached('sales')) {
            AdminNotification::notify(
                'account_suspended_or_limited',
                'Vente bloquee',
                'Une tentative achat a ete bloquee car le compte ' . $owner->name . " n'est pas autorise a vendre.",
                'warning',
                ['owner_id' => $owner->id, 'plan_id' => $plan->id]
            );

            abort(403, "Ce forfait n'est pas disponible pour le moment.");
        }
    }
}
