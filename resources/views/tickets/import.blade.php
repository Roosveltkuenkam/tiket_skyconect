@extends('layouts.admin')

@section('content')
<div class="page-header">
    <span class="eyebrow"><span class="eyebrow-dot"></span> Mikhmon CSV</span>
    <h3>Importer des tickets</h3>
    <p>Ajoutez rapidement un lot de vouchers et rattachez-le a un plan Wi-Fi.</p>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
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

<div class="row g-4">
    <div class="col-md-8">
        <div class="panel-card">
            <h4>Import CSV Mikhmon</h4>
            <p class="section-copy">Les doublons seront ignores automatiquement pour proteger votre stock.</p>

            <form method="POST" action="{{ route(($routeArea ?? 'admin') . '.tickets.import.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Forfait concerne</label>
                    <select name="plan_id" class="form-control" required>
                        <option value="">Choisir un forfait</option>
                        @foreach($plans as $plan)
                            <option value="{{ $plan->id }}">{{ $plan->name }} - {{ $plan->price }} FCFA</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Fichier CSV Mikhmon</label>
                    <input type="file" name="file" class="form-control" accept=".csv,.txt" required>
                    <small class="text-muted">Format accepte : CSV ou TXT exporte depuis Mikhmon.</small>
                </div>

                <button type="submit" class="sky-btn"><i class="bi bi-upload"></i> Importer les tickets</button>
                <a href="{{ route(($routeArea ?? 'admin') . '.tickets.index') }}" class="btn btn-outline-primary ms-2">Retour</a>
            </form>
        </div>
    </div>

    <div class="col-md-4">
        <div class="panel-card">
            <h4>Format attendu</h4>
            <p class="section-copy">Le fichier peut contenir une ligne d'en-tete comme :</p>
            <pre style="background:#f1f5f9;padding:15px;border-radius:12px;">Username,Password,Profile,Time Limit,Data Limit,Comment</pre>
            <ul class="plan-list">
                <li>Username</li>
                <li>Password</li>
                <li>Profile</li>
            </ul>
        </div>
    </div>
</div>
@endsection
