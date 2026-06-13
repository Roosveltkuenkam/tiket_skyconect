<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientWithdrawal extends Model
{
    use HasFactory;

    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSED = 'processed';
    const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'user_id',
        'reference',
        'amount',
        'fee_type',
        'fee_amount',
        'net_amount',
        'method',
        'account_name',
        'account_number',
        'status',
        'client_note',
        'admin_note',
        'processed_by',
        'processed_at',
        'rejected_by',
        'rejected_at',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'integer',
        'fee_amount' => 'integer',
        'net_amount' => 'integer',
        'processed_at' => 'datetime',
        'rejected_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function processor()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function rejecter()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function isPending()
    {
        return $this->status === self::STATUS_PENDING;
    }
}
