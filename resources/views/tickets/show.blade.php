@extends('layouts.public')

@section('title', 'Votre ticket Wi-Fi - SkyConnect')

@section('content')
<main class="sky-section">
    <div class="sky-container form-shell">
        <section class="premium-card">
            <div class="success-burst"><i class="bi bi-check2 fs-2"></i></div>
            <h1 class="section-title" style="font-size:42px;">Ticket active avec succes</h1>
            <p class="section-copy">Votre paiement est confirme. Utilisez ces identifiants sur le portail captif SkyConnect.</p>

            <div class="ticket-mini" style="margin-top:24px;">
                <p><strong>Forfait :</strong> {{ $order->plan->name }}</p>
                <p><strong>Montant paye :</strong> {{ $order->amount }} FCFA</p>
                <p><strong>Reference :</strong> {{ $order->reference }}</p>
                <p><strong>Date d'expiration :</strong> selon duree du forfait</p>
            </div>
        </section>

        <aside class="premium-card" style="text-align:center;">
            <h2 style="margin-top:0;color:#0f2747;">Code ticket</h2>
            <div class="qr-box" style="margin:0 auto 22px;"></div>
            <div class="ticket-mini" style="text-align:left;">
                <p>Username</p>
                <h3 style="color:#0d47a1;">{{ $order->ticket->username }}</h3>
                <p>Password</p>
                <h3 style="color:#0d47a1;">{{ $order->ticket->password }}</h3>
            </div>
            <button class="sky-btn" style="width:100%;margin-top:18px;" onclick="navigator.clipboard && navigator.clipboard.writeText('{{ $order->ticket->username }} / {{ $order->ticket->password }}')">
                <i class="bi bi-copy"></i> Copier
            </button>
        </aside>
    </div>
</main>
@endsection
