<?php

namespace App\Http\Controllers;

use App\Services\QuotaManager;
use App\Services\WithdrawalManager;
use Illuminate\Http\Request;

class DashboardWithdrawalController extends Controller
{
    public function index(Request $request)
    {
        $wallet = QuotaManager::walletFor($request->user());
        $withdrawals = $request->user()->withdrawalRequests()
            ->latest()
            ->paginate(15);

        return view('dashboard.withdrawals.index', [
            'wallet' => $wallet,
            'withdrawals' => $withdrawals,
            'methods' => WithdrawalManager::methods(),
            'enabled' => WithdrawalManager::enabled(),
            'availableBalance' => WithdrawalManager::availableBalance($wallet),
            'minimumAmount' => WithdrawalManager::minimumAmount(),
            'maximumAmount' => WithdrawalManager::maximumAmount(),
            'feeType' => WithdrawalManager::feeType(),
            'feeValue' => WithdrawalManager::feeFor(10000),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'amount' => 'required|integer|min:1',
            'method' => 'required|string|max:100',
            'account_name' => 'nullable|string|max:255',
            'account_phone' => 'required|string|max:100',
            'client_note' => 'nullable|string|max:1000',
        ]);

        WithdrawalManager::request($request->user(), $data);

        return back()->with('success', 'Demande de retrait envoyee.');
    }
}
