@extends('layouts.public')

@section('title', 'SkyConnect - Internet rapide partout')

@section('content')
<main>
    <section class="sky-container sky-hero">
        <div>
            <span class="eyebrow"><span class="eyebrow-dot"></span> Tickets Wi-Fi instantanes</span>
            <h1>Internet rapide partout avec SkyConnect</h1>
            <p class="hero-copy">
                Achetez votre ticket Wi-Fi en quelques secondes et restez connecte sur vos hotspots preferes. Simple,
                rapide et pense pour les utilisateurs comme pour les administrateurs de reseaux.
            </p>
            <div class="sky-actions">
                <a href="{{ route('plans.index') }}" class="sky-btn"><i class="bi bi-wifi"></i> Acheter un ticket</a>
                <a href="#plans" class="sky-btn-outline"><i class="bi bi-grid-3x3-gap"></i> Voir les forfaits</a>
            </div>
        </div>

        <div class="hero-visual" aria-label="Illustration SkyConnect">
            <div class="cloud-core"></div>
            <div class="wifi-rings">
                <span style="--i:0"></span>
                <span style="--i:1"></span>
                <span style="--i:2"></span>
            </div>
            <div class="floating-chip chip-left"><i class="bi bi-lightning-charge-fill"></i> Paiement rapide</div>
            <div class="floating-chip chip-right"><i class="bi bi-shield-check"></i> Ticket securise</div>
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
                    <div class="sky-btn" style="margin-top:20px;width:100%;">Activer le pass</div>
                </div>
            </div>
        </div>
    </section>

    <section id="plans" class="sky-section">
        <div class="sky-container">
            <div class="section-head">
                <div>
                    <span class="eyebrow"><span class="eyebrow-dot"></span> Forfaits populaires</span>
                    <h2 class="section-title">Choisissez votre pass Wi-Fi</h2>
                </div>
                <p class="section-copy">Des tickets prepayes clairs, accessibles et adaptes aux usages courts comme aux connexions longue duree.</p>
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
                        <a href="{{ route('plans.index') }}" class="sky-btn" style="margin-top:auto;">Acheter</a>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section id="coverage" class="sky-section">
        <div class="sky-container">
            <div class="feature-grid">
                <div class="premium-card">
                    <h3><i class="bi bi-phone"></i> Mobile Money</h3>
                    <p class="section-copy">Orange Money, MTN Mobile Money et Campay prets pour un parcours fluide.</p>
                </div>
                <div class="premium-card">
                    <h3><i class="bi bi-router"></i> Hotspots</h3>
                    <p class="section-copy">Gestion des routeurs, tickets Mikhmon et stocks de connexion.</p>
                </div>
                <div class="premium-card">
                    <h3><i class="bi bi-graph-up-arrow"></i> Analytics</h3>
                    <p class="section-copy">Suivi des ventes, revenus, connexions et performances reseau.</p>
                </div>
                <div class="premium-card">
                    <h3><i class="bi bi-shield-lock"></i> Securite</h3>
                    <p class="section-copy">Roles separes, acces client securise et tickets consultables uniquement par leur proprietaire.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="sky-section">
        <div class="sky-container form-shell">
            <div>
                <span class="eyebrow"><span class="eyebrow-dot"></span> Portail captif</span>
                <h2 class="section-title">Une experience claire au moment de se connecter</h2>
                <p class="section-copy">Le portail MikroTik peut rester dans le routeur tout en gardant une identite SkyConnect coherente : logo, code ticket et lien d'achat.</p>
            </div>
            <div class="portal-preview">
                <div class="portal-box">
                    <img src="{{ asset('images/logo-skyconnect.PNG') }}" alt="SkyConnect" style="width:190px;background:white;border-radius:16px;padding:10px;">
                    <h3>Bienvenue sur SkyConnect</h3>
                    <input class="modern-input" style="width:100%;margin:12px 0;" placeholder="Code ticket">
                    <button class="sky-btn" style="width:100%;">Se connecter</button>
                    <p style="margin:14px 0 0;">Acheter un ticket</p>
                </div>
            </div>
        </div>
    </section>

    <section id="support" class="sky-section">
        <div class="sky-container">
            <div class="section-head">
                <div>
                    <span class="eyebrow"><span class="eyebrow-dot"></span> Application mobile</span>
                    <h2 class="section-title">Des ecrans mobiles prets pour une V1</h2>
                </div>
                <p class="section-copy">Splash screen, achat, paiement, historique et profil utilisateur dans une interface mobile-first.</p>
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
