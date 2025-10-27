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
// Load services
function loadServices() {
    const services = getMockServices();
    const container = document.getElementById('servicesGrid');
    
    container.innerHTML = services.map(service => `
        <div class="service-card bg-white rounded-xl p-4 border-2 border-gray-200 hover:border-blue-500 cursor-pointer text-center"
             onclick="selectService(${JSON.stringify(service).replace(/'/g, "\\'")})">
            <div class="w-16 h-16 ${service.color} rounded-full flex items-center justify-center mx-auto mb-3">
                <i class="${service.icon} text-white text-xl"></i>
            </div>
            <h4 class="font-semibold text-gray-800 mb-1">${service.name}</h4>
            <p class="text-sm text-gray-500 mb-2">${service.category}</p>
            <div class="text-xs text-gray-400">
                ${service.items.length} item
            </div>
        </div>
    `).join('');
}

// Select service
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
    document.getElementById('selectedServicePreview').classList.remove('hidden');
}

// Initial load
loadServices();
</script>