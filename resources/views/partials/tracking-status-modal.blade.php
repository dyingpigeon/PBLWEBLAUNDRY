<!-- Status Update Modal -->
<div id="statusModal" class="fixed inset-0 bg-black bg-opacity-50 z-30 hidden">
    <div class="absolute bottom-0 left-0 right-0 bg-white rounded-t-2xl p-4 max-h-screen overflow-y-auto">
        <!-- Header -->
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-lg font-semibold text-gray-800">Update Status Pesanan</h3>
                <p class="text-sm text-gray-500" id="statusOrderCode"></p>
            </div>
            <button onclick="closeStatusModal()" class="p-2 text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>

        <!-- Customer Info -->
        <div class="bg-gray-50 rounded-lg p-3 mb-4">
            <div class="flex items-center">
                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                    <i class="fas fa-user text-blue-600"></i>
                </div>
                <div>
                    <p class="font-medium text-gray-800" id="statusCustomerName"></p>
                    <p class="text-sm text-gray-500">Pelanggan</p>
                </div>
            </div>
        </div>

        <input type="hidden" id="statusOrderId">

        <!-- Status Options -->
        <div class="mb-4">
            <h4 class="font-medium text-gray-700 mb-3">Pilih Status Baru:</h4>
            <div id="statusOptions">
                <!-- Status options will be loaded here -->
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex space-x-3">
            <button 
                onclick="closeStatusModal()"
                class="flex-1 py-3 border border-gray-300 text-gray-700 rounded-xl font-semibold hover:bg-gray-50 transition-colors"
            >
                Batal
            </button>
        </div>
    </div>
</div>