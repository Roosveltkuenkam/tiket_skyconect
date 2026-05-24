<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Router extends Model
{
    protected $fillable = [
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

}