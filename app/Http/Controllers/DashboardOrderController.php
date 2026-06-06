<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Router;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class DashboardOrderController extends Controller
{
    public function index(Request $request)
    {
        $filters = $this->filters($request);
        $baseQuery = $this->filteredOwnerOrders($request, $filters);
        $paidQuery = (clone $baseQuery)->where('orders.status', 'paid');

        $routers = Router::where('user_id', $request->user()->id)
            ->orderBy('name')
            ->get();

        $orders = (clone $baseQuery)
            ->with(['plan.router', 'ticket', 'payment'])
            ->latest()
            ->paginate($filters['per_page'])
            ->appends($request->query());

        $summary = [
            'paid_count' => (clone $paidQuery)->count(),
            'revenue' => (clone $paidQuery)->sum('orders.amount'),
            'top_hotspot' => $this->topHotspot(clone $paidQuery),
            'top_network' => $this->topNetwork(clone $paidQuery),
        ];

        $dailySeries = $this->dailySeries(clone $paidQuery, $filters['date_from'], $filters['date_to']);
        $hourlySeries = $this->hourlySeries(clone $paidQuery);
        $hotspotBreakdown = $this->hotspotBreakdown(clone $paidQuery);
        $networkBreakdown = $this->networkBreakdown(clone $paidQuery);

        return view('dashboard.orders.index', compact(
            'orders',
            'routers',
            'filters',
            'summary',
            'dailySeries',
            'hourlySeries',
            'hotspotBreakdown',
            'networkBreakdown'
        ));
    }

    private function filters(Request $request): array
    {
        $dateFrom = $request->input('date_from') ?: now()->subDays(30)->toDateString();
        $dateTo = $request->input('date_to') ?: now()->toDateString();

        if ($dateFrom > $dateTo) {
            [$dateFrom, $dateTo] = [$dateTo, $dateFrom];
        }

        return [
            'router_id' => $request->input('router_id'),
            'status' => $request->input('status'),
            'search' => trim((string) $request->input('search', $request->input('reference', $request->input('phone', '')))),
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'per_page' => min(max((int) $request->input('per_page', 25), 10), 100),
        ];
    }

    private function filteredOwnerOrders(Request $request, array $filters)
    {
        return Order::query()
            ->whereHas('plan.router', function ($routerQuery) use ($request) {
                $routerQuery->where('user_id', $request->user()->id);
            })
            ->when($filters['router_id'], function ($query, $routerId) {
                $query->whereHas('plan', function ($planQuery) use ($routerId) {
                    $planQuery->where('router_id', $routerId);
                });
            })
            ->when($filters['status'], function ($query, $status) {
                $query->where('orders.status', $status);
            })
            ->when($filters['search'], function ($query, $search) {
                $query->where(function ($searchQuery) use ($search) {
                    $searchQuery
                        ->where('orders.reference', 'like', '%' . $search . '%')
                        ->orWhere('orders.customer_phone', 'like', '%' . $search . '%')
                        ->orWhereHas('plan', function ($planQuery) use ($search) {
                            $planQuery->where('name', 'like', '%' . $search . '%');
                        })
                        ->orWhereHas('plan.router', function ($routerQuery) use ($search) {
                            $routerQuery->where('name', 'like', '%' . $search . '%');
                        });
                });
            })
            ->whereDate('orders.created_at', '>=', $filters['date_from'])
            ->whereDate('orders.created_at', '<=', $filters['date_to']);
    }

    private function topHotspot($query)
    {
        return $query
            ->join('plans', 'plans.id', '=', 'orders.plan_id')
            ->join('routers', 'routers.id', '=', 'plans.router_id')
            ->selectRaw('routers.name as label, COUNT(*) as count, COALESCE(SUM(orders.amount), 0) as amount')
            ->groupBy('routers.id', 'routers.name')
            ->orderByDesc('amount')
            ->first();
    }

    private function topNetwork($query)
    {
        return $query
            ->leftJoin('payments', 'payments.order_id', '=', 'orders.id')
            ->selectRaw("COALESCE(NULLIF(payments.payment_method, ''), NULLIF(payments.provider, ''), 'simulation') as label, COUNT(*) as count, COALESCE(SUM(orders.amount), 0) as amount")
            ->groupBy(DB::raw("COALESCE(NULLIF(payments.payment_method, ''), NULLIF(payments.provider, ''), 'simulation')"))
            ->orderByDesc('amount')
            ->first();
    }

    private function dailySeries($query, string $dateFrom, string $dateTo): array
    {
        $rows = $query
            ->selectRaw('DATE(orders.created_at) as label, COUNT(*) as sales, COALESCE(SUM(orders.amount), 0) as revenue')
            ->groupBy(DB::raw('DATE(orders.created_at)'))
            ->orderBy('label')
            ->get()
            ->keyBy('label');

        $series = [];

        foreach (CarbonPeriod::create(Carbon::parse($dateFrom), Carbon::parse($dateTo)) as $day) {
            $key = $day->toDateString();
            $row = $rows->get($key);

            $series[] = [
                'label' => $day->format('d/m'),
                'revenue' => $row ? (float) $row->revenue : 0,
                'sales' => $row ? (int) $row->sales : 0,
            ];
        }

        return $series;
    }

    private function hourlySeries($query): array
    {
        $rows = $query
            ->selectRaw('HOUR(orders.created_at) as hour, COUNT(*) as sales')
            ->groupBy(DB::raw('HOUR(orders.created_at)'))
            ->pluck('sales', 'hour');

        $series = [];

        for ($hour = 0; $hour < 24; $hour++) {
            $series[] = [
                'label' => $hour . 'h',
                'sales' => (int) ($rows[$hour] ?? 0),
            ];
        }

        return $series;
    }

    private function hotspotBreakdown($query)
    {
        return $query
            ->join('plans', 'plans.id', '=', 'orders.plan_id')
            ->join('routers', 'routers.id', '=', 'plans.router_id')
            ->selectRaw('routers.name as label, COUNT(*) as count, COALESCE(SUM(orders.amount), 0) as amount')
            ->groupBy('routers.id', 'routers.name')
            ->orderByDesc('amount')
            ->limit(5)
            ->get();
    }

    private function networkBreakdown($query)
    {
        return $query
            ->leftJoin('payments', 'payments.order_id', '=', 'orders.id')
            ->selectRaw("COALESCE(NULLIF(payments.payment_method, ''), NULLIF(payments.provider, ''), 'simulation') as label, COUNT(*) as count, COALESCE(SUM(orders.amount), 0) as amount")
            ->groupBy(DB::raw("COALESCE(NULLIF(payments.payment_method, ''), NULLIF(payments.provider, ''), 'simulation')"))
            ->orderByDesc('amount')
            ->limit(5)
            ->get();
    }
}
