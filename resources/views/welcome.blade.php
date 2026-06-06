@extends('layouts.public')

@section('title', __('ui.landing.title'))

@section('content')
<main class="landing-main">
    <section class="sky-container sky-hero african-hero">
        <div>
            <span class="eyebrow"><span class="eyebrow-dot"></span> {{ __('ui.landing.eyebrow') }}</span>
            <h1>{{ __('ui.landing.hero_title') }}</h1>
            <p class="hero-copy">
                {{ __('ui.landing.hero_copy') }}
            </p>
            <div class="sky-actions">
                <a href="{{ route('register') }}" class="sky-btn"><i class="bi bi-person-plus"></i> {{ __('ui.landing.create_account') }}</a>
                <a href="#pricing" class="sky-btn-outline"><i class="bi bi-gem"></i> {{ __('ui.landing.view_subscriptions') }}</a>
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
                        <strong>{{ __('ui.landing.dashboard_preview') }}</strong>
                        <div class="plan-price">42 <span>{{ __('ui.landing.sales_today_short') }}</span></div>
                        <div class="mini-lines">
                            <span></span>
                            <span></span>
                            <span></span>
                        </div>
                    </div>
                    <div class="sky-btn" style="margin-top:20px;width:100%;">{{ __('ui.landing.manage_sales') }}</div>
                </div>
            </div>
        </div>
    </section>

    <section class="sky-section">
        <div class="sky-container">
            <div class="trust-strip african-pattern">
                @foreach(__('ui.landing.trust_points') as $point)
                    <div class="trust-item">
                        <i class="bi {{ $point['icon'] }}"></i>
                        <div>
                            <strong>{{ $point['title'] }}</strong>
                            <span>{{ $point['copy'] }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section id="features" class="sky-section">
        <div class="sky-container">
            <div class="section-head">
                <div>
                    <span class="eyebrow"><span class="eyebrow-dot"></span> {{ __('ui.landing.platform_scope') }}</span>
                    <h2 class="section-title">{{ __('ui.landing.manage_title') }}</h2>
                </div>
                <p class="section-copy">{{ __('ui.landing.manage_copy') }}</p>
            </div>

            <div class="feature-grid">
                @foreach(__('ui.landing.owner_features') as $feature)
                    <div class="premium-card">
                        <h3><i class="bi {{ $feature['icon'] }}"></i> {{ $feature['title'] }}</h3>
                        <p class="section-copy">{{ $feature['copy'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="sky-section">
        <div class="sky-container form-shell">
            <div>
                <span class="eyebrow"><span class="eyebrow-dot"></span> {{ __('ui.landing.automation') }}</span>
                <h2 class="section-title">{{ __('ui.landing.automation_title') }}</h2>
                <p class="section-copy">{{ __('ui.landing.automation_copy') }}</p>
                <div class="automation-steps">
                    @foreach(__('ui.landing.automation_steps') as $step)
                        <div class="automation-step">
                            <span>{{ $loop->iteration }}</span>
                            <p>{{ $step }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="automation-board african-pattern">
                <div class="flow-node"><i class="bi bi-router"></i>{{ __('ui.landing.flow_router') }}</div>
                <div class="flow-line"></div>
                <div class="flow-node"><i class="bi bi-phone"></i>{{ __('ui.landing.flow_payment') }}</div>
                <div class="flow-line"></div>
                <div class="flow-node"><i class="bi bi-ticket-perforated"></i>{{ __('ui.landing.flow_ticket') }}</div>
            </div>
        </div>
    </section>

    <section id="how-it-works" class="sky-section">
        <div class="sky-container">
            <div class="section-head">
                <div>
                    <span class="eyebrow"><span class="eyebrow-dot"></span> {{ __('ui.landing.for_whom') }}</span>
                    <h2 class="section-title">{{ __('ui.landing.for_whom_title') }}</h2>
                </div>
                <p class="section-copy">{{ __('ui.landing.for_whom_copy') }}</p>
            </div>

            <div class="plans-grid subscription-pricing-grid">
                @foreach(__('ui.landing.audiences') as $audience)
                    <article class="plan-card">
                        <div class="plan-duration"><i class="bi {{ $audience['icon'] }}"></i> {{ $audience['title'] }}</div>
                        <p class="section-copy">{{ $audience['copy'] }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="sky-section">
        <div class="sky-container">
            <div class="section-head">
                <div>
                    <span class="eyebrow"><span class="eyebrow-dot"></span> {{ __('ui.landing.how_it_works') }}</span>
                    <h2 class="section-title">{{ __('ui.landing.how_it_works_title') }}</h2>
                </div>
                <p class="section-copy">{{ __('ui.landing.how_it_works_copy') }}</p>
            </div>

            <div class="feature-grid">
                @foreach(__('ui.landing.steps') as $index => $step)
                    <div class="premium-card">
                        <span class="eyebrow"><span class="eyebrow-dot"></span> {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</span>
                        <h3>{{ $step['title'] }}</h3>
                        <p class="section-copy">{{ $step['copy'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section id="pricing" class="sky-section">
        <div class="sky-container">
            <div class="section-head">
                <div>
                    <span class="eyebrow"><span class="eyebrow-dot"></span> {{ __('ui.landing.subscription_plans') }}</span>
                    <h2 class="section-title">{{ __('ui.landing.subscription_title') }}</h2>
                </div>
                <p class="section-copy">{{ __('ui.landing.subscription_copy') }}</p>
            </div>

            <div class="plans-grid">
                @foreach(__('ui.landing.subscription_cards') as $plan)
                    <article class="plan-card subscription-choice-card {{ !empty($plan['highlight']) ? 'is-current' : '' }}">
                        @if(!empty($plan['badge']))
                            <span class="subscription-ribbon {{ !empty($plan['highlight']) ? 'pro' : '' }}">{{ $plan['badge'] }}</span>
                        @endif
                        <div class="plan-duration">{{ $plan['name'] }}</div>
                        <p class="section-copy">{{ $plan['copy'] }}</p>
                        <div class="plan-price">{{ $plan['price'] }} <span>XAF / {{ __('ui.dashboard_subscriptions.month') }}</span></div>
                        <ul class="plan-list">
                            @foreach($plan['features'] as $feature)
                                <li>{{ $feature }}</li>
                            @endforeach
                        </ul>
                        <a href="{{ route('register') }}" class="sky-btn" style="margin-top:auto;">{{ __('ui.landing.start_with_plan') }}</a>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section id="coverage" class="sky-section">
        <div class="sky-container">
            <div class="section-head">
                <div>
                    <span class="eyebrow"><span class="eyebrow-dot"></span> {{ __('ui.landing.africa_ready') }}</span>
                    <h2 class="section-title">{{ __('ui.landing.africa_title') }}</h2>
                </div>
                <p class="section-copy">{{ __('ui.landing.africa_copy') }}</p>
            </div>
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

    <section class="sky-section">
        <div class="sky-container">
            <div class="section-head">
                <div>
                    <span class="eyebrow"><span class="eyebrow-dot"></span> {{ __('ui.landing.faq') }}</span>
                    <h2 class="section-title">{{ __('ui.landing.faq_title') }}</h2>
                </div>
                <p class="section-copy">{{ __('ui.landing.faq_copy') }}</p>
            </div>
            <div class="faq-grid">
                @foreach(__('ui.landing.faq_items') as $item)
                    <details class="faq-item">
                        <summary>{{ $item['question'] }}</summary>
                        <p>{{ $item['answer'] }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </section>

    <section id="support" class="sky-section">
        <div class="sky-container form-shell">
            <div>
                <span class="eyebrow"><span class="eyebrow-dot"></span> {{ __('ui.landing.contact') }}</span>
                <h2 class="section-title">{{ __('ui.landing.contact_title') }}</h2>
                <p class="section-copy">{{ __('ui.landing.contact_copy') }}</p>
                <div class="contact-actions">
                    <a href="mailto:support@skyconnect.local" class="sky-btn"><i class="bi bi-envelope"></i> support@skyconnect.local</a>
                    <a href="{{ route('register') }}" class="sky-btn-outline"><i class="bi bi-person-plus"></i> {{ __('ui.landing.create_account') }}</a>
                </div>
            </div>
            <div class="contact-card african-pattern">
                <div class="afro-symbol"><i class="bi bi-diamond-fill"></i></div>
                <h3>{{ __('ui.landing.contact_card_title') }}</h3>
                <p>{{ __('ui.landing.contact_card_copy') }}</p>
            </div>
        </div>
    </section>

    <section class="sky-section">
        <div class="sky-container">
            <div class="section-head">
                <div>
                    <span class="eyebrow"><span class="eyebrow-dot"></span> {{ __('ui.landing.mobile_app') }}</span>
                    <h2 class="section-title">{{ __('ui.landing.mobile_title') }}</h2>
                </div>
                <p class="section-copy">{{ __('ui.landing.mobile_copy') }}</p>
            </div>
            <div class="mobile-grid">
                @foreach(__('ui.landing.mobile_screens') as $screen)
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
