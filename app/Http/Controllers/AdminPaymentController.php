<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class AdminPaymentController extends Controller
{
    public function index(Request $request)
    {
        $payments = $this->filteredPayments($request)
            ->latest()
            ->paginate(20)
            ->appends($request->query());

        $providers = $this->providers();
        $statuses = $this->statuses();

        return view('admin.payments.index', compact('payments', 'providers', 'statuses'));
    }

    public function show(Payment $payment)
    {
        $payment->load(['order.plan.router.user', 'order.ticket', 'refunds.requester', 'refunds.processor']);

        return view('admin.payments.show', [
            'payment' => $payment,
            'isOrphan' => $this->isOrphan($payment),
        ]);
    }

    public function verify(Payment $payment)
    {
        $payment->load('order');

        if ($payment->provider === 'test') {
            $payment->update([
                'raw_response' => array_merge($payment->raw_response ?: [], [
                    'last_manual_check' => now()->toDateTimeString(),
                    'verification_mode' => 'simulation',
                    'message' => 'Verification simulee: aucun appel provider externe.',
                ]),
            ]);

            ActivityLogger::log('payment.verification_requested', $payment, [
                'provider' => $payment->provider,
                'mode' => 'simulation',
                'status' => $payment->status,
            ]);

            return back()->with('success', 'Verification simulee effectuee.');
        }

        if ($payment->provider === 'campay') {
            $payment->update([
                'raw_response' => array_merge($payment->raw_response ?: [], [
                    'last_manual_check' => now()->toDateTimeString(),
                    'verification_mode' => 'pending_provider_configuration',
                    'message' => 'Campay pas encore configure avec les documents officiels.',
                ]),
            ]);

            ActivityLogger::log('payment.verification_requested', $payment, [
                'provider' => $payment->provider,
                'mode' => 'pending_provider_configuration',
                'status' => $payment->status,
            ]);

            return back()->with('error', "Campay n'est pas encore configure. Verification reelle indisponible.");
        }

        return back()->with('error', 'Provider non pris en charge pour la verification automatique.');
    }

    private function filteredPayments(Request $request)
    {
        return Payment::with(['order.plan.router.user'])
            ->when($request->filled('provider'), function ($query) use ($request) {
                $query->where('provider', $request->provider);
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->when($request->filled('phone'), function ($query) use ($request) {
                $query->where('phone', 'like', '%' . $request->phone . '%');
            })
            ->when($request->filled('reference'), function ($query) use ($request) {
                $query->where(function ($referenceQuery) use ($request) {
                    $referenceQuery
                        ->where('campay_reference', 'like', '%' . $request->reference . '%')
                        ->orWhere('operator_reference', 'like', '%' . $request->reference . '%')
                        ->orWhereHas('order', function ($orderQuery) use ($request) {
                            $orderQuery->where('reference', 'like', '%' . $request->reference . '%');
                        });
                });
            })
            ->when($request->filled('date_from'), function ($query) use ($request) {
                $query->whereDate('created_at', '>=', $request->date_from);
            })
            ->when($request->filled('date_to'), function ($query) use ($request) {
                $query->whereDate('created_at', '<=', $request->date_to);
            })
            ->when($request->boolean('orphans'), function ($query) {
                $query->doesntHave('order');
            });
    }

    private function isOrphan(Payment $payment)
    {
        return ! $payment->order;
    }

    private function providers()
    {
        return [
            'test' => 'Simulation',
            'campay' => 'Campay',
            'mobile_money' => 'Mobile Money',
            'orange_money' => 'Orange Money',
            'mtn_mobile_money' => 'MTN Mobile Money',
        ];
    }

    private function statuses()
    {
        return [
            'pending' => 'En attente',
            'successful' => 'Reussi',
            'failed' => 'Echoue',
            'cancelled' => 'Annule',
            'refunded' => 'Rembourse',
        ];
    }
}
