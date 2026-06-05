<?php

namespace App\Http\Controllers;

use App\Models\AdminNotification;
use App\Models\Plan;
use App\Models\Ticket;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class TicketImportController extends Controller
{
    private function routeName(Request $request, $name)
    {
        return ($request->is('dashboard*') ? 'dashboard' : 'admin') . '.tickets.import.' . $name;
    }

    public function create()
    {
        $plans = Plan::where('is_active', true)
            ->when(auth()->check() && auth()->user()->isClientOwner(), function ($query) {
                $query->whereHas('router', function ($routerQuery) {
                    $routerQuery->where('user_id', auth()->id());
                });
            })
            ->get();

        return view('tickets.import', compact('plans'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|exists:plans,id',
            'file' => 'required|file|mimes:csv,txt',
        ]);

        if ($request->user()->isClientOwner()) {
            if (! $request->user()->canUseSubscription()) {
                AdminNotification::notify(
                    'account_suspended',
                    'Import bloque par abonnement',
                    $request->user()->name . ' a tente d importer des tickets avec un abonnement non actif.',
                    'warning',
                    ['user_id' => $request->user()->id]
                );

                return back()->with('error', "Votre abonnement n'est pas actif.");
            }

            if ($request->user()->subscriptionLimitReached('tickets')) {
                return back()->with('error', 'Limite mensuelle de tickets atteinte pour votre abonnement.');
            }

            abort_unless(
                Plan::where('id', $request->plan_id)
                    ->whereHas('router', function ($query) use ($request) {
                        $query->where('user_id', $request->user()->id);
                    })
                    ->exists(),
                403
            );
        }

        $file = fopen($request->file('file')->getRealPath(), 'r');

        $imported = 0;
        $skipped = 0;
        $lineNumber = 0;

        while (($row = fgetcsv($file, 1000, ',')) !== false) {
            $lineNumber++;

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

            if (Ticket::where('username', $username)->exists()) {
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

        ActivityLogger::log('tickets.imported', Plan::class, [
            'plan_id' => $request->plan_id,
            'imported' => $imported,
            'skipped' => $skipped,
        ], $request);

        return redirect()->route($this->routeName($request, 'create'))->with(
            'success',
            "Import termine : $imported tickets importes, $skipped ignores."
        );
    }
}
