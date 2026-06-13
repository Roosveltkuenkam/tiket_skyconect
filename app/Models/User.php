<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    const ROLE_SUPER_ADMIN = 'super_admin';
    const ROLE_ADMIN = 'admin';
    const ROLE_SUPPORT_AGENT = 'support_agent';
    const ROLE_ACCOUNTANT = 'accountant';
    const ROLE_TECHNICIAN = 'technician';
    const ROLE_CLIENT = 'client';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'business_name',
        'business_type',
        'city',
        'country',
        'role',
        'is_active',
        'quota_balance',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
        'quota_balance' => 'integer',
    ];

    public function isSuperAdmin()
    {
        return $this->role === self::ROLE_SUPER_ADMIN;
    }

    public function isBackOfficeUser()
    {
        return in_array($this->role, [
            self::ROLE_SUPER_ADMIN,
            self::ROLE_ADMIN,
            self::ROLE_SUPPORT_AGENT,
            self::ROLE_ACCOUNTANT,
            self::ROLE_TECHNICIAN,
        ], true);
    }

    public function isClientOwner()
    {
        return $this->role === self::ROLE_CLIENT;
    }

    public function isAdminLike()
    {
        return $this->isClientOwner();
    }

    public function roleLabel()
    {
        return self::roleLabels()[$this->role] ?? $this->role;
    }

    public static function roleLabels()
    {
        return [
            self::ROLE_SUPER_ADMIN => 'Super admin',
            self::ROLE_ADMIN => 'Admin',
            self::ROLE_SUPPORT_AGENT => 'Agent support',
            self::ROLE_ACCOUNTANT => 'Comptable',
            self::ROLE_TECHNICIAN => 'Technicien',
            self::ROLE_CLIENT => 'Client proprietaire',
        ];
    }

    public function canAccessBackOffice($permission = null)
    {
        if (! $this->isBackOfficeUser()) {
            return false;
        }

        if ($this->isSuperAdmin() || $permission === null) {
            return true;
        }

        $permissions = [
            self::ROLE_ADMIN => [
                'dashboard.view',
                'clients.view',
                'clients.manage',
                'routers.manage',
                'plans.manage',
                'tickets.view',
                'tickets.manage',
                'orders.view',
                'orders.manage',
                'payments.view',
                'payments.manage',
                'quota_topups.view',
                'quota_topups.manage',
                'withdrawals.view',
                'withdrawals.manage',
                'refunds.view',
                'refunds.manage',
                'refunds.export',
                'subscriptions.view',
                'subscriptions.manage',
                'notifications.view',
                'client_notifications.view',
                'client_notifications.manage',
                'reports.view',
                'reports.export',
                'settings.manage',
                'support.view',
                'support.manage',
            ],
            self::ROLE_SUPPORT_AGENT => [
                'dashboard.view',
                'clients.view',
                'orders.view',
                'tickets.view',
                'payments.view',
                'quota_topups.view',
                'withdrawals.view',
                'refunds.view',
                'notifications.view',
                'client_notifications.view',
                'support.view',
                'support.manage',
            ],
            self::ROLE_ACCOUNTANT => [
                'dashboard.view',
                'orders.view',
                'payments.view',
                'payments.export',
                'quota_topups.view',
                'quota_topups.manage',
                'withdrawals.view',
                'withdrawals.manage',
                'refunds.view',
                'refunds.manage',
                'refunds.export',
                'subscriptions.view',
                'notifications.view',
                'reports.view',
                'reports.export',
            ],
            self::ROLE_TECHNICIAN => [
                'dashboard.view',
                'orders.view',
                'tickets.view',
                'routers.manage',
                'tickets.manage',
                'notifications.view',
                'diagnostics.view',
                'stocks.view',
            ],
        ];

        return in_array($permission, $permissions[$this->role] ?? [], true);
    }

    public function routers()
    {
        return $this->hasMany(Router::class);
    }

    public function supportTickets()
    {
        return $this->hasMany(SupportTicket::class);
    }

    public function supportMessages()
    {
        return $this->hasMany(SupportTicketMessage::class);
    }

    public function clientSubscription()
    {
        return $this->hasOne(ClientSubscription::class);
    }

    public function wallet()
    {
        return $this->hasOne(ClientWallet::class);
    }

    public function walletTransactions()
    {
        return $this->hasMany(ClientWalletTransaction::class);
    }

    public function quotaTransactions()
    {
        return $this->hasMany(QuotaTransaction::class);
    }

    public function quotaTopups()
    {
        return $this->hasMany(ClientQuotaTopup::class);
    }

    public function withdrawals()
    {
        return $this->hasMany(ClientWithdrawal::class);
    }

    public function withdrawalRequests()
    {
        return $this->hasMany(WithdrawalRequest::class);
    }

    public function activeSubscription()
    {
        $subscription = $this->clientSubscription()->with('plan')->first();

        if (! $subscription) {
            return null;
        }

        return $subscription->refreshExpirationStatus();
    }

    public function canUseSubscription()
    {
        if (! $this->isClientOwner()) {
            return true;
        }

        $subscription = $this->activeSubscription();

        return $subscription && $subscription->isUsable();
    }

    public function subscriptionLimitReached($limit)
    {
        if (! $this->isClientOwner()) {
            return false;
        }

        $subscription = $this->activeSubscription();

        if (! $subscription || ! $subscription->plan) {
            return true;
        }

        if ($limit === 'routers') {
            return $subscription->plan->max_routers !== null
                && $this->routers()->count() >= $subscription->plan->max_routers;
        }

        if ($limit === 'tickets') {
            if ($subscription->plan->max_tickets_per_month === null) {
                return false;
            }

            $count = Ticket::whereHas('plan.router', function ($query) {
                $query->where('user_id', $this->id);
            })->where('created_at', '>=', now()->startOfMonth())->count();

            return $count >= $subscription->plan->max_tickets_per_month;
        }

        if ($limit === 'sales') {
            if ($subscription->plan->max_sales_per_month === null) {
                return false;
            }

            $count = Order::where('status', 'paid')
                ->whereHas('plan.router', function ($query) {
                    $query->where('user_id', $this->id);
                })
                ->where('created_at', '>=', now()->startOfMonth())
                ->count();

            return $count >= $subscription->plan->max_sales_per_month;
        }

        return false;
    }
}
