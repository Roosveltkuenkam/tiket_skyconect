@extends('layouts.public')

@section('title', __('ui.auth.register_title'))

@section('content')
<main class="sky-section">
    <div class="sky-container form-shell">
        <section>
            <span class="eyebrow"><span class="eyebrow-dot"></span> {{ __('ui.common.onboarding') }}</span>
            <h1 class="section-title">{{ __('ui.auth.register_heading') }}</h1>
            <p class="section-copy">{{ __('ui.auth.register_copy') }}</p>
            <div class="mobile-card" style="margin-top:28px;max-width:340px;">
                <div class="mobile-screen">
                    <div>
                        <div class="screen-title">{{ __('ui.auth.new_account') }}</div>
                        <div class="mock-bar" style="width:85%;"></div>
                        <div class="mock-bar" style="width:64%;"></div>
                    </div>
                    <div class="ticket-mini">
                        <strong>{{ __('ui.auth.dashboard_ready') }}</strong>
                        <div class="mock-bar"></div>
                    </div>
                </div>
            </div>
        </section>

        <section class="premium-card">
            <h2 class="theme-title" style="margin-top:0;">{{ __('ui.auth.owner_account') }}</h2>

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
                    <label class="form-label">{{ __('ui.auth.name') }}</label>
                    <input type="text" name="name" class="modern-input" style="width:100%;" value="{{ old('name') }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ __('ui.auth.email') }}</label>
                    <input type="email" name="email" class="modern-input" style="width:100%;" value="{{ old('email') }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ __('ui.auth.phone') }}</label>
                    <input type="text" name="phone" class="modern-input" style="width:100%;" value="{{ old('phone') }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ __('ui.auth.business') }}</label>
                    <input type="text" name="business_name" class="modern-input" style="width:100%;" value="{{ old('business_name') }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ __('ui.auth.business_type') }}</label>
                    <select name="business_type" class="modern-input" style="width:100%;" required>
                        <option value="">{{ __('ui.auth.choose_business_type') }}</option>
                        @foreach(__('ui.auth.business_types') as $value => $label)
                            <option value="{{ $value }}" {{ old('business_type') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ __('ui.auth.city_country') }}</label>
                    <div class="field-grid-two">
                        <input type="text" name="city" class="modern-input" value="{{ old('city') }}" placeholder="{{ __('ui.auth.city') }}">
                        <input type="text" name="country" class="modern-input" value="{{ old('country', config('app.default_country', 'Cameroun')) }}" placeholder="{{ __('ui.auth.country') }}">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ __('ui.auth.password') }}</label>
                    <input type="password" name="password" class="modern-input" style="width:100%;" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ __('ui.auth.confirm_password') }}</label>
                    <input type="password" name="password_confirmation" class="modern-input" style="width:100%;" required>
                </div>

                <label class="legal-consent mb-3">
                    <input type="checkbox" name="terms_accepted" value="1" {{ old('terms_accepted') ? 'checked' : '' }} required>
                    <span>
                        {!! __('ui.auth.accept_terms', [
                            'terms' => '<a href="' . route('legal.terms') . '" target="_blank" rel="noopener">' . __('ui.auth.terms') . '</a>',
                            'privacy' => '<a href="' . route('legal.privacy') . '" target="_blank" rel="noopener">' . __('ui.auth.privacy') . '</a>',
                        ]) !!}
                    </span>
                </label>

                <button type="submit" class="sky-btn" style="width:100%;">{{ __('ui.auth.create_space') }}</button>
            </form>

            <p style="margin:18px 0 0;text-align:center;">
                <a href="{{ route('login') }}" class="theme-link-icon" style="font-weight:800;">{{ __('ui.auth.already_account') }}</a>
            </p>
        </section>
    </div>
</main>
@endsection
