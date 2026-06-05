@extends('layouts.admin')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <span class="eyebrow"><span class="eyebrow-dot"></span> Detail paiement</span>
        <h3>Paiement #{{ $payment->id }}</h3>
        <p>Controle provider, rapprochement commande et reponse brute.</p>
    </div>
    <a href="{{ route('admin.payments.index') }}" class="btn btn-outline-primary">
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
                    <span class="eyebrow"><span class="eyebrow-dot"></span> Transaction</span>
                    <h4 style="font-family:Poppins, sans-serif;color:#0d47a1;">{{ strtoupper($payment->provider) }}</h4>
                </div>
                @include('admin.payments.partials.status-badge', ['status' => $payment->status])
            </div>

            <div class="row g-3 mt-2">
                <div class="col-md-6">
                    <div class="stat-card">
                        <span>Montant</span>
                        <strong>{{ number_format($payment->amount, 0, ',', ' ') }} XAF</strong>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="stat-card">
                        <span>Telephone</span>
                        <strong>{{ $payment->phone }}</strong>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="stat-card">
                        <span>Reference provider</span>
                        <strong>{{ $payment->campay_reference ?: $payment->operator_reference ?: '-' }}</strong>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="stat-card">
                        <span>Orphelin</span>
                        <strong>{{ $isOrphan ? 'Oui' : 'Non' }}</strong>
                    </div>
                </div>
            </div>

            @if(auth()->user()->canAccessBackOffice('payments.manage'))
                <div class="d-flex gap-2 mt-4">
                    <form method="POST" action="{{ route('admin.payments.verify', $payment) }}">
                        @csrf
                        <button class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-repeat"></i> Relancer verification
                        </button>
                    </form>

                </div>
            @endif
        </div>

        <div class="panel-card">
            <span class="eyebrow"><span class="eyebrow-dot"></span> Reponse brute provider</span>
            @if($payment->raw_response)
                <pre class="mt-3 p-3 rounded" style="background:#0f172a;color:#e2e8f0;white-space:pre-wrap;font-size:13px;">{{ json_encode($payment->raw_response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
            @else
                <p class="text-muted mt-3 mb-0">Aucune reponse brute enregistree.</p>
            @endif
        </div>

        @if(auth()->user()->canAccessBackOffice('refunds.manage') && in_array($payment->status, ['successful', 'refunded'], true))
            <div class="panel-card mt-4">
                <span class="eyebrow"><span class="eyebrow-dot"></span> Demander remboursement</span>
                <form method="POST" action="{{ route('admin.refunds.store', $payment) }}" class="mt-3">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Montant</label>
                            <input type="number" name="amount" class="form-control" value="{{ old('amount', $payment->amount) }}" min="1">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Motif</label>
                            <input type="text" name="reason" class="form-control" value="{{ old('reason') }}" placeholder="Erreur paiement, litige client..." required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Note admin</label>
                            <textarea name="admin_note" class="form-control" rows="3">{{ old('admin_note') }}</textarea>
                        </div>
                        <div class="col-12">
                            <button class="sky-btn">Creer la demande</button>
                        </div>
                    </div>
                </form>
            </div>
        @endif
    </div>

    <div class="col-lg-5">
        <div class="panel-card mb-4">
            <span class="eyebrow"><span class="eyebrow-dot"></span> Commande liee</span>
            @if($payment->order)
                <div class="table-responsive mt-3">
                    <table class="table">
                        <tbody>
                            <tr>
                                <th>Reference</th>
                                <td>
                                    <a href="{{ route('admin.orders.show', $payment->order) }}">
                                        {{ $payment->order->reference }}
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <th>Statut</th>
                                <td>{{ $payment->order->status }}</td>
                            </tr>
                            <tr>
                                <th>Forfait</th>
                                <td>{{ optional($payment->order->plan)->name ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Routeur</th>
                                <td>{{ optional(optional($payment->order->plan)->router)->name ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Client</th>
                                <td>{{ optional(optional(optional($payment->order->plan)->router)->user)->name ?? '-' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            @else
                <div class="alert alert-danger mt-3 mb-0">
                    Paiement orphelin: aucune commande associee n'est disponible.
                </div>
            @endif
        </div>

        <div class="panel-card">
            <span class="eyebrow"><span class="eyebrow-dot"></span> Dates</span>
            <div class="table-responsive mt-3">
                <table class="table">
                    <tbody>
                        <tr>
                            <th>Cree le</th>
                            <td>{{ $payment->created_at }}</td>
                        </tr>
                        <tr>
                            <th>Mis a jour le</th>
                            <td>{{ $payment->updated_at }}</td>
                        </tr>
                        <tr>
                            <th>Paye le</th>
                            <td>{{ $payment->paid_at ?: '-' }}</td>
                        </tr>
                        <tr>
                            <th>Methode</th>
                            <td>{{ $payment->payment_method ?: '-' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="panel-card mt-4">
            <span class="eyebrow"><span class="eyebrow-dot"></span> Remboursements</span>
            <div class="table-responsive mt-3">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Statut</th>
                            <th>Montant</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payment->refunds as $refund)
                            <tr>
                                <td><a href="{{ route('admin.refunds.show', $refund) }}">#{{ $refund->id }}</a></td>
                                <td>@include('admin.refunds.partials.status-badge', ['status' => $refund->status])</td>
                                <td>{{ number_format($refund->amount, 0, ',', ' ') }} XAF</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-muted">Aucune demande.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
