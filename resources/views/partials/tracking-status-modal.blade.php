<!-- Status Update Modal -->
<div id="statusModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-xl mx-4 w-full max-w-md max-h-[90vh] overflow-y-auto">
        <!-- Modal Header -->
        <div class="p-4 border-b border-gray-200 sticky top-0 bg-white">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-800">Update Status Pesanan</h3>
                <button onclick="closeStatusModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>

        <!-- Order Info -->
        <div class="p-4 border-b border-gray-200">
            <input type="hidden" id="statusOrderId">
            <div class="space-y-3">
                <div class="flex justify-between">
                    <span class="text-gray-600">No. Transaksi:</span>
                    <span id="statusOrderCode" class="font-semibold"></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Pelanggan:</span>
                    <span id="statusCustomerName" class="font-semibold"></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Layanan:</span>
                    <span id="statusServiceName" class="font-semibold"></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Jenis:</span>
                    <span id="statusOrderType" class="font-semibold text-blue-600"></span>
                </div>
                <div id="statusWeight" class="flex justify-between hidden">
                    <span class="text-gray-600">Berat:</span>
                    <span class="font-semibold"></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Total:</span>
                    <span id="statusTotalAmount" class="font-semibold text-blue-600"></span>
                </div>
                <div id="statusPaymentInfo" class="space-y-1 text-sm">
                    <!-- Payment info akan diisi oleh JavaScript -->
                </div>
            </div>
        </div>

        <!-- Payment Update Section -->
        <div class="p-4 border-b border-gray-200 bg-gray-50">
            <h4 class="font-medium text-gray-800 mb-3 flex items-center">
                <i class="fas fa-money-bill-wave mr-2 text-green-500"></i>
                Update Pembayaran
            </h4>
            
            <div class="space-y-3">
                <!-- Payment Status -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status Pembayaran</label>
                    <select id="paymentStatus" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="pending">Belum Bayar</option>
                        <option value="partial">DP (Bayar Sebagian)</option>
                        <option value="paid">Lunas</option>
                    </select>
                </div>

                <!-- Payment Method -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Metode Pembayaran</label>
                    <select id="paymentMethod" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Pilih Metode</option>
                        <option value="cash">Tunai</option>
                        <option value="transfer">Transfer</option>
                        <option value="qris">QRIS</option>
                    </select>
                </div>

                <!-- Paid Amount -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah Dibayar</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">Rp</span>
                        <input type="number" id="paidAmount" 
                               class="w-full border border-gray-300 rounded-lg pl-10 pr-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                               placeholder="0" min="0" step="1000">
                    </div>
                </div>

                <!-- Change Amount Display -->
                <div id="changeAmountDisplay" class="hidden">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Kembalian:</span>
                        <span id="changeAmount" class="font-semibold text-green-600">Rp 0</span>
                    </div>
                </div>

                <!-- Update Payment Button -->
                <button onclick="trackingApp.updatePayment()" 
                        class="w-full bg-green-500 text-white py-2 px-4 rounded-lg hover:bg-green-600 transition-colors flex items-center justify-center">
                    <i class="fas fa-money-check-alt mr-2"></i>
                    Update Pembayaran
                </button>
            </div>
        </div>

        <!-- Status Options -->
        <div class="p-4">
            <h4 class="font-medium text-gray-800 mb-3">Pilih Status Baru:</h4>
            <div id="statusOptions" class="space-y-2">
                <!-- Status options akan diisi oleh JavaScript -->
            </div>
        </div>

        <!-- Modal Footer -->
        <div class="p-4 border-t border-gray-200 flex justify-end space-x-2 sticky bottom-0 bg-white">
            <button onclick="closeStatusModal()" 
                    class="px-4 py-2 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                Batal
            </button>
        </div>
    </div>
</div>