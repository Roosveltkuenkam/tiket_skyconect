<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AssignStandardSubscriptionToClients extends Migration
{
    public function up()
    {
        $standard = DB::table('subscription_plans')->where('slug', 'standard')->first();

        if (! $standard) {
            return;
        }

        $free = DB::table('subscription_plans')->where('slug', 'gratuit')->first();

        if ($free) {
            DB::table('subscription_plans')
                ->where('id', $free->id)
                ->update(['is_active' => false, 'updated_at' => now()]);
        }

        DB::table('users')
            ->where('role', 'client')
            ->orderBy('id')
            ->get(['id'])
            ->each(function ($client) use ($standard, $free) {
                $subscription = DB::table('client_subscriptions')
                    ->where('user_id', $client->id)
                    ->first();

                if (! $subscription) {
                    DB::table('client_subscriptions')->insert([
                        'user_id' => $client->id,
                        'subscription_plan_id' => $standard->id,
                        'status' => 'active',
                        'starts_at' => now(),
                        'expires_at' => now()->addDays(30),
                        'last_payment_amount' => $standard->monthly_price,
                        'last_payment_status' => 'included',
                        'notes' => 'Abonnement standard assigne automatiquement.',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    return;
                }

                if ($free && (int) $subscription->subscription_plan_id === (int) $free->id) {
                    DB::table('client_subscriptions')
                        ->where('id', $subscription->id)
                        ->update([
                            'subscription_plan_id' => $standard->id,
                            'status' => 'active',
                            'starts_at' => now(),
                            'expires_at' => now()->addDays(30),
                            'last_payment_amount' => $standard->monthly_price,
                            'last_payment_status' => 'included',
                            'notes' => 'Migration vers abonnement standard par defaut.',
                            'updated_at' => now(),
                        ]);
                }
            });
    }

    public function down()
    {
        DB::table('subscription_plans')
            ->where('slug', 'gratuit')
            ->update(['is_active' => true, 'updated_at' => now()]);
    }
}
