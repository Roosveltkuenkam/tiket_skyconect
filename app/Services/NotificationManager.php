<?php

namespace App\Services;

use App\Models\AdminNotification;
use App\Models\ClientNotification;
use App\Models\User;

class NotificationManager
{
    public static function admin($type, $title, $message, $severity = 'info', array $data = [])
    {
        return AdminNotification::notify($type, $title, $message, $severity, $data);
    }

    public static function client(User $user, $title, $message, array $data = [])
    {
        return ClientNotification::create([
            'user_id' => $user->id,
            'title' => $title,
            'message' => $message,
            'channel' => ClientNotification::CHANNEL_DASHBOARD,
            'status' => ClientNotification::STATUS_SENT,
            'sent_at' => now(),
        ]);
    }
}
