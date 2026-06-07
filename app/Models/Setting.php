<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type',
        'setting_group',
        'is_secret',
    ];

    protected $casts = [
        'is_secret' => 'boolean',
    ];
}
