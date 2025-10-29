<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TransactionController extends Controller
{
    /**
     * Display listing of transactions
     */
    public function index()
    {
        return view('transactions.index');
    }

    /**
     * Show form for creating new transaction
     */
    public function create()
    {
        return redirect()->route('transactions.index');
    }

    /**
     * Store new transaction
     */
    public function store(Request $request)
    {
        // Validasi data
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'service_id' => 'required|exists:services,id',
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:service_items,id',
            'items.*.quantity' => 'required|numeric|min:0.1',
            'items.*.price' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
            'total_amount' => 'required|numeric|min:0'
        ]);

        DB::beginTransaction();

        try {
            // Generate transaction number
            $today = now()->format('Ymd');
            $todayCount = DB::selectOne("
                SELECT COUNT(*) as count 
                FROM transactions 
                WHERE DATE(created_at) = CURDATE()
            ")->count + 1;

            $transactionNumber = 'TRX-' . $today . '-' . str_pad($todayCount, 4, '0', STR_PAD_LEFT);

            // Create transaction dengan query langsung
            DB::insert("
                INSERT INTO transactions (
                    transaction_number, customer_id, service_id, total_amount, notes, 
                    status, payment_status, payment_method, order_date, estimated_completion,
                    created_at, updated_at
                ) VALUES (?, ?, ?, ?, ?, 'new', 'pending', 'cash', NOW(), DATE_ADD(NOW(), INTERVAL 2 DAY), NOW(), NOW())
            ", [
                $transactionNumber,
                $validated['customer_id'],
                $validated['service_id'],
                $validated['total_amount'],
                $validated['notes'] ?? null
            ]);

            // Get the last inserted transaction ID
            $transactionId = DB::getPdo()->lastInsertId();

            // Create transaction items dengan query langsung
            foreach ($validated['items'] as $item) {
                // Get service item name from database
                $serviceItem = DB::selectOne("
                    SELECT name FROM service_items WHERE id = ?
                ", [$item['id']]);

                $itemName = $serviceItem ? $serviceItem->name : 'Unknown Item';
                $subtotal = $item['quantity'] * $item['price'];

                DB::insert("
                    INSERT INTO transaction_items (
                        transaction_id, service_item_id, item_name, quantity, unit_price, subtotal,
                        created_at, updated_at
                    ) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
                ", [
                    $transactionId,
                    $item['id'],
                    $itemName,
                    $item['quantity'],
                    $item['price'],
                    $subtotal
                ]);
            }

            DB::commit();
            // Dalam method store() di TransactionController
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Transaksi berhasil dibuat!',
                    'data' => [
                        'transaction_number' => $transactionNumber,
                        'transaction_id' => $transactionId
                    ]
                ]);
            }

            return redirect()->route('dashboard')->with('success', 'Transaksi berhasil dibuat! No: ' . $transactionNumber);

            // return redirect()->route('dashboard')->with('success', 'Transaksi berhasil dibuat! No: ' . $transactionNumber);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal membuat transaksi: ' . $e->getMessage())->withInput();
        }


    }

    /**
     * Display specific transaction
     */
    public function show($id)
    {
        // Get transaction dengan query langsung
        $transaction = DB::selectOne("
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
                si.name as service_item_name,
                si.description as service_item_description
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

    /**
     * Update transaction status
     */
    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:new,washing,ironing,ready,picked_up,cancelled'
        ]);

        // Check if transaction exists
        $transaction = DB::selectOne("SELECT timeline FROM transactions WHERE id = ?", [$id]);

        if (!$transaction) {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi tidak ditemukan'
            ], 404);
        }

        // Update timeline
        $timeline = $transaction->timeline ? json_decode($transaction->timeline, true) : [];
        $timeline[] = [
            'status' => $validated['status'],
            'timestamp' => now()->toISOString(),
            'description' => $this->getStatusDescription($validated['status'])
        ];

        // Update transaction status dengan query langsung
        DB::update("
            UPDATE transactions 
            SET status = ?, timeline = ?, updated_at = NOW()
            WHERE id = ?
        ", [
            $validated['status'],
            json_encode($timeline),
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

        return $descriptions[$status] ?? 'Status updated';
    }

    /**
     * Get customers for transaction (searchable)
     */
    public function getCustomers(Request $request)
    {
        $search = $request->get('search');

        $query = "
            SELECT id, name, phone, address 
            FROM customers 
            WHERE 1=1
        ";
        $params = [];

        if ($search) {
            $query .= " AND (name LIKE ? OR phone LIKE ?)";
            $searchTerm = "%{$search}%";
            $params = [$searchTerm, $searchTerm];
        }

        $query .= " ORDER BY name ASC LIMIT 20";

        $customers = DB::select($query, $params);

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
        // Get active services
        $services = DB::select("
            SELECT * FROM services WHERE active = 1
        ");

        // Get service items for each service
        foreach ($services as &$service) {
            $service->items = DB::select("
                SELECT * FROM service_items 
                WHERE service_id = ? AND active = 1
                ORDER BY name ASC
            ", [$service->id]);
        }

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
        $today = Carbon::today()->format('Y-m-d');

        $summary = DB::selectOne("
            SELECT 
                COUNT(*) as total_transactions,
                SUM(CASE WHEN status IN ('new', 'washing', 'ironing') THEN 1 ELSE 0 END) as processing_count,
                COALESCE(SUM(total_amount), 0) as total_income
            FROM transactions 
            WHERE DATE(created_at) = ?
        ", [$today]);

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
        $transactions = DB::select("
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
        ");

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
        // Get transaction dengan semua data yang diperlukan untuk receipt
        $transaction = DB::selectOne("
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
        ", [$id]);

        if (!$transaction) {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi tidak ditemukan'
            ], 404);
        }

        // Get transaction items untuk receipt
        $items = DB::select("
            SELECT 
                ti.item_name,
                ti.quantity,
                ti.unit_price,
                ti.subtotal
            FROM transaction_items ti
            WHERE ti.transaction_id = ?
            ORDER BY ti.id ASC
        ", [$id]);

        $transaction->items = $items;

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
        $stats = DB::selectOne("
            SELECT 
                COUNT(*) as total_transactions,
                SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) as today_transactions,
                SUM(CASE WHEN status = 'new' THEN 1 ELSE 0 END) as new_transactions,
                SUM(CASE WHEN status IN ('washing', 'ironing') THEN 1 ELSE 0 END) as processing_transactions,
                SUM(CASE WHEN status = 'ready' THEN 1 ELSE 0 END) as ready_transactions,
                COALESCE(SUM(total_amount), 0) as total_revenue,
                COALESCE(SUM(CASE WHEN DATE(created_at) = CURDATE() THEN total_amount ELSE 0 END), 0) as today_revenue
            FROM transactions
        ");

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
        DB::beginTransaction();

        try {
            // Delete transaction items first
            DB::delete("DELETE FROM transaction_items WHERE transaction_id = ?", [$id]);

            // Delete transaction
            DB::delete("DELETE FROM transactions WHERE id = ?", [$id]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil dihapus'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus transaksi: ' . $e->getMessage()
            ], 500);
        }
    }
}