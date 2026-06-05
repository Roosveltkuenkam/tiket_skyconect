@extends('layouts.admin')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <span class="eyebrow"><span class="eyebrow-dot"></span> Nouvelle demande</span>
        <h3>Créer un ticket support</h3>
        <p>Décrivez le problème rencontré afin que l'équipe SkyConnect puisse vous aider.</p>
    </div>
    <a href="{{ route('dashboard.support.index') }}" class="btn btn-outline-primary">Retour</a>
</div>

@if($errors->any())
    <div class="alert alert-danger">
        @foreach($errors->all() as $error)
            <div>{{ $error }}</div>
        @endforeach
    </div>
@endif

<div class="panel-card">
    <form method="POST" action="{{ route('dashboard.support.store') }}" class="row g-3">
        @csrf
        <div class="col-md-8">
            <label class="form-label">Sujet</label>
            <input type="text" name="subject" class="form-control" value="{{ old('subject') }}" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Priorité</label>
            <select name="priority" class="form-control">
                <option value="low">Faible</option>
                <option value="normal" selected>Normale</option>
                <option value="high">Haute</option>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Commande liée</label>
            <select name="order_id" class="form-control">
                <option value="">Aucune</option>
                @foreach($orders as $order)
                    <option value="{{ $order->id }}" {{ old('order_id') == $order->id ? 'selected' : '' }}>
                        {{ $order->reference }} - {{ $order->amount }} XAF - {{ $order->status }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-12">
            <label class="form-label">Message</label>
            <textarea name="message" class="form-control" rows="6" required>{{ old('message') }}</textarea>
        </div>
        <div class="col-12">
            <button class="sky-btn">Envoyer</button>
        </div>
    </form>
</div>
@endsection
