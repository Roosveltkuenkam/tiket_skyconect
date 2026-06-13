<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddKeyValueToSettingsTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('settings')) {
            Schema::create('settings', function (Blueprint $table) {
                $table->id();
                $table->string('key', 100)->unique();
                $table->text('value')->nullable();
                $table->string('type')->default('text');
                $table->string('setting_group', 50)->default('general');
                $table->boolean('is_secret')->default(false);
                $table->timestamps();

                $table->index('setting_group');
                $table->index('is_secret');
            });

            return;
        }

        Schema::table('settings', function (Blueprint $table) {
            if (!Schema::hasColumn('settings', 'key')) {
                $table->string('key', 100)->nullable()->unique()->after('id');
            }

            if (!Schema::hasColumn('settings', 'value')) {
                $table->text('value')->nullable()->after('key');
            }

            if (!Schema::hasColumn('settings', 'type')) {
                $table->string('type')->default('text')->after('value');
            }

            if (!Schema::hasColumn('settings', 'setting_group')) {
                $table->string('setting_group', 50)->default('general')->after('type');
            }

            if (!Schema::hasColumn('settings', 'is_secret')) {
                $table->boolean('is_secret')->default(false)->after('setting_group');
            }
        });
    }

    public function down()
    {
        //
    }
}