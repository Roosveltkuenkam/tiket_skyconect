@extends('layouts.admin')

@section('content')

<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h3>Tarifs</h3>
        <p>{{ $plans->count() }} tarif(s)</p>
    </div>

    <a href="{{ route('admin.plans.create') }}" class="sky-btn">
        + Ajouter un tarif
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="alert alert-info">
    <strong>Comment fonctionnent les tarifs ?</strong><br>
    Chaque tarif génère un lien de paiement direct à placer sur le portail captif.
</div>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-value">{{ $plans->count() }}</div>
            <div class="stat-title">Total tarifs</div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-value">{{ $plans->where('is_active', true)->count() }}</div>
            <div class="stat-title">Actifs</div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-value">{{ \App\Models\Ticket::where('status', 'available')->count() }}</div>
            <div class="stat-title">Tickets disponibles</div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-value">{{ $plans->min('price') }} — {{ $plans->max('price') }}</div>
            <div class="stat-title">Fourchette de prix</div>
        </div>
    </div>
</div>

<div class="panel-card">
    <table class="table">
        <thead>
            <tr>
                <th>Tarif</th>
                <th>Routeur</th>
                <th>Prix</th>
                <th>Détails</th>
                <th>Stock</th>
                <th>Actif</th>
                <th>Lien</th>
            </tr>
        </thead>
        <tbody>
            @foreach($plans as $plan)
                <tr>
                    <td>
                        <strong>{{ strtoupper($plan->name) }}</strong><br>
                        <small>Ordre : {{ $plan->id }}</small>
                    </td>
                    <td>
                        <span class="badge bg-info text-dark">
                            {{ $plan->router->name ?? 'Aucun routeur' }}
                        </span>
                    </td>
                    <td>
                        <strong style="color:#1e9beb;">
                            {{ $plan->price }} XAF
                        </strong>
                    </td>

                    <td>
                        <span class="badge bg-warning text-dark">
                            {{ $plan->duration }}
                        </span>
                    </td>

                    <td>
                        <span class="badge bg-info text-dark">
                            {{ \App\Models\Ticket::where('plan_id', $plan->id)->where('status', 'available')->count() }}
                        </span>
                    </td>

                    <td>
                        @if($plan->is_active)
                            <span class="badge bg-success">Oui</span>
                        @else
                            <span class="badge bg-danger">Non</span>
                        @endif
                    </td>

                    <td>
                        <input class="form-control" value="{{ route('orders.create', $plan) }}" readonly>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

@endsection