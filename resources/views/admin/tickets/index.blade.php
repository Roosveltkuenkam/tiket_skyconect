@extends('layouts.admin')

@section('content')

<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h3>Tickets</h3>
        <p>Gestion des tickets importés depuis Mikhmon</p>
    </div>

    <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#clearTicketsModal">
        Vider des tickets
    </button>

    <a href="{{ route('tickets.import.create') }}" class="sky-btn">
        + Importer des tickets
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="panel-card mb-4">
    <form method="GET" action="{{ route('admin.tickets.index') }}" class="row">
        <div class="col-md-5">
            <label>Forfait</label>
            <select name="plan_id" class="form-control">
                <option value="">Tous les forfaits</option>
                @foreach($plans as $plan)
                    <option value="{{ $plan->id }}" {{ request('plan_id') == $plan->id ? 'selected' : '' }}>
                        {{ $plan->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-5">
            <label>Statut</label>
            <select name="status" class="form-control">
                <option value="">Tous les statuts</option>
                <option value="available" {{ request('status') == 'available' ? 'selected' : '' }}>Disponible</option>
                <option value="sold" {{ request('status') == 'sold' ? 'selected' : '' }}>Vendu</option>
                <option value="disabled" {{ request('status') == 'disabled' ? 'selected' : '' }}>Désactivé</option>
                <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expiré</option>
            </select>
        </div>

        <div class="col-md-2 d-flex align-items-end">
            <button class="sky-btn w-100">Filtrer</button>
        </div>
    </form>
</div>

<div class="panel-card">
    <table class="table">
        <thead>
            <tr>
                <th>Username</th>
                <th>Password</th>
                <th>Forfait</th>
                <th>Profil</th>
                <th>Statut</th>
                <th>Vendu le</th>
                <th>Actions</th>
            </tr>
        </thead>

        <tbody>
            @foreach($tickets as $ticket)
                <tr>
                    <td><strong>{{ $ticket->username }}</strong></td>
                    <td>{{ $ticket->password }}</td>
                    <td>{{ $ticket->plan->name ?? '-' }}</td>
                    <td>{{ $ticket->profile }}</td>

                    <td>
                        @if($ticket->status === 'available')
                            <span class="badge bg-success">Disponible</span>
                        @elseif($ticket->status === 'sold')
                            <span class="badge bg-primary">Vendu</span>
                        @elseif($ticket->status === 'disabled')
                            <span class="badge bg-danger">Désactivé</span>
                        @else
                            <span class="badge bg-secondary">{{ $ticket->status }}</span>
                        @endif
                    </td>

                    <td>{{ $ticket->sold_at ?? '-' }}</td>

                    <td class="d-flex gap-1">
                        @if($ticket->status === 'available')
                            <form method="POST" action="{{ route('admin.tickets.disable', $ticket) }}">
                                @csrf
                                @method('PATCH')
                                <button class="btn btn-sm btn-outline-warning">Désactiver</button>
                            </form>
                        @endif

                        @if($ticket->status === 'disabled')
                            <form method="POST" action="{{ route('admin.tickets.enable', $ticket) }}">
                                @csrf
                                @method('PATCH')
                                <button class="btn btn-sm btn-outline-success">Réactiver</button>
                            </form>
                        @endif

                        @if($ticket->status !== 'sold')
                            <form method="POST" action="{{ route('admin.tickets.destroy', $ticket) }}">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">
                                    Supprimer
                                </button>
                            </form>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{ $tickets->links() }}
</div>

<div class="modal fade" id="clearTicketsModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:22px;">
            <div class="modal-header" style="background:#ffe4ec;">
                <h5 class="modal-title" style="color:#ff4f8b;">
                    Vider les tickets disponibles
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form method="POST" action="{{ route('admin.tickets.clear') }}">
                @csrf
                @method('DELETE')

                <div class="modal-body">
                    <p>
                        Cette action supprime uniquement les tickets disponibles.
                        Les tickets déjà vendus ne seront pas supprimés.
                    </p>

                    <label class="form-label">Par forfait</label>
                    <select name="plan_id" class="form-control">
                        <option value="">Tous les forfaits</option>
                        @foreach($plans as $plan)
                            <option value="{{ $plan->id }}">
                                {{ $plan->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                        Annuler
                    </button>

                    <button type="submit" class="btn btn-danger">
                        Vider
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection