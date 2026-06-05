<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientSubscription extends Model
{
    use HasFactory;

    const STATUS_TRIAL = 'trial';
    const STATUS_ACTIVE = 'active';
    const STATUS_EXPIRED = 'expired';
    const STATUS_SUSPENDED = 'suspended';

    protected $fillable = [
        'user_id',
        'subscription_plan_id',
        'status',
        'starts_at',
        'expires_at',
        'last_payment_amount',
        'last_payment_status',
        'last_payment_reference',
        'last_paid_at',
        'notes',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'last_paid_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function plan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }

    public function isUsable()
    {
        return in_array($this->status, [self::STATUS_TRIAL, self::STATUS_ACTIVE], true)
            && (! $this->expires_at || $this->expires_at->endOfDay()->isFuture());
    }

    public function refreshExpirationStatus()
    {
        if ($this->expires_at && $this->expires_at->endOfDay()->isPast() && $this->status !== self::STATUS_SUSPENDED) {
            $this->update(['status' => self::STATUS_EXPIRED]);
        }

        return $this;
    }
}
