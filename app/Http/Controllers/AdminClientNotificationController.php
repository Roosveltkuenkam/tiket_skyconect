<?php

namespace App\Http\Controllers;

use App\Models\ClientNotification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Services\ActivityLogger;
use Throwable;

class AdminClientNotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = ClientNotification::with(['user', 'sender'])
            ->when($request->filled('client'), function ($query) use ($request) {
                $query->whereHas('user', function ($userQuery) use ($request) {
                    $userQuery
                        ->where('name', 'like', '%' . $request->client . '%')
                        ->orWhere('email', 'like', '%' . $request->client . '%')
                        ->orWhere('business_name', 'like', '%' . $request->client . '%');
                });
            })
            ->when($request->filled('channel'), function ($query) use ($request) {
                $query->where('channel', $request->channel);
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->latest()
            ->paginate(20)
            ->appends($request->query());

        $clients = User::where('role', User::ROLE_CLIENT)->orderBy('name')->get();

        return view('admin.client_notifications.index', [
            'notifications' => $notifications,
            'clients' => $clients,
            'channels' => $this->channels(),
            'statuses' => $this->statuses(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'recipient' => 'required|in:single,all',
            'user_id' => 'required_if:recipient,single|nullable|exists:users,id',
            'title' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
            'channel' => 'required|in:dashboard,email,dashboard_email',
        ]);

        $clients = $data['recipient'] === 'all'
            ? User::where('role', User::ROLE_CLIENT)->where('is_active', true)->get()
            : User::where('role', User::ROLE_CLIENT)->where('id', $data['user_id'])->get();

        if ($clients->isEmpty()) {
            return back()->with('error', 'Aucun client destinataire trouve.');
        }

        $sent = 0;
        $failed = 0;

        foreach ($clients as $client) {
            $notification = ClientNotification::create([
                'user_id' => $client->id,
                'title' => $data['title'],
                'message' => $data['message'],
                'channel' => $data['channel'],
                'status' => ClientNotification::STATUS_SENT,
                'sent_by' => $request->user()->id,
                'sent_at' => now(),
            ]);

            if (in_array($data['channel'], [ClientNotification::CHANNEL_EMAIL, ClientNotification::CHANNEL_DASHBOARD_EMAIL], true)) {
                if ($this->sendEmail($client, $notification)) {
                    $notification->update(['email_sent_at' => now()]);
                } else {
                    $notification->update(['status' => ClientNotification::STATUS_FAILED]);
                    $failed++;
                    continue;
                }
            }

            $sent++;
        }

        ActivityLogger::log('client_notifications.sent', ClientNotification::class, [
            'recipient' => $data['recipient'],
            'target_user_id' => $data['user_id'] ?? null,
            'channel' => $data['channel'],
            'sent' => $sent,
            'failed' => $failed,
            'title' => $data['title'],
        ], $request);

        return back()->with('success', "$sent notification(s) envoyee(s), $failed echec(s).");
    }

    private function sendEmail(User $client, ClientNotification $notification)
    {
        if (! $client->email) {
            $notification->update(['error_message' => 'Client sans email.']);
            return false;
        }

        try {
            Mail::raw(
                $notification->message . "\n\nSkyConnect",
                function ($message) use ($client, $notification) {
                    $message->to($client->email)->subject($notification->title);
                }
            );

            return true;
        } catch (Throwable $exception) {
            $notification->update(['error_message' => $exception->getMessage()]);
            return false;
        }
    }

    private function channels()
    {
        return [
            ClientNotification::CHANNEL_DASHBOARD => 'Dashboard',
            ClientNotification::CHANNEL_EMAIL => 'Email',
            ClientNotification::CHANNEL_DASHBOARD_EMAIL => 'Dashboard + email',
        ];
    }

    private function statuses()
    {
        return [
            ClientNotification::STATUS_SENT => 'Envoyee',
            ClientNotification::STATUS_FAILED => 'Echec',
        ];
    }
}
