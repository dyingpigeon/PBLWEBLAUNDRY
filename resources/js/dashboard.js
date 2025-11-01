// dashboard.js
// Handle dashboard page functionality saja

// Helper function untuk get CSRF token
function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
}

// Load data on page load
document.addEventListener('DOMContentLoaded', function() {
    loadTodaySummary();
    loadRecentOrders();
});

// Load today's summary
async function loadTodaySummary() {
    try {
        const response = await fetch('/api/transactions/today-summary');
        const data = await response.json();

        if (data.success) {
            document.getElementById('todayOrders').textContent = data.data.total_transactions || 0;
            document.getElementById('processingOrders').textContent = data.data.processing_count || 0;
            document.getElementById('todayRevenue').textContent = `Rp ${formatPrice(data.data.total_income || 0)}`;
            
            // Update comparison text
            const comparison = data.data.yesterday_comparison || 0;
            document.getElementById('todayComparison').textContent = 
                comparison >= 0 ? `+${comparison} dari kemarin` : `${comparison} dari kemarin`;
            
            // Update processing breakdown
            const washing = data.data.washing_count || 0;
            const ironing = data.data.ironing_count || 0;
            document.getElementById('processingBreakdown').textContent = `${washing} cuci, ${ironing} setrika`;
            
            // Update average order value
            const avgOrder = data.data.avg_order_value || 0;
            document.getElementById('avgOrder').textContent = `Rata-rata Rp ${formatPrice(avgOrder)}/order`;
        }
    } catch (error) {
        console.error('Error loading summary:', error);
    }
}

// Load recent orders
async function loadRecentOrders() {
    try {
        const response = await fetch('/api/transactions/recent?limit=3');
        const data = await response.json();

        if (data.success) {
            renderRecentOrders(data.data);
        }
    } catch (error) {
        console.error('Error loading recent orders:', error);
    }
}

// Render recent orders
function renderRecentOrders(orders) {
    const container = document.getElementById('recentOrders');
    const loading = document.getElementById('recentOrdersLoading');
    
    loading.classList.add('hidden');
    
    if (orders.length === 0) {
        container.innerHTML = `
            <div class="text-center py-8 bg-gray-50 rounded-xl">
                <i class="fas fa-exchange-alt text-gray-300 text-2xl mb-2"></i>
                <p class="text-gray-500">Belum ada transaksi hari ini</p>
            </div>
        `;
        return;
    }

    container.innerHTML = orders.map(order => `
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100" 
             onclick="viewOrderDetail(${order.id})">
            <div class="flex justify-between items-start mb-2">
                <div>
                    <h3 class="font-semibold text-gray-800">${order.customer_name}</h3>
                    <p class="text-sm text-gray-500">${order.transaction_number}</p>
                    <p class="text-sm text-gray-500">${order.service_name}</p>
                </div>
                <div class="text-right">
                    <p class="font-bold text-gray-800">Rp ${formatPrice(order.total_amount)}</p>
                    <span class="text-xs px-2 py-1 rounded-full ${getStatusColor(order.status)}">
                        ${getStatusText(order.status)}
                    </span>
                </div>
            </div>
            <div class="flex justify-between items-center text-sm text-gray-500">
                <span>${formatTime(order.created_at)}</span>
                <span class="text-blue-500 font-medium">Detail</span>
            </div>
        </div>
    `).join('');
}

// Utility functions
function formatPrice(price) {
    return parseFloat(price).toLocaleString('id-ID');
}

function formatTime(dateString) {
    return new Date(dateString).toLocaleTimeString('id-ID', {
        hour: '2-digit',
        minute: '2-digit'
    });
}

function getStatusText(status) {
    const statusMap = {
        'new': 'Baru',
        'washing': 'Dicuci',
        'ironing': 'Disetrika',
        'ready': 'Selesai',
        'picked_up': 'Diambil'
    };
    return statusMap[status] || 'Unknown';
}

function getStatusColor(status) {
    const colorMap = {
        'new': 'bg-blue-100 text-blue-600',
        'washing': 'bg-orange-100 text-orange-600',
        'ironing': 'bg-purple-100 text-purple-600',
        'ready': 'bg-green-100 text-green-600',
        'picked_up': 'bg-gray-100 text-gray-600'
    };
    return colorMap[status] || 'bg-gray-100 text-gray-600';
}

function viewOrderDetail(orderId) {
    window.location.href = `/tracking?highlight=${orderId}`;
}

// Customer modal function
function showAddCustomerModal() {
    document.getElementById('addCustomerModal').classList.remove('hidden');
    document.getElementById('customerName').focus();
}

// Close all modals
function closeAllModals() {
    document.querySelectorAll('[id$="Modal"]').forEach(modal => {
        modal.classList.add('hidden');
    });
}  