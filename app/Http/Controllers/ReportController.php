<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ReportController extends Controller
{

    public function index()
    {
        return view('reports.index'); // Sesuaikan dengan nama view Anda
    }
    /**
     * Get financial report summary
     */
    public function getFinancialSummary(Request $request)
    {
        // Gunakan input() method untuk mengakses request data
        $validator = Validator::make($request->all(), [
            'period' => 'required|in:week,month,quarter,year,custom',
            'start_date' => 'required_if:period,custom|date',
            'end_date' => 'required_if:period,custom|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        // PERBAIKAN: Gunakan input() method
        $period = $request->input('period');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $dateRange = $this->getDateRange($period, $startDate, $endDate);

        try {
            // Get total income and orders
            $summaryQuery = "
                SELECT 
                    COUNT(*) as total_orders,
                    COALESCE(SUM(total_amount), 0) as total_income,
                    COUNT(DISTINCT customer_id) as total_customers
                FROM transactions 
                WHERE status IN ('completed', 'ready', 'picked_up')
                AND payment_status IN ('paid', 'partial')
                AND created_at BETWEEN ? AND ?
            ";

            $summary = DB::select($summaryQuery, [$dateRange['start'], $dateRange['end']])[0];

            // Get daily revenue for chart
            $revenueChartQuery = "
                SELECT 
                    DATE(created_at) as date,
                    COALESCE(SUM(total_amount), 0) as daily_income,
                    COUNT(*) as daily_orders
                FROM transactions 
                WHERE status IN ('completed', 'ready', 'picked_up')
                AND payment_status IN ('paid', 'partial')
                AND created_at BETWEEN ? AND ?
                GROUP BY DATE(created_at)
                ORDER BY date
            ";

            $revenueData = DB::select($revenueChartQuery, [$dateRange['start'], $dateRange['end']]);

            // Get service distribution
            $servicesQuery = "
                SELECT 
                    s.name as service_name,
                    COUNT(t.id) as order_count,
                    COALESCE(SUM(t.total_amount), 0) as total_revenue
                FROM transactions t
                INNER JOIN services s ON t.service_id = s.id
                WHERE t.status IN ('completed', 'ready', 'picked_up')
                AND t.payment_status IN ('paid', 'partial')
                AND t.created_at BETWEEN ? AND ?
                GROUP BY s.id, s.name
                ORDER BY order_count DESC
            ";

            $servicesData = DB::select($servicesQuery, [$dateRange['start'], $dateRange['end']]);

            // Get recent transactions
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
                WHERE t.status IN ('completed', 'ready', 'picked_up')
                AND t.payment_status IN ('paid', 'partial')
                AND t.created_at BETWEEN ? AND ?
                ORDER BY t.created_at DESC
                LIMIT 50
            ";

            $transactions = DB::select($transactionsQuery, [$dateRange['start'], $dateRange['end']]);

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
        try {
            $today = now()->format('Y-m-d');
            $tomorrow = now()->addDay()->format('Y-m-d');

            $summaryQuery = "
                SELECT 
                    COUNT(*) as total_transactions,
                    SUM(CASE WHEN status IN ('new', 'washing', 'ironing') THEN 1 ELSE 0 END) as processing_count,
                    COALESCE(SUM(CASE WHEN payment_status = 'paid' THEN total_amount ELSE 0 END), 0) as total_income
                FROM transactions 
                WHERE DATE(created_at) = ?
            ";

            $summary = DB::select($summaryQuery, [$today])[0];

            return response()->json([
                'success' => true,
                'data' => [
                    'total_transactions' => (int) $summary->total_transactions,
                    'processing_count' => (int) $summary->processing_count,
                    'total_income' => (float) $summary->total_income
                ]
            ]);

        } catch (\Exception $e) {
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
        $validator = Validator::make($request->all(), [
            'period' => 'required|in:week,month,quarter,year',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        // PERBAIKAN: Gunakan input() method
        $period = $request->input('period');

        $currentRange = $this->getDateRange($period);
        $previousRange = $this->getPreviousDateRange($period);

        try {
            // Current period revenue
            $currentQuery = "
                SELECT COALESCE(SUM(total_amount), 0) as total_income
                FROM transactions 
                WHERE status IN ('completed', 'ready', 'picked_up')
                AND payment_status IN ('paid', 'partial')
                AND created_at BETWEEN ? AND ?
            ";

            $currentRevenue = DB::select($currentQuery, [$currentRange['start'], $currentRange['end']])[0];
            $previousRevenue = DB::select($currentQuery, [$previousRange['start'], $previousRange['end']])[0];

            $currentIncome = (float) $currentRevenue->total_income;
            $previousIncome = (float) $previousRevenue->total_income;

            $growth = $previousIncome > 0 ? (($currentIncome - $previousIncome) / $previousIncome) * 100 : 0;

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
                    'growth_amount' => $currentIncome - $previousIncome
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil perbandingan revenue',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export report to CSV
     */
    public function exportReport(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'period' => 'required|in:week,month,quarter,year,custom',
            'start_date' => 'required_if:period,custom|date',
            'end_date' => 'required_if:period,custom|date',
            'format' => 'required|in:csv,pdf'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        // PERBAIKAN: Gunakan input() method
        $period = $request->input('period');
        $format = $request->input('format');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $dateRange = $this->getDateRange($period, $startDate, $endDate);

        try {
            // Get detailed transactions for export
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
                WHERE t.status IN ('completed', 'ready', 'picked_up')
                AND t.payment_status IN ('paid', 'partial')
                AND t.created_at BETWEEN ? AND ?
                ORDER BY t.created_at DESC
            ";

            $transactions = DB::select($exportQuery, [$dateRange['start'], $dateRange['end']]);

            // Get summary for export
            $summaryQuery = "
                SELECT 
                    COUNT(*) as total_orders,
                    COALESCE(SUM(total_amount), 0) as total_income,
                    COUNT(DISTINCT customer_id) as total_customers
                FROM transactions 
                WHERE status IN ('completed', 'ready', 'picked_up')
                AND payment_status IN ('paid', 'partial')
                AND created_at BETWEEN ? AND ?
            ";

            $summary = DB::select($summaryQuery, [$dateRange['start'], $dateRange['end']])[0];

            if ($format === 'csv') {
                return $this->generateCSV($transactions, $summary, $dateRange);
            }

            return response()->json([
                'success' => false,
                'message' => 'Format export tidak didukung'
            ], 400);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengekspor laporan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate CSV file for export
     */
    private function generateCSV($transactions, $summary, $dateRange)
    {
        $fileName = 'laporan-keuangan-' . $dateRange['start'] . '-to-' . $dateRange['end'] . '.csv';

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

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Helper function to get date range based on period
     */
    private function getDateRange($period, $startDate = null, $endDate = null)
    {
        $now = now();

        switch ($period) {
            case 'week':
                $start = $now->startOfWeek()->format('Y-m-d 00:00:00');
                $end = $now->endOfWeek()->format('Y-m-d 23:59:59');
                break;

            case 'month':
                $start = $now->startOfMonth()->format('Y-m-d 00:00:00');
                $end = $now->endOfMonth()->format('Y-m-d 23:59:59');
                break;

            case 'quarter':
                $start = $now->startOfQuarter()->format('Y-m-d 00:00:00');
                $end = $now->endOfQuarter()->format('Y-m-d 23:59:59');
                break;

            case 'year':
                $start = $now->startOfYear()->format('Y-m-d 00:00:00');
                $end = $now->endOfYear()->format('Y-m-d 23:59:59');
                break;

            case 'custom':
                $start = $startDate . ' 00:00:00';
                $end = $endDate . ' 23:59:59';
                break;

            default:
                $start = $now->startOfWeek()->format('Y-m-d 00:00:00');
                $end = $now->endOfWeek()->format('Y-m-d 23:59:59');
        }

        return [
            'start' => $start,
            'end' => $end
        ];
    }

    /**
     * Helper function to get previous period date range
     */
    private function getPreviousDateRange($period)
    {
        $now = now();

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

        return [
            'start' => $start,
            'end' => $end
        ];
    }

    /**
     * Translate status to Indonesian
     */
    private function translateStatus($status)
    {
        $translations = [
            'new' => 'Baru',
            'washing' => 'Dicuci',
            'ironing' => 'Disetrika',
            'ready' => 'Selesai',
            'picked_up' => 'Diambil',
            'completed' => 'Selesai'
        ];

        return $translations[$status] ?? $status;
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

        return $translations[$paymentStatus] ?? $paymentStatus;
    }
}