<!-- Success Modal -->
<div id="successModal" class="fixed inset-0 bg-black bg-opacity-50 z-30 hidden">
    <div class="absolute bottom-0 left-0 right-0 bg-white rounded-t-2xl p-6 text-center">
        <!-- Success Icon -->
        <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-check text-green-500 text-2xl"></i>
        </div>

        <!-- Success Message -->
        <h3 class="text-xl font-semibold text-gray-800 mb-2">Transaksi Berhasil!</h3>
        <p class="text-gray-600 mb-2">Pesanan telah berhasil dicatat</p>
        <p class="text-2xl font-bold text-blue-600 mb-6">Rp <span id="successTotal">0</span></p>

        <!-- Transaction Details -->
        <div class="bg-gray-50 rounded-xl p-4 mb-6 text-left">
            <div class="flex justify-between items-center mb-2">
                <span class="text-gray-600">No. Transaksi:</span>
                <span class="font-semibold">#TRX-${Date.now().toString().slice(-6)}</span>
            </div>
            <div class="flex justify-between items-center mb-2">
                <span class="text-gray-600">Pelanggan:</span>
                <span class="font-semibold" id="successCustomer"></span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-gray-600">Layanan:</span>
                <span class="font-semibold" id="successService"></span>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="grid grid-cols-2 gap-3">
            <button onclick="printReceipt()" 
                    class="bg-white border border-blue-500 text-blue-500 py-3 rounded-xl font-semibold hover:bg-blue-50 transition-colors">
                <i class="fas fa-print mr-2"></i>Cetak Struk
            </button>
            <button onclick="createNewTransaction()" 
                    class="bg-blue-500 text-white py-3 rounded-xl font-semibold hover:bg-blue-600 transition-colors">
                <i class="fas fa-plus mr-2"></i>Transaksi Baru
            </button>
        </div>
    </div>
</div>

<script>
// Update success modal
function showSuccessModal() {
    document.getElementById('successTotal').textContent = formatPrice(transactionData.total);
    document.getElementById('successCustomer').textContent = transactionData.customer.name;
    document.getElementById('successService').textContent = transactionData.service.name;
    document.getElementById('successModal').classList.remove('hidden');
}

// Print receipt
function printReceipt() {
    alert('Fitur cetak struk akan diimplementasi!');
    closeAllModals();
    loadTransactions(); // Refresh transactions list
}

// Create new transaction
function createNewTransaction() {
    closeAllModals();
    startNewTransaction();
}
</script>