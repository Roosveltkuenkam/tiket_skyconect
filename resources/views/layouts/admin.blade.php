<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>SkyConnect Admin</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/skyconnect.css') }}">
    <style>
        body {
            background: #f5f8fb;
            font-family: Arial, sans-serif;
        }

        .sidebar {
            width: 250px;
            min-height: 100vh;
            background: white;
            border-right: 1px solid #e5e7eb;
            position: fixed;
            left: 0;
            top: 0;
            padding: 25px 18px;
        }

        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #00b8b8;
            margin-bottom: 35px;
        }

        .menu-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 13px 15px;
            margin-bottom: 8px;
            color: #334155;
            text-decoration: none;
            border-radius: 25px;
            font-size: 15px;
        }

        .menu-link:hover,
        .menu-link.active {
            background: #dff8fa;
            color: #00a6a6;
        }

        .main {
            margin-left: 250px;
            padding: 25px 30px;
        }

        .topbar {
            height: 65px;
            background: white;
            border-radius: 18px;
            padding: 15px 25px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .page-header {
            background: #dff8fa;
            border-radius: 22px;
            padding: 25px;
            margin-bottom: 25px;
        }

        .stat-card {
            background: white;
            border-radius: 22px;
            padding: 25px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            min-height: 140px;
        }

        .stat-title {
            color: #64748b;
            font-size: 15px;
            font-weight: bold;
        }

        .stat-value {
            font-size: 28px;
            font-weight: bold;
            color: #0f172a;
        }

        .panel-card {
            background: white;
            border-radius: 22px;
            padding: 25px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }

        .table thead {
            background: #00a99d;
            color: white;
        }

        .badge-paid {
            background: #d1fae5;
            color: #059669;
        }

        .badge-pending {
            background: #fef3c7;
            color: #d97706;
        }
    </style>
</head>
<body>

    <aside class="sidebar">
        <div class="logo">
            <img src="{{ asset('images/logo-skyconnect.png') }}" alt="SkyConnect" style="width:170px;">        </div>

        <a href="{{ route('admin.dashboard') }}" class="menu-link active">
            <i class="bi bi-grid"></i> Tableau de bord
        </a>

        <a href="{{ route('admin.routers.index') }}" class="menu-link">
            <i class="bi bi-router"></i> Routeurs
        </a>

        <a href="{{ route('admin.plans.index') }}" class="menu-link">
            <i class="bi bi-tags"></i> Tarifs
        </a>

        <a href="{{ route('admin.tickets.index') }}" class="menu-link">
            <i class="bi bi-ticket-perforated"></i> Tickets
        </a>

        <a href="#" class="menu-link">
            <i class="bi bi-cart-check"></i> Ventes
        </a>

        <a href="#" class="menu-link">
            <i class="bi bi-wallet2"></i> Paiements
        </a>

        <a href="#" class="menu-link">
            <i class="bi bi-gear"></i> Paramètres
        </a>
    </aside>

    <main class="main">
        <div class="topbar">
            <div>
                <i class="bi bi-list fs-4"></i>
            </div>

            <div>
                <span class="badge bg-info text-dark">STANDARD</span>
                <span class="ms-3">Français</span>
                <i class="bi bi-bell ms-3"></i>
                <i class="bi bi-person-circle ms-3 fs-4"></i>
            </div>
        </div>

        @yield('content')
    </main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>