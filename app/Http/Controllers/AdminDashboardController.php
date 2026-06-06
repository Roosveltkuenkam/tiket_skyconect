<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Router;
use App\Models\Ticket;
use App\Models\User;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $plans = Plan::all();
        $ordersCount = Order::count();
        $paidOrdersCount = Order::where('status', 'paid')->count();
        $ticketsCount = Ticket::count();
        $ticketsAvailable = Ticket::where('status', 'available')->count();
        $revenueToday = Order::where('status', 'paid')
            ->whereDate('created_at', today())
            ->sum('amount');
        $salesWeek = Order::where('status', 'paid')
            ->where('created_at', '>=', now()->subDays(7))
            ->count();
        $salesMonth = Order::where('status', 'paid')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $latestOrders = Order::with(['plan', 'ticket'])
            ->latest()
            ->take(10)
            ->get();

        $chartRows = collect(range(6, 0))
            ->map(function ($daysAgo) {
                $date = now()->subDays($daysAgo)->toDateString();

                $query = Order::where('status', 'paid')
                    ->whereDate('created_at', $date);

                return [
                    'label' => now()->subDays($daysAgo)->format('d/m'),
                    'revenue' => (int) (clone $query)->sum('amount'),
                    'sales' => (int) (clone $query)->count(),
                ];
            });

        $maxChartRevenue = max(1, $chartRows->max('revenue'));
        $maxChartSales = max(1, $chartRows->max('sales'));

        $globalStats = [
            'clients_total' => User::where('role', User::ROLE_CLIENT)->count(),
            'clients_active' => User::where('role', User::ROLE_CLIENT)->where('is_active', true)->count(),
            'clients_inactive' => User::where('role', User::ROLE_CLIENT)->where('is_active', false)->count(),
            'routers_connected' => Router::where('status', 'active')->count(),
            'tickets_sold_today' => Ticket::where('status', 'sold')->whereDate('sold_at', today())->count(),
            'revenue_total' => Order::where('status', 'paid')->sum('amount'),
            'revenue_today' => Order::where('status', 'paid')->whereDate('created_at', today())->sum('amount'),
            'revenue_month' => Order::where('status', 'paid')
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('amount'),
            'payments_successful' => Payment::where('status', 'successful')->count(),
            'payments_failed' => Payment::where('status', 'failed')->count(),
            'orders_pending' => Order::where('status', 'pending')->count(),
        ];

        $lowStockPlans = Plan::with('router')
            ->withCount([
                'tickets as available_tickets_count' => function ($query) {
                    $query->where('status', 'available');
                },
            ])
            ->having('available_tickets_count', '<=', 5)
            ->orderBy('available_tickets_count')
            ->take(8)
            ->get();

        $newClients = User::where('role', User::ROLE_CLIENT)
            ->latest()
            ->take(8)
            ->get();

        $onboarding = null;

        return view('admin.dashboard', compact(
            'plans',
            'ordersCount',
            'paidOrdersCount',
            'ticketsCount',
            'ticketsAvailable',
            'revenueToday',
            'salesWeek',
            'salesMonth',
            'latestOrders',
            'chartRows',
            'maxChartRevenue',
            'maxChartSales',
            'globalStats',
            'lowStockPlans',
            'newClients',
            'onboarding'
        ));
    }
}
