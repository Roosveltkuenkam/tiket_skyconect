<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Refund;
use App\Models\AdminNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Services\ActivityLogger;
use Throwable;

class AdminRefundController extends Controller
{
    public function index(Request $request)
    {
        $refunds = $this->filteredRefunds($request)
            ->latest()
            ->paginate(20)
            ->appends($request->query());

        $statuses = $this->statuses();

        return view('admin.refunds.index', compact('refunds', 'statuses'));
    }

    public function export(Request $request)
    {
        $fileName = 'skyconnect-remboursements-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($request) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'ID',
                'Statut',
                'Commande',
                'Paiement',
                'Client',
                'Montant',
                'Motif',
                'Note admin',
                'Demande par',
                'Valide par',
                'Traite par',
                'Demande le',
                'Valide le',
                'Traite le',
            ]);

            $this->filteredRefunds($request)
                ->orderBy('id')
                ->chunk(500, function ($refunds) use ($handle) {
                    foreach ($refunds as $refund) {
                        fputcsv($handle, [
                            $refund->id,
                            $refund->status,
                            optional($refund->order)->reference,
                            $refund->payment_id,
                            optional($refund->client)->name,
                            $refund->amount,
                            $refund->reason,
                            $refund->admin_note,
                            optional($refund->requester)->name,
                            optional($refund->approver)->name,
                            optional($refund->processor)->name,
                            $refund->requested_at,
                            $refund->approved_at,
                            $refund->processed_at,
                        ]);
                    }
                });

            fclose($handle);
        }, $fileName, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function store(Request $request, Payment $payment)
    {
        $request->validate([
            'reason' => 'required|string|min:5|max:2000',
            'amount' => 'nullable|integer|min:1',
            'admin_note' => 'nullable|string|max:2000',
        ]);

        $payment->load('order.plan.router.user');

        if (! in_array($payment->status, ['successful', 'refunded'], true)) {
            return back()->with('error', 'Une demande de remboursement concerne uniquement un paiement reussi ou deja rembourse.');
        }

        if (($request->amount ?: $payment->amount) > $payment->amount) {
            return back()->with('error', 'Le montant du remboursement ne peut pas depasser le montant du paiement.');
        }

        $existing = Refund::where('payment_id', $payment->id)
            ->whereIn('status', [Refund::STATUS_REQUESTED, Refund::STATUS_APPROVED])
            ->exists();

        if ($existing) {
            return back()->with('error', 'Une demande de remboursement est deja ouverte pour ce paiement.');
        }

        $refund = Refund::create([
            'order_id' => optional($payment->order)->id,
            'payment_id' => $payment->id,
            'client_id' => optional(optional(optional($payment->order)->plan)->router)->user_id,
            'amount' => $request->amount ?: $payment->amount,
            'status' => Refund::STATUS_REQUESTED,
            'reason' => $request->reason,
            'admin_note' => $request->admin_note,
            'requested_by' => $request->user()->id,
            'requested_at' => now(),
        ]);

        ActivityLogger::log('refund.requested', $refund, [
            'payment_id' => $payment->id,
            'order_id' => optional($payment->order)->id,
            'amount' => $refund->amount,
            'client_id' => $refund->client_id,
        ], $request);

        return redirect()->route('admin.refunds.show', $refund)
            ->with('success', 'Demande de remboursement creee.');
    }

    public function show(Refund $refund)
    {
        $refund->load([
            'order.plan.router.user',
            'payment',
            'client',
            'requester',
            'approver',
            'processor',
        ]);

        return view('admin.refunds.show', [
            'refund' => $refund,
            'statuses' => $this->statuses(),
        ]);
    }

    public function approve(Request $request, Refund $refund)
    {
        $request->validate([
            'admin_note' => 'nullable|string|max:2000',
        ]);

        if ($refund->status !== Refund::STATUS_REQUESTED) {
            return back()->with('error', 'Seules les demandes en attente peuvent etre validees.');
        }

        $refund->update([
            'status' => Refund::STATUS_APPROVED,
            'admin_note' => $request->admin_note ?: $refund->admin_note,
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
        ]);

        ActivityLogger::log('refund.approved', $refund, [
            'amount' => $refund->amount,
            'payment_id' => $refund->payment_id,
        ], $request);

        return back()->with('success', 'Demande de remboursement validee.');
    }

    public function reject(Request $request, Refund $refund)
    {
        $request->validate([
            'admin_note' => 'required|string|min:5|max:2000',
        ]);

        if (! in_array($refund->status, [Refund::STATUS_REQUESTED, Refund::STATUS_APPROVED], true)) {
            return back()->with('error', 'Cette demande ne peut plus etre refusee.');
        }

        $refund->update([
            'status' => Refund::STATUS_REJECTED,
            'admin_note' => $request->admin_note,
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
        ]);

        ActivityLogger::log('refund.rejected', $refund, [
            'amount' => $refund->amount,
            'payment_id' => $refund->payment_id,
        ], $request);

        return back()->with('success', 'Demande de remboursement refusee.');
    }

    public function process(Request $request, Refund $refund)
    {
        if ($refund->status !== Refund::STATUS_APPROVED) {
            return back()->with('error', 'Le remboursement doit etre valide avant traitement.');
        }

        DB::transaction(function () use ($refund, $request) {
            $refund->load(['payment.order.plan.router.user']);

            $refund->payment->update([
                'status' => 'refunded',
                'raw_response' => array_merge($refund->payment->raw_response ?: [], [
                    'refund_id' => $refund->id,
                    'refund_processed_by' => $request->user()->id,
                    'refund_processed_at' => now()->toDateTimeString(),
                ]),
            ]);

            if ($refund->order && $refund->order->status === 'paid') {
                $refund->order->update(['status' => 'refunded']);
            }

            $refund->update([
                'status' => Refund::STATUS_PROCESSED,
                'processed_by' => $request->user()->id,
                'processed_at' => now(),
            ]);
        });

        $refund->refresh()->load(['client', 'order', 'payment']);
        $this->notifyClient($refund);

        AdminNotification::notify(
            'refund_processed',
            'Remboursement effectue',
            'Un remboursement de ' . $refund->amount . ' XAF a ete traite.',
            'success',
            ['refund_id' => $refund->id, 'payment_id' => $refund->payment_id]
        );

        ActivityLogger::log('refund.processed', $refund, [
            'amount' => $refund->amount,
            'payment_id' => $refund->payment_id,
            'order_id' => optional($refund->order)->id,
            'notification_sent_at' => $refund->notification_sent_at,
        ], $request);

        return back()->with('success', 'Remboursement traite et paiement marque comme rembourse.');
    }

    private function filteredRefunds(Request $request)
    {
        return Refund::with(['order', 'payment', 'client', 'requester', 'approver', 'processor'])
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->when($request->filled('reference'), function ($query) use ($request) {
                $query->whereHas('order', function ($orderQuery) use ($request) {
                    $orderQuery->where('reference', 'like', '%' . $request->reference . '%');
                });
            })
            ->when($request->filled('client'), function ($query) use ($request) {
                $query->whereHas('client', function ($clientQuery) use ($request) {
                    $clientQuery
                        ->where('name', 'like', '%' . $request->client . '%')
                        ->orWhere('email', 'like', '%' . $request->client . '%')
                        ->orWhere('business_name', 'like', '%' . $request->client . '%');
                });
            })
            ->when($request->filled('date_from'), function ($query) use ($request) {
                $query->whereDate('created_at', '>=', $request->date_from);
            })
            ->when($request->filled('date_to'), function ($query) use ($request) {
                $query->whereDate('created_at', '<=', $request->date_to);
            });
    }

    private function notifyClient(Refund $refund)
    {
        if (! $refund->client || ! $refund->client->email) {
            return;
        }

        try {
            Mail::raw(
                "Bonjour {$refund->client->name},\n\nVotre remboursement SkyConnect de {$refund->amount} XAF a ete traite.\nCommande: " . (optional($refund->order)->reference ?: '-') . "\n\nSkyConnect",
                function ($message) use ($refund) {
                    $message->to($refund->client->email)
                        ->subject('Remboursement SkyConnect traite');
                }
            );

            $refund->update(['notification_sent_at' => now()]);
        } catch (Throwable $exception) {
            $refund->update([
                'admin_note' => trim(($refund->admin_note ?: '') . "\nNotification email non envoyee: " . $exception->getMessage()),
            ]);
        }
    }

    private function statuses()
    {
        return [
            Refund::STATUS_REQUESTED => 'Demandee',
            Refund::STATUS_APPROVED => 'Validee',
            Refund::STATUS_REJECTED => 'Refusee',
            Refund::STATUS_PROCESSED => 'Traitee',
        ];
    }
}
