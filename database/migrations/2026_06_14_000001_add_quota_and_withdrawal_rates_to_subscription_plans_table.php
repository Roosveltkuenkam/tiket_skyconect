<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddQuotaAndWithdrawalRatesToSubscriptionPlansTable extends Migration
{
    public function up()
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            if (! Schema::hasColumn('subscription_plans', 'quota_rate_percent')) {
                $table->decimal('quota_rate_percent', 5, 2)->default(5)->after('max_sales_per_month');
            }

            if (! Schema::hasColumn('subscription_plans', 'withdrawal_fee_percent')) {
                $table->decimal('withdrawal_fee_percent', 5, 2)->default(5)->after('quota_rate_percent');
            }
        });
    }

    public function down()
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            if (Schema::hasColumn('subscription_plans', 'withdrawal_fee_percent')) {
                $table->dropColumn('withdrawal_fee_percent');
            }

            if (Schema::hasColumn('subscription_plans', 'quota_rate_percent')) {
                $table->dropColumn('quota_rate_percent');
            }
        });
    }
}
