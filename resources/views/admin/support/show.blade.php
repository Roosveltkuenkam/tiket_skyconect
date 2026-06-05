@extends('layouts.admin')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <span class="eyebrow"><span class="eyebrow-dot"></span> Ticket support</span>
        <h3>{{ $ticket->subject }}</h3>
        <p>{{ optional($ticket->user)->name }} · {{ optional($ticket->user)->email }}</p>
    </div>
    <a href="{{ route('admin.support.index') }}" class="btn btn-outline-primary">Retour</a>
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

<div class="row g-4">
    <div class="col-lg-7">
        <div class="panel-card mb-4">
            <h4>Historique</h4>
            @foreach($ticket->messages as $message)
                <div class="border rounded p-3 mb-3 {{ $message->is_internal ? 'bg-warning-subtle' : '' }}">
                    <div class="d-flex justify-content-between">
                        <strong>{{ optional($message->user)->name ?: 'Systeme' }}</strong>
                        <small>{{ $message->created_at }}</small>
                    </div>
                    <div class="mb-2">
                        <span class="badge text-bg-{{ $message->is_internal ? 'warning' : ($message->type === 'client' ? 'primary' : 'success') }}">
                            {{ $message->is_internal ? 'Note interne' : ($message->type === 'client' ? 'Client' : 'Equipe SkyConnect') }}
                        </span>
                    </div>
                    <p class="mb-0">{{ $message->message }}</p>
                </div>
            @endforeach
        </div>

        @if(auth()->user()->canAccessBackOffice('support.manage'))
            <div class="panel-card">
                <h4>Ajouter un message</h4>
                <form method="POST" action="{{ route('admin.support.reply', $ticket) }}">
                    @csrf
                    <textarea name="message" class="form-control" rows="4" required>{{ old('message') }}</textarea>
                    <label class="form-check d-flex align-items-center gap-2 mt-3">
                        <input type="checkbox" name="is_internal" value="1" class="form-check-input">
                        <span>Note interne uniquement</span>
                    </label>
                    <button class="sky-btn mt-3">Ajouter</button>
                </form>
            </div>
        @endif
    </div>

    <div class="col-lg-5">
        <div class="panel-card">
            <h4>Parametres</h4>
            <form method="POST" action="{{ route('admin.support.update', $ticket) }}">
                @csrf
                @method('PATCH')
                <label class="form-label">Statut</label>
                <select name="status" class="form-control mb-3">
                    @foreach($statuses as $value => $label)
                        <option value="{{ $value }}" {{ $ticket->status === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                <label class="form-label">Priorite</label>
                <select name="priority" class="form-control mb-3">
                    @foreach($priorities as $value => $label)
                        <option value="{{ $value }}" {{ $ticket->priority === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                <label class="form-label">Assigne a</label>
                <select name="assigned_to" class="form-control mb-3">
                    <option value="">Non assigne</option>
                    @foreach($admins as $admin)
                        <option value="{{ $admin->id }}" {{ $ticket->assigned_to === $admin->id ? 'selected' : '' }}>{{ $admin->name }}</option>
                    @endforeach
                </select>
                <label class="form-label">Commande liee</label>
                <input type="number" name="order_id" class="form-control mb-3" value="{{ $ticket->order_id }}" placeholder="ID commande">
                <label class="form-label">Paiement lie</label>
                <input type="number" name="payment_id" class="form-control mb-3" value="{{ $ticket->payment_id }}" placeholder="ID paiement">
                @if(auth()->user()->canAccessBackOffice('support.manage'))
                    <button class="sky-btn w-100">Enregistrer</button>
                @endif
            </form>
        </div>

        <div class="panel-card mt-4">
            <h4>Liens</h4>
            <p><strong>Commande:</strong>
                @if($ticket->order)
                    <a href="{{ route('admin.orders.show', $ticket->order) }}">{{ $ticket->order->reference }}</a>
                @else
                    -
                @endif
            </p>
            <p><strong>Paiement:</strong>
                @if($ticket->payment)
                    <a href="{{ route('admin.payments.show', $ticket->payment) }}">#{{ $ticket->payment->id }}</a>
                @else
                    -
                @endif
            </p>
        </div>
    </div>
</div>
@endsection
