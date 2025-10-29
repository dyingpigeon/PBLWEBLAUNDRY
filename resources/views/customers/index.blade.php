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
                value="{{ $search ?? '' }}"
            >
            <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                <button id="clearSearch" class="text-gray-400 hover:text-gray-600 {{ $search ? '' : 'hidden' }}">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        
        <!-- Back Button ketika ada search -->
        @if($search)
        <div class="mt-3 flex justify-between items-center">
            <div class="flex items-center space-x-2 text-sm text-gray-600">
                <i class="fas fa-filter"></i>
                <span>Hasil pencarian: "{{ $search }}"</span>
            </div>
            <a href="{{ route('customers.index') }}" 
               class="flex items-center space-x-1 bg-gray-100 hover:bg-gray-200 px-3 py-1 rounded-lg text-sm text-gray-700 transition-colors">
                <i class="fas fa-times"></i>
                <span>Hapus Filter</span>
            </a>
        </div>
        @endif
    </div>

    <!-- Statistics Cards - Sembunyikan ketika search aktif -->
    @if(!$search)
    <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
        <div class="grid grid-cols-2 gap-3">
            <div class="bg-white rounded-xl p-4 shadow-sm text-center">
                <p class="text-2xl font-bold text-blue-600">{{ $stats['total'] }}</p>
                <p class="text-xs text-gray-500 mt-1">Total Pelanggan</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm text-center">
                <p class="text-2xl font-bold text-green-600">{{ $stats['today'] }}</p>
                <p class="text-xs text-gray-500 mt-1">Hari Ini</p>
            </div>
        </div>
    </div>
    @else
    <!-- Search Results Info -->
    <div class="px-4 py-2 bg-blue-50 border-b border-blue-100">
        <div class="flex justify-between items-center">
            <div class="flex items-center space-x-2 text-sm text-blue-700">
                <i class="fas fa-info-circle"></i>
                <span>Ditemukan {{ count($customers) }} hasil untuk "{{ $search }}"</span>
            </div>
            <a href="{{ route('customers.index') }}" 
               class="flex items-center space-x-1 bg-white hover:bg-blue-100 px-3 py-1 rounded-lg text-sm text-blue-600 transition-colors border border-blue-200">
                <i class="fas fa-arrow-left"></i>
                <span>Kembali</span>
            </a>
        </div>
    </div>
    @endif

    <!-- Customers List -->
    <div id="customersList" class="space-y-2 px-4 mt-2">
        @forelse($customers as $customer)
            <div class="customer-item bg-white rounded-xl p-4 shadow-sm border border-gray-100 relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3 flex-1">
                        <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                            <span class="text-blue-600 font-semibold">
                                {{ substr($customer->name, 0, 1) }}{{ substr(strstr($customer->name, ' ') ?: '', 1, 1) }}
                            </span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="font-semibold text-gray-800 truncate">{{ $customer->name }}</h3>
                            @if($customer->phone)
                                <p class="text-sm text-gray-500 truncate">
                                    <i class="fas fa-phone mr-1"></i>{{ $customer->phone }}
                                </p>
                            @endif
                            @if($customer->email)
                                <p class="text-sm text-gray-500 truncate">
                                    <i class="fas fa-envelope mr-1"></i>{{ $customer->email }}
                                </p>
                            @endif
                            @if($customer->address)
                                <p class="text-xs text-gray-400 truncate mt-1">
                                    <i class="fas fa-map-marker-alt mr-1"></i>{{ Str::limit($customer->address, 40) }}
                                </p>
                            @endif
                        </div>
                    </div>
                    <div class="swipe-actions absolute inset-y-0 right-0 flex items-center space-x-1 pr-4 bg-white rounded-xl">
                        <button class="edit-btn w-10 h-10 bg-blue-500 text-white rounded-full flex items-center justify-center" 
                                onclick="editCustomer({{ $customer->id }}, '{{ addslashes($customer->name) }}', '{{ $customer->phone }}', '{{ addslashes($customer->email) }}', '{{ addslashes($customer->address) }}', '{{ addslashes($customer->notes) }}')">
                            <i class="fas fa-edit text-sm"></i>
                        </button>
                        <button class="delete-btn w-10 h-10 bg-red-500 text-white rounded-full flex items-center justify-center"
                                onclick="deleteCustomer({{ $customer->id }}, '{{ addslashes($customer->name) }}')">
                            <i class="fas fa-trash text-sm"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Customer Notes (if any) -->
                @if($customer->notes)
                    <div class="mt-3 pt-3 border-t border-gray-100">
                        <p class="text-xs text-gray-500">
                            <i class="fas fa-sticky-note mr-1"></i>
                            {{ Str::limit($customer->notes, 60) }}
                        </p>
                    </div>
                @endif
            </div>
        @empty
            <!-- Empty State -->
            <div class="text-center py-8">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-users text-gray-300 text-xl"></i>
                </div>
                
                @if($search)
                    <p class="text-gray-500 font-medium">Tidak ada hasil untuk "{{ $search }}"</p>
                    <p class="text-gray-400 text-sm mt-1">Coba kata kunci lain atau hapus filter</p>
                    <button onclick="clearSearchAndReload()" 
                           class="mt-4 bg-blue-500 text-white px-6 py-2 rounded-lg text-sm font-semibold hover:bg-blue-600 transition-colors">
                        Hapus Pencarian
                    </button>
                @else
                    <p class="text-gray-500 font-medium">Belum ada pelanggan</p>
                    <p class="text-gray-400 text-sm mt-1">Tambahkan pelanggan pertama Anda</p>
                    <button onclick="showAddCustomerModal()" 
                           class="mt-4 bg-blue-500 text-white px-6 py-2 rounded-lg text-sm font-semibold hover:bg-blue-600 transition-colors">
                        Tambah Pelanggan Pertama
                    </button>
                @endif
            </div>
        @endforelse
    </div>
</div>

<!-- Floating Action Button -->
<button 
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
    // JavaScript untuk modal functionality
    document.addEventListener('DOMContentLoaded', function() {
        setupSwipeActions();
        setupSearch();
        
        // Set initial clear button state
        const searchInput = document.getElementById('searchInput');
        const clearSearch = document.getElementById('clearSearch');
        if (searchInput.value) {
            clearSearch.classList.remove('hidden');
        }
    });

    // Setup swipe actions for customer items
    function setupSwipeActions() {
        const customerItems = document.querySelectorAll('.customer-item');
        
        customerItems.forEach(item => {
            let startX;
            let currentX;
            let isSwiped = false;

            item.addEventListener('touchstart', e => {
                startX = e.touches[0].clientX;
                item.style.transition = 'none';
                
                // Reset other items
                customerItems.forEach(otherItem => {
                    if (otherItem !== item) {
                        otherItem.style.transform = 'translateX(0)';
                    }
                });
            });

            item.addEventListener('touchmove', e => {
                if (!startX) return;
                
                currentX = e.touches[0].clientX;
                const diff = startX - currentX;
                
                if (diff > 0) { // Swipe left (show actions)
                    item.style.transform = `translateX(-${Math.min(diff, 80)}px)`;
                    isSwiped = diff > 30;
                }
            });

            item.addEventListener('touchend', () => {
                item.style.transition = 'transform 0.3s ease';
                
                if (isSwiped) {
                    item.style.transform = 'translateX(-80px)';
                } else {
                    item.style.transform = 'translateX(0)';
                }
                
                startX = null;
            });
        });

        // Reset swipe when clicking/touching elsewhere
        document.addEventListener('touchstart', function resetSwipe(e) {
            if (!e.target.closest('.customer-item')) {
                customerItems.forEach(item => {
                    item.style.transform = 'translateX(0)';
                });
            }
        });
    }

    // Setup search functionality
    function setupSearch() {
        const searchInput = document.getElementById('searchInput');
        const clearSearch = document.getElementById('clearSearch');
        let searchTimeout;

        searchInput.addEventListener('input', function(e) {
            searchQuery = e.target.value;
            clearTimeout(searchTimeout);
            
            // Show/hide clear button
            clearSearch.classList.toggle('hidden', !e.target.value);
            
            // Submit form after delay (for better UX)
            searchTimeout = setTimeout(() => {
                window.location.href = '{{ route("customers.index") }}?search=' + encodeURIComponent(searchQuery);
            }, 800);
        });

        clearSearch.addEventListener('click', function() {
            searchInput.value = '';
            clearSearch.classList.add('hidden');
            window.location.href = '{{ route("customers.index") }}';
        });
    }

    // Clear search and reload
    function clearSearchAndReload() {
        window.location.href = '{{ route("customers.index") }}';
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

    // Edit customer - menggunakan modal
    function editCustomer(id, name, phone, email, address, notes) {
        document.getElementById('editCustomerId').value = id;
        document.getElementById('editCustomerName').value = name;
        document.getElementById('editCustomerPhone').value = phone || '';
        document.getElementById('editCustomerEmail').value = email || '';
        document.getElementById('editCustomerAddress').value = address || '';
        document.getElementById('editCustomerNotes').value = notes || '';
        
        document.getElementById('editCustomerModal').classList.remove('hidden');
    }

    // Close edit modal
    function closeEditCustomerModal() {
        document.getElementById('editCustomerModal').classList.add('hidden');
    }

    // Delete customer confirmation
    function deleteCustomer(id, name) {
        document.getElementById('deleteCustomerId').value = id;
        document.getElementById('deleteCustomerName').textContent = name;
        document.getElementById('deleteCustomerModal').classList.remove('hidden');
    }

    // Close delete modal
    function closeDeleteModal() {
        document.getElementById('deleteCustomerModal').classList.add('hidden');
    }

    // Submit delete form
    function submitDeleteForm() {
        document.getElementById('deleteCustomerForm').submit();
    }

    // Quick actions
    function quickCall(phone) {
        if (phone) {
            window.location.href = 'tel:' + phone;
        }
    }

    function quickEmail(email) {
        if (email) {
            window.location.href = 'mailto:' + email;
        }
    }
</script>

<style>
.customer-item {
    transition: all 0.3s ease;
    touch-action: pan-y;
}

.customer-item:active {
    transform: scale(0.98);
}

.swipe-actions {
    opacity: 0;
    transition: opacity 0.3s ease;
}

.customer-item:hover .swipe-actions,
.customer-item[style*="translateX(-80px)"] .swipe-actions {
    opacity: 1;
}

/* Loading animation */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.customer-item {
    animation: fadeIn 0.3s ease;
}
</style>
@endpush