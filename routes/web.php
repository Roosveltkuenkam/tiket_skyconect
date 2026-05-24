<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\TicketImportController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminRouterController;
use App\Http\Controllers\AdminPlanController;
use App\Http\Controllers\AdminTicketController;



/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/forfaits', [PlanController::class, 'index'])->name('plans.index');

Route::get('/acheter/forfait/{plan:slug}', [OrderController::class, 'create'])
    ->name('orders.create');

Route::post('/acheter/forfait/{plan:slug}', [OrderController::class, 'store'])
    ->name('orders.store');

    Route::get('/commande/{order}', [OrderController::class, 'show'])
    ->name('orders.show');


Route::post('/commande/{order}/paiement-test', [PaymentController::class, 'simulate'])
    ->name('payments.simulate');

Route::get('/ticket/{order}', [TicketController::class, 'show'])
    ->name('tickets.show');


Route::get('/admin/tickets/import', [TicketImportController::class, 'create'])
    ->name('tickets.import.create');

Route::post('/admin/tickets/import', [TicketImportController::class, 'store'])
    ->name('tickets.import.store');


Route::get('/admin', [AdminDashboardController::class, 'index'])
    ->name('admin.dashboard');


Route::get('/admin/routeurs', [AdminRouterController::class, 'index'])
    ->name('admin.routers.index');

Route::get('/admin/routeurs/create', [AdminRouterController::class, 'create'])
    ->name('admin.routers.create');

Route::post('/admin/routeurs', [AdminRouterController::class, 'store'])
    ->name('admin.routers.store');

Route::get('/admin/tarifs', [AdminPlanController::class, 'index'])
    ->name('admin.plans.index');

Route::get('/admin/tarifs/create', [AdminPlanController::class, 'create'])
    ->name('admin.plans.create');

Route::post('/admin/tarifs', [AdminPlanController::class, 'store'])
    ->name('admin.plans.store');

Route::get('/admin/tickets', [AdminTicketController::class, 'index'])
    ->name('admin.tickets.index');

Route::patch('/admin/tickets/{ticket}/disable', [AdminTicketController::class, 'disable'])
    ->name('admin.tickets.disable');

Route::patch('/admin/tickets/{ticket}/enable', [AdminTicketController::class, 'enable'])
    ->name('admin.tickets.enable');

Route::delete('/admin/tickets/{ticket}', [AdminTicketController::class, 'destroy'])
    ->name('admin.tickets.destroy');

Route::delete('/admin/tickets/clear', [AdminTicketController::class, 'clear'])
    ->name('admin.tickets.clear');