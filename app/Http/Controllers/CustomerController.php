<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        Log::debug('CustomerController@index: Memulai proses mengambil data customers');
        Log::debug('CustomerController@index: Request parameters', $request->all());

        // Build query dasar
        $query = "SELECT * FROM customers WHERE 1=1";
        $params = [];

        // Search functionality
        $search = $request->get('search');
        if ($search) {
            Log::debug('CustomerController@index: Menerapkan filter search', ['search_term' => $search]);
            $query .= " AND (name LIKE ? OR phone LIKE ? OR email LIKE ? OR address LIKE ?)";
            $searchTerm = "%{$search}%";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        }

        // Order dan pagination - tambah limit untuk mobile
        $query .= " ORDER BY created_at DESC LIMIT 50";
        Log::debug('CustomerController@index: Query yang akan dieksekusi', [
            'query' => $query,
            'params_count' => count($params)
        ]);

        // Eksekusi query
        Log::debug('CustomerController@index: Menjalankan query customers');
        $customers = DB::select($query, $params);
        Log::debug('CustomerController@index: Berhasil mengambil ' . count($customers) . ' customers');

        // Hitung statistics dengan query terpisah
        Log::debug('CustomerController@index: Menghitung statistics customers');
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

        Log::debug('CustomerController@index: Statistics hasil perhitungan', $stats);
        Log::debug('CustomerController@index: Proses selesai, mengembalikan view');

        return view('customers.index', compact('customers', 'stats', 'search'));
    }

    public function store(Request $request)
    {
        Log::debug('CustomerController@store: Memulai proses membuat customer baru');
        Log::debug('CustomerController@store: Data request received', $request->all());

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
        ]);

        Log::debug('CustomerController@store: Validasi berhasil', $validated);

        try {
            // Insert data langsung ke MySQL
            Log::debug('CustomerController@store: Menyimpan customer ke database');
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

            $customerId = DB::getPdo()->lastInsertId();
            Log::debug('CustomerController@store: Customer berhasil dibuat', ['customer_id' => $customerId]);

            return redirect()->route('customers.index')
                ->with('success', 'Pelanggan berhasil ditambahkan!');
                
        } catch (\Exception $e) {
            Log::error('CustomerController@store: Gagal membuat customer', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'input_data' => $validated
            ]);
            return redirect()->back()
                ->with('error', 'Gagal menambahkan pelanggan: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show($id)
    {
        Log::debug('CustomerController@show: Memulai proses mengambil detail customer', ['customer_id' => $id]);

        // Get customer by ID
        Log::debug('CustomerController@show: Mencari customer di database');
        $customer = DB::selectOne("SELECT * FROM customers WHERE id = ?", [$id]);
        
        if (!$customer) {
            Log::warning('CustomerController@show: Customer tidak ditemukan', ['customer_id' => $id]);
            abort(404, 'Pelanggan tidak ditemukan');
        }

        Log::debug('CustomerController@show: Customer ditemukan', [
            'customer_id' => $customer->id,
            'customer_name' => $customer->name
        ]);

        // Cek apakah table transactions exists sebelum query
        $transactionStats = [
            'total_orders' => 0,
            'total_spent' => 0,
            'avg_order_value' => 0,
            'last_order_date' => null
        ];

        try {
            Log::debug('CustomerController@show: Memeriksa keberadaan table transactions');
            $tableExists = DB::selectOne("
                SELECT COUNT(*) as count 
                FROM information_schema.tables 
                WHERE table_schema = DATABASE() 
                AND table_name = 'transactions'
            ");

            Log::debug('CustomerController@show: Table transactions exists', ['exists' => $tableExists->count > 0]);

            if ($tableExists->count > 0) {
                Log::debug('CustomerController@show: Mengambil statistics transaksi customer');
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
                    Log::debug('CustomerController@show: Statistics transaksi ditemukan', $transactionStats);
                } else {
                    Log::debug('CustomerController@show: Tidak ada data transaksi untuk customer ini');
                }
            } else {
                Log::debug('CustomerController@show: Table transactions tidak ditemukan, menggunakan default stats');
            }
        } catch (\Exception $e) {
            Log::warning('CustomerController@show: Error saat mengambil statistics transaksi', [
                'error' => $e->getMessage(),
                'customer_id' => $id
            ]);
            // Jika table transactions tidak ada, lanjutkan tanpa error
        }

        Log::debug('CustomerController@show: Proses selesai, mengembalikan view');
        return view('customers.show', compact('customer', 'transactionStats'));
    }

    public function update(Request $request, $id)
    {
        Log::debug('CustomerController@update: Memulai proses update customer', ['customer_id' => $id]);
        Log::debug('CustomerController@update: Data request received', $request->all());

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
        ]);

        Log::debug('CustomerController@update: Validasi berhasil', $validated);

        try {
            // Update data langsung di MySQL
            Log::debug('CustomerController@update: Menyimpan perubahan ke database');
            $affectedRows = DB::update("
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

            Log::debug('CustomerController@update: Update selesai', ['affected_rows' => $affectedRows]);

            if ($affectedRows === 0) {
                Log::warning('CustomerController@update: Tidak ada data yang diupdate', ['customer_id' => $id]);
            }

            return redirect()->route('customers.index')
                ->with('success', 'Data pelanggan berhasil diperbarui!');
                
        } catch (\Exception $e) {
            Log::error('CustomerController@update: Gagal update customer', [
                'customer_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'update_data' => $validated
            ]);
            return redirect()->back()
                ->with('error', 'Gagal memperbarui data pelanggan: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy($id)
    {
        Log::debug('CustomerController@destroy: Memulai proses hapus customer', ['customer_id' => $id]);

        try {
            // Cek apakah table transactions exists
            Log::debug('CustomerController@destroy: Memeriksa keberadaan table transactions');
            $tableExists = DB::selectOne("
                SELECT COUNT(*) as count 
                FROM information_schema.tables 
                WHERE table_schema = DATABASE() 
                AND table_name = 'transactions'
            ");

            Log::debug('CustomerController@destroy: Table transactions exists', ['exists' => $tableExists->count > 0]);

            if ($tableExists->count > 0) {
                // Check if customer has transactions
                Log::debug('CustomerController@destroy: Memeriksa apakah customer memiliki transaksi');
                $hasTransactions = DB::selectOne("
                    SELECT COUNT(*) as count FROM transactions WHERE customer_id = ?
                ", [$id]);

                Log::debug('CustomerController@destroy: Jumlah transaksi customer', ['transaction_count' => $hasTransactions->count]);

                if ($hasTransactions->count > 0) {
                    Log::warning('CustomerController@destroy: Tidak dapat menghapus customer karena memiliki transaksi', [
                        'customer_id' => $id,
                        'transaction_count' => $hasTransactions->count
                    ]);
                    return redirect()->back()
                        ->with('error', 'Tidak dapat menghapus pelanggan karena memiliki riwayat transaksi.');
                }
            }

            // Delete customer
            Log::debug('CustomerController@destroy: Menghapus customer dari database');
            $deletedRows = DB::delete("DELETE FROM customers WHERE id = ?", [$id]);
            
            Log::debug('CustomerController@destroy: Delete selesai', ['deleted_rows' => $deletedRows]);

            if ($deletedRows === 0) {
                Log::warning('CustomerController@destroy: Tidak ada customer yang dihapus', ['customer_id' => $id]);
            }

            return redirect()->route('customers.index')
                ->with('success', 'Pelanggan berhasil dihapus!');
                
        } catch (\Exception $e) {
            Log::error('CustomerController@destroy: Gagal menghapus customer', [
                'customer_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()
                ->with('error', 'Gagal menghapus pelanggan: ' . $e->getMessage());
        }
    }

    // API untuk live search (digunakan di view)
    public function search(Request $request)
    {
        Log::debug('CustomerController@search: Memulai live search', ['search_query' => $request->get('q')]);
        
        $search = $request->get('q');
        
        $query = "SELECT id, name, phone, email, address FROM customers WHERE 1=1";
        $params = [];

        if ($search) {
            Log::debug('CustomerController@search: Menerapkan filter search', ['search_term' => $search]);
            $query .= " AND (name LIKE ? OR phone LIKE ? OR email LIKE ?)";
            $searchTerm = "%{$search}%";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
        }

        $query .= " ORDER BY name ASC LIMIT 20";

        Log::debug('CustomerController@search: Menjalankan query search', [
            'query' => $query,
            'params_count' => count($params)
        ]);

        $customers = DB::select($query, $params);
        Log::debug('CustomerController@search: Hasil search ditemukan', ['results_count' => count($customers)]);

        return response()->json([
            'success' => true,
            'data' => $customers
        ]);
    }

    // Method untuk mendapatkan top customers (optional)
    public function getTopCustomers()
    {
        Log::debug('CustomerController@getTopCustomers: Memulai proses mengambil top customers');

        try {
            // Cek apakah table transactions exists
            Log::debug('CustomerController@getTopCustomers: Memeriksa keberadaan table transactions');
            $tableExists = DB::selectOne("
                SELECT COUNT(*) as count 
                FROM information_schema.tables 
                WHERE table_schema = DATABASE() 
                AND table_name = 'transactions'
            ");

            Log::debug('CustomerController@getTopCustomers: Table transactions exists', ['exists' => $tableExists->count > 0]);

            $topCustomers = [];

            if ($tableExists->count > 0) {
                Log::debug('CustomerController@getTopCustomers: Mengambil top customers berdasarkan transaksi');
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
                Log::debug('CustomerController@getTopCustomers: Berhasil mengambil top customers berdasarkan transaksi', ['count' => count($topCustomers)]);
            } else {
                // Jika tidak ada transactions, ambil customers terbaru
                Log::debug('CustomerController@getTopCustomers: Mengambil customers terbaru (transactions tidak ada)');
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
                Log::debug('CustomerController@getTopCustomers: Berhasil mengambil customers terbaru', ['count' => count($topCustomers)]);
            }

            return response()->json([
                'success' => true,
                'data' => $topCustomers
            ]);

        } catch (\Exception $e) {
            Log::error('CustomerController@getTopCustomers: Gagal mengambil top customers', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}