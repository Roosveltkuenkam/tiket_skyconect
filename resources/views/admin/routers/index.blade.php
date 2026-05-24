@extends('layouts.admin')

@section('content')

<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h3>Mes routeurs</h3>
        <p>{{ $routers->count() }} routeur(s) enregistré(s)</p>
    </div>

    <a href="{{ route('admin.routers.create') }}" class="sky-btn">
        + Ajouter un routeur
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

<div class="alert alert-info">
    Accès distant à vos routeurs ? SkyConnect vous permettra plus tard de gérer vos routeurs MikroTik à distance.
</div>

<div class="panel-card">

    <table class="table mt-3">
        <thead>
            <tr>
                <th>Routeur</th>
                <th>DNS</th>
                <th>Assistance</th>
                <th>Plateforme</th>
                <th>Statut</th>
                <th>Intégration</th>
                <th>Actions</th>
            </tr>
        </thead>

        <tbody>
            @foreach($routers as $router)
                <tr>
                    <td>
                        <strong>{{ $router->name }}</strong><br>
                        <small>{{ $router->location }}</small><br>
                        <small>{{ $router->integration_key }}</small>
                    </td>

                    <td>{{ $router->dns ?? '-' }}</td>
                    <td>{{ $router->assistance_phone ?? '-' }}</td>

                    <td>
                        <span class="badge bg-info text-dark">
                            {{ $router->platform }}
                        </span>
                    </td>

                    <td>
                        @if($router->status === 'active')
                            <span class="badge bg-success">Actif</span>
                        @else
                            <span class="badge bg-danger">Inactif</span>
                        @endif
                    </td>

                    <td>
                        <button class="btn btn-sm btn-outline-info">API</button>
                        <button class="btn btn-sm btn-outline-primary">Lien</button>
                    </td>

                    <td>
                        <button class="btn btn-sm btn-outline-warning">Modifier</button>
                        <button class="btn btn-sm btn-outline-danger">Supprimer</button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

</div>

@endsection