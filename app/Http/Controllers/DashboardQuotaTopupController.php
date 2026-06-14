<?php

namespace App\Http\Controllers;

use App\Models\ClientQuotaTopup;
use App\Models\Plan;
use App\Models\QuotaTransaction;
use App\Services\QuotaManager;
use App\Services\NotificationManager;
use App\Services\SettingManager;
use App\Services\WithdrawalManager;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DashboardQuotaTopupController extends Controller
{
    public function index(Request $request)
    {
        $wallet = QuotaManager::walletFor($request->user());
        $topups = ClientQuotaTopup::where('user_id', $request->user()->id)
            ->latest()
            ->paginate(15);
        $commissions = QuotaTransaction::where('user_id', $request->user()->id)
            ->where('type', QuotaTransaction::TYPE_SALE_COMMISSION)
            ->with('order')
            ->latest()
            ->paginate(10, ['*'], 'commissions_page');
        $plans = Plan::whereHas('router', function ($query) use ($request) {
                $query->where('user_id', $request->user()->id);
            })
            ->where('is_active', true)
            ->orderBy('price')
            ->get();

        return view('dashboard.quota_topups.index', [
            'wallet' => $wallet,
            'topups' => $topups,
            'commissions' => $commissions,
            'plans' => $plans,
            'methods' => $this->methods(),
            'quotaRate' => QuotaManager::rate($request->user()),
            'minimumBalance' => QuotaManager::minimumBalance(),
            'availableWithdrawalBalance' => WithdrawalManager::availableBalance($wallet),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'amount' => 'required|integer|min:' . max(100, (int) SettingManager::get('quota.minimum_topup', 1000)),
            'method' => 'required|string|max:100',
            'phone' => 'nullable|string|max:50',
            'external_reference' => 'nullable|string|max:255',
            'client_note' => 'nullable|string|max:1000',
        ]);

        $topup = ClientQuotaTopup::create([
            'user_id' => $request->user()->id,
            'reference' => 'QT-' . strtoupper(Str::random(10)),
            'amount' => $data['amount'],
            'method' => $data['method'],
            'phone' => $data['phone'] ?? null,
            'external_reference' => $data['external_reference'] ?? null,
            'status' => ClientQuotaTopup::STATUS_PENDING,
            'client_note' => $data['client_note'] ?? null,
        ]);

        NotificationManager::admin(
            'quota_topup_received',
            'Recharge quota recue',
            $request->user()->name . ' a envoye une demande de recharge quota de ' . $topup->amount . ' XAF.',
            'info',
            ['user_id' => $request->user()->id, 'topup_id' => $topup->id]
        );

        NotificationManager::client(
            $request->user(),
            'Recharge quota recue',
            'Votre demande de recharge de ' . $topup->amount . ' XAF a ete recue.'
        );

        return back()->with('success', 'Demande de recharge envoyee. Elle sera creditee apres validation du paiement.');
    }

    private function methods()
    {
        $raw = (string) SettingManager::get('withdrawals.methods', "Orange Money\nMTN Mobile Money\nVirement bancaire\nCash");

        return collect(preg_split('/\r\n|\r|\n|,/', $raw))
            ->map(function ($method) {
                return trim($method);
            })
            ->filter()
            ->values();
    }
}
