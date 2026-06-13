<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClientQuotaTopupsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('client_quota_topups')) {
            return;
        }

        Schema::create('client_quota_topups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('reference')->unique();
            $table->integer('amount');
            $table->string('method', 100);
            $table->string('phone')->nullable();
            $table->string('external_reference')->nullable();
            $table->string('status', 30)->default('pending');
            $table->text('client_note')->nullable();
            $table->text('admin_note')->nullable();
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('confirmed_at')->nullable();
            $table->foreignId('rejected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('rejected_at')->nullable();
            $table->json('raw_response')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('created_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('client_quota_topups');
    }
}
