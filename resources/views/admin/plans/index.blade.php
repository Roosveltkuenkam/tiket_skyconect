@extends('layouts.admin')

@section('content')
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
            <div class="stat-value">{{ \App\Models\Ticket::where('status', 'available')->count() }}</div>
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
                    <th>Lien portail</th>
                </tr>
            </thead>
            <tbody>
                @foreach($plans as $plan)
                    <tr>
                        <td><strong>{{ $plan->name }}</strong><br><small>#{{ $plan->id }}</small></td>
                        <td><span class="badge text-bg-info">{{ $plan->router->name ?? 'Aucun routeur' }}</span></td>
                        <td><strong style="color:#0d47a1;">{{ $plan->price }} XAF</strong></td>
                        <td><span class="badge text-bg-warning">{{ $plan->duration }}</span></td>
                        <td><span class="badge text-bg-primary">{{ \App\Models\Ticket::where('plan_id', $plan->id)->where('status', 'available')->count() }}</span></td>
                        <td>
                            @if($plan->is_active)
                                <span class="badge text-bg-success">Actif</span>
                            @else
                                <span class="badge text-bg-danger">Inactif</span>
                            @endif
                        </td>
                        <td><input class="form-control" value="{{ route('orders.create', $plan) }}" readonly></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
