<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientQuotaTopup extends Model
{
    use HasFactory;

    const STATUS_PENDING = 'pending';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'user_id',
        'reference',
        'amount',
        'method',
        'phone',
        'external_reference',
        'status',
        'client_note',
        'admin_note',
        'confirmed_by',
        'confirmed_at',
        'rejected_by',
        'rejected_at',
        'raw_response',
    ];

    protected $casts = [
        'amount' => 'integer',
        'confirmed_at' => 'datetime',
        'rejected_at' => 'datetime',
        'raw_response' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function confirmer()
    {
        return $this->belongsTo(User::class, 'confirmed_by');
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
