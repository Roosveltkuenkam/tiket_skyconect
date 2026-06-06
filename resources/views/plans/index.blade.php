@extends('layouts.public')

@section('title', __('ui.plans_page.title'))

@section('content')
<main class="sky-section">
    <div class="sky-container">
        <div class="section-head">
            <div>
                <span class="eyebrow"><span class="eyebrow-dot"></span> {{ __('ui.plans_page.eyebrow') }}</span>
                <h1 class="section-title">{{ __('ui.plans_page.heading') }}</h1>
            </div>
            <p class="section-copy">{{ __('ui.plans_page.copy') }}</p>
        </div>

        <div class="plans-grid">
            @foreach($plans as $plan)
                <article class="plan-card">
                    <div class="plan-duration">{{ $plan->name }}</div>
                    <div class="plan-price">{{ $plan->price }} <span>FCFA</span></div>
                    <ul class="plan-list">
                        <li>{{ __('ui.plans_page.duration') }} : {{ $plan->duration }}</li>
                        <li>{{ $plan->description ?: __('ui.plans_page.default_description') }}</li>
                        <li>{{ __('ui.plans_page.instant_delivery') }}</li>
                    </ul>
                    <a href="{{ route('orders.create', $plan) }}" class="sky-btn" style="margin-top:auto;">
                        <i class="bi bi-cart-check"></i> {{ __('ui.nav.buy') }}
                    </a>
                </article>
            @endforeach
        </div>
    </div>
</main>
@endsection
