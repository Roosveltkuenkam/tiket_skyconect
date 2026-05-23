<h1>Importer des tickets</h1>

@if(session('success'))
    <p>{{ session('success') }}</p>
@endif

<form method="POST" action="{{ route('tickets.import.store') }}" enctype="multipart/form-data">
    @csrf

    <label>Forfait</label>
    <select name="plan_id" required>
        @foreach($plans as $plan)
            <option value="{{ $plan->id }}">
                {{ $plan->name }} - {{ $plan->price }} FCFA
            </option>
        @endforeach
    </select>

    <br><br>

    <label>Fichier CSV Mikhmon</label>
    <input type="file" name="file" required>

    <br><br>

    <button type="submit">Importer</button>
</form>