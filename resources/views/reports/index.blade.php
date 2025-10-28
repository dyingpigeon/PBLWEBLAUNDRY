@extends('layouts.mobile')

@section('title', 'Laporan Keuangan')

@section('content')
<div class="pb-20">
    <!-- Period Selector -->
    <div class="bg-white px-4 py-3 border-b border-gray-200 sticky top-0 z-10">
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-lg font-semibold text-gray-800">Laporan Keuangan</h2>
            <button id="exportBtn" class="bg-green-500 text-white px-4 py-2 rounded-lg text-sm font-semibold">
                <i class="fas fa-download mr-1"></i>Export
            </button>
        </div>

        <!-- Date Range Picker -->
        <div class="flex items-center space-x-2 mb-3">
            <button id="prevPeriod" class="w-8 h-8 flex items-center justify-center text-gray-500 hover:text-gray-700">
                <i class="fas fa-chevron-left"></i>
            </button>
            
            <div class="flex-1">
                <button id="dateRangeBtn" class="w-full bg-gray-100 border border-gray-300 rounded-lg py-2 px-3 text-sm text-gray-700 flex items-center justify-center">
                    <i class="fas fa-calendar-alt mr-2 text-gray-500"></i>
                    <span id="dateRangeText">Minggu Ini</span>
                    <i class="fas fa-chevron-down ml-2 text-gray-500 text-xs"></i>
                </button>
            </div>
            
            <button id="nextPeriod" class="w-8 h-8 flex items-center justify-center text-gray-500 hover:text-gray-700">
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>

        <!-- Quick Period Tabs -->
        <div class="swipeable-periods flex overflow-x-auto space-x-2 pb-1" style="scrollbar-width: none;">
            <button class="period-tab flex-shrink-0 px-3 py-1 bg-blue-100 text-blue-600 rounded-full text-sm font-medium active-period">
                Minggu
            </button>
            <button class="period-tab flex-shrink-0 px-3 py-1 bg-gray-100 text-gray-600 rounded-full text-sm font-medium">
                Bulan
            </button>
            <button class="period-tab flex-shrink-0 px-3 py-1 bg-gray-100 text-gray-600 rounded-full text-sm font-medium">
                3 Bulan
            </button>
            <button class="period-tab flex-shrink-0 px-3 py-1 bg-gray-100 text-gray-600 rounded-full text-sm font-medium">
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
                        <p class="text-xl font-bold text-gray-800 mt-1" id="totalIncome">Rp 2.450.000</p>
                        <p class="text-xs text-green-500 mt-1">
                            <i class="fas fa-arrow-up mr-1"></i>12% vs minggu lalu
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
                        <p class="text-xl font-bold text-gray-800 mt-1" id="totalOrders">124</p>
                        <p class="text-xs text-blue-500 mt-1">
                            <i class="fas fa-chart-line mr-1"></i>8% vs minggu lalu
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
                <span class="text-xs text-gray-500">7 hari terakhir</span>
            </div>
            <div class="h-40" id="revenueChart">
                <!-- Chart will be rendered here -->
            </div>
        </div>

        <!-- Orders Chart -->
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-semibold text-gray-800">Pesanan per Layanan</h3>
                <span class="text-xs text-gray-500">Minggu ini</span>
            </div>
            <div class="h-40" id="servicesChart">
                <!-- Chart will be rendered here -->
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
                <!-- Transactions will be loaded here -->
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
let currentPeriod = 'week';
let currentDateRange = getCurrentWeekRange();
let revenueChart, servicesChart;

// Sample data
const sampleData = {
    week: {
        revenue: [450000, 520000, 380000, 610000, 550000, 480000, 590000],
        services: {
            'Cuci Setrika': 45,
            'Cuci Biasa': 32,
            'Setrika Saja': 28,
            'Dry Clean': 19
        },
        transactions: [
            { date: '15 Jan', customer: 'Budi Santoso', service: 'Cuci Setrika', amount: 40000, status: 'completed' },
            { date: '15 Jan', customer: 'Siti Rahayu', service: 'Setrika Saja', amount: 30000, status: 'completed' },
            { date: '14 Jan', customer: 'Ahmad Fauzi', service: 'Dry Clean', amount: 75000, status: 'completed' },
            { date: '14 Jan', customer: 'Dewi Lestari', service: 'Cuci Biasa', amount: 35000, status: 'completed' },
            { date: '13 Jan', customer: 'Rizki Pratama', service: 'Cuci Setrika', amount: 55000, status: 'completed' }
        ]
    },
    month: {
        revenue: [420000, 480000, 510000, 390000, 450000, 520000, 380000, 610000, 550000, 480000, 590000, 520000, 450000, 510000, 480000, 420000, 460000, 490000, 530000, 470000, 440000, 510000, 480000, 520000, 460000, 490000, 510000, 540000, 470000, 520000],
        services: {
            'Cuci Setrika': 120,
            'Cuci Biasa': 85,
            'Setrika Saja': 65,
            'Dry Clean': 40
        },
        transactions: [] // Would be filled with month data
    }
};

document.addEventListener('DOMContentLoaded', function() {
    initializeCharts();
    loadReportData();
    setupEventListeners();
    setupSwipeGestures();
});

// Initialize charts
function initializeCharts() {
    // Revenue Chart
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    revenueChart = new Chart(revenueCtx, {
        type: 'bar',
        data: {
            labels: ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'],
            datasets: [{
                label: 'Pendapatan',
                data: [],
                backgroundColor: '#3b82f6',
                borderColor: '#3b82f6',
                borderWidth: 0,
                borderRadius: 4,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return `Rp ${context.raw.toLocaleString()}`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0,0,0,0.1)'
                    },
                    ticks: {
                        callback: function(value) {
                            return 'Rp ' + (value / 1000) + 'k';
                        }
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });

    // Services Chart
    const servicesCtx = document.getElementById('servicesChart').getContext('2d');
    servicesChart = new Chart(servicesCtx, {
        type: 'doughnut',
        data: {
            labels: [],
            datasets: [{
                data: [],
                backgroundColor: [
                    '#3b82f6', '#10b981', '#f59e0b', '#8b5cf6'
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '60%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        boxWidth: 12,
                        padding: 15
                    }
                }
            }
        }
    });
}

// Setup event listeners
function setupEventListeners() {
    // Period tabs
    const periodTabs = document.querySelectorAll('.period-tab');
    periodTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            periodTabs.forEach(t => t.classList.remove('active-period'));
            this.classList.add('active-period');
            
            currentPeriod = this.textContent.toLowerCase().includes('minggu') ? 'week' :
                          this.textContent.toLowerCase().includes('bulan') ? 'month' : 'custom';
            
            loadReportData();
        });
    });

    // Date range picker
    document.getElementById('dateRangeBtn').addEventListener('click', function() {
        document.getElementById('dateModal').classList.remove('hidden');
    });

    // Navigation buttons
    document.getElementById('prevPeriod').addEventListener('click', navigatePeriod(-1));
    document.getElementById('nextPeriod').addEventListener('click', navigatePeriod(1));

    // Export button
    document.getElementById('exportBtn').addEventListener('click', function() {
        document.getElementById('exportModal').classList.remove('hidden');
    });
}

// Setup swipe gestures for periods
function setupSwipeGestures() {
    const periodsContainer = document.querySelector('.swipeable-periods');
    let startX;

    periodsContainer.addEventListener('touchstart', e => {
        startX = e.touches[0].clientX;
    });

    periodsContainer.addEventListener('touchend', e => {
        if (!startX) return;
        
        const endX = e.changedTouches[0].clientX;
        const diff = startX - endX;
        
        if (Math.abs(diff) > 50) { // Minimum swipe distance
            const periodTabs = document.querySelectorAll('.period-tab');
            const activeIndex = Array.from(periodTabs).findIndex(tab => 
                tab.classList.contains('active-period')
            );
            
            if (diff > 0 && activeIndex < periodTabs.length - 1) {
                // Swipe left - next period
                periodTabs[activeIndex + 1].click();
            } else if (diff < 0 && activeIndex > 0) {
                // Swipe right - previous period
                periodTabs[activeIndex - 1].click();
            }
        }
        
        startX = null;
    });
}

// Load report data based on current period
function loadReportData() {
    const data = sampleData[currentPeriod];
    
    // Update summary cards
    const totalIncome = data.revenue.reduce((sum, val) => sum + val, 0);
    const totalOrders = data.transactions.length;
    
    document.getElementById('totalIncome').textContent = `Rp ${totalIncome.toLocaleString()}`;
    document.getElementById('totalOrders').textContent = totalOrders;
    
    // Update charts
    updateRevenueChart(data.revenue);
    updateServicesChart(data.services);
    
    // Update transactions list
    updateTransactionsList(data.transactions);
    
    // Update date range text
    updateDateRangeText();
}

// Update revenue chart
function updateRevenueChart(revenueData) {
    revenueChart.data.datasets[0].data = revenueData;
    revenueChart.update();
}

// Update services chart
function updateServicesChart(servicesData) {
    servicesChart.data.labels = Object.keys(servicesData);
    servicesChart.data.datasets[0].data = Object.values(servicesData);
    servicesChart.update();
}

// Update transactions list
function updateTransactionsList(transactions) {
    const container = document.getElementById('transactionsList');
    
    if (transactions.length === 0) {
        container.innerHTML = `
            <div class="p-4 text-center text-gray-500">
                <i class="fas fa-receipt text-2xl mb-2"></i>
                <p>Tidak ada transaksi</p>
            </div>
        `;
        return;
    }
    
    container.innerHTML = transactions.map(transaction => `
        <div class="p-4 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-receipt text-blue-600"></i>
                </div>
                <div>
                    <p class="font-medium text-gray-800">${transaction.customer}</p>
                    <p class="text-sm text-gray-500">${transaction.service}</p>
                    <p class="text-xs text-gray-400">${transaction.date}</p>
                </div>
            </div>
            <div class="text-right">
                <p class="font-semibold text-gray-800">Rp ${transaction.amount.toLocaleString()}</p>
                <span class="inline-block px-2 py-1 bg-green-100 text-green-600 text-xs rounded-full">
                    Selesai
                </span>
            </div>
        </div>
    `).join('');
}

// Date range functions
function getCurrentWeekRange() {
    const now = new Date();
    const start = new Date(now);
    start.setDate(now.getDate() - now.getDay());
    
    const end = new Date(now);
    end.setDate(now.getDate() + (6 - now.getDay()));
    
    return { start, end };
}

function updateDateRangeText() {
    const rangeText = document.getElementById('dateRangeText');
    const { start, end } = currentDateRange;
    
    if (currentPeriod === 'week') {
        rangeText.textContent = 'Minggu Ini';
    } else if (currentPeriod === 'month') {
        rangeText.textContent = 'Bulan Ini';
    } else {
        rangeText.textContent = `${formatDate(start)} - ${formatDate(end)}`;
    }
}

function formatDate(date) {
    return date.toLocaleDateString('id-ID', { 
        day: 'numeric', 
        month: 'short' 
    });
}

// Period navigation
function navigatePeriod(direction) {
    return function() {
        const { start, end } = currentDateRange;
        
        if (currentPeriod === 'week') {
            start.setDate(start.getDate() + (7 * direction));
            end.setDate(end.getDate() + (7 * direction));
        } else if (currentPeriod === 'month') {
            start.setMonth(start.getMonth() + direction);
            end.setMonth(end.getMonth() + direction);
        }
        
        loadReportData();
    };
}

// Export functions
function exportReport(format) {
    const data = sampleData[currentPeriod];
    const totalIncome = data.revenue.reduce((sum, val) => sum + val, 0);
    
    let content = `LAPORAN LAUNDRY\n`;
    content += `Periode: ${document.getElementById('dateRangeText').textContent}\n`;
    content += `Total Pendapatan: Rp ${totalIncome.toLocaleString()}\n`;
    content += `Total Pesanan: ${data.transactions.length}\n\n`;
    content += `Detail Transaksi:\n`;
    
    data.transactions.forEach(transaction => {
        content += `${transaction.date} - ${transaction.customer} - ${transaction.service} - Rp ${transaction.amount.toLocaleString()}\n`;
    });
    
    if (format === 'pdf') {
        // Simulate PDF export
        alert('Fitur export PDF akan segera hadir!');
    } else {
        // CSV export
        const blob = new Blob([content], { type: 'text/csv' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `laporan-laundry-${new Date().toISOString().split('T')[0]}.csv`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    }
    
    closeExportModal();
}
</script>
@endpush