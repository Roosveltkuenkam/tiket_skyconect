<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubscriptionPlansTable extends Migration
{
    public function up()
    {
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->integer('monthly_price')->default(0);
            $table->integer('max_routers')->nullable();
            $table->integer('max_tickets_per_month')->nullable();
            $table->integer('max_sales_per_month')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'monthly_price']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('subscription_plans');
    }
}
