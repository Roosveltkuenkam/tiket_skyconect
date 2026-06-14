@extends('layouts.admin')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center african-header">
    <div>
        <span class="eyebrow"><span class="eyebrow-dot"></span> {{ __('ui.dashboard_subscriptions.eyebrow') }}</span>
        <h3>{{ __('ui.dashboard_subscriptions.title') }}</h3>
        <p>{{ __('ui.dashboard_subscriptions.subtitle') }}</p>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if($subscription && $subscription->plan)
    <div class="panel-card mb-4 subscription-current african-pattern">
        <div>
            <span class="eyebrow"><span class="eyebrow-dot"></span> {{ __('ui.dashboard_subscriptions.current_plan') }}</span>
            <h4>{{ __('ui.subscriptions_page.plan_names.' . $subscription->plan->slug) }}</h4>
            <p class="section-copy mb-0">{{ __('ui.dashboard_subscriptions.current_copy', ['date' => optional($subscription->expires_at)->format('d/m/Y') ?: __('ui.dashboard_subscriptions.no_expiration')]) }}</p>
        </div>
        <div class="subscription-current-side">
            <div class="subscription-status-pill">
                {{ __('ui.subscriptions_page.statuses.' . $subscription->status) }}
            </div>
            <a href="#subscription-plans" class="sky-btn-outline">{{ __('ui.dashboard_subscriptions.compare') }}</a>
        </div>
    </div>
@endif

<div id="subscription-plans" class="plans-grid subscription-choice-grid">
    @foreach($plans as $plan)
        @php($isCurrent = $subscription && $subscription->subscription_plan_id === $plan->id)
        @php($nameKey = 'ui.subscriptions_page.plan_names.' . $plan->slug)
        @php($descriptionKey = 'ui.subscriptions_page.plan_descriptions.' . $plan->slug)
        @php($featuresKey = 'ui.dashboard_subscriptions.features.' . $plan->slug)
        <article class="plan-card subscription-choice-card {{ $isCurrent ? 'is-current' : '' }}">
            @if($plan->slug === 'standard')
                <span class="subscription-ribbon">{{ __('ui.dashboard_subscriptions.default') }}</span>
            @elseif($plan->slug === 'pro')
                <span class="subscription-ribbon pro">{{ __('ui.dashboard_subscriptions.recommended') }}</span>
            @endif

            <div class="plan-duration">{{ __($nameKey) }}</div>
            <p class="section-copy">{{ __($descriptionKey) }}</p>
            <div class="plan-price">{{ number_format($plan->monthly_price, 0, ',', ' ') }} <span>XAF / {{ __('ui.dashboard_subscriptions.month') }}</span></div>

            <div class="subscription-limits">
                <div><strong>{{ $plan->max_routers ?? __('ui.subscriptions_page.unlimited') }}</strong><span>{{ __('ui.dashboard_subscriptions.routers') }}</span></div>
                <div><strong>{{ $plan->max_tickets_per_month ?? __('ui.subscriptions_page.unlimited') }}</strong><span>{{ __('ui.dashboard_subscriptions.tickets') }}</span></div>
                <div><strong>{{ $plan->max_sales_per_month ?? __('ui.subscriptions_page.unlimited') }}</strong><span>{{ __('ui.dashboard_subscriptions.sales') }}</span></div>
                <div><strong>{{ number_format((float) ($plan->quota_rate_percent ?? 5), 2, ',', ' ') }}%</strong><span>Taux quota</span></div>
                <div><strong>{{ number_format((float) ($plan->withdrawal_fee_percent ?? 5), 2, ',', ' ') }}%</strong><span>Frais retrait</span></div>
            </div>

            <ul class="plan-list">
                @foreach(__($featuresKey) as $feature)
                    <li>{{ $feature }}</li>
                @endforeach
            </ul>

            <form method="POST" action="{{ route('dashboard.subscriptions.choose', $plan) }}" style="margin-top:auto;">
                @csrf
                <button class="{{ $isCurrent ? 'sky-btn-outline' : 'sky-btn' }}" style="width:100%;" {{ $isCurrent ? 'disabled' : '' }}>
                    {{ $isCurrent ? __('ui.dashboard_subscriptions.active') : __('ui.dashboard_subscriptions.choose') }}
                </button>
            </form>
        </article>
    @endforeach
</div>

<div class="panel-card mt-4">
    <div class="section-head mb-0">
        <div>
            <span class="eyebrow"><span class="eyebrow-dot"></span> {{ __('ui.dashboard_subscriptions.rules_eyebrow') }}</span>
            <h4>{{ __('ui.dashboard_subscriptions.rules_title') }}</h4>
        </div>
        <p class="section-copy">{{ __('ui.dashboard_subscriptions.rules_copy') }}</p>
    </div>
</div>
@endsection
