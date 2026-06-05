@extends('layouts.admin')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <span class="eyebrow"><span class="eyebrow-dot"></span> Litiges</span>
        <h3>Remboursements</h3>
        <p>Suivez les demandes, validations, traitements et responsables admin.</p>
    </div>
    @if(auth()->user()->canAccessBackOffice('refunds.export'))
        <a href="{{ route('admin.refunds.export', request()->query()) }}" class="btn btn-outline-primary">
            <i class="bi bi-download"></i> Export CSV
        </a>
    @endif
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="panel-card mb-4">
    <form method="GET" action="{{ route('admin.refunds.index') }}" class="row g-3">
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
            <label class="form-label">Commande</label>
            <input type="text" name="reference" class="form-control" value="{{ request('reference') }}" placeholder="SKY-...">
        </div>
        <div class="col-md-3">
            <label class="form-label">Client</label>
            <input type="text" name="client" class="form-control" value="{{ request('client') }}" placeholder="Nom, email, commerce">
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
            <a href="{{ route('admin.refunds.index') }}" class="btn btn-outline-primary w-100">Reinitialiser</a>
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
                    <th>Client</th>
                    <th>Montant</th>
                    <th>Statut</th>
                    <th>Demande par</th>
                    <th>Valide par</th>
                    <th>Traite par</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($refunds as $refund)
                    <tr>
                        <td>{{ $refund->created_at }}</td>
                        <td>{{ optional($refund->order)->reference ?: '-' }}</td>
                        <td>
                            <strong>{{ optional($refund->client)->name ?: '-' }}</strong><br>
                            <small>{{ optional($refund->client)->business_name ?: optional($refund->client)->email }}</small>
                        </td>
                        <td><strong>{{ number_format($refund->amount, 0, ',', ' ') }} XAF</strong></td>
                        <td>@include('admin.refunds.partials.status-badge', ['status' => $refund->status])</td>
                        <td>{{ optional($refund->requester)->name ?: '-' }}</td>
                        <td>{{ optional($refund->approver)->name ?: '-' }}</td>
                        <td>{{ optional($refund->processor)->name ?: '-' }}</td>
                        <td>
                            <a href="{{ route('admin.refunds.show', $refund) }}" class="btn btn-sm btn-outline-primary">Details</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">Aucun remboursement trouve.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $refunds->links() }}
</div>
@endsection
