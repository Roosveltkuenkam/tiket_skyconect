@extends('layouts.admin')

@section('content')

<div class="page-header">
    <h3>Ajouter un tarif</h3>
    <p>Accueil / Tarifs / Ajouter un tarif</p>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="panel-card">
            <form method="POST" action="{{ route('admin.plans.store') }}">
                @csrf

                <h5 style="color:#1e9beb;">Informations principales</h5>

                <div class="mb-3">
                    <label>Routeur concerné</label>
                        <select name="router_id" class="form-control" required>
                            <option value="">— Choisir un routeur —</option>
                            @foreach($routers as $router)
                                <option value="{{ $router->id }}">
                                {{ $router->name }} - {{ $router->location }}
                            </option>
                             @endforeach
                        </select>
                </div>

                <div class="mb-3">
                    <label>Nom du tarif</label>
                    <input type="text" name="name" class="form-control" placeholder="Ex: 24 heures" required>
                </div>

                <div class="mb-3">
                    <label>Prix</label>
                    <input type="number" name="price" class="form-control" placeholder="500" required>
                </div>

                <div class="mb-3">
                    <label>Description</label>
                    <input type="text" name="description" class="form-control" placeholder="Ex: Idéal pour naviguer">
                </div>

                <div class="mb-3">
                    <label>Durée affichée au client</label>
                    <input type="text" name="duration" class="form-control" placeholder="Ex: 24 heures" required>
                </div>

                <div class="mb-3 form-check">
                    <input type="checkbox" name="is_active" class="form-check-input" id="is_active" checked>
                        <label class="form-check-label" for="is_active">
                            Activer ce tarif à la création
                        </label>
                </div>

                <button type="submit" class="sky-btn">
                    Enregistrer
                </button>

                <a href="{{ route('admin.plans.index') }}" class="btn btn-outline-primary ms-2">
                    Retour
                </a>
            </form>
        </div>
    </div>

    <div class="col-md-4">
        <div class="panel-card text-center">
            <h4 style="color:#1e9beb;">Aperçu client</h4>

            <div class="sky-card mt-3">
                <h4>Pass WiFi</h4>
                <h2>0</h2>
                <p>FCFA</p>
                <button class="sky-btn">
                    Acheter ce pass
                </button>
            </div>
        </div>
    </div>
</div>

@endsection