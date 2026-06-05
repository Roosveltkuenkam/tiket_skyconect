@extends('layouts.public')

@section('title', 'Achat ' . $plan->name . ' - SkyConnect')

@section('content')
<main class="sky-section">
    <div class="sky-container form-shell">
        <section class="premium-card">
            <span class="eyebrow"><span class="eyebrow-dot"></span> Paiement securise</span>
            <h1 class="section-title" style="font-size:40px;">Achat du forfait {{ $plan->name }}</h1>
            <p class="section-copy">Entrez votre numero Mobile Money. Le paiement est simule pour le moment, mais l'interface est prete pour Orange Money, MTN Mobile Money et Campay.</p>

            <form method="POST" action="{{ route('orders.store', $plan) }}" style="margin-top:26px;">
                @csrf

                <label class="form-label">Numero de telephone</label>
                <input type="text" name="customer_phone" class="modern-input" style="width:100%;" placeholder="Ex: 690000000" required>

                <button type="submit" class="sky-btn" style="width:100%;margin-top:18px;">
                    Continuer vers le paiement <i class="bi bi-arrow-right"></i>
                </button>
            </form>
        </section>

        <aside class="premium-card">
            <h2 style="margin-top:0;color:#0f2747;">Resume commande</h2>
            <div class="ticket-mini">
                <div class="plan-duration">{{ $plan->name }}</div>
                <div class="plan-price">{{ $plan->price }} <span>FCFA</span></div>
                <p class="section-copy" style="margin-bottom:0;">Duree : {{ $plan->duration }}</p>
            </div>

            <div class="payment-methods" style="margin-top:18px;">
                <div class="pay-method"><span><i class="bi bi-phone"></i> Orange Money</span><strong>pret</strong></div>
                <div class="pay-method"><span><i class="bi bi-phone-vibrate"></i> MTN Mobile Money</span><strong>pret</strong></div>
                <div class="pay-method"><span><i class="bi bi-credit-card"></i> Campay</span><strong>simulation</strong></div>
            </div>
        </aside>
    </div>
</main>
@endsection
