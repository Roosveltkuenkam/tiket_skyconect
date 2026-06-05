<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SkyConnect Dashboard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Poppins:wght@700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('skyconnect.css') }}">
</head>
<body class="admin-shell">
    @php($area = $routeArea ?? 'admin')
    @php($platformName = \App\Services\SettingManager::get('platform.name', 'SkyConnect'))
    @php($platformLogo = \App\Services\SettingManager::get('platform.logo_path', 'images/logo-skyconnect.PNG'))
    @php($unreadAdminNotifications = $area === 'admin' && auth()->check() && auth()->user()->canAccessBackOffice('notifications.view') ? \App\Models\AdminNotification::unread()->count() : 0)
    @php($unreadClientNotifications = $area === 'dashboard' && auth()->check() ? \App\Models\ClientNotification::visibleInDashboard()->where('user_id', auth()->id())->unread()->count() : 0)

    <aside class="admin-sidebar">
        <a href="{{ route($area . '.dashboard') }}" class="sky-brand" style="margin-bottom:28px;">
            <img src="{{ asset($platformLogo) }}" alt="{{ $platformName }}">
        </a>

        <a href="{{ route($area . '.dashboard') }}" class="menu-link {{ request()->is($area) || request()->is($area . '/*') && request()->segment(2) === null ? 'active' : '' }}">
            <i class="bi bi-grid-1x2-fill"></i> Dashboard
        </a>
        @if($area === 'dashboard')
            <a href="{{ route('dashboard.onboarding.index') }}" class="menu-link {{ request()->is('dashboard/onboarding*') ? 'active' : '' }}">
                <i class="bi bi-rocket-takeoff-fill"></i> Onboarding
            </a>
        @endif
        @if($area === 'dashboard' || auth()->user()->canAccessBackOffice('plans.manage'))
            <a href="{{ route($area . '.plans.index') }}" class="menu-link {{ request()->is($area . '/plans') || request()->is($area . '/tarifs*') ? 'active' : '' }}">
                <i class="bi bi-tags-fill"></i> Plans
            </a>
        @endif
        @if($area === 'dashboard' || auth()->user()->canAccessBackOffice('tickets.view'))
            <a href="{{ route($area . '.tickets.index') }}" class="menu-link {{ request()->is($area . '/tickets*') ? 'active' : '' }}">
                <i class="bi bi-ticket-perforated-fill"></i> Tickets
            </a>
        @endif
        @if($area === 'dashboard' || auth()->user()->canAccessBackOffice('orders.view'))
            <a href="{{ route($area . '.orders.index') }}" class="menu-link {{ request()->is($area . '/orders*') ? 'active' : '' }}">
                <i class="bi bi-cart-check-fill"></i> Ventes
            </a>
        @endif
        @if($area === 'dashboard' || auth()->user()->canAccessBackOffice('payments.view'))
            <a href="{{ route($area . '.payments.index') }}" class="menu-link {{ request()->is($area . '/payments*') ? 'active' : '' }}">
                <i class="bi bi-credit-card-2-front-fill"></i> Paiements
            </a>
        @endif
        @if($area === 'admin' && auth()->user()->canAccessBackOffice('refunds.view'))
            <a href="{{ route('admin.refunds.index') }}" class="menu-link {{ request()->is('admin/refunds*') ? 'active' : '' }}">
                <i class="bi bi-arrow-counterclockwise"></i> Remboursements
            </a>
        @endif
        @if($area === 'admin' && auth()->user()->canAccessBackOffice('subscriptions.view'))
            <a href="{{ route('admin.subscriptions.index') }}" class="menu-link {{ request()->is('admin/subscriptions*') ? 'active' : '' }}">
                <i class="bi bi-gem"></i> Abonnements
            </a>
        @endif
        @if($area === 'admin' && auth()->user()->canAccessBackOffice('notifications.view'))
            <a href="{{ route('admin.notifications.index') }}" class="menu-link {{ request()->is('admin/notifications*') ? 'active' : '' }}">
                <i class="bi bi-bell-fill"></i> Notifications
                @if($unreadAdminNotifications > 0)
                    <span class="badge text-bg-danger ms-auto">{{ $unreadAdminNotifications }}</span>
                @endif
            </a>
        @endif
        @if($area === 'admin' && auth()->user()->canAccessBackOffice('client_notifications.view'))
            <a href="{{ route('admin.client_notifications.index') }}" class="menu-link {{ request()->is('admin/client-notifications*') ? 'active' : '' }}">
                <i class="bi bi-envelope-paper-fill"></i> Messages clients
            </a>
        @endif
        @if($area === 'admin' && auth()->user()->canAccessBackOffice('audit.view'))
            <a href="{{ route('admin.audit.index') }}" class="menu-link {{ request()->is('admin/audit*') ? 'active' : '' }}">
                <i class="bi bi-shield-lock-fill"></i> Audit
            </a>
        @endif
        @if($area === 'admin' && auth()->user()->canAccessBackOffice('reports.view'))
            <a href="{{ route('admin.reports.finance') }}" class="menu-link {{ request()->is('admin/reports*') ? 'active' : '' }}">
                <i class="bi bi-bar-chart-fill"></i> Rapports
            </a>
        @endif
        @if($area === 'admin' && auth()->user()->canAccessBackOffice('support.view'))
            <a href="{{ route('admin.support.index') }}" class="menu-link {{ request()->is('admin/support*') ? 'active' : '' }}">
                <i class="bi bi-life-preserver"></i> Support
            </a>
        @endif
        @if($area === 'dashboard' || auth()->user()->canAccessBackOffice('routers.manage'))
            <a href="{{ route($area . '.routers.index') }}" class="menu-link {{ request()->is($area . '/routers*') || request()->is($area . '/routeurs*') ? 'active' : '' }}">
                <i class="bi bi-router-fill"></i> Routeurs
            </a>
        @endif
        @if($area === 'admin' && auth()->user()->canAccessBackOffice('clients.view'))
            <a href="{{ route('admin.clients.index') }}" class="menu-link {{ request()->is('admin/clients*') ? 'active' : '' }}">
                <i class="bi bi-people-fill"></i> Clients
            </a>
        @endif

        @if($area === 'dashboard')
            <a href="{{ route('dashboard.notifications.index') }}" class="menu-link {{ request()->is('dashboard/notifications*') ? 'active' : '' }}">
                <i class="bi bi-bell-fill"></i> Notifications
                @if($unreadClientNotifications > 0)
                    <span class="badge text-bg-danger ms-auto">{{ $unreadClientNotifications }}</span>
                @endif
            </a>
            <a href="{{ route('dashboard.support.index') }}" class="menu-link {{ request()->is('dashboard/support*') ? 'active' : '' }}">
                <i class="bi bi-life-preserver"></i> Support
            </a>
            <a href="{{ route('dashboard.settings.index') }}" class="menu-link">
                <i class="bi bi-gear-fill"></i> Parametres
            </a>
        @else
            @if(auth()->user()->canAccessBackOffice('settings.manage'))
                <a href="{{ route('admin.settings.index') }}" class="menu-link {{ request()->is('admin/settings*') ? 'active' : '' }}">
                    <i class="bi bi-gear-fill"></i> Parametres
                </a>
            @endif
        @endif

        @auth
            <form method="POST" action="{{ route('logout') }}" style="margin-top:28px;">
                @csrf
                <button type="submit" class="sky-btn-outline" style="width:100%;">
                    <i class="bi bi-box-arrow-right"></i> Deconnexion
                </button>
            </form>
        @endauth
    </aside>

    <main class="admin-main">
        <div class="admin-topbar">
            <div>
                <strong style="color:#0f2747;">{{ $area === 'admin' ? 'Back-office global' : 'Espace proprietaire WiFi' }}</strong>
                <div style="color:#64748b;font-size:14px;">{{ $platformName }} Enterprise Console</div>
            </div>
            <div class="d-flex align-items-center gap-3">
                <span class="badge text-bg-primary">{{ auth()->user()->roleLabel() }}</span>
                @if($area === 'admin' && auth()->user()->canAccessBackOffice('notifications.view'))
                    <a href="{{ route('admin.notifications.index') }}" class="position-relative" style="color:#0d47a1;">
                        <i class="bi bi-bell fs-5"></i>
                        @if($unreadAdminNotifications > 0)
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill text-bg-danger">{{ $unreadAdminNotifications }}</span>
                        @endif
                    </a>
                @elseif($area === 'dashboard')
                    <a href="{{ route('dashboard.notifications.index') }}" class="position-relative" style="color:#0d47a1;">
                        <i class="bi bi-bell fs-5"></i>
                        @if($unreadClientNotifications > 0)
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill text-bg-danger">{{ $unreadClientNotifications }}</span>
                        @endif
                    </a>
                @else
                    <i class="bi bi-bell fs-5" style="color:#0d47a1;"></i>
                @endif
                <i class="bi bi-person-circle fs-3" style="color:#0d47a1;"></i>
            </div>
        </div>

        @yield('content')
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
