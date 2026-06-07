<?php

namespace App\Http\Controllers;

use App\Models\ClientNotification;
use Illuminate\Http\Request;

class DashboardClientNotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = ClientNotification::visibleInDashboard()
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(20);

        return view('dashboard.notifications.index', compact('notifications'));
    }

    public function markRead(Request $request, ClientNotification $notification)
    {
        abort_unless($notification->user_id === $request->user()->id, 403);

        $notification->update(['read_at' => now()]);

        return back()->with('success', 'Notification marquee comme lue.');
    }
}
