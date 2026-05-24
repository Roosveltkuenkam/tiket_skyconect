@extends('layouts.admin')

@section('content')

<div class="page-header">
    <h3>Importer des tickets</h3>
    <p>Accueil / Tickets / Importer</p>
</div>

@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="row">
    <div class="col-md-8">
        <div class="panel-card">
            <h5 style="color:#1e9beb;">Import CSV Mikhmon</h5>

            <p class="text-muted">
                Importez ici les tickets générés depuis Mikhmon. Les doublons seront ignorés automatiquement.
            </p>

            <form method="POST" action="{{ route('tickets.import.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Forfait concerné</label>
                    <select name="plan_id" class="form-control" required>
                        <option value="">— Choisir un forfait —</option>
                        @foreach($plans as $plan)
                            <option value="{{ $plan->id }}">
                                {{ $plan->name }} - {{ $plan->price }} FCFA
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Fichier CSV Mikhmon</label>
                    <input type="file" name="file" class="form-control" accept=".csv,.txt" required>
                    <small class="text-muted">
                        Format accepté : CSV ou TXT exporté depuis Mikhmon.
                    </small>
                </div>

                <button type="submit" class="sky-btn">
                    Importer les tickets
                </button>

                <a href="{{ route('admin.tickets.index') }}" class="btn btn-outline-primary ms-2">
                    Retour aux tickets
                </a>
            </form>
        </div>
    </div>

    <div class="col-md-4">
        <div class="panel-card">
            <h5 style="color:#1e9beb;">Format attendu</h5>

            <p class="text-muted">
                Le fichier Mikhmon peut contenir une ligne d’en-tête comme :
            </p>

            <pre style="background:#f1f5f9; padding:15px; border-radius:12px;">Username,Password,Profile,Time Limit,Data Limit,Comment</pre>

            <p class="text-muted">
                L’application utilisera seulement :
            </p>

            <ul>
                <li>Username</li>
                <li>Password</li>
                <li>Profile</li>
            </ul>
        </div>
    </div>
</div>

@endsection