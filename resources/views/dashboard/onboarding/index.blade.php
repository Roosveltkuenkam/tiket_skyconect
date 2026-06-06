@extends('layouts.admin')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <span class="eyebrow"><span class="eyebrow-dot"></span> {{ __('ui.dashboard_pages.first_sale') }}</span>
        <h3>{{ __('ui.dashboard_pages.onboarding_title') }}</h3>
        <p>{{ __('ui.dashboard_pages.onboarding_subtitle') }}</p>
    </div>
    <div class="text-end">
        <div class="stat-title">{{ __('ui.dashboard_pages.progress') }}</div>
        <div class="stat-value">{{ $progress }}%</div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <div class="panel-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="mb-0">{{ __('ui.dashboard_pages.launch_checklist') }}</h4>
                <span class="badge text-bg-primary">{{ __('ui.dashboard_pages.steps_count', ['completed' => $completedSteps, 'total' => count($steps)]) }}</span>
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
                                        <span class="badge text-bg-secondary">{{ __('ui.dashboard_pages.step', ['number' => $index + 1]) }}</span>
                                    @endif
                                    <strong>{{ $step['title'] }}</strong>
                                </div>
                                <p class="section-copy mb-0">{{ $step['description'] }}</p>

                                @if(! empty($step['sale_link']))
                                    <div class="mt-3">
                                        <label class="form-label">{{ __('ui.dashboard_pages.portal_link_ready') }}</label>
                                        <div class="input-group">
                                            <input class="form-control" id="saleLinkInput" value="{{ $step['sale_link'] }}" readonly>
                                            <button class="btn btn-outline-primary" type="button" onclick="navigator.clipboard && navigator.clipboard.writeText(document.getElementById('saleLinkInput').value)">
                                                {{ __('ui.dashboard_pages.copy') }}
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
            <h4>{{ __('ui.dashboard_pages.space_status') }}</h4>
            <div class="ticket-mini">
                <div class="stat-title">{{ __('ui.common.routers') }}</div>
                <div class="stat-value">{{ $routersCount }}</div>
            </div>
            <div class="ticket-mini mt-3">
                <div class="stat-title">{{ __('ui.nav.plans') }}</div>
                <div class="stat-value">{{ $plansCount }}</div>
            </div>
            <div class="ticket-mini mt-3">
                <div class="stat-title">{{ __('ui.admin_dashboard.tickets_available') }}</div>
                <div class="stat-value">{{ $availableTicketsCount }}</div>
            </div>
        </div>

        <div class="panel-card">
            <h4>{{ __('ui.dashboard_pages.objective') }}</h4>
            <p class="section-copy">
                {{ __('ui.dashboard_pages.objective_copy') }}
            </p>
            @if($saleLink)
                <a href="{{ $saleLink }}" class="sky-btn w-100" target="_blank">{{ __('ui.dashboard_pages.test_portal_link') }}</a>
            @else
                <a href="{{ route('dashboard.routers.create') }}" class="sky-btn w-100">{{ __('ui.dashboard_pages.start') }}</a>
            @endif
        </div>
    </div>
</div>
@endsection
