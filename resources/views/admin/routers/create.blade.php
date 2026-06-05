@extends('layouts.admin')

@section('content')
<div class="page-header">
    <span class="eyebrow"><span class="eyebrow-dot"></span> Nouveau hotspot</span>
    <h3>Ajouter un routeur</h3>
    <p>Ce routeur sera rattache a votre espace et servira a organiser vos plans et tickets.</p>
</div>

<div class="panel-card">
    <form method="POST" action="{{ route(($routeArea ?? 'admin') . '.routers.store') }}">
        @csrf

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Nom du routeur</label>
                <input type="text" name="name" class="form-control" placeholder="SkyConnect Hotspot" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Localisation</label>
                <input type="text" name="location" class="form-control" placeholder="Snack, hotel, campus...">
            </div>
            <div class="col-md-6">
                <label class="form-label">Adresse IP / DNS</label>
                <input type="text" name="dns" class="form-control" placeholder="skyconnect.lan">
            </div>
            <div class="col-md-6">
                <label class="form-label">Telephone assistance</label>
                <input type="text" name="assistance_phone" class="form-control" placeholder="+237 690 000 000">
            </div>
            <div class="col-md-6">
                <label class="form-label">Plateforme</label>
                <select name="platform" class="form-control">
                    <option value="Mikrotik">MikroTik</option>
                    <option value="Mikhmon">Mikhmon</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Statut</label>
                <select name="status" class="form-control">
                    <option value="active">Actif</option>
                    <option value="inactive">Inactif</option>
                </select>
            </div>
        </div>

        <div class="mt-4 d-flex gap-2">
            <button type="submit" class="sky-btn">Enregistrer le routeur</button>
            <a href="{{ route(($routeArea ?? 'admin') . '.routers.index') }}" class="btn btn-outline-primary">Retour</a>
        </div>
    </form>
</div>
@endsection
