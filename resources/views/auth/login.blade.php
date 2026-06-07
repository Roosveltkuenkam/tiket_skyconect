@extends('layouts.public')

@section('title', __('ui.auth.login_title'))

@section('content')
<main class="sky-section">
    <div class="sky-container form-shell">
        <section>
            <span class="eyebrow"><span class="eyebrow-dot"></span> {{ __('ui.auth.secure_space') }}</span>
            <h1 class="section-title">{{ __('ui.auth.login_heading') }}</h1>
            <p class="section-copy">{{ __('ui.auth.login_copy') }}</p>
            <div class="portal-preview" style="margin-top:28px;">
                <div class="portal-box">
                    <img src="{{ asset('images/logo-skyconnect.PNG') }}" alt="SkyConnect" class="logo-tile" style="width:190px;border-radius:16px;padding:10px;">
                    <h3>{{ __('ui.auth.console') }}</h3>
                    <p>{{ __('ui.auth.simple_fast_secure') }}</p>
                </div>
            </div>
        </section>

        <section class="premium-card">
            <h2 class="theme-title" style="margin-top:0;">{{ __('ui.auth.login_button') }}</h2>

            @if($errors->any())
                <div class="alert alert-danger">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('login.store') }}">
                @csrf

                <div class="mb-3">
                    <label class="form-label">{{ __('ui.auth.email') }}</label>
                    <input type="email" name="email" class="modern-input" style="width:100%;" value="{{ old('email') }}" required autofocus>
                </div>

                <div class="mb-3">
                    <label class="form-label">{{ __('ui.auth.password') }}</label>
                    <input type="password" name="password" class="modern-input" style="width:100%;" required>
                </div>

                <label style="display:flex;gap:10px;align-items:center;margin-bottom:18px;">
                    <input type="checkbox" name="remember" value="1"> {{ __('ui.auth.remember') }}
                </label>

                <button type="submit" class="sky-btn" style="width:100%;">{{ __('ui.auth.login_button') }}</button>
            </form>

            <p style="margin:18px 0 0;text-align:center;">
                <a href="{{ route('register') }}" class="theme-link-icon" style="font-weight:800;">{{ __('ui.auth.create_owner_account') }}</a>
            </p>
        </section>
    </div>
</main>
@endsection
