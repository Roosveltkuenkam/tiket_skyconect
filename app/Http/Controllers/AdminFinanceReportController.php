<?php

namespace App\Http\Controllers;

use App\Models\ClientSubscription;
use App\Models\ClientWallet;
use App\Models\Order;
use App\Models\Payment;
use App\Models\QuotaTransaction;
use App\Models\Refund;
use App\Models\Router;
use App\Models\User;
use App\Models\WithdrawalRequest;
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
            $this->writeSection($handle, 'Quota par type', ['Type', 'Operations', 'Montant'], $data['quotaByType']);
            $this->writeSection($handle, 'Retraits par statut', ['Statut', 'Demandes', 'Montant'], $data['withdrawalsByStatus']);
            $this->writeWalletBalances($handle, $data['walletBalances']);

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
        $quotaTransactions = $this->quotaTransactionsQuery($filters);
        $withdrawals = $this->withdrawalsQuery($filters);
        $walletBalances = $this->walletBalances($filters);

        $revenue = (clone $paidOrders)->sum('orders.amount');
        $salesCount = (clone $paidOrders)->count();
        $refundsAmount = (clone $refunds)->where('refunds.status', 'processed')->sum('refunds.amount');
        $subscriptionRevenue = (clone $subscriptions)->sum('client_subscriptions.last_payment_amount');
        $commissionAmount = round($revenue * ((float) $filters['commission_rate'] / 100));
        $quotaLoaded = (clone $quotaTransactions)->where('quota_transactions.type', QuotaTransaction::TYPE_TOPUP)->sum('quota_transactions.amount');
        $quotaConsumed = (clone $quotaTransactions)->where('quota_transactions.type', QuotaTransaction::TYPE_SALE_COMMISSION)->sum('quota_transactions.amount');
        $withdrawalsRequested = (clone $withdrawals)
            ->whereIn('withdrawal_requests.status', [
                WithdrawalRequest::STATUS_REQUESTED,
                WithdrawalRequest::STATUS_APPROVED,
                WithdrawalRequest::STATUS_PROCESSED,
            ])
            ->sum('withdrawal_requests.amount_requested');
        $withdrawalsProcessed = (clone $withdrawals)
            ->where('withdrawal_requests.status', WithdrawalRequest::STATUS_PROCESSED)
            ->sum('withdrawal_requests.amount_requested');
        $withdrawalFees = (clone $withdrawals)
            ->where('withdrawal_requests.status', WithdrawalRequest::STATUS_PROCESSED)
            ->sum('withdrawal_requests.fee_amount');
        $clientBalanceDue = $walletBalances->sum('balance_due');
        $quotaBalanceRemaining = $walletBalances->sum('quota_balance');

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
            'Quota charge' => $quotaLoaded,
            'Quota consomme' => $quotaConsumed,
            'Commissions SkyConnect' => $quotaConsumed,
            'Retraits demandes' => $withdrawalsRequested,
            'Retraits traites' => $withdrawalsProcessed,
            'Frais retrait collectes' => $withdrawalFees,
            'Solde du aux clients' => $clientBalanceDue,
            'Solde quota restant' => $quotaBalanceRemaining,
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
            'quotaByType' => $this->quotaByType($filters),
            'withdrawalsByStatus' => $this->withdrawalsByStatus($filters),
            'walletBalances' => $walletBalances,
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

    private function quotaTransactionsQuery(array $filters)
    {
        return QuotaTransaction::query()
            ->leftJoin('orders', 'quota_transactions.order_id', '=', 'orders.id')
            ->leftJoin('plans', 'orders.plan_id', '=', 'plans.id')
            ->leftJoin('routers', 'plans.router_id', '=', 'routers.id')
            ->leftJoin('payments', 'quota_transactions.payment_id', '=', 'payments.id')
            ->whereDate('quota_transactions.created_at', '>=', $filters['date_from'])
            ->whereDate('quota_transactions.created_at', '<=', $filters['date_to'])
            ->when($filters['client_id'], function ($query) use ($filters) {
                $query->where('quota_transactions.user_id', $filters['client_id']);
            })
            ->when($filters['router_id'], function ($query) use ($filters) {
                $query->where('routers.id', $filters['router_id']);
            })
            ->when($filters['provider'], function ($query) use ($filters) {
                $query->where('payments.provider', $filters['provider']);
            });
    }

    private function withdrawalsQuery(array $filters)
    {
        return WithdrawalRequest::query()
            ->whereDate('withdrawal_requests.requested_at', '>=', $filters['date_from'])
            ->whereDate('withdrawal_requests.requested_at', '<=', $filters['date_to'])
            ->when($filters['client_id'], function ($query) use ($filters) {
                $query->where('withdrawal_requests.user_id', $filters['client_id']);
            })
            ->when($filters['router_id'], function ($query) use ($filters) {
                $query->whereExists(function ($subQuery) use ($filters) {
                    $subQuery->select(DB::raw(1))
                        ->from('routers')
                        ->whereColumn('routers.user_id', 'withdrawal_requests.user_id')
                        ->where('routers.id', $filters['router_id']);
                });
            });
    }

    private function walletBalances(array $filters)
    {
        return ClientWallet::query()
            ->with('user')
            ->when($filters['client_id'], function ($query) use ($filters) {
                $query->where('client_wallets.user_id', $filters['client_id']);
            })
            ->when($filters['router_id'], function ($query) use ($filters) {
                $query->whereExists(function ($subQuery) use ($filters) {
                    $subQuery->select(DB::raw(1))
                        ->from('routers')
                        ->whereColumn('routers.user_id', 'client_wallets.user_id')
                        ->where('routers.id', $filters['router_id']);
                });
            })
            ->orderByDesc('total_sales_amount')
            ->get()
            ->map(function (ClientWallet $wallet) {
                $gross = max(0, $wallet->total_sales_amount - $wallet->total_quota_used);
                $balanceDue = max(0, $gross - $wallet->total_withdrawn);
                $available = max(0, $balanceDue - $wallet->pending_withdrawal_amount);

                $wallet->gross_client_balance = $gross;
                $wallet->balance_due = $balanceDue;
                $wallet->available_withdrawal_balance = $available;

                return $wallet;
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

    private function quotaByType(array $filters)
    {
        return $this->quotaTransactionsQuery($filters)
            ->selectRaw('quota_transactions.type as label, COUNT(*) as count, COALESCE(SUM(quota_transactions.amount), 0) as amount')
            ->groupBy('quota_transactions.type')
            ->orderBy('quota_transactions.type')
            ->get();
    }

    private function withdrawalsByStatus(array $filters)
    {
        return $this->withdrawalsQuery($filters)
            ->selectRaw('withdrawal_requests.status as label, COUNT(*) as count, COALESCE(SUM(withdrawal_requests.amount_requested), 0) as amount')
            ->groupBy('withdrawal_requests.status')
            ->orderBy('withdrawal_requests.status')
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

    private function writeWalletBalances($handle, $rows)
    {
        fputcsv($handle, []);
        fputcsv($handle, ['Soldes clients']);
        fputcsv($handle, [
            'Client',
            'Quota restant',
            'Quota charge',
            'Quota consomme',
            'Ventes totales',
            'Solde brut client',
            'Retraits en attente',
            'Retraits traites',
            'Solde du',
            'Disponible retrait',
        ]);

        foreach ($rows as $wallet) {
            fputcsv($handle, [
                optional($wallet->user)->business_name ?: optional($wallet->user)->name ?: 'Client',
                $wallet->quota_balance,
                $wallet->total_quota_loaded,
                $wallet->total_quota_used,
                $wallet->total_sales_amount,
                $wallet->gross_client_balance,
                $wallet->pending_withdrawal_amount,
                $wallet->total_withdrawn,
                $wallet->balance_due,
                $wallet->available_withdrawal_balance,
            ]);
        }
    }
}
