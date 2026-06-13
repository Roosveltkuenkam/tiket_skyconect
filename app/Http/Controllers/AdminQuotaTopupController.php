<?php

namespace App\Http\Controllers;

use App\Models\ClientQuotaTopup;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\NotificationManager;
use App\Services\QuotaManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminQuotaTopupController extends Controller
{
    public function index(Request $request)
    {
        $topups = ClientQuotaTopup::with(['user', 'confirmer', 'rejecter'])
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->when($request->filled('client_id'), function ($query) use ($request) {
                $query->where('user_id', $request->client_id);
            })
            ->when($request->filled('date_from'), function ($query) use ($request) {
                $query->whereDate('created_at', '>=', $request->date_from);
            })
            ->when($request->filled('date_to'), function ($query) use ($request) {
                $query->whereDate('created_at', '<=', $request->date_to);
            })
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = '%' . $request->search . '%';

                $query->where(function ($searchQuery) use ($search) {
                    $searchQuery
                        ->where('reference', 'like', $search)
                        ->orWhere('external_reference', 'like', $search)
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

        return view('admin.quota_topups.index', compact('topups', 'clients'));
    }

    public function approve(Request $request, ClientQuotaTopup $topup)
    {
        $request->validate([
            'admin_note' => 'nullable|string|max:1000',
        ]);

        if (! $topup->isPending()) {
            return back()->with('error', 'Cette recharge a deja ete traitee.');
        }

        if ($request->filled('admin_note')) {
            $topup->update(['admin_note' => $request->admin_note]);
        }

        $wallet = QuotaManager::confirmTopup($topup, $request->user());

        ActivityLogger::log('quota.topup_confirmed', $topup, [
            'topup_reference' => $topup->reference,
            'amount' => $topup->amount,
            'new_balance' => $wallet->quota_balance,
        ], $request);

        return back()->with('success', 'Recharge quota validee et solde credite.');
    }

    public function reject(Request $request, ClientQuotaTopup $topup)
    {
        $data = $request->validate([
            'admin_note' => 'required|string|max:1000',
        ]);

        if (! $topup->isPending()) {
            return back()->with('error', 'Cette recharge a deja ete traitee.');
        }

        $rejected = false;

        $topup = DB::transaction(function () use ($topup, $request, $data, &$rejected) {
            $topup = ClientQuotaTopup::whereKey($topup->id)->lockForUpdate()->firstOrFail();

            if (! $topup->isPending()) {
                return $topup;
            }

            $topup->update([
                'status' => ClientQuotaTopup::STATUS_REJECTED,
                'admin_note' => $data['admin_note'],
                'rejected_by' => $request->user()->id,
                'rejected_at' => now(),
            ]);

            $rejected = true;

            return $topup->fresh();
        });

        if (! $rejected) {
            return back()->with('error', 'Cette recharge a deja ete traitee.');
        }

        NotificationManager::client(
            $topup->user,
            'Recharge quota refusee',
            'Votre recharge quota ' . $topup->reference . ' a ete refusee. Motif: ' . $data['admin_note']
        );

        ActivityLogger::log('quota.topup_rejected', $topup, [
            'topup_reference' => $topup->reference,
            'amount' => $topup->amount,
        ], $request);

        return back()->with('success', 'Recharge quota refusee.');
    }
}
