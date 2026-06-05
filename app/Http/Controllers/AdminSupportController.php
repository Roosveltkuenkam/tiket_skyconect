<?php

namespace App\Http\Controllers;

use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class AdminSupportController extends Controller
{
    public function index(Request $request)
    {
        $tickets = SupportTicket::with(['user', 'assignee', 'order', 'payment'])
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->when($request->filled('priority'), function ($query) use ($request) {
                $query->where('priority', $request->priority);
            })
            ->when($request->filled('client'), function ($query) use ($request) {
                $query->whereHas('user', function ($userQuery) use ($request) {
                    $userQuery
                        ->where('name', 'like', '%' . $request->client . '%')
                        ->orWhere('email', 'like', '%' . $request->client . '%')
                        ->orWhere('business_name', 'like', '%' . $request->client . '%');
                });
            })
            ->latest()
            ->paginate(20)
            ->appends($request->query());

        return view('admin.support.index', [
            'tickets' => $tickets,
            'statuses' => $this->statuses(),
            'priorities' => $this->priorities(),
        ]);
    }

    public function show(SupportTicket $ticket)
    {
        $ticket->load(['user', 'assignee', 'order', 'payment', 'messages.user']);

        return view('admin.support.show', [
            'ticket' => $ticket,
            'statuses' => $this->statuses(),
            'priorities' => $this->priorities(),
            'admins' => User::whereIn('role', [
                User::ROLE_SUPER_ADMIN,
                User::ROLE_ADMIN,
                User::ROLE_SUPPORT_AGENT,
                User::ROLE_TECHNICIAN,
            ])->orderBy('name')->get(),
        ]);
    }

    public function reply(Request $request, SupportTicket $ticket)
    {
        $data = $request->validate([
            'message' => 'required|string|min:2|max:5000',
            'is_internal' => 'nullable|boolean',
        ]);

        $message = $ticket->messages()->create([
            'user_id' => $request->user()->id,
            'type' => $request->boolean('is_internal')
                ? SupportTicketMessage::TYPE_INTERNAL_NOTE
                : SupportTicketMessage::TYPE_ADMIN,
            'message' => $data['message'],
            'is_internal' => $request->boolean('is_internal'),
        ]);

        if ($ticket->status === SupportTicket::STATUS_OPEN && ! $request->boolean('is_internal')) {
            $ticket->update(['status' => SupportTicket::STATUS_IN_PROGRESS]);
        }

        ActivityLogger::log($message->is_internal ? 'support.internal_note_created' : 'support.replied', $ticket, [
            'ticket_id' => $ticket->id,
            'client_id' => $ticket->user_id,
            'message_id' => $message->id,
        ], $request);

        return back()->with('success', $message->is_internal ? 'Note interne ajoutee.' : 'Reponse ajoutee.');
    }

    public function update(Request $request, SupportTicket $ticket)
    {
        $data = $request->validate([
            'status' => 'required|in:open,in_progress,resolved',
            'priority' => 'required|in:low,normal,high',
            'assigned_to' => 'nullable|exists:users,id',
            'order_id' => 'nullable|exists:orders,id',
            'payment_id' => 'nullable|exists:payments,id',
        ]);

        $old = $ticket->only(['status', 'priority', 'assigned_to', 'order_id', 'payment_id']);
        $ticket->update($data);

        ActivityLogger::log('support.updated', $ticket, [
            'old' => $old,
            'new' => $ticket->only(['status', 'priority', 'assigned_to', 'order_id', 'payment_id']),
        ], $request);

        return back()->with('success', 'Ticket support mis a jour.');
    }

    private function statuses()
    {
        return [
            SupportTicket::STATUS_OPEN => 'Ouvert',
            SupportTicket::STATUS_IN_PROGRESS => 'En cours',
            SupportTicket::STATUS_RESOLVED => 'Resolu',
        ];
    }

    private function priorities()
    {
        return [
            SupportTicket::PRIORITY_LOW => 'Faible',
            SupportTicket::PRIORITY_NORMAL => 'Normale',
            SupportTicket::PRIORITY_HIGH => 'Haute',
        ];
    }
}
