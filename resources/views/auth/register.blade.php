@extends('layouts.public')

@section('title', 'Inscription - SkyConnect')

@section('content')
<main class="sky-section">
    <div class="sky-container form-shell">
        <section>
            <span class="eyebrow"><span class="eyebrow-dot"></span> Onboarding</span>
            <h1 class="section-title">Lancez votre espace Wi-Fi</h1>
            <p class="section-copy">Creez votre compte proprietaire, ajoutez vos routeurs, configurez vos plans et commencez a vendre vos tickets SkyConnect.</p>
            <div class="mobile-card" style="margin-top:28px;max-width:340px;">
                <div class="mobile-screen">
                    <div>
                        <div class="screen-title">Nouveau compte</div>
                        <div class="mock-bar" style="width:85%;"></div>
                        <div class="mock-bar" style="width:64%;"></div>
                    </div>
                    <div class="ticket-mini">
                        <strong>Dashboard pret</strong>
                        <div class="mock-bar"></div>
                    </div>
                </div>
            </div>
        </section>

        <section class="premium-card">
            <h2 style="margin-top:0;color:#0f2747;">Compte proprietaire WiFi</h2>

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('register.store') }}">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Nom</label>
                    <input type="text" name="name" class="modern-input" style="width:100%;" value="{{ old('name') }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="modern-input" style="width:100%;" value="{{ old('email') }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Telephone</label>
                    <input type="text" name="phone" class="modern-input" style="width:100%;" value="{{ old('phone') }}">
                </div>
                <div class="mb-3">
                    <label class="form-label">Commerce</label>
                    <input type="text" name="business_name" class="modern-input" style="width:100%;" value="{{ old('business_name') }}">
                </div>
                <div class="mb-3">
                    <label class="form-label">Ville / Pays</label>
                    <div class="field-grid-two">
                        <input type="text" name="city" class="modern-input" value="{{ old('city') }}" placeholder="Ville">
                        <input type="text" name="country" class="modern-input" value="{{ old('country') }}" placeholder="Pays">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Mot de passe</label>
                    <input type="password" name="password" class="modern-input" style="width:100%;" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Confirmer le mot de passe</label>
                    <input type="password" name="password_confirmation" class="modern-input" style="width:100%;" required>
                </div>

                <button type="submit" class="sky-btn" style="width:100%;">Creer mon espace</button>
            </form>

            <p style="margin:18px 0 0;text-align:center;">
                <a href="{{ route('login') }}" style="color:#0d47a1;font-weight:800;">J'ai deja un compte</a>
            </p>
        </section>
    </div>
</main>
@endsection
