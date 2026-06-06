<?php

namespace App\Http\Controllers;

use App\Models\ClientSubscription;
use App\Models\SubscriptionPlan;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class DashboardSubscriptionController extends Controller
{
    public function index(Request $request)
    {
        $plans = $this->availablePlans();
        $subscription = $request->user()->activeSubscription();

        return view('dashboard.subscriptions.index', compact('plans', 'subscription'));
    }

    public function choose(Request $request, SubscriptionPlan $plan)
    {
        abort_unless($this->availablePlans()->contains('id', $plan->id), 404);

        $subscription = ClientSubscription::updateOrCreate(
            ['user_id' => $request->user()->id],
            [
                'subscription_plan_id' => $plan->id,
                'status' => ClientSubscription::STATUS_ACTIVE,
                'starts_at' => now(),
                'expires_at' => now()->addDays(30),
                'last_payment_amount' => $plan->monthly_price,
                'last_payment_status' => $plan->monthly_price > 0 ? 'pending_manual_confirmation' : 'included',
                'last_paid_at' => $plan->monthly_price > 0 ? now() : null,
                'notes' => 'Plan choisi depuis le dashboard client.',
            ]
        );

        ActivityLogger::log('client_subscription.selected', $subscription, [
            'subscription_plan_id' => $plan->id,
            'plan_slug' => $plan->slug,
            'monthly_price' => $plan->monthly_price,
        ], $request);

        return back()->with('success', __('ui.dashboard_subscriptions.changed'));
    }

    private function availablePlans()
    {
        return SubscriptionPlan::where('is_active', true)
            ->whereIn('slug', ['standard', 'pro', 'entreprise'])
            ->orderBy('monthly_price')
            ->get();
    }
}
