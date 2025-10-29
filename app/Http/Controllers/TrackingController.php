<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TrackingController extends Controller
{
    public function index(Request $request)
    {
        // Build base query dengan JOIN
        $query = "
            SELECT 
                t.*,
                c.name as customer_name,
                c.phone as customer_phone,
                s.name as service_name,
                (SELECT SUM(quantity) FROM transaction_items WHERE transaction_id = t.id) as total_items
            FROM transactions t
            LEFT JOIN customers c ON t.customer_id = c.id
            LEFT JOIN services s ON t.service_id = s.id
            WHERE 1=1
        ";
        
        $params = [];

        // Filter by status
        if ($request->status && $request->status !== 'all') {
            $query .= " AND t.status = ?";
            $params[] = $request->status;
        }

        // Search functionality
        if ($request->search) {
            $query .= " AND (t.transaction_number LIKE ? OR c.name LIKE ? OR c.phone LIKE ?)";
            $searchTerm = "%{$request->search}%";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
        }

        // Order dan pagination
        $query .= " ORDER BY t.created_at DESC LIMIT 10";

        // Eksekusi query
        $transactions = DB::select($query, $params);

        // Manual pagination (sederhana)
        $transactions = collect($transactions);

        // Get statistics dengan query langsung
        $stats = [
            'total' => DB::selectOne("SELECT COUNT(*) as total FROM transactions")->total,
            'new' => DB::selectOne("SELECT COUNT(*) as count FROM transactions WHERE status = 'new'")->count,
            'washing' => DB::selectOne("SELECT COUNT(*) as count FROM transactions WHERE status = 'washing'")->count,
            'ironing' => DB::selectOne("SELECT COUNT(*) as count FROM transactions WHERE status = 'ironing'")->count,
            'ready' => DB::selectOne("SELECT COUNT(*) as count FROM transactions WHERE status = 'ready'")->count,
            'picked_up' => DB::selectOne("SELECT COUNT(*) as count FROM transactions WHERE status = 'picked_up'")->count,
            'processing' => DB::selectOne("SELECT COUNT(*) as count FROM transactions WHERE status IN ('new', 'washing', 'ironing')")->count,
        ];

        // Jika request AJAX, return JSON
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'transactions' => $transactions,
                    'stats' => $stats
                ]
            ]);
        }

        // Jika regular request, return full view
        return view('tracking.index', compact('transactions', 'stats'));
    }

    public function search(Request $request)
    {
        $search = $request->get('search');

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
        $transactions = DB::select($query, [$searchTerm, $searchTerm, $searchTerm]);

        return view('tracking.index', compact('transactions'));
    }

    public function filterByStatus(Request $request)
    {
        $status = $request->get('status');

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

        $transactions = DB::select($query, [$status]);

        return view('tracking.index', compact('transactions'));
    }

    public function show($id)
    {
        // Get transaction dengan semua relationships
        $transaction = DB::selectOne("
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
        ", [$id]);

        if (!$transaction) {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi tidak ditemukan'
            ], 404);
        }

        // Get transaction items
        $items = DB::select("
            SELECT 
                ti.*,
                si.name as service_item_name
            FROM transaction_items ti
            LEFT JOIN service_items si ON ti.service_item_id = si.id
            WHERE ti.transaction_id = ?
        ", [$id]);

        $transaction->items = $items;

        return response()->json([
            'success' => true,
            'data' => $transaction
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:new,washing,ironing,ready,picked_up,cancelled'
        ]);

        // Check if transaction exists
        $transaction = DB::selectOne("SELECT * FROM transactions WHERE id = ?", [$id]);
        
        if (!$transaction) {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi tidak ditemukan'
            ], 404);
        }

        // Update timeline
        $timeline = $transaction->timeline ? json_decode($transaction->timeline, true) : [];
        $timeline[] = [
            'status' => $request->status,
            'timestamp' => now()->toISOString(),
            'description' => $this->getStatusDescription($request->status),
            'updated_by' => auth()->id()
        ];

        // Build update query berdasarkan status
        $updateQuery = "UPDATE transactions SET status = ?, timeline = ?";
        $params = [$request->status, json_encode($timeline)];

        // Update timestamps based on status
        switch ($request->status) {
            case 'washing':
                $updateQuery .= ", washing_started_at = ?";
                $params[] = now();
                break;
            case 'ironing':
                $updateQuery .= ", ironing_started_at = ?";
                $params[] = now();
                break;
            case 'ready':
                $updateQuery .= ", completed_at = ?";
                $params[] = now();
                break;
            case 'picked_up':
                $updateQuery .= ", picked_up_at = ?, payment_status = ?";
                $params[] = now();
                $params[] = 'paid'; // Auto mark as paid when picked up
                break;
            case 'cancelled':
                $updateQuery .= ", cancelled_at = ?, cancellation_reason = ?, cancelled_by = ?";
                $params[] = now();
                $params[] = $request->cancellation_reason;
                $params[] = auth()->id();
                break;
        }

        $updateQuery .= " WHERE id = ?";
        $params[] = $id;

        // Execute update
        DB::update($updateQuery, $params);

        // Get updated transaction
        $updatedTransaction = DB::selectOne("
            SELECT t.*, c.name as customer_name, s.name as service_name
            FROM transactions t
            LEFT JOIN customers c ON t.customer_id = c.id
            LEFT JOIN services s ON t.service_id = s.id
            WHERE t.id = ?
        ", [$id]);

        return response()->json([
            'success' => true,
            'message' => 'Status berhasil diupdate',
            'data' => $updatedTransaction
        ]);
    }

    public function updatePayment(Request $request, $id)
    {
        $request->validate([
            'payment_status' => 'required|in:pending,paid,partial,overpaid',
            'payment_method' => 'required|in:cash,transfer,qris',
            'paid_amount' => 'required|numeric|min:0'
        ]);

        // Check if transaction exists
        $transaction = DB::selectOne("SELECT total_amount FROM transactions WHERE id = ?", [$id]);
        
        if (!$transaction) {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi tidak ditemukan'
            ], 404);
        }

        $changeAmount = max(0, $request->paid_amount - $transaction->total_amount);

        // Update payment data
        DB::update("
            UPDATE transactions 
            SET payment_status = ?, payment_method = ?, paid_amount = ?, change_amount = ?
            WHERE id = ?
        ", [
            $request->payment_status,
            $request->payment_method,
            $request->paid_amount,
            $changeAmount,
            $id
        ]);

        // Get updated transaction
        $updatedTransaction = DB::selectOne("
            SELECT t.*, c.name as customer_name, s.name as service_name
            FROM transactions t
            LEFT JOIN customers c ON t.customer_id = c.id
            LEFT JOIN services s ON t.service_id = s.id
            WHERE t.id = ?
        ", [$id]);

        return response()->json([
            'success' => true,
            'message' => 'Status pembayaran berhasil diupdate',
            'data' => $updatedTransaction
        ]);
    }

    public function getTodayTransactions()
    {
        $today = Carbon::today()->format('Y-m-d');

        $transactions = DB::select("
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
        ", [$today]);

        // Calculate summary manually
        $summary = [
            'total' => count($transactions),
            'total_amount' => collect($transactions)->sum('total_amount'),
            'paid_amount' => collect($transactions)->sum('paid_amount'),
            'new_count' => collect($transactions)->where('status', 'new')->count(),
            'ready_count' => collect($transactions)->where('status', 'ready')->count(),
        ];

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
        $descriptions = [
            'new' => 'Pesanan baru dibuat',
            'washing' => 'Sedang dalam proses pencucian',
            'ironing' => 'Sedang dalam proses penyetrikaan',
            'ready' => 'Pesanan sudah siap diambil',
            'picked_up' => 'Pesanan sudah diambil oleh customer',
            'cancelled' => 'Pesanan dibatalkan'
        ];

        return $descriptions[$status] ?? 'Status diperbarui';
    }

    public function getStats()
    {
        $stats = [
            'total' => DB::selectOne("SELECT COUNT(*) as count FROM transactions")->count,
            'today' => DB::selectOne("SELECT COUNT(*) as count FROM transactions WHERE DATE(created_at) = CURDATE()")->count,
            'new' => DB::selectOne("SELECT COUNT(*) as count FROM transactions WHERE status = 'new'")->count,
            'washing' => DB::selectOne("SELECT COUNT(*) as count FROM transactions WHERE status = 'washing'")->count,
            'ironing' => DB::selectOne("SELECT COUNT(*) as count FROM transactions WHERE status = 'ironing'")->count,
            'ready' => DB::selectOne("SELECT COUNT(*) as count FROM transactions WHERE status = 'ready'")->count,
            'pending_payment' => DB::selectOne("SELECT COUNT(*) as count FROM transactions WHERE payment_status = 'pending'")->count,
            'total_revenue' => DB::selectOne("SELECT COALESCE(SUM(total_amount), 0) as total FROM transactions WHERE payment_status = 'paid'")->total,
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    // Additional helper method untuk mendapatkan detail lengkap
    public function getTransactionWithDetails($id)
    {
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
            return null;
        }

        // Get items dengan detail
        $items = DB::select("
            SELECT 
                ti.*,
                si.name as item_name,
                si.description as item_description
            FROM transaction_items ti
            LEFT JOIN service_items si ON ti.service_item_id = si.id
            WHERE ti.transaction_id = ?
        ", [$id]);

        $transaction->items = $items;

        return $transaction;
    }
}