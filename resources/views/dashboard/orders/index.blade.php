@extends('layouts.admin')

@section('content')
@php
    $maxRevenue = max(array_column($dailySeries, 'revenue') ?: [0]) ?: 1;
    $maxSales = max(array_column($dailySeries, 'sales') ?: [0]) ?: 1;
    $maxHourly = max(array_column($hourlySeries, 'sales') ?: [0]) ?: 1;
    $chartWidth = 760;
    $chartHeight = 280;
    $paddingLeft = 46;
    $paddingRight = 22;
    $paddingTop = 18;
    $paddingBottom = 42;
    $plotWidth = $chartWidth - $paddingLeft - $paddingRight;
    $plotHeight = $chartHeight - $paddingTop - $paddingBottom;
    $dailyCount = max(count($dailySeries), 1);
    $barWidth = max(7, min(20, ($plotWidth / $dailyCount) * 0.42));
    $salesPoints = [];

    foreach ($dailySeries as $index => $item) {
        $x = $paddingLeft + ($dailyCount === 1 ? $plotWidth / 2 : ($plotWidth / ($dailyCount - 1)) * $index);
        $y = $paddingTop + $plotHeight - (($item['sales'] / $maxSales) * $plotHeight);
        $salesPoints[] = round($x, 2) . ',' . round($y, 2);
    }

    $money = fn ($amount) => number_format((float) $amount, 0, ',', ' ') . ' XAF';
    $networkLabel = function ($value) {
        $label = strtoupper((string) $value);

        return match (true) {
            str_contains($label, 'ORANGE') => 'ORANGE',
            str_contains($label, 'MTN') => 'MTN',
            str_contains($label, 'CAMPAY') => 'CAMPAY',
            str_contains($label, 'SIMULATION'), str_contains($label, 'TEST') => 'TEST',
            default => $label ?: 'TEST',
        };
    };
    $donut = function ($items) {
        $colors = ['#08bfa6', '#5b83f7', '#ffb703', '#b24bd1', '#22c55e'];
        $total = max($items->sum('amount'), 1);
        $cursor = 0;
        $parts = [];

        foreach ($items as $index => $item) {
            $next = $cursor + (((float) $item->amount / $total) * 100);
            $parts[] = $colors[$index % count($colors)] . ' ' . round($cursor, 2) . '% ' . round($next, 2) . '%';
            $cursor = $next;
        }

        return $items->isEmpty() ? '#e2e8f0 0% 100%' : implode(', ', $parts);
    };
@endphp

<div class="advanced-sales-page">
    <div class="page-header sales-advanced-header">
        <div>
            <span class="eyebrow"><span class="eyebrow-dot"></span> Ventes proprietaire</span>
            <h3>Recettes avancees</h3>
            <p>
                Tableau de bord /
                <strong>{{ \Carbon\Carbon::parse($filters['date_from'])->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($filters['date_to'])->format('d/m/Y') }}</strong>
            </p>
        </div>
    </div>

    <div class="sales-kpi-grid mb-4">
        <div class="sales-kpi-card accent-green">
            <span class="sales-kpi-icon"><i class="bi bi-check-circle-fill"></i></span>
            <div>
                <span>Payees</span>
                <strong>{{ $summary['paid_count'] }}</strong>
            </div>
        </div>
        <div class="sales-kpi-card accent-blue">
            <span class="sales-kpi-icon"><i class="bi bi-wallet2"></i></span>
            <div>
                <span>Recettes</span>
                <strong>{{ $money($summary['revenue']) }}</strong>
            </div>
        </div>
        <div class="sales-kpi-card accent-purple">
            <span class="sales-kpi-icon"><i class="bi bi-hdd-network-fill"></i></span>
            <div>
                <span>Top hotspot</span>
                <strong>{{ optional($summary['top_hotspot'])->label ?: '-' }}</strong>
            </div>
        </div>
        <div class="sales-kpi-card accent-teal">
            <span class="sales-kpi-icon"><i class="bi bi-phone"></i></span>
            <div>
                <span>Top reseau</span>
                <strong>{{ $networkLabel(optional($summary['top_network'])->label ?: '-') }}</strong>
            </div>
        </div>
    </div>

    <div class="panel-card mb-4">
        <form method="GET" action="{{ route('dashboard.orders.index') }}" class="sales-filter-grid">
            <div>
                <label class="form-label">Routeur / Hotspot</label>
                <select name="router_id" class="form-control">
                    <option value="">Tous les routeurs</option>
                    @foreach($routers as $router)
                        <option value="{{ $router->id }}" {{ (string) $filters['router_id'] === (string) $router->id ? 'selected' : '' }}>
                            {{ $router->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Date debut</label>
                <input type="date" name="date_from" class="form-control" value="{{ $filters['date_from'] }}">
            </div>
            <div>
                <label class="form-label">Date fin</label>
                <input type="date" name="date_to" class="form-control" value="{{ $filters['date_to'] }}">
            </div>
            <div>
                <label class="form-label">Statut</label>
                <select name="status" class="form-control">
                    <option value="">Tous les statuts</option>
                    @foreach(['pending' => 'En attente', 'paid' => 'Payee', 'failed' => 'Echouee', 'cancelled' => 'Annulee', 'refunded' => 'Remboursee'] as $status => $label)
                        <option value="{{ $status }}" {{ $filters['status'] === $status ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Recherche libre</label>
                <input type="text" name="search" class="form-control" value="{{ $filters['search'] }}" placeholder="Ref, client, tel...">
            </div>
            <div class="sales-filter-actions">
                <a href="{{ route('dashboard.orders.index') }}" class="btn btn-outline-primary">Reinitialiser</a>
                <button class="sky-btn"><i class="bi bi-search"></i> Filtrer</button>
            </div>
        </form>
    </div>

    <div class="sales-analytics-grid mb-4">
        <div class="panel-card sales-chart-panel">
            <div class="sales-panel-title">
                <h4><i class="bi bi-bar-chart-line"></i> Evolution des recettes</h4>
                <div class="sales-chart-switch">
                    <button type="button" class="active" data-sales-chart="bars">Barres</button>
                    <button type="button" data-sales-chart="line">Courbe</button>
                </div>
            </div>

            <svg class="advanced-sales-chart" viewBox="0 0 {{ $chartWidth }} {{ $chartHeight }}" role="img" aria-label="Evolution des recettes et ventes">
                @for($line = 0; $line <= 4; $line++)
                    @php
                        $y = $paddingTop + ($plotHeight / 4) * $line;
                    @endphp
                    <line x1="{{ $paddingLeft }}" y1="{{ $y }}" x2="{{ $chartWidth - $paddingRight }}" y2="{{ $y }}" class="chart-grid-line" />
                @endfor

                <g data-chart-bars>
                    @foreach($dailySeries as $index => $item)
                        @php
                            $x = $paddingLeft + ($dailyCount === 1 ? $plotWidth / 2 : ($plotWidth / max($dailyCount - 1, 1)) * $index) - ($barWidth / 2);
                            $height = max(2, ($item['revenue'] / $maxRevenue) * $plotHeight);
                            $y = $paddingTop + $plotHeight - $height;
                        @endphp
                        <rect x="{{ round($x, 2) }}" y="{{ round($y, 2) }}" width="{{ round($barWidth, 2) }}" height="{{ round($height, 2) }}" rx="5" class="sales-revenue-bar">
                            <title>{{ $item['label'] }} - {{ $money($item['revenue']) }}</title>
                        </rect>
                    @endforeach
                </g>

                <polyline points="{{ implode(' ', $salesPoints) }}" class="sales-count-line" />
                @foreach($dailySeries as $index => $item)
                    @php
                        $x = $paddingLeft + ($dailyCount === 1 ? $plotWidth / 2 : ($plotWidth / max($dailyCount - 1, 1)) * $index);
                        $y = $paddingTop + $plotHeight - (($item['sales'] / $maxSales) * $plotHeight);
                    @endphp
                    <circle cx="{{ round($x, 2) }}" cy="{{ round($y, 2) }}" r="4.5" class="sales-count-dot">
                        <title>{{ $item['label'] }} - {{ $item['sales'] }} vente(s)</title>
                    </circle>
                    @if($dailyCount <= 18 || $index % 2 === 0)
                        <text x="{{ round($x, 2) }}" y="{{ $chartHeight - 12 }}" text-anchor="middle" class="sales-chart-label">{{ $item['label'] }}</text>
                    @endif
                @endforeach
            </svg>

            <div class="sales-chart-legend">
                <span><i class="legend-dot blue"></i> Nb ventes</span>
                <span><i class="legend-dot green"></i> Recettes (XAF)</span>
            </div>
        </div>

        <div class="sales-side-charts">
            <div class="panel-card sales-donut-card">
                <h4><i class="bi bi-hdd-network"></i> Par hotspot</h4>
                <div class="donut-wrap">
                    <div class="sales-donut" style="background: conic-gradient({{ $donut($hotspotBreakdown) }});"></div>
                    <div class="donut-legend">
                        @forelse($hotspotBreakdown as $item)
                            <span><i></i>{{ $item->label }}</span>
                        @empty
                            <span>Aucune donnee</span>
                        @endforelse
                    </div>
                </div>
            </div>
            <div class="panel-card sales-donut-card">
                <h4><i class="bi bi-phone"></i> Par reseau</h4>
                <div class="donut-wrap">
                    <div class="sales-donut network" style="background: conic-gradient({{ $donut($networkBreakdown) }});"></div>
                    <div class="donut-legend">
                        @forelse($networkBreakdown as $item)
                            <span><i></i>{{ $networkLabel($item->label) }}</span>
                        @empty
                            <span>Aucune donnee</span>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="panel-card sales-hour-card mb-4">
        <h4><i class="bi bi-clock"></i> Activite par heure de la journee <small>(toute la periode)</small></h4>
            <div class="hour-bars">
            @foreach($hourlySeries as $item)
                @php
                    $height = max(6, ($item['sales'] / $maxHourly) * 122);
                @endphp
                <div class="hour-bar-item">
                    <span class="hour-bar" style="height: {{ round($height, 2) }}px;" title="{{ $item['label'] }} - {{ $item['sales'] }} vente(s)"></span>
                    <small>{{ $item['label'] }}</small>
                </div>
            @endforeach
        </div>
    </div>

    <div class="panel-card sales-table-panel">
        <div class="sales-table-head">
            <h4><i class="bi bi-cart-check"></i> {{ $orders->total() }} vente(s)</h4>
            <div class="d-flex gap-2 flex-wrap">
                <button type="button" class="sky-btn" onclick="window.print()"><i class="bi bi-printer-fill"></i> Imprimer tout</button>
                <a class="sky-btn-outline" href="mailto:?subject=Rapport ventes SkyConnect&body={{ urlencode(request()->fullUrl()) }}">
                    <i class="bi bi-envelope-fill"></i> Envoyer par email
                </a>
            </div>
        </div>

        <div class="sales-table-tools">
            <form method="GET" action="{{ route('dashboard.orders.index') }}" class="d-flex align-items-center gap-2">
                @foreach(request()->except('per_page', 'page') as $key => $value)
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endforeach
                <select name="per_page" class="form-control" onchange="this.form.submit()">
                    @foreach([10, 25, 50, 100] as $size)
                        <option value="{{ $size }}" {{ $filters['per_page'] === $size ? 'selected' : '' }}>{{ $size }}</option>
                    @endforeach
                </select>
                <span class="text-muted">par page</span>
            </form>

            <form method="GET" action="{{ route('dashboard.orders.index') }}" class="sales-quick-search">
                @foreach(request()->except('search', 'page') as $key => $value)
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endforeach
                <input type="text" name="search" class="form-control" value="{{ $filters['search'] }}" placeholder="Recherche rapide...">
            </form>
        </div>

        <div class="table-responsive">
            <table class="table sales-advanced-table">
                <thead>
                    <tr>
                        <th></th>
                        <th>Date</th>
                        <th>Telephone</th>
                        <th>Routeur</th>
                        <th>Forfait</th>
                        <th>Montant</th>
                        <th>Reseau</th>
                        <th>Statut</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr>
                            <td><span class="row-expand-dot">+</span></td>
                            <td><strong>{{ optional($order->created_at)->format('d/m/Y H:i:s') }}</strong><br><small>{{ $order->reference }}</small></td>
                            <td>{{ $order->customer_phone }}</td>
                            <td><strong>{{ optional(optional($order->plan)->router)->name ?? '-' }}</strong></td>
                            <td>{{ $order->plan->name ?? '-' }}</td>
                            <td><strong>{{ $money($order->amount) }}</strong></td>
                            <td><span class="network-pill">{{ $networkLabel(optional($order->payment)->payment_method ?: optional($order->payment)->provider ?: 'test') }}</span></td>
                            <td>@include('admin.orders.partials.status-badge', ['status' => $order->status])</td>
                            <td>
                                @if($order->status === 'paid' && $order->public_access_token)
                                    <a href="{{ route('tickets.show', $order->publicRouteParameters()) }}" class="receipt-pill" target="_blank">Recu</a>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">Aucune vente trouvee.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $orders->links() }}
    </div>
</div>

<script>
    document.querySelectorAll('[data-sales-chart]').forEach(function (button) {
        button.addEventListener('click', function () {
            var chart = document.querySelector('[data-chart-bars]');
            document.querySelectorAll('[data-sales-chart]').forEach(function (item) {
                item.classList.remove('active');
            });
            button.classList.add('active');

            if (chart) {
                chart.style.opacity = button.dataset.salesChart === 'line' ? '0.08' : '1';
            }
        });
    });
</script>
@endsection
