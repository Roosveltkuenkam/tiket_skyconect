<?php

namespace App\Http\Controllers;

use App\Models\AdminNotification;
use App\Models\Order;
use App\Models\Plan;
use App\Services\SettingManager;
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
            'public_access_token' => Str::random(48),
            'plan_id' => $plan->id,
            'customer_phone' => $request->customer_phone,
            'amount' => $plan->price,
            'status' => 'pending',
            'expires_at' => now()->addMinutes($this->orderExpirationMinutes()),
        ]);

        return redirect()->route('orders.show', $order->publicRouteParameters());
    }

    public function show(Order $order, $accessToken)
    {
        abort_unless($order->hasValidPublicAccessToken($accessToken), 404);

        if ($order->isExpired()) {
            $order->expire();
            $order->refresh();
        }

        return view('orders.show', compact('order'));
    }

    private function orderExpirationMinutes()
    {
        return max(1, (int) SettingManager::get('sales.order_expiration_minutes', 15));
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
