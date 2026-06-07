<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Plan;
use App\Models\Router;
use App\Models\Ticket;
use Illuminate\Http\Request;

class DashboardDashboardController extends Controller
{
    public function index(Request $request)
    {
        $scopeOrders = function ($query) use ($request) {
            $query->whereHas('plan.router', function ($routerQuery) use ($request) {
                $routerQuery->where('user_id', $request->user()->id);
            });
        };

        $scopeTickets = function ($query) use ($request) {
            $query->whereHas('plan.router', function ($routerQuery) use ($request) {
                $routerQuery->where('user_id', $request->user()->id);
            });
        };

        $plans = Plan::whereHas('router', function ($query) use ($request) {
                $query->where('user_id', $request->user()->id);
            })
            ->get();

        $ordersCountQuery = Order::query();
        $scopeOrders($ordersCountQuery);
        $ordersCount = $ordersCountQuery->count();

        $paidOrdersCountQuery = Order::where('status', 'paid');
        $scopeOrders($paidOrdersCountQuery);
        $paidOrdersCount = $paidOrdersCountQuery->count();

        $ticketsCountQuery = Ticket::query();
        $scopeTickets($ticketsCountQuery);
        $ticketsCount = $ticketsCountQuery->count();

        $ticketsAvailableQuery = Ticket::where('status', 'available');
        $scopeTickets($ticketsAvailableQuery);
        $ticketsAvailable = $ticketsAvailableQuery->count();

        $revenueTodayQuery = Order::where('status', 'paid')
            ->whereDate('created_at', today());
        $scopeOrders($revenueTodayQuery);
        $revenueToday = $revenueTodayQuery->sum('amount');

        $salesWeekQuery = Order::where('status', 'paid')
            ->where('created_at', '>=', now()->subDays(7));
        $scopeOrders($salesWeekQuery);
        $salesWeek = $salesWeekQuery->count();

        $salesMonthQuery = Order::where('status', 'paid')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year);
        $scopeOrders($salesMonthQuery);
        $salesMonth = $salesMonthQuery->count();

        $latestOrders = Order::with(['plan', 'ticket'])
            ->latest();
        $scopeOrders($latestOrders);
        $latestOrders = $latestOrders->take(10)->get();

        $chartRows = collect(range(6, 0))
            ->map(function ($daysAgo) use ($scopeOrders) {
                $date = now()->subDays($daysAgo)->toDateString();

                $query = Order::where('status', 'paid')
                    ->whereDate('created_at', $date);
                $scopeOrders($query);

                return [
                    'label' => now()->subDays($daysAgo)->format('d/m'),
                    'revenue' => (int) (clone $query)->sum('amount'),
                    'sales' => (int) (clone $query)->count(),
                ];
            });

        $maxChartRevenue = max(1, $chartRows->max('revenue'));
        $maxChartSales = max(1, $chartRows->max('sales'));

        $ownerRoutersCount = Router::where('user_id', $request->user()->id)->count();
        $ownerPlansCount = $plans->count();
        $ownerSaleLinkReady = Plan::whereHas('router', function ($query) use ($request) {
                $query->where('user_id', $request->user()->id);
            })
            ->where('is_active', true)
            ->whereHas('tickets', function ($query) {
                $query->where('status', 'available');
            })
            ->exists();

        $completed = collect([
            $ownerRoutersCount > 0,
            $ownerPlansCount > 0,
            $ticketsAvailable > 0,
            true,
            $ownerSaleLinkReady,
        ])->filter()->count();

        $onboarding = [
            'completed' => $completed,
            'total' => 5,
            'progress' => (int) round(($completed / 5) * 100),
        ];

        $globalStats = null;
        $lowStockPlans = collect();
        $newClients = collect();

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
