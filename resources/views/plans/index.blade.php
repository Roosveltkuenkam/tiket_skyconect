@extends('layouts.public')

@section('title', 'Forfaits Wi-Fi - SkyConnect')

@section('content')
<main class="sky-section">
    <div class="sky-container">
        <div class="section-head">
            <div>
                <span class="eyebrow"><span class="eyebrow-dot"></span> Forfaits Wi-Fi</span>
                <h1 class="section-title">Achetez votre ticket en quelques secondes</h1>
            </div>
            <p class="section-copy">Selectionnez la duree qui vous convient, payez par Mobile Money, puis recevez votre ticket de connexion.</p>
        </div>

        <div class="plans-grid">
            @foreach($plans as $plan)
                <article class="plan-card">
                    <div class="plan-duration">{{ $plan->name }}</div>
                    <div class="plan-price">{{ $plan->price }} <span>FCFA</span></div>
                    <ul class="plan-list">
                        <li>Duree : {{ $plan->duration }}</li>
                        <li>{{ $plan->description ?: 'Ticket Wi-Fi prepayé' }}</li>
                        <li>Livraison instantanee apres paiement</li>
                    </ul>
                    <a href="{{ route('orders.create', $plan) }}" class="sky-btn" style="margin-top:auto;">
                        <i class="bi bi-cart-check"></i> Acheter
                    </a>
                </article>
            @endforeach
        </div>
    </div>
</main>
@endsection
