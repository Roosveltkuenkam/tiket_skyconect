@extends('layouts.admin')

@section('content')
@php
    $currentArea = $routeArea ?? 'admin';
@endphp
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <span class="eyebrow"><span class="eyebrow-dot"></span> Catalogue</span>
        <h3>Plans Wi-Fi</h3>
        <p>{{ $plans->count() }} plan(s) disponibles pour le portail captif.</p>
    </div>

    <a href="{{ route(($routeArea ?? 'admin') . '.plans.create') }}" class="sky-btn">
        <i class="bi bi-plus-circle"></i> Ajouter un plan
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-title">Total plans</div>
            <div class="stat-value">{{ $plans->count() }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-title">Plans actifs</div>
            <div class="stat-value">{{ $plans->where('is_active', true)->count() }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-title">Tickets disponibles</div>
            <div class="stat-value">{{ $ticketsAvailableCount }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-title">Prix</div>
            <div class="stat-value">{{ $plans->min('price') }} - {{ $plans->max('price') }}</div>
        </div>
    </div>
</div>

<div class="panel-card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Plan</th>
                    <th>Routeur</th>
                    <th>Prix</th>
                    <th>Duree</th>
                    <th>Stock</th>
                    <th>Statut</th>
                    <th>Lien paiement</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($plans as $plan)
                    <tr>
                        <td><strong>{{ $plan->name }}</strong><br><small>#{{ $plan->id }}</small></td>
                        <td><span class="badge text-bg-info">{{ $plan->router->name ?? 'Aucun routeur' }}</span></td>
                        <td><strong class="theme-link-icon">{{ $plan->price }} XAF</strong></td>
                        <td><span class="badge text-bg-warning">{{ $plan->duration }}</span></td>
                        <td><span class="badge text-bg-primary">{{ $plan->available_tickets_count }}</span></td>
                        <td>
                            @if($plan->is_active)
                                <span class="badge text-bg-success">Actif</span>
                            @else
                                <span class="badge text-bg-danger">Inactif</span>
                            @endif
                        </td>
                        <td>
                            <input class="form-control" value="{{ route('orders.create', $plan) }}" readonly>
                        </td>
                        <td class="d-flex gap-1 flex-wrap">
                            @if($currentArea === 'dashboard')
                                <a href="{{ route('dashboard.plans.edit', $plan) }}" class="btn btn-sm btn-outline-warning" title="Modifier">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="POST" action="{{ route('dashboard.plans.toggle', $plan) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button class="btn btn-sm {{ $plan->is_active ? 'btn-outline-secondary' : 'btn-outline-success' }}" title="{{ $plan->is_active ? 'Desactiver' : 'Activer' }}">
                                        <i class="bi {{ $plan->is_active ? 'bi-pause-circle' : 'bi-play-circle' }}"></i>
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('dashboard.plans.destroy', $plan) }}" onsubmit="return confirm('Supprimer ce forfait ? Cette action est bloquee si des tickets ou ventes existent.');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" title="Supprimer">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
