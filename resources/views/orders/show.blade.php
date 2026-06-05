@extends('layouts.public')

@section('title', 'Paiement - SkyConnect')

@section('content')
<main class="sky-section">
    <div class="sky-container form-shell">
        <section class="premium-card">
            <span class="eyebrow"><span class="eyebrow-dot"></span> Validation paiement</span>
            <h1 class="section-title" style="font-size:40px;">Confirmer votre paiement</h1>
            <p class="section-copy">La commande est creee. Validez le paiement test pour recevoir automatiquement votre ticket Wi-Fi.</p>

            <div class="payment-methods" style="margin:26px 0;">
                <div class="pay-method"><span><i class="bi bi-phone"></i> Orange Money</span><strong>Mobile</strong></div>
                <div class="pay-method"><span><i class="bi bi-phone-vibrate"></i> MTN Mobile Money</span><strong>Mobile</strong></div>
                <div class="pay-method"><span><i class="bi bi-credit-card-2-front"></i> Campay</span><strong>API future</strong></div>
            </div>

            <form method="POST" action="{{ route('payments.simulate', $order) }}">
                @csrf
                <button type="submit" class="sky-btn" style="width:100%;">
                    Confirmer paiement test <i class="bi bi-check2-circle"></i>
                </button>
            </form>
        </section>

        <aside class="premium-card">
            <h2 style="margin-top:0;color:#0f2747;">Resume</h2>
            <p><strong>Reference :</strong> {{ $order->reference }}</p>
            <p><strong>Montant :</strong> {{ $order->amount }} FCFA</p>
            <p><strong>Statut :</strong> {{ $order->status }}</p>
            <p><strong>Telephone :</strong> {{ $order->customer_phone }}</p>
        </aside>
    </div>
</main>
@endsection
