<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\AdminNotification;
use App\Models\Payment;
use App\Models\Ticket;
use App\Services\ActivityLogger;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function simulate(Order $order, $accessToken)
    {
        abort_unless($order->hasValidPublicAccessToken($accessToken), 404);

        if ($order->status === 'paid') {
            return redirect()->route('tickets.show', $order->publicRouteParameters());
        }

        if ($order->isExpired()) {
            $order->expire();
            ActivityLogger::log('order.expired_before_payment', $order, [
                'order_reference' => $order->reference,
                'expires_at' => optional($order->expires_at)->toDateTimeString(),
            ]);

            abort(403, 'Cette commande a expire. Veuillez creer une nouvelle commande.');
        }

        $payment = null;

        DB::transaction(function () use ($order, &$payment) {
            $order = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();

            if ($order->status === 'paid') {
                return;
            }

            if ($order->isExpired()) {
                $order->expire();
                ActivityLogger::log('order.expired_before_payment', $order, [
                    'order_reference' => $order->reference,
                    'expires_at' => optional($order->expires_at)->toDateTimeString(),
                ]);

                abort(403, 'Cette commande a expire. Veuillez creer une nouvelle commande.');
            }

            $order->load('plan.router.user');

            $owner = optional(optional($order->plan)->router)->user;

            if ($owner && (! $owner->canUseSubscription() || $owner->subscriptionLimitReached('sales'))) {
                AdminNotification::notify(
                    'account_suspended_or_limited',
                    'Vente bloquee par abonnement',
                    'Une vente a ete bloquee pour ' . $owner->name . ' a cause de l abonnement.',
                    'warning',
                    ['order_id' => $order->id, 'owner_id' => $owner->id]
                );

                abort(403, 'Le compte proprietaire WiFi ne peut pas recevoir de nouvelles ventes pour le moment.');
            }

            $ticket = Ticket::where('plan_id', $order->plan_id)
                ->where('status', 'available')
                ->lockForUpdate()
                ->first();

            if (!$ticket) {
                AdminNotification::notify(
                    'payment_confirmed_no_ticket',
                    'Paiement confirme sans ticket disponible',
                    'Une commande ne peut pas etre livree car aucun ticket nest disponible pour le forfait.',
                    'danger',
                    ['order_id' => $order->id, 'plan_id' => $order->plan_id]
                );

                abort(404, 'Aucun ticket disponible pour ce forfait.');
            }

            $ticket->update([
                'status' => 'sold',
                'sold_at' => now(),
            ]);

            $order->update([
                'ticket_id' => $ticket->id,
                'status' => 'paid',
            ]);

            $payment = Payment::create([
                'order_id' => $order->id,
                'provider' => 'test',
                'payment_method' => 'simulation',
                'amount' => $order->amount,
                'phone' => $order->customer_phone,
                'status' => 'successful',
                'paid_at' => now(),
                'raw_response' => [
                    'message' => 'Paiement simule avec succes',
                ],
            ]);
        });

        if ($payment) {
            ActivityLogger::log('payment.confirmed', $payment, [
                'order_id' => $order->id,
                'order_reference' => $order->reference,
                'provider' => $payment->provider,
                'amount' => $payment->amount,
                'status' => $payment->status,
            ]);
        }

        return redirect()->route('tickets.show', $order->publicRouteParameters());
    }
}
