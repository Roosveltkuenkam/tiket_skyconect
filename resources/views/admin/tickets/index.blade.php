@extends('layouts.admin')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <span class="eyebrow"><span class="eyebrow-dot"></span> Stock tickets global</span>
        <h3>Gestion des tickets</h3>
        <p>Surveillez les tickets importes, vendus, disponibles ou desactives sur toute la plateforme.</p>
    </div>

    <div class="d-flex gap-2">
        <a href="{{ route(($routeArea ?? 'admin') . '.tickets.export', request()->query()) }}" class="btn btn-outline-primary">
            <i class="bi bi-download"></i> Export CSV
        </a>

        @if(($routeArea ?? 'admin') === 'dashboard' || auth()->user()->canAccessBackOffice('tickets.manage'))
            <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#clearTicketsModal">
                <i class="bi bi-trash3"></i> Vider
            </button>
            <a href="{{ route(($routeArea ?? 'admin') . '.tickets.import.create') }}" class="sky-btn">
                <i class="bi bi-upload"></i> Import CSV
            </a>
        @endif
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="panel-card mb-4">
    <form method="GET" action="{{ route(($routeArea ?? 'admin') . '.tickets.index') }}" class="row g-3">
        @if(($routeArea ?? 'admin') === 'admin')
            <div class="col-md-3">
                <label class="form-label">Client</label>
                <select name="client_id" class="form-control">
                    <option value="">Tous les clients</option>
                    @foreach($clients as $client)
                        <option value="{{ $client->id }}" {{ request('client_id') == $client->id ? 'selected' : '' }}>
                            {{ $client->name }} - {{ $client->business_name ?: $client->email }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endif

        <div class="col-md-3">
            <label class="form-label">Routeur</label>
            <select name="router_id" class="form-control">
                <option value="">Tous les routeurs</option>
                @foreach($routers as $router)
                    <option value="{{ $router->id }}" {{ request('router_id') == $router->id ? 'selected' : '' }}>
                        {{ $router->name }}
                        @if(($routeArea ?? 'admin') === 'admin')
                            - {{ $router->user->name ?? 'Sans client' }}
                        @endif
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-3">
            <label class="form-label">Forfait</label>
            <select name="plan_id" class="form-control">
                <option value="">Tous les forfaits</option>
                @foreach($plans as $plan)
                    <option value="{{ $plan->id }}" {{ request('plan_id') == $plan->id ? 'selected' : '' }}>
                        {{ $plan->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-2">
            <label class="form-label">Statut</label>
            <select name="status" class="form-control">
                <option value="">Tous</option>
                <option value="available" {{ request('status') === 'available' ? 'selected' : '' }}>Disponible</option>
                <option value="sold" {{ request('status') === 'sold' ? 'selected' : '' }}>Vendu</option>
                <option value="disabled" {{ request('status') === 'disabled' ? 'selected' : '' }}>Desactive</option>
                <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expire</option>
            </select>
        </div>

        <div class="col-md-1 d-flex align-items-end">
            <button class="sky-btn w-100">OK</button>
        </div>
    </form>
</div>

<div class="panel-card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    @if(($routeArea ?? 'admin') === 'admin')
                        <th>Client</th>
                    @endif
                    <th>Routeur</th>
                    <th>Username</th>
                    <th>Password</th>
                    <th>Forfait</th>
                    <th>Profil</th>
                    <th>Statut</th>
                    <th>Commande</th>
                    <th>Vendu le</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tickets as $ticket)
                    <tr>
                        @if(($routeArea ?? 'admin') === 'admin')
                            <td>
                                <strong>{{ optional(optional(optional($ticket->plan)->router)->user)->name ?? '-' }}</strong><br>
                                <small>{{ optional(optional(optional($ticket->plan)->router)->user)->business_name ?? '' }}</small>
                            </td>
                        @endif
                        <td>{{ optional(optional($ticket->plan)->router)->name ?? '-' }}</td>
                        <td><strong>{{ $ticket->username }}</strong></td>
                        <td>
                            @if($canViewPasswords)
                                {{ $ticket->password }}
                            @else
                                <span class="badge text-bg-secondary">Masque</span>
                            @endif
                        </td>
                        <td>{{ $ticket->plan->name ?? '-' }}</td>
                        <td>{{ $ticket->profile ?: '-' }}</td>
                        <td>
                            @if($ticket->status === 'available')
                                <span class="badge text-bg-success">Disponible</span>
                            @elseif($ticket->status === 'sold')
                                <span class="badge text-bg-primary">Vendu</span>
                            @elseif($ticket->status === 'disabled')
                                <span class="badge text-bg-danger">Desactive</span>
                            @else
                                <span class="badge text-bg-secondary">{{ $ticket->status }}</span>
                            @endif
                        </td>
                        <td>
                            @if($ticket->order)
                                <a href="{{ route('orders.show', $ticket->order) }}" class="btn btn-sm btn-outline-primary">
                                    {{ $ticket->order->reference }}
                                </a>
                            @else
                                -
                            @endif
                        </td>
                        <td>{{ $ticket->sold_at ?? '-' }}</td>
                        <td class="d-flex gap-1">
                            @if((($routeArea ?? 'admin') === 'dashboard' || auth()->user()->canAccessBackOffice('tickets.manage')) && $ticket->status === 'available')
                                <form method="POST" action="{{ route(($routeArea ?? 'admin') . '.tickets.disable', $ticket) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button class="btn btn-sm btn-outline-warning">Desactiver</button>
                                </form>
                            @endif
                            @if((($routeArea ?? 'admin') === 'dashboard' || auth()->user()->canAccessBackOffice('tickets.manage')) && $ticket->status === 'disabled')
                                <form method="POST" action="{{ route(($routeArea ?? 'admin') . '.tickets.enable', $ticket) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button class="btn btn-sm btn-outline-success">Reactiver</button>
                                </form>
                            @endif
                            @if((($routeArea ?? 'admin') === 'dashboard' || auth()->user()->canAccessBackOffice('tickets.manage')) && $ticket->status !== 'sold')
                                <form method="POST" action="{{ route(($routeArea ?? 'admin') . '.tickets.destroy', $ticket) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">Supprimer</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ ($routeArea ?? 'admin') === 'admin' ? 10 : 9 }}" class="text-center text-muted py-4">
                            Aucun ticket trouve.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $tickets->links() }}
</div>

@if(($routeArea ?? 'admin') === 'dashboard' || auth()->user()->canAccessBackOffice('tickets.manage'))
<div class="modal fade" id="clearTicketsModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:22px;">
            <div class="modal-header">
                <h5 class="modal-title">Vider les tickets disponibles</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route(($routeArea ?? 'admin') . '.tickets.clear') }}">
                @csrf
                @method('DELETE')
                <div class="modal-body">
                    <p>Cette action supprime uniquement les tickets disponibles. Les tickets deja vendus ne seront pas supprimes.</p>
                    <label class="form-label">Par forfait</label>
                    <select name="plan_id" class="form-control">
                        <option value="">Tous les forfaits</option>
                        @foreach($plans as $plan)
                            <option value="{{ $plan->id }}">{{ $plan->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-danger">Vider</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection
