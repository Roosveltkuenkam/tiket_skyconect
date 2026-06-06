<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'reference',
        'public_access_token',
        'plan_id',
        'ticket_id',
        'customer_phone',
        'amount',
        'status',
    ];

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    public function refunds()
    {
        return $this->hasMany(Refund::class);
    }

    public function hasValidPublicAccessToken($token)
    {
        return is_string($token)
            && is_string($this->public_access_token)
            && hash_equals($this->public_access_token, $token);
    }

    public function publicRouteParameters()
    {
        return [$this->reference, $this->public_access_token];
    }

    use HasFactory;
}
