<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Ticket;
use Illuminate\Http\Request;

class AdminTicketController extends Controller
{
    public function index(Request $request)
    {
        $plans = Plan::all();

        $tickets = Ticket::with('plan')
            ->when($request->plan_id, function ($query) use ($request) {
                $query->where('plan_id', $request->plan_id);
            })
            ->when($request->status, function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->latest()
            ->paginate(20);

        return view('admin.tickets.index', compact('tickets', 'plans'));
    }

    public function disable(Ticket $ticket)
    {
        if ($ticket->status === 'sold') {
            return back()->with('error', 'Impossible de désactiver un ticket déjà vendu.');
        }

        $ticket->update([
            'status' => 'disabled',
        ]);

        return back()->with('success', 'Ticket désactivé.');
    }

    public function enable(Ticket $ticket)
    {
        if ($ticket->status === 'sold') {
            return back()->with('error', 'Impossible de réactiver un ticket déjà vendu.');
        }

        $ticket->update([
            'status' => 'available',
        ]);

        return back()->with('success', 'Ticket réactivé.');
    }

    public function destroy(Ticket $ticket)
    {
        if ($ticket->status === 'sold') {
            return back()->with('error', 'Impossible de supprimer un ticket déjà vendu.');
        }

        $ticket->delete();

        return back()->with('success', 'Ticket supprimé.');
    }

    public function clear(Request $request)
    {
        $request->validate([
            'plan_id' => 'nullable|exists:plans,id',
        ]);

        $query = Ticket::where('status', 'available');

        if ($request->filled('plan_id')) {
            $query->where('plan_id', $request->plan_id);
        }

        $count = $query->count();

        $query->delete();

        return back()->with('success', "$count ticket(s) disponible(s) supprimé(s).");
    }
}