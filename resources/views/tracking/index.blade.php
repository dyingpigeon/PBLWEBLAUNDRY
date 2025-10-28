@extends('layouts.mobile')

@section('title', 'Tracking Pesanan')

@section('content')
<div class="pb-4">
    <!-- Status Tabs (Swipeable) -->
    <div class="bg-white border-b border-gray-200 sticky top-0 z-10">
        <div class="swipeable-tabs flex overflow-x-auto px-4" style="scrollbar-width: none;">
            <button class="status-tab flex-shrink-0 px-4 py-3 font-medium text-gray-500 border-b-2 border-transparent transition-all duration-200 active-tab" data-status="all">
                Semua
            </button>
            <button class="status-tab flex-shrink-0 px-4 py-3 font-medium text-gray-500 border-b-2 border-transparent transition-all duration-200" data-status="new">
                Baru
            </button>
            <button class="status-tab flex-shrink-0 px-4 py-3 font-medium text-gray-500 border-b-2 border-transparent transition-all duration-200" data-status="washing">
                Dicuci
            </button>
            <button class="status-tab flex-shrink-0 px-4 py-3 font-medium text-gray-500 border-b-2 border-transparent transition-all duration-200" data-status="ironing">
                Disetrika
            </button>
            <button class="status-tab flex-shrink-0 px-4 py-3 font-medium text-gray-500 border-b-2 border-transparent transition-all duration-200" data-status="ready">
                Selesai
            </button>
            <button class="status-tab flex-shrink-0 px-4 py-3 font-medium text-gray-500 border-b-2 border-transparent transition-all duration-200" data-status="picked_up">
                Diambil
            </button>
        </div>
    </div>

    <!-- Orders List -->
    <div id="ordersList" class="space-y-3 px-4 mt-3">
        <!-- Orders will be loaded here -->
    </div>

    <!-- Empty State -->
    <div id="emptyState" class="hidden text-center py-8 px-4">
        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
            <i class="fas fa-box-open text-gray-400 text-xl"></i>
        </div>
        <p class="text-gray-500 font-medium">Tidak ada pesanan</p>
        <p class="text-gray-400 text-sm mt-1">Belum ada pesanan dengan status ini</p>
    </div>
</div>

<!-- Status Update Modal -->
@include('partials.tracking-status-modal')
@endsection

@push('styles')
<style>
.swipeable-tabs::-webkit-scrollbar {
    display: none;
}
.status-tab.active-tab {
    color: #3b82f6;
    border-bottom-color: #3b82f6;
}
.progress-bar {
    height: 6px;
    border-radius: 3px;
    transition: all 0.3s ease;
}
.status-dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    transition: all 0.3s ease;
}
</style>
@endpush

@push('scripts')
<script>
let currentStatus = 'all';
let orders = [];

// Sample data
const sampleOrders = [
    {
        id: 1,
        code: 'LAUNDRY-0012',
        customer_name: 'Budi Santoso',
        customer_phone: '081234567890',
        service: 'Cuci Setrika',
        weight: 2,
        price: 40000,
        status: 'new',
        created_at: '2024-01-15 08:30',
        timeline: [
            { status: 'new', time: '2024-01-15 08:30', completed: true },
            { status: 'washing', time: null, completed: false },
            { status: 'ironing', time: null, completed: false },
            { status: 'ready', time: null, completed: false },
            { status: 'picked_up', time: null, completed: false }
        ]
    },
    {
        id: 2,
        code: 'LAUNDRY-0011',
        customer_name: 'Siti Rahayu',
        customer_phone: '081234567891',
        service: 'Setrika saja',
        weight: 3,
        price: 30000,
        status: 'washing',
        created_at: '2024-01-15 09:15',
        timeline: [
            { status: 'new', time: '2024-01-15 09:15', completed: true },
            { status: 'washing', time: '2024-01-15 10:00', completed: true },
            { status: 'ironing', time: null, completed: false },
            { status: 'ready', time: null, completed: false },
            { status: 'picked_up', time: null, completed: false }
        ]
    },
    {
        id: 3,
        code: 'LAUNDRY-0010',
        customer_name: 'Ahmad Fauzi',
        customer_phone: '081234567892',
        service: 'Dry Clean',
        weight: 1,
        price: 75000,
        status: 'ironing',
        created_at: '2024-01-14 14:20',
        timeline: [
            { status: 'new', time: '2024-01-14 14:20', completed: true },
            { status: 'washing', time: '2024-01-15 08:00', completed: true },
            { status: 'ironing', time: '2024-01-15 11:30', completed: true },
            { status: 'ready', time: null, completed: false },
            { status: 'picked_up', time: null, completed: false }
        ]
    },
    {
        id: 4,
        code: 'LAUNDRY-0009',
        customer_name: 'Dewi Lestari',
        customer_phone: '081234567893',
        service: 'Cuci Biasa',
        weight: 4,
        price: 60000,
        status: 'ready',
        created_at: '2024-01-14 10:45',
        timeline: [
            { status: 'new', time: '2024-01-14 10:45', completed: true },
            { status: 'washing', time: '2024-01-14 13:00', completed: true },
            { status: 'ironing', time: '2024-01-15 09:00', completed: true },
            { status: 'ready', time: '2024-01-15 12:00', completed: true },
            { status: 'picked_up', time: null, completed: false }
        ]
    },
    {
        id: 5,
        code: 'LAUNDRY-0008',
        customer_name: 'Rizki Pratama',
        customer_phone: '081234567894',
        service: 'Cuci Setrika',
        weight: 3,
        price: 55000,
        status: 'picked_up',
        created_at: '2024-01-13 16:20',
        timeline: [
            { status: 'new', time: '2024-01-13 16:20', completed: true },
            { status: 'washing', time: '2024-01-14 08:00', completed: true },
            { status: 'ironing', time: '2024-01-14 14:00', completed: true },
            { status: 'ready', time: '2024-01-15 10:00', completed: true },
            { status: 'picked_up', time: '2024-01-15 14:30', completed: true }
        ]
    }
];

document.addEventListener('DOMContentLoaded', function() {
    loadOrders();
    setupTabSwiping();
    setupStatusTabs();
});

// Setup tab swiping
function setupTabSwiping() {
    const tabsContainer = document.querySelector('.swipeable-tabs');
    let startX;
    let currentX;

    tabsContainer.addEventListener('touchstart', e => {
        startX = e.touches[0].clientX;
    });

    tabsContainer.addEventListener('touchmove', e => {
        if (!startX) return;
        
        currentX = e.touches[0].clientX;
        const diff = startX - currentX;
        
        // Horizontal swipe for tabs
        tabsContainer.scrollLeft += diff;
        startX = currentX;
    });
}

// Setup status tabs
function setupStatusTabs() {
    const tabs = document.querySelectorAll('.status-tab');
    
    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            // Update active tab
            tabs.forEach(t => t.classList.remove('active-tab'));
            this.classList.add('active-tab');
            
            // Filter orders
            currentStatus = this.dataset.status;
            filterOrders();
        });
    });
}

// Load and filter orders
function loadOrders() {
    orders = sampleOrders;
    filterOrders();
}

function filterOrders() {
    const filteredOrders = currentStatus === 'all' 
        ? orders 
        : orders.filter(order => order.status === currentStatus);
    
    renderOrders(filteredOrders);
}

// Render orders to the list
function renderOrders(orders) {
    const container = document.getElementById('ordersList');
    const emptyState = document.getElementById('emptyState');
    
    if (orders.length === 0) {
        container.innerHTML = '';
        emptyState.classList.remove('hidden');
        return;
    }
    
    emptyState.classList.add('hidden');
    container.innerHTML = orders.map(order => createOrderElement(order)).join('');
}

// Create order element with progress bar
function createOrderElement(order) {
    const progress = calculateProgress(order.timeline);
    const statusInfo = getStatusInfo(order.status);
    
    return `
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100" onclick="showStatusModal(${order.id})">
            <!-- Order Header -->
            <div class="flex justify-between items-start mb-3">
                <div>
                    <h3 class="font-semibold text-gray-800">${order.customer_name}</h3>
                    <p class="text-sm text-gray-500">${order.code}</p>
                </div>
                <div class="text-right">
                    <p class="font-semibold text-gray-800">Rp ${order.price.toLocaleString()}</p>
                    <span class="inline-block px-2 py-1 ${statusInfo.color} text-xs rounded-full mt-1">
                        ${statusInfo.text}
                    </span>
                </div>
            </div>
            
            <!-- Service Info -->
            <div class="flex items-center justify-between text-sm text-gray-600 mb-4">
                <span>${order.service}</span>
                <span>${order.weight} kg</span>
            </div>
            
            <!-- Visual Progress Bar -->
            <div class="mb-3">
                <div class="flex justify-between items-center mb-2">
                    ${getProgressSteps(order.timeline)}
                </div>
                <div class="progress-bar bg-gray-200 relative">
                    <div class="progress-bar bg-blue-500 absolute top-0 left-0" style="width: ${progress.percentage}%"></div>
                </div>
            </div>
            
            <!-- Timeline Info -->
            <div class="text-xs text-gray-500">
                <i class="fas fa-clock mr-1"></i>
                ${getCurrentStepText(order.timeline)}
            </div>
        </div>
    `;
}

// Calculate progress percentage
function calculateProgress(timeline) {
    const completedSteps = timeline.filter(step => step.completed).length;
    const totalSteps = timeline.length;
    const percentage = (completedSteps / totalSteps) * 100;
    
    return { completedSteps, totalSteps, percentage };
}

// Get progress steps with dots
function getProgressSteps(timeline) {
    const steps = ['Baru', 'Cuci', 'Setrika', 'Selesai', 'Ambil'];
    
    return steps.map((step, index) => {
        const isCompleted = timeline[index]?.completed || false;
        const isCurrent = timeline[index]?.completed === false && 
                         timeline.findIndex(t => !t.completed) === index;
        
        let dotClass = 'status-dot bg-gray-300';
        if (isCompleted) dotClass = 'status-dot bg-green-500';
        if (isCurrent) dotClass = 'status-dot bg-blue-500 border-2 border-blue-300';
        
        return `
            <div class="flex flex-col items-center">
                <div class="${dotClass} mb-1"></div>
                <span class="text-xs text-gray-500 whitespace-nowrap">${step}</span>
            </div>
        `;
    }).join('');
}

// Get current step text
function getCurrentStepText(timeline) {
    const currentStep = timeline.find(step => !step.completed);
    const lastCompleted = timeline.filter(step => step.completed).pop();
    
    if (!currentStep) return 'Pesanan sudah diambil';
    
    const statusText = {
        'new': 'Menunggu diproses',
        'washing': 'Sedang dicuci',
        'ironing': 'Sedang disetrika', 
        'ready': 'Siap diambil',
        'picked_up': 'Sudah diambil'
    };
    
    return statusText[currentStep.status] || 'Sedang diproses';
}

// Get status info
function getStatusInfo(status) {
    const statusMap = {
        'new': { text: 'Baru', color: 'bg-blue-100 text-blue-600' },
        'washing': { text: 'Dicuci', color: 'bg-orange-100 text-orange-600' },
        'ironing': { text: 'Disetrika', color: 'bg-purple-100 text-purple-600' },
        'ready': { text: 'Selesai', color: 'bg-green-100 text-green-600' },
        'picked_up': { text: 'Diambil', color: 'bg-gray-100 text-gray-600' }
    };
    
    return statusMap[status] || { text: 'Unknown', color: 'bg-gray-100 text-gray-600' };
}

// Show status update modal
function showStatusModal(orderId) {
    const order = orders.find(o => o.id === orderId);
    if (!order) return;
    
    // Set modal content
    document.getElementById('statusOrderId').value = order.id;
    document.getElementById('statusOrderCode').textContent = order.code;
    document.getElementById('statusCustomerName').textContent = order.customer_name;
    
    // Update status options
    updateStatusOptions(order.status);
    
    // Show modal
    document.getElementById('statusModal').classList.remove('hidden');
}

// Update status options based on current status
function updateStatusOptions(currentStatus) {
    const statusOptions = document.getElementById('statusOptions');
    const statusOrder = ['new', 'washing', 'ironing', 'ready', 'picked_up'];
    const currentIndex = statusOrder.indexOf(currentStatus);
    
    statusOptions.innerHTML = statusOrder.map((status, index) => {
        const statusInfo = getStatusInfo(status);
        const isCompleted = index < currentIndex;
        const isCurrent = index === currentIndex;
        const isDisabled = index > currentIndex + 1; // Can only move to next step
        
        return `
            <button 
                class="status-option w-full text-left p-3 rounded-lg mb-2 transition-all duration-200 ${isCompleted ? 'bg-green-50 text-green-700' : isCurrent ? 'bg-blue-50 text-blue-700' : 'bg-gray-50 text-gray-500'} ${isDisabled ? 'opacity-50 cursor-not-allowed' : 'hover:bg-blue-100'}" 
                data-status="${status}"
                ${isDisabled ? 'disabled' : ''}
                onclick="updateOrderStatus('${status}')"
            >
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-8 h-8 rounded-full ${isCompleted ? 'bg-green-500' : isCurrent ? 'bg-blue-500' : 'bg-gray-300'} flex items-center justify-center mr-3">
                            ${isCompleted ? '<i class="fas fa-check text-white text-sm"></i>' : ''}
                        </div>
                        <span>${statusInfo.text}</span>
                    </div>
                    ${isCurrent ? '<i class="fas fa-check-circle text-blue-500"></i>' : ''}
                </div>
            </button>
        `;
    }).join('');
}

// Update order status
function updateOrderStatus(newStatus) {
    const orderId = document.getElementById('statusOrderId').value;
    const order = orders.find(o => o.id == orderId);
    
    if (order) {
        // Update timeline
        order.timeline.forEach(step => {
            if (step.status === newStatus) {
                step.completed = true;
                step.time = new Date().toISOString();
            }
        });
        
        // Update order status
        order.status = newStatus;
        
        // Show success message
        alert(`Status pesanan berhasil diubah menjadi: ${getStatusInfo(newStatus).text}`);
        
        // Close modal and refresh
        closeStatusModal();
        filterOrders();
    }
}

// Close status modal
function closeStatusModal() {
    document.getElementById('statusModal').classList.add('hidden');
}
</script>
@endpush