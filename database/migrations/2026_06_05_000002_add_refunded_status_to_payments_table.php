<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddRefundedStatusToPaymentsTable extends Migration
{
    public function up()
    {
        DB::statement("ALTER TABLE payments MODIFY status ENUM('pending', 'successful', 'failed', 'cancelled', 'refunded') NOT NULL DEFAULT 'pending'");
    }

    public function down()
    {
        DB::statement("ALTER TABLE payments MODIFY status ENUM('pending', 'successful', 'failed', 'cancelled') NOT NULL DEFAULT 'pending'");
    }
}
