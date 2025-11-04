<!-- transaction-customer-modal.blade.php -->
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
                <input type="text" id="customerSearch" placeholder="Cari nama atau telepon..."
                    class="w-full pl-10 pr-4 py-3 bg-gray-100 border-0 rounded-xl focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all duration-200"
                    onkeyup="filterCustomers(this.value)">
            </div>
        </div>

        <!-- Customer List -->
        <div class="flex-1 overflow-y-auto">
            <div id="customersList" class="p-4 space-y-3">
                <!-- Customers will be loaded here -->
            </div>

            <!-- Add New Customer -->
            <div class="p-4 border-t border-gray-200">
                <button onclick="showAddCustomerModal()"
                    class="w-full py-3 border-2 border-dashed border-gray-300 text-gray-500 rounded-xl hover:border-blue-500 hover:text-blue-500 transition-colors">
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