// reportsPage.js
// Handle halaman laporan keuangan (charts, period selection, export)

let currentPeriod = 'week';
let currentDateRange = getCurrentWeekRange();
let revenueChart, servicesChart;

// Sample data sebagai fallback
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
        transactions: []
    }
};

// ===== INITIALIZATION FUNCTIONS =====

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Tunggu sebentar untuk memastikan DOM selesai load
    setTimeout(() => {
        initializeCharts();
        loadReportDataFromAPI();
        setupEventListeners();
        setupSwipeGestures();
    }, 100);
});

// Initialize charts dengan error handling
function initializeCharts() {
    try {
        // Revenue Chart - Periksa apakah elemen ada dan adalah canvas
        const revenueCanvas = document.getElementById('revenueChart');
        if (!revenueCanvas) {
            console.error('Element dengan ID revenueChart tidak ditemukan');
            return;
        }
        
        if (revenueCanvas.tagName !== 'CANVAS') {
            console.error('Element revenueChart bukan canvas element');
            return;
        }

        const revenueCtx = revenueCanvas.getContext('2d');
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

        // Services Chart - Periksa apakah elemen ada dan adalah canvas
        const servicesCanvas = document.getElementById('servicesChart');
        if (!servicesCanvas) {
            console.error('Element dengan ID servicesChart tidak ditemukan');
            return;
        }
        
        if (servicesCanvas.tagName !== 'CANVAS') {
            console.error('Element servicesChart bukan canvas element');
            return;
        }

        const servicesCtx = servicesCanvas.getContext('2d');
        servicesChart = new Chart(servicesCtx, {
            type: 'doughnut',
            data: {
                labels: [],
                datasets: [{
                    data: [],
                    backgroundColor: [
                        '#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ef4444', '#06b6d4'
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

        console.log('Charts initialized successfully');
    } catch (error) {
        console.error('Error initializing charts:', error);
    }
}

// ===== EVENT HANDLING FUNCTIONS =====

// Setup event listeners
function setupEventListeners() {
    try {
        // Period tabs
        const periodTabs = document.querySelectorAll('.period-tab');
        periodTabs.forEach(tab => {
            tab.addEventListener('click', function() {
                periodTabs.forEach(t => t.classList.remove('active-period'));
                this.classList.add('active-period');
                
                currentPeriod = this.textContent.toLowerCase().includes('minggu') ? 'week' :
                              this.textContent.toLowerCase().includes('bulan') ? 'month' : 
                              this.textContent.toLowerCase().includes('3') ? 'quarter' : 'custom';
                
                loadReportDataFromAPI();
            });
        });

        // Date range picker
        const dateRangeBtn = document.getElementById('dateRangeBtn');
        if (dateRangeBtn) {
            dateRangeBtn.addEventListener('click', function() {
                const dateModal = document.getElementById('dateModal');
                if (dateModal) {
                    dateModal.classList.remove('hidden');
                }
            });
        }

        // Navigation buttons
        const prevPeriodBtn = document.getElementById('prevPeriod');
        const nextPeriodBtn = document.getElementById('nextPeriod');
        
        if (prevPeriodBtn) {
            prevPeriodBtn.addEventListener('click', navigatePeriod(-1));
        }
        if (nextPeriodBtn) {
            nextPeriodBtn.addEventListener('click', navigatePeriod(1));
        }

        // Export button
        const exportBtn = document.getElementById('exportBtn');
        if (exportBtn) {
            exportBtn.addEventListener('click', function() {
                const exportModal = document.getElementById('exportModal');
                if (exportModal) {
                    exportModal.classList.remove('hidden');
                }
            });
        }
    } catch (error) {
        console.error('Error setting up event listeners:', error);
    }
}

// Setup swipe gestures for periods
function setupSwipeGestures() {
    try {
        const periodsContainer = document.querySelector('.swipeable-periods');
        if (!periodsContainer) return;

        let startX;

        periodsContainer.addEventListener('touchstart', e => {
            startX = e.touches[0].clientX;
        });

        periodsContainer.addEventListener('touchend', e => {
            if (!startX) return;
            
            const endX = e.changedTouches[0].clientX;
            const diff = startX - endX;
            
            if (Math.abs(diff) > 50) {
                const periodTabs = document.querySelectorAll('.period-tab');
                const activeIndex = Array.from(periodTabs).findIndex(tab => 
                    tab.classList.contains('active-period')
                );
                
                if (diff > 0 && activeIndex < periodTabs.length - 1) {
                    periodTabs[activeIndex + 1].click();
                } else if (diff < 0 && activeIndex > 0) {
                    periodTabs[activeIndex - 1].click();
                }
            }
            
            startX = null;
        });
    } catch (error) {
        console.error('Error setting up swipe gestures:', error);
    }
}

// ===== API INTEGRATION FUNCTIONS =====

// Load report data from API
async function loadReportDataFromAPI() {
    try {
        showLoading();
        
        const params = new URLSearchParams({
            period: currentPeriod
        });

        // Tambahkan tanggal untuk custom period
        if (currentPeriod === 'custom' && currentDateRange.start && currentDateRange.end) {
            params.append('start_date', currentDateRange.start.toISOString().split('T')[0]);
            params.append('end_date', currentDateRange.end.toISOString().split('T')[0]);
        }

        const response = await fetch(`/api/reports/financial-summary?${params}`);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();

        if (data.success) {
            processAPIData(data.data);
        } else {
            throw new Error(data.message || 'Gagal memuat data');
        }
    } catch (error) {
        console.error('Error loading report data:', error);
        showError('Gagal memuat data laporan, menggunakan sample data');
        // Fallback to sample data
        loadSampleData();
    } finally {
        hideLoading();
    }
}

// Process API data
function processAPIData(apiData) {
    try {
        // Update summary cards
        const totalIncomeEl = document.getElementById('totalIncome');
        const totalOrdersEl = document.getElementById('totalOrders');
        
        if (totalIncomeEl) {
            totalIncomeEl.textContent = `Rp ${formatPrice(apiData.summary.total_income)}`;
        }
        if (totalOrdersEl) {
            totalOrdersEl.textContent = apiData.summary.total_orders;
        }

        // Process revenue chart data
        const revenueData = processRevenueChartData(apiData.revenue_chart);
        updateRevenueChart(revenueData);

        // Process services chart data
        const servicesData = processServicesChartData(apiData.services_distribution);
        updateServicesChart(servicesData);

        // Process transactions list
        updateTransactionsListFromAPI(apiData.recent_transactions);

        // Update current date range
        if (apiData.date_range) {
            currentDateRange = {
                start: new Date(apiData.date_range.start),
                end: new Date(apiData.date_range.end)
            };
            updateDateRangeText();
        }
    } catch (error) {
        console.error('Error processing API data:', error);
    }
}

// Process revenue chart data from API
function processRevenueChartData(revenueChartData) {
    if (!revenueChartData || revenueChartData.length === 0) {
        return sampleData[currentPeriod]?.revenue || [0,0,0,0,0,0,0];
    }

    // Jika data harian, extract daily_income
    return revenueChartData.map(item => parseFloat(item.daily_income) || 0);
}

// Process services chart data from API
function processServicesChartData(servicesData) {
    if (!servicesData || servicesData.length === 0) {
        return sampleData[currentPeriod]?.services || {};
    }

    const distribution = {};
    servicesData.forEach(service => {
        distribution[service.service_name] = service.order_count;
    });
    return distribution;
}

// Update transactions list from API data
function updateTransactionsListFromAPI(transactions) {
    try {
        const container = document.getElementById('transactionsList');
        if (!container) return;
        
        if (!transactions || transactions.length === 0) {
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
                        <p class="font-medium text-gray-800">${transaction.customer_name}</p>
                        <p class="text-sm text-gray-500">${transaction.service_name}</p>
                        <p class="text-xs text-gray-400">${new Date(transaction.created_at).toLocaleDateString('id-ID')}</p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="font-semibold text-gray-800">Rp ${formatPrice(transaction.total_amount)}</p>
                    <span class="inline-block px-2 py-1 bg-green-100 text-green-600 text-xs rounded-full">
                        ${translateStatus(transaction.status)}
                    </span>
                </div>
            </div>
        `).join('');
    } catch (error) {
        console.error('Error updating transactions list:', error);
    }
}

// ===== FALLBACK FUNCTIONS =====

// Load sample data as fallback
function loadSampleData() {
    try {
        const data = sampleData[currentPeriod] || sampleData.week;
        
        const totalIncome = data.revenue.reduce((sum, val) => sum + val, 0);
        const totalOrders = data.transactions.length;
        
        const totalIncomeEl = document.getElementById('totalIncome');
        const totalOrdersEl = document.getElementById('totalOrders');
        
        if (totalIncomeEl) {
            totalIncomeEl.textContent = `Rp ${formatPrice(totalIncome)}`;
        }
        if (totalOrdersEl) {
            totalOrdersEl.textContent = totalOrders;
        }
        
        updateRevenueChart(data.revenue);
        updateServicesChart(data.services);
        updateTransactionsList(data.transactions);
        updateDateRangeText();
    } catch (error) {
        console.error('Error loading sample data:', error);
    }
}

// ===== CHART UPDATE FUNCTIONS =====

// Update revenue chart
function updateRevenueChart(revenueData) {
    try {
        if (revenueChart && revenueData) {
            revenueChart.data.datasets[0].data = revenueData;
            revenueChart.update('none');
        }
    } catch (error) {
        console.error('Error updating revenue chart:', error);
    }
}

// Update services chart
function updateServicesChart(servicesData) {
    try {
        if (servicesChart && servicesData) {
            servicesChart.data.labels = Object.keys(servicesData);
            servicesChart.data.datasets[0].data = Object.values(servicesData);
            servicesChart.update('none');
        }
    } catch (error) {
        console.error('Error updating services chart:', error);
    }
}

// Update transactions list (untuk sample data)
function updateTransactionsList(transactions) {
    try {
        const container = document.getElementById('transactionsList');
        if (!container) return;
        
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
                    <p class="font-semibold text-gray-800">Rp ${formatPrice(transaction.amount)}</p>
                    <span class="inline-block px-2 py-1 bg-green-100 text-green-600 text-xs rounded-full">
                        Selesai
                    </span>
                </div>
            </div>
        `).join('');
    } catch (error) {
        console.error('Error updating transactions list:', error);
    }
}

// ===== DATE RANGE FUNCTIONS =====

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
    try {
        const rangeText = document.getElementById('dateRangeText');
        if (!rangeText) return;
        
        const { start, end } = currentDateRange;
        
        if (currentPeriod === 'week') {
            rangeText.textContent = 'Minggu Ini';
        } else if (currentPeriod === 'month') {
            rangeText.textContent = 'Bulan Ini';
        } else if (currentPeriod === 'quarter') {
            rangeText.textContent = '3 Bulan Ini';
        } else {
            rangeText.textContent = `${formatDate(start)} - ${formatDate(end)}`;
        }
    } catch (error) {
        console.error('Error updating date range text:', error);
    }
}

function formatDate(date) {
    if (!(date instanceof Date) || isNaN(date)) {
        return 'Invalid Date';
    }
    return date.toLocaleDateString('id-ID', { 
        day: 'numeric', 
        month: 'short' 
    });
}

// Period navigation
function navigatePeriod(direction) {
    return function() {
        try {
            const { start, end } = currentDateRange;
            
            if (currentPeriod === 'week') {
                start.setDate(start.getDate() + (7 * direction));
                end.setDate(end.getDate() + (7 * direction));
            } else if (currentPeriod === 'month') {
                start.setMonth(start.getMonth() + direction);
                end.setMonth(end.getMonth() + direction);
            } else if (currentPeriod === 'quarter') {
                start.setMonth(start.getMonth() + (3 * direction));
                end.setMonth(end.getMonth() + (3 * direction));
            }
            
            loadReportDataFromAPI();
        } catch (error) {
            console.error('Error navigating period:', error);
        }
    };
}

// ===== EXPORT FUNCTIONS =====

// Export via API
async function exportReport(format) {
    try {
        showLoading();
        
        const formData = new FormData();
        formData.append('period', currentPeriod);
        formData.append('format', format);
        
        if (currentPeriod === 'custom' && currentDateRange.start && currentDateRange.end) {
            formData.append('start_date', currentDateRange.start.toISOString().split('T')[0]);
            formData.append('end_date', currentDateRange.end.toISOString().split('T')[0]);
        }

        const response = await fetch('/api/reports/export', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': getCsrfToken()
            }
        });

        if (response.ok) {
            // Handle CSV download
            const blob = await response.blob();
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `laporan-keuangan-${new Date().toISOString().split('T')[0]}.csv`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);
            
            showToast('Laporan berhasil diexport');
        } else {
            const errorData = await response.json();
            throw new Error(errorData.message || 'Export gagal');
        }
        
    } catch (error) {
        console.error('Error exporting report:', error);
        showError('Gagal mengekspor laporan: ' + error.message);
        
        // Fallback to client-side export
        if (format === 'csv') {
            exportCSVClientSide();
        } else {
            alert('Fitur export PDF akan segera hadir!');
        }
    } finally {
        hideLoading();
        closeExportModal();
    }
}

// Client-side CSV export fallback
function exportCSVClientSide() {
    try {
        const data = sampleData[currentPeriod] || sampleData.week;
        const totalIncome = data.revenue.reduce((sum, val) => sum + val, 0);
        
        let content = `LAPORAN LAUNDRY\n`;
        content += `Periode: ${document.getElementById('dateRangeText').textContent}\n`;
        content += `Total Pendapatan: Rp ${formatPrice(totalIncome)}\n`;
        content += `Total Pesanan: ${data.transactions.length}\n\n`;
        content += `Detail Transaksi:\n`;
        
        data.transactions.forEach(transaction => {
            content += `${transaction.date} - ${transaction.customer} - ${transaction.service} - Rp ${formatPrice(transaction.amount)}\n`;
        });
        
        const blob = new Blob([content], { type: 'text/csv' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `laporan-laundry-${new Date().toISOString().split('T')[0]}.csv`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    } catch (error) {
        console.error('Error in client-side export:', error);
        showError('Gagal melakukan export');
    }
}

// ===== UTILITY FUNCTIONS =====

// Helper function untuk CSRF token
function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
}

// Format price to Indonesian format
function formatPrice(price) {
    return new Intl.NumberFormat('id-ID').format(price);
}

// Translate status to Indonesian
function translateStatus(status) {
    const statusMap = {
        'completed': 'Selesai',
        'ready': 'Selesai',
        'picked_up': 'Diambil',
        'new': 'Baru',
        'washing': 'Dicuci',
        'ironing': 'Disetrika'
    };
    return statusMap[status] || status;
}

// Show loading indicator
function showLoading() {
    // Implement loading indicator jika diperlukan
    console.log('Loading report data...');
}

// Hide loading indicator
function hideLoading() {
    // Hide loading indicator jika diperlukan
    console.log('Report data loaded');
}

// Show error message
function showError(message) {
    // Bisa diganti dengan toast notification yang lebih baik
    console.error('Error:', message);
    alert(message);
}

// Show toast notification
function showToast(message) {
    // Simple toast implementation
    const toast = document.createElement('div');
    toast.className = 'fixed top-4 left-4 right-4 bg-green-500 text-white p-3 rounded-lg shadow-lg text-center z-50';
    toast.textContent = message;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        document.body.removeChild(toast);
    }, 3000);
}

// Close export modal
function closeExportModal() {
    const exportModal = document.getElementById('exportModal');
    if (exportModal) {
        exportModal.classList.add('hidden');
    }
}

// Close date modal
function closeDateModal() {
    const dateModal = document.getElementById('dateModal');
    if (dateModal) {
        dateModal.classList.add('hidden');
    }
}

// Apply date range from modal (jika menggunakan date picker)
function applyDateRange() {
    // Implement date range selection dari modal
    closeDateModal();
    loadReportDataFromAPI();
}