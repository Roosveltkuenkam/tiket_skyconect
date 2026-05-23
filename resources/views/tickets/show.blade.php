<h1>Votre ticket WiFi SkyConnect</h1>

<p>Forfait : {{ $order->plan->name }}</p>
<p>Montant payé : {{ $order->amount }} FCFA</p>
<p>Référence commande : {{ $order->reference }}</p>

<hr>

<h2>Informations de connexion</h2>

<p>Username : <strong>{{ $order->ticket->username }}</strong></p>
<p>Password : <strong>{{ $order->ticket->password }}</strong></p>

<p>Merci pour votre achat.</p>