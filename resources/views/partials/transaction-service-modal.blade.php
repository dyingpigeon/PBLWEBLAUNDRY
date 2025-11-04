<!-- transaction-service-modal.blade.php -->
<!-- Service Selection Modal -->
<div id="serviceModal" class="fixed inset-0 bg-black bg-opacity-50 z-30 hidden">
    <div class="absolute bottom-0 left-0 right-0 bg-white rounded-t-2xl max-h-screen overflow-hidden flex flex-col">
        <!-- Header -->
        <div class="flex items-center justify-between p-4 border-b border-gray-200">
            <div>
                <h3 class="text-lg font-semibold text-gray-800">Pilih Tipe Laundry</h3>
                <div class="flex items-center space-x-2 mt-1">
                    <div class="step-indicator step-completed w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold">1</div>
                    <div class="step-indicator step-active w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold">2</div>
                    <p class="text-sm text-gray-500">Pilih Tipe</p>
                </div>
            </div>
            <button onclick="closeAllModals()" class="p-2 text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>

        <!-- Type Selection -->
        <div class="flex-1 overflow-y-auto p-4">
            <div class="grid grid-cols-1 gap-4">
                <!-- Kiloan Option -->
                <div class="type-card bg-white rounded-xl p-6 border-2 border-gray-200 hover:border-blue-500 cursor-pointer"
                     onclick="selectOrderType('kiloan')">
                    <div class="flex items-center space-x-4">
                        <div class="w-16 h-16 bg-blue-500 rounded-full flex items-center justify-center">
                            <i class="fas fa-weight text-white text-2xl"></i>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-semibold text-gray-800 text-lg mb-1">Laundry Kiloan</h4>
                            <p class="text-gray-600 mb-2">Cuci berdasarkan berat (per kg)</p>
                            <div class="text-sm text-gray-500">
                                <i class="fas fa-check text-green-500 mr-1"></i> Cocok untuk pakaian sehari-hari
                            </div>
                        </div>
                        <i class="fas fa-chevron-right text-gray-400 text-xl"></i>
                    </div>
                </div>

                <!-- Satuan Option -->
                <div class="type-card bg-white rounded-xl p-6 border-2 border-gray-200 hover:border-green-500 cursor-pointer"
                     onclick="selectOrderType('satuan')">
                    <div class="flex items-center space-x-4">
                        <div class="w-16 h-16 bg-green-500 rounded-full flex items-center justify-center">
                            <i class="fas fa-tshirt text-white text-2xl"></i>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-semibold text-gray-800 text-lg mb-1">Laundry Satuan</h4>
                            <p class="text-gray-600 mb-2">Cuci per item (per pieces)</p>
                            <div class="text-sm text-gray-500">
                                <i class="fas fa-check text-green-500 mr-1"></i> Cocok untuk item khusus
                            </div>
                        </div>
                        <i class="fas fa-chevron-right text-gray-400 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Select order type
function selectOrderType(type) {
    transactionData.order_type = type;
    
    if (type === 'kiloan') {
        showKiloanModal();
    } else {
        showSatuanModal();
    }
}

// Show kiloan modal
function showKiloanModal() {
    closeAllModals();
    document.getElementById('kiloanModal').classList.remove('hidden');
    loadKiloanServices();
}

// Show satuan modal  
function showSatuanModal() {
    closeAllModals();
    document.getElementById('satuanModal').classList.remove('hidden');
    loadCategories();
}
</script>