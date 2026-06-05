@extends('layouts.admin')

@section('content')
<div class="page-header">
    <span class="eyebrow"><span class="eyebrow-dot"></span> Controle temps reel</span>
    <h3>Dashboard administrateur</h3>
    <p>Ventes, revenus, tickets disponibles et activite reseau.</p>
</div>

@if($onboarding)
    <div class="panel-card mb-4">
        <div class="d-flex justify-content-between align-items-center gap-3">
            <div>
                <h4 class="mb-1">Lancement de votre espace Wi-Fi</h4>
                <p class="section-copy mb-0">
                    {{ $onboarding['completed'] }}/{{ $onboarding['total'] }} etapes terminees avant votre premiere vente.
                </p>
            </div>
            <a href="{{ route('dashboard.onboarding.index') }}" class="sky-btn">Continuer</a>
        </div>
        <div class="progress mt-3" style="height:12px;border-radius:999px;">
            <div class="progress-bar" style="width: {{ $onboarding['progress'] }}%; background: linear-gradient(135deg, #1e88e5, #0d47a1);"></div>
        </div>
    </div>
@endif

@if($globalStats)
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-title">Clients total</div>
                <div class="stat-value">{{ $globalStats['clients_total'] }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-title">Clients actifs</div>
                <div class="stat-value">{{ $globalStats['clients_active'] }}</div>
                <small class="text-muted">{{ $globalStats['clients_inactive'] }} inactif(s)</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-title">Routeurs connectes</div>
                <div class="stat-value">{{ $globalStats['routers_connected'] }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-title">Tickets vendus aujourd'hui</div>
                <div class="stat-value">{{ $globalStats['tickets_sold_today'] }}</div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-title">CA total</div>
                <div class="stat-value">{{ number_format($globalStats['revenue_total'], 0, ',', ' ') }} XAF</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-title">CA jour</div>
                <div class="stat-value">{{ number_format($globalStats['revenue_today'], 0, ',', ' ') }} XAF</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-title">CA mois</div>
                <div class="stat-value">{{ number_format($globalStats['revenue_month'], 0, ',', ' ') }} XAF</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-title">Commandes en attente</div>
                <div class="stat-value">{{ $globalStats['orders_pending'] }}</div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="stat-card">
                <div class="stat-title">Paiements reussis</div>
                <div class="stat-value">{{ $globalStats['payments_successful'] }}</div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="stat-card">
                <div class="stat-title">Paiements echoues</div>
                <div class="stat-value">{{ $globalStats['payments_failed'] }}</div>
            </div>
        </div>
    </div>
@endif

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-title">Chiffre d'affaires du jour</div>
            <div class="stat-value">{{ $revenueToday }} XAF</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-title">Ventes semaine</div>
            <div class="stat-value">{{ $salesWeek }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-title">Ventes mois</div>
            <div class="stat-value">{{ $salesMonth }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-title">Tickets disponibles</div>
            <div class="stat-value">{{ $ticketsAvailable }}</div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <div class="panel-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="mb-0">Revenus & ventes</h4>
                <span class="badge text-bg-success">7 derniers jours</span>
            </div>
            <div class="chart-card" style="align-items:stretch;gap:16px;">
                @foreach($chartRows as $row)
                    @php
                        $revenueHeight = 32 + (($row['revenue'] / $maxChartRevenue) * 150);
                        $salesHeight = 24 + (($row['sales'] / $maxChartSales) * 110);
                    @endphp
                    <div style="flex:1;display:flex;flex-direction:column;justify-content:flex-end;gap:8px;min-width:52px;">
                        <div style="display:flex;align-items:flex-end;justify-content:center;gap:5px;height:190px;">
                            <div title="Revenus: {{ $row['revenue'] }} XAF" class="chart-bar" style="width:20px;height:{{ $revenueHeight }}px;"></div>
                            <div title="Ventes: {{ $row['sales'] }}" class="chart-bar" style="width:12px;height:{{ $salesHeight }}px;background:linear-gradient(180deg,#22c55e,#15803d);"></div>
                        </div>
                        <div style="text-align:center;font-size:12px;color:#64748b;font-weight:800;">{{ $row['label'] }}</div>
                        <div style="text-align:center;font-size:11px;color:#0f2747;">
                            <strong>{{ $row['sales'] }}</strong> vente(s)<br>
                            {{ number_format($row['revenue'], 0, ',', ' ') }} XAF
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="d-flex gap-3 mt-3" style="color:#64748b;font-size:13px;font-weight:800;">
                <span><i class="bi bi-square-fill" style="color:#1e88e5;"></i> Revenus</span>
                <span><i class="bi bi-square-fill" style="color:#22c55e;"></i> Ventes</span>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="panel-card">
            <h4>Connexions</h4>
            <div class="ticket-mini">
                <div class="stat-title">Utilisateurs connectes</div>
                <div class="stat-value">--</div>
                <p class="section-copy mb-0">Pret pour la synchronisation MikroTik RouterOS.</p>
            </div>
        </div>
    </div>
</div>

@if($globalStats)
    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="panel-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="mb-0">Alertes stock faible</h4>
                    <span class="badge text-bg-warning">{{ $lowStockPlans->count() }} alerte(s)</span>
                </div>

                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Forfait</th>
                                <th>Routeur</th>
                                <th>Tickets dispo.</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($lowStockPlans as $plan)
                                <tr>
                                    <td><strong>{{ $plan->name }}</strong></td>
                                    <td>{{ $plan->router->name ?? '-' }}</td>
                                    <td>
                                        <span class="badge {{ $plan->available_tickets_count === 0 ? 'text-bg-danger' : 'text-bg-warning' }}">
                                            {{ $plan->available_tickets_count }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-4">Aucune alerte stock.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="panel-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="mb-0">Nouveaux clients inscrits</h4>
                    <span class="badge text-bg-primary">{{ $newClients->count() }} recents</span>
                </div>

                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Client</th>
                                <th>Commerce</th>
                                <th>Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($newClients as $client)
                                <tr>
                                    <td>
                                        <strong>{{ $client->name }}</strong><br>
                                        <small>{{ $client->email }}</small>
                                    </td>
                                    <td>{{ $client->business_name ?: '-' }}</td>
                                    <td>
                                        @if($client->is_active)
                                            <span class="badge text-bg-success">Actif</span>
                                        @else
                                            <span class="badge text-bg-secondary">Inactif</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-4">Aucun nouveau client.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endif

<div class="panel-card">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Dernieres ventes</h4>
        <span class="badge text-bg-primary">{{ $ordersCount }} commandes</span>
    </div>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Telephone</th>
                    <th>Forfait</th>
                    <th>Montant</th>
                    <th>Statut</th>
                    <th>Ticket</th>
                </tr>
            </thead>
            <tbody>
                @foreach($latestOrders as $order)
                    <tr>
                        <td>{{ $order->created_at }}</td>
                        <td>{{ $order->customer_phone }}</td>
                        <td>{{ $order->plan->name ?? '-' }}</td>
                        <td><strong>{{ $order->amount }} XAF</strong></td>
                        <td>
                            @if($order->status === 'paid')
                                <span class="badge text-bg-success">Paye</span>
                            @else
                                <span class="badge text-bg-warning">{{ $order->status }}</span>
                            @endif
                        </td>
                        <td>{{ $order->ticket->username ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
