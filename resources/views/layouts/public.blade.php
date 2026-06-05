<!DOCTYPE html>
<html lang="fr">
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
                    <a href="{{ url('/') }}">Accueil</a>
                    <a href="{{ route('plans.index') }}">Forfaits</a>
                    <a href="{{ url('/#coverage') }}">Couverture</a>
                    <a href="{{ url('/#support') }}">Assistance</a>
                    <a href="{{ route('legal.terms') }}">CGU</a>
                    <a href="{{ route('legal.privacy') }}">Confidentialite</a>
                    <a href="{{ route('login') }}">Connexion</a>
                </nav>

                <a href="{{ route('plans.index') }}" class="sky-btn">Acheter</a>
            </div>
        </header>

        @yield('content')
    </div>
</body>
</html>
