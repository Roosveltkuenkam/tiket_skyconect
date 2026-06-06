@extends('layouts.admin')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <span class="eyebrow"><span class="eyebrow-dot"></span> {{ __('ui.finance_report.eyebrow') }}</span>
        <h3>{{ __('ui.finance_report.title') }}</h3>
        <p>{{ __('ui.finance_report.subtitle') }}</p>
    </div>
    @if(auth()->user()->canAccessBackOffice('reports.export'))
        <a href="{{ route('admin.reports.finance.export', request()->query()) }}" class="btn btn-outline-primary">
            <i class="bi bi-download"></i> {{ __('ui.finance_report.export_csv') }}
        </a>
    @endif
</div>

<div class="panel-card mb-4">
    <form method="GET" action="{{ route('admin.reports.finance') }}" class="row g-3">
        <div class="col-md-2">
            <label class="form-label">{{ __('ui.finance_report.from') }}</label>
            <input type="date" name="date_from" class="form-control" value="{{ $filters['date_from'] }}">
        </div>
        <div class="col-md-2">
            <label class="form-label">{{ __('ui.finance_report.to') }}</label>
            <input type="date" name="date_to" class="form-control" value="{{ $filters['date_to'] }}">
        </div>
        <div class="col-md-3">
            <label class="form-label">{{ __('ui.finance_report.client') }}</label>
            <select name="client_id" class="form-control">
                <option value="">{{ __('ui.finance_report.all_clients') }}</option>
                @foreach($clients as $client)
                    <option value="{{ $client->id }}" {{ (string) $filters['client_id'] === (string) $client->id ? 'selected' : '' }}>
                        {{ $client->name }} - {{ $client->business_name ?: $client->email }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">{{ __('ui.finance_report.router') }}</label>
            <select name="router_id" class="form-control">
                <option value="">{{ __('ui.finance_report.all_routers') }}</option>
                @foreach($routers as $router)
                    <option value="{{ $router->id }}" {{ (string) $filters['router_id'] === (string) $router->id ? 'selected' : '' }}>
                        {{ $router->name }} - {{ optional($router->user)->name ?: __('ui.finance_report.no_client') }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">{{ __('ui.finance_report.provider') }}</label>
            <select name="provider" class="form-control">
                <option value="">{{ __('ui.finance_report.all') }}</option>
                @foreach($providers as $provider)
                    <option value="{{ $provider }}" {{ $filters['provider'] === $provider ? 'selected' : '' }}>
                        {{ strtoupper($provider) }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">{{ __('ui.finance_report.commission_rate') }}</label>
            <input type="number" step="0.01" min="0" name="commission_rate" class="form-control" value="{{ $filters['commission_rate'] }}">
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <a href="{{ route('admin.reports.finance') }}" class="btn btn-outline-primary w-100">{{ __('ui.finance_report.reset') }}</a>
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button class="sky-btn w-100">{{ __('ui.finance_report.apply') }}</button>
        </div>
    </form>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-title">{{ __('ui.finance_report.sales_revenue') }}</div>
            <div class="stat-value">{{ number_format($kpis['Chiffre affaires ventes'], 0, ',', ' ') }} XAF</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-title">{{ __('ui.finance_report.paid_sales') }}</div>
            <div class="stat-value">{{ $kpis['Nombre ventes payees'] }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-title">{{ __('ui.finance_report.average_cart') }}</div>
            <div class="stat-value">{{ number_format($kpis['Panier moyen'], 0, ',', ' ') }} XAF</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-title">{{ __('ui.finance_report.estimated_net_balance') }}</div>
            <div class="stat-value">{{ number_format($kpis['Solde net estime'], 0, ',', ' ') }} XAF</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-title">{{ __('ui.finance_report.successful_payments') }}</div>
            <div class="stat-value">{{ $kpis['Paiements reussis'] }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-title">{{ __('ui.finance_report.failed_payments') }}</div>
            <div class="stat-value">{{ $kpis['Paiements echoues'] }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-title">{{ __('ui.finance_report.refunds') }}</div>
            <div class="stat-value">{{ number_format($kpis['Remboursements traites'], 0, ',', ' ') }} XAF</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-title">{{ __('ui.finance_report.subscriptions') }}</div>
            <div class="stat-value">{{ number_format($kpis['Revenus abonnements'], 0, ',', ' ') }} XAF</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-title">{{ __('ui.finance_report.estimated_commissions') }}</div>
            <div class="stat-value">{{ number_format($kpis['Commissions estimees'], 0, ',', ' ') }} XAF</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-title">{{ __('ui.finance_report.skyconnect_revenue') }}</div>
            <div class="stat-value">{{ number_format($kpis['Revenus SkyConnect estimes'], 0, ',', ' ') }} XAF</div>
        </div>
    </div>
</div>

@php
    $tables = [
        __('ui.finance_report.sales_by_day') => ['rows' => $salesByDay, 'first' => __('ui.finance_report.date')],
        __('ui.finance_report.sales_by_month') => ['rows' => $salesByMonth, 'first' => __('ui.finance_report.month')],
        __('ui.finance_report.sales_by_client') => ['rows' => $salesByClient, 'first' => __('ui.finance_report.client')],
        __('ui.finance_report.sales_by_router') => ['rows' => $salesByRouter, 'first' => __('ui.finance_report.router')],
        __('ui.finance_report.payments_by_provider') => ['rows' => $paymentsByProvider, 'first' => __('ui.finance_report.provider')],
        __('ui.finance_report.refunds_by_status') => ['rows' => $refundsByStatus, 'first' => __('ui.finance_report.status')],
        __('ui.finance_report.subscription_revenue') => ['rows' => $subscriptionRevenueByStatus, 'first' => __('ui.finance_report.status')],
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
                                <th>{{ __('ui.finance_report.count') }}</th>
                                <th>{{ __('ui.finance_report.amount') }}</th>
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
                                    <td colspan="3" class="text-center text-muted py-4">{{ __('ui.finance_report.no_data') }}</td>
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
