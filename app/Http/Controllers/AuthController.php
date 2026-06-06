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
                ->withErrors(['email' => __('auth.failed')])
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
            'phone' => 'required|string|max:50',
            'business_name' => 'required|string|max:255',
            'business_type' => 'required|in:hotel,cybercafe,snack,residence,campus,other',
            'city' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'password' => 'required|string|min:8|confirmed',
            'terms_accepted' => 'accepted',
        ], [
            'terms_accepted.accepted' => __('validation.accepted', ['attribute' => __('ui.auth.terms')]),
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'business_name' => $data['business_name'],
            'business_type' => $data['business_type'],
            'city' => $data['city'] ?? null,
            'country' => $data['country'] ?: config('app.default_country', 'Cameroun'),
            'role' => 'client',
            'is_active' => true,
            'password' => Hash::make($data['password']),
        ]);

        $defaultPlan = SubscriptionPlan::firstOrCreate(
            ['slug' => 'standard'],
            [
                'name' => 'Standard',
                'description' => 'Pour les proprietaires WiFi qui lancent leurs ventes avec un ou plusieurs hotspots.',
                'monthly_price' => 5000,
                'max_routers' => 3,
                'max_tickets_per_month' => 1000,
                'max_sales_per_month' => 500,
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
            'business_type' => $user->business_type,
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
