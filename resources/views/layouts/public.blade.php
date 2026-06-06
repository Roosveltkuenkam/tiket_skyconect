<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @php($platformName = \App\Services\SettingManager::get('platform.name', 'SkyConnect'))
    @php($platformLogo = \App\Services\SettingManager::get('platform.logo_path', 'images/logo-skyconnect.PNG'))
    <title>@yield('title', $platformName)</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Poppins:wght@700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <script>
        (function () {
            var theme = localStorage.getItem('skyconnect-theme');
            if (! theme && window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                theme = 'dark';
            }
            document.documentElement.setAttribute('data-theme', theme === 'dark' ? 'dark' : 'light');
        })();
    </script>
    <link rel="stylesheet" href="{{ asset('skyconnect.css') }}">
</head>
<body>
    <div class="sky-page">
        <header class="sky-nav">
            <div class="sky-container sky-nav-inner">
                <a href="{{ url('/') }}" class="sky-brand">
                    <img src="{{ asset($platformLogo) }}" alt="{{ $platformName }}">
                </a>

                <nav class="sky-nav-links">
                    <a href="{{ url('/') }}">{{ __('ui.nav.home') }}</a>
                    <a href="{{ route('plans.index') }}">{{ __('ui.nav.plans') }}</a>
                    <a href="{{ url('/#coverage') }}">{{ __('ui.nav.coverage') }}</a>
                    <a href="{{ url('/#support') }}">{{ __('ui.nav.support') }}</a>
                    <a href="{{ route('legal.terms') }}">{{ __('ui.nav.terms') }}</a>
                    <a href="{{ route('legal.privacy') }}">{{ __('ui.nav.privacy') }}</a>
                    <a href="{{ route('login') }}">{{ __('ui.nav.login') }}</a>
                </nav>

                <div class="d-flex align-items-center gap-2">
                    @include('partials.language-switcher')
                    <button type="button" class="theme-toggle" data-theme-toggle aria-label="Changer le theme">
                        <i class="bi bi-moon-stars-fill theme-icon-light"></i>
                        <i class="bi bi-sun-fill theme-icon-dark"></i>
                    </button>
                    <a href="{{ route('plans.index') }}" class="sky-btn">{{ __('ui.nav.buy') }}</a>
                </div>
            </div>
        </header>

        @yield('content')
    </div>
    <script src="{{ asset('theme.js') }}"></script>
</body>
</html>
