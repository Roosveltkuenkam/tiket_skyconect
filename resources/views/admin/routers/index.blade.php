@extends('layouts.admin')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <span class="eyebrow"><span class="eyebrow-dot"></span> RouterOS ready</span>
        <h3>Routeurs</h3>
        <p>{{ $routers->count() }} routeur(s) enregistres</p>
    </div>

    <a href="{{ route(($routeArea ?? 'admin') . '.routers.create') }}" class="sky-btn">
        <i class="bi bi-plus-circle"></i> Ajouter un routeur
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="panel-card">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Gestion des routeurs</h4>
        <span class="badge text-bg-primary">MikroTik</span>
    </div>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Adresse IP / DNS</th>
                    <th>Assistance</th>
                    <th>Plateforme</th>
                    <th>Statut</th>
                    <th>Integration</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($routers as $router)
                    <tr>
                        <td>
                            <strong>{{ $router->name }}</strong><br>
                            <small>{{ $router->location ?: 'Emplacement non renseigne' }}</small>
                        </td>
                        <td>{{ $router->dns ?: '-' }}</td>
                        <td>{{ $router->assistance_phone ?: '-' }}</td>
                        <td><span class="badge text-bg-info">{{ $router->platform }}</span></td>
                        <td>
                            @if($router->status === 'active')
                                <span class="badge text-bg-success">En ligne</span>
                            @else
                                <span class="badge text-bg-danger">Hors ligne</span>
                            @endif
                        </td>
                        <td>
                            <code>
                                {{ $router->integration_key ? substr($router->integration_key, 0, 3) . str_repeat('*', max(strlen($router->integration_key) - 5, 3)) . substr($router->integration_key, -2) : '-' }}
                            </code>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary">Lien</button>
                            <button class="btn btn-sm btn-outline-secondary">API</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
