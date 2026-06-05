<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRoleAndProfileFieldsToUsersTable extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->string('business_name')->nullable()->after('phone');
            $table->string('city')->nullable()->after('business_name');
            $table->string('country')->nullable()->after('city');
            $table->string('role')->default('client')->after('password');
            $table->boolean('is_active')->default(true)->after('role');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone',
                'business_name',
                'city',
                'country',
                'role',
                'is_active',
            ]);
        });
    }
}
