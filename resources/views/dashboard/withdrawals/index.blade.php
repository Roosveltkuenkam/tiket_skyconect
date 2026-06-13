@extends('layouts.admin')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <span class="eyebrow"><span class="eyebrow-dot"></span> Revenus</span>
        <h3>Retraits</h3>
        <p>Demandez le versement de votre solde disponible.</p>
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
            <div class="stat-title">Ventes totales</div>
            <div class="stat-value">{{ number_format((int) $wallet->total_sales_amount, 0, ',', ' ') }} XAF</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-title">Commission quota</div>
            <div class="stat-value">{{ number_format((int) $wallet->total_quota_used, 0, ',', ' ') }} XAF</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-title">Retraits attente</div>
            <div class="stat-value">{{ number_format((int) $wallet->pending_withdrawal_amount, 0, ',', ' ') }} XAF</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-title">Disponible</div>
            <div class="stat-value">{{ number_format((int) $availableBalance, 0, ',', ' ') }} XAF</div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-5">
        <div class="panel-card">
            <h4>Nouveau retrait</h4>
            @if(! $enabled)
                <div class="alert alert-warning mt-3">Les retraits sont temporairement desactives.</div>
            @endif
            <form method="POST" action="{{ route('dashboard.withdrawals.store') }}" class="mt-3">
                @csrf
                <label class="form-label">Montant brut a retirer</label>
                <input type="number" name="amount" class="form-control mb-2" min="1" max="{{ $availableBalance }}" required placeholder="Ex: 90000" {{ ! $enabled ? 'disabled' : '' }}>
                <small class="text-muted d-block mb-3">
                    Minimum: {{ number_format((int) $minimumAmount, 0, ',', ' ') }} XAF
                    @if($maximumAmount)
                        · Maximum: {{ number_format((int) $maximumAmount, 0, ',', ' ') }} XAF
                    @endif
                </small>

                <label class="form-label">Methode</label>
                <select name="method" class="form-control mb-3" required {{ ! $enabled ? 'disabled' : '' }}>
                    @foreach($methods as $method)
                        <option value="{{ $method }}">{{ $method }}</option>
                    @endforeach
                </select>

                <label class="form-label">Nom du beneficiaire</label>
                <input type="text" name="account_name" class="form-control mb-3" placeholder="Nom du compte">

                <label class="form-label">Numero / compte de reception</label>
                <input type="text" name="account_phone" class="form-control mb-3" required placeholder="Telephone ou compte bancaire" {{ ! $enabled ? 'disabled' : '' }}>

                <label class="form-label">Note</label>
                <textarea name="client_note" class="form-control mb-3" rows="3" placeholder="Precision utile"></textarea>

                <button class="sky-btn w-100" {{ ! $enabled ? 'disabled' : '' }}>Demander le retrait</button>
            </form>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="panel-card">
            <h4>Historique des retraits</h4>
            <div class="table-responsive mt-3">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Reference</th>
                            <th>Brut</th>
                            <th>Frais</th>
                            <th>Envoye</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($withdrawals as $withdrawal)
                            <tr>
                                <td>{{ $withdrawal->created_at }}</td>
                                <td>{{ $withdrawal->reference }}</td>
                                <td>{{ number_format((int) $withdrawal->amount_requested, 0, ',', ' ') }} XAF</td>
                                <td>{{ number_format((int) $withdrawal->fee_amount, 0, ',', ' ') }} XAF</td>
                                <td>{{ number_format((int) $withdrawal->amount_to_pay, 0, ',', ' ') }} XAF</td>
                                <td>
                                    @if($withdrawal->status === 'processed')
                                        <span class="badge text-bg-success">Traite</span>
                                    @elseif($withdrawal->status === 'rejected')
                                        <span class="badge text-bg-danger">Refuse</span>
                                    @elseif($withdrawal->status === 'approved')
                                        <span class="badge text-bg-primary">Valide</span>
                                    @else
                                        <span class="badge text-bg-warning">Demande</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">Aucun retrait.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $withdrawals->links() }}
        </div>
    </div>
</div>
@endsection
