<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Plan;

class PlanSeeder extends Seeder
{
    public function run()
    {
        Plan::create([
            'name' => '5 heures',
            'duration' => '5h',
            'price' => 250,
            'description' => 'Ticket WiFi valable pendant 5 heures',
            'is_active' => true,
        ]);

        Plan::create([
            'name' => '24 heures',
            'duration' => '24h',
            'price' => 500,
            'description' => 'Ticket WiFi valable pendant 24 heures',
            'is_active' => true,
        ]);

        Plan::create([
            'name' => '3 jours',
            'duration' => '3j',
            'price' => 1000,
            'description' => 'Ticket WiFi valable pendant 3 jours',
            'is_active' => true,
        ]);

        Plan::create([
            'name' => '7 jours',
            'duration' => '7j',
            'price' => 1850,
            'description' => 'Ticket WiFi valable pendant 7 jours',
            'is_active' => true,
        ]);

        Plan::create([
            'name' => '30 jours',
            'duration' => '30j',
            'price' => 5000,
            'description' => 'Ticket WiFi valable pendant 30 jours',
            'is_active' => true,
        ]);
    }
}