@extends('layouts.admin')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <span class="eyebrow"><span class="eyebrow-dot"></span> Rapport</span>
        <h3>Quota, commissions et retraits</h3>
        <p>Suivi des soldes clients, commissions SkyConnect et versements.</p>
    </div>
</div>

<div class="panel-card mb-4">
    <form method="GET" action="{{ route('admin.reports.quota') }}" class="row g-3">
        <div class="col-md-3">
            <label class="form-label">Client</label>
            <select name="client_id" class="form-control">
                <option value="">Tous</option>
                @foreach($clients as $client)
                    <option value="{{ $client->id }}" {{ (string) $filters['client_id'] === (string) $client->id ? 'selected' : '' }}>{{ $client->business_name ?: $client->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Statut</label>
            <select name="status" class="form-control">
                <option value="">Tous</option>
                <option value="pending" {{ $filters['status'] === 'pending' ? 'selected' : '' }}>Recharge attente</option>
                <option value="confirmed" {{ $filters['status'] === 'confirmed' ? 'selected' : '' }}>Recharge validee</option>
                <option value="requested" {{ $filters['status'] === 'requested' ? 'selected' : '' }}>Retrait demande</option>
                <option value="approved" {{ $filters['status'] === 'approved' ? 'selected' : '' }}>Retrait valide</option>
                <option value="processed" {{ $filters['status'] === 'processed' ? 'selected' : '' }}>Retrait traite</option>
                <option value="rejected" {{ $filters['status'] === 'rejected' ? 'selected' : '' }}>Refuse</option>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Du</label>
            <input type="date" name="date_from" class="form-control" value="{{ $filters['date_from'] }}">
        </div>
        <div class="col-md-2">
            <label class="form-label">Au</label>
            <input type="date" name="date_to" class="form-control" value="{{ $filters['date_to'] }}">
        </div>
        <div class="col-md-3 d-flex align-items-end">
            <button class="sky-btn w-100">Filtrer</button>
        </div>
    </form>
</div>

<div class="row g-3 mb-4">
    @foreach($kpis as $label => $value)
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-title">{{ str_replace('_', ' ', $label) }}</div>
                <div class="stat-value">{{ number_format((int) $value, 0, ',', ' ') }} XAF</div>
            </div>
        </div>
    @endforeach
</div>

<div class="panel-card mb-4">
    <h4>Soldes par client</h4>
    <div class="table-responsive mt-3">
        <table class="table">
            <thead>
                <tr>
                    <th>Client</th>
                    <th>Ventes</th>
                    <th>Commission quota</th>
                    <th>Solde brut</th>
                    <th>Retraits attente</th>
                    <th>Retraits traites</th>
                    <th>Disponible</th>
                    <th>Quota</th>
                </tr>
            </thead>
            <tbody>
                @forelse($walletRows as $wallet)
                    <tr>
                        <td>{{ optional($wallet->user)->business_name ?: optional($wallet->user)->name }}</td>
                        <td>{{ number_format((int) $wallet->total_sales_amount, 0, ',', ' ') }} XAF</td>
                        <td>{{ number_format((int) $wallet->total_quota_used, 0, ',', ' ') }} XAF</td>
                        <td>{{ number_format((int) $wallet->grossClientBalance(), 0, ',', ' ') }} XAF</td>
                        <td>{{ number_format((int) $wallet->pending_withdrawal_amount, 0, ',', ' ') }} XAF</td>
                        <td>{{ number_format((int) $wallet->total_withdrawn, 0, ',', ' ') }} XAF</td>
                        <td>{{ number_format((int) $wallet->availableWithdrawalBalance(), 0, ',', ' ') }} XAF</td>
                        <td>{{ number_format((int) $wallet->quota_balance, 0, ',', ' ') }} XAF</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">Aucun wallet client.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $walletRows->links() }}
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="panel-card">
            <h4>Derniers mouvements quota</h4>
            <div class="table-responsive mt-3">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Client</th>
                            <th>Type</th>
                            <th>Montant</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentTransactions as $transaction)
                            <tr>
                                <td>{{ $transaction->created_at }}</td>
                                <td>{{ optional($transaction->user)->business_name ?: optional($transaction->user)->name }}</td>
                                <td>{{ $transaction->type }}</td>
                                <td>{{ number_format((int) $transaction->amount, 0, ',', ' ') }} XAF</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">Aucun mouvement.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="panel-card">
            <h4>Derniers retraits</h4>
            <div class="table-responsive mt-3">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Client</th>
                            <th>Brut</th>
                            <th>Net</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentWithdrawals as $withdrawal)
                            <tr>
                                <td>{{ $withdrawal->requested_at }}</td>
                                <td>{{ optional($withdrawal->user)->business_name ?: optional($withdrawal->user)->name }}</td>
                                <td>{{ number_format((int) $withdrawal->amount_requested, 0, ',', ' ') }} XAF</td>
                                <td>{{ number_format((int) $withdrawal->amount_to_pay, 0, ',', ' ') }} XAF</td>
                                <td>{{ $withdrawal->status }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">Aucun retrait.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
