<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Router;
use App\Models\Ticket;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardTicketController extends Controller
{
    public function index(Request $request)
    {
        $clients = collect();

        $routers = Router::with('user')
            ->where('user_id', $request->user()->id)
            ->orderBy('name')
            ->get();

        $plans = Plan::with('router')
            ->whereHas('router', function ($query) use ($request) {
                $query->where('user_id', $request->user()->id);
            })
            ->when($request->filled('router_id'), function ($query) use ($request) {
                $query->where('router_id', $request->router_id);
            })
            ->orderBy('name')
            ->get();

        $tickets = $this->filteredTickets($request)
            ->latest()
            ->paginate(20)
            ->appends($request->query());

        $ticketTotalCount = Ticket::whereHas('plan.router', function ($routerQuery) use ($request) {
                $routerQuery->where('user_id', $request->user()->id);
            })
            ->count();

        $planStockCards = Plan::with('router')
            ->withCount([
                'tickets as total_tickets_count',
                'tickets as available_tickets_count' => function ($query) {
                    $query->where('status', 'available');
                },
            ])
            ->whereHas('router', function ($query) use ($request) {
                $query->where('user_id', $request->user()->id);
            })
            ->orderBy('name')
            ->get();

        $importLots = $this->importLots($request);
        $canViewPasswords = true;

        return view('admin.tickets.index', compact(
            'tickets',
            'plans',
            'routers',
            'clients',
            'ticketTotalCount',
            'planStockCards',
            'importLots',
            'canViewPasswords'
        ));
    }

    public function export(Request $request)
    {
        $fileName = 'skyconnect-tickets-' . now()->format('Ymd-His') . '.csv';

        ActivityLogger::log('tickets.exported', Ticket::class, [
            'filters' => $request->only(['router_id', 'plan_id', 'status']),
            'passwords_visible' => true,
        ], $request);

        return response()->streamDownload(function () use ($request) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'ID',
                'Routeur',
                'Forfait',
                'Username',
                'Password',
                'Profil',
                'Statut',
                'Commande',
                'Vendu le',
                'Cree le',
            ]);

            $this->filteredTickets($request)
                ->orderBy('id')
                ->chunk(500, function ($tickets) use ($handle) {
                    foreach ($tickets as $ticket) {
                        fputcsv($handle, [
                            $ticket->id,
                            optional(optional($ticket->plan)->router)->name,
                            optional($ticket->plan)->name,
                            $ticket->username,
                            $ticket->password,
                            $ticket->profile,
                            $ticket->status,
                            optional($ticket->order)->reference,
                            $ticket->sold_at,
                            $ticket->created_at,
                        ]);
                    }
                });

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function disable(Ticket $ticket)
    {
        $this->ensureTicketAccess($ticket->load('plan.router'));

        if ($ticket->status === 'sold') {
            return back()->with('error', 'Impossible de desactiver un ticket deja vendu.');
        }

        $ticket->update(['status' => 'disabled']);

        ActivityLogger::log('ticket.disabled', $ticket, [
            'username' => $ticket->username,
            'plan_id' => $ticket->plan_id,
        ]);

        return back()->with('success', 'Ticket desactive.');
    }

    public function enable(Ticket $ticket)
    {
        $this->ensureTicketAccess($ticket->load('plan.router'));

        if ($ticket->status === 'sold') {
            return back()->with('error', 'Impossible de reactiver un ticket deja vendu.');
        }

        $ticket->update(['status' => 'available']);

        ActivityLogger::log('ticket.enabled', $ticket, [
            'username' => $ticket->username,
            'plan_id' => $ticket->plan_id,
        ]);

        return back()->with('success', 'Ticket reactive.');
    }

    public function destroy(Ticket $ticket)
    {
        $this->ensureTicketAccess($ticket->load('plan.router'));

        if ($ticket->status === 'sold') {
            return back()->with('error', 'Impossible de supprimer un ticket deja vendu.');
        }

        ActivityLogger::log('ticket.deleted', $ticket, [
            'username' => $ticket->username,
            'plan_id' => $ticket->plan_id,
        ]);

        $ticket->delete();

        return back()->with('success', 'Ticket supprime.');
    }

    public function clear(Request $request)
    {
        $request->validate([
            'plan_id' => 'nullable|exists:plans,id',
        ]);

        $query = Ticket::where('status', 'available')
            ->whereHas('plan.router', function ($routerQuery) use ($request) {
                $routerQuery->where('user_id', $request->user()->id);
            });

        if ($request->filled('plan_id')) {
            $query->where('plan_id', $request->plan_id);
        }

        $count = $query->count();
        $query->delete();

        ActivityLogger::log('tickets.cleared', Ticket::class, [
            'count' => $count,
            'plan_id' => $request->plan_id,
        ], $request);

        return back()->with('success', "$count ticket(s) disponible(s) supprime(s).");
    }

    private function filteredTickets(Request $request)
    {
        return Ticket::with(['plan.router.user', 'order'])
            ->whereHas('plan.router', function ($routerQuery) use ($request) {
                $routerQuery->where('user_id', $request->user()->id);
            })
            ->when($request->filled('router_id'), function ($query) use ($request) {
                $query->whereHas('plan', function ($planQuery) use ($request) {
                    $planQuery->where('router_id', $request->router_id);
                });
            })
            ->when($request->filled('plan_id'), function ($query) use ($request) {
                $query->where('plan_id', $request->plan_id);
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            });
    }

    private function importLots(Request $request)
    {
        return Ticket::query()
            ->join('plans', 'plans.id', '=', 'tickets.plan_id')
            ->join('routers', 'routers.id', '=', 'plans.router_id')
            ->where('routers.user_id', $request->user()->id)
            ->selectRaw("
                COALESCE(tickets.import_batch, CONCAT('LEGACY-', plans.id, '-', DATE_FORMAT(tickets.created_at, '%Y%m%d%H%i'))) as lot,
                plans.name as plan_name,
                plans.price as plan_price,
                routers.name as router_name,
                COUNT(*) as total_count,
                SUM(CASE WHEN tickets.status = 'available' THEN 1 ELSE 0 END) as available_count,
                SUM(CASE WHEN tickets.status = 'sold' THEN 1 ELSE 0 END) as sold_count,
                MIN(tickets.created_at) as imported_at
            ")
            ->groupBy(
                DB::raw("COALESCE(tickets.import_batch, CONCAT('LEGACY-', plans.id, '-', DATE_FORMAT(tickets.created_at, '%Y%m%d%H%i')))"),
                'plans.name',
                'plans.price',
                'routers.name'
            )
            ->orderByDesc('imported_at')
            ->limit(20)
            ->get();
    }

    private function ensureTicketAccess(Ticket $ticket)
    {
        abort_unless(
            $ticket->plan && $ticket->plan->router && $ticket->plan->router->user_id === auth()->id(),
            403
        );
    }
}
