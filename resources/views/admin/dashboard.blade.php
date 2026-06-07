@extends('layouts.admin')

@section('content')
<div class="page-header">
    <span class="eyebrow"><span class="eyebrow-dot"></span> {{ __('ui.admin_dashboard.eyebrow') }}</span>
    <h3>{{ __('ui.admin_dashboard.title') }}</h3>
    <p>{{ __('ui.admin_dashboard.subtitle') }}</p>
</div>

@if($onboarding)
    <div class="panel-card mb-4">
        <div class="d-flex justify-content-between align-items-center gap-3">
            <div>
                <h4 class="mb-1">{{ __('ui.admin_dashboard.owner_launch') }}</h4>
                <p class="section-copy mb-0">
                    {{ __('ui.admin_dashboard.onboarding_progress', ['completed' => $onboarding['completed'], 'total' => $onboarding['total']]) }}
                </p>
            </div>
            <a href="{{ route('dashboard.onboarding.index') }}" class="sky-btn">{{ __('ui.admin_dashboard.continue') }}</a>
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
                <div class="stat-title">{{ __('ui.admin_dashboard.clients_total') }}</div>
                <div class="stat-value">{{ $globalStats['clients_total'] }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-title">{{ __('ui.admin_dashboard.clients_active') }}</div>
                <div class="stat-value">{{ $globalStats['clients_active'] }}</div>
                <small class="text-muted">{{ __('ui.admin_dashboard.inactive_count', ['count' => $globalStats['clients_inactive']]) }}</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-title">{{ __('ui.admin_dashboard.routers_connected') }}</div>
                <div class="stat-value">{{ $globalStats['routers_connected'] }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-title">{{ __('ui.admin_dashboard.tickets_sold_today') }}</div>
                <div class="stat-value">{{ $globalStats['tickets_sold_today'] }}</div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-title">{{ __('ui.admin_dashboard.revenue_total') }}</div>
                <div class="stat-value">{{ number_format($globalStats['revenue_total'], 0, ',', ' ') }} XAF</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-title">{{ __('ui.admin_dashboard.revenue_day_short') }}</div>
                <div class="stat-value">{{ number_format($globalStats['revenue_today'], 0, ',', ' ') }} XAF</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-title">{{ __('ui.admin_dashboard.revenue_month_short') }}</div>
                <div class="stat-value">{{ number_format($globalStats['revenue_month'], 0, ',', ' ') }} XAF</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-title">{{ __('ui.admin_dashboard.pending_orders') }}</div>
                <div class="stat-value">{{ $globalStats['orders_pending'] }}</div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="stat-card">
                <div class="stat-title">{{ __('ui.admin_dashboard.successful_payments') }}</div>
                <div class="stat-value">{{ $globalStats['payments_successful'] }}</div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="stat-card">
                <div class="stat-title">{{ __('ui.admin_dashboard.failed_payments') }}</div>
                <div class="stat-value">{{ $globalStats['payments_failed'] }}</div>
            </div>
        </div>
    </div>
@endif

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-title">{{ __('ui.admin_dashboard.revenue_today') }}</div>
            <div class="stat-value">{{ $revenueToday }} XAF</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-title">{{ __('ui.admin_dashboard.sales_week') }}</div>
            <div class="stat-value">{{ $salesWeek }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-title">{{ __('ui.admin_dashboard.sales_month') }}</div>
            <div class="stat-value">{{ $salesMonth }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-title">{{ __('ui.admin_dashboard.tickets_available') }}</div>
            <div class="stat-value">{{ $ticketsAvailable }}</div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <div class="panel-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="mb-0">{{ __('ui.admin_dashboard.revenue_sales') }}</h4>
                <span class="badge text-bg-success">{{ __('ui.admin_dashboard.last_7_days') }}</span>
            </div>
            @php
                $chartCount = max(count($chartRows), 1);
                $revenuePoints = [];
                $salesPoints = [];
                foreach($chartRows as $index => $row) {
                    $x = $chartCount === 1 ? 50 : 6 + (($index / ($chartCount - 1)) * 88);
                    $revenueY = 90 - (($row['revenue'] / max($maxChartRevenue, 1)) * 76);
                    $salesY = 90 - (($row['sales'] / max($maxChartSales, 1)) * 76);
                    $revenuePoints[] = round($x, 2) . ',' . round($revenueY, 2);
                    $salesPoints[] = round($x, 2) . ',' . round($salesY, 2);
                }
            @endphp
            <div class="chart-card line-chart-card">
                <svg class="line-chart" viewBox="0 0 100 100" role="img" aria-label="{{ __('ui.admin_dashboard.revenue_sales') }}">
                    <defs>
                        <linearGradient id="revenueLine" x1="0" y1="0" x2="1" y2="0">
                            <stop offset="0%" stop-color="#1e88e5" />
                            <stop offset="100%" stop-color="#0d47a1" />
                        </linearGradient>
                        <linearGradient id="salesLine" x1="0" y1="0" x2="1" y2="0">
                            <stop offset="0%" stop-color="#22c55e" />
                            <stop offset="100%" stop-color="#15803d" />
                        </linearGradient>
                    </defs>
                    <line x1="6" y1="90" x2="94" y2="90" class="chart-grid-line" />
                    <line x1="6" y1="65" x2="94" y2="65" class="chart-grid-line" />
                    <line x1="6" y1="40" x2="94" y2="40" class="chart-grid-line" />
                    <line x1="6" y1="15" x2="94" y2="15" class="chart-grid-line" />
                    <polyline points="{{ implode(' ', $revenuePoints) }}" class="chart-line chart-line-revenue" />
                    <polyline points="{{ implode(' ', $salesPoints) }}" class="chart-line chart-line-sales" />
                    @foreach($chartRows as $index => $row)
                        @php
                            $x = $chartCount === 1 ? 50 : 6 + (($index / ($chartCount - 1)) * 88);
                            $revenueY = 90 - (($row['revenue'] / max($maxChartRevenue, 1)) * 76);
                            $salesY = 90 - (($row['sales'] / max($maxChartSales, 1)) * 76);
                        @endphp
                        <circle cx="{{ $x }}" cy="{{ $revenueY }}" r="1.8" class="chart-dot chart-dot-revenue">
                            <title>{{ __('ui.admin_dashboard.revenue') }}: {{ number_format($row['revenue'], 0, ',', ' ') }} XAF</title>
                        </circle>
                        <circle cx="{{ $x }}" cy="{{ $salesY }}" r="1.8" class="chart-dot chart-dot-sales">
                            <title>{{ __('ui.admin_dashboard.sales') }}: {{ $row['sales'] }}</title>
                        </circle>
                    @endforeach
                </svg>
                <div class="line-chart-labels">
                    @foreach($chartRows as $row)
                        <div>
                            <div class="chart-label">{{ $row['label'] }}</div>
                            <div class="chart-meta">
                                {{ __('ui.admin_dashboard.sale_count', ['count' => $row['sales']]) }}<br>
                                {{ number_format($row['revenue'], 0, ',', ' ') }} XAF
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="d-flex gap-3 mt-3 chart-legend">
                <span><i class="bi bi-square-fill" style="color:#1e88e5;"></i> {{ __('ui.admin_dashboard.revenue') }}</span>
                <span><i class="bi bi-square-fill" style="color:#22c55e;"></i> {{ __('ui.admin_dashboard.sales') }}</span>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="panel-card">
            <h4>{{ __('ui.admin_dashboard.connections') }}</h4>
            <div class="ticket-mini">
                <div class="stat-title">{{ __('ui.admin_dashboard.connected_users') }}</div>
                <div class="stat-value">--</div>
                <p class="section-copy mb-0">{{ __('ui.admin_dashboard.routeros_ready') }}</p>
            </div>
        </div>
    </div>
</div>

@if($globalStats)
    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="panel-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="mb-0">{{ __('ui.admin_dashboard.low_stock_alerts') }}</h4>
                    <span class="badge text-bg-warning">{{ __('ui.admin_dashboard.alert_count', ['count' => $lowStockPlans->count()]) }}</span>
                </div>

                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>{{ __('ui.admin_dashboard.plan') }}</th>
                                <th>{{ __('ui.admin_dashboard.router') }}</th>
                                <th>{{ __('ui.admin_dashboard.available_tickets_short') }}</th>
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
                                    <td colspan="3" class="text-center text-muted py-4">{{ __('ui.admin_dashboard.no_stock_alert') }}</td>
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
                    <h4 class="mb-0">{{ __('ui.admin_dashboard.new_clients') }}</h4>
                    <span class="badge text-bg-primary">{{ __('ui.admin_dashboard.recent_count', ['count' => $newClients->count()]) }}</span>
                </div>

                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>{{ __('ui.admin_dashboard.client') }}</th>
                                <th>{{ __('ui.admin_dashboard.business') }}</th>
                                <th>{{ __('ui.admin_dashboard.status') }}</th>
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
                                            <span class="badge text-bg-success">{{ __('ui.admin_dashboard.active') }}</span>
                                        @else
                                            <span class="badge text-bg-secondary">{{ __('ui.admin_dashboard.inactive') }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-4">{{ __('ui.admin_dashboard.no_new_client') }}</td>
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
        <h4 class="mb-0">{{ __('ui.admin_dashboard.latest_sales') }}</h4>
        <span class="badge text-bg-primary">{{ __('ui.admin_dashboard.order_count', ['count' => $ordersCount]) }}</span>
    </div>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>{{ __('ui.admin_dashboard.date') }}</th>
                    <th>{{ __('ui.admin_dashboard.phone') }}</th>
                    <th>{{ __('ui.admin_dashboard.plan') }}</th>
                    <th>{{ __('ui.admin_dashboard.amount') }}</th>
                    <th>{{ __('ui.admin_dashboard.status') }}</th>
                    <th>{{ __('ui.admin_dashboard.ticket') }}</th>
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
                                <span class="badge text-bg-success">{{ __('ui.admin_dashboard.paid') }}</span>
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
