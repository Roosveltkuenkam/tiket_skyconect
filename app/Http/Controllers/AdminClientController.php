<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Router;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Services\ActivityLogger;

class AdminClientController extends Controller
{
    public function index(Request $request)
    {
        $clients = User::where('role', User::ROLE_CLIENT)
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = '%' . $request->search . '%';

                $query->where(function ($clientQuery) use ($search) {
                    $clientQuery
                        ->where('name', 'like', $search)
                        ->orWhere('email', 'like', $search)
                        ->orWhere('phone', 'like', $search)
                        ->orWhere('business_name', 'like', $search);
                });
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('is_active', $request->status === 'active');
            })
            ->withCount('routers')
            ->latest()
            ->paginate(20)
            ->appends($request->query());

        return view('admin.clients.index', compact('clients'));
    }

    public function show(User $client)
    {
        $this->ensureClient($client);

        $client->load(['routers.plans', 'clientSubscription.plan']);

        $orders = Order::with(['plan.router', 'ticket', 'payment'])
            ->whereHas('plan.router', function ($query) use ($client) {
                $query->where('user_id', $client->id);
            })
            ->latest()
            ->paginate(10, ['*'], 'orders_page');

        $payments = Payment::with('order.plan.router')
            ->whereHas('order.plan.router', function ($query) use ($client) {
                $query->where('user_id', $client->id);
            })
            ->latest()
            ->paginate(10, ['*'], 'payments_page');

        $stats = [
            'routers' => Router::where('user_id', $client->id)->count(),
            'orders' => Order::whereHas('plan.router', function ($query) use ($client) {
                $query->where('user_id', $client->id);
            })->count(),
            'revenue' => Order::where('status', 'paid')
                ->whereHas('plan.router', function ($query) use ($client) {
                    $query->where('user_id', $client->id);
                })
                ->sum('amount'),
            'payments_successful' => Payment::where('status', 'successful')
                ->whereHas('order.plan.router', function ($query) use ($client) {
                    $query->where('user_id', $client->id);
                })
                ->count(),
        ];

        $roles = User::roleLabels();
        $subscriptionPlans = SubscriptionPlan::where('is_active', true)->orderBy('monthly_price')->get();

        return view('admin.clients.show', compact('client', 'orders', 'payments', 'stats', 'roles', 'subscriptionPlans'));
    }

    public function updateStatus(Request $request, User $client)
    {
        $this->ensureManagePermission($request);
        $this->ensureClient($client);

        $oldStatus = $client->is_active;
        $client->update(['is_active' => ! $client->is_active]);

        ActivityLogger::log($client->is_active ? 'client.activated' : 'client.suspended', $client, [
            'old_status' => $oldStatus ? 'active' : 'inactive',
            'new_status' => $client->is_active ? 'active' : 'inactive',
            'client_email' => $client->email,
        ], $request);

        return back()->with('success', $client->is_active ? 'Client active.' : 'Client desactive.');
    }

    public function resetPassword(Request $request, User $client)
    {
        $this->ensureManagePermission($request);
        $this->ensureClient($client);

        $temporaryPassword = 'Sky-' . Str::upper(Str::random(8));

        $client->update([
            'password' => Hash::make($temporaryPassword),
        ]);

        ActivityLogger::log('client.password_reset', $client, [
            'client_email' => $client->email,
            'temporary_password_generated' => true,
        ], $request);

        return back()->with('success', 'Mot de passe temporaire : ' . $temporaryPassword);
    }

    public function updateRole(Request $request, User $client)
    {
        $this->ensureManagePermission($request);
        $this->ensureClient($client);

        $data = $request->validate([
            'role' => 'required|in:client,admin,support_agent,accountant,technician',
        ]);

        $oldRole = $client->role;
        $client->update(['role' => $data['role']]);

        ActivityLogger::log('client.role_updated', $client, [
            'old_role' => $oldRole,
            'new_role' => $client->role,
            'client_email' => $client->email,
        ], $request);

        return redirect()->route('admin.clients.index')
            ->with('success', 'Role mis a jour.');
    }

    private function ensureClient(User $client)
    {
        abort_unless($client->isClientOwner(), 404);
    }

    private function ensureManagePermission(Request $request)
    {
        abort_unless($request->user()->canAccessBackOffice('clients.manage'), 403);
    }
}
