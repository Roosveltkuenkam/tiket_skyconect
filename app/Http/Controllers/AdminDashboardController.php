<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Order;
use App\Models\Ticket;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $plans = Plan::all();

        $ordersCount = Order::count();

        $paidOrdersCount = Order::where('status', 'paid')->count();

        $ticketsCount = Ticket::count();

        $latestOrders = Order::with(['plan', 'ticket'])
            ->latest()
            ->take(10)
            ->get();

        return view('admin.dashboard', compact(
            'plans',
            'ordersCount',
            'paidOrdersCount',
            'ticketsCount',
            'latestOrders'
        ));
    }
}