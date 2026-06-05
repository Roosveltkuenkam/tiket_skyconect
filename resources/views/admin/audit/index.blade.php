@extends('layouts.admin')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <span class="eyebrow"><span class="eyebrow-dot"></span> Super admin</span>
        <h3>Journal d'activite</h3>
        <p>Tracez les actions sensibles: connexions, clients, tickets, paiements, remboursements et parametres.</p>
    </div>
</div>

<div class="panel-card mb-4">
    <form method="GET" action="{{ route('admin.audit.index') }}" class="row g-3">
        <div class="col-md-3">
            <label class="form-label">Utilisateur</label>
            <select name="user_id" class="form-control">
                <option value="">Tous</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                        {{ $user->name }} - {{ $user->email }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Action</label>
            <select name="action" class="form-control">
                <option value="">Toutes</option>
                @foreach($actions as $action)
                    <option value="{{ $action }}" {{ request('action') === $action ? 'selected' : '' }}>{{ $action }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Ressource</label>
            <select name="resource_type" class="form-control">
                <option value="">Toutes</option>
                @foreach($resources as $resource)
                    <option value="{{ $resource }}" {{ request('resource_type') === $resource ? 'selected' : '' }}>
                        {{ class_basename($resource) }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">IP</label>
            <input type="text" name="ip" class="form-control" value="{{ request('ip') }}" placeholder="127.0.0.1">
        </div>
        <div class="col-md-3">
            <label class="form-label">Du</label>
            <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
        </div>
        <div class="col-md-3">
            <label class="form-label">Au</label>
            <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
        </div>
        <div class="col-md-3 d-flex align-items-end">
            <a href="{{ route('admin.audit.index') }}" class="btn btn-outline-primary w-100">Reinitialiser</a>
        </div>
        <div class="col-md-3 d-flex align-items-end">
            <button class="sky-btn w-100">Filtrer</button>
        </div>
    </form>
</div>

<div class="panel-card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Utilisateur</th>
                    <th>Action</th>
                    <th>Ressource</th>
                    <th>IP</th>
                    <th>Route</th>
                    <th>Details</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td>{{ $log->created_at }}</td>
                        <td>
                            <strong>{{ optional($log->user)->name ?: 'Systeme' }}</strong><br>
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
                            @if($log->details)
                                <details>
                                    <summary>Voir</summary>
                                    <dl class="mb-0 mt-2">
                                        @foreach($log->details as $key => $value)
                                            <dt>{{ str_replace('_', ' ', $key) }}</dt>
                                            <dd>
                                                @if(is_array($value))
                                                    <pre class="mb-0" style="white-space:pre-wrap;">{{ json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                                @else
                                                    {{ $value }}
                                                @endif
                                            </dd>
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
                        <td colspan="7" class="text-center text-muted py-4">Aucun log trouve.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $logs->links() }}
</div>
@endsection
