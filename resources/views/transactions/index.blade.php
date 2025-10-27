@extends('layouts.mobile')

@section('title', 'Transaksi')

@section('content')
<div class="pb-4">
    <!-- Header Stats -->
    <div class="bg-white px-4 py-3 border-b border-gray-200">
        <div class="grid grid-cols-3 gap-4 text-center">
            <div>
                <p class="text-2xl font-bold text-blue-600">12</p>
                <p class="text-xs text-gray-500">Hari Ini</p>
            </div>
            <div>
                <p class="text-2xl font-bold text-orange-600">8</p>
                <p class="text-xs text-gray-500">Diproses</p>
            </div>
            <div>
                <p class="text-2xl font-bold text-green-600">Rp 450K</p>
                <p class="text-xs text-gray-500">Pendapatan</p>
            </div>
        </div>
    </div>

    <!-- Transaction List -->
    <div class="space-y-2 p-4">
        <!-- Transactions will be loaded here -->
        <div id="transactionsList">
            <!-- Dynamic content -->
        </div>
    </div>

    <!-- Empty State -->
    <div id="emptyState" class="hidden text-center py-8 px-4">
        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
            <i class="fas fa-exchange-alt text-gray-400 text-xl"></i>
        </div>
        <h3 class="text-lg font-semibold text-gray-700 mb-2">Belum Ada Transaksi</h3>
        <p class="text-gray-500 mb-4">Mulai buat transaksi pertama Anda</p>
        <button onclick="startNewTransaction()" class="bg-blue-500 text-white px-6 py-2 rounded-lg font-semibold">
            Buat Transaksi
        </button>
    </div>
</div>

<!-- Floating Action Button -->
<button 
    id="fabButton"
    class="fixed bottom-20 right-4 w-14 h-14 bg-blue-500 text-white rounded-full shadow-lg flex items-center justify-center hover:bg-blue-600 active:scale-95 transition-all duration-200 z-20"
    onclick="startNewTransaction()"
>
    <i class="fas fa-plus text-lg"></i>
</button>

<!-- Transaction Wizard Modals -->
@include('partials.transaction-customer-modal')
@include('partials.transaction-service-modal')
@include('partials.transaction-items-modal')
@include('partials.transaction-review-modal')
@include('partials.transaction-success-modal')
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

    // Start new transaction
    function startNewTransaction() {
        currentStep = 0;
        transactionData = {
            customer: null,
            service: null,
            items: [],
            notes: '',
            total: 0
        };
        showCustomerModal();
    }

    // Show customer selection modal
    function showCustomerModal() {
        updateStepIndicator(0);
        document.getElementById('customerModal').classList.remove('hidden');
        document.getElementById('customerSearch').focus();
    }

    // Show service selection modal
    function showServiceModal() {
        updateStepIndicator(1);
        document.getElementById('customerModal').classList.add('hidden');
        document.getElementById('serviceModal').classList.remove('hidden');
    }

    // Show items input modal
    function showItemsModal() {
        updateStepIndicator(2);
        document.getElementById('serviceModal').classList.add('hidden');
        document.getElementById('itemsModal').classList.remove('hidden');
        initializeItemsForm();
    }

    // Show review modal
    function showReviewModal() {
        updateStepIndicator(3);
        document.getElementById('itemsModal').classList.add('hidden');
        document.getElementById('reviewModal').classList.remove('hidden');
        updateReviewSummary();
    }

    // Show success modal
    function showSuccessModal() {
        document.getElementById('reviewModal').classList.add('hidden');
        document.getElementById('successModal').classList.remove('hidden');
    }

    // Update step indicator
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

    // Customer selection
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

    // Service selection
    function selectService(service) {
        transactionData.service = service;
        document.getElementById('selectedService').innerHTML = `
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 ${service.color} rounded-full flex items-center justify-center">
                    <i class="${service.icon} text-white"></i>
                </div>
                <div>
                    <p class="font-semibold text-gray-800">${service.name}</p>
                    <p class="text-sm text-gray-500">${service.category}</p>
                </div>
            </div>
        `;
        showItemsModal();
    }

    // Initialize items form based on selected service
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

    // Create item input element
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

    // Increase item quantity
    function increaseQuantity(itemId) {
        const quantityElement = document.getElementById(`quantity-${itemId}`);
        let quantity = parseInt(quantityElement.textContent) || 0;
        quantity++;
        quantityElement.textContent = quantity;
        
        updateItemInTransaction(itemId, quantity);
        updateTotal();
    }

    // Decrease item quantity
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

    // Update item in transaction data
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
        
        // Update item total display
        const itemTotalElement = document.querySelector(`[onclick="decreaseQuantity('${itemId}')]`).parentElement.parentElement.querySelector('.item-total');
        itemTotalElement.textContent = `Rp ${formatPrice(quantity * item.price)}`;
    }

    // Update total calculation
    function updateTotal() {
        transactionData.total = transactionData.items.reduce((sum, item) => sum + item.subtotal, 0);
        document.getElementById('itemsTotal').textContent = `Rp ${formatPrice(transactionData.total)}`;
    }

    // Update review summary
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
                <div class="w-12 h-12 ${transactionData.service.color} rounded-full flex items-center justify-center">
                    <i class="${transactionData.service.icon} text-white"></i>
                </div>
                <div>
                    <p class="font-semibold text-gray-800">${transactionData.service.name}</p>
                    <p class="text-sm text-gray-500">${transactionData.service.category}</p>
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

    // Submit transaction
    function submitTransaction() {
        // Simulate API call
        console.log('Submitting transaction:', transactionData);
        
        // Show success modal
        showSuccessModal();
        
        // In real app, you would:
        // 1. Send data to backend
        // 2. Handle response
        // 3. Update UI accordingly
    }

    // Format price
    function formatPrice(price) {
        return price.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    // Close all modals
    function closeAllModals() {
        document.querySelectorAll('[id$="Modal"]').forEach(modal => {
            modal.classList.add('hidden');
        });
    }

    // Mock data
    function getMockCustomers() {
        return [
            { id: 1, name: 'Budi Santoso', phone: '081234567890', address: 'Jl. Merdeka No. 123' },
            { id: 2, name: 'Siti Rahayu', phone: '081234567891', address: 'Jl. Sudirman No. 45' },
            { id: 3, name: 'Ahmad Fauzi', phone: '081234567892', address: 'Jl. Thamrin No. 67' },
            { id: 4, name: 'Dewi Lestari', phone: '081234567893', address: 'Jl. Gatot Subroto No. 89' },
        ];
    }

    function getMockServices() {
        return [
            {
                id: 1,
                name: 'Cuci Biasa',
                category: 'Cuci',
                icon: 'fas fa-soap',
                color: 'bg-blue-500',
                items: [
                    { id: 1, name: 'Baju', price: 5000 },
                    { id: 2, name: 'Celana', price: 6000 },
                    { id: 3, name: 'Jaket', price: 10000 }
                ]
            },
            {
                id: 2,
                name: 'Cuci Setrika',
                category: 'Cuci',
                icon: 'fas fa-tshirt',
                color: 'bg-green-500',
                items: [
                    { id: 1, name: 'Baju', price: 8000 },
                    { id: 2, name: 'Celana', price: 9000 },
                    { id: 3, name: 'Jaket', price: 15000 }
                ]
            },
            {
                id: 3,
                name: 'Setrika Saja',
                category: 'Setrika',
                icon: 'fas fa-fire',
                color: 'bg-orange-500',
                items: [
                    { id: 1, name: 'Baju', price: 4000 },
                    { id: 2, name: 'Celana', price: 5000 }
                ]
            }
        ];
    }

    // Initialize on load
    document.addEventListener('DOMContentLoaded', function() {
        // Load mock transactions for the list
        loadTransactions();
    });

    function loadTransactions() {
        // Mock transactions data
        const transactions = [
            { id: 1, customer: 'Budi Santoso', service: 'Cuci Setrika', total: 45000, status: 'process', date: '15 Jan 2024' },
            { id: 2, customer: 'Siti Rahayu', service: 'Setrika Saja', total: 25000, status: 'completed', date: '14 Jan 2024' },
            { id: 3, customer: 'Ahmad Fauzi', service: 'Cuci Biasa', total: 30000, status: 'completed', date: '14 Jan 2024' }
        ];

        const container = document.getElementById('transactionsList');
        const emptyState = document.getElementById('emptyState');

        if (transactions.length === 0) {
            container.classList.add('hidden');
            emptyState.classList.remove('hidden');
        } else {
            container.classList.remove('hidden');
            emptyState.classList.add('hidden');
            
            container.innerHTML = transactions.map(transaction => `
                <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                    <div class="flex justify-between items-start mb-2">
                        <div>
                            <h3 class="font-semibold text-gray-800">${transaction.customer}</h3>
                            <p class="text-sm text-gray-500">${transaction.service}</p>
                        </div>
                        <div class="text-right">
                            <p class="font-bold text-gray-800">Rp ${formatPrice(transaction.total)}</p>
                            <span class="text-xs px-2 py-1 rounded-full ${getStatusColor(transaction.status)}">
                                ${getStatusText(transaction.status)}
                            </span>
                        </div>
                    </div>
                    <div class="flex justify-between items-center text-sm text-gray-500">
                        <span>${transaction.date}</span>
                        <button class="text-blue-500 font-medium">Detail</button>
                    </div>
                </div>
            `).join('');
        }
    }

    function getStatusColor(status) {
        switch(status) {
            case 'process': return 'bg-orange-100 text-orange-600';
            case 'completed': return 'bg-green-100 text-green-600';
            default: return 'bg-gray-100 text-gray-600';
        }
    }

    function getStatusText(status) {
        switch(status) {
            case 'process': return 'Diproses';
            case 'completed': return 'Selesai';
            default: return 'Baru';
        }
    }
</script>
@endpush