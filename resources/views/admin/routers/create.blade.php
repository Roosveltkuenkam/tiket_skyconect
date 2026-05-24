@extends('layouts.admin')

@section('content')

<div class="page-header">
    <h3>Ajouter un routeur</h3>
    <p>Accueil / Routeurs / Ajouter</p>
</div>

<div class="panel-card">
    <form method="POST" action="{{ route('admin.routers.store') }}">
        @csrf

        <div class="mb-3">
            <label>Nom du routeur</label>
            <input type="text" name="name" class="form-control" placeholder="SKYCONNECT" required>
        </div>

        <div class="mb-3">
            <label>Localisation</label>
            <input type="text" name="location" class="form-control" placeholder="WiFi Zone Haut-Débit">
        </div>

        <div class="mb-3">
            <label>DNS</label>
            <input type="text" name="dns" class="form-control" placeholder="skyconnect.lan">
        </div>

        <div class="mb-3">
            <label>Téléphone assistance</label>
            <input type="text" name="assistance_phone" class="form-control" placeholder="+237678862852">
        </div>

        <div class="mb-3">
            <label>Plateforme</label>
            <select name="platform" class="form-control">
                <option value="Mikrotik">Mikrotik</option>
                <option value="Mikhmon">Mikhmon</option>
            </select>
        </div>

        <div class="mb-3">
            <label>Statut</label>
            <select name="status" class="form-control">
                <option value="active">Actif</option>
                <option value="inactive">Inactif</option>
            </select>
        </div>

        <button type="submit" class="sky-btn">
            Enregistrer le routeur
        </button>
    </form>
</div>

@endsection