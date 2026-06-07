@extends('layouts.admin')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <span class="eyebrow"><span class="eyebrow-dot"></span> Paiements</span>
        <h3>Transactions</h3>
        <p>Suivez les paiements associes aux ventes de vos routeurs.</p>
    </div>
</div>

<div class="panel-card mb-4">
    <form method="GET" action="{{ route('dashboard.payments.index') }}" class="row g-3">
        <div class="col-md-3">
            <label class="form-label">Telephone</label>
            <input type="text" name="phone" class="form-control" value="{{ request('phone') }}" placeholder="690000000">
        </div>
        <div class="col-md-3">
            <label class="form-label">Provider</label>
            <select name="provider" class="form-control">
                <option value="">Tous</option>
                <option value="test" {{ request('provider') === 'test' ? 'selected' : '' }}>Simulation</option>
                <option value="campay" {{ request('provider') === 'campay' ? 'selected' : '' }}>Campay</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Statut</label>
            <select name="status" class="form-control">
                <option value="">Tous</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>En attente</option>
                <option value="successful" {{ request('status') === 'successful' ? 'selected' : '' }}>Reussi</option>
                <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Echoue</option>
                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Annule</option>
                <option value="refunded" {{ request('status') === 'refunded' ? 'selected' : '' }}>Rembourse</option>
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
                    <th>Commande</th>
                    <th>Telephone</th>
                    <th>Provider</th>
                    <th>Methode</th>
                    <th>Montant</th>
                    <th>Statut</th>
                    <th>Paye le</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $payment)
                    <tr>
                        <td>{{ $payment->created_at }}</td>
                        <td><strong>{{ optional($payment->order)->reference ?? '-' }}</strong></td>
                        <td>{{ $payment->phone }}</td>
                        <td>{{ strtoupper($payment->provider) }}</td>
                        <td>{{ $payment->payment_method ?: '-' }}</td>
                        <td><strong>{{ $payment->amount }} XAF</strong></td>
                        <td>
                            @if($payment->status === 'successful')
                                <span class="badge text-bg-success">Reussi</span>
                            @elseif($payment->status === 'pending')
                                <span class="badge text-bg-warning">En attente</span>
                            @elseif($payment->status === 'failed')
                                <span class="badge text-bg-danger">Echoue</span>
                            @elseif($payment->status === 'refunded')
                                <span class="badge text-bg-info">Rembourse</span>
                            @else
                                <span class="badge text-bg-secondary">{{ $payment->status }}</span>
                            @endif
                        </td>
                        <td>{{ $payment->paid_at ?: '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">Aucun paiement trouve.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $payments->links() }}
</div>
@endsection
