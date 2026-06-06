<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Services\ActivityLogger;
use Illuminate\Console\Command;

class ExpirePendingOrders extends Command
{
    protected $signature = 'orders:expire-pending {--dry-run : Affiche les commandes expirees sans les annuler}';

    protected $description = 'Annule les commandes pending dont expires_at est depassee.';

    public function handle()
    {
        $expiredCount = 0;

        Order::where('status', 'pending')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->orderBy('id')
            ->chunkById(100, function ($orders) use (&$expiredCount) {
                foreach ($orders as $order) {
                    $expiredCount++;

                    if ($this->option('dry-run')) {
                        $this->line("Commande expiree: {$order->reference}");
                        continue;
                    }

                    $order->update(['status' => 'cancelled']);

                    $order->payment()
                        ->where('status', 'pending')
                        ->update(['status' => 'cancelled']);

                    ActivityLogger::log('order.auto_expired', $order, [
                        'order_reference' => $order->reference,
                        'expires_at' => optional($order->expires_at)->toDateTimeString(),
                    ]);
                }
            });

        $message = $this->option('dry-run')
            ? "{$expiredCount} commande(s) expiree(s) detectee(s)."
            : "{$expiredCount} commande(s) expiree(s) annulee(s).";

        $this->info($message);

        return 0;
    }
}
