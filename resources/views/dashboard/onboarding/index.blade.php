@extends('layouts.admin')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <span class="eyebrow"><span class="eyebrow-dot"></span> Premiere vente</span>
        <h3>Onboarding SkyConnect</h3>
        <p>Suivez les etapes essentielles pour vendre votre premier ticket Wi-Fi.</p>
    </div>
    <div class="text-end">
        <div class="stat-title">Progression</div>
        <div class="stat-value">{{ $progress }}%</div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <div class="panel-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="mb-0">Checklist de lancement</h4>
                <span class="badge text-bg-primary">{{ $completedSteps }}/{{ count($steps) }} etapes</span>
            </div>

            <div class="progress mb-4" style="height:12px;border-radius:999px;">
                <div class="progress-bar" style="width: {{ $progress }}%; background: linear-gradient(135deg, #1e88e5, #0d47a1);"></div>
            </div>

            <div class="d-grid gap-3">
                @foreach($steps as $index => $step)
                    <div class="premium-card" style="box-shadow:none;">
                        <div class="d-flex justify-content-between gap-3 align-items-start">
                            <div>
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    @if($step['done'])
                                        <span class="badge text-bg-success">{{ $step['done_label'] }}</span>
                                    @else
                                        <span class="badge text-bg-secondary">Etape {{ $index + 1 }}</span>
                                    @endif
                                    <strong style="color:#0f2747;">{{ $step['title'] }}</strong>
                                </div>
                                <p class="section-copy mb-0">{{ $step['description'] }}</p>

                                @if(! empty($step['sale_link']))
                                    <div class="mt-3">
                                        <label class="form-label">Lien de vente pret</label>
                                        <div class="input-group">
                                            <input class="form-control" id="saleLinkInput" value="{{ $step['sale_link'] }}" readonly>
                                            <button class="btn btn-outline-primary" type="button" onclick="navigator.clipboard && navigator.clipboard.writeText(document.getElementById('saleLinkInput').value)">
                                                Copier
                                            </button>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <a href="{{ $step['action_url'] }}" class="{{ $step['done'] ? 'sky-btn-outline' : 'sky-btn' }}">
                                {{ $step['action_label'] }}
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="panel-card mb-4">
            <h4>Etat de votre espace</h4>
            <div class="ticket-mini">
                <div class="stat-title">Routeurs</div>
                <div class="stat-value">{{ $routersCount }}</div>
            </div>
            <div class="ticket-mini mt-3">
                <div class="stat-title">Forfaits</div>
                <div class="stat-value">{{ $plansCount }}</div>
            </div>
            <div class="ticket-mini mt-3">
                <div class="stat-title">Tickets disponibles</div>
                <div class="stat-value">{{ $availableTicketsCount }}</div>
            </div>
        </div>

        <div class="panel-card">
            <h4>Objectif</h4>
            <p class="section-copy">
                Une fois le lien de vente pret, placez-le dans votre portail captif MikroTik.
                Le client achete, paie, puis recoit son code de connexion.
            </p>
            @if($saleLink)
                <a href="{{ $saleLink }}" class="sky-btn w-100" target="_blank">Tester le lien de vente</a>
            @else
                <a href="{{ route('dashboard.routers.create') }}" class="sky-btn w-100">Demarrer</a>
            @endif
        </div>
    </div>
</div>
@endsection
