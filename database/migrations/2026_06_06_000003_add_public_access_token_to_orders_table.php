<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AddPublicAccessTokenToOrdersTable extends Migration
{
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'public_access_token')) {
                $table->string('public_access_token', 64)->nullable()->unique()->after('reference');
            }
        });

        DB::table('orders')
            ->whereNull('public_access_token')
            ->orderBy('id')
            ->get(['id'])
            ->each(function ($order) {
                DB::table('orders')
                    ->where('id', $order->id)
                    ->update(['public_access_token' => Str::random(48)]);
            });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'public_access_token')) {
                $table->dropUnique('orders_public_access_token_unique');
                $table->dropColumn('public_access_token');
            }
        });
    }
}
