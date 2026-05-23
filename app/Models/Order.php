<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'reference',
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
    use HasFactory;
}
