<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\TrackingController;
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

    // Customer Routes
    Route::prefix('customers')->group(function () {
        Route::get('/', [CustomerController::class, 'index'])->name('customers.index');
        Route::post('/', [CustomerController::class, 'store'])->name('customers.store');
        Route::get('/search', [CustomerController::class, 'search'])->name('customers.search');
        Route::get('/{id}', [CustomerController::class, 'show'])->name('customers.show');
        Route::put('/{id}', [CustomerController::class, 'update'])->name('customers.update');
        Route::delete('/{id}', [CustomerController::class, 'destroy'])->name('customers.destroy');
    });

    // Service Routes
    Route::prefix('services')->group(function () {
        // Basic service routes
        Route::get('/', [ServiceController::class, 'index'])->name('services.index');
        Route::post('/', [ServiceController::class, 'store'])->name('services.store');
        Route::get('/{serviceId}', [ServiceController::class, 'getServiceWithItems'])->name('services.show');
        Route::post('/{id}/toggle', [ServiceController::class, 'toggleService']);

        // Service items routes
        Route::post('/{serviceId}/items', [ServiceController::class, 'addServiceItem'])->name('services.items.store');
        Route::put('/{serviceId}/items/{itemId}', [ServiceController::class, 'updateServiceItem'])->name('services.items.update');
        Route::delete('/{serviceId}/items/{itemId}', [ServiceController::class, 'deleteServiceItem'])->name('services.items.delete');
    });

    // Transaction Routes
    Route::prefix('transactions')->group(function () {
        Route::get('/', [TransactionController::class, 'index'])->name('transactions.index');
        Route::get('/create', [TransactionController::class, 'create'])->name('transactions.create');
        Route::post('/', [TransactionController::class, 'store'])->name('transactions.store');
        Route::get('/{transaction}', [TransactionController::class, 'show'])->name('transactions.show');
        Route::put('/{transaction}/status', [TransactionController::class, 'updateStatus'])->name('transactions.updateStatus');
        Route::get('/{transaction}/receipt', [TransactionController::class, 'printReceipt'])->name('transactions.receipt');
        Route::post('/customers', [TransactionController::class, 'getCustomers'])->name('transactions.getCustomers');
        Route::post('/services', [TransactionController::class, 'getServices'])->name('transactions.getServices');
    });

    // API Routes untuk data
    Route::prefix('api')->group(function () {
        Route::get('/top-customers', [CustomerController::class, 'getTopCustomers'])->name('customers.top');
        Route::get('/transactions/customers', [TransactionController::class, 'getCustomers'])->name('transactions.customers');
        Route::get('/transactions/services', [TransactionController::class, 'getServices'])->name('transactions.services');
        Route::get('/transactions/today-summary', [TransactionController::class, 'getTodaySummary'])->name('transactions.today-summary');
        Route::get('/transactions/recent', [TransactionController::class, 'getRecentTransactions'])->name('transactions.recent');

        // REPORT API ROUTES - TAMBAHKAN INI
        Route::prefix('reports')->group(function () {
            Route::get('/financial-summary', [ReportController::class, 'getFinancialSummary'])->name('reports.financial-summary');
            Route::get('/today-summary', [ReportController::class, 'getTodaySummary'])->name('reports.today-summary');
            Route::get('/revenue-comparison', [ReportController::class, 'getRevenueComparison'])->name('reports.revenue-comparison');
            Route::post('/export', [ReportController::class, 'exportReport'])->name('reports.export');
        });
    });

    // Profile Routes
    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'index'])->name('profile.index');
        Route::put('/', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('profile.destroy');
        Route::put('/password', [ProfileController::class, 'updatePassword'])->name('password.update');
    });

    // Other Routes
    Route::get('/tracking', [TrackingController::class, 'index'])->name('tracking.index');
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index'); // Halaman laporan
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');

    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Temporary routes (coming soon)
    Route::get('/forgot-password', function () {
        return view('coming-soon');
    })->name('password.request');
});

// Fallback - redirect to dashboard if authenticated, otherwise to login
Route::fallback(function () {
    return auth()->check() ? redirect('/dashboard') : redirect('/login');
});

// routes/web.php
Route::get('/js/{file}', function ($file) {
    $path = resource_path('js/' . $file);

    if (!file_exists($path)) {
        abort(404);
    }

    return response(file_get_contents($path), 200, [
        'Content-Type' => 'application/javascript',
    ]);
})->where('file', '.*\.js$');