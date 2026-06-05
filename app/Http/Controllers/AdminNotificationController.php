<?php

namespace App\Http\Controllers;

use App\Models\AdminNotification;
use Illuminate\Http\Request;

class AdminNotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = AdminNotification::query()
            ->when($request->filled('severity'), function ($query) use ($request) {
                $query->where('severity', $request->severity);
            })
            ->when($request->filled('type'), function ($query) use ($request) {
                $query->where('type', $request->type);
            })
            ->when($request->boolean('unread'), function ($query) {
                $query->unread();
            })
            ->latest()
            ->paginate(20)
            ->appends($request->query());

        $types = AdminNotification::select('type')->distinct()->orderBy('type')->pluck('type');

        return view('admin.notifications.index', compact('notifications', 'types'));
    }

    public function markRead(AdminNotification $notification)
    {
        $notification->update(['read_at' => now()]);

        return back()->with('success', 'Notification marquee comme lue.');
    }

    public function markAllRead()
    {
        AdminNotification::unread()->update(['read_at' => now()]);

        return back()->with('success', 'Toutes les notifications ont ete marquees comme lues.');
    }
}
