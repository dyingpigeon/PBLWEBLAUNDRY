@extends('layouts.mobile')

@section('title', 'Dashboard')

@section('content')
    <div class="px-4">
        <!-- Welcome Section -->
        <div class="pt-4">
            <h2 class="text-lg font-semibold text-gray-700">Selamat Pagi, Admin! 👋</h2>
            <p class="text-sm text-gray-500">{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</p>
        </div>

        <!-- Quick Actions -->
        <div class="mt-6">
            <h3 class="font-medium text-gray-700 mb-3">Quick Actions</h3>
            <div class="grid grid-cols-2 gap-3">
                <!-- Transaksi Baru -->
                <button onclick="startNewTransaction()"
                    class="bg-white rounded-xl p-4 shadow-sm border-2 border-blue-500 text-center active:scale-95 transition-all duration-200 hover:bg-blue-50 relative group">
                    <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center mx-auto mb-2 shadow-lg">
                        <i class="fas fa-plus text-white text-lg"></i>
                    </div>
                    <p class="text-sm font-medium text-blue-600">Transaksi Baru</p>
                    <div
                        class="absolute inset-0 rounded-xl border-2 border-transparent group-active:border-blue-300 transition-all duration-200">
                    </div>
                </button>

                <!-- Pelanggan Baru -->
                <button onclick="showAddCustomerModal()"
                    class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 text-center active:scale-95 transition-all duration-200 hover:bg-gray-50 group">
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-2 group-active:scale-110 transition-transform">
                        <i class="fas fa-user-plus text-green-600 text-lg"></i>
                    </div>
                    <p class="text-sm font-medium text-gray-700">Pelanggan Baru</p>
                </button>

                <!-- Layanan & Harga -->
                <a href="/services"
                    class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 text-center active:scale-95 transition-all duration-200 hover:bg-gray-50 block group">
                    <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-2 group-active:scale-110 transition-transform">
                        <i class="fas fa-tshirt text-orange-600 text-lg"></i>
                    </div>
                    <p class="text-sm font-medium text-gray-700">Layanan & Harga</p>
                </a>

                <!-- Lihat Laporan -->
                <a href="/reports"
                    class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 text-center active:scale-95 transition-all duration-200 hover:bg-gray-50 block group">
                    <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-2 group-active:scale-110 transition-transform">
                        <i class="fas fa-chart-bar text-purple-600 text-lg"></i>
                    </div>
                    <p class="text-sm font-medium text-gray-700">Lihat Laporan</p>
                </a>
            </div>
        </div>

        <!-- Statistik Cards -->
        <div class="mt-6">
            <h3 class="font-medium text-gray-700 mb-2">Statistik Hari Ini</h3>
            <div class="swipeable flex overflow-x-auto space-x-3 pb-2" style="scrollbar-width: none;">
                <!-- Pesanan Hari Ini -->
                <div class="stat-card bg-gradient-to-r from-blue-500 to-blue-600 rounded-2xl p-4 text-white shadow-lg flex-shrink-0"
                    style="width: 85%;">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm opacity-90">Pesanan Hari Ini</p>
                            <p class="text-2xl font-bold mt-1" id="todayOrders">0</p>
                            <p class="text-xs opacity-80 mt-1" id="todayComparison">+0 dari kemarin</p>
                        </div>
                        <div class="w-12 h-12 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                            <i class="fas fa-box text-xl"></i>
                        </div>
                    </div>
                </div>

                <!-- Sedang Diproses -->
                <div class="stat-card bg-gradient-to-r from-orange-500 to-orange-600 rounded-2xl p-4 text-white shadow-lg flex-shrink-0"
                    style="width: 85%;">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm opacity-90">Sedang Diproses</p>
                            <p class="text-2xl font-bold mt-1" id="processingOrders">0</p>
                            <p class="text-xs opacity-80 mt-1" id="processingBreakdown">0 cuci, 0 setrika</p>
                        </div>
                        <div class="w-12 h-12 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                            <i class="fas fa-spinner text-xl"></i>
                        </div>
                    </div>
                </div>

                <!-- Pendapatan Harian -->
                <div class="stat-card bg-gradient-to-r from-purple-500 to-purple-600 rounded-2xl p-4 text-white shadow-lg flex-shrink-0"
                    style="width: 85%;">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm opacity-90">Pendapatan Harian</p>
                            <p class="text-2xl font-bold mt-1" id="todayRevenue">Rp 0</p>
                            <p class="text-xs opacity-80 mt-1" id="avgOrder">Rata-rata Rp 0/order</p>
                        </div>
                        <div class="w-12 h-12 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                            <i class="fas fa-coins text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Orders -->
        <div class="mt-6">
            <div class="flex justify-between items-center mb-3">
                <h3 class="font-medium text-gray-700">Pesanan Terbaru</h3>
                <a href="/tracking" class="text-blue-500 text-sm">Lihat Semua</a>
            </div>
            <div id="recentOrders" class="space-y-2">
                <!-- Orders will be loaded dynamically -->
            </div>
            <div id="recentOrdersLoading" class="text-center py-4">
                <div class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-2">
                    <i class="fas fa-spinner fa-spin text-gray-400"></i>
                </div>
                <p class="text-gray-500 text-sm">Memuat pesanan...</p>
            </div>
        </div>
    </div>

    <!-- Transaction Wizard Modals -->
    @include('partials.transaction-customer-modal')
    @include('partials.transaction-service-modal')
    @include('partials.transaction-items-modal')
    @include('partials.transaction-review-modal')
    @include('partials.transaction-success-modal')

    <!-- Include Customer Modal -->
    @include('partials.customer-add-modal')
@endsection

@push('styles')
<style>
.step-indicator {
    transition: all 0.3s ease;
}
.step-active {
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    color: white;
}
.step-completed {
    background: #10b981;
    color: white;
}
.service-card {
    transition: all 0.2s ease;
}
.service-card:active {
    transform: scale(0.98);
}
.quantity-btn {
    min-width: 36px;
    min-height: 36px;
}
</style>
@endpush

@push('scripts')
<script>
    let currentStep = 0;
    let transactionData = {
        customer: null,
        service: null,
        items: [],
        notes: '',
        total: 0
    };

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

    // Start new transaction wizard (SAMA PERSIS dengan FAB di halaman transaksi)
    async function startNewTransaction() {
        currentStep = 0;
        transactionData = {
            customer: null,
            service: null,
            items: [],
            notes: '',
            total: 0
        };
        
        await loadCustomers();
        await loadServices();
        showCustomerModal();
    }

    // ========== TRANSACTION WIZARD FUNCTIONS ==========
    // (Sama persis dengan yang di halaman transactions)

    async function loadCustomers() {
        try {
            const response = await fetch('/api/transactions/customers');
            const data = await response.json();
            if (data.success) {
                const customerList = document.getElementById('customerList');
                customerList.innerHTML = data.data.map(customer => `
                    <div class="customer-item bg-white rounded-xl p-4 border border-gray-200 mb-2" 
                         onclick="selectCustomer(${JSON.stringify(customer).replace(/"/g, '&quot;')})">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-user text-blue-600"></i>
                            </div>
                            <div class="flex-1">
                                <p class="font-semibold text-gray-800">${customer.name}</p>
                                <p class="text-sm text-gray-500">${customer.phone}</p>
                                ${customer.address ? `<p class="text-xs text-gray-400 truncate">${customer.address}</p>` : ''}
                            </div>
                        </div>
                    </div>
                `).join('');
            }
        } catch (error) {
            console.error('Error loading customers:', error);
        }
    }

    async function loadServices() {
        try {
            const response = await fetch('/api/transactions/services');
            const data = await response.json();
            if (data.success) {
                const serviceList = document.getElementById('serviceList');
                serviceList.innerHTML = data.data.map(service => `
                    <div class="service-card bg-white rounded-xl p-4 border border-gray-200 mb-3" 
                         onclick="selectService(${JSON.stringify(service).replace(/"/g, '&quot;')})">
                        <div class="flex items-center space-x-3">
                            <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center">
                                <i class="fas fa-tshirt text-white"></i>
                            </div>
                            <div class="flex-1">
                                <h3 class="font-semibold text-gray-800">${service.name}</h3>
                                <p class="text-sm text-gray-500">${service.description || 'Layanan laundry'}</p>
                                <div class="mt-2">
                                    ${service.items && service.items.map(item => `
                                        <span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded mr-1">
                                            ${item.name}: Rp ${formatPrice(item.price)}
                                        </span>
                                    `).join('')}
                                </div>
                            </div>
                        </div>
                    </div>
                `).join('');
            }
        } catch (error) {
            console.error('Error loading services:', error);
        }
    }

    function showCustomerModal() {
        updateStepIndicator(0);
        document.getElementById('customerModal').classList.remove('hidden');
        document.getElementById('customerSearch').focus();
    }

    function showServiceModal() {
        updateStepIndicator(1);
        document.getElementById('customerModal').classList.add('hidden');
        document.getElementById('serviceModal').classList.remove('hidden');
    }

    function showItemsModal() {
        updateStepIndicator(2);
        document.getElementById('serviceModal').classList.add('hidden');
        document.getElementById('itemsModal').classList.remove('hidden');
        initializeItemsForm();
    }

    function showReviewModal() {
        updateStepIndicator(3);
        document.getElementById('itemsModal').classList.add('hidden');
        document.getElementById('reviewModal').classList.remove('hidden');
        updateReviewSummary();
    }

    function showSuccessModal() {
        document.getElementById('reviewModal').classList.add('hidden');
        document.getElementById('successModal').classList.remove('hidden');
    }

    function updateStepIndicator(step) {
        document.querySelectorAll('.step-indicator').forEach((indicator, index) => {
            indicator.classList.remove('step-active', 'step-completed', 'bg-gray-200', 'text-gray-400');
            
            if (index < step) {
                indicator.classList.add('step-completed');
            } else if (index === step) {
                indicator.classList.add('step-active');
            } else {
                indicator.classList.add('bg-gray-200', 'text-gray-400');
            }
        });
        currentStep = step;
    }

    function selectCustomer(customer) {
        transactionData.customer = customer;
        document.getElementById('selectedCustomer').innerHTML = `
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-user text-blue-600"></i>
                </div>
                <div>
                    <p class="font-semibold text-gray-800">${customer.name}</p>
                    <p class="text-sm text-gray-500">${customer.phone}</p>
                </div>
            </div>
        `;
        showServiceModal();
    }

    function selectService(service) {
        transactionData.service = service;
        document.getElementById('selectedService').innerHTML = `
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center">
                    <i class="fas fa-tshirt text-white"></i>
                </div>
                <div>
                    <p class="font-semibold text-gray-800">${service.name}</p>
                    <p class="text-sm text-gray-500">${service.description || 'Layanan laundry'}</p>
                </div>
            </div>
        `;
        showItemsModal();
    }

    function initializeItemsForm() {
        const container = document.getElementById('itemsContainer');
        container.innerHTML = '';
        
        if (transactionData.service && transactionData.service.items) {
            transactionData.service.items.forEach(item => {
                const itemElement = createItemElement(item);
                container.appendChild(itemElement);
            });
        }
        
        updateTotal();
    }

    function createItemElement(item) {
        const div = document.createElement('div');
        div.className = 'bg-white rounded-xl p-4 border border-gray-200';
        div.innerHTML = `
            <div class="flex items-center justify-between mb-3">
                <div>
                    <h4 class="font-semibold text-gray-800">${item.name}</h4>
                    <p class="text-sm text-gray-500">Rp ${formatPrice(item.price)}/item</p>
                </div>
                <span class="text-lg font-bold text-blue-600 item-total">Rp 0</span>
            </div>
            
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <button type="button" onclick="decreaseQuantity('${item.id}')" 
                            class="quantity-btn w-9 h-9 bg-gray-100 text-gray-600 rounded-lg flex items-center justify-center">
                        <i class="fas fa-minus text-sm"></i>
                    </button>
                    <span id="quantity-${item.id}" class="text-lg font-semibold w-8 text-center">0</span>
                    <button type="button" onclick="increaseQuantity('${item.id}')" 
                            class="quantity-btn w-9 h-9 bg-gray-100 text-gray-600 rounded-lg flex items-center justify-center">
                        <i class="fas fa-plus text-sm"></i>
                    </button>
                </div>
                <span class="text-sm text-gray-500">kg/pcs</span>
            </div>
        `;
        return div;
    }

    function increaseQuantity(itemId) {
        const quantityElement = document.getElementById(`quantity-${itemId}`);
        let quantity = parseInt(quantityElement.textContent) || 0;
        quantity++;
        quantityElement.textContent = quantity;
        
        updateItemInTransaction(itemId, quantity);
        updateTotal();
    }

    function decreaseQuantity(itemId) {
        const quantityElement = document.getElementById(`quantity-${itemId}`);
        let quantity = parseInt(quantityElement.textContent) || 0;
        if (quantity > 0) {
            quantity--;
            quantityElement.textContent = quantity;
            updateItemInTransaction(itemId, quantity);
            updateTotal();
        }
    }

    function updateItemInTransaction(itemId, quantity) {
        const item = transactionData.service.items.find(i => i.id == itemId);
        const existingIndex = transactionData.items.findIndex(i => i.id == itemId);
        
        if (quantity > 0) {
            if (existingIndex >= 0) {
                transactionData.items[existingIndex].quantity = quantity;
                transactionData.items[existingIndex].subtotal = quantity * item.price;
            } else {
                transactionData.items.push({
                    ...item,
                    quantity: quantity,
                    subtotal: quantity * item.price
                });
            }
        } else {
            transactionData.items = transactionData.items.filter(i => i.id != itemId);
        }
        
        const itemTotalElement = document.querySelector(`[onclick="decreaseQuantity('${itemId}')]`).parentElement.parentElement.querySelector('.item-total');
        if (itemTotalElement) {
            itemTotalElement.textContent = `Rp ${formatPrice(quantity * item.price)}`;
        }
    }

    function updateTotal() {
        transactionData.total = transactionData.items.reduce((sum, item) => sum + item.subtotal, 0);
        const itemsTotalElement = document.getElementById('itemsTotal');
        if (itemsTotalElement) {
            itemsTotalElement.textContent = `Rp ${formatPrice(transactionData.total)}`;
        }
    }

    function updateReviewSummary() {
        document.getElementById('reviewCustomer').innerHTML = `
            <div class="flex items-center space-x-3">
                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-user text-blue-600"></i>
                </div>
                <div>
                    <p class="font-semibold text-gray-800">${transactionData.customer.name}</p>
                    <p class="text-sm text-gray-500">${transactionData.customer.phone}</p>
                </div>
            </div>
        `;

        document.getElementById('reviewService').innerHTML = `
            <div class="flex items-center space-x-3">
                <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center">
                    <i class="fas fa-tshirt text-white"></i>
                </div>
                <div>
                    <p class="font-semibold text-gray-800">${transactionData.service.name}</p>
                    <p class="text-sm text-gray-500">${transactionData.service.description || 'Layanan laundry'}</p>
                </div>
            </div>
        `;

        const itemsList = document.getElementById('reviewItems');
        itemsList.innerHTML = transactionData.items.map(item => `
            <div class="flex justify-between items-center py-2 border-b border-gray-100 last:border-b-0">
                <div>
                    <p class="font-medium text-gray-800">${item.name}</p>
                    <p class="text-sm text-gray-500">${item.quantity} × Rp ${formatPrice(item.price)}</p>
                </div>
                <span class="font-semibold text-gray-800">Rp ${formatPrice(item.subtotal)}</span>
            </div>
        `).join('');

        document.getElementById('reviewTotal').textContent = `Rp ${formatPrice(transactionData.total)}`;
    }

    async function submitTransaction() {
        try {
            const formData = {
                customer_id: transactionData.customer.id,
                service_id: transactionData.service.id,
                items: transactionData.items.map(item => ({
                    id: item.id,
                    quantity: item.quantity,
                    price: item.price
                })),
                notes: transactionData.notes,
                total_amount: transactionData.total
            };

            const response = await fetch('/transactions', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(formData)
            });

            if (response.ok) {
                showSuccessModal();
                // Refresh dashboard data
                loadTodaySummary();
                loadRecentOrders();
            } else {
                const errorData = await response.json();
                alert('Gagal membuat transaksi: ' + (errorData.message || 'Unknown error'));
            }
        } catch (error) {
            console.error('Error submitting transaction:', error);
            alert('Terjadi kesalahan saat membuat transaksi');
        }
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
</script>
@endpush