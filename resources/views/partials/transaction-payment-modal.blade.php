<!-- resources/views/partials/transaction-payment-modal.blade.php -->
<!-- Payment Modal -->
<div id="paymentModal" class="fixed inset-0 bg-black bg-opacity-50 z-30 hidden">
    <div class="absolute bottom-0 left-0 right-0 bg-white rounded-t-2xl max-h-screen overflow-hidden flex flex-col">
        <!-- Header -->
        <div class="flex items-center justify-between p-4 border-b border-gray-200">
            <div>
                <h3 class="text-lg font-semibold text-gray-800">Pembayaran</h3>
                <div class="flex items-center space-x-2 mt-1">
                    <div class="step-indicator step-completed w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold">1</div>
                    <div class="step-indicator step-completed w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold">2</div>
                    <div class="step-indicator step-completed w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold">3</div>
                    <div class="step-indicator step-completed w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold">4</div>
                    <div class="step-indicator step-active w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold">5</div>
                    <p class="text-sm text-gray-500">Pembayaran</p>
                </div>
            </div>
            <button onclick="backToPreviousStep()" class="p-2 text-gray-400 hover:text-gray-600">
                <i class="fas fa-arrow-left text-lg"></i>
            </button>
        </div>

        <!-- Payment Content -->
        <div class="flex-1 overflow-y-auto p-4 space-y-6">
            <!-- Order Summary -->
            <div class="bg-gray-50 rounded-xl p-4">
                <h4 class="font-semibold text-gray-700 mb-3">Ringkasan Pesanan</h4>
                <div class="space-y-2">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Pelanggan:</span>
                        <span class="font-semibold" id="paymentCustomerName">-</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Layanan:</span>
                        <span class="font-semibold" id="paymentServiceName">-</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Tipe:</span>
                        <span class="font-semibold" id="paymentOrderType">-</span>
                    </div>
                    <div class="border-t border-gray-200 pt-2 mt-2">
                        <div class="flex justify-between items-center">
                            <span class="text-lg font-bold text-gray-800">Total:</span>
                            <span id="paymentTotal" class="text-xl font-bold text-blue-600">Rp 0</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Type Selection -->
            <div>
                <h4 class="font-semibold text-gray-700 mb-3">Tipe Pembayaran</h4>
                <div class="grid grid-cols-2 gap-3">
                    <!-- Bayar Sekarang -->
                    <div class="payment-type-card bg-white rounded-xl p-4 border-2 border-gray-200 hover:border-green-500 cursor-pointer text-center"
                         onclick="selectPaymentType('now')">
                        <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center mx-auto mb-2">
                            <i class="fas fa-money-bill-wave text-white"></i>
                        </div>
                        <h5 class="font-semibold text-gray-800">Bayar Sekarang</h5>
                        <p class="text-xs text-gray-500 mt-1">Lunas saat order</p>
                    </div>

                    <!-- Bayar Nanti -->
                    <div class="payment-type-card bg-white rounded-xl p-4 border-2 border-gray-200 hover:border-yellow-500 cursor-pointer text-center"
                         onclick="selectPaymentType('later')">
                        <div class="w-12 h-12 bg-yellow-500 rounded-full flex items-center justify-center mx-auto mb-2">
                            <i class="fas fa-clock text-white"></i>
                        </div>
                        <h5 class="font-semibold text-gray-800">Bayar Nanti</h5>
                        <p class="text-xs text-gray-500 mt-1">Bayar saat ambil</p>
                    </div>
                </div>
            </div>

            <!-- Payment Method Selection (Hanya tampil jika bayar sekarang) -->
            <div id="paymentMethodSection" class="hidden">
                <h4 class="font-semibold text-gray-700 mb-3">Metode Pembayaran</h4>
                <div class="grid grid-cols-3 gap-3">
                    <!-- Cash -->
                    <div class="payment-method-card bg-white rounded-xl p-3 border-2 border-gray-200 hover:border-blue-500 cursor-pointer text-center"
                         onclick="selectPaymentMethod('cash')">
                        <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center mx-auto mb-2">
                            <i class="fas fa-money-bill text-white text-sm"></i>
                        </div>
                        <h6 class="font-semibold text-gray-800 text-sm">Cash</h6>
                    </div>

                    <!-- Transfer -->
                    <div class="payment-method-card bg-white rounded-xl p-3 border-2 border-gray-200 hover:border-green-500 cursor-pointer text-center"
                         onclick="selectPaymentMethod('transfer')">
                        <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center mx-auto mb-2">
                            <i class="fas fa-university text-white text-sm"></i>
                        </div>
                        <h6 class="font-semibold text-gray-800 text-sm">Transfer</h6>
                    </div>

                    <!-- QRIS -->
                    <div class="payment-method-card bg-white rounded-xl p-3 border-2 border-gray-200 hover:border-purple-500 cursor-pointer text-center"
                         onclick="selectPaymentMethod('qris')">
                        <div class="w-10 h-10 bg-purple-500 rounded-full flex items-center justify-center mx-auto mb-2">
                            <i class="fas fa-qrcode text-white text-sm"></i>
                        </div>
                        <h6 class="font-semibold text-gray-800 text-sm">QRIS</h6>
                    </div>
                </div>
            </div>

            <!-- Additional Notes -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Catatan Pembayaran (Opsional)</label>
                <textarea 
                    id="paymentNotes"
                    rows="2"
                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    placeholder="Catatan khusus untuk pembayaran..."
                ></textarea>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="p-4 border-t border-gray-200 bg-white">
            <button onclick="showReviewModal()" 
                    class="w-full bg-blue-500 text-white py-3 rounded-xl font-semibold hover:bg-blue-600 transition-colors">
                Review Pesanan <i class="fas fa-arrow-right ml-2"></i>
            </button>
        </div>
    </div>
</div>

<script>
// Update payment summary
function updatePaymentSummary() {
    // Update customer name
    if (transactionData.customer) {
        document.getElementById('paymentCustomerName').textContent = transactionData.customer.name;
    }
    
    // Update service name
    if (transactionData.service) {
        document.getElementById('paymentServiceName').textContent = transactionData.service.name;
    }
    
    // Update order type
    if (transactionData.order_type) {
        const typeText = transactionData.order_type === 'kiloan' ? 'Kiloan' : 'Satuan';
        document.getElementById('paymentOrderType').textContent = typeText;
    }
    
    // Update total
    const paymentTotal = document.getElementById("paymentTotal");
    if (paymentTotal) {
        paymentTotal.textContent = `Rp ${formatPrice(transactionData.total)}`;
    }
    
    // Reset payment selections
    resetPaymentSelections();
}

// Reset payment selections
function resetPaymentSelections() {
    transactionData.payment_type = 'later'; // Default to bayar nanti
    transactionData.payment_method = null;
    
    // Reset UI
    document.querySelectorAll('.payment-type-card').forEach(card => {
        card.classList.remove('border-green-500', 'border-yellow-500', 'bg-green-50', 'bg-yellow-50');
    });
    
    document.querySelectorAll('.payment-method-card').forEach(card => {
        card.classList.remove('border-blue-500', 'border-green-500', 'border-purple-500', 'bg-blue-50', 'bg-green-50', 'bg-purple-50');
    });
    
    // Hide payment method section
    document.getElementById('paymentMethodSection').classList.add('hidden');
    
    // Select "Bayar Nanti" by default
    const laterCard = document.querySelector('[onclick="selectPaymentType(\'later\')"]');
    if (laterCard) {
        laterCard.classList.add('border-yellow-500', 'bg-yellow-50');
    }
}

// Select payment type
function selectPaymentType(type) {
    transactionData.payment_type = type;
    
    // Update UI for payment type
    document.querySelectorAll('.payment-type-card').forEach(card => {
        card.classList.remove('border-green-500', 'border-yellow-500', 'bg-green-50', 'bg-yellow-50');
    });
    
    const selectedCard = document.querySelector(`[onclick="selectPaymentType('${type}')"]`);
    if (selectedCard) {
        if (type === 'now') {
            selectedCard.classList.add('border-green-500', 'bg-green-50');
        } else {
            selectedCard.classList.add('border-yellow-500', 'bg-yellow-50');
        }
    }
    
    // Show/hide payment method section
    const paymentMethodSection = document.getElementById("paymentMethodSection");
    if (paymentMethodSection) {
        if (type === 'now') {
            paymentMethodSection.classList.remove('hidden');
            // Auto select cash as default payment method
            selectPaymentMethod('cash');
        } else {
            paymentMethodSection.classList.add('hidden');
            transactionData.payment_method = null;
        }
    }
}

// Select payment method
function selectPaymentMethod(method) {
    transactionData.payment_method = method;
    
    // Update UI untuk selected payment method
    document.querySelectorAll('.payment-method-card').forEach(card => {
        card.classList.remove('border-blue-500', 'border-green-500', 'border-purple-500', 'bg-blue-50', 'bg-green-50', 'bg-purple-50');
    });
    
    const selectedCard = document.querySelector(`[onclick="selectPaymentMethod('${method}')"]`);
    if (selectedCard) {
        switch(method) {
            case 'cash':
                selectedCard.classList.add('border-blue-500', 'bg-blue-50');
                break;
            case 'transfer':
                selectedCard.classList.add('border-green-500', 'bg-green-50');
                break;
            case 'qris':
                selectedCard.classList.add('border-purple-500', 'bg-purple-50');
                break;
        }
    }
}

// Back navigation
function backToPreviousStep() {
    if (transactionData.order_type === 'kiloan') {
        backToKiloanModal();
    } else {
        backToSatuanModal();
    }
}

// Format price helper
function formatPrice(price) {
    return new Intl.NumberFormat('id-ID').format(price);
}
</script>

<style>
.payment-type-card:hover,
.payment-method-card:hover {
    transform: translateY(-2px);
    transition: all 0.2s ease-in-out;
}
</style>