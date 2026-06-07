<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientNotification extends Model
{
    use HasFactory;

    const CHANNEL_DASHBOARD = 'dashboard';
    const CHANNEL_EMAIL = 'email';
    const CHANNEL_DASHBOARD_EMAIL = 'dashboard_email';

    const STATUS_SENT = 'sent';
    const STATUS_FAILED = 'failed';

    protected $fillable = [
        'user_id',
        'title',
        'message',
        'channel',
        'status',
        'sent_by',
        'sent_at',
        'read_at',
        'email_sent_at',
        'error_message',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'read_at' => 'datetime',
        'email_sent_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sent_by');
    }

    public function scopeVisibleInDashboard($query)
    {
        return $query->whereIn('channel', [
            self::CHANNEL_DASHBOARD,
            self::CHANNEL_DASHBOARD_EMAIL,
        ]);
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }
}
