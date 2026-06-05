@extends('layouts.admin')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <span class="eyebrow"><span class="eyebrow-dot"></span> Detail commande</span>
        <h3>{{ $order->reference }}</h3>
        <p>Vue complete de la vente, du paiement et du ticket livre.</p>
    </div>
    <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-primary">
        <i class="bi bi-arrow-left"></i> Retour
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="row g-4">
    <div class="col-lg-7">
        <div class="panel-card mb-4">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <span class="eyebrow"><span class="eyebrow-dot"></span> Commande</span>
                    <h4 style="font-family:Poppins, sans-serif;color:#0d47a1;">{{ $order->reference }}</h4>
                </div>
                @include('admin.orders.partials.status-badge', ['status' => $order->status])
            </div>

            <div class="row g-3 mt-2">
                <div class="col-md-6">
                    <div class="stat-card">
                        <span>Telephone</span>
                        <strong>{{ $order->customer_phone }}</strong>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="stat-card">
                        <span>Montant</span>
                        <strong>{{ number_format($order->amount, 0, ',', ' ') }} XAF</strong>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="stat-card">
                        <span>Creee le</span>
                        <strong>{{ $order->created_at }}</strong>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="stat-card">
                        <span>Derniere mise a jour</span>
                        <strong>{{ $order->updated_at }}</strong>
                    </div>
                </div>
            </div>

            @if(auth()->user()->canAccessBackOffice('orders.manage') && $order->status === 'pending')
                <form method="POST" action="{{ route('admin.orders.cancel', $order) }}" class="mt-4">
                    @csrf
                    @method('PATCH')
                    <button class="btn btn-outline-danger">
                        <i class="bi bi-x-circle"></i> Annuler la commande en attente
                    </button>
                </form>
            @endif
        </div>

        <div class="panel-card">
            <span class="eyebrow"><span class="eyebrow-dot"></span> Ticket livre</span>
            @if($order->ticket)
                <div class="table-responsive mt-3">
                    <table class="table">
                        <tbody>
                            <tr>
                                <th>Username</th>
                                <td><strong>{{ $order->ticket->username }}</strong></td>
                            </tr>
                            <tr>
                                <th>Password</th>
                                <td>{{ auth()->user()->canAccessBackOffice('tickets.manage') ? $order->ticket->password : 'Masque' }}</td>
                            </tr>
                            <tr>
                                <th>Profil</th>
                                <td>{{ $order->ticket->profile ?: '-' }}</td>
                            </tr>
                            <tr>
                                <th>Statut</th>
                                <td>{{ $order->ticket->status }}</td>
                            </tr>
                            <tr>
                                <th>Vendu le</th>
                                <td>{{ $order->ticket->sold_at ?: '-' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted mt-3 mb-0">Aucun ticket livre pour cette commande.</p>
            @endif
        </div>
    </div>

    <div class="col-lg-5">
        <div class="panel-card mb-4">
            <span class="eyebrow"><span class="eyebrow-dot"></span> Client et routeur</span>
            <div class="table-responsive mt-3">
                <table class="table">
                    <tbody>
                        <tr>
                            <th>Client</th>
                            <td>{{ optional(optional(optional($order->plan)->router)->user)->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Commerce</th>
                            <td>{{ optional(optional(optional($order->plan)->router)->user)->business_name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Routeur</th>
                            <td>{{ optional(optional($order->plan)->router)->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Forfait</th>
                            <td>{{ $order->plan->name ?? '-' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="panel-card">
            <span class="eyebrow"><span class="eyebrow-dot"></span> Paiement lie</span>
            @if($order->payment)
                <div class="table-responsive mt-3">
                    <table class="table">
                        <tbody>
                            <tr>
                                <th>Provider</th>
                                <td>{{ $order->payment->provider }}</td>
                            </tr>
                            <tr>
                                <th>Methode</th>
                                <td>{{ $order->payment->payment_method ?: '-' }}</td>
                            </tr>
                            <tr>
                                <th>Statut</th>
                                <td>{{ $order->payment->status }}</td>
                            </tr>
                            <tr>
                                <th>Reference</th>
                                <td>{{ $order->payment->campay_reference ?: $order->payment->operator_reference ?: '-' }}</td>
                            </tr>
                            <tr>
                                <th>Telephone</th>
                                <td>{{ $order->payment->phone ?: '-' }}</td>
                            </tr>
                            <tr>
                                <th>Paye le</th>
                                <td>{{ $order->payment->paid_at ?: '-' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted mt-3 mb-0">Aucun paiement lie a cette commande.</p>
            @endif
        </div>
    </div>
</div>
@endsection
