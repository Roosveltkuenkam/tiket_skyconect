<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class DashboardOrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::with(['plan.router', 'ticket', 'payment'])
            ->when(! $request->user()->isSuperAdmin(), function ($query) use ($request) {
                $query->whereHas('plan.router', function ($routerQuery) use ($request) {
                    $routerQuery->where('user_id', $request->user()->id);
                });
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->when($request->filled('phone'), function ($query) use ($request) {
                $query->where('customer_phone', 'like', '%' . $request->phone . '%');
            })
            ->when($request->filled('reference'), function ($query) use ($request) {
                $query->where('reference', 'like', '%' . $request->reference . '%');
            })
            ->latest()
            ->paginate(20)
            ->appends($request->query());

        return view('dashboard.orders.index', compact('orders'));
    }
}
