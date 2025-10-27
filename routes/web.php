<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

// Redirect root to login
Route::get('/', function () {
    return redirect('/login');
});

// Public routes (only for guests)
Route::middleware(['guest'])->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// Protected routes (require authentication)
Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Tambahkan di dalam group auth
    Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');

    Route::get('/services', [ServiceController::class, 'index'])->name('services.index');

    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::post('/transactions', [TransactionController::class, 'logout'])->name('logout');

    // Temporary routes (coming soon)
    Route::get('/transactions', function () {
        return view('coming-soon');
    })->name('transactions.index');

    // Route::get('/customers', function () {
    //     return view('coming-soon');
    // })->name('customers.index');

    Route::get('/reports', function () {
        return view('coming-soon');
    })->name('reports.index');

    Route::get('/forgot-password', function () {
        return view('coming-soon');
    })->name('password.request');


    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
    Route::post('/transactions', [TransactionController::class, 'store'])->name('transactions.store');
    Route::get('/transactions/{transaction}', [TransactionController::class, 'show'])->name('transactions.show');
    Route::put('/transactions/{transaction}/status', [TransactionController::class, 'updateStatus'])->name('transactions.updateStatus');
    Route::get('/transactions/{transaction}/receipt', [TransactionController::class, 'printReceipt'])->name('transactions.receipt');

    // API Routes untuk data
    Route::get('/api/transactions/customers', [TransactionController::class, 'getCustomers'])->name('transactions.customers');
    Route::get('/api/transactions/services', [TransactionController::class, 'getServices'])->name('transactions.services');
    Route::get('/api/transactions/today-summary', [TransactionController::class, 'getTodaySummary'])->name('transactions.today-summary');
    Route::get('/api/transactions/recent', [TransactionController::class, 'getRecentTransactions'])->name('transactions.recent');
});

// Fallback - redirect to dashboard if authenticated, otherwise to login
Route::get('/', function () {
    return auth()->check() ? redirect('/dashboard') : redirect('/login');
});