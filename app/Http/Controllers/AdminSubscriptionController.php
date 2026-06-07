<?php

namespace App\Http\Controllers;

use App\Models\ClientSubscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class AdminSubscriptionController extends Controller
{
    public function index(Request $request)
    {
        $plans = SubscriptionPlan::orderBy('monthly_price')->get();

        $subscriptions = ClientSubscription::with(['user', 'plan'])
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->when($request->filled('client'), function ($query) use ($request) {
                $query->whereHas('user', function ($userQuery) use ($request) {
                    $userQuery
                        ->where('name', 'like', '%' . $request->client . '%')
                        ->orWhere('email', 'like', '%' . $request->client . '%')
                        ->orWhere('business_name', 'like', '%' . $request->client . '%');
                });
            })
            ->latest()
            ->paginate(20)
            ->appends($request->query());

        return view('admin.subscriptions.index', [
            'plans' => $plans,
            'subscriptions' => $subscriptions,
            'statuses' => $this->statuses(),
        ]);
    }

    public function storePlan(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:subscription_plans,name',
            'description' => 'nullable|string|max:2000',
            'monthly_price' => 'required|integer|min:0',
            'max_routers' => 'nullable|integer|min:0',
            'max_tickets_per_month' => 'nullable|integer|min:0',
            'max_sales_per_month' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);

        $plan = SubscriptionPlan::create($data);

        ActivityLogger::log('subscription_plan.created', $plan, [
            'name' => $plan->name,
            'monthly_price' => $plan->monthly_price,
            'max_routers' => $plan->max_routers,
            'max_tickets_per_month' => $plan->max_tickets_per_month,
            'max_sales_per_month' => $plan->max_sales_per_month,
        ], $request);

        return back()->with('success', __('ui.subscriptions_page.messages.plan_created'));
    }

    public function assign(Request $request, User $client)
    {
        abort_unless($client->isClientOwner(), 404);

        $data = $request->validate([
            'subscription_plan_id' => 'required|exists:subscription_plans,id',
            'status' => 'required|in:trial,active,expired,suspended',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date',
            'last_payment_amount' => 'nullable|integer|min:0',
            'last_payment_status' => 'nullable|string|max:255',
            'last_payment_reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:2000',
        ]);

        $data['starts_at'] = $data['starts_at'] ?? now();
        $data['last_paid_at'] = $request->filled('last_payment_amount') ? now() : null;

        $subscription = ClientSubscription::updateOrCreate(
            ['user_id' => $client->id],
            $data + ['user_id' => $client->id]
        );

        ActivityLogger::log('client_subscription.assigned', $subscription, [
            'client_id' => $client->id,
            'client_email' => $client->email,
            'subscription_plan_id' => $subscription->subscription_plan_id,
            'status' => $subscription->status,
            'expires_at' => $subscription->expires_at,
            'last_payment_amount' => $subscription->last_payment_amount,
            'last_payment_status' => $subscription->last_payment_status,
        ], $request);

        return back()->with('success', __('ui.subscriptions_page.messages.subscription_updated'));
    }

    public function suspend(Request $request, ClientSubscription $subscription)
    {
        $subscription->update(['status' => ClientSubscription::STATUS_SUSPENDED]);

        ActivityLogger::log('client_subscription.suspended', $subscription, [
            'client_id' => $subscription->user_id,
            'subscription_plan_id' => $subscription->subscription_plan_id,
        ], $request);

        return back()->with('success', __('ui.subscriptions_page.messages.suspended'));
    }

    public function reactivate(Request $request, ClientSubscription $subscription)
    {
        $subscription->update(['status' => ClientSubscription::STATUS_ACTIVE]);

        ActivityLogger::log('client_subscription.reactivated', $subscription, [
            'client_id' => $subscription->user_id,
            'subscription_plan_id' => $subscription->subscription_plan_id,
        ], $request);

        return back()->with('success', __('ui.subscriptions_page.messages.reactivated'));
    }

    private function statuses()
    {
        return [
            ClientSubscription::STATUS_TRIAL => __('ui.subscriptions_page.statuses.trial'),
            ClientSubscription::STATUS_ACTIVE => __('ui.subscriptions_page.statuses.active'),
            ClientSubscription::STATUS_EXPIRED => __('ui.subscriptions_page.statuses.expired'),
            ClientSubscription::STATUS_SUSPENDED => __('ui.subscriptions_page.statuses.suspended'),
        ];
    }
}
