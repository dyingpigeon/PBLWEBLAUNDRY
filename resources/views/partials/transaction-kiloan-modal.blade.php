<!-- transaction-kiloan-modal.blade.php -->
<!-- Kiloan Service Modal -->
<div id="kiloanModal" class="fixed inset-0 bg-black bg-opacity-50 z-30 hidden">
    <div class="absolute bottom-0 left-0 right-0 bg-white rounded-t-2xl max-h-screen overflow-hidden flex flex-col">
        <!-- Header -->
        <div class="flex items-center justify-between p-4 border-b border-gray-200">
            <div>
                <h3 class="text-lg font-semibold text-gray-800">Laundry Kiloan</h3>
                <div class="flex items-center space-x-2 mt-1">
                    <div class="step-indicator step-completed w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold">1</div>
                    <div class="step-indicator step-completed w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold">2</div>
                    <div class="step-indicator step-active w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold">3</div>
                    <p class="text-sm text-gray-500">Pilih Layanan</p>
                </div>
            </div>
            <button onclick="showServiceModal()" class="p-2 text-gray-400 hover:text-gray-600">
                <i class="fas fa-arrow-left text-lg"></i>
            </button>
        </div>

        <!-- Service Grid -->
        <div class="flex-1 overflow-y-auto p-4">
            <div class="grid grid-cols-1 gap-3" id="kiloanServicesGrid">
                <!-- Kiloan services will be loaded here -->
            </div>
        </div>

        <!-- Weight Input -->
        <div id="weightInputSection" class="hidden p-4 border-t border-gray-200">
            <label class="block text-sm font-medium text-gray-700 mb-3">Berat Laundry (kg)</label>
            <div class="flex items-center space-x-3">
                <button onclick="decreaseWeight()" class="w-12 h-12 bg-gray-100 rounded-xl flex items-center justify-center text-gray-600 hover:bg-gray-200">
                    <i class="fas fa-minus"></i>
                </button>
                <input type="number" id="weightInput" min="0.5" step="0.5" value="1.0"
                       class="flex-1 text-center text-2xl font-bold border-0 bg-gray-50 rounded-xl py-3 focus:ring-2 focus:ring-blue-500">
                <button onclick="increaseWeight()" class="w-12 h-12 bg-gray-100 rounded-xl flex items-center justify-center text-gray-600 hover:bg-gray-200">
                    <i class="fas fa-plus"></i>
                </button>
            </div>
            <div class="flex justify-between text-sm text-gray-500 mt-2">
                <span>Min: 0.5 kg</span>
                <span>Step: 0.5 kg</span>
            </div>
        </div>

        <!-- Selected Service & Total -->
        <div id="kiloanPreview" class="hidden p-4 border-t border-gray-200 bg-blue-50">
            <div class="flex items-center justify-between mb-3">
                <div id="selectedKiloanService"></div>
                <div class="text-right">
                    <div class="text-sm text-gray-600">Total:</div>
                    <div id="kiloanTotal" class="text-xl font-bold text-blue-600">Rp 0</div>
                </div>
            </div>
            <button onclick="showPaymentModal()" class="w-full bg-blue-500 text-white py-3 rounded-xl font-semibold hover:bg-blue-600">
                Lanjut ke Pembayaran <i class="fas fa-arrow-right ml-2"></i>
            </button>
        </div>
    </div>
</div>

<script>
// Load kiloan services
function loadKiloanServices() {
    const container = document.getElementById('kiloanServicesGrid');
    
    container.innerHTML = `
        <div class="text-center py-8">
            <i class="fas fa-spinner fa-spin text-blue-500 text-2xl"></i>
            <p class="text-gray-500 mt-2">Memuat layanan kiloan...</p>
        </div>
    `;

    fetch('{{ route("transactions.getServices") }}?type=kiloan')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const services = data.data;
            
            if (services.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-8">
                        <i class="fas fa-concierge-bell text-gray-400 text-2xl"></i>
                        <p class="text-gray-500 mt-2">Tidak ada layanan kiloan tersedia</p>
                    </div>
                `;
                return;
            }

            container.innerHTML = services.map(service => {
                const price = service.items && service.items[0] ? service.items[0].price : 0;
                
                // PERBAIKAN: Gunakan escape yang benar untuk JSON
                const serviceJson = JSON.stringify(service).replace(/"/g, '&quot;');
                
                return `
                <div class="service-card bg-white rounded-xl p-4 border-2 border-gray-200 hover:border-blue-500 cursor-pointer"
                     onclick="selectKiloanServiceSafe('${serviceJson}')">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center">
                                <i class="fas fa-weight text-white"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-800">${service.name}</h4>
                                <p class="text-sm text-gray-500">Rp ${formatPrice(price)} / kg</p>
                            </div>
                        </div>
                        <i class="fas fa-chevron-right text-gray-400"></i>
                    </div>
                </div>
                `;
            }).join('');
        }
    })
    .catch(error => {
        console.error('Error loading kiloan services:', error);
        container.innerHTML = `
            <div class="text-center py-8">
                <i class="fas fa-exclamation-triangle text-red-500 text-2xl"></i>
                <p class="text-red-500 mt-2">Gagal memuat layanan</p>
            </div>
        `;
    });
}

// Fungsi aman untuk memilih service kiloan
function selectKiloanServiceSafe(serviceJson) {
    try {
        const service = JSON.parse(serviceJson.replace(/&quot;/g, '"'));
        selectKiloanService(service);
    } catch (error) {
        console.error('Error parsing service JSON:', error);
        alert('Terjadi kesalahan saat memilih layanan');
    }
}

// Select kiloan service
function selectKiloanService(service) {
    transactionData.service = service;
    transactionData.service_item = service.items[0]; // Kiloan hanya punya 1 item
    
    document.getElementById('selectedKiloanService').innerHTML = `
        <div>
            <p class="font-semibold text-gray-800">${service.name}</p>
            <p class="text-sm text-gray-600">Rp ${formatPrice(service.items[0].price)} / kg</p>
        </div>
    `;
    
    document.getElementById('weightInputSection').classList.remove('hidden');
    document.getElementById('kiloanPreview').classList.remove('hidden');
    calculateKiloanTotal();
}

// Weight functions
function decreaseWeight() {
    const input = document.getElementById('weightInput');
    let value = parseFloat(input.value) - 0.5;
    if (value < 0.5) value = 0.5;
    input.value = value.toFixed(1);
    calculateKiloanTotal();
}

function increaseWeight() {
    const input = document.getElementById('weightInput');
    let value = parseFloat(input.value) + 0.5;
    input.value = value.toFixed(1);
    calculateKiloanTotal();
}

function calculateKiloanTotal() {
    const weight = parseFloat(document.getElementById('weightInput').value);
    const price = transactionData.service_item.price;
    const total = weight * price;
    
    transactionData.weight = weight;
    transactionData.total = total;
    transactionData.items = [{
        service_item_id: transactionData.service_item.id,
        item_name: transactionData.service_item.name,
        quantity: weight,
        unit_price: price,
        subtotal: total,
        unit: 'kg'
    }];
    
    document.getElementById('kiloanTotal').textContent = `Rp ${formatPrice(total)}`;
}

// Format price helper
function formatPrice(price) {
    return new Intl.NumberFormat('id-ID').format(price);
}
</script>