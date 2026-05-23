<h1>Commande créée</h1>

<p>Référence : {{ $order->reference }}</p>
<p>Montant : {{ $order->amount }} FCFA</p>
<p>Statut : {{ $order->status }}</p>
<p>Téléphone : {{ $order->customer_phone }}</p>

<form method="POST" action="{{ route('payments.simulate', $order) }}">
    @csrf

    <button type="submit">
        Confirmer paiement test
    </button>
</form>