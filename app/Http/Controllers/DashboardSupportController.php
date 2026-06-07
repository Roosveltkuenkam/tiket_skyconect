<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class DashboardSupportController extends Controller
{
    public function index(Request $request)
    {
        $tickets = SupportTicket::where('user_id', $request->user()->id)
            ->with(['order', 'payment'])
            ->latest()
            ->paginate(20);

        return view('dashboard.support.index', compact('tickets'));
    }

    public function create(Request $request)
    {
        $orders = Order::whereHas('plan.router', function ($query) use ($request) {
                $query->where('user_id', $request->user()->id);
            })
            ->latest()
            ->limit(50)
            ->get();

        return view('dashboard.support.create', compact('orders'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string|min:5|max:5000',
            'priority' => 'required|in:low,normal,high',
            'order_id' => 'nullable|exists:orders,id',
        ]);

        if ($request->filled('order_id')) {
            abort_unless(
                Order::where('id', $request->order_id)
                    ->whereHas('plan.router', function ($query) use ($request) {
                        $query->where('user_id', $request->user()->id);
                    })
                    ->exists(),
                403
            );
        }

        $ticket = SupportTicket::create([
            'user_id' => $request->user()->id,
            'order_id' => $data['order_id'] ?? null,
            'subject' => $data['subject'],
            'priority' => $data['priority'],
            'status' => SupportTicket::STATUS_OPEN,
        ]);

        $ticket->messages()->create([
            'user_id' => $request->user()->id,
            'type' => SupportTicketMessage::TYPE_CLIENT,
            'message' => $data['message'],
            'is_internal' => false,
        ]);

        ActivityLogger::log('support.created', $ticket, [
            'client_id' => $request->user()->id,
            'order_id' => $ticket->order_id,
            'priority' => $ticket->priority,
        ], $request);

        return redirect()->route('dashboard.support.show', $ticket)
            ->with('success', 'Ticket support cree.');
    }

    public function show(Request $request, SupportTicket $ticket)
    {
        $this->ensureOwner($request, $ticket);
        $ticket->load(['order', 'payment', 'messages.user']);

        return view('dashboard.support.show', compact('ticket'));
    }

    public function reply(Request $request, SupportTicket $ticket)
    {
        $this->ensureOwner($request, $ticket);

        if ($ticket->status === SupportTicket::STATUS_RESOLVED) {
            return back()->with('error', 'Ce ticket est deja resolu.');
        }

        $data = $request->validate([
            'message' => 'required|string|min:2|max:5000',
        ]);

        $message = $ticket->messages()->create([
            'user_id' => $request->user()->id,
            'type' => SupportTicketMessage::TYPE_CLIENT,
            'message' => $data['message'],
            'is_internal' => false,
        ]);

        ActivityLogger::log('support.client_replied', $ticket, [
            'client_id' => $request->user()->id,
            'message_id' => $message->id,
        ], $request);

        return back()->with('success', 'Reponse ajoutee.');
    }

    private function ensureOwner(Request $request, SupportTicket $ticket)
    {
        abort_unless($ticket->user_id === $request->user()->id, 403);
    }
}
