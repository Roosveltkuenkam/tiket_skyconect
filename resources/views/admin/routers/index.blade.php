@extends('layouts.admin')

@section('content')
@php
    $currentArea = $routeArea ?? 'admin';
@endphp
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <span class="eyebrow"><span class="eyebrow-dot"></span> RouterOS ready</span>
        <h3>{{ __('ui.common.routers') }}</h3>
        <p>{{ __('ui.routers.registered_count', ['count' => $routers->count()]) }}</p>
    </div>

    <a href="{{ route(($routeArea ?? 'admin') . '.routers.create') }}" class="sky-btn">
        <i class="bi bi-plus-circle"></i> {{ __('ui.routers.add') }}
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="panel-card router-management-card">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">{{ __('ui.routers.management') }}</h4>
        <span class="badge text-bg-primary">MikroTik</span>
    </div>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>{{ __('ui.routers.name') }}</th>
                    <th>{{ __('ui.routers.dns') }}</th>
                    <th>{{ __('ui.routers.assistance') }}</th>
                    <th>{{ __('ui.routers.platform') }}</th>
                    <th>{{ __('ui.routers.status') }}</th>
                    <th>Integration</th>
                    <th>{{ __('ui.admin_tickets.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($routers as $router)
                    <tr>
                        <td>
                            <div class="router-name-cell">
                                <strong>{{ $router->name }}</strong>
                                <span>{{ $router->location ?: __('ui.routers.location_missing') }}</span>
                            </div>
                        </td>
                        <td>{{ $router->dns ?: '-' }}</td>
                        <td>{{ $router->assistance_phone ?: '-' }}</td>
                        <td><span class="badge text-bg-info">{{ $router->platform }}</span></td>
                        <td>
                            @if($router->status === 'active')
                                <span class="badge text-bg-success">{{ __('ui.portal.online') }}</span>
                            @else
                                <span class="badge text-bg-danger">{{ __('ui.routers.offline') }}</span>
                            @endif
                        </td>
                        <td>
                            @if($router->public_slug)
                                @php
                                    $portalUrl = route('portal.show', $router->public_slug);
                                @endphp
                                <div class="router-integration-cell">
                                    <code>{{ $router->integration_key ? substr($router->integration_key, 0, 3) . str_repeat('*', max(strlen($router->integration_key) - 5, 3)) . substr($router->integration_key, -2) : '-' }}</code>
                                    <div class="router-button-row">
                                        <a class="router-mini-btn" href="{{ $portalUrl }}" target="_blank">
                                            <i class="bi bi-box-arrow-up-right"></i> Portail
                                        </a>
                                        <button class="router-mini-btn" type="button" onclick="navigator.clipboard && navigator.clipboard.writeText('{{ $portalUrl }}')">
                                            <i class="bi bi-clipboard"></i> Copier
                                        </button>
                                        <button class="router-mini-btn primary" type="button" data-bs-toggle="modal" data-bs-target="#routerIntegration{{ $router->id }}">
                                            <i class="bi bi-terminal"></i> Code
                                        </button>
                                    </div>
                                </div>
                            @else
                                <span class="text-muted">{{ __('ui.portal.link_pending') }}</span>
                            @endif
                        </td>
                        <td>
                            @if($currentArea === 'dashboard')
                                <div class="router-action-row">
                                    <a href="{{ route('dashboard.routers.edit', $router) }}" class="router-action-btn edit" title="Modifier">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    @if($router->status === 'active')
                                        <form method="POST" action="{{ route('dashboard.routers.deactivate', $router) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button class="router-action-btn power" title="Desactiver">
                                                <i class="bi bi-power"></i>
                                            </button>
                                        </form>
                                    @endif
                                    <form method="POST" action="{{ route('dashboard.routers.destroy', $router) }}" onsubmit="return confirm('Supprimer ce routeur ? Cette action est bloquee si des tickets ou ventes existent.');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="router-action-btn delete" title="Supprimer">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@foreach($routers as $router)
    @if($router->public_slug)
        @php
            $portalUrl = route('portal.show', $router->public_slug);
            $portalHost = parse_url($portalUrl, PHP_URL_HOST);
            $portalHtml = '<a href="' . $portalUrl . '" style="display:block;padding:14px 18px;border-radius:14px;background:#1E88E5;color:#fff;text-align:center;font-weight:800;text-decoration:none;">Acheter un ticket Wi-Fi</a>';
            $routerOsScript = '/ip hotspot walled-garden add dst-host=' . $portalHost . ' comment="SkyConnect portal"';
        @endphp
        <div class="modal fade" id="routerIntegration{{ $router->id }}" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content" style="border-radius:22px;">
                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title">Integration {{ $router->name }}</h5>
                            <small class="text-muted">Copiez le lien, le bloc HTML ou la commande RouterOS selon votre usage.</small>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="integration-snippet mb-3">
                            <label>Lien public pour les clients</label>
                            <div class="input-group">
                                <input class="form-control" id="portalUrl{{ $router->id }}" value="{{ $portalUrl }}" readonly>
                                <button class="btn btn-outline-primary" type="button" onclick="navigator.clipboard && navigator.clipboard.writeText(document.getElementById('portalUrl{{ $router->id }}').value)">Copier</button>
                            </div>
                        </div>
                        <div class="integration-snippet mb-3">
                            <label>Code a mettre dans le portail captif MikroTik</label>
                            <textarea class="form-control" id="portalHtml{{ $router->id }}" rows="3" readonly>{{ $portalHtml }}</textarea>
                            <button class="btn btn-outline-primary mt-2" type="button" onclick="navigator.clipboard && navigator.clipboard.writeText(document.getElementById('portalHtml{{ $router->id }}').value)">Copier le HTML</button>
                        </div>
                        <div class="integration-snippet">
                            <label>Commande terminal RouterOS pour autoriser le domaine SkyConnect</label>
                            <textarea class="form-control" id="routerOs{{ $router->id }}" rows="2" readonly>{{ $routerOsScript }}</textarea>
                            <button class="btn btn-outline-primary mt-2" type="button" onclick="navigator.clipboard && navigator.clipboard.writeText(document.getElementById('routerOs{{ $router->id }}').value)">Copier la commande</button>
                        </div>
                        <div class="alert alert-info mt-3 mb-0">
                            Apres achat, SkyConnect redirige automatiquement le client vers son ticket securise. Le lien public ci-dessus est donc celui a mettre en avant dans le portail captif.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endforeach
@endsection
