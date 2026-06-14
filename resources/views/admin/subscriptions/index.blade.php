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

@if(auth()->user()->isSuperAdmin())
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
            <div class="col-md-2">
                <label class="form-label">Taux quota (%)</label>
                <input type="number" name="quota_rate_percent" class="form-control" value="5" min="0" max="100" step="0.01" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Frais retrait (%)</label>
                <input type="number" name="withdrawal_fee_percent" class="form-control" value="5" min="0" max="100" step="0.01" required>
            </div>
            <div class="col-md-2 d-flex align-items-end">
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
    @php($canManageSubscriptionPlans = auth()->user()->isSuperAdmin())
    <div class="table-responsive mt-3">
        <table class="table">
            <thead>
                <tr>
                    <th>{{ __('ui.subscriptions_page.plan') }}</th>
                    <th>{{ __('ui.subscriptions_page.price') }}</th>
                    <th>{{ __('ui.subscriptions_page.routers') }}</th>
                    <th>{{ __('ui.subscriptions_page.tickets_per_month') }}</th>
                    <th>{{ __('ui.subscriptions_page.sales_per_month') }}</th>
                    <th>Taux quota</th>
                    <th>Frais retrait</th>
                    <th>{{ __('ui.subscriptions_page.status') }}</th>
                    @if($canManageSubscriptionPlans)
                        <th>{{ __('ui.subscriptions_page.actions') }}</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse($plans as $plan)
                    @php($updateFormId = 'subscription-plan-update-' . $plan->id)
                    <tr>
                        @if($canManageSubscriptionPlans)
                            <td style="min-width:260px;">
                                <input type="text" name="name" form="{{ $updateFormId }}" class="form-control mb-2" value="{{ old('name', $plan->name) }}" required>
                                <textarea name="description" form="{{ $updateFormId }}" class="form-control" rows="2">{{ old('description', $plan->description) }}</textarea>
                            </td>
                            <td style="min-width:150px;">
                                <input type="number" name="monthly_price" form="{{ $updateFormId }}" class="form-control" value="{{ old('monthly_price', $plan->monthly_price) }}" min="0" required>
                            </td>
                            <td style="min-width:120px;">
                                <input type="number" name="max_routers" form="{{ $updateFormId }}" class="form-control" value="{{ old('max_routers', $plan->max_routers) }}" min="0" placeholder="{{ __('ui.subscriptions_page.unlimited') }}">
                            </td>
                            <td style="min-width:150px;">
                                <input type="number" name="max_tickets_per_month" form="{{ $updateFormId }}" class="form-control" value="{{ old('max_tickets_per_month', $plan->max_tickets_per_month) }}" min="0" placeholder="{{ __('ui.subscriptions_page.unlimited') }}">
                            </td>
                            <td style="min-width:150px;">
                                <input type="number" name="max_sales_per_month" form="{{ $updateFormId }}" class="form-control" value="{{ old('max_sales_per_month', $plan->max_sales_per_month) }}" min="0" placeholder="{{ __('ui.subscriptions_page.unlimited') }}">
                            </td>
                            <td style="min-width:120px;">
                                <input type="number" name="quota_rate_percent" form="{{ $updateFormId }}" class="form-control" value="{{ old('quota_rate_percent', $plan->quota_rate_percent ?? 5) }}" min="0" max="100" step="0.01" required>
                            </td>
                            <td style="min-width:120px;">
                                <input type="number" name="withdrawal_fee_percent" form="{{ $updateFormId }}" class="form-control" value="{{ old('withdrawal_fee_percent', $plan->withdrawal_fee_percent ?? 5) }}" min="0" max="100" step="0.01" required>
                            </td>
                            <td>
                                <label class="d-flex align-items-center gap-2">
                                    <input type="checkbox" name="is_active" form="{{ $updateFormId }}" value="1" {{ old('is_active', $plan->is_active) ? 'checked' : '' }}>
                                    {{ __('ui.subscriptions_page.statuses.active') }}
                                </label>
                            </td>
                            <td>
                                <form id="{{ $updateFormId }}" method="POST" action="{{ route('admin.subscriptions.plans.update', $plan) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button class="btn btn-sm btn-outline-primary">Mettre a jour</button>
                                </form>
                            </td>
                        @else
                            @php($nameKey = 'ui.subscriptions_page.plan_names.' . $plan->slug)
                            @php($descriptionKey = 'ui.subscriptions_page.plan_descriptions.' . $plan->slug)
                            <td>
                                <strong>{{ \Illuminate\Support\Facades\Lang::has($nameKey) ? __($nameKey) : $plan->name }}</strong><br>
                                <small>{{ \Illuminate\Support\Facades\Lang::has($descriptionKey) ? __($descriptionKey) : $plan->description }}</small>
                            </td>
                            <td>{{ number_format($plan->monthly_price, 0, ',', ' ') }} XAF</td>
                            <td>{{ $plan->max_routers ?? __('ui.subscriptions_page.unlimited') }}</td>
                            <td>{{ $plan->max_tickets_per_month ?? __('ui.subscriptions_page.unlimited') }}</td>
                            <td>{{ $plan->max_sales_per_month ?? __('ui.subscriptions_page.unlimited') }}</td>
                            <td>{{ number_format((float) ($plan->quota_rate_percent ?? 5), 2, ',', ' ') }}%</td>
                            <td>{{ number_format((float) ($plan->withdrawal_fee_percent ?? 5), 2, ',', ' ') }}%</td>
                            <td>{{ $plan->is_active ? __('ui.subscriptions_page.statuses.active') : __('ui.subscriptions_page.statuses.inactive') }}</td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $canManageSubscriptionPlans ? 9 : 8 }}" class="text-center text-muted py-4">{{ __('ui.subscriptions_page.no_subscription_plan') }}</td>
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
