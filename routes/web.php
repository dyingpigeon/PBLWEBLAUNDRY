<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\HeaderController;
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

        // ✅ SINGLE ROUTE untuk show - HAPUS DUPLIKAT
        Route::get('/{serviceId}', [ServiceController::class, 'show'])->name('services.show');

        // Service actions
        Route::post('/{id}/toggle', [ServiceController::class, 'toggleService'])->name('services.toggle');
        Route::get('/{id}/edit', [ServiceController::class, 'getServiceForEdit'])->name('services.edit');
        Route::post('/{id}', [ServiceController::class, 'updateService'])->name('services.update');
        Route::delete('/{id}', [ServiceController::class, 'destroy'])->name('services.destroy');

        // Service items routes
        Route::post('/{serviceId}/items', [ServiceController::class, 'addServiceItem'])->name('services.items.store');
        Route::post('/{serviceId}/items/{itemId}', [ServiceController::class, 'updateServiceItem'])->name('services.items.update');
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

        // API Routes untuk transaction creation
        Route::post('/customers', [TransactionController::class, 'getCustomers'])->name('transactions.getCustomers');
        Route::post('/services', [TransactionController::class, 'getServices'])->name('transactions.getServices');
    });

    // Tracking Routes
    Route::prefix('tracking')->group(function () {
        Route::get('/', [TrackingController::class, 'index'])->name('tracking.index');
        Route::get('/search', [TrackingController::class, 'search'])->name('tracking.search');
        Route::get('/filter', [TrackingController::class, 'filterByStatus'])->name('tracking.filter');
        Route::get('/{id}', [TrackingController::class, 'show'])->name('tracking.show');
        Route::put('/{id}/status', [TrackingController::class, 'updateStatus'])->name('tracking.updateStatus');
        Route::put('/{id}/payment', [TrackingController::class, 'updatePayment'])->name('tracking.updatePayment');
    });

    // ✅ PERBAIKAN 1: Report Routes - HAPUS DUPLIKASI
    Route::prefix('reports')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('reports.index');
        // HAPUS route yang tidak ada di controller:
        // Route::post('/data', [ReportController::class, 'getReportData'])->name('reports.data'); // TIDAK ADA
        // Route::post('/export/pdf', [ReportController::class, 'exportPDF'])->name('reports.export.pdf'); // TIDAK ADA
        // Route::post('/export/excel', [ReportController::class, 'exportExcel'])->name('reports.export.excel'); // TIDAK ADA
        
        // ✅ GUNAKAN route yang sesuai dengan controller:
        Route::get('/financial-summary', [ReportController::class, 'getFinancialSummary'])->name('reports.financial-summary');
        Route::post('/export', [ReportController::class, 'exportReport'])->name('reports.export');
    });

    // ✅ PERBAIKAN 2: API Routes - Reorganize dan konsisten
    Route::prefix('api')->group(function () {
        // Customer API
        Route::get('/top-customers', [CustomerController::class, 'getTopCustomers'])->name('customers.top');
        Route::get('/transactions/customers', [TransactionController::class, 'getCustomers'])->name('api.transactions.customers');

        // Service API
        Route::get('/transactions/services', [TransactionController::class, 'getServices'])->name('api.transactions.services');
        Route::get('/transactions/categories', [TransactionController::class, 'getCategories'])->name('api.transactions.categories');
        Route::get('/transactions/categories/{categoryId}/items', [TransactionController::class, 'getCategoryItems'])->name('api.transactions.category.items');

        // Transaction API
        Route::get('/transactions/today-summary', [TransactionController::class, 'getTodaySummary'])->name('api.transactions.today-summary');
        Route::get('/transactions/recent', [TransactionController::class, 'getRecentTransactions'])->name('api.transactions.recent');
        Route::post('/transactions', [TransactionController::class, 'store'])->name('api.transactions.store');

        // ✅ PERBAIKAN 3: Report API - Pindahkan dari luar dan konsisten
        Route::prefix('reports')->group(function () {
            Route::get('/financial-summary', [ReportController::class, 'getFinancialSummary'])->name('api.reports.financial-summary');
            Route::get('/today-summary', [ReportController::class, 'getTodaySummary'])->name('api.reports.today-summary');
            Route::get('/revenue-comparison', [ReportController::class, 'getRevenueComparison'])->name('api.reports.revenue-comparison');
            Route::post('/export', [ReportController::class, 'exportReport'])->name('api.reports.export');
        });

        // Tracking API
        Route::prefix('tracking')->group(function () {
            Route::get('/today', [TrackingController::class, 'getTodayTransactions'])->name('api.tracking.today');
            Route::get('/stats', [TrackingController::class, 'getStats'])->name('api.tracking.stats');
            Route::get('/stats/processing', [TrackingController::class, 'getProcessingStats'])->name('api.tracking.processingStats');
            Route::get('/{id}/details', [TrackingController::class, 'getTransactionWithDetails'])->name('api.tracking.details');
        });
    });

    // Profile Routes
    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'index'])->name('profile.index');
        Route::put('/', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('profile.destroy');
        Route::put('/password', [ProfileController::class, 'updatePassword'])->name('password.update');
    });

    // ✅ PERBAIKAN 4: Hapus route duplicate untuk tracking dan reports
    // Route::get('/tracking', [TrackingController::class, 'index'])->name('tracking.index'); // DUPLIKAT - SUDAH ADA DI ATAS
    // Route::get('/reports', [ReportController::class, 'index'])->name('reports.index'); // DUPLIKAT - SUDAH ADA DI ATAS

    // Settings Routes
    Route::prefix('settings')->group(function () {
        Route::get('/', [SettingController::class, 'index'])->name('settings.index');
        Route::get('/business', [SettingController::class, 'getBusinessSettings'])->name('settings.business.get');
        Route::post('/business', [SettingController::class, 'saveBusinessSettings'])->name('settings.business.save');
        Route::get('/hours', [SettingController::class, 'getBusinessHours'])->name('settings.hours.get');
        Route::post('/hours', [SettingController::class, 'saveBusinessHours'])->name('settings.hours.save');
        Route::get('/receipt', [SettingController::class, 'getReceiptSettings'])->name('settings.receipt.get');
        Route::post('/receipt', [SettingController::class, 'saveReceiptSettings'])->name('settings.receipt.save');
        Route::get('/notifications', [SettingController::class, 'getNotificationSettings'])->name('settings.notifications.get');
        Route::post('/notifications', [SettingController::class, 'saveNotificationSettings'])->name('settings.notifications.save');
        Route::post('/backup', [SettingController::class, 'performBackup'])->name('settings.backup');
        Route::post('/reset', [SettingController::class, 'resetData'])->name('settings.reset');
    });

    // Header Routes
    Route::prefix('header')->group(function () {
        Route::get('/view', [HeaderController::class, 'renderHeader'])->name('header.view');
        Route::get('/business-name', [HeaderController::class, 'getBusinessName'])->name('header.business-name');
        Route::get('/data', [HeaderController::class, 'getHeaderData'])->name('header.data');
        Route::post('/business-name', [HeaderController::class, 'updateBusinessName'])->name('header.update-business-name');
        Route::get('/notifications', [HeaderController::class, 'getNotifications'])->name('header.notifications');
    });

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

// ✅ PERBAIKAN 5: Static JS files - Tetap pertahankan
Route::get('/js/{file}', function ($file) {
    $path = resource_path('js/' . $file);

    if (!file_exists($path)) {
        abort(404);
    }

    return response(file_get_contents($path), 200, [
        'Content-Type' => 'application/javascript',
    ]);
})->where('file', '.*\.js$');

// ✅ PERBAIKAN 6: Backup routes - Tetap pertahankan
Route::get('/backup/download/{filename}', function ($filename) {
    $filePath = 'backups/' . $filename;

    if (Storage::exists($filePath)) {
        return Storage::download($filePath);
    }

    return response()->json(['error' => 'File not found'], 404);
})->name('backup.download');

Route::get('/backup/files', function () {
    $files = Storage::files('backups');
    $fileInfo = [];

    foreach ($files as $file) {
        $fileInfo[] = [
            'name' => basename($file),
            'path' => $file,
            'size' => Storage::size($file) . ' bytes',
            'last_modified' => date('Y-m-d H:i:s', Storage::lastModified($file)),
            'download_url' => route('backup.download', ['filename' => basename($file)])
        ];
    }

    return response()->json([
        'success' => true,
        'files' => $fileInfo,
        'total_files' => count($files),
        'storage_path' => storage_path('app/backups')
    ]);
});