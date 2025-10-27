<!-- Customer Selection Modal -->
<div id="customerModal" class="fixed inset-0 bg-black bg-opacity-50 z-30 hidden">
    <div class="absolute bottom-0 left-0 right-0 bg-white rounded-t-2xl max-h-screen overflow-hidden flex flex-col">
        <!-- Header -->
        <div class="flex items-center justify-between p-4 border-b border-gray-200">
            <div>
                <h3 class="text-lg font-semibold text-gray-800">Pilih Pelanggan</h3>
                <div class="flex items-center space-x-2 mt-1">
                    <div class="step-indicator step-active w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold">1</div>
                    <p class="text-sm text-gray-500">Pilih Pelanggan</p>
                </div>
            </div>
            <button onclick="closeAllModals()" class="p-2 text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>

        <!-- Search -->
        <div class="p-4 border-b border-gray-200">
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-search text-gray-400"></i>
                </div>
                <input 
                    type="text" 
                    id="customerSearch"
                    placeholder="Cari nama atau telepon..."
                    class="w-full pl-10 pr-4 py-3 bg-gray-100 border-0 rounded-xl focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all duration-200"
                    onkeyup="filterCustomers(this.value)"
                >
            </div>
        </div>

        <!-- Customer List -->
        <div class="flex-1 overflow-y-auto">
            <div id="customersList" class="p-4 space-y-3">
                <!-- Customers will be loaded here -->
            </div>

            <!-- Add New Customer -->
            <div class="p-4 border-t border-gray-200">
                <button onclick="addNewCustomer()" class="w-full py-3 border-2 border-dashed border-gray-300 text-gray-500 rounded-xl hover:border-blue-500 hover:text-blue-500 transition-colors">
                    <i class="fas fa-plus mr-2"></i>Tambah Pelanggan Baru
                </button>
            </div>
        </div>

        <!-- Selected Customer Preview -->
        <div id="selectedCustomerPreview" class="hidden p-4 border-t border-gray-200 bg-blue-50">
            <div class="flex items-center justify-between">
                <div id="selectedCustomer"></div>
                <button onclick="showServiceModal()" class="bg-blue-500 text-white px-6 py-2 rounded-lg font-semibold">
                    Lanjut <i class="fas fa-arrow-right ml-1"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Filter customers
function filterCustomers(query) {
    const customers = getMockCustomers();
    const container = document.getElementById('customersList');
    
    const filtered = query ? 
        customers.filter(customer => 
            customer.name.toLowerCase().includes(query.toLowerCase()) ||
            customer.phone.includes(query)
        ) : customers;

    container.innerHTML = filtered.map(customer => `
        <div class="customer-item flex items-center space-x-3 p-3 bg-white border border-gray-200 rounded-xl hover:border-blue-500 cursor-pointer"
             onclick="selectCustomer(${JSON.stringify(customer).replace(/'/g, "\\'")})">
            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                <i class="fas fa-user text-blue-600"></i>
            </div>
            <div class="flex-1">
                <h4 class="font-semibold text-gray-800">${customer.name}</h4>
                <p class="text-sm text-gray-500">${customer.phone}</p>
                <p class="text-xs text-gray-400 truncate">${customer.address}</p>
            </div>
            <i class="fas fa-chevron-right text-gray-400"></i>
        </div>
    `).join('');
}

// Add new customer
function addNewCustomer() {
    // In real app, show add customer modal
    const newCustomer = {
        id: Date.now(),
        name: 'Pelanggan Baru',
        phone: '081234567894',
        address: 'Alamat baru'
    };
    selectCustomer(newCustomer);
}

// Initialize customers on modal show
document.addEventListener('DOMContentLoaded', function() {
    // Initialize customers list when modal is shown
    const customerModal = document.getElementById('customerModal');
    customerModal.addEventListener('click', function(e) {
        if (e.target === this) {
            filterCustomers('');
        }
    });
});

// Show selected customer preview
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
    document.getElementById('selectedCustomerPreview').classList.remove('hidden');
}

// Initial load
filterCustomers('');
</script>