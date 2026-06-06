@extends('layouts.public')

@section('title', __('ui.portal.title', ['router' => $router->name]))

@section('content')
@php
    $businessName = optional($router->user)->business_name ?: optional($router->user)->name ?: 'SkyConnect';
@endphp
<main class="portal-public-page">
    <section class="sky-section portal-public-hero african-hero">
        <div class="sky-container">
            <div class="portal-public-grid">
                <div>
                    <span class="eyebrow"><span class="eyebrow-dot"></span> {{ __('ui.portal.eyebrow') }}</span>
                    <h1>{{ __('ui.portal.heading', ['business' => $businessName]) }}</h1>
                    <p class="hero-copy">
                        {{ __('ui.portal.copy') }}
                    </p>

                    <div class="portal-router-summary">
                        <div>
                            <span>{{ __('ui.portal.router') }}</span>
                            <strong>{{ $router->name }}</strong>
                        </div>
                        <div>
                            <span>{{ __('ui.portal.location') }}</span>
                            <strong>{{ $router->location ?: __('ui.portal.location_unknown') }}</strong>
                        </div>
                        <div>
                            <span>{{ __('ui.portal.status') }}</span>
                            <strong class="text-success">{{ __('ui.portal.online') }}</strong>
                        </div>
                    </div>
                </div>

                <div class="portal-connect-card">
                    <div class="portal-cloud-icon">
                        <i class="bi bi-cloud-fill"></i>
                        <i class="bi bi-wifi"></i>
                    </div>
                    <h2>{{ __('ui.portal.welcome') }}</h2>
                    <p>{{ __('ui.portal.captive_hint') }}</p>
                    <div class="modern-input d-flex align-items-center justify-content-between px-3">
                        <span class="text-muted">{{ __('ui.landing.ticket_code') }}</span>
                        <i class="bi bi-lock-fill theme-link-icon"></i>
                    </div>
                    <a href="#plans" class="sky-btn w-100 mt-3">{{ __('ui.portal.choose_plan') }}</a>
                </div>
            </div>
        </div>
    </section>

    <section class="sky-section" id="plans">
        <div class="sky-container">
            <div class="section-head">
                <div>
                    <span class="eyebrow"><span class="eyebrow-dot"></span> {{ __('ui.portal.active_plans') }}</span>
                    <h2 class="section-title">{{ __('ui.portal.select_ticket') }}</h2>
                </div>
                <p class="section-copy">{{ __('ui.portal.plans_copy') }}</p>
            </div>

            @if($plans->isEmpty())
                <div class="premium-card text-center">
                    <i class="bi bi-ticket-perforated theme-link-icon" style="font-size:44px;"></i>
                    <h3 class="mt-3">{{ __('ui.portal.no_plan_title') }}</h3>
                    <p class="section-copy mx-auto">{{ __('ui.portal.no_plan_copy') }}</p>
                </div>
            @else
                <div class="plans-grid portal-plans-grid">
                    @foreach($plans as $plan)
                        @php
                            $hasStock = $plan->available_tickets_count > 0;
                        @endphp
                        <article class="plan-card portal-plan-card">
                            <div class="d-flex justify-content-between align-items-start gap-3">
                                <div>
                                    <div class="plan-duration">{{ $plan->name }}</div>
                                    <p class="text-muted mb-0">{{ $plan->duration }}</p>
                                </div>
                                <span class="badge {{ $hasStock ? 'text-bg-success' : 'text-bg-secondary' }}">
                                    {{ $hasStock ? __('ui.portal.available') : __('ui.portal.out_of_stock') }}
                                </span>
                            </div>

                            <div class="plan-price">{{ number_format($plan->price, 0, ',', ' ') }} <span>FCFA</span></div>

                            <ul class="plan-list">
                                <li>{{ __('ui.portal.instant_delivery') }}</li>
                                <li>{{ __('ui.portal.mobile_money_ready') }}</li>
                                <li>{{ __('ui.portal.stock_count', ['count' => $plan->available_tickets_count]) }}</li>
                            </ul>

                            @if($hasStock)
                                <a href="{{ route('orders.create', $plan) }}" class="sky-btn mt-auto">
                                    <i class="bi bi-cart-check"></i> {{ __('ui.portal.buy') }}
                                </a>
                            @else
                                <button type="button" class="sky-btn-outline mt-auto" disabled>
                                    <i class="bi bi-hourglass-split"></i> {{ __('ui.portal.unavailable') }}
                                </button>
                            @endif
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    <section class="sky-section pt-0">
        <div class="sky-container">
            <div class="premium-card portal-help-card african-pattern">
                <div>
                    <span class="eyebrow"><span class="eyebrow-dot"></span> {{ __('ui.nav.support') }}</span>
                    <h2>{{ __('ui.portal.need_help') }}</h2>
                    <p class="section-copy mb-0">{{ __('ui.portal.need_help_copy') }}</p>
                </div>
                @if($router->assistance_phone)
                    <a class="sky-btn-outline" href="tel:{{ $router->assistance_phone }}">
                        <i class="bi bi-telephone-fill"></i> {{ $router->assistance_phone }}
                    </a>
                @endif
            </div>
        </div>
    </section>
</main>
@endsection
