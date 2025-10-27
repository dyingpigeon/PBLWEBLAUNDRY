<!-- Review Modal -->
<div id="reviewModal" class="fixed inset-0 bg-black bg-opacity-50 z-30 hidden">
    <div class="absolute bottom-0 left-0 right-0 bg-white rounded-t-2xl max-h-screen overflow-hidden flex flex-col">
        <!-- Header -->
        <div class="flex items-center justify-between p-4 border-b border-gray-200">
            <div>
                <h3 class="text-lg font-semibold text-gray-800">Review Pesanan</h3>
                <div class="flex items-center space-x-2 mt-1">
                    <div class="step-indicator step-completed w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold">1</div>
                    <div class="step-indicator step-completed w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold">2</div>
                    <div class="step-indicator step-completed w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold">3</div>
                    <div class="step-indicator step-active w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold">4</div>
                    <p class="text-sm text-gray-500">Konfirmasi</p>
                </div>
            </div>
            <button onclick="closeAllModals()" class="p-2 text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>

        <!-- Review Content -->
        <div class="flex-1 overflow-y-auto p-4 space-y-4">
            <!-- Customer -->
            <div>
                <h4 class="font-semibold text-gray-700 mb-2">Pelanggan</h4>
                <div id="reviewCustomer"></div>
            </div>

            <!-- Service -->
            <div>
                <h4 class="font-semibold text-gray-700 mb-2">Layanan</h4>
                <div id="reviewService"></div>
            </div>

            <!-- Items -->
            <div>
                <h4 class="font-semibold text-gray-700 mb-2">Items</h4>
                <div id="reviewItems" class="bg-gray-50 rounded-xl p-3">
                    <!-- Items will be loaded here -->
                </div>
            </div>

            <!-- Notes -->
            <div id="reviewNotesSection" class="hidden">
                <h4 class="font-semibold text-gray-700 mb-2">Catatan</h4>
                <p class="text-gray-600 bg-gray-50 rounded-xl p-3" id="reviewNotes"></p>
            </div>
        </div>

        <!-- Total & Action -->
        <div class="p-4 border-t border-gray-200 bg-white">
            <div class="flex items-center justify-between mb-4">
                <span class="text-xl font-bold text-gray-800">Total Bayar:</span>
                <span id="reviewTotal" class="text-2xl font-bold text-blue-600">Rp 0</span>
            </div>
            <button onclick="submitTransaction()" 
                    class="w-full bg-green-500 text-white py-4 rounded-xl font-semibold hover:bg-green-600 transition-colors text-lg">
                <i class="fas fa-check-circle mr-2"></i>Konfirmasi & Simpan
            </button>
        </div>
    </div>
</div>

<script>
// Update notes in review
function updateReviewSummary() {
    // ... existing code ...
    
    // Update notes
    if (transactionData.notes) {
        document.getElementById('reviewNotesSection').classList.remove('hidden');
        document.getElementById('reviewNotes').textContent = transactionData.notes;
    } else {
        document.getElementById('reviewNotesSection').classList.add('hidden');
    }
}
</script>