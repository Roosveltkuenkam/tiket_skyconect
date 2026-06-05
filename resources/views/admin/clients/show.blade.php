@extends('layouts.admin')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <span class="eyebrow"><span class="eyebrow-dot"></span> Fiche client</span>
        <h3>{{ $client->name }}</h3>
        <p>{{ $client->email }} · {{ $client->business_name ?: 'Commerce non renseigne' }}</p>
    </div>
    <a href="{{ route('admin.clients.index') }}" class="sky-btn-outline">Retour</a>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-title">Routeurs</div>
            <div class="stat-value">{{ $stats['routers'] }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-title">Ventes</div>
            <div class="stat-value">{{ $stats['orders'] }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-title">CA client</div>
            <div class="stat-value">{{ number_format($stats['revenue'], 0, ',', ' ') }} XAF</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-title">Paiements reussis</div>
            <div class="stat-value">{{ $stats['payments_successful'] }}</div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-7">
        <div class="panel-card">
            <h4>Informations client</h4>
            <div class="row g-3 mt-1">
                <div class="col-md-6"><strong>Nom</strong><br>{{ $client->name }}</div>
                <div class="col-md-6"><strong>Email</strong><br>{{ $client->email }}</div>
                <div class="col-md-6"><strong>Telephone</strong><br>{{ $client->phone ?: '-' }}</div>
                <div class="col-md-6"><strong>Entreprise</strong><br>{{ $client->business_name ?: '-' }}</div>
                <div class="col-md-6"><strong>Ville</strong><br>{{ $client->city ?: '-' }}</div>
                <div class="col-md-6"><strong>Pays</strong><br>{{ $client->country ?: '-' }}</div>
                <div class="col-md-6"><strong>Statut</strong><br>{{ $client->is_active ? 'Actif' : 'Inactif' }}</div>
                <div class="col-md-6">
                    <strong>Abonnement</strong><br>
                    {{ optional(optional($client->clientSubscription)->plan)->name ?: 'Aucun abonnement' }}
                    @if($client->clientSubscription)
                        <small>({{ $client->clientSubscription->status }})</small>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="panel-card">
            <h4>Actions administratives</h4>

            @if(auth()->user()->canAccessBackOffice('clients.manage'))
                <form method="POST" action="{{ route('admin.clients.status', $client) }}" class="mb-3">
                    @csrf
                    @method('PATCH')
                    <button class="btn {{ $client->is_active ? 'btn-outline-danger' : 'btn-outline-success' }} w-100">
                        {{ $client->is_active ? 'Desactiver le compte' : 'Activer le compte' }}
                    </button>
                </form>

                <form method="POST" action="{{ route('admin.clients.password', $client) }}" class="mb-3">
                    @csrf
                    @method('PATCH')
                    <button class="btn btn-outline-warning w-100">Reinitialiser le mot de passe</button>
                </form>

                <form method="POST" action="{{ route('admin.clients.role', $client) }}">
                    @csrf
                    @method('PATCH')
                    <label class="form-label">Modifier le role</label>
                    <select name="role" class="form-control mb-3">
                        @foreach($roles as $role => $label)
                            @if($role !== \App\Models\User::ROLE_SUPER_ADMIN)
                                <option value="{{ $role }}" {{ $client->role === $role ? 'selected' : '' }}>{{ $label }}</option>
                            @endif
                        @endforeach
                    </select>
                    <button class="sky-btn w-100">Enregistrer le role</button>
                </form>

                @if(auth()->user()->canAccessBackOffice('subscriptions.manage'))
                    <hr>
                    <form method="POST" action="{{ route('admin.subscriptions.assign', $client) }}">
                        @csrf
                        <label class="form-label">Abonnement</label>
                        <select name="subscription_plan_id" class="form-control mb-3" required>
                            @foreach($subscriptionPlans as $plan)
                                <option value="{{ $plan->id }}" {{ optional($client->clientSubscription)->subscription_plan_id === $plan->id ? 'selected' : '' }}>
                                    {{ $plan->name }} - {{ number_format($plan->monthly_price, 0, ',', ' ') }} XAF/mois
                                </option>
                            @endforeach
                        </select>
                        <label class="form-label">Statut</label>
                        <select name="status" class="form-control mb-3">
                            <option value="trial" {{ optional($client->clientSubscription)->status === 'trial' ? 'selected' : '' }}>Essai</option>
                            <option value="active" {{ optional($client->clientSubscription)->status === 'active' ? 'selected' : '' }}>Actif</option>
                            <option value="expired" {{ optional($client->clientSubscription)->status === 'expired' ? 'selected' : '' }}>Expire</option>
                            <option value="suspended" {{ optional($client->clientSubscription)->status === 'suspended' ? 'selected' : '' }}>Suspendu</option>
                        </select>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="form-label">Debut</label>
                                <input type="date" name="starts_at" class="form-control" value="{{ optional(optional($client->clientSubscription)->starts_at)->format('Y-m-d') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Expiration</label>
                                <input type="date" name="expires_at" class="form-control" value="{{ optional(optional($client->clientSubscription)->expires_at)->format('Y-m-d') }}">
                            </div>
                        </div>
                        <label class="form-label mt-3">Paiement abonnement</label>
                        <input type="number" name="last_payment_amount" class="form-control mb-2" placeholder="Montant paye" value="{{ optional($client->clientSubscription)->last_payment_amount }}">
                        <input type="text" name="last_payment_status" class="form-control mb-2" placeholder="Statut paiement" value="{{ optional($client->clientSubscription)->last_payment_status }}">
                        <input type="text" name="last_payment_reference" class="form-control mb-3" placeholder="Reference paiement" value="{{ optional($client->clientSubscription)->last_payment_reference }}">
                        <textarea name="notes" class="form-control mb-3" rows="2" placeholder="Notes">{{ optional($client->clientSubscription)->notes }}</textarea>
                        <button class="sky-btn w-100">Assigner abonnement</button>
                    </form>
                @endif
            @else
                <p class="section-copy mb-0">Votre role permet la consultation, mais pas les actions sensibles.</p>
            @endif
        </div>
    </div>
</div>

<div class="panel-card mb-4">
    <h4>Routeurs du client</h4>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Routeur</th>
                    <th>DNS/IP</th>
                    <th>Statut</th>
                    <th>Forfaits</th>
                </tr>
            </thead>
            <tbody>
                @forelse($client->routers as $router)
                    <tr>
                        <td><strong>{{ $router->name }}</strong><br><small>{{ $router->location ?: '-' }}</small></td>
                        <td>{{ $router->dns ?: '-' }}</td>
                        <td>
                            @if($router->status === 'active')
                                <span class="badge text-bg-success">En ligne</span>
                            @else
                                <span class="badge text-bg-danger">Hors ligne</span>
                            @endif
                        </td>
                        <td>{{ $router->plans->count() }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-4">Aucun routeur.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="panel-card">
            <h4>Ventes du client</h4>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Reference</th>
                            <th>Montant</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                            <tr>
                                <td>{{ $order->created_at }}</td>
                                <td>{{ $order->reference }}</td>
                                <td>{{ $order->amount }} XAF</td>
                                <td>{{ $order->status }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">Aucune vente.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $orders->links() }}
        </div>
    </div>

    <div class="col-lg-6">
        <div class="panel-card">
            <h4>Paiements du client</h4>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Provider</th>
                            <th>Montant</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $payment)
                            <tr>
                                <td>{{ $payment->created_at }}</td>
                                <td>{{ strtoupper($payment->provider) }}</td>
                                <td>{{ $payment->amount }} XAF</td>
                                <td>{{ $payment->status }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">Aucun paiement.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $payments->links() }}
        </div>
    </div>
</div>
@endsection
