@extends('layouts.public')

@section('title', __('ui.orders.buy_title', ['plan' => $plan->name]))

@section('content')
<main class="sky-section">
    <div class="sky-container form-shell">
        <section class="premium-card">
            <span class="eyebrow"><span class="eyebrow-dot"></span> {{ __('ui.orders.secure_payment') }}</span>
            <h1 class="section-title" style="font-size:40px;">{{ __('ui.orders.buy_heading', ['plan' => $plan->name]) }}</h1>
            <p class="section-copy">{{ __('ui.orders.buy_copy') }}</p>

            <form method="POST" action="{{ route('orders.store', $plan) }}" style="margin-top:26px;">
                @csrf

                <label class="form-label">{{ __('ui.orders.phone_number') }}</label>
                <input type="text" name="customer_phone" class="modern-input" style="width:100%;" placeholder="Ex: 690000000" required>

                <button type="submit" class="sky-btn" style="width:100%;margin-top:18px;">
                    {{ __('ui.orders.continue_payment') }} <i class="bi bi-arrow-right"></i>
                </button>
            </form>
        </section>

        <aside class="premium-card">
            <h2 class="theme-title" style="margin-top:0;">{{ __('ui.orders.order_summary') }}</h2>
            <div class="ticket-mini">
                <div class="plan-duration">{{ $plan->name }}</div>
                <div class="plan-price">{{ $plan->price }} <span>FCFA</span></div>
                <p class="section-copy" style="margin-bottom:0;">{{ __('ui.orders.duration') }} : {{ $plan->duration }}</p>
            </div>

            <div class="payment-methods" style="margin-top:18px;">
                <div class="pay-method"><span><i class="bi bi-phone"></i> Orange Money</span><strong>{{ __('ui.orders.ready') }}</strong></div>
                <div class="pay-method"><span><i class="bi bi-phone-vibrate"></i> MTN Mobile Money</span><strong>{{ __('ui.orders.ready') }}</strong></div>
                <div class="pay-method"><span><i class="bi bi-credit-card"></i> Campay</span><strong>{{ __('ui.orders.simulation') }}</strong></div>
            </div>
        </aside>
    </div>
</main>
@endsection
