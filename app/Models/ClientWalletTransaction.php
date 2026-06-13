<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientWalletTransaction extends Model
{
    use HasFactory;

    const TYPE_QUOTA_CREDIT = 'quota_credit';
    const TYPE_QUOTA_DEBIT = 'quota_debit';
    const TYPE_QUOTA_SET = 'quota_set';
    const TYPE_SALE_COMMISSION = 'sale_commission';

    protected $fillable = [
        'client_wallet_id',
        'user_id',
        'order_id',
        'payment_id',
        'performed_by',
        'type',
        'amount',
        'balance_before',
        'balance_after',
        'reference',
        'note',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'integer',
        'balance_before' => 'integer',
        'balance_after' => 'integer',
        'metadata' => 'array',
    ];

    public function wallet()
    {
        return $this->belongsTo(ClientWallet::class, 'client_wallet_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function performer()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
