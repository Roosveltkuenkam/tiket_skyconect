<?php

namespace App\Http\Controllers;

use App\Models\Order;

class TicketController extends Controller
{
    public function show(Order $order, $accessToken)
    {
        abort_unless($order->hasValidPublicAccessToken($accessToken), 404);

        if ($order->status !== 'paid' || !$order->ticket) {
            abort(403, 'Ticket non disponible.');
        }

        return view('tickets.show', compact('order'));
    }
}
