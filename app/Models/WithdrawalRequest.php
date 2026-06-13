<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WithdrawalRequest extends Model
{
    use HasFactory;

    const STATUS_REQUESTED = 'requested';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_PROCESSED = 'processed';
    const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'user_id',
        'reference',
        'amount_requested',
        'fee_amount',
        'amount_to_pay',
        'method',
        'account_name',
        'account_phone',
        'status',
        'requested_at',
        'approved_by',
        'approved_at',
        'processed_by',
        'processed_at',
        'rejected_by',
        'rejected_at',
        'cancelled_by',
        'cancelled_at',
        'client_note',
        'admin_note',
        'metadata',
    ];

    protected $casts = [
        'amount_requested' => 'integer',
        'fee_amount' => 'integer',
        'amount_to_pay' => 'integer',
        'requested_at' => 'datetime',
        'approved_at' => 'datetime',
        'processed_at' => 'datetime',
        'rejected_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function processor()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function rejecter()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function canceller()
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function isRequested()
    {
        return $this->status === self::STATUS_REQUESTED;
    }

    public function isApproved()
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isReserved()
    {
        return in_array($this->status, [self::STATUS_REQUESTED, self::STATUS_APPROVED], true);
    }
}
