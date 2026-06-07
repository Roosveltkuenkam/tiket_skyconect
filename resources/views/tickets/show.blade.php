@extends('layouts.public')

@section('title', __('ui.ticket.title'))

@section('content')
<main class="sky-section">
    <div class="sky-container form-shell">
        <section class="premium-card">
            <div class="success-burst"><i class="bi bi-check2 fs-2"></i></div>
            <h1 class="section-title" style="font-size:42px;">{{ __('ui.ticket.activated') }}</h1>
            <p class="section-copy">{{ __('ui.ticket.copy') }}</p>

            <div class="ticket-mini" style="margin-top:24px;">
                <p><strong>{{ __('ui.ticket.plan') }} :</strong> {{ $order->plan->name }}</p>
                <p><strong>{{ __('ui.ticket.paid_amount') }} :</strong> {{ $order->amount }} FCFA</p>
                <p><strong>{{ __('ui.ticket.reference') }} :</strong> {{ $order->reference }}</p>
                <p><strong>{{ __('ui.ticket.expires') }} :</strong> {{ __('ui.ticket.expires_value') }}</p>
            </div>
        </section>

        <aside class="premium-card" style="text-align:center;">
            <h2 class="theme-title" style="margin-top:0;">{{ __('ui.ticket.code') }}</h2>
            <div class="qr-box" style="margin:0 auto 22px;"></div>
            <div class="ticket-mini" style="text-align:left;">
                <p>{{ __('ui.ticket.username') }}</p>
                <h3 class="theme-link-icon">{{ $order->ticket->username }}</h3>
                <p>{{ __('ui.ticket.password') }}</p>
                <h3 class="theme-link-icon">{{ $order->ticket->password }}</h3>
            </div>
            <button class="sky-btn" style="width:100%;margin-top:18px;" onclick="navigator.clipboard && navigator.clipboard.writeText('{{ $order->ticket->username }} / {{ $order->ticket->password }}')">
                <i class="bi bi-copy"></i> {{ __('ui.ticket.copy_button') }}
            </button>
        </aside>
    </div>
</main>
@endsection
