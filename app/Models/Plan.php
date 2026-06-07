<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Plan extends Model
{
    protected $fillable = [
        'router_id',
        'name',
        'slug',
        'duration',
        'price',
        'description',
        'is_active',
    ];
    public function router()
    {
        return $this->belongsTo(Router::class);
    }
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($plan) {
            if (empty($plan->slug)) {
                $plan->slug = static::uniqueSlug($plan->name);
            }
        });

        static::updating(function ($plan) {
            if ($plan->isDirty('name')) {
                $plan->slug = static::uniqueSlug($plan->name, $plan->id);
            }
        });
    }

    protected static function uniqueSlug($name, $ignoreId = null)
    {
        $baseSlug = Str::slug($name) ?: 'forfait';
        $slug = $baseSlug;
        $suffix = 2;

        while (static::where('slug', $slug)
            ->when($ignoreId, function ($query) use ($ignoreId) {
                $query->where('id', '!=', $ignoreId);
            })
            ->exists()) {
            $slug = $baseSlug . '-' . $suffix;
            $suffix++;
        }

        return $slug;
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
