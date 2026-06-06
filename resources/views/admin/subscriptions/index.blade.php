@extends('layouts.admin')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <span class="eyebrow"><span class="eyebrow-dot"></span> {{ __('ui.subscriptions_page.eyebrow') }}</span>
        <h3>{{ __('ui.subscriptions_page.title') }}</h3>
        <p>{{ __('ui.subscriptions_page.subtitle') }}</p>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

@if($errors->any())
    <div class="alert alert-danger">
        @foreach($errors->all() as $error)
            <div>{{ $error }}</div>
        @endforeach
    </div>
@endif

@if(auth()->user()->canAccessBackOffice('subscriptions.manage'))
    <div class="panel-card mb-4">
        <span class="eyebrow"><span class="eyebrow-dot"></span> {{ __('ui.subscriptions_page.new_plan') }}</span>
        <form method="POST" action="{{ route('admin.subscriptions.plans.store') }}" class="row g-3 mt-2">
            @csrf
            <div class="col-md-3">
                <label class="form-label">{{ __('ui.subscriptions_page.name') }}</label>
                <input type="text" name="name" class="form-control" placeholder="{{ __('ui.subscriptions_page.name_placeholder') }}" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">{{ __('ui.subscriptions_page.monthly_price') }}</label>
                <input type="number" name="monthly_price" class="form-control" value="0" min="0" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">{{ __('ui.subscriptions_page.max_routers') }}</label>
                <input type="number" name="max_routers" class="form-control" min="0">
            </div>
            <div class="col-md-2">
                <label class="form-label">{{ __('ui.subscriptions_page.tickets_per_month') }}</label>
                <input type="number" name="max_tickets_per_month" class="form-control" min="0">
            </div>
            <div class="col-md-2">
                <label class="form-label">{{ __('ui.subscriptions_page.sales_per_month') }}</label>
                <input type="number" name="max_sales_per_month" class="form-control" min="0">
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button class="sky-btn w-100">{{ __('ui.subscriptions_page.create_plan') }}</button>
            </div>
            <div class="col-12">
                <label class="form-label">{{ __('ui.subscriptions_page.description') }}</label>
                <textarea name="description" class="form-control" rows="2"></textarea>
            </div>
        </form>
    </div>
@endif

<div class="panel-card mb-4">
    <span class="eyebrow"><span class="eyebrow-dot"></span> {{ __('ui.subscriptions_page.available_plans') }}</span>
    <div class="table-responsive mt-3">
        <table class="table">
            <thead>
                <tr>
                    <th>{{ __('ui.subscriptions_page.plan') }}</th>
                    <th>{{ __('ui.subscriptions_page.price') }}</th>
                    <th>{{ __('ui.subscriptions_page.routers') }}</th>
                    <th>{{ __('ui.subscriptions_page.tickets_per_month') }}</th>
                    <th>{{ __('ui.subscriptions_page.sales_per_month') }}</th>
                    <th>{{ __('ui.subscriptions_page.status') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($plans as $plan)
                    @php($nameKey = 'ui.subscriptions_page.plan_names.' . $plan->slug)
                    @php($descriptionKey = 'ui.subscriptions_page.plan_descriptions.' . $plan->slug)
                    <tr>
                        <td>
                            <strong>{{ \Illuminate\Support\Facades\Lang::has($nameKey) ? __($nameKey) : $plan->name }}</strong><br>
                            <small>{{ \Illuminate\Support\Facades\Lang::has($descriptionKey) ? __($descriptionKey) : $plan->description }}</small>
                        </td>
                        <td>{{ number_format($plan->monthly_price, 0, ',', ' ') }} XAF</td>
                        <td>{{ $plan->max_routers ?? __('ui.subscriptions_page.unlimited') }}</td>
                        <td>{{ $plan->max_tickets_per_month ?? __('ui.subscriptions_page.unlimited') }}</td>
                        <td>{{ $plan->max_sales_per_month ?? __('ui.subscriptions_page.unlimited') }}</td>
                        <td>{{ $plan->is_active ? __('ui.subscriptions_page.statuses.active') : __('ui.subscriptions_page.statuses.inactive') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">{{ __('ui.subscriptions_page.no_subscription_plan') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="panel-card mb-4">
    <form method="GET" action="{{ route('admin.subscriptions.index') }}" class="row g-3">
        <div class="col-md-4">
            <label class="form-label">{{ __('ui.subscriptions_page.client') }}</label>
            <input type="text" name="client" class="form-control" value="{{ request('client') }}" placeholder="{{ __('ui.subscriptions_page.client_placeholder') }}">
        </div>
        <div class="col-md-3">
            <label class="form-label">{{ __('ui.subscriptions_page.status') }}</label>
            <select name="status" class="form-control">
                <option value="">{{ __('ui.subscriptions_page.all') }}</option>
                @foreach($statuses as $value => $label)
                    <option value="{{ $value }}" {{ request('status') === $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <a href="{{ route('admin.subscriptions.index') }}" class="btn btn-outline-primary w-100">{{ __('ui.subscriptions_page.reset') }}</a>
        </div>
        <div class="col-md-3 d-flex align-items-end">
            <button class="sky-btn w-100">{{ __('ui.subscriptions_page.filter_subscriptions') }}</button>
        </div>
    </form>
</div>

<div class="panel-card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>{{ __('ui.subscriptions_page.client') }}</th>
                    <th>{{ __('ui.subscriptions_page.plan') }}</th>
                    <th>{{ __('ui.subscriptions_page.status') }}</th>
                    <th>{{ __('ui.subscriptions_page.expiration') }}</th>
                    <th>{{ __('ui.subscriptions_page.last_payment') }}</th>
                    <th>{{ __('ui.subscriptions_page.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($subscriptions as $subscription)
                    @php($subscriptionPlanSlug = optional($subscription->plan)->slug)
                    @php($subscriptionPlanNameKey = 'ui.subscriptions_page.plan_names.' . $subscriptionPlanSlug)
                    <tr>
                        <td>
                            <strong>{{ $subscription->user->name }}</strong><br>
                            <small>{{ $subscription->user->business_name ?: $subscription->user->email }}</small>
                        </td>
                        <td>{{ $subscriptionPlanSlug && \Illuminate\Support\Facades\Lang::has($subscriptionPlanNameKey) ? __($subscriptionPlanNameKey) : ($subscription->plan->name ?? '-') }}</td>
                        <td>{{ $statuses[$subscription->status] ?? $subscription->status }}</td>
                        <td>{{ $subscription->expires_at ?: '-' }}</td>
                        <td>
                            {{ $subscription->last_payment_amount ? number_format($subscription->last_payment_amount, 0, ',', ' ') . ' XAF' : '-' }}<br>
                            <small>{{ $subscription->last_payment_status ?: '' }}</small>
                        </td>
                        <td class="d-flex gap-1">
                            @if(auth()->user()->canAccessBackOffice('subscriptions.manage') && $subscription->status !== 'suspended')
                                <form method="POST" action="{{ route('admin.subscriptions.suspend', $subscription) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button class="btn btn-sm btn-outline-danger">{{ __('ui.subscriptions_page.suspend') }}</button>
                                </form>
                            @endif
                            @if(auth()->user()->canAccessBackOffice('subscriptions.manage') && $subscription->status === 'suspended')
                                <form method="POST" action="{{ route('admin.subscriptions.reactivate', $subscription) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button class="btn btn-sm btn-outline-success">{{ __('ui.subscriptions_page.reactivate') }}</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">{{ __('ui.subscriptions_page.no_subscription') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $subscriptions->links() }}
</div>
@endsection
