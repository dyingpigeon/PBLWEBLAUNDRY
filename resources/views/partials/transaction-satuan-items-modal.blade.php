<!-- resources/views/partials/transaction-satuan-items-modal.blade.php -->
<!-- Satuan Items Modal -->
<div id="satuanItemsModal" class="fixed inset-0 bg-black bg-opacity-50 z-30 hidden">
    <div class="absolute bottom-0 left-0 right-0 bg-white rounded-t-2xl max-h-screen overflow-hidden flex flex-col">
        <!-- Header -->
        <div class="flex items-center justify-between p-4 border-b border-gray-200">
            <div>
                <h3 class="text-lg font-semibold text-gray-800">Pilih Items Satuan</h3>
                <div class="flex items-center space-x-2 mt-1">
                    <div
                        class="step-indicator step-completed w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold">
                        1</div>
                    <div
                        class="step-indicator step-completed w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold">
                        2</div>
                    <div
                        class="step-indicator step-active w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold">
                        3</div>
                    <p class="text-sm text-gray-500">Pilih Items</p>
                </div>
            </div>
            <button onclick="backToServiceModal()" class="p-2 text-gray-400 hover:text-gray-600">
                <i class="fas fa-arrow-left text-lg"></i>
            </button>
        </div>

        <!-- Search Bar -->
        <div class="p-4 border-b border-gray-200">
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-search text-gray-400"></i>
                </div>
                <input type="text" id="satuanItemsSearch" placeholder="Cari item satuan..."
                    class="w-full pl-10 pr-4 py-3 bg-gray-100 border-0 rounded-xl focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all duration-200"
                    onkeyup="filterSatuanItems(this.value)">
            </div>
        </div>

        <!-- Items Container -->
        <div class="flex-1 overflow-y-auto">
            <div class="p-4 space-y-3" id="satuanItemsContainer">
                <!-- Items will be loaded dynamically -->
            </div>

            <!-- Additional Notes -->
            <div class="p-4 border-t border-gray-200">
                <label class="block text-sm font-medium text-gray-700 mb-2">Catatan Tambahan</label>
                <textarea id="transactionNotes" rows="2"
                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    placeholder="Catatan khusus untuk pesanan ini..."
                    oninput="transactionData.notes = this.value"></textarea>
            </div>
        </div>

        <!-- Total & Action -->
        <div class="p-4 border-t border-gray-200 bg-white">
            <div class="flex items-center justify-between mb-3">
                <span class="text-lg font-semibold text-gray-800">Total:</span>
                <span id="satuanItemsTotal" class="text-2xl font-bold text-blue-600">Rp 0</span>
            </div>
            <button onclick="showPaymentModal()"
                class="w-full bg-blue-500 text-white py-3 rounded-xl font-semibold hover:bg-blue-600 transition-colors">
                Lanjut ke Pembayaran <i class="fas fa-arrow-right ml-2"></i>
            </button>
        </div>
    </div>
</div>

<script>
    // Global variables untuk items satuan

    // Render items ke container
    function renderSatuanItems() {
        const container = document.getElementById('satuanItemsContainer');

        if (filteredSatuanItems.length === 0) {
            container.innerHTML = `
            <div class="text-center py-8">
                <i class="fas fa-box-open text-gray-400 text-2xl"></i>
                <p class="text-gray-500 mt-2">Tidak ada items yang cocok</p>
            </div>
        `;
            return;
        }

        container.innerHTML = filteredSatuanItems.map(item => {
            const currentQty = getCurrentItemQuantity(item.id);

            return `
        <div class="item-card bg-white border border-gray-200 rounded-xl p-4 mb-3">
            <div class="flex items-center justify-between mb-3">
                <div class="flex-1">
                    <h4 class="font-semibold text-gray-800">${item.name}</h4>
                    <p class="text-sm text-gray-500">Rp ${formatPrice(item.price)} / ${item.unit}</p>
                    ${item.description ? `<p class="text-xs text-gray-400 mt-1">${item.description}</p>` : ''}
                    ${item.category_name ? `<span class="inline-block bg-gray-100 text-gray-600 text-xs px-2 py-1 rounded-full mt-1">${item.category_name}</span>` : ''}
                </div>
                <span class="text-lg font-bold text-blue-600 item-total" id="itemTotal-${item.id}">
                    Rp ${formatPrice(currentQty * item.price)}
                </span>
            </div>
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <button onclick="decreaseSatuanItemQuantity(${item.id})" 
                            class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center hover:bg-gray-300 transition-colors">
                        <i class="fas fa-minus text-gray-600 text-xs"></i>
                    </button>
                    <span id="itemQty-${item.id}" class="font-semibold w-8 text-center">${currentQty}</span>
                    <button onclick="increaseSatuanItemQuantity(${item.id})" 
                            class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center hover:bg-gray-300 transition-colors">
                        <i class="fas fa-plus text-gray-600 text-xs"></i>
                    </button>
                </div>
                <span class="text-sm text-gray-500">${item.unit}</span>
            </div>
        </div>
        `;
        }).join('');
    }

    // Filter items berdasarkan search
    function filterSatuanItems(searchTerm) {
        if (!searchTerm) {
            filteredSatuanItems = [...allSatuanItems];
        } else {
            const term = searchTerm.toLowerCase();
            filteredSatuanItems = allSatuanItems.filter(item =>
                item.name.toLowerCase().includes(term) ||
                (item.description && item.description.toLowerCase().includes(term)) ||
                (item.category_name && item.category_name.toLowerCase().includes(term))
            );
        }
        renderSatuanItems();
    }

    // Get current quantity dari transactionData
    function getCurrentItemQuantity(itemId) {
        const existingItem = transactionData.items.find(i => i.service_item_id == itemId);
        return existingItem ? existingItem.quantity : 0;
    }

    // Item quantity functions untuk satuan
    function increaseSatuanItemQuantity(itemId) {
        const qtyElement = document.getElementById(`itemQty-${itemId}`);
        if (!qtyElement) return;

        let quantity = parseInt(qtyElement.textContent) || 0;
        quantity++;
        qtyElement.textContent = quantity;
        updateSatuanItemCalculation(itemId, quantity);
    }

    function decreaseSatuanItemQuantity(itemId) {
        const qtyElement = document.getElementById(`itemQty-${itemId}`);
        if (!qtyElement) return;

        let quantity = parseInt(qtyElement.textContent) || 0;
        if (quantity > 0) {
            quantity--;
            qtyElement.textContent = quantity;
            updateSatuanItemCalculation(itemId, quantity);
        }
    }

    function updateSatuanItemCalculation(itemId, quantity) {
        const item = allSatuanItems.find(i => i.id == itemId);
        if (!item) return;

        const subtotal = quantity * item.price;

        // Update item total display
        const itemTotalElement = document.getElementById(`itemTotal-${itemId}`);
        if (itemTotalElement) {
            itemTotalElement.textContent = `Rp ${formatPrice(subtotal)}`;
        }

        // Update transaction data
        const existingIndex = transactionData.items.findIndex(i => i.service_item_id == itemId);

        if (quantity > 0) {
            if (existingIndex >= 0) {
                transactionData.items[existingIndex].quantity = quantity;
                transactionData.items[existingIndex].subtotal = subtotal;
            } else {
                transactionData.items.push({
                    service_item_id: itemId,
                    item_name: item.name,
                    quantity: quantity,
                    unit_price: item.price,
                    subtotal: subtotal,
                    unit: item.unit
                });
            }
        } else {
            transactionData.items = transactionData.items.filter(i => i.service_item_id != itemId);
        }

        updateSatuanTotal();
    }

    function updateSatuanTotal() {
        transactionData.total = transactionData.items.reduce((sum, item) => sum + item.subtotal, 0);
        const itemsTotalElement = document.getElementById("satuanItemsTotal");
        if (itemsTotalElement) {
            itemsTotalElement.textContent = `Rp ${formatPrice(transactionData.total)}`;
        }
    }

    // Show satuan items modal (tanpa kategori)
    function showSatuanItemsModal() {
        closeAllModals();
        document.getElementById('satuanItemsModal').classList.remove('hidden');
        loadAllSatuanItems(); // Fungsi ini ada di newTransaction.js
    }

    function backToServiceModal() {
        closeAllModals();
        document.getElementById('serviceModal').classList.remove('hidden');
    }

    // Format price helper
    function formatPrice(price) {
        return new Intl.NumberFormat('id-ID').format(price);
    }
</script>