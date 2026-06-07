@extends('layouts.admin')

@section('content')
@php
    $currentArea = $routeArea ?? 'admin';
@endphp
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <span class="eyebrow"><span class="eyebrow-dot"></span> {{ __('ui.admin_tickets.eyebrow') }}</span>
        <h3>{{ __('ui.admin_tickets.title') }}</h3>
        <p>
            @if($currentArea === 'dashboard')
                {{ $ticketTotalCount ?? $tickets->total() }} ticket(s) en stock
            @else
                {{ __('ui.admin_tickets.subtitle') }}
            @endif
        </p>
    </div>

    <div class="d-flex gap-2">
        <a href="{{ route(($routeArea ?? 'admin') . '.tickets.export', request()->query()) }}" class="btn btn-outline-primary">
            <i class="bi bi-download"></i> {{ __('ui.admin_tickets.export_csv') }}
        </a>

        @if($currentArea === 'dashboard' || auth()->user()->canAccessBackOffice('tickets.manage'))
            <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#clearTicketsModal">
                <i class="bi bi-trash3"></i> {{ __('ui.admin_tickets.clear') }}
            </button>
            <a href="{{ route(($routeArea ?? 'admin') . '.tickets.import.create') }}" class="sky-btn">
                <i class="bi bi-upload"></i> {{ __('ui.admin_tickets.import_csv') }}
            </a>
        @endif
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

@if($currentArea === 'dashboard' && isset($planStockCards))
    <div class="ticket-stock-grid mb-4">
        @foreach($planStockCards as $stockPlan)
            <div class="ticket-stock-card">
                <div>
                    <strong>{{ strtoupper($stockPlan->name) }}</strong>
                    <span>{{ optional($stockPlan->router)->name ?: '-' }}</span>
                </div>
                <div class="text-end">
                    <strong class="stock-available">{{ $stockPlan->available_tickets_count }}</strong>
                    <span>{{ $stockPlan->total_tickets_count }} total</span>
                </div>
            </div>
        @endforeach
    </div>
@endif

<div class="panel-card mb-4">
    <form method="GET" action="{{ route(($routeArea ?? 'admin') . '.tickets.index') }}" class="row g-3">
        @if($currentArea === 'admin')
            <div class="col-md-3">
                <label class="form-label">{{ __('ui.admin_tickets.client') }}</label>
                <select name="client_id" class="form-control">
                    <option value="">{{ __('ui.admin_tickets.all_clients') }}</option>
                    @foreach($clients as $client)
                        <option value="{{ $client->id }}" {{ request('client_id') == $client->id ? 'selected' : '' }}>
                            {{ $client->name }} - {{ $client->business_name ?: $client->email }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endif

        <div class="col-md-3">
                <label class="form-label">{{ __('ui.admin_tickets.router') }}</label>
                <select name="router_id" class="form-control">
                <option value="">{{ __('ui.admin_tickets.all_routers') }}</option>
                @foreach($routers as $router)
                    <option value="{{ $router->id }}" {{ request('router_id') == $router->id ? 'selected' : '' }}>
                        {{ $router->name }}
                        @if($currentArea === 'admin')
                            - {{ $router->user->name ?? __('ui.admin_tickets.no_client') }}
                        @endif
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-3">
            <label class="form-label">{{ __('ui.admin_tickets.plan') }}</label>
            <select name="plan_id" class="form-control">
                <option value="">{{ __('ui.admin_tickets.all_plans') }}</option>
                @foreach($plans as $plan)
                    <option value="{{ $plan->id }}" {{ request('plan_id') == $plan->id ? 'selected' : '' }}>
                        {{ $plan->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-2">
            <label class="form-label">{{ __('ui.admin_tickets.status') }}</label>
            <select name="status" class="form-control">
                <option value="">{{ __('ui.admin_tickets.all') }}</option>
                <option value="available" {{ request('status') === 'available' ? 'selected' : '' }}>{{ __('ui.admin_tickets.statuses.available') }}</option>
                <option value="sold" {{ request('status') === 'sold' ? 'selected' : '' }}>{{ __('ui.admin_tickets.statuses.sold') }}</option>
                <option value="disabled" {{ request('status') === 'disabled' ? 'selected' : '' }}>{{ __('ui.admin_tickets.statuses.disabled') }}</option>
                <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>{{ __('ui.admin_tickets.statuses.expired') }}</option>
            </select>
        </div>

        <div class="col-md-1 d-flex align-items-end">
            <button class="sky-btn w-100">OK</button>
        </div>
    </form>
</div>

<div class="panel-card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    @if(($routeArea ?? 'admin') === 'admin')
                        <th>{{ __('ui.admin_tickets.client') }}</th>
                    @endif
                    <th>{{ __('ui.admin_tickets.router') }}</th>
                    <th>Username</th>
                    <th>Password</th>
                    <th>{{ __('ui.admin_tickets.plan') }}</th>
                    <th>{{ __('ui.admin_tickets.profile') }}</th>
                    <th>{{ __('ui.admin_tickets.status') }}</th>
                    <th>{{ __('ui.admin_tickets.order') }}</th>
                    <th>{{ __('ui.admin_tickets.sold_at') }}</th>
                    <th>{{ __('ui.admin_tickets.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tickets as $ticket)
                    <tr>
                        @if(($routeArea ?? 'admin') === 'admin')
                            <td>
                                <strong>{{ optional(optional(optional($ticket->plan)->router)->user)->name ?? '-' }}</strong><br>
                                <small>{{ optional(optional(optional($ticket->plan)->router)->user)->business_name ?? '' }}</small>
                            </td>
                        @endif
                        <td>{{ optional(optional($ticket->plan)->router)->name ?? '-' }}</td>
                        <td><strong>{{ $ticket->username }}</strong></td>
                        <td>
                            @if($canViewPasswords)
                                {{ $ticket->password }}
                            @else
                                <span class="badge text-bg-secondary">{{ __('ui.admin_tickets.masked') }}</span>
                            @endif
                        </td>
                        <td>{{ $ticket->plan->name ?? '-' }}</td>
                        <td>{{ $ticket->profile ?: '-' }}</td>
                        <td>
                            @if($ticket->status === 'available')
                                <span class="badge text-bg-success">{{ __('ui.admin_tickets.statuses.available') }}</span>
                            @elseif($ticket->status === 'sold')
                                <span class="badge text-bg-primary">{{ __('ui.admin_tickets.statuses.sold') }}</span>
                            @elseif($ticket->status === 'disabled')
                                <span class="badge text-bg-danger">{{ __('ui.admin_tickets.statuses.disabled') }}</span>
                            @else
                                <span class="badge text-bg-secondary">{{ $ticket->status }}</span>
                            @endif
                        </td>
                        <td>
                            @if($ticket->order)
                                <a href="{{ route('orders.show', $ticket->order->publicRouteParameters()) }}" class="btn btn-sm btn-outline-primary">
                                    {{ $ticket->order->reference }}
                                </a>
                            @else
                                -
                            @endif
                        </td>
                        <td>{{ $ticket->sold_at ?? '-' }}</td>
                        <td class="d-flex gap-1">
                            @if((($routeArea ?? 'admin') === 'dashboard' || auth()->user()->canAccessBackOffice('tickets.manage')) && $ticket->status === 'available')
                                <form method="POST" action="{{ route(($routeArea ?? 'admin') . '.tickets.disable', $ticket) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button class="btn btn-sm btn-outline-warning">{{ __('ui.admin_tickets.disable') }}</button>
                                </form>
                            @endif
                            @if((($routeArea ?? 'admin') === 'dashboard' || auth()->user()->canAccessBackOffice('tickets.manage')) && $ticket->status === 'disabled')
                                <form method="POST" action="{{ route(($routeArea ?? 'admin') . '.tickets.enable', $ticket) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button class="btn btn-sm btn-outline-success">{{ __('ui.admin_tickets.enable') }}</button>
                                </form>
                            @endif
                            @if((($routeArea ?? 'admin') === 'dashboard' || auth()->user()->canAccessBackOffice('tickets.manage')) && $ticket->status !== 'sold')
                                <form method="POST" action="{{ route(($routeArea ?? 'admin') . '.tickets.destroy', $ticket) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">{{ __('ui.admin_tickets.delete') }}</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ ($routeArea ?? 'admin') === 'admin' ? 10 : 9 }}" class="text-center text-muted py-4">
                            {{ __('ui.admin_tickets.no_ticket') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $tickets->links() }}
</div>

@if($currentArea === 'dashboard' && isset($importLots))
    <div class="panel-card mt-4 import-lots-card">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0"><i class="bi bi-box-seam"></i> Lots d'import ({{ $importLots->count() }})</h4>
            <span class="badge text-bg-info">CSV / Mikhmon</span>
        </div>
        <div class="table-responsive">
            <table class="table import-lots-table">
                <thead>
                    <tr>
                        <th>Lot</th>
                        <th>Tarif</th>
                        <th>Routeur</th>
                        <th>Total</th>
                        <th>Disponibles</th>
                        <th>Vendus</th>
                        <th>Importe le</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($importLots as $lot)
                        <tr>
                            <td><strong class="theme-link-icon">{{ $lot->lot }}</strong></td>
                            <td>
                                <span class="badge text-bg-info">{{ $lot->plan_name }}</span><br>
                                <strong>{{ number_format($lot->plan_price, 0, ',', ' ') }} FCFA</strong>
                            </td>
                            <td><span class="badge text-bg-light"><i class="bi bi-hdd-network"></i> {{ $lot->router_name }}</span></td>
                            <td><strong>{{ $lot->total_count }}</strong></td>
                            <td><span class="badge text-bg-success">{{ $lot->available_count }}</span></td>
                            <td><strong>{{ $lot->sold_count }}</strong></td>
                            <td>{{ \Carbon\Carbon::parse($lot->imported_at)->format('d/m/Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Aucun lot d'import trouve.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endif

@if($currentArea === 'dashboard' || auth()->user()->canAccessBackOffice('tickets.manage'))
<div class="modal fade" id="clearTicketsModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:22px;">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('ui.admin_tickets.clear_available_title') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route(($routeArea ?? 'admin') . '.tickets.clear') }}">
                @csrf
                @method('DELETE')
                <div class="modal-body">
                    <p>{{ __('ui.admin_tickets.clear_available_copy') }}</p>
                    <label class="form-label">{{ __('ui.admin_tickets.by_plan') }}</label>
                    <select name="plan_id" class="form-control">
                        <option value="">{{ __('ui.admin_tickets.all_plans') }}</option>
                        @foreach($plans as $plan)
                            <option value="{{ $plan->id }}">{{ $plan->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('ui.admin_tickets.cancel') }}</button>
                    <button type="submit" class="btn btn-danger">{{ __('ui.admin_tickets.clear') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection
