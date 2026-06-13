<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientWallet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'quota_balance',
        'total_quota_loaded',
        'total_quota_used',
        'total_sales_amount',
        'total_withdrawn',
        'pending_withdrawal_amount',
    ];

    protected $casts = [
        'quota_balance' => 'integer',
        'total_quota_loaded' => 'integer',
        'total_quota_used' => 'integer',
        'total_sales_amount' => 'integer',
        'total_withdrawn' => 'integer',
        'pending_withdrawal_amount' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactions()
    {
        return $this->hasMany(ClientWalletTransaction::class);
    }

    public function grossClientBalance()
    {
        return max(0, $this->total_sales_amount - $this->total_quota_used);
    }

    public function availableWithdrawalBalance()
    {
        return max(0, $this->grossClientBalance() - $this->total_withdrawn - $this->pending_withdrawal_amount);
    }
}
