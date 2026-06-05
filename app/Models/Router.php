<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Router extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'location',
        'dns',
        'assistance_phone',
        'platform',
        'status',
        'integration_key',
    ];

    public function plans()
    {
        return $this->hasMany(Plan::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
