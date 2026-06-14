@extends('layouts.admin')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <span class="eyebrow"><span class="eyebrow-dot"></span> {{ __('ui.dashboard_withdrawals.eyebrow') }}</span>
        <h3>{{ __('ui.dashboard_withdrawals.title') }}</h3>
        <p>{{ __('ui.dashboard_withdrawals.subtitle') }}</p>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if($errors->any())
    <div class="alert alert-danger">
        @foreach($errors->all() as $error)
            <div>{{ $error }}</div>
        @endforeach
    </div>
@endif

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-title">{{ __('ui.dashboard_withdrawals.total_sales') }}</div>
            <div class="stat-value">{{ number_format((int) $wallet->total_sales_amount, 0, ',', ' ') }} XAF</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-title">{{ __('ui.dashboard_withdrawals.quota_commission') }}</div>
            <div class="stat-value">{{ number_format((int) $wallet->total_quota_used, 0, ',', ' ') }} XAF</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-title">{{ __('ui.dashboard_withdrawals.pending_withdrawals') }}</div>
            <div class="stat-value">{{ number_format((int) $wallet->pending_withdrawal_amount, 0, ',', ' ') }} XAF</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-title">{{ __('ui.dashboard_withdrawals.available') }}</div>
            <div class="stat-value">{{ number_format((int) $availableBalance, 0, ',', ' ') }} XAF</div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-5">
        <div class="panel-card">
            <h4>{{ __('ui.dashboard_withdrawals.new_withdrawal') }}</h4>
            @if(! $enabled)
                <div class="alert alert-warning mt-3">{{ __('ui.dashboard_withdrawals.disabled') }}</div>
            @endif
            <form method="POST" action="{{ route('dashboard.withdrawals.store') }}" class="mt-3">
                @csrf
                <label class="form-label">{{ __('ui.dashboard_withdrawals.gross_amount') }}</label>
                <input type="number" name="amount" class="form-control mb-2" min="1" max="{{ $availableBalance }}" required placeholder="Ex: 90000" {{ ! $enabled ? 'disabled' : '' }}>
                <small class="text-muted d-block mb-3">
                    {{ __('ui.dashboard_withdrawals.minimum') }}: {{ number_format((int) $minimumAmount, 0, ',', ' ') }} XAF
                    @if($maximumAmount)
                        · {{ __('ui.dashboard_withdrawals.maximum') }}: {{ number_format((int) $maximumAmount, 0, ',', ' ') }} XAF
                    @endif
                    · {{ __('ui.dashboard_withdrawals.withdrawal_fee') }}: {{ number_format((float) $feeValue, 2, ',', ' ') }}{{ $feeType === 'percent' ? '%' : ' XAF' }}
                </small>

                <label class="form-label">{{ __('ui.dashboard_withdrawals.method') }}</label>
                <select name="method" class="form-control mb-3" required {{ ! $enabled ? 'disabled' : '' }}>
                    @foreach($methods as $method)
                        <option value="{{ $method }}">{{ $method }}</option>
                    @endforeach
                </select>

                <label class="form-label">{{ __('ui.dashboard_withdrawals.beneficiary_name') }}</label>
                <input type="text" name="account_name" class="form-control mb-3" placeholder="{{ __('ui.dashboard_withdrawals.account_name_placeholder') }}">

                <label class="form-label">{{ __('ui.dashboard_withdrawals.receiver_account') }}</label>
                <input type="text" name="account_phone" class="form-control mb-3" required placeholder="{{ __('ui.dashboard_withdrawals.receiver_account_placeholder') }}" {{ ! $enabled ? 'disabled' : '' }}>

                <label class="form-label">{{ __('ui.dashboard_withdrawals.note') }}</label>
                <textarea name="client_note" class="form-control mb-3" rows="3" placeholder="{{ __('ui.dashboard_withdrawals.note_placeholder') }}"></textarea>

                <button class="sky-btn w-100" {{ ! $enabled ? 'disabled' : '' }}>{{ __('ui.dashboard_withdrawals.request_button') }}</button>
            </form>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="panel-card">
            <h4>{{ __('ui.dashboard_withdrawals.history') }}</h4>
            <div class="table-responsive mt-3">
                <table class="table">
                    <thead>
                        <tr>
                            <th>{{ __('ui.dashboard_withdrawals.date') }}</th>
                            <th>{{ __('ui.dashboard_withdrawals.reference') }}</th>
                            <th>{{ __('ui.dashboard_withdrawals.gross') }}</th>
                            <th>{{ __('ui.dashboard_withdrawals.fees') }}</th>
                            <th>{{ __('ui.dashboard_withdrawals.sent') }}</th>
                            <th>{{ __('ui.dashboard_withdrawals.status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($withdrawals as $withdrawal)
                            <tr>
                                <td>{{ $withdrawal->created_at }}</td>
                                <td>{{ $withdrawal->reference }}</td>
                                <td>{{ number_format((int) $withdrawal->amount_requested, 0, ',', ' ') }} XAF</td>
                                <td>{{ number_format((int) $withdrawal->fee_amount, 0, ',', ' ') }} XAF</td>
                                <td>{{ number_format((int) $withdrawal->amount_to_pay, 0, ',', ' ') }} XAF</td>
                                <td>
                                    @if($withdrawal->status === 'processed')
                                        <span class="badge text-bg-success">{{ __('ui.dashboard_withdrawals.statuses.processed') }}</span>
                                    @elseif($withdrawal->status === 'rejected')
                                        <span class="badge text-bg-danger">{{ __('ui.dashboard_withdrawals.statuses.rejected') }}</span>
                                    @elseif($withdrawal->status === 'approved')
                                        <span class="badge text-bg-primary">{{ __('ui.dashboard_withdrawals.statuses.approved') }}</span>
                                    @else
                                        <span class="badge text-bg-warning">{{ __('ui.dashboard_withdrawals.statuses.requested') }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">{{ __('ui.dashboard_withdrawals.no_withdrawal') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $withdrawals->links() }}
        </div>
    </div>
</div>
@endsection
