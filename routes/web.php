<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\FarmController;
use App\Http\Controllers\HarvestController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RiceVarietyController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\AdminOnly;
use Illuminate\Support\Facades\Route;

// FR-01 Login
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:8,1');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Master data (FR-03, FR-07) and lookups. Staff may create/edit; delete is Admin-only (below).
    Route::resource('farms', FarmController::class)->except(['show', 'destroy']);
    Route::resource('varieties', RiceVarietyController::class)->except(['show', 'destroy'])->parameters(['varieties' => 'variety']);
    Route::resource('customers', CustomerController::class)->except(['destroy']);

    // Harvests (FR-04) and derived inventory (FR-05, FR-06, FR-12)
    Route::resource('harvests', HarvestController::class)->except(['destroy']);
    Route::get('inventory', [InventoryController::class, 'index'])->name('inventory.index');

    // Sales (FR-08, FR-09): header + lines + payments
    Route::resource('sales', SaleController::class)->except(['destroy']);
    Route::post('sales/{sale}/lines', [SaleController::class, 'addLine'])->name('sales.lines.store');
    Route::post('sales/{sale}/payments', [SaleController::class, 'addPayment'])->name('sales.payments.store');

    // Expenses (FR-10)
    Route::resource('expenses', ExpenseController::class)->except(['show', 'destroy']);

    // Reports (FR-11 .. FR-14)
    Route::get('reports/{type}', [ReportController::class, 'show'])->name('reports.show');

    // Administrator only: user management (FR-02) and every delete action
    Route::middleware(AdminOnly::class)->group(function () {
        Route::resource('users', UserController::class)->except(['show']);
        Route::delete('farms/{farm}', [FarmController::class, 'destroy'])->name('farms.destroy');
        Route::delete('varieties/{variety}', [RiceVarietyController::class, 'destroy'])->name('varieties.destroy');
        Route::delete('customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');
        Route::delete('harvests/{harvest}', [HarvestController::class, 'destroy'])->name('harvests.destroy');
        Route::delete('sales/{sale}', [SaleController::class, 'destroy'])->name('sales.destroy');
        Route::delete('sales/{sale}/lines/{detail}', [SaleController::class, 'removeLine'])->name('sales.lines.destroy');
        Route::delete('sales/{sale}/payments/{payment}', [SaleController::class, 'removePayment'])->name('sales.payments.destroy');
        Route::delete('expenses/{expense}', [ExpenseController::class, 'destroy'])->name('expenses.destroy');
    });
});
