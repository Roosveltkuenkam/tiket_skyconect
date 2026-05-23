<h1>Achat du forfait {{ $plan->name }}</h1>

<p>Prix : {{ $plan->price }} FCFA</p>
<p>Durée : {{ $plan->duration }}</p>

<form method="POST" action="{{ route('orders.store', $plan) }}">
    @csrf

    <label>Numéro de téléphone</label>
    <input type="text" name="customer_phone" placeholder="Ex: 690000000" required>

    <button type="submit">
        Continuer vers le paiement
    </button>
</form>