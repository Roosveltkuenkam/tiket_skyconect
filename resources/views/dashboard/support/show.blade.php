@extends('layouts.admin')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <span class="eyebrow"><span class="eyebrow-dot"></span> Ticket support</span>
        <h3>{{ $ticket->subject }}</h3>
        <p>Statut: {{ $ticket->status }} · Priorité: {{ $ticket->priority }}</p>
    </div>
    <a href="{{ route('dashboard.support.index') }}" class="btn btn-outline-primary">Retour</a>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="row g-4">
    <div class="col-lg-8">
        <div class="panel-card mb-4">
            <h4>Historique</h4>
            @foreach($ticket->messages->where('is_internal', false) as $message)
                <div class="border rounded p-3 mb-3">
                    <div class="d-flex justify-content-between">
                        <strong>{{ $message->type === 'client' ? 'Vous' : 'Equipe SkyConnect' }}</strong>
                        <small>{{ $message->created_at }}</small>
                    </div>
                    <p class="mb-0 mt-2">{{ $message->message }}</p>
                </div>
            @endforeach
        </div>

        @if($ticket->status !== 'resolved')
            <div class="panel-card">
                <h4>Répondre</h4>
                <form method="POST" action="{{ route('dashboard.support.reply', $ticket) }}">
                    @csrf
                    <textarea name="message" class="form-control" rows="4" required>{{ old('message') }}</textarea>
                    <button class="sky-btn mt-3">Envoyer</button>
                </form>
            </div>
        @endif
    </div>

    <div class="col-lg-4">
        <div class="panel-card">
            <h4>Informations</h4>
            <p><strong>Commande:</strong> {{ optional($ticket->order)->reference ?: '-' }}</p>
            <p><strong>Paiement:</strong> {{ $ticket->payment_id ? '#' . $ticket->payment_id : '-' }}</p>
            <p><strong>Créé le:</strong> {{ $ticket->created_at }}</p>
            <p><strong>Mis à jour:</strong> {{ $ticket->updated_at }}</p>
        </div>
    </div>
</div>
@endsection
