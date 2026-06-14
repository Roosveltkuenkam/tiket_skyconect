<?php

namespace App\Services;

use App\Models\ClientWallet;
use App\Models\User;
use App\Models\WithdrawalRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WithdrawalManager
{
    public static function enabled()
    {
        return (bool) (int) SettingManager::get('withdrawals.enabled', 0);
    }

    public static function availableBalance(ClientWallet $wallet)
    {
        return max(0, (int) $wallet->total_sales_amount
            - (int) $wallet->total_quota_used
            - (int) $wallet->total_withdrawn
            - (int) $wallet->pending_withdrawal_amount);
    }

    public static function feeFor(int $amount, ?User $client = null)
    {
        $type = self::feeType($client);
        $value = self::feeValue($client);

        if ($type === 'percent') {
            return (int) ceil($amount * $value / 100);
        }

        return (int) $value;
    }

    public static function feeType(?User $client = null)
    {
        if ($client && $client->isClientOwner()) {
            return 'percent';
        }

        return SettingManager::get('withdrawals.fee_type', 'fixed') === 'percent'
            ? 'percent'
            : 'fixed';
    }

    public static function feeValue(?User $client = null)
    {
        if ($client && $client->isClientOwner()) {
            $subscription = $client->activeSubscription();

            if ($subscription && $subscription->plan && $subscription->plan->withdrawal_fee_percent !== null) {
                return max(0, (float) $subscription->plan->withdrawal_fee_percent);
            }
        }

        return max(0, (float) SettingManager::get('withdrawals.fee_value', 0));
    }

    public static function minimumAmount()
    {
        return max(0, (int) SettingManager::get('withdrawals.minimum_amount', 0));
    }

    public static function maximumAmount()
    {
        $value = SettingManager::get('withdrawals.maximum_amount');

        return $value === null || $value === '' ? null : max(0, (int) $value);
    }

    public static function methods()
    {
        $raw = (string) SettingManager::get('withdrawals.methods', "Orange Money\nMTN Mobile Money\nVirement bancaire\nCash");

        return collect(preg_split('/\r\n|\r|\n|,/', $raw))
            ->map(function ($method) {
                return trim($method);
            })
            ->filter()
            ->values();
    }

    public static function request(User $client, array $data)
    {
        return DB::transaction(function () use ($client, $data) {
            if (! self::enabled()) {
                abort(403, 'Les retraits ne sont pas disponibles pour le moment.');
            }

            $wallet = self::lockedWalletFor($client);
            $amount = (int) $data['amount'];
            $minimum = self::minimumAmount();
            $maximum = self::maximumAmount();

            if ($minimum > 0 && $amount < $minimum) {
                abort(422, 'Le montant minimum de retrait est ' . $minimum . ' XAF.');
            }

            if ($maximum !== null && $maximum > 0 && $amount > $maximum) {
                abort(422, 'Le montant maximum de retrait est ' . $maximum . ' XAF.');
            }

            if ($amount > self::availableBalance($wallet)) {
                abort(422, 'Solde disponible insuffisant pour ce retrait.');
            }

            $feeAmount = min($amount, self::feeFor($amount, $client));
            $withdrawal = WithdrawalRequest::create([
                'user_id' => $client->id,
                'reference' => 'WD-' . strtoupper(Str::random(10)),
                'amount_requested' => $amount,
                'fee_amount' => $feeAmount,
                'amount_to_pay' => max(0, $amount - $feeAmount),
                'method' => $data['method'],
                'account_name' => $data['account_name'] ?? null,
                'account_phone' => $data['account_phone'] ?? $data['account_number'] ?? null,
                'status' => WithdrawalRequest::STATUS_REQUESTED,
                'requested_at' => now(),
                'client_note' => $data['client_note'] ?? null,
                'metadata' => [
                    'available_before' => self::availableBalance($wallet),
                    'fee_type' => self::feeType($client),
                    'fee_value' => self::feeValue($client),
                ],
            ]);

            $wallet->increment('pending_withdrawal_amount', $amount);

            NotificationManager::admin(
                'withdrawal_requested',
                'Nouvelle demande de retrait',
                $client->name . ' a demande un retrait de ' . $amount . ' XAF.',
                'info',
                ['user_id' => $client->id, 'withdrawal_id' => $withdrawal->id]
            );

            NotificationManager::client(
                $client,
                'Demande de retrait creee',
                'Votre demande de retrait de ' . $amount . ' XAF a ete recue.'
            );

            return $withdrawal;
        });
    }

    public static function approve(WithdrawalRequest $withdrawal, User $admin, ?string $note = null)
    {
        return DB::transaction(function () use ($withdrawal, $admin, $note) {
            $withdrawal = WithdrawalRequest::whereKey($withdrawal->id)->lockForUpdate()->firstOrFail();

            if (! $withdrawal->isRequested()) {
                return $withdrawal;
            }

            $withdrawal->update([
                'status' => WithdrawalRequest::STATUS_APPROVED,
                'admin_note' => $note ?: $withdrawal->admin_note,
                'approved_by' => $admin->id,
                'approved_at' => now(),
            ]);

            NotificationManager::client(
                $withdrawal->user,
                'Retrait valide',
                'Votre retrait ' . $withdrawal->reference . ' a ete valide. Il sera traite prochainement.'
            );

            return $withdrawal->fresh();
        });
    }

    public static function process(WithdrawalRequest $withdrawal, User $admin, ?string $note = null)
    {
        return DB::transaction(function () use ($withdrawal, $admin, $note) {
            $withdrawal = WithdrawalRequest::whereKey($withdrawal->id)->lockForUpdate()->firstOrFail();

            if (! $withdrawal->isApproved()) {
                return $withdrawal;
            }

            $wallet = self::lockedWalletFor($withdrawal->user);

            $wallet->update([
                'pending_withdrawal_amount' => max(0, $wallet->pending_withdrawal_amount - $withdrawal->amount_requested),
                'total_withdrawn' => $wallet->total_withdrawn + $withdrawal->amount_requested,
            ]);

            $withdrawal->update([
                'status' => WithdrawalRequest::STATUS_PROCESSED,
                'admin_note' => $note ?: $withdrawal->admin_note,
                'processed_by' => $admin->id,
                'processed_at' => now(),
            ]);

            NotificationManager::client(
                $withdrawal->user,
                'Retrait traite',
                'Votre retrait ' . $withdrawal->reference . ' a ete traite. Montant envoye: ' . $withdrawal->amount_to_pay . ' XAF.'
            );

            return $withdrawal->fresh();
        });
    }

    public static function reject(WithdrawalRequest $withdrawal, User $admin, string $note)
    {
        return DB::transaction(function () use ($withdrawal, $admin, $note) {
            $withdrawal = WithdrawalRequest::whereKey($withdrawal->id)->lockForUpdate()->firstOrFail();

            if (! $withdrawal->isReserved()) {
                return $withdrawal;
            }

            $wallet = self::lockedWalletFor($withdrawal->user);
            $wallet->update([
                'pending_withdrawal_amount' => max(0, $wallet->pending_withdrawal_amount - $withdrawal->amount_requested),
            ]);

            $withdrawal->update([
                'status' => WithdrawalRequest::STATUS_REJECTED,
                'admin_note' => $note,
                'rejected_by' => $admin->id,
                'rejected_at' => now(),
            ]);

            NotificationManager::client(
                $withdrawal->user,
                'Retrait refuse',
                'Votre retrait ' . $withdrawal->reference . ' a ete refuse. Motif: ' . $note
            );

            return $withdrawal->fresh();
        });
    }

    private static function lockedWalletFor(User $client)
    {
        QuotaManager::walletFor($client);

        return ClientWallet::where('user_id', $client->id)->lockForUpdate()->firstOrFail();
    }
}
