<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        // Build query dasar
        $query = "SELECT * FROM customers WHERE 1=1";
        $params = [];

        // Search functionality
        $search = $request->get('search');
        if ($search) {
            $query .= " AND (name LIKE ? OR phone LIKE ? OR email LIKE ? OR address LIKE ?)";
            $searchTerm = "%{$search}%";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        }

        // Order dan pagination - tambah limit untuk mobile
        $query .= " ORDER BY created_at DESC LIMIT 50";

        // Eksekusi query
        $customers = DB::select($query, $params);

        // Hitung statistics dengan query terpisah
        $statsQuery = "
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) as today,
                SUM(CASE WHEN YEARWEEK(created_at) = YEARWEEK(CURDATE()) THEN 1 ELSE 0 END) as this_week,
                SUM(CASE WHEN MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE()) THEN 1 ELSE 0 END) as this_month
            FROM customers
        ";
        
        $statsResult = DB::selectOne($statsQuery);
        $stats = [
            'total' => $statsResult->total,
            'today' => $statsResult->today,
            'this_week' => $statsResult->this_week,
            'this_month' => $statsResult->this_month,
        ];

        return view('customers.index', compact('customers', 'stats', 'search'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            // Insert data langsung ke MySQL
            DB::insert("
                INSERT INTO customers (name, phone, email, address, notes, created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, NOW(), NOW())
            ", [
                $validated['name'],
                $validated['phone'],
                $validated['email'],
                $validated['address'],
                $validated['notes']
            ]);

            return redirect()->route('customers.index')
                ->with('success', 'Pelanggan berhasil ditambahkan!');
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal menambahkan pelanggan: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show($id)
    {
        // Get customer by ID
        $customer = DB::selectOne("SELECT * FROM customers WHERE id = ?", [$id]);
        
        if (!$customer) {
            abort(404, 'Pelanggan tidak ditemukan');
        }

        // Cek apakah table transactions exists sebelum query
        $transactionStats = [
            'total_orders' => 0,
            'total_spent' => 0,
            'avg_order_value' => 0,
            'last_order_date' => null
        ];

        try {
            $tableExists = DB::selectOne("
                SELECT COUNT(*) as count 
                FROM information_schema.tables 
                WHERE table_schema = DATABASE() 
                AND table_name = 'transactions'
            ");

            if ($tableExists->count > 0) {
                $stats = DB::selectOne("
                    SELECT 
                        COUNT(*) as total_orders,
                        COALESCE(SUM(total_amount), 0) as total_spent,
                        COALESCE(AVG(total_amount), 0) as avg_order_value,
                        MAX(created_at) as last_order_date
                    FROM transactions 
                    WHERE customer_id = ?
                ", [$id]);

                if ($stats) {
                    $transactionStats = [
                        'total_orders' => $stats->total_orders,
                        'total_spent' => $stats->total_spent,
                        'avg_order_value' => $stats->avg_order_value,
                        'last_order_date' => $stats->last_order_date
                    ];
                }
            }
        } catch (\Exception $e) {
            // Jika table transactions tidak ada, lanjutkan tanpa error
        }

        return view('customers.show', compact('customer', 'transactionStats'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            // Update data langsung di MySQL
            DB::update("
                UPDATE customers 
                SET name = ?, phone = ?, email = ?, address = ?, notes = ?, updated_at = NOW()
                WHERE id = ?
            ", [
                $validated['name'],
                $validated['phone'],
                $validated['email'],
                $validated['address'],
                $validated['notes'],
                $id
            ]);

            return redirect()->route('customers.index')
                ->with('success', 'Data pelanggan berhasil diperbarui!');
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal memperbarui data pelanggan: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            // Cek apakah table transactions exists
            $tableExists = DB::selectOne("
                SELECT COUNT(*) as count 
                FROM information_schema.tables 
                WHERE table_schema = DATABASE() 
                AND table_name = 'transactions'
            ");

            if ($tableExists->count > 0) {
                // Check if customer has transactions
                $hasTransactions = DB::selectOne("
                    SELECT COUNT(*) as count FROM transactions WHERE customer_id = ?
                ", [$id]);

                if ($hasTransactions->count > 0) {
                    return redirect()->back()
                        ->with('error', 'Tidak dapat menghapus pelanggan karena memiliki riwayat transaksi.');
                }
            }

            // Delete customer
            DB::delete("DELETE FROM customers WHERE id = ?", [$id]);

            return redirect()->route('customers.index')
                ->with('success', 'Pelanggan berhasil dihapus!');
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal menghapus pelanggan: ' . $e->getMessage());
        }
    }

    // API untuk live search (digunakan di view)
    public function search(Request $request)
    {
        $search = $request->get('q');
        
        $query = "SELECT id, name, phone, email, address FROM customers WHERE 1=1";
        $params = [];

        if ($search) {
            $query .= " AND (name LIKE ? OR phone LIKE ? OR email LIKE ?)";
            $searchTerm = "%{$search}%";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
        }

        $query .= " ORDER BY name ASC LIMIT 20";

        $customers = DB::select($query, $params);

        return response()->json([
            'success' => true,
            'data' => $customers
        ]);
    }

    // Method untuk mendapatkan top customers (optional)
    public function getTopCustomers()
    {
        try {
            // Cek apakah table transactions exists
            $tableExists = DB::selectOne("
                SELECT COUNT(*) as count 
                FROM information_schema.tables 
                WHERE table_schema = DATABASE() 
                AND table_name = 'transactions'
            ");

            $topCustomers = [];

            if ($tableExists->count > 0) {
                $topCustomers = DB::select("
                    SELECT 
                        c.id,
                        c.name,
                        c.phone,
                        COUNT(t.id) as total_orders,
                        COALESCE(SUM(t.total_amount), 0) as total_spent
                    FROM customers c
                    LEFT JOIN transactions t ON c.id = t.customer_id
                    GROUP BY c.id, c.name, c.phone
                    ORDER BY total_spent DESC
                    LIMIT 10
                ");
            } else {
                // Jika tidak ada transactions, ambil customers terbaru
                $topCustomers = DB::select("
                    SELECT 
                        id,
                        name,
                        phone,
                        0 as total_orders,
                        0 as total_spent
                    FROM customers
                    ORDER BY created_at DESC
                    LIMIT 10
                ");
            }

            return response()->json([
                'success' => true,
                'data' => $topCustomers
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}