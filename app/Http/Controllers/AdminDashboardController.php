<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Router;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function index(Request $request)
    {
        $scopePlans = function ($query) use ($request) {
            if ($request->user() && $request->user()->isClientOwner()) {
                $query->whereHas('router', function ($routerQuery) use ($request) {
                    $routerQuery->where('user_id', $request->user()->id);
                });
            }
        };

        $scopeOrders = function ($query) use ($request) {
            if ($request->user() && $request->user()->isClientOwner()) {
                $query->whereHas('plan.router', function ($routerQuery) use ($request) {
                    $routerQuery->where('user_id', $request->user()->id);
                });
            }
        };

        $scopeTickets = function ($query) use ($request) {
            if ($request->user() && $request->user()->isClientOwner()) {
                $query->whereHas('plan.router', function ($routerQuery) use ($request) {
                    $routerQuery->where('user_id', $request->user()->id);
                });
            }
        };

        $plansQuery = Plan::query();
        $scopePlans($plansQuery);
        $plans = $plansQuery->get();

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

        $globalStats = null;

        if ($request->user() && $request->user()->isBackOfficeUser()) {
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
        } else {
            $lowStockPlans = collect();
            $newClients = collect();
        }

        $onboarding = null;

        if ($request->user() && $request->user()->isClientOwner()) {
            $ownerRoutersCount = Router::where('user_id', $request->user()->id)->count();
            $ownerPlansCount = $plans->count();
            $ownerTicketsAvailable = $ticketsAvailable;
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
                $ownerTicketsAvailable > 0,
                true,
                $ownerSaleLinkReady,
            ])->filter()->count();

            $onboarding = [
                'completed' => $completed,
                'total' => 5,
                'progress' => (int) round(($completed / 5) * 100),
            ];
        }

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
