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
                <p class="text-2xl font-bold text-blue-600">{{ $totalServices ?? 8 }}</p>
                <p class="text-xs text-gray-500">Total Layanan</p>
            </div>
        </div>
    </div>

    <!-- Categories Swipe -->
    <div class="bg-white border-b border-gray-200">
        <div class="swipeable-categories flex overflow-x-auto px-4 space-x-1 py-2" style="scrollbar-width: none;">
            <button class="category-btn flex-shrink-0 px-4 py-2 bg-blue-500 text-white rounded-full text-sm font-medium active">
                Semua
            </button>
            <button class="category-btn flex-shrink-0 px-4 py-2 bg-gray-100 text-gray-600 rounded-full text-sm font-medium">
                Cuci
            </button>
            <button class="category-btn flex-shrink-0 px-4 py-2 bg-gray-100 text-gray-600 rounded-full text-sm font-medium">
                Setrika
            </button>
            <button class="category-btn flex-shrink-0 px-4 py-2 bg-gray-100 text-gray-600 rounded-full text-sm font-medium">
                Dry Clean
            </button>
            <button class="category-btn flex-shrink-0 px-4 py-2 bg-gray-100 text-gray-600 rounded-full text-sm font-medium">
                Lainnya
            </button>
        </div>
    </div>

    <!-- Services Grid -->
    <div id="servicesGrid" class="grid grid-cols-1 gap-3 p-4">
        <!-- Services will be loaded here -->
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
<button 
    id="fabButton"
    class="fixed bottom-20 right-4 w-14 h-14 bg-blue-500 text-white rounded-full shadow-lg flex items-center justify-center hover:bg-blue-600 active:scale-95 transition-all duration-200 z-20"
    onclick="showAddServiceModal()"
>
    <i class="fas fa-plus text-lg"></i>
</button>

<!-- Add Service Modal -->
@include('partials.service-add-modal')

<!-- Edit Service Modal -->
{{-- @include('partials.service-edit-modal')

<!-- Price History Modal -->
@include('partials.service-price-history-modal') --}}
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
    let services = [];

    // Load services on page load
    document.addEventListener('DOMContentLoaded', function() {
        loadServices();
        setupEventListeners();
    });

    // Setup event listeners
    function setupEventListeners() {
        // Category swipe functionality
        const categoriesContainer = document.querySelector('.swipeable-categories');
        let isScrolling;
        
        categoriesContainer.addEventListener('scroll', () => {
            window.clearTimeout(isScrolling);
            isScrolling = setTimeout(() => {
                const scrollLeft = categoriesContainer.scrollLeft;
                const categoryWidth = categoriesContainer.children[0].offsetWidth + 8;
                const index = Math.round(scrollLeft / categoryWidth);
                
                // Update active category
                document.querySelectorAll('.category-btn').forEach((btn, i) => {
                    btn.classList.toggle('active', i === index);
                    btn.classList.toggle('bg-blue-500', i === index);
                    btn.classList.toggle('text-white', i === index);
                    btn.classList.toggle('bg-gray-100', i !== index);
                    btn.classList.toggle('text-gray-600', i !== index);
                });
                
                currentCategory = document.querySelectorAll('.category-btn')[index].textContent.toLowerCase();
                filterServices();
            }, 100);
        });

        // Category button clicks
        document.querySelectorAll('.category-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.category-btn').forEach(b => {
                    b.classList.remove('active', 'bg-blue-500', 'text-white');
                    b.classList.add('bg-gray-100', 'text-gray-600');
                });
                this.classList.add('active', 'bg-blue-500', 'text-white');
                this.classList.remove('bg-gray-100', 'text-gray-600');
                
                currentCategory = this.textContent.toLowerCase();
                filterServices();
                
                // Scroll to selected category
                const container = categoriesContainer;
                const left = this.offsetLeft - (container.offsetWidth - this.offsetWidth) / 2;
                container.scrollTo({ left: left, behavior: 'smooth' });
            });
        });
    }

    // Load services
    function loadServices() {
        services = generateMockServices();
        renderServices(services);
    }

    // Filter services by category
    function filterServices() {
        let filteredServices = services;
        
        if (currentCategory !== 'semua') {
            filteredServices = services.filter(service => 
                service.category.toLowerCase().includes(currentCategory)
            );
        }
        
        renderServices(filteredServices);
    }

    // Render services grid
    function renderServices(servicesToRender) {
        const container = document.getElementById('servicesGrid');
        const emptyState = document.getElementById('emptyState');
        
        if (servicesToRender.length === 0) {
            container.classList.add('hidden');
            emptyState.classList.remove('hidden');
            return;
        }
        
        container.classList.remove('hidden');
        emptyState.classList.add('hidden');
        container.innerHTML = '';
        
        servicesToRender.forEach(service => {
            const serviceElement = createServiceElement(service);
            container.appendChild(serviceElement);
        });
    }

    // Create service card element
    function createServiceElement(service) {
        const div = document.createElement('div');
        div.className = 'service-card bg-white rounded-xl p-4 shadow-sm border border-gray-100 active:scale-95';
        div.innerHTML = `
            <div class="flex items-start justify-between mb-3">
                <div class="flex items-center space-x-3 flex-1">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center ${service.color}">
                        <i class="${service.icon} text-white text-lg"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="font-semibold text-gray-800 truncate">${service.name}</h3>
                        <p class="text-sm text-gray-500">${service.category}</p>
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" ${service.active ? 'checked' : ''} 
                               class="sr-only peer" onchange="toggleService(${service.id}, this.checked)">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-500"></div>
                    </label>
                </div>
            </div>
            
            <div class="space-y-2">
                ${service.items.map(item => `
                    <div class="flex items-center justify-between py-2 border-b border-gray-100 last:border-b-0">
                        <span class="text-sm text-gray-600">${item.name}</span>
                        <div class="flex items-center space-x-2">
                            <span class="font-semibold text-gray-800">Rp ${formatPrice(item.price)}</span>
                            <button onclick="editServiceItem(${service.id}, ${item.id})" 
                                    class="w-8 h-8 bg-blue-50 text-blue-600 rounded-lg flex items-center justify-center hover:bg-blue-100">
                                <i class="fas fa-edit text-xs"></i>
                            </button>
                        </div>
                    </div>
                `).join('')}
            </div>
            
            <div class="mt-3 pt-3 border-t border-gray-100 flex justify-between items-center">
                <button onclick="showPriceHistory(${service.id})" 
                        class="text-xs text-gray-500 hover:text-gray-700 flex items-center space-x-1">
                    <i class="fas fa-history"></i>
                    <span>Riwayat Harga</span>
                </button>
                <span class="text-xs px-2 py-1 rounded-full ${service.items.length > 1 ? 'bg-purple-100 text-purple-600' : 'bg-gray-100 text-gray-600'}">
                    ${service.items.length} item
                </span>
            </div>
        `;
        
        // Add tap to edit functionality
        div.addEventListener('click', function(e) {
            // Only trigger if not clicking on buttons or toggle
            if (!e.target.closest('button') && !e.target.closest('label')) {
                editService(service.id);
            }
        });
        
        return div;
    }

    // Show add service modal
    function showAddServiceModal() {
        document.getElementById('addServiceModal').classList.remove('hidden');
        document.getElementById('serviceName').focus();
    }

    // Close add service modal
    function closeAddServiceModal() {
        document.getElementById('addServiceModal').classList.add('hidden');
        document.getElementById('addServiceForm').reset();
    }

    // Edit service
    function editService(id) {
        const service = services.find(s => s.id === id);
        if (!service) return;
        
        document.getElementById('editServiceId').value = service.id;
        document.getElementById('editServiceName').value = service.name;
        document.getElementById('editServiceCategory').value = service.category;
        document.getElementById('editServiceIcon').value = service.icon;
        document.getElementById('editServiceColor').value = service.color.replace('bg-', '');
        
        document.getElementById('editServiceModal').classList.remove('hidden');
    }

    // Close edit service modal
    function closeEditServiceModal() {
        document.getElementById('editServiceModal').classList.add('hidden');
    }

    // Edit service item price
    function editServiceItem(serviceId, itemId) {
        const service = services.find(s => s.id === serviceId);
        const item = service?.items.find(i => i.id === itemId);
        
        if (!item) return;
        
        document.getElementById('editItemServiceId').value = serviceId;
        document.getElementById('editItemId').value = itemId;
        document.getElementById('editItemName').value = item.name;
        document.getElementById('editItemPrice').value = item.price;
        
        document.getElementById('editItemModal').classList.remove('hidden');
    }

    // Close edit item modal
    function closeEditItemModal() {
        document.getElementById('editItemModal').classList.add('hidden');
    }

    // Show price history
    function showPriceHistory(serviceId) {
        const service = services.find(s => s.id === serviceId);
        if (!service) return;
        
        document.getElementById('priceHistoryServiceName').textContent = service.name;
        
        // Generate price history (in real app, fetch from API)
        const priceHistory = generatePriceHistory(service);
        const historyContainer = document.getElementById('priceHistoryList');
        historyContainer.innerHTML = priceHistory.map(history => `
            <div class="flex justify-between items-center py-3 border-b border-gray-100 last:border-b-0">
                <div>
                    <p class="font-medium text-gray-800">Rp ${formatPrice(history.price)}</p>
                    <p class="text-sm text-gray-500">${history.date}</p>
                    <p class="text-xs text-gray-400">${history.note}</p>
                </div>
                <span class="px-2 py-1 rounded-full text-xs ${history.type === 'increase' ? 'bg-red-100 text-red-600' : 'bg-green-100 text-green-600'}">
                    ${history.type === 'increase' ? '↑ Naik' : '↓ Turun'}
                </span>
            </div>
        `).join('');
        
        document.getElementById('priceHistoryModal').classList.remove('hidden');
    }

    // Close price history modal
    function closePriceHistoryModal() {
        document.getElementById('priceHistoryModal').classList.add('hidden');
    }

    // Toggle service status
    function toggleService(id, active) {
        console.log(`Service ${id} ${active ? 'activated' : 'deactivated'}`);
        // In real app, send API request to update status
    }

    // Format price
    function formatPrice(price) {
        return price.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    // Mock data generator
    function generateMockServices() {
        return [
            {
                id: 1,
                name: 'Cuci Biasa',
                category: 'Cuci',
                icon: 'fas fa-soap',
                color: 'bg-blue-500',
                active: true,
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
                active: true,
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
                active: true,
                items: [
                    { id: 1, name: 'Baju', price: 4000 },
                    { id: 2, name: 'Celana', price: 5000 }
                ]
            },
            {
                id: 4,
                name: 'Dry Clean',
                category: 'Dry Clean',
                icon: 'fas fa-wind',
                color: 'bg-purple-500',
                active: false,
                items: [
                    { id: 1, name: 'Suit', price: 25000 },
                    { id: 2, name: 'Gaun', price: 30000 }
                ]
            }
        ];
    }

    // Generate price history
    function generatePriceHistory(service) {
        return [
            { date: '15 Jan 2024', price: service.items[0].price, type: 'current', note: 'Harga saat ini' },
            { date: '10 Jan 2024', price: service.items[0].price - 1000, type: 'increase', note: 'Penyesuaian harga' },
            { date: '1 Dec 2023', price: service.items[0].price - 2000, type: 'decrease', note: 'Promo akhir tahun' }
        ];
    }
</script>
@endpush