<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Ticket;
use Illuminate\Http\Request;

class TicketImportController extends Controller
{
    public function create()
    {
        $plans = Plan::where('is_active', true)->get();

        return view('tickets.import', compact('plans'));
    }

    public function store(Request $request)
{
    $request->validate([
        'plan_id' => 'required|exists:plans,id',
        'file' => 'required|file|mimes:csv,txt',
    ]);

    $file = fopen($request->file('file')->getRealPath(), 'r');

    $imported = 0;
    $skipped = 0;
    $lineNumber = 0;

    while (($row = fgetcsv($file, 1000, ',')) !== false) {
        $lineNumber++;

        // Ignorer l'en-tête Mikhmon
        if ($lineNumber === 1 && strtolower($row[0]) === 'username') {
            continue;
        }

        $username = trim($row[0] ?? '');
        $password = trim($row[1] ?? '');
        $profile = trim($row[2] ?? '');

        if ($username === '') {
            $skipped++;
            continue;
        }

        $exists = Ticket::where('username', $username)->exists();

        if ($exists) {
            $skipped++;
            continue;
        }

        Ticket::create([
            'plan_id' => $request->plan_id,
            'username' => $username,
            'password' => $password,
            'profile' => $profile,
            'status' => 'available',
        ]);

        $imported++;
    }

    fclose($file);

    return back()->with(
        'success',
        "Import terminé : $imported tickets importés, $skipped ignorés."
    );
}
}