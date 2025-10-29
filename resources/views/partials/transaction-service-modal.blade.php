<!-- Service Selection Modal -->
<div id="serviceModal" class="fixed inset-0 bg-black bg-opacity-50 z-30 hidden">
    <div class="absolute bottom-0 left-0 right-0 bg-white rounded-t-2xl max-h-screen overflow-hidden flex flex-col">
        <!-- Header -->
        <div class="flex items-center justify-between p-4 border-b border-gray-200">
            <div>
                <h3 class="text-lg font-semibold text-gray-800">Pilih Layanan</h3>
                <div class="flex items-center space-x-2 mt-1">
                    <div class="step-indicator step-completed w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold">1</div>
                    <div class="step-indicator step-active w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold">2</div>
                    <p class="text-sm text-gray-500">Pilih Layanan</p>
                </div>
            </div>
            <button onclick="closeAllModals()" class="p-2 text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>

        <!-- Service Grid -->
        <div class="flex-1 overflow-y-auto p-4">
            <div class="grid grid-cols-2 gap-3" id="servicesGrid">
                <!-- Services will be loaded here -->
            </div>
        </div>

        <!-- Selected Service Preview -->
        <div id="selectedServicePreview" class="hidden p-4 border-t border-gray-200 bg-blue-50">
            <div class="flex items-center justify-between">
                <div id="selectedService"></div>
                <button onclick="showItemsModal()" class="bg-blue-500 text-white px-6 py-2 rounded-lg font-semibold">
                    Lanjut <i class="fas fa-arrow-right ml-1"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Load services from database
function loadServices() {
    const container = document.getElementById('servicesGrid');
    
    // Show loading
    container.innerHTML = `
        <div class="col-span-2 text-center py-8">
            <i class="fas fa-spinner fa-spin text-blue-500 text-2xl"></i>
            <p class="text-gray-500 mt-2">Memuat layanan...</p>
        </div>
    `;

    // Fetch data from server
    fetch('{{ route("transactions.getServices") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const services = data.data;
            
            if (services.length === 0) {
                container.innerHTML = `
                    <div class="col-span-2 text-center py-8">
                        <i class="fas fa-concierge-bell text-gray-400 text-2xl"></i>
                        <p class="text-gray-500 mt-2">Tidak ada layanan tersedia</p>
                    </div>
                `;
                return;
            }

            container.innerHTML = services.map(service => {
                const color = getServiceColor(service.name);
                const icon = getServiceIcon(service.name);
                
                return `
                <div class="service-card bg-white rounded-xl p-4 border-2 border-gray-200 hover:border-blue-500 cursor-pointer text-center"
                     onclick="selectService(${JSON.stringify(service).replace(/'/g, "\\'")})">
                    <div class="w-16 h-16 ${color} rounded-full flex items-center justify-center mx-auto mb-3">
                        <i class="${icon} text-white text-xl"></i>
                    </div>
                    <h4 class="font-semibold text-gray-800 mb-1">${service.name}</h4>
                    <p class="text-sm text-gray-500 mb-2">${service.description || 'Layanan laundry'}</p>
                    <div class="text-xs text-gray-400">
                        ${service.items ? service.items.length : 0} item
                    </div>
                </div>
                `;
            }).join('');
        } else {
            throw new Error(data.message);
        }
    })
    .catch(error => {
        console.error('Error loading services:', error);
        container.innerHTML = `
            <div class="col-span-2 text-center py-8">
                <i class="fas fa-exclamation-triangle text-red-500 text-2xl"></i>
                <p class="text-red-500 mt-2">Gagal memuat data layanan</p>
            </div>
        `;
    });
}

// Helper function to determine service color
function getServiceColor(serviceName) {
    const colors = {
        'Cuci Setrika': 'bg-green-500',
        'Cuci Kering': 'bg-blue-500',
        'Setrika Saja': 'bg-yellow-500',
        'Express': 'bg-red-500',
        'Premium': 'bg-purple-500'
    };
    return colors[serviceName] || 'bg-blue-500';
}

// Helper function to determine service icon
function getServiceIcon(serviceName) {
    const icons = {
        'Cuci Setrika': 'fas fa-tshirt',
        'Cuci Kering': 'fas fa-wind',
        'Setrika Saja': 'fas fa-fire',
        'Express': 'fas fa-bolt',
        'Premium': 'fas fa-crown'
    };
    return icons[serviceName] || 'fas fa-tshirt';
}

// Select service
function selectService(service) {
    transactionData.service = service;
    document.getElementById('selectedService').innerHTML = `
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 ${getServiceColor(service.name)} rounded-full flex items-center justify-center">
                <i class="${getServiceIcon(service.name)} text-white"></i>
            </div>
            <div>
                <p class="font-semibold text-gray-800">${service.name}</p>
                <p class="text-sm text-gray-500">${service.description || 'Layanan laundry'}</p>
            </div>
        </div>
    `;
    document.getElementById('selectedServicePreview').classList.remove('hidden');
}
</script>