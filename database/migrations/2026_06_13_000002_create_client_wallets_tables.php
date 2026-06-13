<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateClientWalletsTables extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('client_wallets')) {
            Schema::create('client_wallets', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');
                $table->integer('quota_balance')->default(0);
                $table->integer('total_quota_loaded')->default(0);
                $table->integer('total_quota_used')->default(0);
                $table->integer('total_sales_amount')->default(0);
                $table->integer('total_withdrawn')->default(0);
                $table->integer('pending_withdrawal_amount')->default(0);
                $table->timestamps();

                $table->index('quota_balance');
            });
        }

        if (! Schema::hasTable('client_wallet_transactions')) {
            Schema::create('client_wallet_transactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('client_wallet_id')->constrained()->onDelete('cascade');
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('payment_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('performed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->string('type', 50);
                $table->integer('amount');
                $table->integer('balance_before');
                $table->integer('balance_after');
                $table->string('reference')->nullable();
                $table->text('note')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'type']);
                $table->index('created_at');
            });
        }

        User::where('role', User::ROLE_CLIENT)->chunkById(100, function ($clients) {
            foreach ($clients as $client) {
                DB::table('client_wallets')->updateOrInsert(
                    ['user_id' => $client->id],
                    [
                        'quota_balance' => (int) ($client->quota_balance ?? 0),
                        'total_quota_loaded' => max(0, (int) ($client->quota_balance ?? 0)),
                        'total_quota_used' => 0,
                        'total_sales_amount' => 0,
                        'total_withdrawn' => 0,
                        'pending_withdrawal_amount' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        });
    }

    public function down()
    {
        Schema::dropIfExists('client_wallet_transactions');
        Schema::dropIfExists('client_wallets');
    }
}
