<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class TransactionController extends Controller
{
    /**
     * Display listing of transactions
     */
    public function index()
    {
        Log::debug('TransactionController@index: Memulai proses menampilkan halaman transactions');
        Log::debug('TransactionController@index: Mengembalikan view transactions.index');
        return view('transactions.index');
    }

    /**
     * Show form for creating new transaction
     */
    public function create()
    {
        Log::debug('TransactionController@create: Redirect ke transactions.index');
        return redirect()->route('transactions.index');
    }

    /**
     * Display specific transaction
     */
    public function show($id)
    {
        Log::debug('TransactionController@show: Memulai proses mengambil detail transaction', ['transaction_id' => $id]);

        // Get transaction dengan query langsung
        $query = "
            SELECT 
                t.*,
                c.name as customer_name,
                c.phone as customer_phone,
                c.address as customer_address,
                s.name as service_name,
                s.description as service_description
            FROM transactions t
            LEFT JOIN customers c ON t.customer_id = c.id
            LEFT JOIN services s ON t.service_id = s.id
            WHERE t.id = ?
        ";

        Log::debug('TransactionController@show: Menjalankan query detail transaction');
        $startTime = microtime(true);
        $transaction = DB::selectOne($query, [$id]);
        $executionTime = round((microtime(true) - $startTime) * 1000, 2);

        if (!$transaction) {
            Log::warning('TransactionController@show: Transaction tidak ditemukan', ['transaction_id' => $id]);
            return response()->json([
                'success' => false,
                'message' => 'Transaksi tidak ditemukan'
            ], 404);
        }

        Log::debug('TransactionController@show: Transaction ditemukan', [
            'transaction_number' => $transaction->transaction_number,
            'customer_name' => $transaction->customer_name,
            'status' => $transaction->status,
            'execution_time_ms' => $executionTime
        ]);

        // Get transaction items
        Log::debug('TransactionController@show: Mengambil transaction items');
        $itemsQuery = "
            SELECT 
                ti.*,
                si.name as service_item_name,
                si.description as service_item_description
            FROM transaction_items ti
            LEFT JOIN service_items si ON ti.service_item_id = si.id
            WHERE ti.transaction_id = ?
        ";

        $items = DB::select($itemsQuery, [$id]);
        $transaction->items = $items;

        Log::debug('TransactionController@show: Detail transaction berhasil diambil', [
            'transaction_id' => $id,
            'items_count' => count($items),
            'total_amount' => $transaction->total_amount
        ]);

        return response()->json([
            'success' => true,
            'data' => $transaction
        ]);
    }

    /**
     * Update transaction status
     */
    public function updateStatus(Request $request, $id)
    {
        Log::debug('TransactionController@updateStatus: Memulai update status transaction', [
            'transaction_id' => $id,
            'new_status' => $request->status
        ]);

        $validated = $request->validate([
            'status' => 'required|in:new,washing,ironing,ready,picked_up,cancelled'
        ]);

        // Check if transaction exists
        Log::debug('TransactionController@updateStatus: Memeriksa keberadaan transaction');
        $transaction = DB::selectOne("SELECT timeline FROM transactions WHERE id = ?", [$id]);

        if (!$transaction) {
            Log::warning('TransactionController@updateStatus: Transaction tidak ditemukan', ['transaction_id' => $id]);
            return response()->json([
                'success' => false,
                'message' => 'Transaksi tidak ditemukan'
            ], 404);
        }

        Log::debug('TransactionController@updateStatus: Transaction ditemukan', [
            'transaction_id' => $id,
            'current_timeline_entries' => $transaction->timeline ? count(json_decode($transaction->timeline, true)) : 0
        ]);

        // Update timeline
        $timeline = $transaction->timeline ? json_decode($transaction->timeline, true) : [];
        $newTimelineEntry = [
            'status' => $validated['status'],
            'timestamp' => now()->toISOString(),
            'description' => $this->getStatusDescription($validated['status'])
        ];
        $timeline[] = $newTimelineEntry;

        Log::debug('TransactionController@updateStatus: Menambahkan timeline entry', $newTimelineEntry);

        // Update transaction status dengan query langsung
        $updateQuery = "
            UPDATE transactions 
            SET status = ?, timeline = ?, updated_at = NOW()
            WHERE id = ?
        ";

        Log::debug('TransactionController@updateStatus: Menjalankan update status');
        $affectedRows = DB::update($updateQuery, [
            $validated['status'],
            json_encode($timeline),
            $id
        ]);

        Log::debug('TransactionController@updateStatus: Update status berhasil', ['affected_rows' => $affectedRows]);

        // Get updated transaction
        $updatedTransaction = DB::selectOne("
            SELECT t.*, c.name as customer_name, s.name as service_name
            FROM transactions t
            LEFT JOIN customers c ON t.customer_id = c.id
            LEFT JOIN services s ON t.service_id = s.id
            WHERE t.id = ?
        ", [$id]);

        Log::debug('TransactionController@updateStatus: Status berhasil diupdate', [
            'transaction_id' => $id,
            'old_status' => $transaction->status ?? 'unknown',
            'new_status' => $updatedTransaction->status,
            'timeline_entries_count' => count($timeline)
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Status transaksi berhasil diupdate',
            'data' => $updatedTransaction
        ]);
    }

    /**
     * Get status description for timeline
     */
    private function getStatusDescription($status)
    {
        $descriptions = [
            'new' => 'Pesanan baru dibuat',
            'washing' => 'Sedang dalam proses pencucian',
            'ironing' => 'Sedang dalam proses penyetrikaan',
            'ready' => 'Pesanan sudah siap diambil',
            'picked_up' => 'Pesanan sudah diambil oleh customer',
            'cancelled' => 'Pesanan dibatalkan'
        ];

        $description = $descriptions[$status] ?? 'Status updated';
        Log::debug('TransactionController@getStatusDescription: Status description', [
            'status' => $status,
            'description' => $description
        ]);

        return $description;
    }

    /**
     * Get customers for transaction (searchable)
     */
    public function getCustomers(Request $request)
    {
        $search = $request->get('search');
        Log::debug('TransactionController@getCustomers: Memulai pencarian customers', ['search_term' => $search]);

        $query = "
            SELECT id, name, phone, address 
            FROM customers 
            WHERE 1=1
        ";
        $params = [];

        if ($search) {
            Log::debug('TransactionController@getCustomers: Menerapkan filter search');
            $query .= " AND (name LIKE ? OR phone LIKE ?)";
            $searchTerm = "%{$search}%";
            $params = [$searchTerm, $searchTerm];
        }

        $query .= " ORDER BY name ASC LIMIT 20";

        Log::debug('TransactionController@getCustomers: Menjalankan query customers');
        $customers = DB::select($query, $params);
        Log::debug('TransactionController@getCustomers: Pencarian customers selesai', [
            'results_count' => count($customers),
            'search_term' => $search
        ]);

        return response()->json([
            'success' => true,
            'data' => $customers
        ]);
    }

    /**
     * Get services for transaction
     */
    public function getServices()
    {
        Log::debug('TransactionController@getServices: Memulai proses mengambil services aktif');

        // Get active services
        Log::debug('TransactionController@getServices: Mengambil services aktif');
        $services = DB::select("
            SELECT * FROM services WHERE active = 1
        ");

        Log::debug('TransactionController@getServices: Services aktif ditemukan', ['services_count' => count($services)]);

        // Get service items for each service
        foreach ($services as &$service) {
            $service->items = DB::select("
                SELECT * FROM service_items 
                WHERE service_id = ? AND active = 1
                ORDER BY name ASC
            ", [$service->id]);
            Log::debug('TransactionController@getServices: Items untuk service', [
                'service_id' => $service->id,
                'service_name' => $service->name,
                'items_count' => count($service->items)
            ]);
        }

        Log::debug('TransactionController@getServices: Semua services dan items berhasil diambil');
        return response()->json([
            'success' => true,
            'data' => $services
        ]);
    }

    /**
     * Get today's transactions summary
     */
    public function getTodaySummary()
    {
        Log::debug('TransactionController@getTodaySummary: Memulai proses mengambil summary hari ini');

        $today = Carbon::today()->format('Y-m-d');
        Log::debug('TransactionController@getTodaySummary: Filter tanggal', ['today' => $today]);

        $query = "
            SELECT 
                COUNT(*) as total_transactions,
                SUM(CASE WHEN status IN ('new', 'washing', 'ironing') THEN 1 ELSE 0 END) as processing_count,
                COALESCE(SUM(total_amount), 0) as total_income
            FROM transactions 
            WHERE DATE(created_at) = ?
        ";

        Log::debug('TransactionController@getTodaySummary: Menjalankan query summary');
        $summary = DB::selectOne($query, [$today]);
        Log::debug('TransactionController@getTodaySummary: Summary hari ini', [
            'total_transactions' => $summary->total_transactions,
            'processing_count' => $summary->processing_count,
            'total_income' => $summary->total_income
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'total_transactions' => $summary->total_transactions,
                'processing_count' => $summary->processing_count,
                'total_income' => $summary->total_income
            ]
        ]);
    }

    /**
     * Get recent transactions
     */
    public function getRecentTransactions()
    {
        Log::debug('TransactionController@getRecentTransactions: Memulai proses mengambil recent transactions');

        $query = "
            SELECT 
                t.*,
                c.name as customer_name,
                c.phone as customer_phone,
                s.name as service_name
            FROM transactions t
            LEFT JOIN customers c ON t.customer_id = c.id
            LEFT JOIN services s ON t.service_id = s.id
            ORDER BY t.created_at DESC
            LIMIT 10
        ";

        Log::debug('TransactionController@getRecentTransactions: Menjalankan query recent transactions');
        $startTime = microtime(true);
        $transactions = DB::select($query);
        $executionTime = round((microtime(true) - $startTime) * 1000, 2);
        Log::debug('TransactionController@getRecentTransactions: Recent transactions berhasil diambil', [
            'transactions_count' => count($transactions),
            'execution_time_ms' => $executionTime
        ]);

        return response()->json([
            'success' => true,
            'data' => $transactions
        ]);
    }

    /**
     * Print receipt
     */
    public function printReceipt($id)
    {
        Log::debug('TransactionController@printReceipt: Memulai proses print receipt', ['transaction_id' => $id]);

        // Get transaction dengan semua data yang diperlukan untuk receipt
        $query = "
            SELECT 
                t.*,
                c.name as customer_name,
                c.phone as customer_phone,
                c.address as customer_address,
                s.name as service_name,
                s.description as service_description
            FROM transactions t
            LEFT JOIN customers c ON t.customer_id = c.id
            LEFT JOIN services s ON t.service_id = s.id
            WHERE t.id = ?
        ";

        Log::debug('TransactionController@printReceipt: Menjalankan query receipt data');
        $transaction = DB::selectOne($query, [$id]);

        if (!$transaction) {
            Log::warning('TransactionController@printReceipt: Transaction tidak ditemukan', ['transaction_id' => $id]);
            return response()->json([
                'success' => false,
                'message' => 'Transaksi tidak ditemukan'
            ], 404);
        }

        Log::debug('TransactionController@printReceipt: Transaction ditemukan untuk receipt', [
            'transaction_number' => $transaction->transaction_number,
            'customer_name' => $transaction->customer_name
        ]);

        // Get transaction items untuk receipt
        $itemsQuery = "
            SELECT 
                ti.item_name,
                ti.quantity,
                ti.unit_price,
                ti.subtotal
            FROM transaction_items ti
            WHERE ti.transaction_id = ?
            ORDER BY ti.id ASC
        ";

        $items = DB::select($itemsQuery, [$id]);
        $transaction->items = $items;

        Log::debug('TransactionController@printReceipt: Receipt data berhasil disiapkan', [
            'transaction_id' => $id,
            'items_count' => count($items),
            'total_amount' => $transaction->total_amount
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'transaction' => $transaction,
                'print_data' => [
                    'store_name' => 'LaundryKu',
                    'store_address' => 'Jl. Contoh No. 123',
                    'store_phone' => '(021) 123-4567',
                    'printed_at' => now()->format('d/m/Y H:i:s')
                ]
            ]
        ]);
    }

    /**
     * Get transaction statistics for dashboard
     */
    public function getTransactionStats()
    {
        Log::debug('TransactionController@getTransactionStats: Memulai proses mengambil statistik transactions');

        $query = "
            SELECT 
                COUNT(*) as total_transactions,
                SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) as today_transactions,
                SUM(CASE WHEN status = 'new' THEN 1 ELSE 0 END) as new_transactions,
                SUM(CASE WHEN status IN ('washing', 'ironing') THEN 1 ELSE 0 END) as processing_transactions,
                SUM(CASE WHEN status = 'ready' THEN 1 ELSE 0 END) as ready_transactions,
                COALESCE(SUM(total_amount), 0) as total_revenue,
                COALESCE(SUM(CASE WHEN DATE(created_at) = CURDATE() THEN total_amount ELSE 0 END), 0) as today_revenue
            FROM transactions
        ";

        Log::debug('TransactionController@getTransactionStats: Menjalankan query statistik');
        $startTime = microtime(true);
        $stats = DB::selectOne($query);
        $executionTime = round((microtime(true) - $startTime) * 1000, 2);
        Log::debug('TransactionController@getTransactionStats: Statistik berhasil diambil', [
            'total_transactions' => $stats->total_transactions,
            'today_transactions' => $stats->today_transactions,
            'total_revenue' => $stats->total_revenue,
            'execution_time_ms' => $executionTime
        ]);

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Delete transaction
     */
    public function destroy($id)
    {
        Log::debug('TransactionController@destroy: Memulai proses hapus transaction', ['transaction_id' => $id]);

        DB::beginTransaction();
        Log::debug('TransactionController@destroy: Memulai database transaction');

        try {
            // Delete transaction items first
            Log::debug('TransactionController@destroy: Menghapus transaction items');
            $itemsDeleted = DB::delete("DELETE FROM transaction_items WHERE transaction_id = ?", [$id]);
            Log::debug('TransactionController@destroy: Transaction items dihapus', ['items_deleted' => $itemsDeleted]);

            // Delete transaction
            Log::debug('TransactionController@destroy: Menghapus transaction');
            $transactionDeleted = DB::delete("DELETE FROM transactions WHERE id = ?", [$id]);
            Log::debug('TransactionController@destroy: Transaction dihapus', ['transaction_deleted' => $transactionDeleted]);

            DB::commit();
            Log::debug('TransactionController@destroy: Database transaction committed');

            Log::debug('TransactionController@destroy: Transaction berhasil dihapus', ['transaction_id' => $id]);
            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil dihapus'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('TransactionController@destroy: Gagal menghapus transaction', [
                'transaction_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus transaksi: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getCategories()
    {
        Log::debug('TransactionController@getCategories: Memulai proses mengambil categories');

        try {
            $query = "
                SELECT 
                    id,
                    name,
                    icon,
                    sort_order,
                    active,
                    created_at,
                    updated_at
                FROM service_categories 
                WHERE active = 1
                ORDER BY sort_order ASC, name ASC
            ";

            Log::debug('TransactionController@getCategories: Menjalankan query categories');
            $categories = DB::select($query);
            Log::debug('TransactionController@getCategories: Categories berhasil diambil', [
                'categories_count' => count($categories)
            ]);

            return response()->json([
                'success' => true,
                'data' => $categories
            ]);
        } catch (\Exception $e) {
            Log::error('TransactionController@getCategories: Gagal memuat kategori', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat kategori: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get items for specific category
     */
    public function getCategoryItems($categoryId)
    {
        Log::debug('TransactionController@getCategoryItems: Memulai proses mengambil category items', ['category_id' => $categoryId]);

        try {
            // Validasi category exists
            Log::debug('TransactionController@getCategoryItems: Memeriksa keberadaan category');
            $category = DB::selectOne("SELECT id, name FROM service_categories WHERE id = ? AND active = 1", [$categoryId]);

            if (!$category) {
                Log::warning('TransactionController@getCategoryItems: Category tidak ditemukan', ['category_id' => $categoryId]);
                return response()->json([
                    'success' => false,
                    'message' => 'Kategori tidak ditemukan'
                ], 404);
            }

            Log::debug('TransactionController@getCategoryItems: Category ditemukan', [
                'category_name' => $category->name
            ]);

            // Get items for this category
            $query = "
                SELECT 
                    si.id,
                    si.name,
                    si.description,
                    si.price,
                    si.unit,
                    si.category_id,
                    si.service_id,
                    si.estimation_time,
                    si.active,
                    si.created_at,
                    si.updated_at,
                    s.name as service_name,
                    s.description as service_description,
                    s.type as service_type
                FROM service_items si
                LEFT JOIN services s ON si.service_id = s.id
                WHERE si.category_id = ? 
                AND si.active = 1
                AND s.active = 1
                ORDER BY si.name ASC
            ";

            Log::debug('TransactionController@getCategoryItems: Menjalankan query category items');
            $items = DB::select($query, [$categoryId]);
            Log::debug('TransactionController@getCategoryItems: Category items berhasil diambil', [
                'category_id' => $categoryId,
                'items_count' => count($items)
            ]);

            return response()->json([
                'success' => true,
                'data' => $items,
                'category' => $category
            ]);
        } catch (\Exception $e) {
            Log::error('TransactionController@getCategoryItems: Gagal memuat items kategori', [
                'category_id' => $categoryId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat items kategori: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get services by type (kiloan/satuan)
     */
    public function getServicesByType(Request $request)
    {
        $type = $request->get('type', 'kiloan');
        Log::debug('TransactionController@getServicesByType: Memulai proses mengambil services by type', ['type' => $type]);

        try {
            $query = "
                SELECT 
                    s.*
                FROM services s
                WHERE s.active = 1
                AND s.type = ?
                ORDER BY s.name ASC
            ";

            Log::debug('TransactionController@getServicesByType: Menjalankan query services by type');
            $services = DB::select($query, [$type]);
            Log::debug('TransactionController@getServicesByType: Services by type ditemukan', [
                'type' => $type,
                'services_count' => count($services)
            ]);

            // Get service items for each service
            foreach ($services as &$service) {
                $service->items = DB::select("
                    SELECT * FROM service_items 
                    WHERE service_id = ? AND active = 1
                    ORDER BY name ASC
                ", [$service->id]);
                Log::debug('TransactionController@getServicesByType: Items untuk service', [
                    'service_id' => $service->id,
                    'service_name' => $service->name,
                    'items_count' => count($service->items)
                ]);
            }

            Log::debug('TransactionController@getServicesByType: Semua services dan items berhasil diambil');
            return response()->json([
                'success' => true,
                'data' => $services
            ]);
        } catch (\Exception $e) {
            Log::error('TransactionController@getServicesByType: Gagal memuat layanan', [
                'type' => $type,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat layanan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store new transaction - PERBAIKAN untuk handle kiloan/satuan
     */
    public function store(Request $request)
    {
        Log::debug('TransactionController@store: Memulai proses membuat transaction baru');
        Log::debug('TransactionController@store: Data request received', [
            'customer_id' => $request->customer_id,
            'order_type' => $request->order_type,
            'items_count' => count($request->items ?? []),
            'total_amount' => $request->total_amount,
            'payment_type' => $request->payment_type
        ]);

        // Validasi dasar
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'order_type' => 'required|in:kiloan,satuan',
            'items' => 'required|array|min:1',
            'items.*.service_item_id' => 'required|exists:service_items,id',
            'items.*.quantity' => 'required|numeric|min:0.1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
            'weight' => 'nullable|numeric|min:0.1', // untuk kiloan
            'payment_type' => 'required|in:now,later',
            'payment_method' => 'nullable|in:cash,transfer,qris',
            'notes' => 'nullable|string|max:500'
        ]);

        Log::debug('TransactionController@store: Validasi berhasil', [
            'customer_id' => $validated['customer_id'],
            'order_type' => $validated['order_type'],
            'items_count' => count($validated['items']),
            'total_amount' => $validated['total_amount']
        ]);

        // Jika service_id tidak dikirim (khusus untuk satuan), set default
        $serviceId = $request->input('service_id');

        if (!$serviceId && $validated['order_type'] === 'satuan') {
            Log::debug('TransactionController@store: Mencari service default untuk satuan');
            // Cari service default untuk satuan
            $defaultService = DB::selectOne("
            SELECT id FROM services 
            WHERE type = 'satuan' AND active = 1 
            ORDER BY id ASC 
            LIMIT 1
        ");

            if ($defaultService) {
                $serviceId = $defaultService->id;
                Log::debug('TransactionController@store: Service default ditemukan', ['service_id' => $serviceId]);
            } else {
                Log::warning('TransactionController@store: Tidak ada layanan satuan yang tersedia');
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada layanan satuan yang tersedia'
                ], 400);
            }
        }

        // Validasi service_id setelah set default
        if (!$serviceId) {
            Log::warning('TransactionController@store: Service ID diperlukan');
            return response()->json([
                'success' => false,
                'message' => 'Service ID diperlukan'
            ], 400);
        }

        // Verifikasi service exists
        Log::debug('TransactionController@store: Memverifikasi service exists', ['service_id' => $serviceId]);
        $service = DB::selectOne("SELECT id, type FROM services WHERE id = ? AND active = 1", [$serviceId]);
        if (!$service) {
            Log::warning('TransactionController@store: Service tidak ditemukan atau tidak aktif', ['service_id' => $serviceId]);
            return response()->json([
                'success' => false,
                'message' => 'Layanan tidak ditemukan atau tidak aktif'
            ], 400);
        }

        // Verifikasi konsistensi order_type dengan service type
        if ($service->type !== $validated['order_type']) {
            Log::warning('TransactionController@store: Tipe layanan tidak sesuai dengan tipe order', [
                'service_type' => $service->type,
                'order_type' => $validated['order_type']
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Tipe layanan tidak sesuai dengan tipe order'
            ], 400);
        }

        Log::debug('TransactionController@store: Service validasi berhasil', [
            'service_id' => $serviceId,
            'service_type' => $service->type
        ]);

        // Tentukan payment status berdasarkan payment type
        $paymentStatus = 'pending';
        if ($validated['payment_type'] === 'now') {
            $paymentStatus = 'paid';
            Log::debug('TransactionController@store: Payment status di-set ke paid (bayar sekarang)');
        } else {
            Log::debug('TransactionController@store: Payment status di-set ke pending (bayar nanti)');
        }

        // Validasi payment method untuk bayar sekarang
        if ($validated['payment_type'] === 'now' && empty($validated['payment_method'])) {
            Log::warning('TransactionController@store: Metode pembayaran diperlukan untuk bayar sekarang');
            return response()->json([
                'success' => false,
                'message' => 'Metode pembayaran harus dipilih untuk pembayaran sekarang'
            ], 400);
        }

        DB::beginTransaction();
        Log::debug('TransactionController@store: Memulai database transaction');

        try {
            // Generate transaction number
            $today = now()->format('Ymd');
            $todayCount = DB::selectOne("
            SELECT COUNT(*) as count 
            FROM transactions 
            WHERE DATE(created_at) = CURDATE()
        ")->count + 1;

            $transactionNumber = 'TRX-' . $today . '-' . str_pad($todayCount, 4, '0', STR_PAD_LEFT);
            Log::debug('TransactionController@store: Generated transaction number', [
                'transaction_number' => $transactionNumber,
                'today_count' => $todayCount
            ]);

            // Create transaction
            Log::debug('TransactionController@store: Menyimpan transaction ke database', [
                'payment_status' => $paymentStatus
            ]);
            DB::insert("
            INSERT INTO transactions (
                transaction_number, customer_id, service_id, order_type, total_amount, 
                weight, payment_type, payment_method, notes,
                status, payment_status, order_date, estimated_completion,
                created_at, updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'new', ?, NOW(), DATE_ADD(NOW(), INTERVAL 2 DAY), NOW(), NOW())
        ", [
                $transactionNumber,
                $validated['customer_id'],
                $serviceId, // Gunakan serviceId yang sudah diproses
                $validated['order_type'],
                $validated['total_amount'],
                $validated['weight'] ?? null,
                $validated['payment_type'],
                $validated['payment_method'] ?? null,
                $validated['notes'] ?? null,
                $paymentStatus // Gunakan payment status yang sudah ditentukan
            ]);

            // Get the last inserted transaction ID
            $transactionId = DB::getPdo()->lastInsertId();
            Log::debug('TransactionController@store: Transaction berhasil dibuat', [
                'transaction_id' => $transactionId,
                'transaction_number' => $transactionNumber,
                'payment_status' => $paymentStatus
            ]);

            // Create transaction items
            $totalItems = 0;
            $totalItemsValue = 0;
            foreach ($validated['items'] as $item) {
                // Get service item details from database
                $serviceItem = DB::selectOne("
                SELECT name, unit FROM service_items WHERE id = ?
            ", [$item['service_item_id']]);

                $itemName = $serviceItem ? $serviceItem->name : 'Unknown Item';
                $unit = $serviceItem ? $serviceItem->unit : ($validated['order_type'] === 'kiloan' ? 'kg' : 'pcs');
                $subtotal = $item['quantity'] * $item['unit_price'];

                Log::debug('TransactionController@store: Menyimpan transaction item', [
                    'item_name' => $itemName,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $subtotal
                ]);

                DB::insert("
                INSERT INTO transaction_items (
                    transaction_id, service_item_id, item_name, quantity, unit_price, subtotal, unit,
                    created_at, updated_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
            ", [
                    $transactionId,
                    $item['service_item_id'],
                    $itemName,
                    $item['quantity'],
                    $item['unit_price'],
                    $subtotal,
                    $unit
                ]);

                $totalItems++;
                $totalItemsValue += $subtotal;
            }

            Log::debug('TransactionController@store: Semua transaction items berhasil disimpan', [
                'total_items' => $totalItems,
                'total_items_value' => $totalItemsValue,
                'transaction_total_amount' => $validated['total_amount']
            ]);

            DB::commit();
            Log::debug('TransactionController@store: Database transaction committed');

            if ($request->wantsJson()) {
                Log::debug('TransactionController@store: Mengembalikan response JSON success');
                return response()->json([
                    'success' => true,
                    'message' => 'Transaksi berhasil dibuat!',
                    'data' => [
                        'transaction_number' => $transactionNumber,
                        'transaction_id' => $transactionId,
                        'payment_status' => $paymentStatus
                    ]
                ]);
            }

            Log::debug('TransactionController@store: Redirect ke dashboard dengan success message');
            return redirect()->route('dashboard')->with('success', 'Transaksi berhasil dibuat! No: ' . $transactionNumber);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('TransactionController@store: Gagal membuat transaction', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => [
                    'customer_id' => $request->customer_id,
                    'order_type' => $request->order_type,
                    'items_count' => count($request->items ?? [])
                ]
            ]);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal membuat transaksi: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Gagal membuat transaksi: ' . $e->getMessage())->withInput();
        }
    }

    // Tambahkan method ini ke TransactionController

    /**
     * Get all satuan items (untuk modal satuan tanpa kategori)
     */
    public function getSatuanItems(Request $request)
    {
        Log::debug('TransactionController@getSatuanItems: Memulai proses mengambil semua items satuan');

        try {
            // Query yang disesuaikan dengan struktur database baru
            $query = "
        SELECT 
            si.id,
            si.name,
            si.description,
            si.price,
            si.unit,
            si.service_id,
            si.estimation_time,
            si.active,
            si.created_at,
            si.updated_at,
            s.name as service_name,
            s.description as service_description,
            s.type as service_type,
            s.icon as service_icon,
            s.color as service_color
        FROM service_items si
        LEFT JOIN services s ON si.service_id = s.id
        WHERE si.active = 1
        AND s.active = 1
        AND s.type = 'satuan'
        ORDER BY si.name ASC
        ";

            Log::debug('TransactionController@getSatuanItems: Menjalankan query semua items satuan');
            $items = DB::select($query);

            // Format data untuk response
            $formattedItems = array_map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'description' => $item->description,
                    'price' => (float) $item->price,
                    'unit' => $item->unit,
                    'service_id' => $item->service_id,
                    'estimation_time' => $item->estimation_time,
                    'active' => (bool) $item->active,
                    'service_name' => $item->service_name,
                    'service_description' => $item->service_description,
                    'service_type' => $item->service_type,
                    'service_icon' => $item->service_icon,
                    'service_color' => $item->service_color,
                    'created_at' => $item->created_at,
                    'updated_at' => $item->updated_at
                ];
            }, $items);

            Log::debug('TransactionController@getSatuanItems: Semua items satuan berhasil diambil', [
                'items_count' => count($formattedItems)
            ]);

            return response()->json([
                'success' => true,
                'data' => $formattedItems
            ]);
        } catch (\Exception $e) {
            Log::error('TransactionController@getSatuanItems: Gagal memuat items satuan', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat items satuan: ' . $e->getMessage()
            ], 500);
        }
    }
}