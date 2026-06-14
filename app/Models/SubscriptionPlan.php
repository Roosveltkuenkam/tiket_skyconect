<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SubscriptionPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'monthly_price',
        'max_routers',
        'max_tickets_per_month',
        'max_sales_per_month',
        'quota_rate_percent',
        'withdrawal_fee_percent',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'quota_rate_percent' => 'float',
        'withdrawal_fee_percent' => 'float',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($plan) {
            if (empty($plan->slug)) {
                $plan->slug = Str::slug($plan->name);
            }
        });
    }

    public function clientSubscriptions()
    {
        return $this->hasMany(ClientSubscription::class);
    }
}
