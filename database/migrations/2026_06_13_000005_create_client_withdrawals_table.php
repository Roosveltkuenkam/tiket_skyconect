<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClientWithdrawalsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('client_withdrawals')) {
            return;
        }

        Schema::create('client_withdrawals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('reference')->unique();
            $table->integer('amount');
            $table->string('fee_type', 30)->default('fixed');
            $table->integer('fee_amount')->default(0);
            $table->integer('net_amount');
            $table->string('method', 100);
            $table->string('account_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('status', 30)->default('pending');
            $table->text('client_note')->nullable();
            $table->text('admin_note')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('processed_at')->nullable();
            $table->foreignId('rejected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('rejected_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('created_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('client_withdrawals');
    }
}
