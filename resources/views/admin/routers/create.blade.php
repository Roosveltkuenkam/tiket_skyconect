@extends('layouts.admin')

@section('content')
@php
    $router = $router ?? null;
    $isEdit = (bool) $router;
@endphp
<div class="page-header">
    <span class="eyebrow"><span class="eyebrow-dot"></span> Nouveau hotspot</span>
    <h3>{{ $isEdit ? 'Modifier le routeur' : 'Ajouter un routeur' }}</h3>
    <p>{{ $isEdit ? 'Mettez a jour les informations du hotspot.' : 'Ce routeur sera rattache a votre espace et servira a organiser vos plans et tickets.' }}</p>
</div>

<div class="panel-card">
    <form method="POST" action="{{ $isEdit ? route(($routeArea ?? 'admin') . '.routers.update', $router) : route(($routeArea ?? 'admin') . '.routers.store') }}">
        @csrf
        @if($isEdit)
            @method('PUT')
        @endif

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Nom du routeur</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', optional($router)->name) }}" placeholder="SkyConnect Hotspot" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Localisation</label>
                <input type="text" name="location" class="form-control" value="{{ old('location', optional($router)->location) }}" placeholder="Snack, hotel, campus...">
            </div>
            <div class="col-md-6">
                <label class="form-label">Adresse IP / DNS</label>
                <input type="text" name="dns" class="form-control" value="{{ old('dns', optional($router)->dns) }}" placeholder="skyconnect.lan">
            </div>
            <div class="col-md-6">
                <label class="form-label">Telephone assistance</label>
                <input type="text" name="assistance_phone" class="form-control" value="{{ old('assistance_phone', optional($router)->assistance_phone) }}" placeholder="+237 690 000 000">
            </div>
            <div class="col-md-6">
                <label class="form-label">Plateforme</label>
                <select name="platform" class="form-control">
                    <option value="Mikrotik" {{ old('platform', optional($router)->platform ?: 'Mikrotik') === 'Mikrotik' ? 'selected' : '' }}>MikroTik</option>
                    <option value="Mikhmon" {{ old('platform', optional($router)->platform) === 'Mikhmon' ? 'selected' : '' }}>Mikhmon</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Statut</label>
                <select name="status" class="form-control">
                    <option value="active" {{ old('status', optional($router)->status ?: 'active') === 'active' ? 'selected' : '' }}>Actif</option>
                    <option value="inactive" {{ old('status', optional($router)->status) === 'inactive' ? 'selected' : '' }}>Inactif</option>
                </select>
            </div>
        </div>

        <div class="mt-4 d-flex gap-2">
            <button type="submit" class="sky-btn">{{ $isEdit ? 'Mettre a jour' : 'Enregistrer le routeur' }}</button>
            <a href="{{ route(($routeArea ?? 'admin') . '.routers.index') }}" class="btn btn-outline-primary">Retour</a>
        </div>
    </form>
</div>
@endsection
