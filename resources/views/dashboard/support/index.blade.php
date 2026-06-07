@extends('layouts.admin')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <span class="eyebrow"><span class="eyebrow-dot"></span> Assistance</span>
        <h3>Support</h3>
        <p>Contactez l'equipe SkyConnect et suivez vos demandes.</p>
    </div>
    <a href="{{ route('dashboard.support.create') }}" class="sky-btn">Nouveau ticket</a>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="panel-card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Sujet</th>
                    <th>Statut</th>
                    <th>Priorite</th>
                    <th>Commande</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tickets as $ticket)
                    <tr>
                        <td>{{ $ticket->created_at }}</td>
                        <td><strong>{{ $ticket->subject }}</strong></td>
                        <td>{{ $ticket->status }}</td>
                        <td>{{ $ticket->priority }}</td>
                        <td>{{ optional($ticket->order)->reference ?: '-' }}</td>
                        <td><a href="{{ route('dashboard.support.show', $ticket) }}" class="btn btn-sm btn-outline-primary">Voir</a></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">Aucun ticket support.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $tickets->links() }}
</div>
@endsection
