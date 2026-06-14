@extends('layouts.admin')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <span class="eyebrow"><span class="eyebrow-dot"></span> {{ __('ui.quota.report_eyebrow') }}</span>
        <h3>{{ __('ui.quota.report_title') }}</h3>
        <p>{{ __('ui.quota.report_subtitle') }}</p>
    </div>
</div>

<div class="panel-card mb-4">
    <form method="GET" action="{{ route('admin.reports.quota') }}" class="row g-3">
        <div class="col-md-3">
            <label class="form-label">{{ __('ui.quota.client') }}</label>
            <select name="client_id" class="form-control">
                <option value="">{{ __('ui.quota.all') }}</option>
                @foreach($clients as $client)
                    <option value="{{ $client->id }}" {{ (string) $filters['client_id'] === (string) $client->id ? 'selected' : '' }}>{{ $client->business_name ?: $client->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">{{ __('ui.quota.status') }}</label>
            <select name="status" class="form-control">
                <option value="">{{ __('ui.quota.all') }}</option>
                <option value="pending" {{ $filters['status'] === 'pending' ? 'selected' : '' }}>{{ __('ui.quota.topup_pending') }}</option>
                <option value="confirmed" {{ $filters['status'] === 'confirmed' ? 'selected' : '' }}>{{ __('ui.quota.topup_confirmed') }}</option>
                <option value="requested" {{ $filters['status'] === 'requested' ? 'selected' : '' }}>{{ __('ui.quota.withdrawal_requested') }}</option>
                <option value="approved" {{ $filters['status'] === 'approved' ? 'selected' : '' }}>{{ __('ui.quota.withdrawal_approved') }}</option>
                <option value="processed" {{ $filters['status'] === 'processed' ? 'selected' : '' }}>{{ __('ui.quota.withdrawal_processed') }}</option>
                <option value="rejected" {{ $filters['status'] === 'rejected' ? 'selected' : '' }}>{{ __('ui.quota.rejected') }}</option>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">{{ __('ui.quota.from') }}</label>
            <input type="date" name="date_from" class="form-control" value="{{ $filters['date_from'] }}">
        </div>
        <div class="col-md-2">
            <label class="form-label">{{ __('ui.quota.to') }}</label>
            <input type="date" name="date_to" class="form-control" value="{{ $filters['date_to'] }}">
        </div>
        <div class="col-md-3 d-flex align-items-end">
            <button class="sky-btn w-100">{{ __('ui.quota.filter') }}</button>
        </div>
    </form>
</div>

<div class="row g-3 mb-4">
    @php
        $kpiLabels = [
            'quota_loaded' => __('ui.quota.quota_loaded'),
            'commissions' => __('ui.quota.commissions_consumed'),
            'quota_credit_remaining' => __('ui.quota.quota_credit_remaining'),
            'quota_sales_capacity' => __('ui.quota.quota_sales_capacity'),
            'adjustments' => __('ui.quota.quota_adjustments'),
            'topups_pending' => __('ui.quota.topups_pending'),
            'withdrawals_requested' => __('ui.quota.withdrawals_requested'),
            'withdrawals_processed' => __('ui.quota.withdrawals_processed'),
            'withdrawal_fees' => __('ui.quota.withdrawal_fees'),
            'amount_paid_to_clients' => __('ui.quota.amount_paid_to_clients'),
        ];
    @endphp
    @foreach($kpis as $label => $value)
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-title">{{ $kpiLabels[$label] ?? str_replace('_', ' ', $label) }}</div>
                <div class="stat-value">{{ number_format((int) $value, 0, ',', ' ') }} XAF</div>
            </div>
        </div>
    @endforeach
</div>

<div class="panel-card mb-4">
    <h4>{{ __('ui.quota.balances_by_client') }}</h4>
    <div class="table-responsive mt-3">
        <table class="table">
            <thead>
                <tr>
                    <th>{{ __('ui.quota.client') }}</th>
                    <th>{{ __('ui.quota.sales') }}</th>
                    <th>{{ __('ui.quota.quota_commission') }}</th>
                    <th>{{ __('ui.quota.gross_balance') }}</th>
                    <th>{{ __('ui.quota.pending_withdrawals') }}</th>
                    <th>{{ __('ui.quota.withdrawals_processed') }}</th>
                    <th>{{ __('ui.quota.available') }}</th>
                    <th>{{ __('ui.quota.quota_sales_capacity') }}</th>
                    <th>{{ __('ui.quota.quota_credit') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($walletRows as $wallet)
                    @php
                        $walletQuotaRate = \App\Services\QuotaManager::rate($wallet->user);
                        $quotaSalesCapacity = $walletQuotaRate > 0
                            ? (int) floor(((int) $wallet->quota_balance) * 100 / $walletQuotaRate)
                            : (int) $wallet->quota_balance;
                    @endphp
                    <tr>
                        <td>{{ optional($wallet->user)->business_name ?: optional($wallet->user)->name }}</td>
                        <td>{{ number_format((int) $wallet->total_sales_amount, 0, ',', ' ') }} XAF</td>
                        <td>{{ number_format((int) $wallet->total_quota_used, 0, ',', ' ') }} XAF</td>
                        <td>{{ number_format((int) $wallet->grossClientBalance(), 0, ',', ' ') }} XAF</td>
                        <td>{{ number_format((int) $wallet->pending_withdrawal_amount, 0, ',', ' ') }} XAF</td>
                        <td>{{ number_format((int) $wallet->total_withdrawn, 0, ',', ' ') }} XAF</td>
                        <td>{{ number_format((int) $wallet->availableWithdrawalBalance(), 0, ',', ' ') }} XAF</td>
                        <td>
                            <strong>{{ number_format($quotaSalesCapacity, 0, ',', ' ') }} XAF</strong><br>
                            <small>{{ number_format((float) $walletQuotaRate, 2, ',', ' ') }}%</small>
                        </td>
                        <td>{{ number_format((int) $wallet->quota_balance, 0, ',', ' ') }} XAF</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">{{ __('ui.quota.no_wallet') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $walletRows->links() }}
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="panel-card">
            <h4>{{ __('ui.quota.latest_quota_movements') }}</h4>
            <div class="table-responsive mt-3">
                <table class="table">
                    <thead>
                        <tr>
                            <th>{{ __('ui.quota.date') }}</th>
                            <th>{{ __('ui.quota.client') }}</th>
                            <th>{{ __('ui.quota.type') }}</th>
                            <th>{{ __('ui.quota.amount') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentTransactions as $transaction)
                            <tr>
                                <td>{{ $transaction->created_at }}</td>
                                <td>{{ optional($transaction->user)->business_name ?: optional($transaction->user)->name }}</td>
                                <td>{{ $transaction->type }}</td>
                                <td>{{ number_format((int) $transaction->amount, 0, ',', ' ') }} XAF</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">{{ __('ui.quota.no_movement') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="panel-card">
            <h4>{{ __('ui.quota.latest_withdrawals') }}</h4>
            <div class="table-responsive mt-3">
                <table class="table">
                    <thead>
                        <tr>
                            <th>{{ __('ui.quota.date') }}</th>
                            <th>{{ __('ui.quota.client') }}</th>
                            <th>{{ __('ui.quota.gross') }}</th>
                            <th>{{ __('ui.quota.net') }}</th>
                            <th>{{ __('ui.quota.status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentWithdrawals as $withdrawal)
                            <tr>
                                <td>{{ $withdrawal->requested_at }}</td>
                                <td>{{ optional($withdrawal->user)->business_name ?: optional($withdrawal->user)->name }}</td>
                                <td>{{ number_format((int) $withdrawal->amount_requested, 0, ',', ' ') }} XAF</td>
                                <td>{{ number_format((int) $withdrawal->amount_to_pay, 0, ',', ' ') }} XAF</td>
                                <td>{{ $withdrawal->status }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">{{ __('ui.quota.no_withdrawal') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
