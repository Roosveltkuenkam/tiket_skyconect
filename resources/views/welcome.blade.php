@extends('layouts.public')

@section('title', __('ui.landing.title'))

@section('content')
<main>
    <section class="sky-container sky-hero">
        <div>
            <span class="eyebrow"><span class="eyebrow-dot"></span> {{ __('ui.landing.eyebrow') }}</span>
            <h1>{{ __('ui.landing.hero_title') }}</h1>
            <p class="hero-copy">
                {{ __('ui.landing.hero_copy') }}
            </p>
            <div class="sky-actions">
                <a href="{{ route('plans.index') }}" class="sky-btn"><i class="bi bi-wifi"></i> {{ __('ui.landing.buy_ticket') }}</a>
                <a href="#plans" class="sky-btn-outline"><i class="bi bi-grid-3x3-gap"></i> {{ __('ui.landing.view_plans') }}</a>
            </div>
        </div>

        <div class="hero-visual" aria-label="Illustration SkyConnect">
            <div class="cloud-core"></div>
            <div class="wifi-rings">
                <span style="--i:0"></span>
                <span style="--i:1"></span>
                <span style="--i:2"></span>
            </div>
            <div class="floating-chip chip-left"><i class="bi bi-lightning-charge-fill"></i> {{ __('ui.landing.fast_payment') }}</div>
            <div class="floating-chip chip-right"><i class="bi bi-shield-check"></i> {{ __('ui.landing.secure_ticket') }}</div>
            <div class="phone-mockup">
                <div class="phone-screen">
                    <div class="ticket-mini">
                        <strong>Ticket 24h</strong>
                        <div class="plan-price">500 <span>FCFA</span></div>
                        <div class="mini-lines">
                            <span></span>
                            <span></span>
                            <span></span>
                        </div>
                    </div>
                    <div class="sky-btn" style="margin-top:20px;width:100%;">{{ __('ui.landing.activate_pass') }}</div>
                </div>
            </div>
        </div>
    </section>

    <section id="plans" class="sky-section">
        <div class="sky-container">
            <div class="section-head">
                <div>
                    <span class="eyebrow"><span class="eyebrow-dot"></span> {{ __('ui.landing.popular_plans') }}</span>
                    <h2 class="section-title">{{ __('ui.landing.choose_pass') }}</h2>
                </div>
                <p class="section-copy">{{ __('ui.landing.plans_copy') }}</p>
            </div>

            <div class="plans-grid">
                @foreach([
                    ['name' => 'Ticket 5h', 'price' => '250', 'duration' => '5 heures'],
                    ['name' => 'Ticket 24h', 'price' => '500', 'duration' => '24 heures'],
                    ['name' => 'Ticket 3 jours', 'price' => '1000', 'duration' => '3 jours'],
                    ['name' => 'Ticket 7 jours', 'price' => '1850', 'duration' => '7 jours'],
                    ['name' => 'Ticket 30 jours', 'price' => '5000', 'duration' => '30 jours'],
                ] as $plan)
                    <article class="plan-card">
                        <div class="plan-duration">{{ $plan['name'] }}</div>
                        <div class="plan-price">{{ $plan['price'] }} <span>FCFA</span></div>
                        <ul class="plan-list">
                            <li>{{ $plan['duration'] }} de connexion</li>
                            <li>Code livre instantanement</li>
                            <li>Compatible portail captif</li>
                        </ul>
                        <a href="{{ route('plans.index') }}" class="sky-btn" style="margin-top:auto;">{{ __('ui.nav.buy') }}</a>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section id="coverage" class="sky-section">
        <div class="sky-container">
            <div class="feature-grid">
                <div class="premium-card">
                    <h3><i class="bi bi-phone"></i> {{ __('ui.landing.mobile_money') }}</h3>
                    <p class="section-copy">{{ __('ui.landing.mobile_money_copy') }}</p>
                </div>
                <div class="premium-card">
                    <h3><i class="bi bi-router"></i> {{ __('ui.landing.hotspots') }}</h3>
                    <p class="section-copy">{{ __('ui.landing.hotspots_copy') }}</p>
                </div>
                <div class="premium-card">
                    <h3><i class="bi bi-graph-up-arrow"></i> {{ __('ui.landing.analytics') }}</h3>
                    <p class="section-copy">{{ __('ui.landing.analytics_copy') }}</p>
                </div>
                <div class="premium-card">
                    <h3><i class="bi bi-shield-lock"></i> {{ __('ui.landing.security') }}</h3>
                    <p class="section-copy">{{ __('ui.landing.security_copy') }}</p>
                </div>
            </div>
        </div>
    </section>

    <section class="sky-section">
        <div class="sky-container form-shell">
            <div>
                <span class="eyebrow"><span class="eyebrow-dot"></span> {{ __('ui.landing.captive_portal') }}</span>
                <h2 class="section-title">{{ __('ui.landing.portal_title') }}</h2>
                <p class="section-copy">{{ __('ui.landing.portal_copy') }}</p>
            </div>
            <div class="portal-preview">
                <div class="portal-box">
                    <img src="{{ asset('images/logo-skyconnect.PNG') }}" alt="SkyConnect" class="logo-tile" style="width:190px;border-radius:16px;padding:10px;">
                    <h3>{{ __('ui.landing.portal_welcome') }}</h3>
                    <input class="modern-input" style="width:100%;margin:12px 0;" placeholder="{{ __('ui.landing.ticket_code') }}">
                    <button class="sky-btn" style="width:100%;">{{ __('ui.landing.connect') }}</button>
                    <p style="margin:14px 0 0;">{{ __('ui.landing.buy_ticket') }}</p>
                </div>
            </div>
        </div>
    </section>

    <section id="support" class="sky-section">
        <div class="sky-container">
            <div class="section-head">
                <div>
                    <span class="eyebrow"><span class="eyebrow-dot"></span> {{ __('ui.landing.mobile_app') }}</span>
                    <h2 class="section-title">{{ __('ui.landing.mobile_title') }}</h2>
                </div>
                <p class="section-copy">{{ __('ui.landing.mobile_copy') }}</p>
            </div>
            <div class="mobile-grid">
                @foreach(['Splash', 'Achat ticket', 'Paiement', 'Historique', 'Profil'] as $screen)
                    <div class="mobile-card">
                        <div class="mobile-screen">
                            <div>
                                <div class="screen-title">{{ $screen }}</div>
                                <div class="mock-bar" style="width:80%;"></div>
                                <div class="mock-bar" style="width:58%;"></div>
                            </div>
                            <div class="ticket-mini">
                                <strong>SkyConnect</strong>
                                <div class="mock-bar"></div>
                                <div class="mock-bar" style="width:70%;"></div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
</main>
@endsection
