@extends('layouts.admin')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <span class="eyebrow"><span class="eyebrow-dot"></span> Centre d'alertes</span>
        <h3>Notifications admin</h3>
        <p>Suivez les evenements importants de la plateforme SkyConnect.</p>
    </div>
    <form method="POST" action="{{ route('admin.notifications.read_all') }}">
        @csrf
        @method('PATCH')
        <button class="btn btn-outline-primary">Tout marquer comme lu</button>
    </form>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="panel-card mb-4">
    <form method="GET" action="{{ route('admin.notifications.index') }}" class="row g-3">
        <div class="col-md-3">
            <label class="form-label">Type</label>
            <select name="type" class="form-control">
                <option value="">Tous les types</option>
                @foreach($types as $type)
                    <option value="{{ $type }}" {{ request('type') === $type ? 'selected' : '' }}>{{ $type }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Priorite</label>
            <select name="severity" class="form-control">
                <option value="">Toutes</option>
                <option value="info" {{ request('severity') === 'info' ? 'selected' : '' }}>Info</option>
                <option value="success" {{ request('severity') === 'success' ? 'selected' : '' }}>Succes</option>
                <option value="warning" {{ request('severity') === 'warning' ? 'selected' : '' }}>Attention</option>
                <option value="danger" {{ request('severity') === 'danger' ? 'selected' : '' }}>Critique</option>
            </select>
        </div>
        <div class="col-md-3 d-flex align-items-end">
            <label class="form-check d-flex align-items-center gap-2 mb-2">
                <input type="checkbox" name="unread" value="1" class="form-check-input" {{ request()->boolean('unread') ? 'checked' : '' }}>
                <span>Non lues seulement</span>
            </label>
        </div>
        <div class="col-md-1 d-flex align-items-end">
            <a href="{{ route('admin.notifications.index') }}" class="btn btn-outline-primary w-100">Reset</a>
        </div>
        <div class="col-md-2 d-flex align-items-end">
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
                    <th>Priorite</th>
                    <th>Type</th>
                    <th>Notification</th>
                    <th>Statut</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($notifications as $notification)
                    <tr>
                        <td>{{ $notification->created_at }}</td>
                        <td><span class="badge text-bg-{{ $notification->severity === 'danger' ? 'danger' : ($notification->severity === 'warning' ? 'warning' : ($notification->severity === 'success' ? 'success' : 'primary')) }}">{{ $notification->severity }}</span></td>
                        <td>{{ $notification->type }}</td>
                        <td>
                            <strong>{{ $notification->title }}</strong><br>
                            <span>{{ $notification->message }}</span>
                        </td>
                        <td>{{ $notification->read_at ? 'Lue' : 'Non lue' }}</td>
                        <td>
                            @if(! $notification->read_at)
                                <form method="POST" action="{{ route('admin.notifications.read', $notification) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button class="btn btn-sm btn-outline-primary">Marquer lue</button>
                                </form>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">Aucune notification trouvee.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $notifications->links() }}
</div>
@endsection
