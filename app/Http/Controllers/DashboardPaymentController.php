<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;

class DashboardPaymentController extends Controller
{
    public function index(Request $request)
    {
        $payments = Payment::with(['order.plan.router'])
            ->when(! $request->user()->isSuperAdmin(), function ($query) use ($request) {
                $query->whereHas('order.plan.router', function ($routerQuery) use ($request) {
                    $routerQuery->where('user_id', $request->user()->id);
                });
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->when($request->filled('provider'), function ($query) use ($request) {
                $query->where('provider', $request->provider);
            })
            ->when($request->filled('phone'), function ($query) use ($request) {
                $query->where('phone', 'like', '%' . $request->phone . '%');
            })
            ->latest()
            ->paginate(20)
            ->appends($request->query());

        return view('dashboard.payments.index', compact('payments'));
    }
}
