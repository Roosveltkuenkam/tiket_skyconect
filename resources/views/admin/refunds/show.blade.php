@extends('layouts.admin')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <span class="eyebrow"><span class="eyebrow-dot"></span> Detail remboursement</span>
        <h3>Remboursement #{{ $refund->id }}</h3>
        <p>Motif, responsables, commande, paiement et traitement.</p>
    </div>
    <a href="{{ route('admin.refunds.index') }}" class="btn btn-outline-primary">
        <i class="bi bi-arrow-left"></i> Retour
    </a>
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

<div class="row g-4">
    <div class="col-lg-7">
        <div class="panel-card mb-4">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <span class="eyebrow"><span class="eyebrow-dot"></span> Demande</span>
                    <h4 class="theme-link-icon" style="font-family:Poppins, sans-serif;">{{ number_format($refund->amount, 0, ',', ' ') }} XAF</h4>
                </div>
                @include('admin.refunds.partials.status-badge', ['status' => $refund->status])
            </div>

            <div class="table-responsive mt-3">
                <table class="table">
                    <tbody>
                        <tr>
                            <th>Motif</th>
                            <td>{{ $refund->reason }}</td>
                        </tr>
                        <tr>
                            <th>Note admin</th>
                            <td>{{ $refund->admin_note ?: '-' }}</td>
                        </tr>
                        <tr>
                            <th>Notification</th>
                            <td>{{ $refund->notification_sent_at ?: 'Non envoyee' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        @if(auth()->user()->canAccessBackOffice('refunds.manage'))
            <div class="panel-card">
                <span class="eyebrow"><span class="eyebrow-dot"></span> Actions</span>

                @if($refund->status === 'requested')
                    <form method="POST" action="{{ route('admin.refunds.approve', $refund) }}" class="mt-3">
                        @csrf
                        @method('PATCH')
                        <label class="form-label">Note admin optionnelle</label>
                        <textarea name="admin_note" class="form-control" rows="3">{{ old('admin_note', $refund->admin_note) }}</textarea>
                        <button class="sky-btn mt-3">Valider la demande</button>
                    </form>
                @endif

                @if(in_array($refund->status, ['requested', 'approved'], true))
                    <form method="POST" action="{{ route('admin.refunds.reject', $refund) }}" class="mt-4">
                        @csrf
                        @method('PATCH')
                        <label class="form-label">Motif du refus</label>
                        <textarea name="admin_note" class="form-control" rows="3" required>{{ old('admin_note') }}</textarea>
                        <button class="btn btn-outline-danger mt-3">Refuser la demande</button>
                    </form>
                @endif

                @if($refund->status === 'approved')
                    <form method="POST" action="{{ route('admin.refunds.process', $refund) }}" class="mt-4">
                        @csrf
                        @method('PATCH')
                        <button class="btn btn-success">Traiter et marquer rembourse</button>
                    </form>
                @endif

                @if(in_array($refund->status, ['rejected', 'processed'], true))
                    <p class="text-muted mt-3 mb-0">Cette demande est terminee.</p>
                @endif
            </div>
        @endif
    </div>

    <div class="col-lg-5">
        <div class="panel-card mb-4">
            <span class="eyebrow"><span class="eyebrow-dot"></span> Paiement et commande</span>
            <div class="table-responsive mt-3">
                <table class="table">
                    <tbody>
                        <tr>
                            <th>Paiement</th>
                            <td>
                                <a href="{{ route('admin.payments.show', $refund->payment) }}">#{{ $refund->payment_id }}</a>
                            </td>
                        </tr>
                        <tr>
                            <th>Provider</th>
                            <td>{{ optional($refund->payment)->provider ?: '-' }}</td>
                        </tr>
                        <tr>
                            <th>Commande</th>
                            <td>
                                @if($refund->order)
                                    <a href="{{ route('admin.orders.show', $refund->order) }}">{{ $refund->order->reference }}</a>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Client</th>
                            <td>{{ optional($refund->client)->name ?: '-' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="panel-card">
            <span class="eyebrow"><span class="eyebrow-dot"></span> Tracabilite</span>
            <div class="table-responsive mt-3">
                <table class="table">
                    <tbody>
                        <tr>
                            <th>Demande par</th>
                            <td>{{ optional($refund->requester)->name ?: '-' }}<br><small>{{ $refund->requested_at ?: '-' }}</small></td>
                        </tr>
                        <tr>
                            <th>Valide par</th>
                            <td>{{ optional($refund->approver)->name ?: '-' }}<br><small>{{ $refund->approved_at ?: '-' }}</small></td>
                        </tr>
                        <tr>
                            <th>Traite par</th>
                            <td>{{ optional($refund->processor)->name ?: '-' }}<br><small>{{ $refund->processed_at ?: '-' }}</small></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
