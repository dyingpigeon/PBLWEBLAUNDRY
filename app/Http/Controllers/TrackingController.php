<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class TrackingController extends Controller
{
    public function index(Request $request)
    {
        Log::debug('TrackingController@index: Memulai proses mengambil data tracking');
        Log::debug('TrackingController@index: Request parameters', [
            'page' => $request->get('page', 1),
            'status' => $request->status,
            'search' => $request->search
        ]);

        $perPage = 10;
        $page = $request->get('page', 1);
        $offset = ($page - 1) * $perPage;

        // Build base query dengan JOIN
        $query = "
        SELECT 
            t.*,
            c.name as customer_name,
            c.phone as customer_phone,
            s.name as service_name,
            DATE_FORMAT(t.order_date, '%d/%m/%Y %H:%i') as order_date_formatted,
            DATE_FORMAT(t.created_at, '%d/%m/%Y %H:%i') as created_at_formatted,
            DATE_FORMAT(t.estimated_completion, '%d/%m/%Y %H:%i') as estimated_completion_formatted,
            (SELECT SUM(quantity) FROM transaction_items WHERE transaction_id = t.id) as total_items
        FROM transactions t
        LEFT JOIN customers c ON t.customer_id = c.id
        LEFT JOIN services s ON t.service_id = s.id
        WHERE 1=1
    ";

        $params = [];
        $countParams = [];

        // Filter by status
        if ($request->status && $request->status !== 'all') {
            Log::debug('TrackingController@index: Menerapkan filter status', ['status' => $request->status]);
            $query .= " AND t.status = ?";
            $params[] = $request->status;
            $countParams[] = $request->status;
        }

        // Search functionality
        if ($request->search) {
            Log::debug('TrackingController@index: Menerapkan filter search', ['search_term' => $request->search]);
            $query .= " AND (t.transaction_number LIKE ? OR c.name LIKE ? OR c.phone LIKE ?)";
            $searchTerm = "%{$request->search}%";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
            $countParams = array_merge($countParams, [$searchTerm, $searchTerm, $searchTerm]);
        }

        // Count total records
        Log::debug('TrackingController@index: Menghitung total records');
        $countQuery = "SELECT COUNT(*) as total FROM transactions t 
                   LEFT JOIN customers c ON t.customer_id = c.id 
                   LEFT JOIN services s ON t.service_id = s.id 
                   WHERE 1=1";

        if ($request->status && $request->status !== 'all') {
            $countQuery .= " AND t.status = ?";
        }
        if ($request->search) {
            $countQuery .= " AND (t.transaction_number LIKE ? OR c.name LIKE ? OR c.phone LIKE ?)";
        }

        $total = DB::selectOne($countQuery, $countParams)->total;
        $totalPages = ceil($total / $perPage);
        Log::debug('TrackingController@index: Hasil perhitungan pagination', [
            'total_records' => $total,
            'total_pages' => $totalPages,
            'current_page' => $page
        ]);

        // Order dan pagination
        $query .= " ORDER BY t.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;

        // Eksekusi query
        Log::debug('TrackingController@index: Menjalankan query utama dengan pagination', [
            'per_page' => $perPage,
            'offset' => $offset,
            'params_count' => count($params)
        ]);
        $startTime = microtime(true);
        $transactions = DB::select($query, $params);
        $executionTime = round((microtime(true) - $startTime) * 1000, 2);
        Log::debug('TrackingController@index: Query utama selesai', [
            'transactions_count' => count($transactions),
            'execution_time_ms' => $executionTime
        ]);

        // Get statistics
        Log::debug('TrackingController@index: Mengambil statistik transactions');
        $stats = [
            'total' => $total,
            'new' => DB::selectOne("SELECT COUNT(*) as count FROM transactions WHERE status = 'new'")->count,
            'process' => DB::selectOne("SELECT COUNT(*) as count FROM transactions WHERE status = 'process'")->count,
            'ready' => DB::selectOne("SELECT COUNT(*) as count FROM transactions WHERE status = 'ready'")->count,
            'done' => DB::selectOne("SELECT COUNT(*) as count FROM transactions WHERE status = 'done'")->count,
            'cancelled' => DB::selectOne("SELECT COUNT(*) as count FROM transactions WHERE status = 'cancelled'")->count,
            'processing' => DB::selectOne("SELECT COUNT(*) as count FROM transactions WHERE status IN ('new', 'process')")->count,
        ];
        Log::debug('TrackingController@index: Statistik berhasil diambil', $stats);

        // Build pagination data manually
        $pagination = [
            'current_page' => $page,
            'last_page' => $totalPages,
            'per_page' => $perPage,
            'total' => $total,
            'from' => $offset + 1,
            'to' => min($offset + $perPage, $total),
            'first_page_url' => route('tracking.index', array_merge($request->query(), ['page' => 1])),
            'last_page_url' => route('tracking.index', array_merge($request->query(), ['page' => $totalPages])),
            'next_page_url' => $page < $totalPages ? route('tracking.index', array_merge($request->query(), ['page' => $page + 1])) : null,
            'prev_page_url' => $page > 1 ? route('tracking.index', array_merge($request->query(), ['page' => $page - 1])) : null,
        ];

        Log::debug('TrackingController@index: Proses selesai, mengembalikan view dengan data', [
            'transactions_count' => count($transactions),
            'pagination_info' => [
                'current_page' => $pagination['current_page'],
                'last_page' => $pagination['last_page'],
                'total_records' => $pagination['total']
            ]
        ]);

        return view('tracking.index', compact('transactions', 'stats', 'pagination'));
    }

    public function search(Request $request)
    {
        Log::debug('TrackingController@search: Memulai proses search transactions');
        $search = $request->get('search');
        Log::debug('TrackingController@search: Search term', ['search_term' => $search]);

        $query = "
            SELECT 
                t.*,
                c.name as customer_name,
                c.phone as customer_phone,
                s.name as service_name
            FROM transactions t
            LEFT JOIN customers c ON t.customer_id = c.id
            LEFT JOIN services s ON t.service_id = s.id
            WHERE t.transaction_number LIKE ? OR c.name LIKE ? OR c.phone LIKE ?
            ORDER BY t.created_at DESC
            LIMIT 10
        ";

        $searchTerm = "%{$search}%";
        Log::debug('TrackingController@search: Menjalankan query search');
        $transactions = DB::select($query, [$searchTerm, $searchTerm, $searchTerm]);
        Log::debug('TrackingController@search: Search selesai', [
            'results_count' => count($transactions),
            'search_term' => $search
        ]);

        return view('tracking.index', compact('transactions'));
    }

    public function filterByStatus(Request $request)
    {
        $status = $request->get('status');
        Log::debug('TrackingController@filterByStatus: Memfilter transactions by status', ['status' => $status]);

        $query = "
            SELECT 
                t.*,
                c.name as customer_name,
                c.phone as customer_phone,
                s.name as service_name
            FROM transactions t
            LEFT JOIN customers c ON t.customer_id = c.id
            LEFT JOIN services s ON t.service_id = s.id
            WHERE t.status = ?
            ORDER BY t.created_at DESC
            LIMIT 10
        ";

        Log::debug('TrackingController@filterByStatus: Menjalankan query filter');
        $transactions = DB::select($query, [$status]);
        Log::debug('TrackingController@filterByStatus: Filter selesai', [
            'status' => $status,
            'results_count' => count($transactions)
        ]);

        return view('tracking.index', compact('transactions'));
    }

    public function show($id)
    {
        Log::debug('TrackingController@show: Memulai proses mengambil detail transaction', ['transaction_id' => $id]);

        // Get transaction dengan semua relationships
        $query = "
            SELECT 
                t.*,
                c.name as customer_name,
                c.phone as customer_phone,
                c.email as customer_email,
                c.address as customer_address,
                s.name as service_name,
                s.description as service_description
            FROM transactions t
            LEFT JOIN customers c ON t.customer_id = c.id
            LEFT JOIN services s ON t.service_id = s.id
            WHERE t.id = ?
        ";

        Log::debug('TrackingController@show: Menjalankan query detail transaction');
        $transaction = DB::selectOne($query, [$id]);

        if (!$transaction) {
            Log::warning('TrackingController@show: Transaction tidak ditemukan', ['transaction_id' => $id]);
            return response()->json([
                'success' => false,
                'message' => 'Transaksi tidak ditemukan'
            ], 404);
        }

        Log::debug('TrackingController@show: Transaction ditemukan', [
            'transaction_number' => $transaction->transaction_number,
            'customer_name' => $transaction->customer_name,
            'status' => $transaction->status
        ]);

        // Get transaction items
        Log::debug('TrackingController@show: Mengambil transaction items');
        $itemsQuery = "
            SELECT 
                ti.*,
                si.name as service_item_name,
                si.price as service_item_price
            FROM transaction_items ti
            LEFT JOIN service_items si ON ti.service_item_id = si.id
            WHERE ti.transaction_id = ?
        ";

        $items = DB::select($itemsQuery, [$id]);
        $transaction->items = $items;

        Log::debug('TrackingController@show: Detail transaction berhasil diambil', [
            'transaction_id' => $id,
            'items_count' => count($items),
            'total_amount' => $transaction->total_amount
        ]);

        return response()->json([
            'success' => true,
            'data' => $transaction
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        Log::debug('TrackingController@updateStatus: Memulai update status transaction', [
            'transaction_id' => $id,
            'new_status' => $request->status,
            'user_id' => auth()->id()
        ]);

        // Validasi status
        $request->validate([
            'status' => 'required|in:new,process,ready,done,cancelled'
        ]);

        // Check if transaction exists
        Log::debug('TrackingController@updateStatus: Memeriksa keberadaan transaction');
        $transaction = DB::selectOne("SELECT * FROM transactions WHERE id = ?", [$id]);

        if (!$transaction) {
            Log::warning('TrackingController@updateStatus: Transaction tidak ditemukan', ['transaction_id' => $id]);
            return response()->json([
                'success' => false,
                'message' => 'Transaksi tidak ditemukan'
            ], 404);
        }

        Log::debug('TrackingController@updateStatus: Transaction ditemukan', [
            'current_status' => $transaction->status,
            'transaction_number' => $transaction->transaction_number
        ]);

        // ✅ PERBAIKAN: Build update query berdasarkan field yang ADA di database
        $updateFields = ["status = ?"];
        $params = [$request->status];

        // ✅ PERBAIKAN: Update payment_status jika status done
        if ($request->status === 'done') {
            $updateFields[] = "payment_status = ?";
            $params[] = 'paid'; // Auto mark as paid when done
        }

        // ✅ PERBAIKAN: Tambahkan cancellation_reason jika status cancelled
        if ($request->status === 'cancelled') {
            $updateFields[] = "cancellation_reason = ?";
            $params[] = $request->cancellation_reason ?? 'Dibatalkan via tracking';
        }

        // ✅ PERBAIKAN: Update estimated_completion berdasarkan status
        if ($request->status === 'process') {
            $updateFields[] = "estimated_completion = ?";
            $params[] = now()->addHours(24)->format('Y-m-d H:i:s'); // Estimasi 24 jam
        }

        // Build final query
        $updateQuery = "UPDATE transactions SET " . implode(", ", $updateFields) . " WHERE id = ?";
        $params[] = $id;

        Log::debug('TrackingController@updateStatus: Menjalankan update query', [
            'update_query' => $updateQuery,
            'params' => $params
        ]);

        try {
            // Execute update
            $affectedRows = DB::update($updateQuery, $params);
            Log::debug('TrackingController@updateStatus: Update berhasil', ['affected_rows' => $affectedRows]);

            // Get updated transaction
            $updatedTransaction = DB::selectOne("
            SELECT t.*, c.name as customer_name, s.name as service_name
            FROM transactions t
            LEFT JOIN customers c ON t.customer_id = c.id
            LEFT JOIN services s ON t.service_id = s.id
            WHERE t.id = ?
        ", [$id]);

            Log::debug('TrackingController@updateStatus: Status berhasil diupdate', [
                'transaction_id' => $id,
                'old_status' => $transaction->status,
                'new_status' => $updatedTransaction->status
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Status berhasil diupdate',
                'data' => $updatedTransaction
            ]);

        } catch (\Exception $e) {
            Log::error('TrackingController@updateStatus: Error updating transaction', [
                'transaction_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengupdate status: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updatePayment(Request $request, $id)
    {
        Log::debug('TrackingController@updatePayment: Memulai update payment transaction', [
            'transaction_id' => $id,
            'payment_status' => $request->payment_status,
            'payment_method' => $request->payment_method,
            'paid_amount' => $request->paid_amount
        ]);

        $request->validate([
            'payment_status' => 'required|in:pending,paid,partial',
            'payment_method' => 'required|in:cash,transfer,qris',
            'paid_amount' => 'required|numeric|min:0'
        ]);

        // Check if transaction exists
        Log::debug('TrackingController@updatePayment: Memeriksa keberadaan transaction');
        $transaction = DB::selectOne("SELECT total_amount FROM transactions WHERE id = ?", [$id]);

        if (!$transaction) {
            Log::warning('TrackingController@updatePayment: Transaction tidak ditemukan', ['transaction_id' => $id]);
            return response()->json([
                'success' => false,
                'message' => 'Transaksi tidak ditemukan'
            ], 404);
        }

        Log::debug('TrackingController@updatePayment: Transaction ditemukan', [
            'transaction_id' => $id,
            'total_amount' => $transaction->total_amount,
            'paid_amount' => $request->paid_amount
        ]);

        $changeAmount = max(0, $request->paid_amount - $transaction->total_amount);
        Log::debug('TrackingController@updatePayment: Menghitung change amount', [
            'change_amount' => $changeAmount
        ]);

        try {
            // Update payment data
            $updateQuery = "
            UPDATE transactions 
            SET payment_status = ?, payment_method = ?, paid_amount = ?, change_amount = ?
            WHERE id = ?
        ";

            Log::debug('TrackingController@updatePayment: Menjalankan update payment');
            $affectedRows = DB::update($updateQuery, [
                $request->payment_status,
                $request->payment_method,
                $request->paid_amount,
                $changeAmount,
                $id
            ]);

            Log::debug('TrackingController@updatePayment: Update payment berhasil', ['affected_rows' => $affectedRows]);

            // Get updated transaction
            $updatedTransaction = DB::selectOne("
            SELECT t.*, c.name as customer_name, s.name as service_name
            FROM transactions t
            LEFT JOIN customers c ON t.customer_id = c.id
            LEFT JOIN services s ON t.service_id = s.id
            WHERE t.id = ?
        ", [$id]);

            Log::debug('TrackingController@updatePayment: Payment berhasil diupdate', [
                'transaction_id' => $id,
                'new_payment_status' => $updatedTransaction->payment_status,
                'paid_amount' => $updatedTransaction->paid_amount
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Status pembayaran berhasil diupdate',
                'data' => $updatedTransaction
            ]);

        } catch (\Exception $e) {
            Log::error('TrackingController@updatePayment: Error updating payment', [
                'transaction_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengupdate pembayaran: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getTodayTransactions()
    {
        Log::debug('TrackingController@getTodayTransactions: Memulai proses mengambil transactions hari ini');

        $today = Carbon::today()->format('Y-m-d');
        Log::debug('TrackingController@getTodayTransactions: Filter tanggal', ['today' => $today]);

        $query = "
            SELECT 
                t.*,
                c.name as customer_name,
                c.phone as customer_phone,
                s.name as service_name
            FROM transactions t
            LEFT JOIN customers c ON t.customer_id = c.id
            LEFT JOIN services s ON t.service_id = s.id
            WHERE DATE(t.created_at) = ?
            ORDER BY t.created_at DESC
        ";

        Log::debug('TrackingController@getTodayTransactions: Menjalankan query today transactions');
        $startTime = microtime(true);
        $transactions = DB::select($query, [$today]);
        $executionTime = round((microtime(true) - $startTime) * 1000, 2);
        Log::debug('TrackingController@getTodayTransactions: Query selesai', [
            'transactions_count' => count($transactions),
            'execution_time_ms' => $executionTime
        ]);

        // Calculate summary manually
        $summary = [
            'total' => count($transactions),
            'total_amount' => collect($transactions)->sum('total_amount'),
            'paid_amount' => collect($transactions)->sum('paid_amount'),
            'new_count' => collect($transactions)->where('status', 'new')->count(),
            'process_count' => collect($transactions)->where('status', 'process')->count(),
            'ready_count' => collect($transactions)->where('status', 'ready')->count(),
        ];

        Log::debug('TrackingController@getTodayTransactions: Summary today transactions', $summary);

        return response()->json([
            'success' => true,
            'data' => [
                'transactions' => $transactions,
                'summary' => $summary
            ]
        ]);
    }

    private function getStatusDescription($status)
    {
        // SESUAI MIGRASI BARU
        $descriptions = [
            'new' => 'Pesanan baru dibuat',
            'process' => 'Sedang dalam proses',
            'ready' => 'Pesanan sudah siap diambil',
            'done' => 'Pesanan sudah selesai',
            'cancelled' => 'Pesanan dibatalkan'
        ];

        $description = $descriptions[$status] ?? 'Status diperbarui';
        Log::debug('TrackingController@getStatusDescription: Status description', [
            'status' => $status,
            'description' => $description
        ]);

        return $description;
    }

    public function getStats()
    {
        Log::debug('TrackingController@getStats: Memulai proses mengambil statistik global');

        try {
            // SESUAI MIGRASI BARU - Query langsung
            $totalResult = DB::selectOne("SELECT COUNT(*) as count FROM transactions");
            $todayResult = DB::selectOne("SELECT COUNT(*) as count FROM transactions WHERE DATE(created_at) = CURDATE()");
            $newResult = DB::selectOne("SELECT COUNT(*) as count FROM transactions WHERE status = 'new'");
            $processResult = DB::selectOne("SELECT COUNT(*) as count FROM transactions WHERE status = 'process'");
            $readyResult = DB::selectOne("SELECT COUNT(*) as count FROM transactions WHERE status = 'ready'");
            $doneResult = DB::selectOne("SELECT COUNT(*) as count FROM transactions WHERE status = 'done'");
            $pendingPaymentResult = DB::selectOne("SELECT COUNT(*) as count FROM transactions WHERE payment_status = 'pending'");
            $totalRevenueResult = DB::selectOne("SELECT COALESCE(SUM(total_amount), 0) as total FROM transactions WHERE payment_status = 'paid'");

            $stats = [
                'total' => $totalResult->count,
                'today' => $todayResult->count,
                'new' => $newResult->count,
                'process' => $processResult->count,
                'ready' => $readyResult->count,
                'done' => $doneResult->count,
                'pending_payment' => $pendingPaymentResult->count,
                'total_revenue' => $totalRevenueResult->total,
            ];

            Log::debug('TrackingController@getStats: Statistik global berhasil diambil', $stats);

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('TrackingController@getStats: Error fetching stats', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil statistik'
            ], 500);
        }
    }

    // Additional helper method untuk mendapatkan detail lengkap
    public function getTransactionWithDetails($id)
    {
        Log::debug('TrackingController@getTransactionWithDetails: Memulai proses mengambil detail lengkap transaction', [
            'transaction_id' => $id
        ]);

        $transaction = DB::selectOne("
            SELECT 
                t.*,
                c.name as customer_name,
                c.phone as customer_phone,
                c.email as customer_email,
                c.address as customer_address,
                s.name as service_name,
                s.description as service_description,
                u.name as cancelled_by_name
            FROM transactions t
            LEFT JOIN customers c ON t.customer_id = c.id
            LEFT JOIN services s ON t.service_id = s.id
            LEFT JOIN users u ON t.cancelled_by = u.id
            WHERE t.id = ?
        ", [$id]);

        if (!$transaction) {
            Log::warning('TrackingController@getTransactionWithDetails: Transaction tidak ditemukan', ['transaction_id' => $id]);
            return null;
        }

        Log::debug('TrackingController@getTransactionWithDetails: Transaction ditemukan', [
            'transaction_number' => $transaction->transaction_number,
            'customer_name' => $transaction->customer_name
        ]);

        // Get items dengan detail
        Log::debug('TrackingController@getTransactionWithDetails: Mengambil transaction items');
        $items = DB::select("
            SELECT 
                ti.*,
                si.name as item_name,
                si.description as item_description,
                si.price as item_price
            FROM transaction_items ti
            LEFT JOIN service_items si ON ti.service_item_id = si.id
            WHERE ti.transaction_id = ?
        ", [$id]);

        $transaction->items = $items;

        Log::debug('TrackingController@getTransactionWithDetails: Detail lengkap berhasil diambil', [
            'transaction_id' => $id,
            'items_count' => count($items)
        ]);

        return $transaction;
    }

    // Method untuk mendapatkan statistik processing yang benar
    public function getProcessingStats()
    {
        Log::debug('TrackingController@getProcessingStats: Memulai proses menghitung statistik processing');

        $stats = DB::selectOne("
            SELECT COUNT(*) as count 
            FROM transactions 
            WHERE status IN ('new', 'process')
        ");

        $count = $stats->count;
        Log::debug('TrackingController@getProcessingStats: Statistik processing', ['processing_count' => $count]);

        return $count;
    }
}