@extends('layouts.admin')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <span class="eyebrow"><span class="eyebrow-dot"></span> Ventes</span>
        <h3>Commandes</h3>
        <p>Suivez uniquement les ventes liees a vos routeurs et forfaits.</p>
    </div>
</div>

<div class="panel-card mb-4">
    <form method="GET" action="{{ route('dashboard.orders.index') }}" class="row g-3">
        <div class="col-md-3">
            <label class="form-label">Reference</label>
            <input type="text" name="reference" class="form-control" value="{{ request('reference') }}" placeholder="SKY-...">
        </div>
        <div class="col-md-3">
            <label class="form-label">Telephone</label>
            <input type="text" name="phone" class="form-control" value="{{ request('phone') }}" placeholder="690000000">
        </div>
        <div class="col-md-3">
            <label class="form-label">Statut</label>
            <select name="status" class="form-control">
                <option value="">Tous</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>En attente</option>
                <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Payee</option>
                <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Echouee</option>
                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Annulee</option>
            </select>
        </div>
        <div class="col-md-3 d-flex align-items-end">
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
                    <th>Forfait</th>
                    <th>Routeur</th>
                    <th>Montant</th>
                    <th>Statut</th>
                    <th>Ticket</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                    <tr>
                        <td>{{ $order->created_at }}</td>
                        <td><strong>{{ $order->reference }}</strong></td>
                        <td>{{ $order->customer_phone }}</td>
                        <td>{{ $order->plan->name ?? '-' }}</td>
                        <td>{{ optional(optional($order->plan)->router)->name ?? '-' }}</td>
                        <td><strong>{{ $order->amount }} XAF</strong></td>
                        <td>
                            @if($order->status === 'paid')
                                <span class="badge text-bg-success">Payee</span>
                            @elseif($order->status === 'pending')
                                <span class="badge text-bg-warning">En attente</span>
                            @elseif($order->status === 'failed')
                                <span class="badge text-bg-danger">Echouee</span>
                            @else
                                <span class="badge text-bg-secondary">{{ $order->status }}</span>
                            @endif
                        </td>
                        <td>{{ $order->ticket->username ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">Aucune commande trouvee.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $orders->links() }}
</div>
@endsection
