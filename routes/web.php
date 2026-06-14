<?php

use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminAuditController;
use App\Http\Controllers\AdminClientController;
use App\Http\Controllers\AdminClientNotificationController;
use App\Http\Controllers\AdminOrderController;
use App\Http\Controllers\AdminFinanceReportController;
use App\Http\Controllers\AdminNotificationController;
use App\Http\Controllers\AdminPaymentController;
use App\Http\Controllers\AdminPlanController;
use App\Http\Controllers\AdminQuotaTopupController;
use App\Http\Controllers\AdminQuotaReportController;
use App\Http\Controllers\AdminRefundController;
use App\Http\Controllers\AdminRouterController;
use App\Http\Controllers\AdminSettingController;
use App\Http\Controllers\AdminSubscriptionController;
use App\Http\Controllers\AdminSupportController;
use App\Http\Controllers\AdminTicketController;
use App\Http\Controllers\AdminWithdrawalController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardClientNotificationController;
use App\Http\Controllers\DashboardDashboardController;
use App\Http\Controllers\DashboardOnboardingController;
use App\Http\Controllers\DashboardOrderController;
use App\Http\Controllers\DashboardPaymentController;
use App\Http\Controllers\DashboardPlanController;
use App\Http\Controllers\DashboardQuotaTopupController;
use App\Http\Controllers\DashboardRouterController;
use App\Http\Controllers\DashboardSettingsController;
use App\Http\Controllers\DashboardSupportController;
use App\Http\Controllers\DashboardSubscriptionController;
use App\Http\Controllers\DashboardTicketController;
use App\Http\Controllers\DashboardWithdrawalController;
use App\Http\Controllers\LegalPageController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\PortalController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\TicketImportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/langue/{locale}', [LocaleController::class, 'set'])->name('locale.switch');

Route::get('/conditions-generales', [LegalPageController::class, 'terms'])->name('legal.terms');
Route::get('/confidentialite', [LegalPageController::class, 'privacy'])->name('legal.privacy');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1')->name('login.store');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:3,1')->name('register.store');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::get('/forfaits', [PlanController::class, 'index'])->name('plans.index');

Route::get('/portal/{router:public_slug}', [PortalController::class, 'show'])
    ->name('portal.show');

Route::get('/acheter/forfait/{plan:slug}', [OrderController::class, 'create'])
    ->name('orders.create');

Route::post('/acheter/forfait/{plan:slug}', [OrderController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('orders.store');

Route::get('/commande/{order:reference}/{accessToken}', [OrderController::class, 'show'])
    ->name('orders.show');

Route::post('/commande/{order:reference}/{accessToken}/paiement-test', [PaymentController::class, 'simulate'])
    ->middleware('throttle:10,1')
    ->name('payments.simulate');

Route::get('/ticket/{order:reference}/{accessToken}', [TicketController::class, 'show'])
    ->name('tickets.show');

Route::middleware(['auth', 'back_office', 'interface.context'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

        Route::get('/audit', [AdminAuditController::class, 'index'])
            ->middleware('back_office:audit.view')
            ->name('audit.index');

        Route::get('/reports/finance', [AdminFinanceReportController::class, 'index'])
            ->middleware('back_office:reports.view')
            ->name('reports.finance');
        Route::get('/reports/finance/export', [AdminFinanceReportController::class, 'export'])
            ->middleware('back_office:reports.export')
            ->name('reports.finance.export');
        Route::get('/reports/quota', [AdminQuotaReportController::class, 'index'])
            ->middleware('back_office:reports.view')
            ->name('reports.quota');

        Route::get('/settings', [AdminSettingController::class, 'index'])
            ->middleware('back_office:settings.manage')
            ->name('settings.index');
        Route::put('/settings', [AdminSettingController::class, 'update'])
            ->middleware('back_office:settings.manage')
            ->name('settings.update');

        Route::get('/clients', [AdminClientController::class, 'index'])
            ->middleware('back_office:clients.view')
            ->name('clients.index');
        Route::get('/clients/{client}', [AdminClientController::class, 'show'])
            ->middleware('back_office:clients.view')
            ->name('clients.show');
        Route::patch('/clients/{client}/status', [AdminClientController::class, 'updateStatus'])
            ->middleware('back_office:clients.manage')
            ->name('clients.status');
        Route::patch('/clients/{client}/password', [AdminClientController::class, 'resetPassword'])
            ->middleware('back_office:clients.manage')
            ->name('clients.password');
        Route::patch('/clients/{client}/role', [AdminClientController::class, 'updateRole'])
            ->middleware('back_office:clients.manage')
            ->name('clients.role');
        Route::patch('/clients/{client}/quota', [AdminClientController::class, 'updateQuota'])
            ->middleware('back_office:clients.manage')
            ->name('clients.quota');

        Route::get('/routeurs', [AdminRouterController::class, 'index'])
            ->middleware('back_office:routers.manage')
            ->name('routers.index');
        Route::get('/routeurs/create', [AdminRouterController::class, 'create'])
            ->middleware('back_office:routers.manage')
            ->name('routers.create');
        Route::post('/routeurs', [AdminRouterController::class, 'store'])
            ->middleware('back_office:routers.manage')
            ->name('routers.store');

        Route::get('/tarifs', [AdminPlanController::class, 'index'])
            ->middleware('back_office:plans.manage')
            ->name('plans.index');
        Route::get('/tarifs/create', [AdminPlanController::class, 'create'])
            ->middleware('back_office:plans.manage')
            ->name('plans.create');
        Route::post('/tarifs', [AdminPlanController::class, 'store'])
            ->middleware('back_office:plans.manage')
            ->name('plans.store');

        Route::get('/tickets', [AdminTicketController::class, 'index'])
            ->middleware('back_office:tickets.view')
            ->name('tickets.index');
        Route::get('/tickets/export', [AdminTicketController::class, 'export'])
            ->middleware('back_office:tickets.view')
            ->name('tickets.export');
        Route::get('/tickets/import', [TicketImportController::class, 'create'])
            ->middleware('back_office:tickets.manage')
            ->name('tickets.import.create');
        Route::post('/tickets/import', [TicketImportController::class, 'store'])
            ->middleware('back_office:tickets.manage')
            ->name('tickets.import.store');
        Route::patch('/tickets/{ticket}/disable', [AdminTicketController::class, 'disable'])
            ->middleware('back_office:tickets.manage')
            ->name('tickets.disable');
        Route::patch('/tickets/{ticket}/enable', [AdminTicketController::class, 'enable'])
            ->middleware('back_office:tickets.manage')
            ->name('tickets.enable');
        Route::delete('/tickets/clear', [AdminTicketController::class, 'clear'])
            ->middleware('back_office:tickets.manage')
            ->name('tickets.clear');
        Route::delete('/tickets/{ticket}', [AdminTicketController::class, 'destroy'])
            ->middleware('back_office:tickets.manage')
            ->name('tickets.destroy');

        Route::get('/orders', [AdminOrderController::class, 'index'])
            ->middleware('back_office:orders.view')
            ->name('orders.index');
        Route::get('/orders/{order}', [AdminOrderController::class, 'show'])
            ->middleware('back_office:orders.view')
            ->name('orders.show');
        Route::patch('/orders/{order}/cancel', [AdminOrderController::class, 'cancel'])
            ->middleware('back_office:orders.manage')
            ->name('orders.cancel');

        Route::get('/payments', [AdminPaymentController::class, 'index'])
            ->middleware('back_office:payments.view')
            ->name('payments.index');
        Route::get('/payments/{payment}', [AdminPaymentController::class, 'show'])
            ->middleware('back_office:payments.view')
            ->name('payments.show');
        Route::post('/payments/{payment}/verify', [AdminPaymentController::class, 'verify'])
            ->middleware('back_office:payments.manage')
            ->name('payments.verify');

        Route::get('/quota-topups', [AdminQuotaTopupController::class, 'index'])
            ->middleware('back_office:quota_topups.view')
            ->name('quota_topups.index');
        Route::patch('/quota-topups/{topup}/approve', [AdminQuotaTopupController::class, 'approve'])
            ->middleware('back_office:quota_topups.manage')
            ->name('quota_topups.approve');
        Route::patch('/quota-topups/{topup}/reject', [AdminQuotaTopupController::class, 'reject'])
            ->middleware('back_office:quota_topups.manage')
            ->name('quota_topups.reject');

        Route::get('/withdrawals', [AdminWithdrawalController::class, 'index'])
            ->middleware('back_office:withdrawals.view')
            ->name('withdrawals.index');
        Route::patch('/withdrawals/{withdrawal}/approve', [AdminWithdrawalController::class, 'approve'])
            ->middleware('back_office:withdrawals.manage')
            ->name('withdrawals.approve');
        Route::patch('/withdrawals/{withdrawal}/process', [AdminWithdrawalController::class, 'process'])
            ->middleware('back_office:withdrawals.manage')
            ->name('withdrawals.process');
        Route::patch('/withdrawals/{withdrawal}/reject', [AdminWithdrawalController::class, 'reject'])
            ->middleware('back_office:withdrawals.manage')
            ->name('withdrawals.reject');

        Route::get('/refunds', [AdminRefundController::class, 'index'])
            ->middleware('back_office:refunds.view')
            ->name('refunds.index');
        Route::get('/refunds/export', [AdminRefundController::class, 'export'])
            ->middleware('back_office:refunds.export')
            ->name('refunds.export');
        Route::post('/payments/{payment}/refunds', [AdminRefundController::class, 'store'])
            ->middleware('back_office:refunds.manage')
            ->name('refunds.store');
        Route::get('/refunds/{refund}', [AdminRefundController::class, 'show'])
            ->middleware('back_office:refunds.view')
            ->name('refunds.show');
        Route::patch('/refunds/{refund}/approve', [AdminRefundController::class, 'approve'])
            ->middleware('back_office:refunds.manage')
            ->name('refunds.approve');
        Route::patch('/refunds/{refund}/reject', [AdminRefundController::class, 'reject'])
            ->middleware('back_office:refunds.manage')
            ->name('refunds.reject');
        Route::patch('/refunds/{refund}/process', [AdminRefundController::class, 'process'])
            ->middleware('back_office:refunds.manage')
            ->name('refunds.process');

        Route::get('/notifications', [AdminNotificationController::class, 'index'])
            ->middleware('back_office:notifications.view')
            ->name('notifications.index');
        Route::patch('/notifications/{notification}/read', [AdminNotificationController::class, 'markRead'])
            ->middleware('back_office:notifications.view')
            ->name('notifications.read');
        Route::patch('/notifications/read-all', [AdminNotificationController::class, 'markAllRead'])
            ->middleware('back_office:notifications.view')
            ->name('notifications.read_all');

        Route::get('/client-notifications', [AdminClientNotificationController::class, 'index'])
            ->middleware('back_office:client_notifications.view')
            ->name('client_notifications.index');
        Route::post('/client-notifications', [AdminClientNotificationController::class, 'store'])
            ->middleware('back_office:client_notifications.manage')
            ->name('client_notifications.store');

        Route::get('/subscriptions', [AdminSubscriptionController::class, 'index'])
            ->middleware('back_office:subscriptions.view')
            ->name('subscriptions.index');
        Route::post('/subscriptions/plans', [AdminSubscriptionController::class, 'storePlan'])
            ->middleware('back_office:subscriptions.manage')
            ->name('subscriptions.plans.store');
        Route::patch('/subscriptions/plans/{plan}', [AdminSubscriptionController::class, 'updatePlan'])
            ->middleware('back_office:subscriptions.manage')
            ->name('subscriptions.plans.update');
        Route::post('/clients/{client}/subscription', [AdminSubscriptionController::class, 'assign'])
            ->middleware('back_office:subscriptions.manage')
            ->name('subscriptions.assign');
        Route::patch('/subscriptions/{subscription}/suspend', [AdminSubscriptionController::class, 'suspend'])
            ->middleware('back_office:subscriptions.manage')
            ->name('subscriptions.suspend');
        Route::patch('/subscriptions/{subscription}/reactivate', [AdminSubscriptionController::class, 'reactivate'])
            ->middleware('back_office:subscriptions.manage')
            ->name('subscriptions.reactivate');

        Route::get('/support', [AdminSupportController::class, 'index'])
            ->middleware('back_office:support.view')
            ->name('support.index');
        Route::get('/support/{ticket}', [AdminSupportController::class, 'show'])
            ->middleware('back_office:support.view')
            ->name('support.show');
        Route::post('/support/{ticket}/messages', [AdminSupportController::class, 'reply'])
            ->middleware('back_office:support.manage')
            ->name('support.reply');
        Route::patch('/support/{ticket}', [AdminSupportController::class, 'update'])
            ->middleware('back_office:support.manage')
            ->name('support.update');
    });

Route::middleware(['auth', 'dashboard_user', 'interface.context'])
    ->prefix('dashboard')
    ->name('dashboard.')
    ->group(function () {
        Route::get('/', [DashboardDashboardController::class, 'index'])->name('dashboard');
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::get('/onboarding', [DashboardOnboardingController::class, 'index'])->name('onboarding.index');

        Route::get('/routers', [DashboardRouterController::class, 'index'])->name('routers.index');
        Route::get('/routers/create', [DashboardRouterController::class, 'create'])->name('routers.create');
        Route::post('/routers', [DashboardRouterController::class, 'store'])->name('routers.store');
        Route::get('/routers/{router}/edit', [DashboardRouterController::class, 'edit'])->name('routers.edit');
        Route::put('/routers/{router}', [DashboardRouterController::class, 'update'])->name('routers.update');
        Route::patch('/routers/{router}/deactivate', [DashboardRouterController::class, 'deactivate'])->name('routers.deactivate');
        Route::delete('/routers/{router}', [DashboardRouterController::class, 'destroy'])->name('routers.destroy');

        Route::get('/plans', [DashboardPlanController::class, 'index'])->name('plans.index');
        Route::get('/plans/create', [DashboardPlanController::class, 'create'])->name('plans.create');
        Route::post('/plans', [DashboardPlanController::class, 'store'])->name('plans.store');
        Route::get('/plans/{plan}/edit', [DashboardPlanController::class, 'edit'])->name('plans.edit');
        Route::put('/plans/{plan}', [DashboardPlanController::class, 'update'])->name('plans.update');
        Route::patch('/plans/{plan}/toggle', [DashboardPlanController::class, 'toggle'])->name('plans.toggle');
        Route::delete('/plans/{plan}', [DashboardPlanController::class, 'destroy'])->name('plans.destroy');

        Route::get('/tickets', [DashboardTicketController::class, 'index'])->name('tickets.index');
        Route::get('/tickets/export', [DashboardTicketController::class, 'export'])->name('tickets.export');
        Route::get('/tickets/import', [TicketImportController::class, 'create'])->name('tickets.import.create');
        Route::post('/tickets/import', [TicketImportController::class, 'store'])->name('tickets.import.store');
        Route::patch('/tickets/{ticket}/disable', [DashboardTicketController::class, 'disable'])->name('tickets.disable');
        Route::patch('/tickets/{ticket}/enable', [DashboardTicketController::class, 'enable'])->name('tickets.enable');
        Route::delete('/tickets/clear', [DashboardTicketController::class, 'clear'])->name('tickets.clear');
        Route::delete('/tickets/{ticket}', [DashboardTicketController::class, 'destroy'])->name('tickets.destroy');

        Route::get('/orders', [DashboardOrderController::class, 'index'])->name('orders.index');
        Route::get('/payments', [DashboardPaymentController::class, 'index'])->name('payments.index');
        Route::get('/quota-topups', [DashboardQuotaTopupController::class, 'index'])->name('quota_topups.index');
        Route::post('/quota-topups', [DashboardQuotaTopupController::class, 'store'])->name('quota_topups.store');
        Route::get('/withdrawals', [DashboardWithdrawalController::class, 'index'])->name('withdrawals.index');
        Route::post('/withdrawals', [DashboardWithdrawalController::class, 'store'])->name('withdrawals.store');
        Route::get('/subscriptions', [DashboardSubscriptionController::class, 'index'])->name('subscriptions.index');
        Route::post('/subscriptions/{plan:slug}', [DashboardSubscriptionController::class, 'choose'])->name('subscriptions.choose');
        Route::get('/notifications', [DashboardClientNotificationController::class, 'index'])->name('notifications.index');
        Route::patch('/notifications/{notification}/read', [DashboardClientNotificationController::class, 'markRead'])->name('notifications.read');
        Route::get('/support', [DashboardSupportController::class, 'index'])->name('support.index');
        Route::get('/support/create', [DashboardSupportController::class, 'create'])->name('support.create');
        Route::post('/support', [DashboardSupportController::class, 'store'])->name('support.store');
        Route::get('/support/{ticket}', [DashboardSupportController::class, 'show'])->name('support.show');
        Route::post('/support/{ticket}/messages', [DashboardSupportController::class, 'reply'])->name('support.reply');
        Route::get('/settings', [DashboardSettingsController::class, 'index'])->name('settings.index');
        Route::put('/settings', [DashboardSettingsController::class, 'update'])->name('settings.update');
    });
