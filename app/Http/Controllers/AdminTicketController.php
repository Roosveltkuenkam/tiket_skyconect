<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Router;
use App\Models\Ticket;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class AdminTicketController extends Controller
{
    public function index(Request $request)
    {
        $clients = User::where('role', User::ROLE_CLIENT)->orderBy('name')->get();

        $routers = Router::with('user')
            ->when($request->user() && $request->user()->isClientOwner(), function ($query) use ($request) {
                $query->where('user_id', $request->user()->id);
            })
            ->orderBy('name')
            ->get();

        $plans = Plan::with('router')
            ->when($request->user() && $request->user()->isClientOwner(), function ($query) use ($request) {
                $query->whereHas('router', function ($routerQuery) use ($request) {
                    $routerQuery->where('user_id', $request->user()->id);
                });
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

        $canViewPasswords = $this->canViewPasswords($request);

        return view('admin.tickets.index', compact(
            'tickets',
            'plans',
            'routers',
            'clients',
            'canViewPasswords'
        ));
    }

    public function export(Request $request)
    {
        $canViewPasswords = $this->canViewPasswords($request);
        $fileName = 'skyconnect-tickets-' . now()->format('Ymd-His') . '.csv';

        ActivityLogger::log('tickets.exported', Ticket::class, [
            'filters' => $request->only(['client_id', 'router_id', 'plan_id', 'status']),
            'passwords_visible' => $canViewPasswords,
        ], $request);

        return response()->streamDownload(function () use ($request, $canViewPasswords) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'ID',
                'Client',
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
                ->chunk(500, function ($tickets) use ($handle, $canViewPasswords) {
                    foreach ($tickets as $ticket) {
                        fputcsv($handle, [
                            $ticket->id,
                            optional(optional(optional($ticket->plan)->router)->user)->name,
                            optional(optional($ticket->plan)->router)->name,
                            optional($ticket->plan)->name,
                            $ticket->username,
                            $canViewPasswords ? $ticket->password : '***',
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

        $query = Ticket::where('status', 'available');

        if ($request->filled('plan_id')) {
            $query->where('plan_id', $request->plan_id);
        }

        if ($request->user() && $request->user()->isClientOwner()) {
            $query->whereHas('plan.router', function ($routerQuery) use ($request) {
                $routerQuery->where('user_id', $request->user()->id);
            });
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
            ->when($request->user() && $request->user()->isClientOwner(), function ($query) use ($request) {
                $query->whereHas('plan.router', function ($routerQuery) use ($request) {
                    $routerQuery->where('user_id', $request->user()->id);
                });
            })
            ->when($request->filled('client_id') && ! $request->user()->isClientOwner(), function ($query) use ($request) {
                $query->whereHas('plan.router', function ($routerQuery) use ($request) {
                    $routerQuery->where('user_id', $request->client_id);
                });
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

    private function ensureTicketAccess(Ticket $ticket)
    {
        if (auth()->check() && auth()->user()->isClientOwner()) {
            abort_unless(
                $ticket->plan && $ticket->plan->router && $ticket->plan->router->user_id === auth()->id(),
                403
            );
        }
    }

    private function canViewPasswords(Request $request)
    {
        if (! $request->user()) {
            return false;
        }

        return $request->user()->isClientOwner()
            || $request->user()->canAccessBackOffice('tickets.manage');
    }
}
