@extends('layouts.admin')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <span class="eyebrow"><span class="eyebrow-dot"></span> Quota</span>
        <h3>Quota et commissions</h3>
        <p>Suivez votre solde quota, vos recharges et les commissions consommees par vente.</p>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if($errors->any())
    <div class="alert alert-danger">
        @foreach($errors->all() as $error)
            <div>{{ $error }}</div>
        @endforeach
    </div>
@endif

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-title">Solde quota</div>
            <div class="stat-value">{{ number_format((int) $wallet->quota_balance, 0, ',', ' ') }} XAF</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-title">Total recharge</div>
            <div class="stat-value">{{ number_format((int) $wallet->total_quota_loaded, 0, ',', ' ') }} XAF</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-title">Quota utilise</div>
            <div class="stat-value">{{ number_format((int) $wallet->total_quota_used, 0, ',', ' ') }} XAF</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-title">Retirable</div>
            <div class="stat-value">{{ number_format((int) $availableWithdrawalBalance, 0, ',', ' ') }} XAF</div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-5">
        <div class="panel-card">
            <h4>Nouvelle recharge</h4>
            <form method="POST" action="{{ route('dashboard.quota_topups.store') }}" class="mt-3">
                @csrf
                <label class="form-label">Montant</label>
                <input type="number" name="amount" class="form-control mb-3" min="100" required placeholder="Ex: 5000">

                <label class="form-label">Methode de paiement</label>
                <select name="method" class="form-control mb-3" required>
                    @foreach($methods as $method)
                        <option value="{{ $method }}">{{ $method }}</option>
                    @endforeach
                </select>

                <label class="form-label">Numero de paiement</label>
                <input type="text" name="phone" class="form-control mb-3" placeholder="Ex: 690000000">

                <label class="form-label">Reference paiement</label>
                <input type="text" name="external_reference" class="form-control mb-3" placeholder="Transaction Mobile Money ou depot">

                <label class="form-label">Note</label>
                <textarea name="client_note" class="form-control mb-3" rows="3" placeholder="Details utiles pour l'equipe"></textarea>

                <button class="sky-btn w-100">Envoyer la demande</button>
            </form>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="panel-card">
            <h4>Mes demandes de recharge</h4>
            <div class="table-responsive mt-3">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Reference</th>
                            <th>Montant</th>
                            <th>Methode</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topups as $topup)
                            <tr>
                                <td>{{ $topup->created_at }}</td>
                                <td>{{ $topup->reference }}</td>
                                <td>{{ number_format((int) $topup->amount, 0, ',', ' ') }} XAF</td>
                                <td>{{ $topup->method }}</td>
                                <td>
                                    @if($topup->status === 'confirmed')
                                        <span class="badge text-bg-success">Validee</span>
                                    @elseif($topup->status === 'rejected')
                                        <span class="badge text-bg-danger">Refusee</span>
                                    @else
                                        <span class="badge text-bg-warning">En attente</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">Aucune demande.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $topups->links() }}
        </div>
    </div>
</div>

<div class="row g-4 mt-1">
    <div class="col-lg-5">
        <div class="panel-card">
            <h4>Quota necessaire pour vendre</h4>
            <p class="text-muted">Taux actuel: {{ $quotaRate }}%. Solde minimal conserve: {{ number_format((int) $minimumBalance, 0, ',', ' ') }} XAF.</p>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Forfait</th>
                            <th>Prix</th>
                            <th>Quota requis</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($plans as $plan)
                            <tr>
                                <td>{{ $plan->name }}</td>
                                <td>{{ number_format((int) $plan->price, 0, ',', ' ') }} XAF</td>
                                <td>{{ number_format((int) ceil($plan->price * $quotaRate / 100), 0, ',', ' ') }} XAF</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted py-4">Aucun forfait actif.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="panel-card">
            <h4>Commissions consommees</h4>
            <div class="table-responsive mt-3">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Commande</th>
                            <th>Commission</th>
                            <th>Solde avant</th>
                            <th>Solde apres</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($commissions as $commission)
                            <tr>
                                <td>{{ $commission->created_at }}</td>
                                <td>{{ optional($commission->order)->reference ?: '-' }}</td>
                                <td>{{ number_format((int) $commission->amount, 0, ',', ' ') }} XAF</td>
                                <td>{{ number_format((int) $commission->balance_before, 0, ',', ' ') }} XAF</td>
                                <td>{{ number_format((int) $commission->balance_after, 0, ',', ' ') }} XAF</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">Aucune commission consommee.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $commissions->links() }}
        </div>
    </div>
</div>
@endsection
