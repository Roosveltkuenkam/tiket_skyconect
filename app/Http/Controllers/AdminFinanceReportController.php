<?php

namespace App\Http\Controllers;

use App\Models\ClientSubscription;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Refund;
use App\Models\Router;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminFinanceReportController extends Controller
{
    public function index(Request $request)
    {
        $data = $this->buildReport($request);

        ActivityLogger::log('finance_report.viewed', 'FinanceReport', [
            'filters' => $data['filters'],
        ], $request);

        return view('admin.reports.finance', $data);
    }

    public function export(Request $request)
    {
        $data = $this->buildReport($request);
        $fileName = 'skyconnect-rapport-financier-' . now()->format('Ymd-His') . '.csv';

        ActivityLogger::log('finance_report.exported', 'FinanceReport', [
            'filters' => $data['filters'],
        ], $request);

        return response()->streamDownload(function () use ($data) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Rapport financier SkyConnect']);
            fputcsv($handle, ['Periode', $data['filters']['date_from'], $data['filters']['date_to']]);
            fputcsv($handle, []);

            fputcsv($handle, ['Indicateur', 'Valeur']);
            foreach ($data['kpis'] as $label => $value) {
                fputcsv($handle, [$label, $value]);
            }

            $this->writeSection($handle, 'Ventes par jour', ['Date', 'Ventes', 'CA'], $data['salesByDay']);
            $this->writeSection($handle, 'Ventes par mois', ['Mois', 'Ventes', 'CA'], $data['salesByMonth']);
            $this->writeSection($handle, 'Ventes par client', ['Client', 'Ventes', 'CA'], $data['salesByClient']);
            $this->writeSection($handle, 'Ventes par routeur', ['Routeur', 'Ventes', 'CA'], $data['salesByRouter']);
            $this->writeSection($handle, 'Paiements par provider', ['Provider', 'Paiements', 'Montant'], $data['paymentsByProvider']);
            $this->writeSection($handle, 'Remboursements par statut', ['Statut', 'Nombre', 'Montant'], $data['refundsByStatus']);
            $this->writeSection($handle, 'Revenus abonnements', ['Statut', 'Nombre', 'Montant'], $data['subscriptionRevenueByStatus']);

            fclose($handle);
        }, $fileName, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function buildReport(Request $request)
    {
        $filters = $this->filters($request);

        $paidOrders = $this->ordersQuery($filters)->where('orders.status', 'paid');
        $allOrders = $this->ordersQuery($filters);
        $payments = $this->paymentsQuery($filters);
        $refunds = $this->refundsQuery($filters);
        $subscriptions = $this->subscriptionsQuery($filters);

        $revenue = (clone $paidOrders)->sum('orders.amount');
        $salesCount = (clone $paidOrders)->count();
        $refundsAmount = (clone $refunds)->where('refunds.status', 'processed')->sum('refunds.amount');
        $subscriptionRevenue = (clone $subscriptions)->sum('client_subscriptions.last_payment_amount');
        $commissionAmount = round($revenue * ((float) $filters['commission_rate'] / 100));

        $kpis = [
            'Chiffre affaires ventes' => $revenue,
            'Nombre ventes payees' => $salesCount,
            'Panier moyen' => $salesCount > 0 ? round($revenue / $salesCount) : 0,
            'Commandes en attente' => (clone $allOrders)->where('orders.status', 'pending')->count(),
            'Paiements reussis' => (clone $payments)->where('payments.status', 'successful')->count(),
            'Paiements echoues' => (clone $payments)->where('payments.status', 'failed')->count(),
            'Remboursements traites' => $refundsAmount,
            'Commissions estimees' => $commissionAmount,
            'Revenus abonnements' => $subscriptionRevenue,
            'Revenus SkyConnect estimes' => $subscriptionRevenue + $commissionAmount,
            'Solde net estime' => $revenue - $refundsAmount + $subscriptionRevenue,
        ];

        return [
            'filters' => $filters,
            'clients' => User::where('role', User::ROLE_CLIENT)->orderBy('name')->get(),
            'routers' => Router::with('user')->orderBy('name')->get(),
            'providers' => Payment::select('provider')->distinct()->orderBy('provider')->pluck('provider'),
            'kpis' => $kpis,
            'salesByDay' => $this->salesByDay($filters),
            'salesByMonth' => $this->salesByMonth($filters),
            'salesByClient' => $this->salesByClient($filters),
            'salesByRouter' => $this->salesByRouter($filters),
            'paymentsByProvider' => $this->paymentsByProvider($filters),
            'refundsByStatus' => $this->refundsByStatus($filters),
            'subscriptionRevenueByStatus' => $this->subscriptionRevenueByStatus($filters),
        ];
    }

    private function filters(Request $request)
    {
        return [
            'date_from' => $request->date_from ?: now()->startOfMonth()->toDateString(),
            'date_to' => $request->date_to ?: now()->toDateString(),
            'client_id' => $request->client_id,
            'router_id' => $request->router_id,
            'provider' => $request->provider,
            'commission_rate' => $request->commission_rate ?: 0,
        ];
    }

    private function ordersQuery(array $filters)
    {
        return Order::query()
            ->leftJoin('plans', 'orders.plan_id', '=', 'plans.id')
            ->leftJoin('routers', 'plans.router_id', '=', 'routers.id')
            ->leftJoin('payments', 'payments.order_id', '=', 'orders.id')
            ->whereDate('orders.created_at', '>=', $filters['date_from'])
            ->whereDate('orders.created_at', '<=', $filters['date_to'])
            ->when($filters['client_id'], function ($query) use ($filters) {
                $query->where('routers.user_id', $filters['client_id']);
            })
            ->when($filters['router_id'], function ($query) use ($filters) {
                $query->where('routers.id', $filters['router_id']);
            })
            ->when($filters['provider'], function ($query) use ($filters) {
                $query->where('payments.provider', $filters['provider']);
            });
    }

    private function paymentsQuery(array $filters)
    {
        return Payment::query()
            ->leftJoin('orders', 'payments.order_id', '=', 'orders.id')
            ->leftJoin('plans', 'orders.plan_id', '=', 'plans.id')
            ->leftJoin('routers', 'plans.router_id', '=', 'routers.id')
            ->whereDate('payments.created_at', '>=', $filters['date_from'])
            ->whereDate('payments.created_at', '<=', $filters['date_to'])
            ->when($filters['client_id'], function ($query) use ($filters) {
                $query->where('routers.user_id', $filters['client_id']);
            })
            ->when($filters['router_id'], function ($query) use ($filters) {
                $query->where('routers.id', $filters['router_id']);
            })
            ->when($filters['provider'], function ($query) use ($filters) {
                $query->where('payments.provider', $filters['provider']);
            });
    }

    private function refundsQuery(array $filters)
    {
        return Refund::query()
            ->leftJoin('payments', 'refunds.payment_id', '=', 'payments.id')
            ->leftJoin('orders', 'refunds.order_id', '=', 'orders.id')
            ->leftJoin('plans', 'orders.plan_id', '=', 'plans.id')
            ->leftJoin('routers', 'plans.router_id', '=', 'routers.id')
            ->whereDate('refunds.created_at', '>=', $filters['date_from'])
            ->whereDate('refunds.created_at', '<=', $filters['date_to'])
            ->when($filters['client_id'], function ($query) use ($filters) {
                $query->where('refunds.client_id', $filters['client_id']);
            })
            ->when($filters['router_id'], function ($query) use ($filters) {
                $query->where('routers.id', $filters['router_id']);
            })
            ->when($filters['provider'], function ($query) use ($filters) {
                $query->where('payments.provider', $filters['provider']);
            });
    }

    private function subscriptionsQuery(array $filters)
    {
        return ClientSubscription::query()
            ->whereNotNull('client_subscriptions.last_payment_amount')
            ->whereDate('client_subscriptions.last_paid_at', '>=', $filters['date_from'])
            ->whereDate('client_subscriptions.last_paid_at', '<=', $filters['date_to'])
            ->when($filters['client_id'], function ($query) use ($filters) {
                $query->where('client_subscriptions.user_id', $filters['client_id']);
            });
    }

    private function salesByDay(array $filters)
    {
        return $this->ordersQuery($filters)
            ->where('orders.status', 'paid')
            ->selectRaw('DATE(orders.created_at) as label, COUNT(*) as count, COALESCE(SUM(orders.amount), 0) as amount')
            ->groupBy(DB::raw('DATE(orders.created_at)'))
            ->orderBy('label')
            ->get();
    }

    private function salesByMonth(array $filters)
    {
        return $this->ordersQuery($filters)
            ->where('orders.status', 'paid')
            ->selectRaw("DATE_FORMAT(orders.created_at, '%Y-%m') as label, COUNT(*) as count, COALESCE(SUM(orders.amount), 0) as amount")
            ->groupBy(DB::raw("DATE_FORMAT(orders.created_at, '%Y-%m')"))
            ->orderBy('label')
            ->get();
    }

    private function salesByClient(array $filters)
    {
        return $this->ordersQuery($filters)
            ->leftJoin('users', 'routers.user_id', '=', 'users.id')
            ->where('orders.status', 'paid')
            ->selectRaw("COALESCE(users.business_name, users.name, 'Sans client') as label, COUNT(*) as count, COALESCE(SUM(orders.amount), 0) as amount")
            ->groupBy('users.id', 'users.business_name', 'users.name')
            ->orderByDesc('amount')
            ->limit(20)
            ->get();
    }

    private function salesByRouter(array $filters)
    {
        return $this->ordersQuery($filters)
            ->where('orders.status', 'paid')
            ->selectRaw("COALESCE(routers.name, 'Sans routeur') as label, COUNT(*) as count, COALESCE(SUM(orders.amount), 0) as amount")
            ->groupBy('routers.id', 'routers.name')
            ->orderByDesc('amount')
            ->limit(20)
            ->get();
    }

    private function paymentsByProvider(array $filters)
    {
        return $this->paymentsQuery($filters)
            ->selectRaw("COALESCE(payments.provider, 'inconnu') as label, COUNT(*) as count, COALESCE(SUM(payments.amount), 0) as amount")
            ->groupBy('payments.provider')
            ->orderByDesc('amount')
            ->get();
    }

    private function refundsByStatus(array $filters)
    {
        return $this->refundsQuery($filters)
            ->selectRaw('refunds.status as label, COUNT(*) as count, COALESCE(SUM(refunds.amount), 0) as amount')
            ->groupBy('refunds.status')
            ->orderBy('refunds.status')
            ->get();
    }

    private function subscriptionRevenueByStatus(array $filters)
    {
        return $this->subscriptionsQuery($filters)
            ->selectRaw('client_subscriptions.status as label, COUNT(*) as count, COALESCE(SUM(client_subscriptions.last_payment_amount), 0) as amount')
            ->groupBy('client_subscriptions.status')
            ->orderBy('client_subscriptions.status')
            ->get();
    }

    private function writeSection($handle, $title, array $headers, $rows)
    {
        fputcsv($handle, []);
        fputcsv($handle, [$title]);
        fputcsv($handle, $headers);

        foreach ($rows as $row) {
            fputcsv($handle, [$row->label, $row->count, $row->amount]);
        }
    }
}
