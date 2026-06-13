<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('ui.common.dashboard') }} - SkyConnect</title>
    @php($platformLogo = \App\Services\SettingManager::get('platform.logo_path', 'images/logo-skyconnect.PNG'))
    <link rel="icon" type="image/png" href="{{ asset($platformLogo) }}">
    <link rel="apple-touch-icon" href="{{ asset($platformLogo) }}">
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
<body class="admin-shell">
    @php($area = $routeArea ?? 'admin')
    @php($platformName = \App\Services\SettingManager::get('platform.name', 'SkyConnect'))
    @php($unreadAdminNotifications = $area === 'admin' && auth()->check() && auth()->user()->canAccessBackOffice('notifications.view') ? \App\Models\AdminNotification::unread()->count() : 0)
    @php($unreadClientNotifications = $area === 'dashboard' && auth()->check() ? \App\Models\ClientNotification::visibleInDashboard()->where('user_id', auth()->id())->unread()->count() : 0)
    @php($currentClientSubscription = $area === 'dashboard' && auth()->check() ? auth()->user()->activeSubscription() : null)

    <aside class="admin-sidebar">
        <a href="{{ route($area . '.dashboard') }}" class="sky-brand" style="margin-bottom:28px;">
            <img src="{{ asset($platformLogo) }}" alt="{{ $platformName }}">
        </a>

        <a href="{{ route($area . '.dashboard') }}" class="menu-link {{ request()->is($area) || request()->is($area . '/*') && request()->segment(2) === null ? 'active' : '' }}">
            <i class="bi bi-grid-1x2-fill"></i> {{ __('ui.common.dashboard') }}
        </a>
        @if($area === 'dashboard')
            <a href="{{ route('dashboard.onboarding.index') }}" class="menu-link {{ request()->is('dashboard/onboarding*') ? 'active' : '' }}">
                <i class="bi bi-rocket-takeoff-fill"></i> {{ __('ui.common.onboarding') }}
            </a>
        @endif
        @if($area === 'dashboard' || auth()->user()->canAccessBackOffice('plans.manage'))
            <a href="{{ route($area . '.plans.index') }}" class="menu-link {{ request()->is($area . '/plans') || request()->is($area . '/tarifs*') ? 'active' : '' }}">
                <i class="bi bi-tags-fill"></i> {{ __('ui.nav.plans') }}
            </a>
        @endif
        @if($area === 'dashboard' || auth()->user()->canAccessBackOffice('tickets.view'))
            <a href="{{ route($area . '.tickets.index') }}" class="menu-link {{ request()->is($area . '/tickets*') ? 'active' : '' }}">
                <i class="bi bi-ticket-perforated-fill"></i> {{ __('ui.common.tickets') }}
            </a>
        @endif
        @if($area === 'dashboard' || auth()->user()->canAccessBackOffice('orders.view'))
            <a href="{{ route($area . '.orders.index') }}" class="menu-link {{ request()->is($area . '/orders*') ? 'active' : '' }}">
                <i class="bi bi-cart-check-fill"></i> {{ __('ui.common.orders') }}
            </a>
        @endif
        @if($area === 'dashboard' || auth()->user()->canAccessBackOffice('payments.view'))
            <a href="{{ route($area . '.payments.index') }}" class="menu-link {{ request()->is($area . '/payments*') ? 'active' : '' }}">
                <i class="bi bi-credit-card-2-front-fill"></i> {{ __('ui.common.payments') }}
            </a>
        @endif
        @if($area === 'dashboard')
            <a href="{{ route('dashboard.quota_topups.index') }}" class="menu-link {{ request()->is('dashboard/quota-topups*') ? 'active' : '' }}">
                <i class="bi bi-wallet2"></i> Quota
            </a>
            <a href="{{ route('dashboard.withdrawals.index') }}" class="menu-link {{ request()->is('dashboard/withdrawals*') ? 'active' : '' }}">
                <i class="bi bi-cash-coin"></i> Retraits
            </a>
        @elseif(auth()->user()->canAccessBackOffice('quota_topups.view'))
            <a href="{{ route('admin.quota_topups.index') }}" class="menu-link {{ request()->is('admin/quota-topups*') ? 'active' : '' }}">
                <i class="bi bi-wallet2"></i> Recharges quota
            </a>
        @endif
        @if($area === 'admin' && auth()->user()->canAccessBackOffice('withdrawals.view'))
            <a href="{{ route('admin.withdrawals.index') }}" class="menu-link {{ request()->is('admin/withdrawals*') ? 'active' : '' }}">
                <i class="bi bi-cash-coin"></i> Retraits
            </a>
        @endif
        @if($area === 'admin' && auth()->user()->canAccessBackOffice('refunds.view'))
            <a href="{{ route('admin.refunds.index') }}" class="menu-link {{ request()->is('admin/refunds*') ? 'active' : '' }}">
                <i class="bi bi-arrow-counterclockwise"></i> {{ __('ui.common.refunds') }}
            </a>
        @endif
        @if($area === 'admin' && auth()->user()->canAccessBackOffice('subscriptions.view'))
            <a href="{{ route('admin.subscriptions.index') }}" class="menu-link {{ request()->is('admin/subscriptions*') ? 'active' : '' }}">
                <i class="bi bi-gem"></i> {{ __('ui.common.subscriptions') }}
            </a>
        @endif
        @if($area === 'dashboard')
            <a href="{{ route('dashboard.subscriptions.index') }}" class="menu-link {{ request()->is('dashboard/subscriptions*') ? 'active' : '' }}">
                <i class="bi bi-gem"></i> {{ __('ui.common.subscriptions') }}
            </a>
        @endif
        @if($area === 'admin' && auth()->user()->canAccessBackOffice('client_notifications.view'))
            <a href="{{ route('admin.client_notifications.index') }}" class="menu-link {{ request()->is('admin/client-notifications*') ? 'active' : '' }}">
                <i class="bi bi-envelope-paper-fill"></i> {{ __('ui.common.client_messages') }}
            </a>
        @endif
        @if($area === 'admin' && auth()->user()->canAccessBackOffice('audit.view'))
            <a href="{{ route('admin.audit.index') }}" class="menu-link {{ request()->is('admin/audit*') ? 'active' : '' }}">
                <i class="bi bi-shield-lock-fill"></i> {{ __('ui.common.audit') }}
            </a>
        @endif
        @if($area === 'admin' && auth()->user()->canAccessBackOffice('reports.view'))
            <a href="{{ route('admin.reports.finance') }}" class="menu-link {{ request()->is('admin/reports*') ? 'active' : '' }}">
                <i class="bi bi-bar-chart-fill"></i> {{ __('ui.common.reports') }}
            </a>
            <a href="{{ route('admin.reports.quota') }}" class="menu-link {{ request()->is('admin/reports/quota*') ? 'active' : '' }}">
                <i class="bi bi-pie-chart-fill"></i> Rapport quota
            </a>
        @endif
        @if($area === 'admin' && auth()->user()->canAccessBackOffice('support.view'))
            <a href="{{ route('admin.support.index') }}" class="menu-link {{ request()->is('admin/support*') ? 'active' : '' }}">
                <i class="bi bi-life-preserver"></i> {{ __('ui.nav.support') }}
            </a>
        @endif
        @if($area === 'dashboard' || auth()->user()->canAccessBackOffice('routers.manage'))
            <a href="{{ route($area . '.routers.index') }}" class="menu-link {{ request()->is($area . '/routers*') || request()->is($area . '/routeurs*') ? 'active' : '' }}">
                <i class="bi bi-router-fill"></i> {{ __('ui.common.routers') }}
            </a>
        @endif
        @if($area === 'admin' && auth()->user()->canAccessBackOffice('clients.view'))
            <a href="{{ route('admin.clients.index') }}" class="menu-link {{ request()->is('admin/clients*') ? 'active' : '' }}">
                <i class="bi bi-people-fill"></i> {{ __('ui.common.clients') }}
            </a>
        @endif

        @if($area === 'dashboard')
            <a href="{{ route('dashboard.support.index') }}" class="menu-link {{ request()->is('dashboard/support*') ? 'active' : '' }}">
                <i class="bi bi-life-preserver"></i> {{ __('ui.nav.support') }}
            </a>
            <a href="{{ route('dashboard.settings.index') }}" class="menu-link">
                <i class="bi bi-gear-fill"></i> {{ __('ui.common.settings') }}
            </a>
        @else
            @if(auth()->user()->canAccessBackOffice('settings.manage'))
                <a href="{{ route('admin.settings.index') }}" class="menu-link {{ request()->is('admin/settings*') ? 'active' : '' }}">
                    <i class="bi bi-gear-fill"></i> {{ __('ui.common.settings') }}
                </a>
            @endif
        @endif

    </aside>

    <main class="admin-main">
        <div class="admin-topbar">
            <div>
                <strong>{{ $area === 'admin' ? __('ui.common.global_backoffice') : __('ui.common.owner_space') }}</strong>
                <div class="text-muted" style="font-size:14px;">{{ $platformName }} {{ __('ui.common.enterprise_console') }}</div>
            </div>
            <div class="d-flex align-items-center gap-3">
                @include('partials.language-switcher')
                <button type="button" class="theme-toggle" data-theme-toggle aria-label="{{ __('ui.common.change_theme') }}">
                    <i class="bi bi-moon-stars-fill theme-icon-light"></i>
                    <i class="bi bi-sun-fill theme-icon-dark"></i>
                </button>
                @if($area === 'dashboard' && $currentClientSubscription && $currentClientSubscription->plan)
                    <a href="{{ route('dashboard.subscriptions.index') }}" class="top-subscription-pill">
                        <i class="bi bi-gem"></i>
                        <span>{{ __('ui.subscriptions_page.plan_names.' . $currentClientSubscription->plan->slug) }}</span>
                    </a>
                @endif
                <span class="badge text-bg-primary">{{ auth()->user()->roleLabel() }}</span>
                @if($area === 'admin' && auth()->user()->canAccessBackOffice('notifications.view'))
                    <a href="{{ route('admin.notifications.index') }}" class="position-relative theme-link-icon">
                        <i class="bi bi-bell fs-5"></i>
                        @if($unreadAdminNotifications > 0)
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill text-bg-danger">{{ $unreadAdminNotifications }}</span>
                        @endif
                    </a>
                @elseif($area === 'dashboard')
                    <a href="{{ route('dashboard.notifications.index') }}" class="position-relative theme-link-icon">
                        <i class="bi bi-bell fs-5"></i>
                        @if($unreadClientNotifications > 0)
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill text-bg-danger">{{ $unreadClientNotifications }}</span>
                        @endif
                    </a>
                @else
                    <i class="bi bi-bell fs-5 theme-link-icon"></i>
                @endif
                <a href="{{ route($area . '.profile.edit') }}" class="theme-link-icon" title="{{ __('ui.profile.title') }}" aria-label="{{ __('ui.profile.title') }}">
                    <i class="bi bi-person-circle fs-3"></i>
                </a>
                <form method="POST" action="{{ route('logout') }}" class="m-0">
                    @csrf
                    <button type="submit" class="btn btn-outline-primary btn-sm d-inline-flex align-items-center gap-2">
                        <i class="bi bi-box-arrow-right"></i>
                        <span>{{ __('ui.common.logout') }}</span>
                    </button>
                </form>
            </div>
        </div>

        @yield('content')
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('theme.js') }}"></script>
</body>
</html>
