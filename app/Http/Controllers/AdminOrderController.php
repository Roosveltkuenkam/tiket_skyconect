<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Router;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class AdminOrderController extends Controller
{
    public function index(Request $request)
    {
        $clients = User::where('role', User::ROLE_CLIENT)->orderBy('name')->get();

        $routers = Router::with('user')
            ->when($request->filled('client_id'), function ($query) use ($request) {
                $query->where('user_id', $request->client_id);
            })
            ->orderBy('name')
            ->get();

        $orders = $this->filteredOrders($request)
            ->latest()
            ->paginate(20)
            ->appends($request->query());

        $statuses = $this->statuses();

        return view('admin.orders.index', compact('orders', 'clients', 'routers', 'statuses'));
    }

    public function show(Order $order)
    {
        $order->load(['plan.router.user', 'ticket', 'payment']);
        $statuses = $this->statuses();

        return view('admin.orders.show', compact('order', 'statuses'));
    }

    public function cancel(Request $request, Order $order)
    {
        if ($order->status !== 'pending') {
            return back()->with('error', 'Seules les commandes en attente peuvent etre annulees.');
        }

        $order->update(['status' => 'cancelled']);

        ActivityLogger::log('order.cancelled', $order, [
            'reference' => $order->reference,
            'amount' => $order->amount,
            'customer_phone' => $order->customer_phone,
        ], $request);

        return back()->with('success', 'Commande annulee.');
    }

    private function filteredOrders(Request $request)
    {
        return Order::with(['plan.router.user', 'ticket', 'payment'])
            ->when($request->filled('client_id'), function ($query) use ($request) {
                $query->whereHas('plan.router', function ($routerQuery) use ($request) {
                    $routerQuery->where('user_id', $request->client_id);
                });
            })
            ->when($request->filled('router_id'), function ($query) use ($request) {
                $query->whereHas('plan', function ($planQuery) use ($request) {
                    $planQuery->where('router_id', $request->router_id);
                });
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->when($request->filled('date_from'), function ($query) use ($request) {
                $query->whereDate('created_at', '>=', $request->date_from);
            })
            ->when($request->filled('date_to'), function ($query) use ($request) {
                $query->whereDate('created_at', '<=', $request->date_to);
            })
            ->when($request->filled('reference'), function ($query) use ($request) {
                $query->where('reference', 'like', '%' . $request->reference . '%');
            })
            ->when($request->filled('phone'), function ($query) use ($request) {
                $query->where('customer_phone', 'like', '%' . $request->phone . '%');
            });
    }

    private function statuses()
    {
        return [
            'pending' => 'En attente',
            'paid' => 'Payee',
            'failed' => 'Echouee',
            'cancelled' => 'Annulee',
            'refunded' => 'Remboursee',
        ];
    }
}
