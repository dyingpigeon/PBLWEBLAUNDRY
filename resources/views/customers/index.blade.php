@extends('layouts.mobile')

@section('title', 'Manajemen Pelanggan')

@section('content')
<div class="pb-4">
    <!-- Search Bar Sticky Top -->
    <div class="bg-white sticky top-0 z-10 px-4 py-3 border-b border-gray-200">
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="fas fa-search text-gray-400"></i>
            </div>
            <input 
                type="text" 
                id="searchInput"
                placeholder="Cari nama, telepon, atau alamat..."
                class="w-full pl-10 pr-4 py-3 bg-gray-100 border-0 rounded-xl focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all duration-200"
            >
            <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                <button id="clearSearch" class="text-gray-400 hover:text-gray-600 hidden">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Customers List -->
    <div id="customersList" class="space-y-2 px-4 mt-2">
        <!-- Customers will be loaded here -->
    </div>

    <!-- Loading Indicator -->
    <div id="loadingIndicator" class="hidden py-4 text-center">
        <i class="fas fa-spinner fa-spin text-blue-500 text-xl"></i>
        <p class="text-gray-500 text-sm mt-2">Memuat data...</p>
    </div>

    <!-- Empty State -->
    <div id="emptyState" class="hidden text-center py-8">
        <i class="fas fa-users text-gray-300 text-4xl mb-3"></i>
        <p class="text-gray-500">Belum ada pelanggan</p>
        <button class="mt-3 bg-blue-500 text-white px-4 py-2 rounded-lg text-sm">
            Tambah Pelanggan Pertama
        </button>
    </div>
</div>

<!-- Floating Action Button -->
<button 
    id="fabButton"
    class="fixed bottom-20 right-4 w-14 h-14 bg-blue-500 text-white rounded-full shadow-lg flex items-center justify-center hover:bg-blue-600 active:scale-95 transition-all duration-200 z-20"
    onclick="showAddCustomerModal()"
>
    <i class="fas fa-plus text-lg"></i>
</button>

<!-- Quick Add Modal (Bottom Sheet) -->
@include('partials.customer-add-modal')

<!-- Edit Modal -->
@include('partials.customer-edit-modal')

<!-- Delete Confirmation -->
@include('partials.customer-delete-modal')
@endsection

@push('scripts')
<script>
    let currentPage = 1;
    let isLoading = false;
    let hasMore = true;
    let searchQuery = '';

    // Load customers on page load
    document.addEventListener('DOMContentLoaded', function() {
        loadCustomers();
        setupEventListeners();
    });

    // Setup event listeners
    function setupEventListeners() {
        // Search functionality
        const searchInput = document.getElementById('searchInput');
        const clearSearch = document.getElementById('clearSearch');

        searchInput.addEventListener('input', function(e) {
            searchQuery = e.target.value;
            currentPage = 1;
            document.getElementById('customersList').innerHTML = '';
            loadCustomers();
            
            // Show/hide clear button
            clearSearch.classList.toggle('hidden', !e.target.value);
        });

        clearSearch.addEventListener('click', function() {
            searchInput.value = '';
            searchQuery = '';
            clearSearch.classList.add('hidden');
            currentPage = 1;
            document.getElementById('customersList').innerHTML = '';
            loadCustomers();
        });

        // Infinite scroll
        window.addEventListener('scroll', function() {
            if (isLoading || !hasMore) return;

            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            const windowHeight = window.innerHeight;
            const documentHeight = document.documentElement.scrollHeight;

            if (scrollTop + windowHeight >= documentHeight - 100) {
                loadCustomers();
            }
        });

        // Pull to refresh
        let startY;
        const customersList = document.getElementById('customersList');
        
        customersList.addEventListener('touchstart', e => {
            startY = e.touches[0].clientY;
        });

        customersList.addEventListener('touchmove', e => {
            if (!startY || window.pageYOffset > 0) return;
            
            const currentY = e.touches[0].clientY;
            const diff = startY - currentY;
            
            if (diff < -50) { // Pull down to refresh
                refreshCustomers();
            }
        });
    }

    // Load customers with infinite scroll
    function loadCustomers() {
        if (isLoading) return;
        
        isLoading = true;
        document.getElementById('loadingIndicator').classList.remove('hidden');

        // Simulate API call
        setTimeout(() => {
            const customers = generateMockCustomers(currentPage, searchQuery);
            
            if (customers.length > 0) {
                renderCustomers(customers);
                currentPage++;
                document.getElementById('emptyState').classList.add('hidden');
            } else {
                hasMore = false;
                if (currentPage === 1) {
                    document.getElementById('emptyState').classList.remove('hidden');
                }
            }
            
            document.getElementById('loadingIndicator').classList.add('hidden');
            isLoading = false;
        }, 1000);
    }

    // Refresh customers
    function refreshCustomers() {
        currentPage = 1;
        hasMore = true;
        document.getElementById('customersList').innerHTML = '';
        loadCustomers();
    }

    // Render customers to the list
    function renderCustomers(customers) {
        const container = document.getElementById('customersList');
        
        customers.forEach(customer => {
            const customerElement = createCustomerElement(customer);
            container.appendChild(customerElement);
        });
    }

    // Create customer list item element
    function createCustomerElement(customer) {
        const div = document.createElement('div');
        div.className = 'customer-item bg-white rounded-xl p-4 shadow-sm border border-gray-100';
        div.innerHTML = `
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3 flex-1">
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-user text-blue-600"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="font-semibold text-gray-800 truncate">${customer.name}</h3>
                        <p class="text-sm text-gray-500 truncate">
                            <i class="fas fa-phone mr-1"></i>${customer.phone}
                        </p>
                        <p class="text-xs text-gray-400 truncate mt-1">
                            <i class="fas fa-map-marker-alt mr-1"></i>${customer.address}
                        </p>
                    </div>
                </div>
                <div class="flex items-center space-x-1">
                    <span class="px-2 py-1 bg-green-100 text-green-600 text-xs rounded-full">
                        ${customer.total_orders} pesanan
                    </span>
                </div>
            </div>
            <div class="swipe-actions absolute inset-y-0 right-0 flex items-center space-x-1 pr-4 bg-white rounded-xl">
                <button class="edit-btn w-10 h-10 bg-blue-500 text-white rounded-full flex items-center justify-center" 
                        onclick="editCustomer(${customer.id})">
                    <i class="fas fa-edit text-sm"></i>
                </button>
                <button class="delete-btn w-10 h-10 bg-red-500 text-white rounded-full flex items-center justify-center"
                        onclick="deleteCustomer(${customer.id})">
                    <i class="fas fa-trash text-sm"></i>
                </button>
            </div>
        `;

        // Add swipe functionality
        setupSwipeActions(div);
        return div;
    }

    // Setup swipe actions for customer item
    function setupSwipeActions(element) {
        let startX;
        let currentX;
        let isSwiped = false;

        element.addEventListener('touchstart', e => {
            startX = e.touches[0].clientX;
            element.style.transition = 'none';
        });

        element.addEventListener('touchmove', e => {
            if (!startX) return;
            
            currentX = e.touches[0].clientX;
            const diff = startX - currentX;
            
            if (diff > 0) { // Swipe left (show actions)
                element.style.transform = `translateX(-${Math.min(diff, 80)}px)`;
                isSwiped = diff > 30;
            }
        });

        element.addEventListener('touchend', () => {
            element.style.transition = 'transform 0.3s ease';
            
            if (isSwiped) {
                element.style.transform = 'translateX(-80px)';
            } else {
                element.style.transform = 'translateX(0)';
            }
            
            startX = null;
        });

        // Reset swipe when clicking/touching elsewhere
        document.addEventListener('touchstart', function resetSwipe(e) {
            if (!element.contains(e.target)) {
                element.style.transform = 'translateX(0)';
                isSwiped = false;
            }
        });
    }

    // Show add customer modal
    function showAddCustomerModal() {
        document.getElementById('addCustomerModal').classList.remove('hidden');
        document.getElementById('customerName').focus();
    }

    // Close add customer modal
    function closeAddCustomerModal() {
        document.getElementById('addCustomerModal').classList.add('hidden');
        document.getElementById('addCustomerForm').reset();
    }

    // Edit customer
    function editCustomer(id) {
        // In real app, fetch customer data from API
        const customer = { id, name: 'Nama Customer', phone: '08123456789', address: 'Alamat customer' };
        
        document.getElementById('editCustomerId').value = customer.id;
        document.getElementById('editCustomerName').value = customer.name;
        document.getElementById('editCustomerPhone').value = customer.phone;
        document.getElementById('editCustomerAddress').value = customer.address;
        
        document.getElementById('editCustomerModal').classList.remove('hidden');
    }

    // Close edit modal
    function closeEditCustomerModal() {
        document.getElementById('editCustomerModal').classList.add('hidden');
    }

    // Delete customer confirmation
    function deleteCustomer(id) {
        document.getElementById('deleteCustomerId').value = id;
        document.getElementById('deleteCustomerModal').classList.remove('hidden');
    }

    // Close delete modal
    function closeDeleteModal() {
        document.getElementById('deleteCustomerModal').classList.add('hidden');
    }

    // Mock data generator (replace with actual API calls)
    function generateMockCustomers(page, query = '') {
        if (page > 3) return []; // Simulate limited data
        
        const mockCustomers = [
            { id: 1, name: 'Budi Santoso', phone: '081234567890', address: 'Jl. Merdeka No. 123', total_orders: 12 },
            { id: 2, name: 'Siti Rahayu', phone: '081234567891', address: 'Jl. Sudirman No. 45', total_orders: 8 },
            { id: 3, name: 'Ahmad Fauzi', phone: '081234567892', address: 'Jl. Thamrin No. 67', total_orders: 15 },
            { id: 4, name: 'Dewi Lestari', phone: '081234567893', address: 'Jl. Gatot Subroto No. 89', total_orders: 6 },
            { id: 5, name: 'Rizki Pratama', phone: '081234567894', address: 'Jl. Asia Afrika No. 101', total_orders: 9 },
        ];

        if (query) {
            return mockCustomers.filter(customer => 
                customer.name.toLowerCase().includes(query.toLowerCase()) ||
                customer.phone.includes(query) ||
                customer.address.toLowerCase().includes(query.toLowerCase())
            );
        }

        return mockCustomers;
    }
</script>
@endpush