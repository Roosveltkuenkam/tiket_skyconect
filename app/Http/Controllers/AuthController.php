<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\AdminNotification;
use App\Models\ClientSubscription;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Services\ActivityLogger;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $credentials['is_active'] = true;

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withErrors(['email' => 'Identifiants invalides.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        $user = $request->user();

        if ($user->isBackOfficeUser()) {
            ActivityLogger::log('admin.login', $user, [
                'role' => $user->role,
                'email' => $user->email,
            ], $request);

            return redirect()->intended(route('admin.dashboard'));
        }

        $request->session()->forget('url.intended');

        return redirect()->route('dashboard.dashboard');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'phone' => 'nullable|string|max:50',
            'business_name' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'business_name' => $data['business_name'] ?? null,
            'city' => $data['city'] ?? null,
            'country' => $data['country'] ?? null,
            'role' => 'client',
            'is_active' => true,
            'password' => Hash::make($data['password']),
        ]);

        $defaultPlan = SubscriptionPlan::firstOrCreate(
            ['slug' => 'gratuit'],
            [
                'name' => 'Gratuit',
                'description' => 'Demarrage simple pour tester SkyConnect.',
                'monthly_price' => 0,
                'max_routers' => 1,
                'max_tickets_per_month' => 100,
                'max_sales_per_month' => 50,
                'is_active' => true,
            ]
        );

        ClientSubscription::create([
            'user_id' => $user->id,
            'subscription_plan_id' => $defaultPlan->id,
            'status' => ClientSubscription::STATUS_TRIAL,
            'starts_at' => now(),
            'expires_at' => now()->addDays(30),
            'notes' => 'Abonnement gratuit assigne automatiquement a l inscription.',
        ]);

        AdminNotification::notify(
            'client_registered',
            'Nouveau client inscrit',
            $user->name . ' a cree un compte proprietaire WiFi.',
            'success',
            ['user_id' => $user->id, 'email' => $user->email]
        );

        ActivityLogger::log('client.created', $user, [
            'name' => $user->name,
            'email' => $user->email,
            'business_name' => $user->business_name,
        ], $request);

        Auth::login($user);

        return redirect()->route('dashboard.onboarding.index');
    }

    public function logout(Request $request)
    {
        $user = $request->user();

        if ($user && $user->isBackOfficeUser()) {
            ActivityLogger::log('admin.logout', $user, [
                'role' => $user->role,
                'email' => $user->email,
            ], $request);
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
