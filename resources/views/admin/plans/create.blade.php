@extends('layouts.admin')

@section('content')
@php
    $plan = $plan ?? null;
    $isEdit = (bool) $plan;
@endphp
<div class="page-header">
    <span class="eyebrow"><span class="eyebrow-dot"></span> Nouveau plan</span>
    <h3>{{ $isEdit ? 'Modifier un forfait Wi-Fi' : 'Ajouter un plan Wi-Fi' }}</h3>
    <p>{{ $isEdit ? 'Ajustez le prix, la duree, le routeur et la disponibilite.' : 'Configurez le prix, la duree et le routeur concerne.' }}</p>
</div>

<div class="row g-4">
    <div class="col-md-8">
        <div class="panel-card">
            <form method="POST" action="{{ $isEdit ? route(($routeArea ?? 'admin') . '.plans.update', $plan) : route(($routeArea ?? 'admin') . '.plans.store') }}">
                @csrf
                @if($isEdit)
                    @method('PUT')
                @endif

                <div class="mb-3">
                    <label class="form-label">Routeur concerne</label>
                    <select name="router_id" class="form-control" required>
                        <option value="">Choisir un routeur</option>
                        @foreach($routers as $router)
                            <option value="{{ $router->id }}" {{ old('router_id', optional($plan)->router_id) == $router->id ? 'selected' : '' }}>
                                {{ $router->name }} - {{ $router->location ?: 'Sans localisation' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Nom du plan</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', optional($plan)->name) }}" placeholder="Ex: Ticket 24h" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Prix</label>
                    <input type="number" name="price" class="form-control" value="{{ old('price', optional($plan)->price) }}" placeholder="500" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Duree affichee</label>
                    <input type="text" name="duration" class="form-control" value="{{ old('duration', optional($plan)->duration) }}" placeholder="Ex: 24 heures" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <input type="text" name="description" class="form-control" value="{{ old('description', optional($plan)->description) }}" placeholder="Ex: Ideal pour une journee de navigation">
                </div>

                <div class="form-check mb-4">
                    <input type="checkbox" name="is_active" class="form-check-input" id="is_active" {{ old('is_active', optional($plan)->is_active ?? true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">Activer ce plan a la creation</label>
                </div>

                <button type="submit" class="sky-btn">{{ $isEdit ? 'Mettre a jour' : 'Enregistrer' }}</button>
                <a href="{{ route(($routeArea ?? 'admin') . '.plans.index') }}" class="btn btn-outline-primary ms-2">Retour</a>
            </form>
        </div>
    </div>

    <div class="col-md-4">
        <div class="panel-card">
            <h4>Apercu client</h4>
            <div class="plan-card mt-3">
                <div class="plan-duration">Pass Wi-Fi</div>
                <div class="plan-price">0 <span>FCFA</span></div>
                <ul class="plan-list">
                    <li>Duree configurable</li>
                    <li>Lien portail captif</li>
                    <li>Livraison apres paiement</li>
                </ul>
                <button class="sky-btn" type="button">Acheter ce pass</button>
            </div>
        </div>
    </div>
</div>
@endsection
