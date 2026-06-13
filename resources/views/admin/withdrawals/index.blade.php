@extends('layouts.admin')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <span class="eyebrow"><span class="eyebrow-dot"></span> Revenus clients</span>
        <h3>Retraits</h3>
        <p>Traitez les demandes de versement des proprietaires.</p>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

@if($errors->any())
    <div class="alert alert-danger">
        @foreach($errors->all() as $error)
            <div>{{ $error }}</div>
        @endforeach
    </div>
@endif

<div class="panel-card mb-4">
    <form method="GET" action="{{ route('admin.withdrawals.index') }}" class="row g-3">
        <div class="col-md-3">
            <label class="form-label">Recherche</label>
            <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Reference, client, compte">
        </div>
        <div class="col-md-2">
            <label class="form-label">Client</label>
            <select name="client_id" class="form-control">
                <option value="">Tous</option>
                @foreach($clients as $client)
                    <option value="{{ $client->id }}" {{ (string) request('client_id') === (string) $client->id ? 'selected' : '' }}>{{ $client->business_name ?: $client->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Statut</label>
            <select name="status" class="form-control">
                <option value="">Tous</option>
                <option value="requested" {{ request('status') === 'requested' ? 'selected' : '' }}>Demande</option>
                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Valide</option>
                <option value="processed" {{ request('status') === 'processed' ? 'selected' : '' }}>Traite</option>
                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Refuse</option>
                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Annule</option>
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
        <div class="col-md-1 d-flex align-items-end">
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
                    <th>Client</th>
                    <th>Reference</th>
                    <th>Montants</th>
                    <th>Reception</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($withdrawals as $withdrawal)
                    <tr>
                        <td>{{ $withdrawal->created_at }}</td>
                        <td>
                            <strong>{{ optional($withdrawal->user)->name }}</strong><br>
                            <small>{{ optional($withdrawal->user)->email }}</small>
                        </td>
                        <td>{{ $withdrawal->reference }}</td>
                        <td>
                            Brut: {{ number_format((int) $withdrawal->amount_requested, 0, ',', ' ') }} XAF<br>
                            Frais: {{ number_format((int) $withdrawal->fee_amount, 0, ',', ' ') }} XAF<br>
                            <strong>Envoyer: {{ number_format((int) $withdrawal->amount_to_pay, 0, ',', ' ') }} XAF</strong>
                        </td>
                        <td>
                            {{ $withdrawal->method }}<br>
                            <small>{{ $withdrawal->account_name ?: '-' }} · {{ $withdrawal->account_phone ?: '-' }}</small>
                        </td>
                        <td>
                            @if($withdrawal->status === 'processed')
                                <span class="badge text-bg-success">Traite</span>
                            @elseif($withdrawal->status === 'rejected')
                                <span class="badge text-bg-danger">Refuse</span>
                            @elseif($withdrawal->status === 'approved')
                                <span class="badge text-bg-primary">Valide</span>
                            @elseif($withdrawal->status === 'cancelled')
                                <span class="badge text-bg-secondary">Annule</span>
                            @else
                                <span class="badge text-bg-warning">Demande</span>
                            @endif
                        </td>
                        <td style="min-width:260px;">
                            @if($withdrawal->isRequested() && auth()->user()->canAccessBackOffice('withdrawals.manage'))
                                <form method="POST" action="{{ route('admin.withdrawals.approve', $withdrawal) }}" class="mb-2">
                                    @csrf
                                    @method('PATCH')
                                    <input type="text" name="admin_note" class="form-control form-control-sm mb-1" placeholder="Note validation">
                                    <button class="btn btn-sm btn-outline-primary w-100">Valider</button>
                                </form>
                                <form method="POST" action="{{ route('admin.withdrawals.reject', $withdrawal) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="text" name="admin_note" class="form-control form-control-sm mb-1" required placeholder="Motif refus">
                                    <button class="btn btn-sm btn-outline-danger w-100">Refuser</button>
                                </form>
                            @elseif($withdrawal->isApproved() && auth()->user()->canAccessBackOffice('withdrawals.manage'))
                                <form method="POST" action="{{ route('admin.withdrawals.process', $withdrawal) }}" class="mb-2">
                                    @csrf
                                    @method('PATCH')
                                    <input type="text" name="admin_note" class="form-control form-control-sm mb-1" placeholder="Reference versement / note">
                                    <button class="btn btn-sm btn-outline-success w-100">Marquer traite</button>
                                </form>
                                <form method="POST" action="{{ route('admin.withdrawals.reject', $withdrawal) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="text" name="admin_note" class="form-control form-control-sm mb-1" required placeholder="Motif refus">
                                    <button class="btn btn-sm btn-outline-danger w-100">Refuser</button>
                                </form>
                            @else
                                <small class="text-muted">
                                    @if($withdrawal->status === 'processed')
                                        Traite par {{ optional($withdrawal->processor)->name ?: '-' }}
                                    @elseif($withdrawal->status === 'rejected')
                                        Refuse par {{ optional($withdrawal->rejecter)->name ?: '-' }}
                                    @else
                                        -
                                    @endif
                                </small>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">Aucun retrait trouve.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $withdrawals->links() }}
</div>
@endsection
