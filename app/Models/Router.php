<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Router extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'public_slug',
        'location',
        'dns',
        'assistance_phone',
        'platform',
        'status',
        'integration_key',
    ];

    protected static function booted()
    {
        static::creating(function (Router $router) {
            if (! $router->public_slug) {
                $router->public_slug = static::uniquePublicSlug($router->name);
            }
        });
    }

    public static function uniquePublicSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'routeur';
        $slug = $base;
        $counter = 2;

        while (static::where('public_slug', $slug)->exists()) {
            $slug = $base . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    public function publicPortalUrl(): ?string
    {
        if (! $this->public_slug) {
            return null;
        }

        return route('portal.show', $this->public_slug);
    }

    public function plans()
    {
        return $this->hasMany(Plan::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
