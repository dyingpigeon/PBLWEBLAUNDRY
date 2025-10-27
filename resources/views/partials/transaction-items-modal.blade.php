<!-- Items Input Modal -->
<div id="itemsModal" class="fixed inset-0 bg-black bg-opacity-50 z-30 hidden">
    <div class="absolute bottom-0 left-0 right-0 bg-white rounded-t-2xl max-h-screen overflow-hidden flex flex-col">
        <!-- Header -->
        <div class="flex items-center justify-between p-4 border-b border-gray-200">
            <div>
                <h3 class="text-lg font-semibold text-gray-800">Input Items</h3>
                <div class="flex items-center space-x-2 mt-1">
                    <div class="step-indicator step-completed w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold">1</div>
                    <div class="step-indicator step-completed w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold">2</div>
                    <div class="step-indicator step-active w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold">3</div>
                    <p class="text-sm text-gray-500">Input Items</p>
                </div>
            </div>
            <button onclick="closeAllModals()" class="p-2 text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>

        <!-- Items Container -->
        <div class="flex-1 overflow-y-auto">
            <div class="p-4 space-y-3" id="itemsContainer">
                <!-- Items will be loaded dynamically -->
            </div>

            <!-- Additional Notes -->
            <div class="p-4 border-t border-gray-200">
                <label class="block text-sm font-medium text-gray-700 mb-2">Catatan Tambahan</label>
                <textarea 
                    id="transactionNotes"
                    rows="2"
                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    placeholder="Catatan khusus untuk pesanan ini..."
                    oninput="transactionData.notes = this.value"
                ></textarea>
            </div>
        </div>

        <!-- Total & Action -->
        <div class="p-4 border-t border-gray-200 bg-white">
            <div class="flex items-center justify-between mb-3">
                <span class="text-lg font-semibold text-gray-800">Total:</span>
                <span id="itemsTotal" class="text-2xl font-bold text-blue-600">Rp 0</span>
            </div>
            <button onclick="showReviewModal()" 
                    class="w-full bg-blue-500 text-white py-3 rounded-xl font-semibold hover:bg-blue-600 transition-colors">
                Review Pesanan <i class="fas fa-arrow-right ml-2"></i>
            </button>
        </div>
    </div>
</div>