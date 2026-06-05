@extends('layouts.admin')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <span class="eyebrow"><span class="eyebrow-dot"></span> Assistance</span>
        <h3>Support client</h3>
        <p>Suivez les demandes, priorites, commandes et paiements associes.</p>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="panel-card mb-4">
    <form method="GET" action="{{ route('admin.support.index') }}" class="row g-3">
        <div class="col-md-3">
            <label class="form-label">Client</label>
            <input type="text" name="client" class="form-control" value="{{ request('client') }}" placeholder="Nom, email, commerce">
        </div>
        <div class="col-md-3">
            <label class="form-label">Statut</label>
            <select name="status" class="form-control">
                <option value="">Tous</option>
                @foreach($statuses as $value => $label)
                    <option value="{{ $value }}" {{ request('status') === $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Priorite</label>
            <select name="priority" class="form-control">
                <option value="">Toutes</option>
                @foreach($priorities as $value => $label)
                    <option value="{{ $value }}" {{ request('priority') === $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-1 d-flex align-items-end">
            <a href="{{ route('admin.support.index') }}" class="btn btn-outline-primary w-100">Reset</a>
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
                    <th>Sujet</th>
                    <th>Client</th>
                    <th>Statut</th>
                    <th>Priorite</th>
                    <th>Commande</th>
                    <th>Paiement</th>
                    <th>Assigne</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tickets as $ticket)
                    <tr>
                        <td>{{ $ticket->created_at }}</td>
                        <td><strong>{{ $ticket->subject }}</strong></td>
                        <td>{{ optional($ticket->user)->name ?: '-' }}<br><small>{{ optional($ticket->user)->business_name ?: optional($ticket->user)->email }}</small></td>
                        <td>{{ $statuses[$ticket->status] ?? $ticket->status }}</td>
                        <td>{{ $priorities[$ticket->priority] ?? $ticket->priority }}</td>
                        <td>{{ optional($ticket->order)->reference ?: '-' }}</td>
                        <td>{{ $ticket->payment_id ? '#' . $ticket->payment_id : '-' }}</td>
                        <td>{{ optional($ticket->assignee)->name ?: '-' }}</td>
                        <td><a href="{{ route('admin.support.show', $ticket) }}" class="btn btn-sm btn-outline-primary">Details</a></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">Aucun ticket support.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $tickets->links() }}
</div>
@endsection
