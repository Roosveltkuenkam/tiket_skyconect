@extends('layouts.admin')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <span class="eyebrow"><span class="eyebrow-dot"></span> Communication clients</span>
        <h3>Messages clients</h3>
        <p>Envoyez des notifications dashboard et email aux proprietaires WiFi.</p>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

@if($errors->any())
    <div class="alert alert-danger">
        @foreach($errors->all() as $error)
            <div>{{ $error }}</div>
        @endforeach
    </div>
@endif

@if(auth()->user()->canAccessBackOffice('client_notifications.manage'))
    <div class="panel-card mb-4">
        <span class="eyebrow"><span class="eyebrow-dot"></span> Nouveau message</span>
        <form method="POST" action="{{ route('admin.client_notifications.store') }}" class="row g-3 mt-2">
            @csrf
            <div class="col-md-3">
                <label class="form-label">Destinataire</label>
                <select name="recipient" class="form-control" required>
                    <option value="single">Un client</option>
                    <option value="all">Tous les clients actifs</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Client</label>
                <select name="user_id" class="form-control">
                    <option value="">Choisir un client</option>
                    @foreach($clients as $client)
                        <option value="{{ $client->id }}">{{ $client->name }} - {{ $client->business_name ?: $client->email }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Canal</label>
                <select name="channel" class="form-control" required>
                    @foreach($channels as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button class="sky-btn w-100">Envoyer</button>
            </div>
            <div class="col-12">
                <label class="form-label">Titre</label>
                <input type="text" name="title" class="form-control" value="{{ old('title') }}" required>
            </div>
            <div class="col-12">
                <label class="form-label">Message</label>
                <textarea name="message" class="form-control" rows="5" required>{{ old('message') }}</textarea>
            </div>
        </form>
    </div>
@endif

<div class="panel-card mb-4">
    <form method="GET" action="{{ route('admin.client_notifications.index') }}" class="row g-3">
        <div class="col-md-4">
            <label class="form-label">Client</label>
            <input type="text" name="client" class="form-control" value="{{ request('client') }}" placeholder="Nom, email, commerce">
        </div>
        <div class="col-md-3">
            <label class="form-label">Canal</label>
            <select name="channel" class="form-control">
                <option value="">Tous</option>
                @foreach($channels as $value => $label)
                    <option value="{{ $value }}" {{ request('channel') === $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Statut</label>
            <select name="status" class="form-control">
                <option value="">Tous</option>
                @foreach($statuses as $value => $label)
                    <option value="{{ $value }}" {{ request('status') === $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-1 d-flex align-items-end">
            <a href="{{ route('admin.client_notifications.index') }}" class="btn btn-outline-primary w-100">Reset</a>
        </div>
        <div class="col-md-1 d-flex align-items-end">
            <button class="sky-btn w-100">OK</button>
        </div>
    </form>
</div>

<div class="panel-card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Client</th>
                    <th>Titre</th>
                    <th>Canal</th>
                    <th>Statut</th>
                    <th>Email</th>
                    <th>Envoye par</th>
                </tr>
            </thead>
            <tbody>
                @forelse($notifications as $notification)
                    <tr>
                        <td>{{ $notification->created_at }}</td>
                        <td>{{ optional($notification->user)->name ?: '-' }}</td>
                        <td>
                            <strong>{{ $notification->title }}</strong><br>
                            <small>{{ \Illuminate\Support\Str::limit($notification->message, 100) }}</small>
                            @if($notification->error_message)
                                <br><small class="text-danger">{{ $notification->error_message }}</small>
                            @endif
                        </td>
                        <td>{{ $channels[$notification->channel] ?? $notification->channel }}</td>
                        <td>
                            <span class="badge text-bg-{{ $notification->status === 'sent' ? 'success' : 'danger' }}">
                                {{ $statuses[$notification->status] ?? $notification->status }}
                            </span>
                        </td>
                        <td>{{ $notification->email_sent_at ?: '-' }}</td>
                        <td>{{ optional($notification->sender)->name ?: '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">Aucun message envoye.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $notifications->links() }}
</div>
@endsection
