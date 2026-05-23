<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\TicketImportController;
use App\Http\Controllers\AdminDashboardController;


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