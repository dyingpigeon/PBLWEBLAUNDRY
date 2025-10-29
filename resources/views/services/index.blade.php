@extends('layouts.mobile')

@section('title', 'Layanan & Harga')

@section('content')
    <div class="pb-4">
        <!-- Header dengan Total -->
        <div class="px-4 py-3 bg-white border-b border-gray-200">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-lg font-semibold text-gray-800">Layanan & Harga</h1>
                    <p class="text-sm text-gray-500">Kelola jenis layanan dan tarif</p>
                </div>
                <div class="text-right">
                    <p class="text-2xl font-bold text-blue-600">{{ $totalServices }}</p>
                    <p class="text-xs text-gray-500">Total Layanan</p>
                </div>
            </div>
        </div>

        <!-- Categories Swipe -->
        <div class="bg-white border-b border-gray-200">
            <div class="swipeable-categories flex overflow-x-auto px-4 space-x-1 py-2" style="scrollbar-width: none;">
                <button
                    class="category-btn flex-shrink-0 px-4 py-2 bg-blue-500 text-white rounded-full text-sm font-medium active"
                    data-category="all">
                    Semua
                </button>
                <button
                    class="category-btn flex-shrink-0 px-4 py-2 bg-gray-100 text-gray-600 rounded-full text-sm font-medium"
                    data-category="cuci">
                    Cuci
                </button>
                <button
                    class="category-btn flex-shrink-0 px-4 py-2 bg-gray-100 text-gray-600 rounded-full text-sm font-medium"
                    data-category="setrika">
                    Setrika
                </button>
                <button
                    class="category-btn flex-shrink-0 px-4 py-2 bg-gray-100 text-gray-600 rounded-full text-sm font-medium"
                    data-category="dry clean">
                    Dry Clean
                </button>
                <button
                    class="category-btn flex-shrink-0 px-4 py-2 bg-gray-100 text-gray-600 rounded-full text-sm font-medium"
                    data-category="lainnya">
                    Lainnya
                </button>
            </div>
        </div>

        <!-- Services Grid -->
        <div id="servicesGrid" class="grid grid-cols-1 gap-3 p-4">
            @foreach($servicesData as $service)
                <div class="service-card bg-white rounded-xl p-4 shadow-sm border border-gray-100 active:scale-95"
                    data-service-id="{{ $service['id'] }}" data-category="{{ strtolower($service['category']) }}">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex items-center space-x-3 flex-1">
                            <div class="w-12 h-12 rounded-xl flex items-center justify-center {{ $service['color'] }}">
                                <i class="{{ $service['icon'] }} text-white text-lg"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="font-semibold text-gray-800 truncate">{{ $service['name'] }}</h3>
                                <p class="text-sm text-gray-500">{{ $service['category'] }}</p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" {{ $service['active'] ? 'checked' : '' }}
                                    class="sr-only peer service-toggle" data-service-id="{{ $service['id'] }}">
                                <div
                                    class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-500">
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="space-y-2">
                        @foreach($service['items'] as $item)
                            <div class="flex items-center justify-between py-2 border-b border-gray-100 last:border-b-0">
                                <span class="text-sm text-gray-600">{{ $item['name'] }}</span>
                                <div class="flex items-center space-x-2">
                                    <span class="font-semibold text-gray-800">Rp
                                        {{ number_format($item['price'], 0, ',', '.') }}</span>
                                    <button
                                        onclick="editServiceItem({{ $service['id'] }}, {{ $item['id'] }}, '{{ $item['name'] }}', {{ $item['price'] }})"
                                        class="w-8 h-8 bg-blue-50 text-blue-600 rounded-lg flex items-center justify-center hover:bg-blue-100">
                                        <i class="fas fa-edit text-xs"></i>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-3 pt-3 border-t border-gray-100 flex justify-between items-center">
                        <button onclick="showPriceHistory({{ $service['id'] }})"
                            class="text-xs text-gray-500 hover:text-gray-700 flex items-center space-x-1">
                            <i class="fas fa-history"></i>
                            <span>Riwayat Harga</span>
                        </button>
                        <span
                            class="text-xs px-2 py-1 rounded-full {{ count($service['items']) > 1 ? 'bg-purple-100 text-purple-600' : 'bg-gray-100 text-gray-600' }}">
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
            <button onclick="showAddServiceModal()" class="bg-blue-500 text-white px-6 py-2 rounded-lg font-semibold">
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

    <!-- Add Service Modal -->
    @include('partials.service-add-modal')

    <!-- Edit Item Modal -->
    <div id="editItemModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50 hidden">
        <div class="bg-white rounded-2xl w-full max-w-md p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Edit Item Layanan</h3>
            <form id="editItemForm">
                <input type="hidden" id="editItemServiceId">
                <input type="hidden" id="editItemId">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Item</label>
                        <input type="text" id="editItemName"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Harga</label>
                        <input type="number" id="editItemPrice"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            min="0" required>
                    </div>
                </div>
                <div class="flex space-x-3 mt-6">
                    <button type="button" onclick="closeEditItemModal()"
                        class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">Batal</button>
                    <button type="submit"
                        class="flex-1 px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">Simpan</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .swipeable-categories {
            scroll-snap-type: x mandatory;
        }

        .swipeable-categories button {
            scroll-snap-align: start;
        }

        .service-card {
            transition: all 0.2s ease;
        }

        .service-card:active {
            transform: scale(0.98);
        }

        .category-btn.active {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
        }
    </style>
@endpush

@push('scripts')
    <script>
        let currentCategory = 'all';
        let priceItemCount = 1;

        // Setup event listeners
        document.addEventListener('DOMContentLoaded', function () {
            setupEventListeners();
            checkEmptyState();
            initializePriceItems();
        });

        function setupEventListeners() {
            // Category filter functionality
            document.querySelectorAll('.category-btn').forEach(btn => {
                btn.addEventListener('click', function () {
                    const category = this.getAttribute('data-category');
                    filterServices(category);

                    // Update active state
                    document.querySelectorAll('.category-btn').forEach(b => {
                        b.classList.remove('active', 'bg-blue-500', 'text-white');
                        b.classList.add('bg-gray-100', 'text-gray-600');
                    });
                    this.classList.add('active', 'bg-blue-500', 'text-white');
                    this.classList.remove('bg-gray-100', 'text-gray-600');
                });
            });

            // Service toggle functionality
            document.querySelectorAll('.service-toggle').forEach(toggle => {
                toggle.addEventListener('change', function () {
                    const serviceId = this.getAttribute('data-service-id');
                    const isActive = this.checked;

                    toggleService(serviceId, isActive);
                });
            });

            // Edit item form submission
            document.getElementById('editItemForm').addEventListener('submit', function (e) {
                e.preventDefault();
                updateServiceItem();
            });

            // Add service form submission
            document.getElementById('addServiceForm').addEventListener('submit', function (e) {
                e.preventDefault();
                handleAddService(e);
            });
        }

        // Floating Button Function - Show Add Service Modal
        function showAddServiceModal() {
            document.getElementById('addServiceModal').classList.remove('hidden');
            document.getElementById('serviceName').focus();
        }

        // Close Add Service Modal
        function closeAddServiceModal() {
            document.getElementById('addServiceModal').classList.add('hidden');
            document.getElementById('addServiceForm').reset();
            resetPriceItems();
        }

        // Initialize price items
        function initializePriceItems() {
            addPriceItem(); // Add one initial item
        }

        // Add new price item row
        function addPriceItem() {
            const container = document.getElementById('priceItems');
            const itemId = priceItemCount++;

            const itemDiv = document.createElement('div');
            itemDiv.className = 'flex space-x-2 price-item';
            itemDiv.innerHTML = `
                    <input type="text" name="items[${itemId}][name]" placeholder="Nama item" 
                           class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-1 focus:ring-blue-500" required>
                    <input type="number" name="items[${itemId}][price]" placeholder="Harga" 
                           class="w-24 px-3 py-2 border border-gray-300 rounded-lg focus:ring-1 focus:ring-blue-500" min="0" required>
                    <button type="button" onclick="removePriceItem(this)" 
                            class="w-10 h-10 bg-red-500 text-white rounded-lg flex items-center justify-center ${priceItemCount === 1 ? 'hidden' : ''}">
                        <i class="fas fa-times"></i>
                    </button>
                `;
            container.appendChild(itemDiv);

            // Show remove buttons if there's more than one item
            updateRemoveButtons();
        }

        // Remove price item row
        function removePriceItem(button) {
            button.closest('.price-item').remove();
            updateRemoveButtons();
        }

        // Update remove buttons visibility
        function updateRemoveButtons() {
            const items = document.querySelectorAll('.price-item');
            const removeButtons = document.querySelectorAll('.price-item button');

            removeButtons.forEach(button => {
                if (items.length > 1) {
                    button.classList.remove('hidden');
                } else {
                    button.classList.add('hidden');
                }
            });
        }

        // Reset price items to initial state
        function resetPriceItems() {
            document.getElementById('priceItems').innerHTML = '';
            priceItemCount = 1;
            addPriceItem();
        }

        // Handle add service form submission
        function handleAddService(event) {
            event.preventDefault();

            const formData = new FormData(event.target);
            const items = [];

            // Collect items data
            document.querySelectorAll('.price-item').forEach((item, index) => {
                const nameInput = item.querySelector('input[type="text"]');
                const priceInput = item.querySelector('input[type="number"]');

                if (nameInput.value && priceInput.value) {
                    items.push({
                        name: nameInput.value,
                        price: parseFloat(priceInput.value)
                    });
                }
            });

            if (items.length === 0) {
                alert('Minimal harus ada satu item harga');
                return;
            }

            const serviceData = {
                name: document.getElementById('serviceName').value,
                category: document.getElementById('serviceCategory').value,
                icon: document.getElementById('serviceIcon').value,
                color: document.getElementById('serviceColor').value,
                items: items
            };

            // Send AJAX request
            fetch('{{ route("services.store") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(serviceData)
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Service berhasil ditambahkan!');
                        closeAddServiceModal();
                        window.location.reload(); // Reload to show new data
                    } else {
                        alert('Gagal menambahkan service: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat menambahkan service');
                });
        }

        function filterServices(category) {
            const serviceCards = document.querySelectorAll('.service-card');
            let visibleCount = 0;

            serviceCards.forEach(card => {
                const cardCategory = card.getAttribute('data-category');

                if (category === 'all' || cardCategory.includes(category)) {
                    card.style.display = 'block';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            // Show/hide empty state
            const emptyState = document.getElementById('emptyState');
            const servicesGrid = document.getElementById('servicesGrid');

            if (visibleCount === 0) {
                servicesGrid.classList.add('hidden');
                emptyState.classList.remove('hidden');
            } else {
                servicesGrid.classList.remove('hidden');
                emptyState.classList.add('hidden');
            }
        }

        function checkEmptyState() {
            const serviceCards = document.querySelectorAll('.service-card');
            const emptyState = document.getElementById('emptyState');
            const servicesGrid = document.getElementById('servicesGrid');

            if (serviceCards.length === 0) {
                servicesGrid.classList.add('hidden');
                emptyState.classList.remove('hidden');
            }
        }

        function toggleService(serviceId, active) {
            fetch(`/services/${serviceId}/toggle`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    active: active
                })
            })
                .then(response => response.json())
                .then(data => {
                    if (!data.success) {
                        alert('Gagal mengupdate status service');
                        // Reset toggle state
                        const toggle = document.querySelector(`.service-toggle[data-service-id="${serviceId}"]`);
                        toggle.checked = !active;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat mengupdate status service');
                });
        }

        function editServiceItem(serviceId, itemId, itemName, itemPrice) {
            document.getElementById('editItemServiceId').value = serviceId;
            document.getElementById('editItemId').value = itemId;
            document.getElementById('editItemName').value = itemName;
            document.getElementById('editItemPrice').value = itemPrice;
            document.getElementById('editItemModal').classList.remove('hidden');
        }

        function closeEditItemModal() {
            document.getElementById('editItemModal').classList.add('hidden');
        }

        function updateServiceItem() {
            const serviceId = document.getElementById('editItemServiceId').value;
            const itemId = document.getElementById('editItemId').value;
            const name = document.getElementById('editItemName').value;
            const price = document.getElementById('editItemPrice').value;

            fetch(`/services/${serviceId}/items/${itemId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    name: name,
                    price: price
                })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Harga berhasil diupdate');
                        closeEditItemModal();
                        location.reload(); // Refresh untuk melihat perubahan
                    } else {
                        alert(data.message || 'Gagal mengupdate harga');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat mengupdate harga');
                });
        }

        function showPriceHistory(serviceId) {
            // Implementasi riwayat harga
            alert(`Riwayat harga untuk service ${serviceId} akan diimplementasikan`);
        }
    </script>
@endpush