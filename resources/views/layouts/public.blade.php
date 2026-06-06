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
                    <a href="{{ url('/#features') }}">{{ __('ui.nav.features') }}</a>
                    <a href="{{ url('/#pricing') }}">{{ __('ui.nav.pricing') }}</a>
                    <a href="{{ url('/#how-it-works') }}">{{ __('ui.nav.how_it_works') }}</a>
                    <a href="{{ url('/#support') }}">{{ __('ui.nav.support') }}</a>
                    @guest
                        <a href="{{ route('login') }}">{{ __('ui.nav.login') }}</a>
                    @endguest
                </nav>

                <div class="d-flex align-items-center gap-2">
                    @include('partials.language-switcher')
                    <button type="button" class="theme-toggle" data-theme-toggle aria-label="{{ __('ui.common.change_theme') }}">
                        <i class="bi bi-moon-stars-fill theme-icon-light"></i>
                        <i class="bi bi-sun-fill theme-icon-dark"></i>
                    </button>
                    @guest
                        <a href="{{ route('register') }}" class="sky-btn">{{ __('ui.nav.create_account') }}</a>
                    @else
                        <form method="POST" action="{{ route('logout') }}" class="m-0">
                            @csrf
                            <button type="submit" class="sky-btn-outline">
                                <i class="bi bi-box-arrow-right"></i> {{ __('ui.common.logout') }}
                            </button>
                        </form>
                    @endguest
                </div>
            </div>
        </header>

        @yield('content')
    </div>
    <script src="{{ asset('theme.js') }}"></script>
</body>
</html>
