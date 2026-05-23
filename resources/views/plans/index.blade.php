<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Forfaits WiFi - SkyConnect</title>
</head>
<body>
    <h1>Forfaits WiFi SkyConnect</h1>

    @foreach($plans as $plan)
        <div style="border:1px solid #ddd; padding:15px; margin-bottom:10px;">
            <h2>{{ $plan->name }}</h2>
            <p>{{ $plan->description }}</p>
            <strong>{{ $plan->price }} FCFA</strong>
            <br><br>
        <a href="{{ route('orders.create', $plan) }}">
            Acheter ce ticket
        </a>
        </div>
    @endforeach
</body>
</html>