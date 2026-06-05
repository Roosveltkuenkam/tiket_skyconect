@extends('layouts.admin')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <span class="eyebrow"><span class="eyebrow-dot"></span> Finance</span>
        <h3>Rapports financiers</h3>
        <p>Analysez les ventes, paiements, remboursements et revenus abonnements SkyConnect.</p>
    </div>
    @if(auth()->user()->canAccessBackOffice('reports.export'))
        <a href="{{ route('admin.reports.finance.export', request()->query()) }}" class="btn btn-outline-primary">
            <i class="bi bi-download"></i> Export CSV
        </a>
    @endif
</div>

<div class="panel-card mb-4">
    <form method="GET" action="{{ route('admin.reports.finance') }}" class="row g-3">
        <div class="col-md-2">
            <label class="form-label">Du</label>
            <input type="date" name="date_from" class="form-control" value="{{ $filters['date_from'] }}">
        </div>
        <div class="col-md-2">
            <label class="form-label">Au</label>
            <input type="date" name="date_to" class="form-control" value="{{ $filters['date_to'] }}">
        </div>
        <div class="col-md-3">
            <label class="form-label">Client</label>
            <select name="client_id" class="form-control">
                <option value="">Tous les clients</option>
                @foreach($clients as $client)
                    <option value="{{ $client->id }}" {{ (string) $filters['client_id'] === (string) $client->id ? 'selected' : '' }}>
                        {{ $client->name }} - {{ $client->business_name ?: $client->email }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Routeur</label>
            <select name="router_id" class="form-control">
                <option value="">Tous les routeurs</option>
                @foreach($routers as $router)
                    <option value="{{ $router->id }}" {{ (string) $filters['router_id'] === (string) $router->id ? 'selected' : '' }}>
                        {{ $router->name }} - {{ optional($router->user)->name ?: 'Sans client' }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Provider</label>
            <select name="provider" class="form-control">
                <option value="">Tous</option>
                @foreach($providers as $provider)
                    <option value="{{ $provider }}" {{ $filters['provider'] === $provider ? 'selected' : '' }}>
                        {{ strtoupper($provider) }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Commission %</label>
            <input type="number" step="0.01" min="0" name="commission_rate" class="form-control" value="{{ $filters['commission_rate'] }}">
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <a href="{{ route('admin.reports.finance') }}" class="btn btn-outline-primary w-100">Reset</a>
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button class="sky-btn w-100">Appliquer</button>
        </div>
    </form>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-title">CA ventes</div>
            <div class="stat-value">{{ number_format($kpis['Chiffre affaires ventes'], 0, ',', ' ') }} XAF</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-title">Ventes payees</div>
            <div class="stat-value">{{ $kpis['Nombre ventes payees'] }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-title">Panier moyen</div>
            <div class="stat-value">{{ number_format($kpis['Panier moyen'], 0, ',', ' ') }} XAF</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-title">Solde net estime</div>
            <div class="stat-value">{{ number_format($kpis['Solde net estime'], 0, ',', ' ') }} XAF</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-title">Paiements reussis</div>
            <div class="stat-value">{{ $kpis['Paiements reussis'] }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-title">Paiements echoues</div>
            <div class="stat-value">{{ $kpis['Paiements echoues'] }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-title">Remboursements</div>
            <div class="stat-value">{{ number_format($kpis['Remboursements traites'], 0, ',', ' ') }} XAF</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-title">Abonnements</div>
            <div class="stat-value">{{ number_format($kpis['Revenus abonnements'], 0, ',', ' ') }} XAF</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-title">Commissions estimees</div>
            <div class="stat-value">{{ number_format($kpis['Commissions estimees'], 0, ',', ' ') }} XAF</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-title">Revenus SkyConnect</div>
            <div class="stat-value">{{ number_format($kpis['Revenus SkyConnect estimes'], 0, ',', ' ') }} XAF</div>
        </div>
    </div>
</div>

@php
    $tables = [
        'Ventes par jour' => ['rows' => $salesByDay, 'first' => 'Date'],
        'Ventes par mois' => ['rows' => $salesByMonth, 'first' => 'Mois'],
        'Ventes par client' => ['rows' => $salesByClient, 'first' => 'Client'],
        'Ventes par routeur' => ['rows' => $salesByRouter, 'first' => 'Routeur'],
        'Paiements par provider' => ['rows' => $paymentsByProvider, 'first' => 'Provider'],
        'Remboursements par statut' => ['rows' => $refundsByStatus, 'first' => 'Statut'],
        'Revenus abonnements' => ['rows' => $subscriptionRevenueByStatus, 'first' => 'Statut'],
    ];
@endphp

<div class="row g-4">
    @foreach($tables as $title => $table)
        <div class="col-lg-6">
            <div class="panel-card h-100">
                <h4>{{ $title }}</h4>
                <div class="table-responsive mt-3">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>{{ $table['first'] }}</th>
                                <th>Nombre</th>
                                <th>Montant</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($table['rows'] as $row)
                                <tr>
                                    <td>{{ $row->label ?: '-' }}</td>
                                    <td>{{ $row->count }}</td>
                                    <td><strong>{{ number_format($row->amount, 0, ',', ' ') }} XAF</strong></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-4">Aucune donnee.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection
