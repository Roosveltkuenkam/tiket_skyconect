@extends('layouts.admin')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <span class="eyebrow"><span class="eyebrow-dot"></span> Monetisation</span>
        <h3>Abonnements clients</h3>
        <p>Gerez les plans SaaS, les limites et les abonnements des proprietaires WiFi.</p>
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
        <span class="eyebrow"><span class="eyebrow-dot"></span> Nouveau plan</span>
        <form method="POST" action="{{ route('admin.subscriptions.plans.store') }}" class="row g-3 mt-2">
            @csrf
            <div class="col-md-3">
                <label class="form-label">Nom</label>
                <input type="text" name="name" class="form-control" placeholder="Standard" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Prix mensuel</label>
                <input type="number" name="monthly_price" class="form-control" value="0" min="0" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Routeurs max</label>
                <input type="number" name="max_routers" class="form-control" min="0">
            </div>
            <div class="col-md-2">
                <label class="form-label">Tickets/mois</label>
                <input type="number" name="max_tickets_per_month" class="form-control" min="0">
            </div>
            <div class="col-md-2">
                <label class="form-label">Ventes/mois</label>
                <input type="number" name="max_sales_per_month" class="form-control" min="0">
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button class="sky-btn w-100">OK</button>
            </div>
            <div class="col-12">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="2"></textarea>
            </div>
        </form>
    </div>
@endif

<div class="panel-card mb-4">
    <span class="eyebrow"><span class="eyebrow-dot"></span> Plans disponibles</span>
    <div class="table-responsive mt-3">
        <table class="table">
            <thead>
                <tr>
                    <th>Plan</th>
                    <th>Prix</th>
                    <th>Routeurs</th>
                    <th>Tickets/mois</th>
                    <th>Ventes/mois</th>
                    <th>Statut</th>
                </tr>
            </thead>
            <tbody>
                @forelse($plans as $plan)
                    <tr>
                        <td><strong>{{ $plan->name }}</strong><br><small>{{ $plan->description }}</small></td>
                        <td>{{ number_format($plan->monthly_price, 0, ',', ' ') }} XAF</td>
                        <td>{{ $plan->max_routers ?? 'Illimite' }}</td>
                        <td>{{ $plan->max_tickets_per_month ?? 'Illimite' }}</td>
                        <td>{{ $plan->max_sales_per_month ?? 'Illimite' }}</td>
                        <td>{{ $plan->is_active ? 'Actif' : 'Inactif' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">Aucun plan abonnement.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="panel-card mb-4">
    <form method="GET" action="{{ route('admin.subscriptions.index') }}" class="row g-3">
        <div class="col-md-4">
            <label class="form-label">Client</label>
            <input type="text" name="client" class="form-control" value="{{ request('client') }}" placeholder="Nom, email, commerce">
        </div>
        <div class="col-md-3">
            <label class="form-label">Statut</label>
            <select name="status" class="form-control">
                <option value="">Tous</option>
                @foreach($statuses as $value => $label)
                    <option value="{{ $value }}" {{ request('status') === $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <a href="{{ route('admin.subscriptions.index') }}" class="btn btn-outline-primary w-100">Reset</a>
        </div>
        <div class="col-md-3 d-flex align-items-end">
            <button class="sky-btn w-100">Filtrer abonnements</button>
        </div>
    </form>
</div>

<div class="panel-card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Client</th>
                    <th>Plan</th>
                    <th>Statut</th>
                    <th>Expiration</th>
                    <th>Dernier paiement</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($subscriptions as $subscription)
                    <tr>
                        <td>
                            <strong>{{ $subscription->user->name }}</strong><br>
                            <small>{{ $subscription->user->business_name ?: $subscription->user->email }}</small>
                        </td>
                        <td>{{ $subscription->plan->name ?? '-' }}</td>
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
                                    <button class="btn btn-sm btn-outline-danger">Suspendre</button>
                                </form>
                            @endif
                            @if(auth()->user()->canAccessBackOffice('subscriptions.manage') && $subscription->status === 'suspended')
                                <form method="POST" action="{{ route('admin.subscriptions.reactivate', $subscription) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button class="btn btn-sm btn-outline-success">Reactiver</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">Aucun abonnement trouve.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $subscriptions->links() }}
</div>
@endsection
