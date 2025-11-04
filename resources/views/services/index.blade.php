@extends('layouts.mobile')

@section('title', 'Layanan & Harga')

@section('content')
    <div class="pb-4">
        <!-- Header dengan Total dan Filter -->
        <div class="px-4 py-3 bg-white border-b border-gray-200">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-lg font-semibold text-gray-800">Layanan & Harga</h1>
                    <p class="text-sm text-gray-500">Kelola semua jenis layanan</p>
                </div>
                <div class="text-right">
                    <p class="text-2xl font-bold text-blue-600">{{ $totalServices }}</p>
                    <p class="text-xs text-gray-500">Total Layanan</p>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="grid grid-cols-3 gap-2 mt-3 text-center">
                <div class="bg-blue-50 rounded-lg p-2">
                    <p class="text-xs text-blue-600">Kiloan</p>
                    <p class="font-semibold text-blue-700">{{ collect($servicesData)->where('type', 'kiloan')->count() }}</p>
                </div>
                <div class="bg-green-50 rounded-lg p-2">
                    <p class="text-xs text-green-600">Satuan</p>
                    <p class="font-semibold text-green-700">{{ collect($servicesData)->where('type', 'satuan')->count() }}</p>
                </div>
                <div class="bg-purple-50 rounded-lg p-2">
                    <p class="text-xs text-purple-600">Khusus</p>
                    <p class="font-semibold text-purple-700">{{ collect($servicesData)->where('type', 'khusus')->count() }}</p>
                </div>
            </div>
        </div>

        <!-- Categories Swipe -->
        <div class="bg-white border-b border-gray-200">
            <div class="swipeable-categories flex overflow-x-auto px-4 space-x-1 py-2" style="scrollbar-width: none;">
                <button class="category-btn flex-shrink-0 px-4 py-2 bg-blue-500 text-white rounded-full text-sm font-medium active" data-category="all">
                    Semua
                </button>
                @foreach($categories as $category)
                    <button class="category-btn flex-shrink-0 px-4 py-2 bg-gray-100 text-gray-600 rounded-full text-sm font-medium" data-category="{{ $category }}">
                        {{ $category }}
                    </button>
                @endforeach
            </div>
        </div>

        <!-- Services Grid -->
        <div id="servicesGrid" class="grid grid-cols-1 gap-3 p-4">
            @foreach($servicesData as $service)
                <div class="service-card bg-white rounded-xl p-4 shadow-sm border border-gray-100 active:scale-95 transition-transform duration-200" 
                     data-service-id="{{ $service['id'] }}"
                     data-category="{{ $service['category'] }}"
                     onclick="showServiceDetail({{ $service['id'] }})">
                    
                    <!-- Header dengan Status dan Type Badge -->
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex items-center space-x-3 flex-1">
                            <div class="w-12 h-12 rounded-xl flex items-center justify-center {{ $service['color'] }}">
                                <i class="{{ $service['icon'] }} text-white text-lg"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center space-x-2 mb-1">
                                    <h3 class="font-semibold text-gray-800 truncate">{{ $service['name'] }}</h3>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <span class="service-type-badge text-xs px-2 py-1 rounded-full 
                                                {{ $service['type'] == 'kiloan' ? 'bg-blue-100 text-blue-600' :
                                                   ($service['type'] == 'satuan' ? 'bg-green-100 text-green-600' :
                                                   'bg-purple-100 text-purple-600') }}">
                                        {{ ucfirst($service['type']) }}
                                    </span>
                                    <span class="text-sm text-gray-500">{{ $service['category'] }}</span>
                                </div>
                                @if(!empty($service['description']))
                                    <p class="text-xs text-gray-400 mt-1">
                                        {{ \Illuminate\Support\Str::limit($service['description'], 50) }}
                                    </p>
                                @endif
                            </div>
                        </div>
                        <div class="flex items-center space-x-2">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" {{ $service['active'] ? 'checked' : '' }} 
                                       class="sr-only peer service-toggle"
                                       data-service-id="{{ $service['id'] }}"
                                       onchange="toggleServiceStatus({{ $service['id'] }}, this.checked)">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-500">
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Service Items -->
                    <div class="space-y-2">
                        @foreach($service['items'] as $item)
                            <div class="flex items-center justify-between py-2 border-b border-gray-100 last:border-b-0">
                                <div class="flex-1">
                                    <span class="text-sm text-gray-600">{{ $item['name'] }}</span>
                                    <div class="flex items-center space-x-2 text-xs text-gray-400 mt-1">
                                        <span>{{ $item['unit'] }}</span>
                                        <span>•</span>
                                        <span><i class="fas fa-clock mr-1"></i>{{ $item['estimation_time'] }} jam</span>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <span class="font-semibold text-gray-800">Rp {{ number_format($item['price'], 0, ',', '.') }}</span>
                                    <button class="edit-item-btn w-8 h-8 bg-blue-50 text-blue-600 rounded-lg flex items-center justify-center hover:bg-blue-100 transition-colors duration-200"
                                            onclick="event.stopPropagation(); showEditItemModal({{ $item['id'] }}, {{ $service['id'] }})">
                                        <i class="fas fa-edit text-xs"></i>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Footer Actions -->
                    <div class="mt-3 pt-3 border-t border-gray-100 flex justify-between items-center">
                        <div class="flex space-x-3">
                            <button class="text-xs text-gray-500 hover:text-gray-700 flex items-center space-x-1 transition-colors duration-200">
                                <i class="fas fa-history"></i>
                                <span>Riwayat</span>
                            </button>
                            <button class="edit-service-btn text-xs text-blue-500 hover:text-blue-700 flex items-center space-x-1 transition-colors duration-200"
                                    onclick="event.stopPropagation(); showEditServiceModal({{ $service['id'] }})">
                                <i class="fas fa-cog"></i>
                                <span>Edit</span>
                            </button>
                            <button class="delete-service-btn text-xs text-red-500 hover:text-red-700 flex items-center space-x-1 transition-colors duration-200"
                                    onclick="event.stopPropagation(); deleteService({{ $service['id'] }}, '{{ $service['name'] }}')">
                                <i class="fas fa-trash"></i>
                                <span>Hapus</span>
                            </button>
                        </div>
                        <span class="text-xs px-2 py-1 rounded-full {{ count($service['items']) > 1 ? 'bg-purple-100 text-purple-600' : 'bg-gray-100 text-gray-600' }}">
                            {{ count($service['items']) }} item
                        </span>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Empty State -->
        <div id="emptyState" class="hidden text-center py-8 px-4">
            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
                <i class="fas fa-tshirt text-gray-400 text-xl"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-700 mb-2">Belum Ada Layanan</h3>
            <p class="text-gray-500 mb-4">Tambahkan layanan pertama untuk memulai</p>
            <button class="bg-blue-500 text-white px-6 py-2 rounded-lg font-semibold hover:bg-blue-600 transition-colors duration-200"
                    onclick="showAddServiceModal()">
                Tambah Layanan
            </button>
        </div>
    </div>

    <!-- Floating Action Button -->
    <button id="fabButton"
        class="fixed bottom-20 right-4 w-14 h-14 bg-blue-500 text-white rounded-full shadow-lg flex items-center justify-center hover:bg-blue-600 active:scale-95 transition-all duration-200 z-20"
        onclick="showAddServiceModal()">
        <i class="fas fa-plus text-lg"></i>
    </button>

    <!-- Toast Notification -->
    <div id="toast" class="fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 hidden transition-all duration-300">
        <div class="flex items-center space-x-2">
            <i class="fas fa-check-circle"></i>
            <span id="toastMessage">Operasi berhasil!</span>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg p-6 flex items-center space-x-3">
            <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-500"></div>
            <span class="text-gray-700">Memproses...</span>
        </div>
    </div>

@include('partials.service-add-modal')
@include('partials.service-edit-modal')
@include('partials.service-edit-item-modal')
@include('partials.service-detail-modal')
@include('partials.service-add-item-modal')
@endsection

@push('styles')
    <style>
        .swipeable-categories {
            scroll-snap-type: x mandatory;
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        .swipeable-categories::-webkit-scrollbar {
            display: none;
        }
        .swipeable-categories button {
            scroll-snap-align: start;
        }
        .service-card {
            transition: all 0.2s ease;
            cursor: pointer;
        }
        .service-card:active {
            transform: scale(0.98);
        }
        .category-btn.active {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
        }
        .custom-toast {
            animation: slideInDown 0.3s ease-out;
        }
        @keyframes slideInDown {
            from {
                transform: translateY(-100%);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        .service-item-card {
            transition: all 0.2s ease;
        }
        .service-item-card:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            background-color: #f8fafc;
        }
        .modal-enter {
            animation: modalEnter 0.3s ease-out;
        }
        @keyframes modalEnter {
            from {
                opacity: 0;
                transform: scale(0.9);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }
        .service-card, .service-item-card {
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }
    </style>
@endpush

@push('scripts')
<script>
    // Global variables
    let currentService = null;
    let currentItem = null;
    let currentServiceId = null;

    // API Base URL
    const API_BASE = '/services';

    // Utility Functions
    function showLoading() {
        document.getElementById('loadingOverlay').classList.remove('hidden');
    }

    function hideLoading() {
        document.getElementById('loadingOverlay').classList.add('hidden');
    }

    function showToast(message, type = 'success') {
        const toast = document.getElementById('toast');
        const toastMessage = document.getElementById('toastMessage');
        
        toastMessage.textContent = message;
        toast.className = `fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg z-50 transition-all duration-300 flex items-center space-x-2 ${
            type === 'success' ? 'bg-green-500 text-white' : 
            type === 'error' ? 'bg-red-500 text-white' : 
            'bg-blue-500 text-white'
        }`;
        
        toast.classList.remove('hidden');
        
        setTimeout(() => {
            toast.classList.add('hidden');
        }, 3000);
    }

    // Service Management Functions
    async function toggleServiceStatus(serviceId, isActive) {
        try {
            showLoading();
            
            const response = await fetch(`${API_BASE}/${serviceId}/toggle`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ active: isActive })
            });

            const result = await response.json();
            
            if (result.success) {
                showToast(`Service ${isActive ? 'diaktifkan' : 'dinonaktifkan'} berhasil`);
            } else {
                showToast(result.message, 'error');
                // Revert toggle state
                const toggle = document.querySelector(`[data-service-id="${serviceId}"]`);
                if (toggle) {
                    toggle.checked = !isActive;
                }
            }
        } catch (error) {
            console.error('Error toggling service:', error);
            showToast('Gagal mengubah status service', 'error');
        } finally {
            hideLoading();
        }
    }

    async function deleteService(serviceId, serviceName) {
        if (!confirm(`Apakah Anda yakin ingin menghapus service "${serviceName}"?`)) {
            return;
        }

        try {
            showLoading();
            
            const response = await fetch(`${API_BASE}/${serviceId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });

            const result = await response.json();
            
            if (result.success) {
                showToast('Service berhasil dihapus');
                // Remove service card from UI
                const serviceCard = document.querySelector(`[data-service-id="${serviceId}"]`);
                if (serviceCard) {
                    serviceCard.remove();
                }
                // Reload page after delay
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                showToast(result.message, 'error');
            }
        } catch (error) {
            console.error('Error deleting service:', error);
            showToast('Gagal menghapus service', 'error');
        } finally {
            hideLoading();
        }
    }

    // Modal Functions
    function showAddServiceModal() {
        const modal = document.getElementById('serviceAddModal');
        modal.classList.remove('hidden');
        modal.classList.add('modal-enter');
    }

    async function showEditServiceModal(serviceId) {
        try {
            showLoading();
            
            const response = await fetch(`${API_BASE}/${serviceId}/edit`);
            const result = await response.json();
            
            if (result.success) {
                currentService = result.service;
                
                // Populate form with service data
                document.getElementById('editServiceId').value = serviceId;
                document.getElementById('editServiceName').value = currentService.name || '';
                document.getElementById('editServiceType').value = currentService.type || 'kiloan';
                document.getElementById('editServiceCategory').value = currentService.category || 'Cuci';
                document.getElementById('editServiceDescription').value = currentService.description || '';
                document.getElementById('editServiceIcon').value = currentService.icon || 'fas fa-tshirt';
                document.getElementById('editServiceColor').value = currentService.color || 'blue-500';
                
                const modal = document.getElementById('editServiceModal');
                modal.classList.remove('hidden');
                modal.classList.add('modal-enter');
            } else {
                showToast(result.message, 'error');
            }
        } catch (error) {
            console.error('Error loading service for edit:', error);
            showToast('Gagal memuat data service', 'error');
        } finally {
            hideLoading();
        }
    }

    async function showEditItemModal(itemId, serviceId) {
        try {
            showLoading();
            
            // First get service data
            const serviceResponse = await fetch(`${API_BASE}/${serviceId}`);
            const serviceResult = await serviceResponse.json();
            
            if (serviceResult.success) {
                currentService = serviceResult.service;
                // Find the specific item
                currentItem = currentService.items.find(item => item.id === itemId);
                
                if (currentItem) {
                    // Populate form with item data
                    document.getElementById('editItemServiceId').value = serviceId;
                    document.getElementById('editItemId').value = itemId;
                    document.getElementById('editItemName').value = currentItem.name || '';
                    document.getElementById('editItemPrice').value = currentItem.price || '';
                    document.getElementById('editItemUnit').value = currentItem.unit || 'kg';
                    document.getElementById('editItemEstimation').value = currentItem.estimation_time || '';
                    
                    const modal = document.getElementById('editItemModal');
                    modal.classList.remove('hidden');
                    modal.classList.add('modal-enter');
                } else {
                    showToast('Item tidak ditemukan', 'error');
                }
            } else {
                showToast(serviceResult.message, 'error');
            }
        } catch (error) {
            console.error('Error loading item for edit:', error);
            showToast('Gagal memuat data item', 'error');
        } finally {
            hideLoading();
        }
    }

    async function showServiceDetail(serviceId) {
        try {
            showLoading();
            
            const response = await fetch(`services/${serviceId}`);
            const result = await response.json();
            
            if (result.success) {
                currentService = result.service;
                currentServiceId = serviceId;
                
                // Populate basic service info
                document.getElementById('detailServiceName').textContent = currentService.name;
                document.getElementById('detailServiceCategory').textContent = currentService.category;
                
                // Set service type badge
                const typeBadge = document.getElementById('detailServiceType');
                typeBadge.textContent = currentService.type.charAt(0).toUpperCase() + currentService.type.slice(1);
                typeBadge.className = `text-xs px-2 py-1 rounded-full ${
                    currentService.type === 'kiloan' ? 'bg-blue-100 text-blue-600' :
                    currentService.type === 'satuan' ? 'bg-green-100 text-green-600' :
                    'bg-purple-100 text-purple-600'
                }`;
                
                // Set service icon and color
                const iconContainer = document.getElementById('detailServiceIcon');
                iconContainer.className = `w-16 h-16 rounded-xl flex items-center justify-center bg-${currentService.color}`;
                iconContainer.querySelector('i').className = currentService.icon + ' text-white text-2xl';
                
                // Set service description
                const descContainer = document.getElementById('detailServiceDescriptionContainer');
                const descElement = document.getElementById('detailServiceDescription');
                if (currentService.description && currentService.description.trim() !== '') {
                    descElement.textContent = currentService.description;
                    descContainer.classList.remove('hidden');
                } else {
                    descContainer.classList.add('hidden');
                }
                
                // Set service status
                const statusElement = document.getElementById('detailActiveStatus');
                statusElement.textContent = currentService.active ? 'Aktif' : 'Nonaktif';
                statusElement.className = `font-medium ${currentService.active ? 'text-green-600' : 'text-red-600'}`;
                
                // Calculate and display statistics
                const totalItems = currentService.items.length;
                const avgPrice = totalItems > 0 ? 
                    currentService.items.reduce((sum, item) => sum + item.price, 0) / totalItems : 0;
                
                document.getElementById('detailTotalItems').textContent = totalItems;
                document.getElementById('detailAvgPrice').textContent = `Rp ${Math.round(avgPrice).toLocaleString('id-ID')}`;
                document.getElementById('detailItemsCount').textContent = `${totalItems} item${totalItems > 1 ? 's' : ''}`;
                
                // Populate service items
                const itemsContainer = document.getElementById('detailServiceItems');
                itemsContainer.innerHTML = '';
                
                currentService.items.forEach((item, index) => {
                    const itemElement = document.createElement('div');
                    itemElement.className = 'service-item-card bg-gray-50 rounded-lg p-3 border border-gray-200 cursor-pointer';
                    itemElement.onclick = function(event) {
                        event.stopPropagation();
                        showEditItemModal(item.id, serviceId);
                    };
                    itemElement.innerHTML = `
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <h5 class="font-medium text-gray-800">${item.name}</h5>
                                <div class="flex items-center space-x-3 mt-2 text-xs text-gray-500">
                                    <span class="flex items-center space-x-1">
                                        <i class="fas fa-tag"></i>
                                        <span>Rp ${item.price.toLocaleString('id-ID')}</span>
                                    </span>
                                    <span class="flex items-center space-x-1">
                                        <i class="fas fa-ruler"></i>
                                        <span>${item.unit}</span>
                                    </span>
                                    <span class="flex items-center space-x-1">
                                        <i class="fas fa-clock"></i>
                                        <span>${item.estimation_time} jam</span>
                                    </span>
                                </div>
                            </div>
                            <div class="text-blue-500">
                                <i class="fas fa-edit"></i>
                            </div>
                        </div>
                    `;
                    itemsContainer.appendChild(itemElement);
                });
                
                // Show modal
                const modal = document.getElementById('serviceDetailModal');
                modal.classList.remove('hidden');
                modal.classList.add('modal-enter');
            } else {
                showToast(result.message, 'error');
            }
        } catch (error) {
            console.error('Error loading service detail:', error);
            showToast('Gagal memuat detail service', 'error');
        } finally {
            hideLoading();
        }
    }

    function showAddItemModal() {
        const modal = document.getElementById('addItemModal');
        modal.classList.remove('hidden');
        modal.classList.add('modal-enter');
        
        // Reset form
        document.getElementById('addItemForm').reset();
        document.getElementById('addItemServiceId').value = currentServiceId || '';
    }

    // Close modal functions
    function closeAddServiceModal() {
        const modal = document.getElementById('serviceAddModal');
        modal.classList.add('hidden');
    }

    function closeEditServiceModal() {
        const modal = document.getElementById('editServiceModal');
        modal.classList.add('hidden');
    }

    function closeEditItemModal() {
        const modal = document.getElementById('editItemModal');
        modal.classList.add('hidden');
    }

    function closeServiceDetailModal() {
        const modal = document.getElementById('serviceDetailModal');
        modal.classList.add('hidden');
    }

    function closeAddItemModal() {
        const modal = document.getElementById('addItemModal');
        modal.classList.add('hidden');
    }

    // Form submission handlers
    document.getElementById('addServiceForm')?.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = {
            name: document.getElementById('serviceName').value,
            type: document.getElementById('serviceType').value,
            category: document.getElementById('serviceCategory').value,
            icon: document.getElementById('serviceIcon').value,
            color: document.getElementById('serviceColor').value,
            description: document.getElementById('serviceDescription').value,
            items: [
                {
                    name: 'Item Default',
                    price: 10000,
                    unit: 'kg',
                    estimation_time: 6
                }
            ]
        };

        try {
            showLoading();
            
            const response = await fetch(API_BASE, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(formData)
            });

            const result = await response.json();
            
            if (result.success) {
                showToast('Service berhasil ditambahkan');
                closeAddServiceModal();
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                showToast(result.message, 'error');
            }
        } catch (error) {
            console.error('Error adding service:', error);
            showToast('Gagal menambahkan service', 'error');
        } finally {
            hideLoading();
        }
    });

    document.getElementById('editServiceForm')?.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const serviceId = document.getElementById('editServiceId').value;
        const formData = {
            name: document.getElementById('editServiceName').value,
            category: document.getElementById('editServiceCategory').value,
            icon: document.getElementById('editServiceIcon').value,
            color: document.getElementById('editServiceColor').value,
            description: document.getElementById('editServiceDescription').value,
            new_items: []
        };

        try {
            showLoading();
            
            const response = await fetch(`${API_BASE}/${serviceId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(formData)
            });

            const result = await response.json();
            
            if (result.success) {
                showToast('Service berhasil diupdate');
                closeEditServiceModal();
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                showToast(result.message, 'error');
            }
        } catch (error) {
            console.error('Error updating service:', error);
            showToast('Gagal mengupdate service', 'error');
        } finally {
            hideLoading();
        }
    });

    document.getElementById('editItemForm')?.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const serviceId = document.getElementById('editItemServiceId').value;
        const itemId = document.getElementById('editItemId').value;
        const formData = {
            name: document.getElementById('editItemName').value,
            price: parseFloat(document.getElementById('editItemPrice').value),
            unit: document.getElementById('editItemUnit').value,
            estimation_time: parseInt(document.getElementById('editItemEstimation').value)
        };

        try {
            showLoading();
            
            const response = await fetch(`${API_BASE}/${serviceId}/items/${itemId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(formData)
            });

            const result = await response.json();
            
            if (result.success) {
                showToast('Item berhasil diupdate');
                closeEditItemModal();
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                showToast(result.message, 'error');
            }
        } catch (error) {
            console.error('Error updating item:', error);
            showToast('Gagal mengupdate item', 'error');
        } finally {
            hideLoading();
        }
    });

    document.getElementById('addItemForm')?.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const serviceId = document.getElementById('addItemServiceId').value;
        const formData = {
            name: document.getElementById('addItemName').value,
            price: parseFloat(document.getElementById('addItemPrice').value),
            unit: document.getElementById('addItemUnit').value,
            estimation_time: parseInt(document.getElementById('addItemEstimation').value),
            description: document.getElementById('addItemDescription').value
        };

        try {
            showLoading();
            
            const response = await fetch(`${API_BASE}/${serviceId}/items`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(formData)
            });

            const result = await response.json();
            
            if (result.success) {
                showToast('Item berhasil ditambahkan');
                closeAddItemModal();
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                showToast(result.message, 'error');
            }
        } catch (error) {
            console.error('Error adding item:', error);
            showToast('Gagal menambahkan item', 'error');
        } finally {
            hideLoading();
        }
    });

    // Category filter functionality
    document.addEventListener('DOMContentLoaded', function() {
        const categoryButtons = document.querySelectorAll('.category-btn');
        
        categoryButtons.forEach(button => {
            button.addEventListener('click', function() {
                // Remove active class from all buttons
                categoryButtons.forEach(btn => btn.classList.remove('active', 'bg-blue-500', 'text-white'));
                categoryButtons.forEach(btn => btn.classList.add('bg-gray-100', 'text-gray-600'));
                
                // Add active class to clicked button
                this.classList.remove('bg-gray-100', 'text-gray-600');
                this.classList.add('active', 'bg-blue-500', 'text-white');
                
                const category = this.dataset.category;
                filterServicesByCategory(category);
            });
        });

        // Close modals when clicking outside
        const modals = ['serviceAddModal', 'editServiceModal', 'editItemModal', 'serviceDetailModal', 'addItemModal'];
        
        modals.forEach(modalId => {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.addEventListener('click', function(e) {
                    if (e.target === modal) {
                        modal.classList.add('hidden');
                    }
                });
            }
        });

        // Close modals with escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                modals.forEach(modalId => {
                    const modal = document.getElementById(modalId);
                    if (modal && !modal.classList.contains('hidden')) {
                        modal.classList.add('hidden');
                    }
                });
            }
        });
    });

    function filterServicesByCategory(category) {
        const serviceCards = document.querySelectorAll('.service-card');
        
        serviceCards.forEach(card => {
            if (category === 'all' || card.dataset.category === category) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    }

    // Edit service from detail modal
    document.getElementById('editServiceFromDetail')?.addEventListener('click', function() {
        closeServiceDetailModal();
        setTimeout(() => {
            showEditServiceModal(currentServiceId);
        }, 300);
    });
</script>
@endpush