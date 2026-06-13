<?php

namespace App\Http\Controllers;

use App\Services\ActivityLogger;
use App\Services\WithdrawalManager;
use Illuminate\Http\Request;
use App\Models\WithdrawalRequest;
use App\Models\User;

class AdminWithdrawalController extends Controller
{
    public function index(Request $request)
    {
        $withdrawals = WithdrawalRequest::with(['user', 'approver', 'processor', 'rejecter'])
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->when($request->filled('client_id'), function ($query) use ($request) {
                $query->where('user_id', $request->client_id);
            })
            ->when($request->filled('date_from'), function ($query) use ($request) {
                $query->whereDate('requested_at', '>=', $request->date_from);
            })
            ->when($request->filled('date_to'), function ($query) use ($request) {
                $query->whereDate('requested_at', '<=', $request->date_to);
            })
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = '%' . $request->search . '%';

                $query->where(function ($searchQuery) use ($search) {
                    $searchQuery
                        ->where('reference', 'like', $search)
                        ->orWhere('account_phone', 'like', $search)
                        ->orWhereHas('user', function ($userQuery) use ($search) {
                            $userQuery
                                ->where('name', 'like', $search)
                                ->orWhere('email', 'like', $search)
                                ->orWhere('business_name', 'like', $search);
                        });
                });
            })
            ->latest()
            ->paginate(20)
            ->appends($request->query());

        $clients = User::where('role', User::ROLE_CLIENT)->orderBy('name')->get();

        return view('admin.withdrawals.index', compact('withdrawals', 'clients'));
    }

    public function approve(Request $request, WithdrawalRequest $withdrawal)
    {
        $request->validate([
            'admin_note' => 'nullable|string|max:1000',
        ]);

        if (! $withdrawal->isRequested()) {
            return back()->with('error', 'Ce retrait ne peut pas etre valide.');
        }

        WithdrawalManager::approve($withdrawal, $request->user(), $request->admin_note);

        ActivityLogger::log('withdrawal.approved', $withdrawal, [
            'reference' => $withdrawal->reference,
            'amount_requested' => $withdrawal->amount_requested,
        ], $request);

        return back()->with('success', 'Retrait valide. Il peut maintenant etre traite.');
    }

    public function process(Request $request, WithdrawalRequest $withdrawal)
    {
        $request->validate([
            'admin_note' => 'nullable|string|max:1000',
        ]);

        if (! $withdrawal->isApproved()) {
            return back()->with('error', 'Ce retrait doit etre valide avant traitement.');
        }

        WithdrawalManager::process($withdrawal, $request->user(), $request->admin_note);

        ActivityLogger::log('withdrawal.processed', $withdrawal, [
            'reference' => $withdrawal->reference,
            'amount_requested' => $withdrawal->amount_requested,
            'amount_to_pay' => $withdrawal->amount_to_pay,
        ], $request);

        return back()->with('success', 'Retrait marque comme traite.');
    }

    public function reject(Request $request, WithdrawalRequest $withdrawal)
    {
        $data = $request->validate([
            'admin_note' => 'required|string|max:1000',
        ]);

        if (! $withdrawal->isReserved()) {
            return back()->with('error', 'Ce retrait ne peut plus etre refuse.');
        }

        WithdrawalManager::reject($withdrawal, $request->user(), $data['admin_note']);

        ActivityLogger::log('withdrawal.rejected', $withdrawal, [
            'reference' => $withdrawal->reference,
            'amount_requested' => $withdrawal->amount_requested,
        ], $request);

        return back()->with('success', 'Retrait refuse.');
    }
}
