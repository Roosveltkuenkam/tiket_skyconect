<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use App\Models\ClientSubscription;
use App\Models\User;
use Illuminate\Database\Seeder;

class SubscriptionPlanSeeder extends Seeder
{
    public function run()
    {
        $plans = [
            [
                'name' => 'Standard',
                'description' => 'Pour les proprietaires WiFi qui lancent leurs ventes avec un ou plusieurs hotspots.',
                'monthly_price' => 5000,
                'max_routers' => 3,
                'max_tickets_per_month' => 1000,
                'max_sales_per_month' => 500,
            ],
            [
                'name' => 'Pro',
                'description' => 'Pour les reseaux avec plusieurs hotspots.',
                'monthly_price' => 15000,
                'max_routers' => 10,
                'max_tickets_per_month' => 10000,
                'max_sales_per_month' => 5000,
            ],
            [
                'name' => 'Entreprise',
                'description' => 'Limites elevees pour grands reseaux et operations multi-sites.',
                'monthly_price' => 50000,
                'max_routers' => null,
                'max_tickets_per_month' => null,
                'max_sales_per_month' => null,
            ],
        ];

        foreach ($plans as $plan) {
            SubscriptionPlan::updateOrCreate(
                ['name' => $plan['name']],
                $plan + ['is_active' => true]
            );
        }

        SubscriptionPlan::where('slug', 'gratuit')->update(['is_active' => false]);

        $standardPlan = SubscriptionPlan::where('slug', 'standard')->first();

        if ($standardPlan) {
            User::where('role', User::ROLE_CLIENT)->chunk(100, function ($clients) use ($standardPlan) {
                foreach ($clients as $client) {
                    ClientSubscription::firstOrCreate(
                        ['user_id' => $client->id],
                        [
                            'subscription_plan_id' => $standardPlan->id,
                            'status' => ClientSubscription::STATUS_ACTIVE,
                            'starts_at' => now(),
                            'expires_at' => now()->addDays(30),
                            'last_payment_amount' => $standardPlan->monthly_price,
                            'last_payment_status' => 'included',
                            'notes' => 'Abonnement standard assigne par le seeder.',
                        ]
                    );
                }
            });
        }
    }
}
