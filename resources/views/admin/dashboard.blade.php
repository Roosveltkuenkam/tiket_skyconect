@extends('layouts.admin')

@section('content')

<div class="page-header">
    <h3>Tableau de bord</h3>
    <p>Accueil / Tableau de bord</p>
</div>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-title">Tickets importés</div>
            <div class="stat-value">{{ $ticketsCount }}</div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-title">Commandes totales</div>
            <div class="stat-value">{{ $ordersCount }}</div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-title">Commandes payées</div>
            <div class="stat-value">{{ $paidOrdersCount }}</div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-title">Solde estimé</div>
            <div class="stat-value">
                {{ \App\Models\Order::where('status', 'paid')->sum('amount') }} XAF
            </div>
        </div>
    </div>
</div>

<div class="panel-card mb-4">
    <h4>Forfaits & liens portail captif</h4>

    <table class="table mt-3">
        <thead>
            <tr>
                <th>Forfait</th>
                <th>Prix</th>
                <th>Total tickets</th>
                <th>Disponibles</th>
                <th>Vendus</th>
                <th>Lien portail captif</th>
            </tr>
        </thead>
        <tbody>
            @foreach($plans as $plan)
                <tr>
                    <td>{{ $plan->name }}</td>
                    <td>{{ $plan->price }} XAF</td>
                    <td>{{ \App\Models\Ticket::where('plan_id', $plan->id)->count() }}</td>
                    <td>{{ \App\Models\Ticket::where('plan_id', $plan->id)->where('status', 'available')->count() }}</td>
                    <td>{{ \App\Models\Ticket::where('plan_id', $plan->id)->where('status', 'sold')->count() }}</td>
                    <td>
                        <input class="form-control" value="{{ route('orders.create', $plan) }}" readonly>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="panel-card">
    <h4>Dernières ventes</h4>

    <table class="table mt-3">
        <thead>
            <tr>
                <th>Date</th>
                <th>Téléphone</th>
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
                    <td>{{ $order->amount }} XAF</td>
                    <td>
                        @if($order->status === 'paid')
                            <span class="badge badge-paid">Payé</span>
                        @else
                            <span class="badge badge-pending">{{ $order->status }}</span>
                        @endif
                    </td>
                    <td>{{ $order->ticket->username ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

@endsection