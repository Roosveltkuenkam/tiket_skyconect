<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateQuotaTransactionsTable extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('quota_transactions')) {
            Schema::create('quota_transactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('type', 50);
                $table->integer('amount');
                $table->integer('balance_before');
                $table->integer('balance_after');
                $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('payment_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->text('note')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'type']);
                $table->index('created_at');
            });
        }

        if (Schema::hasTable('client_wallet_transactions')) {
            DB::table('client_wallet_transactions')
                ->orderBy('id')
                ->chunkById(500, function ($transactions) {
                    foreach ($transactions as $transaction) {
                        DB::table('quota_transactions')->updateOrInsert(
                            [
                                'user_id' => $transaction->user_id,
                                'type' => $this->mapType($transaction->type),
                                'amount' => $transaction->amount,
                                'balance_before' => $transaction->balance_before,
                                'balance_after' => $transaction->balance_after,
                                'created_at' => $transaction->created_at,
                            ],
                            [
                                'order_id' => $transaction->order_id,
                                'payment_id' => $transaction->payment_id,
                                'created_by' => $transaction->performed_by,
                                'note' => $transaction->note,
                                'metadata' => $transaction->metadata,
                                'updated_at' => $transaction->updated_at,
                            ]
                        );
                    }
                });
        }
    }

    public function down()
    {
        Schema::dropIfExists('quota_transactions');
    }

    private function mapType($type)
    {
        if ($type === 'quota_credit') {
            return 'topup';
        }

        if ($type === 'sale_commission') {
            return 'sale_commission';
        }

        return 'adjustment';
    }
}
