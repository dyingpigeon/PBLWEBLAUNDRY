@extends('layouts.mobile')

@section('title', 'Laporan Keuangan')

@section('content')
<div class="pb-20">
    <!-- Period Selector -->
    <div class="bg-white px-4 py-3 border-b border-gray-200 sticky top-0 z-10">
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-lg font-semibold text-gray-800">Laporan Keuangan</h2>
            <button id="exportBtn" type="button" class="bg-green-500 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-green-600 transition-colors">
                <i class="fas fa-download mr-1"></i>Export
            </button>
        </div>

        <!-- Date Range Picker -->
        <div class="flex items-center space-x-2 mb-3">
            <button id="prevPeriod" type="button" class="w-8 h-8 flex items-center justify-center text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-full transition-colors">
                <i class="fas fa-chevron-left"></i>
            </button>
            
            <div class="flex-1">
                <button id="dateRangeBtn" type="button" class="w-full bg-gray-100 border border-gray-300 rounded-lg py-2 px-3 text-sm text-gray-700 flex items-center justify-center hover:bg-gray-200 transition-colors">
                    <i class="fas fa-calendar-alt mr-2 text-gray-500"></i>
                    <span id="dateRangeText">Minggu Ini</span>
                    <i class="fas fa-chevron-down ml-2 text-gray-500 text-xs"></i>
                </button>
            </div>
            
            <button id="nextPeriod" type="button" class="w-8 h-8 flex items-center justify-center text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-full transition-colors">
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>

        <!-- Quick Period Tabs -->
        <div class="swipeable-periods flex overflow-x-auto space-x-2 pb-1" style="scrollbar-width: none;">
            <button type="button" class="period-tab flex-shrink-0 px-3 py-1 bg-blue-100 text-blue-600 rounded-full text-sm font-medium active-period hover:bg-blue-200 transition-colors">
                Minggu
            </button>
            <button type="button" class="period-tab flex-shrink-0 px-3 py-1 bg-gray-100 text-gray-600 rounded-full text-sm font-medium hover:bg-gray-200 transition-colors">
                Bulan
            </button>
            <button type="button" class="period-tab flex-shrink-0 px-3 py-1 bg-gray-100 text-gray-600 rounded-full text-sm font-medium hover:bg-gray-200 transition-colors">
                3 Bulan
            </button>
            <button type="button" class="period-tab flex-shrink-0 px-3 py-1 bg-gray-100 text-gray-600 rounded-full text-sm font-medium hover:bg-gray-200 transition-colors">
                Custom
            </button>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="px-4 py-3">
        <div class="grid grid-cols-2 gap-3">
            <!-- Total Pendapatan -->
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Total Pendapatan</p>
                        <p class="text-xl font-bold text-gray-800 mt-1" id="totalIncome">Rp 0</p>
                        <p class="text-xs text-green-500 mt-1" id="incomeGrowth">
                            <i class="fas fa-arrow-up mr-1"></i>0% vs periode lalu
                        </p>
                    </div>
                    <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-money-bill-wave text-green-600"></i>
                    </div>
                </div>
            </div>

            <!-- Total Pesanan -->
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Total Pesanan</p>
                        <p class="text-xl font-bold text-gray-800 mt-1" id="totalOrders">0</p>
                        <p class="text-xs text-blue-500 mt-1" id="ordersGrowth">
                            <i class="fas fa-chart-line mr-1"></i>0% vs periode lalu
                        </p>
                    </div>
                    <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-shopping-bag text-blue-600"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="px-4 py-3">
        <!-- Revenue Chart -->
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 mb-3">
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-semibold text-gray-800">Pendapatan Harian</h3>
                <span class="text-xs text-gray-500" id="revenueChartPeriod">7 hari terakhir</span>
            </div>
            <div class="h-40">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>

        <!-- Orders Chart -->
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-semibold text-gray-800">Pesanan per Layanan</h3>
                <span class="text-xs text-gray-500" id="servicesChartPeriod">Minggu ini</span>
            </div>
            <div class="h-40">
                <canvas id="servicesChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Detailed Report -->
    <div class="px-4 py-3">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100">
            <div class="p-4 border-b border-gray-200">
                <h3 class="font-semibold text-gray-800">Detail Transaksi</h3>
            </div>
            <div class="divide-y divide-gray-100" id="transactionsList">
                <!-- Loading state -->
                <div class="p-4 text-center text-gray-500">
                    <i class="fas fa-spinner fa-spin text-xl mb-2"></i>
                    <p>Memuat data transaksi...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Date Picker Modal -->
@include('partials.reports-date-modal')

<!-- Export Options Modal -->
@include('partials.reports-export-modal')
@endsection

@push('styles')
<style>
.swipeable-periods::-webkit-scrollbar {
    display: none;
}
.period-tab.active-period {
    background-color: #3b82f6;
    color: white;
}
.chart-bar {
    transition: all 0.3s ease;
}
.chart-bar:hover {
    opacity: 0.8;
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    {!! file_get_contents(resource_path('js/reportsPage.js')) !!}
</script>
@endpush