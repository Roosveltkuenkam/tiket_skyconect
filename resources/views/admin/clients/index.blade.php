@extends('layouts.admin')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <span class="eyebrow"><span class="eyebrow-dot"></span> Proprietaires WiFi</span>
        <h3>Gestion des clients</h3>
        <p>Recherchez et administrez les comptes proprietaires inscrits sur SkyConnect.</p>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="panel-card mb-4">
    <form method="GET" action="{{ route('admin.clients.index') }}" class="row g-3">
        <div class="col-md-7">
            <label class="form-label">Recherche</label>
            <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Nom, email, telephone, entreprise">
        </div>
        <div class="col-md-3">
            <label class="form-label">Statut</label>
            <select name="status" class="form-control">
                <option value="">Tous</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Actif</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactif</option>
            </select>
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
                    <th>Client</th>
                    <th>Entreprise</th>
                    <th>Telephone</th>
                    <th>Routeurs</th>
                    <th>Quota</th>
                    <th>Statut</th>
                    <th>Inscription</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($clients as $client)
                    <tr>
                        <td>
                            <strong>{{ $client->name }}</strong><br>
                            <small>{{ $client->email }}</small>
                        </td>
                        <td>{{ $client->business_name ?: '-' }}</td>
                        <td>{{ $client->phone ?: '-' }}</td>
                        <td><span class="badge text-bg-primary">{{ $client->routers_count }}</span></td>
                        <td>{{ number_format((int) optional($client->wallet)->quota_balance, 0, ',', ' ') }} XAF</td>
                        <td>
                            @if($client->is_active)
                                <span class="badge text-bg-success">Actif</span>
                            @else
                                <span class="badge text-bg-secondary">Inactif</span>
                            @endif
                        </td>
                        <td>{{ $client->created_at }}</td>
                        <td>
                            <a href="{{ route('admin.clients.show', $client) }}" class="btn btn-sm btn-outline-primary">Details</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">Aucun client trouve.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $clients->links() }}
</div>
@endsection
