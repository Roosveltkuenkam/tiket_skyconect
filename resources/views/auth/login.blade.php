@extends('layouts.public')

@section('title', 'Connexion - SkyConnect')

@section('content')
<main class="sky-section">
    <div class="sky-container form-shell">
        <section>
            <span class="eyebrow"><span class="eyebrow-dot"></span> Espace securise</span>
            <h1 class="section-title">Connectez-vous a SkyConnect</h1>
            <p class="section-copy">Accedez a votre dashboard, suivez vos ventes, gerez vos routeurs et importez vos tickets Wi-Fi.</p>
            <div class="portal-preview" style="margin-top:28px;">
                <div class="portal-box">
                    <img src="{{ asset('images/logo-skyconnect.PNG') }}" alt="SkyConnect" style="width:190px;background:white;border-radius:16px;padding:10px;">
                    <h3>Console SkyConnect</h3>
                    <p>Simple. Rapide. Securise.</p>
                </div>
            </div>
        </section>

        <section class="premium-card">
            <h2 style="margin-top:0;color:#0f2747;">Connexion</h2>

            @if($errors->any())
                <div class="alert alert-danger">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('login.store') }}">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="modern-input" style="width:100%;" value="{{ old('email') }}" required autofocus>
                </div>

                <div class="mb-3">
                    <label class="form-label">Mot de passe</label>
                    <input type="password" name="password" class="modern-input" style="width:100%;" required>
                </div>

                <label style="display:flex;gap:10px;align-items:center;margin-bottom:18px;">
                    <input type="checkbox" name="remember" value="1"> Se souvenir de moi
                </label>

                <button type="submit" class="sky-btn" style="width:100%;">Se connecter</button>
            </form>

            <p style="margin:18px 0 0;text-align:center;">
                <a href="{{ route('register') }}" style="color:#0d47a1;font-weight:800;">Creer un compte proprietaire WiFi</a>
            </p>
        </section>
    </div>
</main>
@endsection
