<?php

namespace App\Services;

use App\Models\ClientQuotaTopup;
use App\Models\ClientWallet;
use App\Models\Order;
use App\Models\Payment;
use App\Models\QuotaTransaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class QuotaManager
{
    public static function enabled()
    {
        return (bool) (int) SettingManager::get('quota.enabled', 0);
    }

    public static function rate()
    {
        return max(0, (float) SettingManager::get('quota.rate', 10));
    }

    public static function minimumBalance()
    {
        return max(0, (int) SettingManager::get('quota.minimum_balance', 0));
    }

    public static function commissionFor($amount)
    {
        if (! self::enabled()) {
            return 0;
        }

        return (int) ceil(((int) $amount) * self::rate() / 100);
    }

    public static function consumeForOrder(Order $order, User $owner, ?Payment $payment = null)
    {
        if (! self::enabled() || ! $owner->isClientOwner()) {
            return 0;
        }

        $commission = self::quotaRequiredForOrder($order);

        if ($commission <= 0) {
            return 0;
        }

        $wallet = self::lockedWalletFor($owner);
        $minimumBalance = self::minimumBalance();
        $availableBalance = max(0, (int) $wallet->quota_balance - $minimumBalance);

        if ($availableBalance < $commission) {
            NotificationManager::admin(
                'quota_insufficient',
                'Vente bloquee par quota',
                'Une vente a ete bloquee pour ' . $owner->name . ' car son solde quota est insuffisant.',
                'warning',
                [
                    'owner_id' => $owner->id,
                    'order_id' => $order->id,
                    'quota_balance' => (int) $wallet->quota_balance,
                    'minimum_balance' => $minimumBalance,
                    'required_quota' => $commission,
                ]
            );

            NotificationManager::client(
                $owner,
                'Vente bloquee',
                'Votre solde quota est insuffisant pour confirmer une vente de ' . $order->amount . ' XAF.'
            );

            abort(403, 'Solde quota insuffisant pour confirmer cette vente.');
        }

        self::recordMovement($wallet, [
            'type' => QuotaTransaction::TYPE_SALE_COMMISSION,
            'amount' => $commission,
            'balance_after' => $wallet->quota_balance - $commission,
            'order_id' => $order->id,
            'payment_id' => optional($payment)->id,
            'note' => 'Commission SkyConnect sur vente',
            'metadata' => [
                'order_reference' => $order->reference,
                'order_amount' => (int) $order->amount,
                'quota_rate' => self::rate(),
            ],
        ]);

        $wallet->increment('total_quota_used', $commission);
        $wallet->increment('total_sales_amount', (int) $order->amount);
        self::notifyLowQuota($wallet->fresh(), $owner);

        return $commission;
    }

    public static function quotaRequiredForOrder(Order $order)
    {
        return self::commissionFor($order->amount);
    }

    public static function walletFor(User $owner)
    {
        return ClientWallet::firstOrCreate(
            ['user_id' => $owner->id],
            [
                'quota_balance' => (int) ($owner->quota_balance ?? 0),
                'total_quota_loaded' => max(0, (int) ($owner->quota_balance ?? 0)),
                'total_quota_used' => 0,
                'total_sales_amount' => 0,
                'total_withdrawn' => 0,
                'pending_withdrawal_amount' => 0,
            ]
        );
    }

    public static function adjustWallet(User $owner, string $operation, int $amount, ?User $performedBy = null, ?string $note = null)
    {
        return DB::transaction(function () use ($owner, $operation, $amount, $performedBy, $note) {
            $wallet = self::lockedWalletFor($owner);
            $balanceBefore = (int) $wallet->quota_balance;

            if ($operation === 'set') {
                $balanceAfter = $amount;
                $type = QuotaTransaction::TYPE_ADJUSTMENT;
            } elseif ($operation === 'credit') {
                $balanceAfter = $balanceBefore + $amount;
                $type = QuotaTransaction::TYPE_TOPUP;
            } else {
                $balanceAfter = max(0, $balanceBefore - $amount);
                $type = QuotaTransaction::TYPE_ADJUSTMENT;
            }

            self::recordMovement($wallet, [
                'type' => $type,
                'amount' => $amount,
                'balance_after' => $balanceAfter,
                'performed_by' => optional($performedBy)->id,
                'note' => $note,
            ]);

            if ($operation === 'credit') {
                $wallet->increment('total_quota_loaded', $amount);
            }

            return $wallet->fresh();
        });
    }

    public static function confirmTopup(ClientQuotaTopup $topup, User $confirmedBy)
    {
        return DB::transaction(function () use ($topup, $confirmedBy) {
            $topup = ClientQuotaTopup::whereKey($topup->id)->lockForUpdate()->firstOrFail();

            if (! $topup->isPending()) {
                return self::walletFor($topup->user);
            }

            $wallet = self::lockedWalletFor($topup->user);

            self::recordMovement($wallet, [
                'type' => QuotaTransaction::TYPE_TOPUP,
                'amount' => (int) $topup->amount,
                'balance_after' => $wallet->quota_balance + (int) $topup->amount,
                'created_by' => $confirmedBy->id,
                'note' => 'Recharge quota confirmee',
                'metadata' => [
                    'topup_id' => $topup->id,
                    'topup_reference' => $topup->reference,
                    'method' => $topup->method,
                    'external_reference' => $topup->external_reference,
                ],
            ]);

            $wallet->increment('total_quota_loaded', (int) $topup->amount);

            $topup->update([
                'status' => ClientQuotaTopup::STATUS_CONFIRMED,
                'confirmed_by' => $confirmedBy->id,
                'confirmed_at' => now(),
            ]);

            $wallet = $wallet->fresh();

            NotificationManager::client(
                $topup->user,
                'Recharge quota validee',
                'Votre recharge de ' . $topup->amount . ' XAF a ete validee. Nouveau solde quota: ' . $wallet->quota_balance . ' XAF.'
            );

            return $wallet;
        });
    }

    private static function notifyLowQuota(ClientWallet $wallet, User $owner)
    {
        $threshold = max(0, (int) SettingManager::get('sales.low_stock_threshold', 10));

        if ($threshold <= 0 || $wallet->quota_balance > $threshold) {
            return;
        }

        NotificationManager::admin(
            'quota_low',
            'Quota client faible',
            'Le solde quota de ' . $owner->name . ' est faible: ' . $wallet->quota_balance . ' XAF.',
            'warning',
            ['owner_id' => $owner->id, 'quota_balance' => $wallet->quota_balance]
        );

        NotificationManager::client(
            $owner,
            'Quota faible',
            'Votre solde quota est faible: ' . $wallet->quota_balance . ' XAF. Pensez a recharger pour continuer vos ventes.'
        );
    }

    private static function lockedWalletFor(User $owner)
    {
        self::walletFor($owner);

        return ClientWallet::where('user_id', $owner->id)->lockForUpdate()->firstOrFail();
    }

    private static function recordMovement(ClientWallet $wallet, array $data)
    {
        $balanceBefore = (int) $wallet->quota_balance;
        $balanceAfter = max(0, (int) $data['balance_after']);

        $wallet->update(['quota_balance' => $balanceAfter]);
        $wallet->user()->update(['quota_balance' => $balanceAfter]);

        return QuotaTransaction::create([
            'user_id' => $wallet->user_id,
            'order_id' => $data['order_id'] ?? null,
            'payment_id' => $data['payment_id'] ?? null,
            'created_by' => $data['created_by'] ?? $data['performed_by'] ?? null,
            'type' => $data['type'],
            'amount' => (int) $data['amount'],
            'balance_before' => $balanceBefore,
            'balance_after' => $balanceAfter,
            'note' => $data['note'] ?? null,
            'metadata' => $data['metadata'] ?? null,
        ]);
    }
}
