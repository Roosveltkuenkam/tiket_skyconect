@extends('layouts.public')

@section('title', __('ui.orders.payment_title'))

@section('content')
<main class="sky-section">
    <div class="sky-container form-shell">
        <section class="premium-card">
            <span class="eyebrow"><span class="eyebrow-dot"></span> {{ __('ui.orders.payment_validation') }}</span>
            <h1 class="section-title" style="font-size:40px;">{{ __('ui.orders.confirm_payment') }}</h1>
            <p class="section-copy">{{ __('ui.orders.payment_copy') }}</p>

            <div class="payment-methods" style="margin:26px 0;">
                <div class="pay-method"><span><i class="bi bi-phone"></i> Orange Money</span><strong>Mobile</strong></div>
                <div class="pay-method"><span><i class="bi bi-phone-vibrate"></i> MTN Mobile Money</span><strong>Mobile</strong></div>
                <div class="pay-method"><span><i class="bi bi-credit-card-2-front"></i> Campay</span><strong>API</strong></div>
            </div>

            <form method="POST" action="{{ route('payments.simulate', $order->publicRouteParameters()) }}">
                @csrf
                <button type="submit" class="sky-btn" style="width:100%;">
                    {{ __('ui.orders.confirm_test_payment') }} <i class="bi bi-check2-circle"></i>
                </button>
            </form>
        </section>

        <aside class="premium-card">
            <h2 class="theme-title" style="margin-top:0;">{{ __('ui.orders.summary') }}</h2>
            <p><strong>{{ __('ui.orders.reference') }} :</strong> {{ $order->reference }}</p>
            <p><strong>{{ __('ui.orders.amount') }} :</strong> {{ $order->amount }} FCFA</p>
            <p><strong>{{ __('ui.orders.status') }} :</strong> {{ $order->status }}</p>
            <p><strong>{{ __('ui.orders.phone') }} :</strong> {{ $order->customer_phone }}</p>
        </aside>
    </div>
</main>
@endsection
