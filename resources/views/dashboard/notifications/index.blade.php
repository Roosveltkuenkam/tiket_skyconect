@extends('layouts.admin')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <span class="eyebrow"><span class="eyebrow-dot"></span> Messages SkyConnect</span>
        <h3>Notifications</h3>
        <p>Retrouvez les communications envoyees par l'equipe SkyConnect.</p>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="panel-card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Message</th>
                    <th>Statut</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($notifications as $notification)
                    <tr>
                        <td>{{ $notification->created_at }}</td>
                        <td>
                            <strong>{{ $notification->title }}</strong><br>
                            <span>{{ $notification->message }}</span>
                        </td>
                        <td>
                            @if($notification->read_at)
                                <span class="badge text-bg-success">Lue</span>
                            @else
                                <span class="badge text-bg-warning">Non lue</span>
                            @endif
                        </td>
                        <td>
                            @if(! $notification->read_at)
                                <form method="POST" action="{{ route('dashboard.notifications.read', $notification) }}">
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
                        <td colspan="4" class="text-center text-muted py-4">Aucune notification pour le moment.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $notifications->links() }}
</div>
@endsection
