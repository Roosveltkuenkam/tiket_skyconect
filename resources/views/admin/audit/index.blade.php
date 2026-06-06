@extends('layouts.admin')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <span class="eyebrow"><span class="eyebrow-dot"></span> {{ __('ui.audit.eyebrow') }}</span>
        <h3>{{ __('ui.audit.title') }}</h3>
        <p>{{ __('ui.audit.subtitle') }}</p>
    </div>
</div>

<div class="panel-card mb-4">
    <form method="GET" action="{{ route('admin.audit.index') }}" class="row g-3">
        <div class="col-md-3">
            <label class="form-label">{{ __('ui.audit.user') }}</label>
            <select name="user_id" class="form-control">
                <option value="">{{ __('ui.audit.all') }}</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                        {{ $user->name }} - {{ $user->email }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">{{ __('ui.audit.action') }}</label>
            <select name="action" class="form-control">
                <option value="">{{ __('ui.audit.all_feminine') }}</option>
                @foreach($actions as $action)
                    <option value="{{ $action }}" {{ request('action') === $action ? 'selected' : '' }}>{{ $action }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">{{ __('ui.audit.resource') }}</label>
            <select name="resource_type" class="form-control">
                <option value="">{{ __('ui.audit.all_feminine') }}</option>
                @foreach($resources as $resource)
                    <option value="{{ $resource }}" {{ request('resource_type') === $resource ? 'selected' : '' }}>
                        {{ class_basename($resource) }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">{{ __('ui.audit.ip') }}</label>
            <input type="text" name="ip" class="form-control" value="{{ request('ip') }}" placeholder="127.0.0.1">
        </div>
        <div class="col-md-3">
            <label class="form-label">{{ __('ui.audit.from') }}</label>
            <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
        </div>
        <div class="col-md-3">
            <label class="form-label">{{ __('ui.audit.to') }}</label>
            <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
        </div>
        <div class="col-md-3 d-flex align-items-end">
            <a href="{{ route('admin.audit.index') }}" class="btn btn-outline-primary w-100">{{ __('ui.audit.reset') }}</a>
        </div>
        <div class="col-md-3 d-flex align-items-end">
            <button class="sky-btn w-100">{{ __('ui.audit.filter') }}</button>
        </div>
    </form>
</div>

<div class="panel-card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>{{ __('ui.audit.date') }}</th>
                    <th>{{ __('ui.audit.user') }}</th>
                    <th>{{ __('ui.audit.action') }}</th>
                    <th>{{ __('ui.audit.resource') }}</th>
                    <th>{{ __('ui.audit.ip') }}</th>
                    <th>{{ __('ui.audit.route') }}</th>
                    <th>{{ __('ui.audit.details') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td>{{ $log->created_at }}</td>
                        <td>
                            <strong>{{ optional($log->user)->name ?: __('ui.audit.system') }}</strong><br>
                            <small>{{ optional($log->user)->email }}</small>
                        </td>
                        <td><span class="badge text-bg-primary">{{ $log->action }}</span></td>
                        <td>
                            {{ $log->resource_type ? class_basename($log->resource_type) : '-' }}
                            @if($log->resource_id)
                                #{{ $log->resource_id }}
                            @endif
                        </td>
                        <td>{{ $log->ip_address ?: '-' }}</td>
                        <td>{{ $log->route_name ?: '-' }}<br><small>{{ $log->method }}</small></td>
                        <td>
                            @if($log->safe_details)
                                <details>
                                    <summary>{{ __('ui.audit.view_summary') }}</summary>
                                    <dl class="mb-0 mt-2 audit-details">
                                        @foreach($log->safe_details as [$label, $value])
                                            <dt>{{ $label }}</dt>
                                            <dd>{{ $value }}</dd>
                                        @endforeach
                                    </dl>
                                </details>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">{{ __('ui.audit.no_logs') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $logs->links() }}
</div>
@endsection
