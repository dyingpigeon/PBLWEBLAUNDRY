<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\Customer;
use App\Models\Service;
use App\Models\TransactionItem;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TransactionController extends Controller
{
    /**
     * Display listing of transactions
     */
    public function index()
    {
        // Untuk sekarang, return view saja
        // Nanti bisa ditambah data transactions
        return view('transactions.index');
    }

    /**
     * Show form for creating new transaction
     */
    public function create()
    {
        // Redirect ke index, karena kita pakai modal workflow
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
            $transactionNumber = 'TRX-' . date('Ymd') . '-' . str_pad(Transaction::count() + 1, 4, '0', STR_PAD_LEFT);

            // Create transaction
            $transaction = Transaction::create([
                'transaction_number' => $transactionNumber,
                'customer_id' => $validated['customer_id'],
                'service_id' => $validated['service_id'],
                'total_amount' => $validated['total_amount'],
                'notes' => $validated['notes'] ?? null,
                'status' => 'pending', // pending, processing, completed, picked_up
                'order_date' => now(),
                'estimated_completion' => now()->addDays(2) // Default 2 hari
            ]);

            // Create transaction items
            foreach ($validated['items'] as $item) {
                TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'service_item_id' => $item['id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],
                    'subtotal' => $item['quantity'] * $item['price']
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil dibuat',
                'data' => [
                    'transaction' => $transaction,
                    'transaction_number' => $transactionNumber
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat transaksi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display specific transaction
     */
    public function show($id)
    {
        $transaction = Transaction::with(['customer', 'service', 'items.serviceItem'])
                                ->findOrFail($id);

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
            'status' => 'required|in:pending,processing,completed,picked_up,cancelled'
        ]);

        $transaction = Transaction::findOrFail($id);
        $transaction->update(['status' => $validated['status']]);

        return response()->json([
            'success' => true,
            'message' => 'Status transaksi berhasil diupdate',
            'data' => $transaction
        ]);
    }

    /**
     * Get customers for transaction (searchable)
     */
    public function getCustomers(Request $request)
    {
        $search = $request->get('search');
        
        $customers = Customer::when($search, function($query) use ($search) {
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
        })
        ->select('id', 'name', 'phone', 'address')
        ->limit(20)
        ->get();

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
        $services = Service::with(['items' => function($query) {
            $query->where('active', true);
        }])
        ->where('active', true)
        ->get();

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
        $today = Carbon::today();
        
        $summary = [
            'total_transactions' => Transaction::whereDate('created_at', $today)->count(),
            'processing_count' => Transaction::where('status', 'processing')->whereDate('created_at', $today)->count(),
            'total_income' => Transaction::whereDate('created_at', $today)->sum('total_amount')
        ];

        return response()->json([
            'success' => true,
            'data' => $summary
        ]);
    }

    /**
     * Get recent transactions
     */
    public function getRecentTransactions()
    {
        $transactions = Transaction::with(['customer', 'service'])
                                ->orderBy('created_at', 'desc')
                                ->limit(10)
                                ->get();

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
        $transaction = Transaction::with(['customer', 'service', 'items.serviceItem'])
                                ->findOrFail($id);

        // Untuk sekarang, return data receipt
        // Nanti bisa generate PDF
        return response()->json([
            'success' => true,
            'data' => [
                'transaction' => $transaction,
                'print_data' => [
                    'store_name' => 'LaundryKu',
                    'store_address' => 'Jl. Contoh No. 123',
                    'store_phone' => '(021) 123-4567'
                ]
            ]
        ]);
    }
}