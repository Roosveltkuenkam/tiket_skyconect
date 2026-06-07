@extends('layouts.admin')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <span class="eyebrow"><span class="eyebrow-dot"></span> Ventes globales</span>
        <h3>Commandes</h3>
        <p>Suivez toutes les ventes de tickets, les paiements associes et les tickets livres.</p>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="panel-card mb-4">
    <form method="GET" action="{{ route('admin.orders.index') }}" class="row g-3">
        <div class="col-md-3">
            <label class="form-label">Client</label>
            <select name="client_id" class="form-control">
                <option value="">Tous les clients</option>
                @foreach($clients as $client)
                    <option value="{{ $client->id }}" {{ request('client_id') == $client->id ? 'selected' : '' }}>
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
                    <option value="{{ $router->id }}" {{ request('router_id') == $router->id ? 'selected' : '' }}>
                        {{ $router->name }} - {{ $router->user->name ?? 'Sans client' }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-2">
            <label class="form-label">Statut</label>
            <select name="status" class="form-control">
                <option value="">Tous</option>
                @foreach($statuses as $value => $label)
                    <option value="{{ $value }}" {{ request('status') === $value ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-2">
            <label class="form-label">Du</label>
            <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
        </div>

        <div class="col-md-2">
            <label class="form-label">Au</label>
            <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
        </div>

        <div class="col-md-4">
            <label class="form-label">Reference</label>
            <input type="text" name="reference" class="form-control" value="{{ request('reference') }}" placeholder="SKY-...">
        </div>

        <div class="col-md-4">
            <label class="form-label">Telephone</label>
            <input type="text" name="phone" class="form-control" value="{{ request('phone') }}" placeholder="690000000">
        </div>

        <div class="col-md-2 d-flex align-items-end">
            <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-primary w-100">Reinitialiser</a>
        </div>

        <div class="col-md-2 d-flex align-items-end">
            <button class="sky-btn w-100">Filtrer</button>
        </div>
    </form>
</div>

<div class="panel-card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Reference</th>
                    <th>Client</th>
                    <th>Routeur</th>
                    <th>Forfait</th>
                    <th>Telephone</th>
                    <th>Montant</th>
                    <th>Statut</th>
                    <th>Ticket</th>
                    <th>Paiement</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                    <tr>
                        <td>{{ $order->created_at }}</td>
                        <td><strong>{{ $order->reference }}</strong></td>
                        <td>
                            <strong>{{ optional(optional(optional($order->plan)->router)->user)->name ?? '-' }}</strong><br>
                            <small>{{ optional(optional(optional($order->plan)->router)->user)->business_name ?? '' }}</small>
                        </td>
                        <td>{{ optional(optional($order->plan)->router)->name ?? '-' }}</td>
                        <td>{{ $order->plan->name ?? '-' }}</td>
                        <td>{{ $order->customer_phone }}</td>
                        <td><strong>{{ number_format($order->amount, 0, ',', ' ') }} XAF</strong></td>
                        <td>
                            @include('admin.orders.partials.status-badge', ['status' => $order->status])
                        </td>
                        <td>{{ $order->ticket->username ?? '-' }}</td>
                        <td>
                            @if($order->payment)
                                <span class="badge text-bg-light">{{ $order->payment->provider }}</span>
                                <span class="badge text-bg-secondary">{{ $order->payment->status }}</span>
                            @else
                                -
                            @endif
                        </td>
                        <td class="d-flex gap-1">
                            <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-outline-primary">Details</a>
                            @if(auth()->user()->canAccessBackOffice('orders.manage') && $order->status === 'pending')
                                <form method="POST" action="{{ route('admin.orders.cancel', $order) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button class="btn btn-sm btn-outline-danger">Annuler</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" class="text-center text-muted py-4">Aucune commande trouvee.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $orders->links() }}
</div>
@endsection
