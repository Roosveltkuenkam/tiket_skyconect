<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Ticket;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function simulate(Order $order)
    {
        if ($order->status === 'paid') {
            return redirect()->route('tickets.show', $order);
        }

        DB::transaction(function () use ($order) {
            $ticket = Ticket::where('plan_id', $order->plan_id)
                ->where('status', 'available')
                ->lockForUpdate()
                ->first();

            if (!$ticket) {
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

            Payment::create([
                'order_id' => $order->id,
                'provider' => 'test',
                'payment_method' => 'simulation',
                'amount' => $order->amount,
                'phone' => $order->customer_phone,
                'status' => 'successful',
                'paid_at' => now(),
                'raw_response' => [
                    'message' => 'Paiement simulé avec succès',
                ],
            ]);
        });

        return redirect()->route('tickets.show', $order);
    }
}