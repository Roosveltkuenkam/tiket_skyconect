@extends('layouts.admin')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <span class="eyebrow"><span class="eyebrow-dot"></span> Quota</span>
        <h3>Recharges quota</h3>
        <p>Validez les paiements hors ligne et auditez les demandes de recharge.</p>
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
    <form method="GET" action="{{ route('admin.quota_topups.index') }}" class="row g-3">
        <div class="col-md-3">
            <label class="form-label">Recherche</label>
            <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Reference, client, transaction">
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
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>En attente</option>
                <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Validee</option>
                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Refusee</option>
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
                    <th>Montant</th>
                    <th>Methode</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($topups as $topup)
                    <tr>
                        <td>{{ $topup->created_at }}</td>
                        <td>
                            <strong>{{ optional($topup->user)->name }}</strong><br>
                            <small>{{ optional($topup->user)->email }}</small>
                        </td>
                        <td>
                            {{ $topup->reference }}<br>
                            <small>{{ $topup->external_reference ?: '-' }}</small>
                        </td>
                        <td>{{ number_format((int) $topup->amount, 0, ',', ' ') }} XAF</td>
                        <td>{{ $topup->method }}<br><small>{{ $topup->phone ?: '-' }}</small></td>
                        <td>
                            @if($topup->status === 'confirmed')
                                <span class="badge text-bg-success">Validee</span>
                            @elseif($topup->status === 'rejected')
                                <span class="badge text-bg-danger">Refusee</span>
                            @else
                                <span class="badge text-bg-warning">En attente</span>
                            @endif
                        </td>
                        <td style="min-width:260px;">
                            @if($topup->isPending() && auth()->user()->canAccessBackOffice('quota_topups.manage'))
                                <form method="POST" action="{{ route('admin.quota_topups.approve', $topup) }}" class="mb-2">
                                    @csrf
                                    @method('PATCH')
                                    <input type="text" name="admin_note" class="form-control form-control-sm mb-1" placeholder="Note validation">
                                    <button class="btn btn-sm btn-outline-success w-100">Valider et crediter</button>
                                </form>
                                <form method="POST" action="{{ route('admin.quota_topups.reject', $topup) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="text" name="admin_note" class="form-control form-control-sm mb-1" required placeholder="Motif refus">
                                    <button class="btn btn-sm btn-outline-danger w-100">Refuser</button>
                                </form>
                            @else
                                <small class="text-muted">
                                    @if($topup->status === 'confirmed')
                                        Validee par {{ optional($topup->confirmer)->name ?: '-' }}
                                    @elseif($topup->status === 'rejected')
                                        Refusee par {{ optional($topup->rejecter)->name ?: '-' }}
                                    @else
                                        -
                                    @endif
                                </small>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">Aucune recharge trouvee.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $topups->links() }}
</div>
@endsection
