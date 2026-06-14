<?php

namespace App\Http\Controllers;

use App\Models\ClientQuotaTopup;
use App\Models\ClientWallet;
use App\Models\QuotaTransaction;
use App\Models\User;
use App\Models\WithdrawalRequest;
use App\Services\QuotaManager;
use Illuminate\Http\Request;

class AdminQuotaReportController extends Controller
{
    public function index(Request $request)
    {
        $filters = $this->filters($request);
        $quotaTransactions = $this->quotaTransactionsQuery($filters);
        $topups = $this->topupsQuery($filters);
        $withdrawals = $this->withdrawalsQuery($filters);
        $walletsForCapacity = ClientWallet::with('user')
            ->when($filters['client_id'], function ($query) use ($filters) {
                $query->where('user_id', $filters['client_id']);
            })
            ->get();

        $kpis = [
            'quota_loaded' => (clone $quotaTransactions)->where('type', QuotaTransaction::TYPE_TOPUP)->sum('amount'),
            'commissions' => (clone $quotaTransactions)->where('type', QuotaTransaction::TYPE_SALE_COMMISSION)->sum('amount'),
            'quota_credit_remaining' => $walletsForCapacity->sum('quota_balance'),
            'adjustments' => (clone $quotaTransactions)->where('type', QuotaTransaction::TYPE_ADJUSTMENT)->sum('amount'),
            'topups_pending' => (clone $topups)->where('status', ClientQuotaTopup::STATUS_PENDING)->sum('amount'),
            'withdrawals_requested' => (clone $withdrawals)->whereIn('status', [WithdrawalRequest::STATUS_REQUESTED, WithdrawalRequest::STATUS_APPROVED])->sum('amount_requested'),
            'withdrawals_processed' => (clone $withdrawals)->where('status', WithdrawalRequest::STATUS_PROCESSED)->sum('amount_requested'),
            'withdrawal_fees' => (clone $withdrawals)->where('status', WithdrawalRequest::STATUS_PROCESSED)->sum('fee_amount'),
            'amount_paid_to_clients' => (clone $withdrawals)->where('status', WithdrawalRequest::STATUS_PROCESSED)->sum('amount_to_pay'),
        ];
        $kpis['quota_sales_capacity'] = $walletsForCapacity->sum(function ($wallet) {
            $walletQuotaRate = QuotaManager::rate($wallet->user);

            return $walletQuotaRate > 0
                ? (int) floor(((int) $wallet->quota_balance) * 100 / $walletQuotaRate)
                : (int) $wallet->quota_balance;
        });

        $walletRows = ClientWallet::with('user')
            ->when($filters['client_id'], function ($query) use ($filters) {
                $query->where('user_id', $filters['client_id']);
            })
            ->orderByDesc('total_sales_amount')
            ->paginate(20)
            ->appends($request->query());

        return view('admin.reports.quota', [
            'filters' => $filters,
            'clients' => User::where('role', User::ROLE_CLIENT)->orderBy('name')->get(),
            'kpis' => $kpis,
            'walletRows' => $walletRows,
            'recentTransactions' => (clone $quotaTransactions)->with(['user', 'order'])->latest()->limit(20)->get(),
            'recentWithdrawals' => (clone $withdrawals)->with('user')->latest()->limit(20)->get(),
        ]);
    }

    private function filters(Request $request)
    {
        return [
            'date_from' => $request->date_from ?: now()->startOfMonth()->toDateString(),
            'date_to' => $request->date_to ?: now()->toDateString(),
            'client_id' => $request->client_id,
            'status' => $request->status,
        ];
    }

    private function quotaTransactionsQuery(array $filters)
    {
        return QuotaTransaction::query()
            ->whereDate('created_at', '>=', $filters['date_from'])
            ->whereDate('created_at', '<=', $filters['date_to'])
            ->when($filters['client_id'], function ($query) use ($filters) {
                $query->where('user_id', $filters['client_id']);
            });
    }

    private function topupsQuery(array $filters)
    {
        return ClientQuotaTopup::query()
            ->whereDate('created_at', '>=', $filters['date_from'])
            ->whereDate('created_at', '<=', $filters['date_to'])
            ->when($filters['client_id'], function ($query) use ($filters) {
                $query->where('user_id', $filters['client_id']);
            })
            ->when($filters['status'], function ($query) use ($filters) {
                if (in_array($filters['status'], [ClientQuotaTopup::STATUS_PENDING, ClientQuotaTopup::STATUS_CONFIRMED, ClientQuotaTopup::STATUS_REJECTED], true)) {
                    $query->where('status', $filters['status']);
                }
            });
    }

    private function withdrawalsQuery(array $filters)
    {
        return WithdrawalRequest::query()
            ->whereDate('requested_at', '>=', $filters['date_from'])
            ->whereDate('requested_at', '<=', $filters['date_to'])
            ->when($filters['client_id'], function ($query) use ($filters) {
                $query->where('user_id', $filters['client_id']);
            })
            ->when($filters['status'], function ($query) use ($filters) {
                if (in_array($filters['status'], [
                    WithdrawalRequest::STATUS_REQUESTED,
                    WithdrawalRequest::STATUS_APPROVED,
                    WithdrawalRequest::STATUS_REJECTED,
                    WithdrawalRequest::STATUS_PROCESSED,
                    WithdrawalRequest::STATUS_CANCELLED,
                ], true)) {
                    $query->where('status', $filters['status']);
                }
            });
    }
}
