<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class ReportController extends Controller
{
    public function index()
    {
        Log::debug('ReportController@index: Memulai proses menampilkan halaman reports');
        Log::debug('ReportController@index: Mengembalikan view reports.index');
        return view('reports.index');
    }

    /**
     * Get financial report summary
     */
    public function getFinancialSummary(Request $request)
    {
        Log::debug('ReportController@getFinancialSummary: Memulai proses mengambil summary keuangan');
        Log::debug('ReportController@getFinancialSummary: Request parameters', $request->all());

        $validator = Validator::make($request->all(), [
            'period' => 'required|in:week,month,quarter,year,custom',
            'start_date' => 'required_if:period,custom|date',
            'end_date' => 'required_if:period,custom|date',
        ]);

        if ($validator->fails()) {
            Log::warning('ReportController@getFinancialSummary: Validasi gagal', [
                'errors' => $validator->errors()->toArray(),
                'input_data' => $request->all()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $period = $request->input('period');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        Log::debug('ReportController@getFinancialSummary: Parameter yang diterima', [
            'period' => $period,
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);

        $dateRange = $this->getDateRange($period, $startDate, $endDate);
        Log::debug('ReportController@getFinancialSummary: Date range yang dihasilkan', $dateRange);

        try {
            // PERBAIKAN 1: Sesuaikan status dengan database
            $statuses = ['ready', 'done']; // Sesuai dengan tabel transactions
            $paymentStatuses = ['paid', 'partial'];

            // Get total income and orders
            Log::debug('ReportController@getFinancialSummary: Menjalankan query summary');
            $summaryQuery = "
                SELECT 
                    COUNT(*) as total_orders,
                    COALESCE(SUM(total_amount), 0) as total_income,
                    COUNT(DISTINCT customer_id) as total_customers
                FROM transactions 
                WHERE status IN ('" . implode("','", $statuses) . "')
                AND payment_status IN ('" . implode("','", $paymentStatuses) . "')
                AND created_at BETWEEN ? AND ?
            ";

            $summary = DB::select($summaryQuery, [$dateRange['start'], $dateRange['end']])[0];
            Log::debug('ReportController@getFinancialSummary: Hasil query summary', [
                'total_orders' => $summary->total_orders,
                'total_income' => $summary->total_income,
                'total_customers' => $summary->total_customers
            ]);

            // Get daily revenue for chart
            Log::debug('ReportController@getFinancialSummary: Menjalankan query revenue chart');
            $revenueChartQuery = "
                SELECT 
                    DATE(created_at) as date,
                    COALESCE(SUM(total_amount), 0) as daily_income,
                    COUNT(*) as daily_orders
                FROM transactions 
                WHERE status IN ('" . implode("','", $statuses) . "')
                AND payment_status IN ('" . implode("','", $paymentStatuses) . "')
                AND created_at BETWEEN ? AND ?
                GROUP BY DATE(created_at)
                ORDER BY date
            ";

            $revenueData = DB::select($revenueChartQuery, [$dateRange['start'], $dateRange['end']]);
            Log::debug('ReportController@getFinancialSummary: Hasil query revenue chart', [
                'data_points_count' => count($revenueData)
            ]);

            // Get service distribution
            Log::debug('ReportController@getFinancialSummary: Menjalankan query service distribution');
            $servicesQuery = "
                SELECT 
                    s.name as service_name,
                    COUNT(t.id) as order_count,
                    COALESCE(SUM(t.total_amount), 0) as total_revenue
                FROM transactions t
                INNER JOIN services s ON t.service_id = s.id
                WHERE t.status IN ('" . implode("','", $statuses) . "')
                AND t.payment_status IN ('" . implode("','", $paymentStatuses) . "')
                AND t.created_at BETWEEN ? AND ?
                GROUP BY s.id, s.name
                ORDER BY order_count DESC
            ";

            $servicesData = DB::select($servicesQuery, [$dateRange['start'], $dateRange['end']]);
            Log::debug('ReportController@getFinancialSummary: Hasil query service distribution', [
                'services_count' => count($servicesData)
            ]);

            // Get recent transactions
            Log::debug('ReportController@getFinancialSummary: Menjalankan query recent transactions');
            $transactionsQuery = "
                SELECT 
                    t.transaction_number,
                    t.total_amount,
                    t.created_at,
                    t.status,
                    t.payment_status,
                    c.name as customer_name,
                    s.name as service_name
                FROM transactions t
                INNER JOIN customers c ON t.customer_id = c.id
                INNER JOIN services s ON t.service_id = s.id
                WHERE t.status IN ('" . implode("','", $statuses) . "')
                AND t.payment_status IN ('" . implode("','", $paymentStatuses) . "')
                AND t.created_at BETWEEN ? AND ?
                ORDER BY t.created_at DESC
                LIMIT 50
            ";

            $transactions = DB::select($transactionsQuery, [$dateRange['start'], $dateRange['end']]);
            Log::debug('ReportController@getFinancialSummary: Hasil query recent transactions', [
                'transactions_count' => count($transactions)
            ]);

            Log::debug('ReportController@getFinancialSummary: Semua query berhasil, mengembalikan response');
            return response()->json([
                'success' => true,
                'data' => [
                    'summary' => [
                        'total_income' => (float) $summary->total_income,
                        'total_orders' => (int) $summary->total_orders,
                        'total_customers' => (int) $summary->total_customers,
                    ],
                    'revenue_chart' => $revenueData,
                    'services_distribution' => $servicesData,
                    'recent_transactions' => $transactions,
                    'date_range' => $dateRange
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('ReportController@getFinancialSummary: Gagal mengambil data laporan', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'date_range' => $dateRange,
                'period' => $period
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data laporan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get today's summary for dashboard
     */
    public function getTodaySummary()
    {
        Log::debug('ReportController@getTodaySummary: Memulai proses mengambil summary hari ini');

        try {
            $today = now()->format('Y-m-d');
            $tomorrow = now()->addDay()->format('Y-m-d');

            Log::debug('ReportController@getTodaySummary: Tanggal yang digunakan', [
                'today' => $today,
                'tomorrow' => $tomorrow
            ]);

            // PERBAIKAN 2: Sesuaikan status
            $statuses = ['ready', 'done'];

            $summaryQuery = "
                SELECT 
                    COUNT(*) as total_transactions,
                    SUM(CASE WHEN status IN ('new', 'process') THEN 1 ELSE 0 END) as processing_count,
                    COALESCE(SUM(CASE WHEN payment_status = 'paid' THEN total_amount ELSE 0 END), 0) as total_income
                FROM transactions 
                WHERE DATE(created_at) = ?
            ";

            Log::debug('ReportController@getTodaySummary: Menjalankan query today summary');
            $summary = DB::select($summaryQuery, [$today])[0];
            Log::debug('ReportController@getTodaySummary: Hasil query today summary', [
                'total_transactions' => $summary->total_transactions,
                'processing_count' => $summary->processing_count,
                'total_income' => $summary->total_income
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'total_transactions' => (int) $summary->total_transactions,
                    'processing_count' => (int) $summary->processing_count,
                    'total_income' => (float) $summary->total_income
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('ReportController@getTodaySummary: Gagal mengambil summary hari ini', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil summary hari ini',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get revenue comparison with previous period
     */
    public function getRevenueComparison(Request $request)
    {
        Log::debug('ReportController@getRevenueComparison: Memulai proses perbandingan revenue');
        Log::debug('ReportController@getRevenueComparison: Request parameters', $request->all());

        $validator = Validator::make($request->all(), [
            'period' => 'required|in:week,month,quarter,year',
        ]);

        if ($validator->fails()) {
            Log::warning('ReportController@getRevenueComparison: Validasi gagal', [
                'errors' => $validator->errors()->toArray(),
                'input_data' => $request->all()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $period = $request->input('period');
        Log::debug('ReportController@getRevenueComparison: Period yang dipilih', ['period' => $period]);

        $currentRange = $this->getDateRange($period);
        $previousRange = $this->getPreviousDateRange($period);

        Log::debug('ReportController@getRevenueComparison: Date ranges', [
            'current_range' => $currentRange,
            'previous_range' => $previousRange
        ]);

        try {
            // PERBAIKAN 3: Sesuaikan status untuk revenue comparison
            $statuses = ['ready', 'done'];
            $paymentStatuses = ['paid', 'partial'];

            // Current period revenue
            Log::debug('ReportController@getRevenueComparison: Menjalankan query revenue comparison');
            $currentQuery = "
                SELECT COALESCE(SUM(total_amount), 0) as total_income
                FROM transactions 
                WHERE status IN ('" . implode("','", $statuses) . "')
                AND payment_status IN ('" . implode("','", $paymentStatuses) . "')
                AND created_at BETWEEN ? AND ?
            ";

            $currentRevenue = DB::select($currentQuery, [$currentRange['start'], $currentRange['end']])[0];
            $previousRevenue = DB::select($currentQuery, [$previousRange['start'], $previousRange['end']])[0];

            $currentIncome = (float) $currentRevenue->total_income;
            $previousIncome = (float) $previousRevenue->total_income;

            Log::debug('ReportController@getRevenueComparison: Hasil perhitungan revenue', [
                'current_income' => $currentIncome,
                'previous_income' => $previousIncome
            ]);

            $growth = $previousIncome > 0 ? (($currentIncome - $previousIncome) / $previousIncome) * 100 : 0;
            $growthAmount = $currentIncome - $previousIncome;

            Log::debug('ReportController@getRevenueComparison: Growth calculation', [
                'growth_percentage' => $growth,
                'growth_amount' => $growthAmount
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'current_period' => [
                        'income' => $currentIncome,
                        'start_date' => $currentRange['start'],
                        'end_date' => $currentRange['end']
                    ],
                    'previous_period' => [
                        'income' => $previousIncome,
                        'start_date' => $previousRange['start'],
                        'end_date' => $previousRange['end']
                    ],
                    'growth_percentage' => round($growth, 2),
                    'growth_amount' => $growthAmount
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('ReportController@getRevenueComparison: Gagal mengambil perbandingan revenue', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'period' => $period,
                'current_range' => $currentRange,
                'previous_range' => $previousRange
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil perbandingan revenue',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export report to CSV/PDF/Excel
     */
    // public function exportReport(Request $request)
    // {
    //     Log::debug('ReportController@exportReport: Memulai proses export report');
    //     Log::debug('ReportController@exportReport: Request parameters', $request->all());

    //     $validator = Validator::make($request->all(), [
    //         'period' => 'required|in:week,month,quarter,year,custom',
    //         'start_date' => 'required_if:period,custom|date',
    //         'end_date' => 'required_if:period,custom|date',
    //         'format' => 'required|in:csv,pdf,excel' // PERBAIKAN 4: Tambahkan format excel
    //     ]);

    //     if ($validator->fails()) {
    //         Log::warning('ReportController@exportReport: Validasi export gagal', [
    //             'errors' => $validator->errors()->toArray(),
    //             'input_data' => $request->all()
    //         ]);
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Validasi gagal',
    //             'errors' => $validator->errors()
    //         ], 422);
    //     }

    //     $period = $request->input('period');
    //     $format = $request->input('format');
    //     $startDate = $request->input('start_date');
    //     $endDate = $request->input('end_date');

    //     Log::debug('ReportController@exportReport: Parameter export', [
    //         'period' => $period,
    //         'format' => $format,
    //         'start_date' => $startDate,
    //         'end_date' => $endDate
    //     ]);

    //     $dateRange = $this->getDateRange($period, $startDate, $endDate);
    //     Log::debug('ReportController@exportReport: Date range export', $dateRange);

    //     try {
    //         // PERBAIKAN 5: Sesuaikan status untuk export
    //         $statuses = ['ready', 'done'];
    //         $paymentStatuses = ['paid', 'partial'];

    //         // Get detailed transactions for export
    //         Log::debug('ReportController@exportReport: Menjalankan query export data');
    //         $exportQuery = "
    //             SELECT 
    //                 t.transaction_number,
    //                 DATE(t.created_at) as transaction_date,
    //                 c.name as customer_name,
    //                 c.phone as customer_phone,
    //                 s.name as service_name,
    //                 t.total_amount,
    //                 t.status,
    //                 t.payment_status,
    //                 t.notes
    //             FROM transactions t
    //             INNER JOIN customers c ON t.customer_id = c.id
    //             INNER JOIN services s ON t.service_id = s.id
    //             WHERE t.status IN ('" . implode("','", $statuses) . "')
    //             AND t.payment_status IN ('" . implode("','", $paymentStatuses) . "')
    //             AND t.created_at BETWEEN ? AND ?
    //             ORDER BY t.created_at DESC
    //         ";

    //         $transactions = DB::select($exportQuery, [$dateRange['start'], $dateRange['end']]);
    //         Log::debug('ReportController@exportReport: Data transaksi untuk export', [
    //             'transactions_count' => count($transactions)
    //         ]);

    //         // Get summary for export
    //         Log::debug('ReportController@exportReport: Menjalankan query summary untuk export');
    //         $summaryQuery = "
    //             SELECT 
    //                 COUNT(*) as total_orders,
    //                 COALESCE(SUM(total_amount), 0) as total_income,
    //                 COUNT(DISTINCT customer_id) as total_customers
    //             FROM transactions 
    //             WHERE status IN ('" . implode("','", $statuses) . "')
    //             AND payment_status IN ('" . implode("','", $paymentStatuses) . "')
    //             AND created_at BETWEEN ? AND ?
    //         ";

    //         $summary = DB::select($summaryQuery, [$dateRange['start'], $dateRange['end']])[0];
    //         Log::debug('ReportController@exportReport: Summary untuk export', [
    //             'total_orders' => $summary->total_orders,
    //             'total_income' => $summary->total_income,
    //             'total_customers' => $summary->total_customers
    //         ]);

    //         // PERBAIKAN 6: Handle multiple formats
    //         if ($format === 'csv') {
    //             Log::debug('ReportController@exportReport: Memulai generate CSV');
    //             return $this->generateCSV($transactions, $summary, $dateRange);
    //         } elseif ($format === 'pdf') {
    //             Log::debug('ReportController@exportReport: Format PDF dipilih');
    //             return $this->generatePDF($transactions, $summary, $dateRange);
    //         } elseif ($format === 'excel') {
    //             Log::debug('ReportController@exportReport: Format Excel dipilih');
    //             return $this->generateExcel($transactions, $summary, $dateRange);
    //         } else {
    //             Log::warning('ReportController@exportReport: Format export tidak didukung', ['format' => $format]);
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Format export tidak didukung'
    //             ], 400);
    //         }

    //     } catch (\Exception $e) {
    //         Log::error('ReportController@exportReport: Gagal mengekspor laporan', [
    //             'error' => $e->getMessage(),
    //             'trace' => $e->getTraceAsString(),
    //             'period' => $period,
    //             'format' => $format,
    //             'date_range' => $dateRange
    //         ]);
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Gagal mengekspor laporan',
    //             'error' => $e->getMessage()
    //         ], 500);
    //     }
    // }

    /**
     * Generate CSV file for export
     */
    // private function generateCSV($transactions, $summary, $dateRange)
    // {
    //     Log::debug('ReportController@generateCSV: Memulai generate file CSV', [
    //         'transactions_count' => count($transactions),
    //         'date_range' => $dateRange
    //     ]);

    //     $fileName = 'laporan-keuangan-' . $dateRange['start'] . '-to-' . $dateRange['end'] . '.csv';
    //     Log::debug('ReportController@generateCSV: Nama file yang akan dihasilkan', ['file_name' => $fileName]);

    //     $headers = [
    //         'Content-Type' => 'text/csv',
    //         'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
    //     ];

    //     $callback = function () use ($transactions, $summary, $dateRange) {
    //         $file = fopen('php://output', 'w');

    //         // BOM for UTF-8
    //         fwrite($file, "\xEF\xBB\xBF");

    //         // Header
    //         fputcsv($file, ['LAPORAN KEUANGAN LAUNDRY']);
    //         fputcsv($file, ['Periode: ' . $dateRange['start'] . ' hingga ' . $dateRange['end']]);
    //         fputcsv($file, ['']);
    //         fputcsv($file, ['SUMMARY']);
    //         fputcsv($file, ['Total Pendapatan', 'Rp ' . number_format($summary->total_income, 0, ',', '.')]);
    //         fputcsv($file, ['Total Pesanan', $summary->total_orders]);
    //         fputcsv($file, ['Total Pelanggan', $summary->total_customers]);
    //         fputcsv($file, ['']);
    //         fputcsv($file, ['DETAIL TRANSAKSI']);
    //         fputcsv($file, [
    //             'No. Transaksi',
    //             'Tanggal',
    //             'Pelanggan',
    //             'Telepon',
    //             'Layanan',
    //             'Total',
    //             'Status',
    //             'Status Pembayaran',
    //             'Catatan'
    //         ]);

    //         // Data rows
    //         foreach ($transactions as $transaction) {
    //             fputcsv($file, [
    //                 $transaction->transaction_number,
    //                 $transaction->transaction_date,
    //                 $transaction->customer_name,
    //                 $transaction->customer_phone,
    //                 $transaction->service_name,
    //                 'Rp ' . number_format($transaction->total_amount, 0, ',', '.'),
    //                 $this->translateStatus($transaction->status),
    //                 $this->translatePaymentStatus($transaction->payment_status),
    //                 $transaction->notes ?? '-'
    //             ]);
    //         }

    //         fclose($file);
    //     };

    //     Log::debug('ReportController@generateCSV: CSV generation completed');
    //     return response()->stream($callback, 200, $headers);
    // }

    /**
     * Generate PDF file for export (Placeholder)
     */
    // private function generatePDF($transactions, $summary, $dateRange)
    // {
    //     Log::debug('ReportController@generatePDF: Memulai generate PDF (placeholder)');

    //     // PERBAIKAN 7: Untuk sementara return CSV, nanti bisa diimplementasi PDF
    //     return $this->generateCSV($transactions, $summary, $dateRange);

    //     // TODO: Implement PDF generation menggunakan library seperti DomPDF
    // }

    // /**
    //  * Generate Excel file for export (Placeholder)
    //  */
    // private function generateExcel($transactions, $summary, $dateRange)
    // {
    //     Log::debug('ReportController@generateExcel: Memulai generate Excel (placeholder)');

    //     // PERBAIKAN 8: Untuk sementara return CSV, nanti bisa diimplementasi Excel
    //     return $this->generateCSV($transactions, $summary, $dateRange);

    //     // TODO: Implement Excel generation menggunakan library seperti Maatwebsite/Laravel-Excel
    // }

    /**
     * Helper function to get date range based on period
     */
    private function getDateRange($period, $startDate = null, $endDate = null)
    {
        Log::debug('ReportController@getDateRange: Menghitung date range', [
            'period' => $period,
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);

        $now = now();

        switch ($period) {
            case 'week':
                $start = $now->copy()->startOfWeek()->format('Y-m-d 00:00:00');
                $end = $now->copy()->endOfWeek()->format('Y-m-d 23:59:59');
                break;

            case 'month':
                $start = $now->copy()->startOfMonth()->format('Y-m-d 00:00:00');
                $end = $now->copy()->endOfMonth()->format('Y-m-d 23:59:59');
                break;

            case 'quarter':
                $start = $now->copy()->startOfQuarter()->format('Y-m-d 00:00:00');
                $end = $now->copy()->endOfQuarter()->format('Y-m-d 23:59:59');
                break;

            case 'year':
                $start = $now->copy()->startOfYear()->format('Y-m-d 00:00:00');
                $end = $now->copy()->endOfYear()->format('Y-m-d 23:59:59');
                break;

            case 'custom':
                $start = $startDate . ' 00:00:00';
                $end = $endDate . ' 23:59:59';
                break;

            default:
                $start = $now->copy()->startOfWeek()->format('Y-m-d 00:00:00');
                $end = $now->copy()->endOfWeek()->format('Y-m-d 23:59:59');
        }

        $result = [
            'start' => $start,
            'end' => $end
        ];

        Log::debug('ReportController@getDateRange: Hasil date range', $result);
        return $result;
    }

    /**
     * Helper function to get previous period date range
     */
    private function getPreviousDateRange($period)
    {
        Log::debug('ReportController@getPreviousDateRange: Menghitung previous date range', ['period' => $period]);

        $now = now()->copy(); // PERBAIKAN 9: Gunakan copy() untuk avoid modification

        switch ($period) {
            case 'week':
                $start = $now->subWeek()->startOfWeek()->format('Y-m-d 00:00:00');
                $end = $now->endOfWeek()->format('Y-m-d 23:59:59');
                break;

            case 'month':
                $start = $now->subMonth()->startOfMonth()->format('Y-m-d 00:00:00');
                $end = $now->endOfMonth()->format('Y-m-d 23:59:59');
                break;

            case 'quarter':
                $start = $now->subQuarter()->startOfQuarter()->format('Y-m-d 00:00:00');
                $end = $now->endOfQuarter()->format('Y-m-d 23:59:59');
                break;

            case 'year':
                $start = $now->subYear()->startOfYear()->format('Y-m-d 00:00:00');
                $end = $now->endOfYear()->format('Y-m-d 23:59:59');
                break;

            default:
                $start = $now->subWeek()->startOfWeek()->format('Y-m-d 00:00:00');
                $end = $now->endOfWeek()->format('Y-m-d 23:59:59');
        }

        $result = [
            'start' => $start,
            'end' => $end
        ];

        Log::debug('ReportController@getPreviousDateRange: Hasil previous date range', $result);
        return $result;
    }

    /**
     * Translate status to Indonesian - PERBAIKAN 10: Sesuaikan dengan database
     */
    private function translateStatus($status)
    {
        $translations = [
            'new' => 'Baru',
            'process' => 'Diproses',
            'ready' => 'Siap Diambil',
            'done' => 'Selesai',
            'cancelled' => 'Dibatalkan'
        ];

        $result = $translations[$status] ?? $status;
        Log::debug('ReportController@translateStatus: Translation', [
            'original' => $status,
            'translated' => $result
        ]);

        return $result;
    }

    /**
     * Translate payment status to Indonesian
     */
    private function translatePaymentStatus($paymentStatus)
    {
        $translations = [
            'pending' => 'Belum Bayar',
            'paid' => 'Lunas',
            'partial' => 'DP',
            'overpaid' => 'Kelebihan'
        ];

        $result = $translations[$paymentStatus] ?? $paymentStatus;
        Log::debug('ReportController@translatePaymentStatus: Translation', [
            'original' => $paymentStatus,
            'translated' => $result
        ]);

        return $result;
    }


    /**
     * Export report to CSV/PDF/Excel
     */
    public function exportReport(Request $request)
    {
        Log::debug('ReportController@exportReport: Memulai proses export report');
        Log::debug('ReportController@exportReport: Request parameters', $request->all());

        $validator = Validator::make($request->all(), [
            'period' => 'required|in:week,month,quarter,year,custom',
            'start_date' => 'required_if:period,custom|date',
            'end_date' => 'required_if:period,custom|date',
            'format' => 'required|in:csv,pdf,excel'
        ]);

        if ($validator->fails()) {
            Log::warning('ReportController@exportReport: Validasi export gagal', [
                'errors' => $validator->errors()->toArray(),
                'input_data' => $request->all()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $period = $request->input('period');
        $format = $request->input('format');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        Log::debug('ReportController@exportReport: Parameter export', [
            'period' => $period,
            'format' => $format,
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);

        $dateRange = $this->getDateRange($period, $startDate, $endDate);
        Log::debug('ReportController@exportReport: Date range export', $dateRange);

        try {
            // Sesuaikan status untuk export
            $statuses = ['ready', 'done'];
            $paymentStatuses = ['paid', 'partial'];

            // Get detailed transactions for export
            Log::debug('ReportController@exportReport: Menjalankan query export data');
            $exportQuery = "
            SELECT 
                t.transaction_number,
                DATE(t.created_at) as transaction_date,
                c.name as customer_name,
                c.phone as customer_phone,
                s.name as service_name,
                t.total_amount,
                t.status,
                t.payment_status,
                t.notes
            FROM transactions t
            INNER JOIN customers c ON t.customer_id = c.id
            INNER JOIN services s ON t.service_id = s.id
            WHERE t.status IN ('" . implode("','", $statuses) . "')
            AND t.payment_status IN ('" . implode("','", $paymentStatuses) . "')
            AND t.created_at BETWEEN ? AND ?
            ORDER BY t.created_at DESC
        ";

            $transactions = DB::select($exportQuery, [$dateRange['start'], $dateRange['end']]);
            Log::debug('ReportController@exportReport: Data transaksi untuk export', [
                'transactions_count' => count($transactions)
            ]);

            // Get summary for export
            Log::debug('ReportController@exportReport: Menjalankan query summary untuk export');
            $summaryQuery = "
            SELECT 
                COUNT(*) as total_orders,
                COALESCE(SUM(total_amount), 0) as total_income,
                COUNT(DISTINCT customer_id) as total_customers
            FROM transactions 
            WHERE status IN ('" . implode("','", $statuses) . "')
            AND payment_status IN ('" . implode("','", $paymentStatuses) . "')
            AND created_at BETWEEN ? AND ?
        ";

            $summary = DB::select($summaryQuery, [$dateRange['start'], $dateRange['end']])[0];
            Log::debug('ReportController@exportReport: Summary untuk export', [
                'total_orders' => $summary->total_orders,
                'total_income' => $summary->total_income,
                'total_customers' => $summary->total_customers
            ]);

            // Handle multiple formats
            if ($format === 'csv') {
                Log::debug('ReportController@exportReport: Memulai generate CSV');
                return $this->generateCSV($transactions, $summary, $dateRange);
            } elseif ($format === 'pdf') {
                Log::debug('ReportController@exportReport: Format PDF dipilih');
                return $this->generatePDF($transactions, $summary, $dateRange);
            } elseif ($format === 'excel') {
                Log::debug('ReportController@exportReport: Format Excel dipilih');
                return $this->generateExcel($transactions, $summary, $dateRange);
            } else {
                Log::warning('ReportController@exportReport: Format export tidak didukung', ['format' => $format]);
                return response()->json([
                    'success' => false,
                    'message' => 'Format export tidak didukung'
                ], 400);
            }

        } catch (\Exception $e) {
            Log::error('ReportController@exportReport: Gagal mengekspor laporan', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'period' => $period,
                'format' => $format,
                'date_range' => $dateRange
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengekspor laporan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate PDF file for export
     */
    private function generatePDF($transactions, $summary, $dateRange)
    {
        Log::debug('ReportController@generatePDF: Memulai generate PDF');

        // Untuk sementara, kita akan generate HTML dan force download
        // Nanti bisa diimplementasi dengan DomPDF atau library lainnya

        $html = $this->generatePDFHTML($transactions, $summary, $dateRange);

        $fileName = 'laporan-keuangan-' . $dateRange['start'] . '-to-' . $dateRange['end'] . '.html';

        return response($html, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }

    /**
     * Generate Excel file for export
     */
    private function generateExcel($transactions, $summary, $dateRange)
    {
        Log::debug('ReportController@generateExcel: Memulai generate Excel');

        // Untuk sementara, kita akan return CSV dengan header Excel
        // Nanti bisa diimplementasi dengan Maatwebsite/Laravel-Excel

        $fileName = 'laporan-keuangan-' . $dateRange['start'] . '-to-' . $dateRange['end'] . '.xlsx';

        return $this->generateCSV($transactions, $summary, $dateRange, $fileName);
    }

    /**
     * Generate CSV file for export - PERBAIKAN: Support custom filename
     */
    private function generateCSV($transactions, $summary, $dateRange, $customFileName = null)
    {
        Log::debug('ReportController@generateCSV: Memulai generate file CSV', [
            'transactions_count' => count($transactions),
            'date_range' => $dateRange
        ]);

        $fileName = $customFileName ?: 'laporan-keuangan-' . $dateRange['start'] . '-to-' . $dateRange['end'] . '.csv';
        Log::debug('ReportController@generateCSV: Nama file yang akan dihasilkan', ['file_name' => $fileName]);

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ];

        $callback = function () use ($transactions, $summary, $dateRange) {
            $file = fopen('php://output', 'w');

            // BOM for UTF-8
            fwrite($file, "\xEF\xBB\xBF");

            // Header
            fputcsv($file, ['LAPORAN KEUANGAN LAUNDRY']);
            fputcsv($file, ['Periode: ' . $dateRange['start'] . ' hingga ' . $dateRange['end']]);
            fputcsv($file, ['']);
            fputcsv($file, ['SUMMARY']);
            fputcsv($file, ['Total Pendapatan', 'Rp ' . number_format($summary->total_income, 0, ',', '.')]);
            fputcsv($file, ['Total Pesanan', $summary->total_orders]);
            fputcsv($file, ['Total Pelanggan', $summary->total_customers]);
            fputcsv($file, ['']);
            fputcsv($file, ['DETAIL TRANSAKSI']);
            fputcsv($file, [
                'No. Transaksi',
                'Tanggal',
                'Pelanggan',
                'Telepon',
                'Layanan',
                'Total',
                'Status',
                'Status Pembayaran',
                'Catatan'
            ]);

            // Data rows
            foreach ($transactions as $transaction) {
                fputcsv($file, [
                    $transaction->transaction_number,
                    $transaction->transaction_date,
                    $transaction->customer_name,
                    $transaction->customer_phone,
                    $transaction->service_name,
                    'Rp ' . number_format($transaction->total_amount, 0, ',', '.'),
                    $this->translateStatus($transaction->status),
                    $this->translatePaymentStatus($transaction->payment_status),
                    $transaction->notes ?? '-'
                ]);
            }

            fclose($file);
        };

        Log::debug('ReportController@generateCSV: CSV generation completed');
        return response()->stream($callback, 200, $headers);
    }

    /**
     * Generate HTML content for PDF (temporary solution)
     */
    private function generatePDFHTML($transactions, $summary, $dateRange)
    {
        $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <title>Laporan Keuangan Laundry</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            .header { text-align: center; margin-bottom: 30px; }
            .summary { margin-bottom: 30px; }
            .summary-table { width: 100%; border-collapse: collapse; }
            .summary-table td { padding: 8px; border: 1px solid #ddd; }
            .transactions-table { width: 100%; border-collapse: collapse; }
            .transactions-table th, .transactions-table td { padding: 8px; border: 1px solid #ddd; text-align: left; }
            .transactions-table th { background-color: #f5f5f5; }
        </style>
    </head>
    <body>
        <div class="header">
            <h1>LAPORAN KEUANGAN LAUNDRY</h1>
            <p>Periode: ' . $dateRange['start'] . ' hingga ' . $dateRange['end'] . '</p>
        </div>
        
        <div class="summary">
            <h2>SUMMARY</h2>
            <table class="summary-table">
                <tr>
                    <td><strong>Total Pendapatan</strong></td>
                    <td>Rp ' . number_format($summary->total_income, 0, ',', '.') . '</td>
                </tr>
                <tr>
                    <td><strong>Total Pesanan</strong></td>
                    <td>' . $summary->total_orders . '</td>
                </tr>
                <tr>
                    <td><strong>Total Pelanggan</strong></td>
                    <td>' . $summary->total_customers . '</td>
                </tr>
            </table>
        </div>
        
        <div class="transactions">
            <h2>DETAIL TRANSAKSI</h2>
            <table class="transactions-table">
                <thead>
                    <tr>
                        <th>No. Transaksi</th>
                        <th>Tanggal</th>
                        <th>Pelanggan</th>
                        <th>Telepon</th>
                        <th>Layanan</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Status Pembayaran</th>
                        <th>Catatan</th>
                    </tr>
                </thead>
                <tbody>';

        foreach ($transactions as $transaction) {
            $html .= '
                    <tr>
                        <td>' . $transaction->transaction_number . '</td>
                        <td>' . $transaction->transaction_date . '</td>
                        <td>' . $transaction->customer_name . '</td>
                        <td>' . $transaction->customer_phone . '</td>
                        <td>' . $transaction->service_name . '</td>
                        <td>Rp ' . number_format($transaction->total_amount, 0, ',', '.') . '</td>
                        <td>' . $this->translateStatus($transaction->status) . '</td>
                        <td>' . $this->translatePaymentStatus($transaction->payment_status) . '</td>
                        <td>' . ($transaction->notes ?? '-') . '</td>
                    </tr>';
        }

        $html .= '
                </tbody>
            </table>
        </div>
    </body>
    </html>';

        return $html;
    }
}