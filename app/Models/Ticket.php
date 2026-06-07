<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $fillable = [
        'plan_id',
        'username',
        'password',
        'profile',
        'import_batch',
        'status',
        'sold_at',
    ];

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }   

    public function order()
    {
        return $this->hasOne(Order::class);
    }
    use HasFactory;
}
