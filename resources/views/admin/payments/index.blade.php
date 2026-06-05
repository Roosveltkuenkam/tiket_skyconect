@extends('layouts.admin')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <span class="eyebrow"><span class="eyebrow-dot"></span> Controle financier</span>
        <h3>Paiements</h3>
        <p>Controlez les transactions, les references provider, les commandes liees et les remboursements.</p>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="panel-card mb-4">
    <form method="GET" action="{{ route('admin.payments.index') }}" class="row g-3">
        <div class="col-md-3">
            <label class="form-label">Provider</label>
            <select name="provider" class="form-control">
                <option value="">Tous les providers</option>
                @foreach($providers as $value => $label)
                    <option value="{{ $value }}" {{ request('provider') === $value ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-3">
            <label class="form-label">Statut</label>
            <select name="status" class="form-control">
                <option value="">Tous les statuts</option>
                @foreach($statuses as $value => $label)
                    <option value="{{ $value }}" {{ request('status') === $value ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-3">
            <label class="form-label">Reference</label>
            <input type="text" name="reference" class="form-control" value="{{ request('reference') }}" placeholder="Campay, operateur, SKY-...">
        </div>

        <div class="col-md-3">
            <label class="form-label">Telephone</label>
            <input type="text" name="phone" class="form-control" value="{{ request('phone') }}" placeholder="690000000">
        </div>

        <div class="col-md-3">
            <label class="form-label">Du</label>
            <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
        </div>

        <div class="col-md-3">
            <label class="form-label">Au</label>
            <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
        </div>

        <div class="col-md-3 d-flex align-items-end">
            <label class="form-check d-flex align-items-center gap-2 mb-2">
                <input type="checkbox" name="orphans" value="1" class="form-check-input" {{ request()->boolean('orphans') ? 'checked' : '' }}>
                <span>Paiements orphelins</span>
            </label>
        </div>

        <div class="col-md-1 d-flex align-items-end">
            <a href="{{ route('admin.payments.index') }}" class="btn btn-outline-primary w-100">Reset</a>
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
                    <th>Commande</th>
                    <th>Client</th>
                    <th>Telephone</th>
                    <th>Provider</th>
                    <th>Reference</th>
                    <th>Montant</th>
                    <th>Statut</th>
                    <th>Orphelin</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $payment)
                    <tr>
                        <td>{{ $payment->created_at }}</td>
                        <td>
                            @if($payment->order)
                                <a href="{{ route('admin.orders.show', $payment->order) }}" class="btn btn-sm btn-outline-primary">
                                    {{ $payment->order->reference }}
                                </a>
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            <strong>{{ optional(optional(optional(optional($payment->order)->plan)->router)->user)->name ?? '-' }}</strong><br>
                            <small>{{ optional(optional(optional(optional($payment->order)->plan)->router)->user)->business_name ?? '' }}</small>
                        </td>
                        <td>{{ $payment->phone }}</td>
                        <td>{{ strtoupper($payment->provider) }}</td>
                        <td>{{ $payment->campay_reference ?: $payment->operator_reference ?: '-' }}</td>
                        <td><strong>{{ number_format($payment->amount, 0, ',', ' ') }} XAF</strong></td>
                        <td>@include('admin.payments.partials.status-badge', ['status' => $payment->status])</td>
                        <td>
                            @if(! $payment->order)
                                <span class="badge text-bg-danger">Oui</span>
                            @else
                                <span class="badge text-bg-success">Non</span>
                            @endif
                        </td>
                        <td class="d-flex gap-1">
                            <a href="{{ route('admin.payments.show', $payment) }}" class="btn btn-sm btn-outline-primary">Details</a>
                            @if(auth()->user()->canAccessBackOffice('payments.manage'))
                                <form method="POST" action="{{ route('admin.payments.verify', $payment) }}">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-secondary">Verifier</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center text-muted py-4">Aucun paiement trouve.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $payments->links() }}
</div>
@endsection
