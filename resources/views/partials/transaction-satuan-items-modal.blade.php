<!-- resources/views/partials/transaction-satuan-items-modal.blade.php -->
<!-- Satuan Items Modal -->
<div id="satuanItemsModal" class="fixed inset-0 bg-black bg-opacity-50 z-30 hidden">
    <div class="absolute bottom-0 left-0 right-0 bg-white rounded-t-2xl max-h-screen overflow-hidden flex flex-col">
        <!-- Header -->
        <div class="flex items-center justify-between p-4 border-b border-gray-200">
            <div>
                <h3 class="text-lg font-semibold text-gray-800" id="satuanItemsTitle">Pilih Items</h3>
                <div class="flex items-center space-x-2 mt-1">
                    <div class="step-indicator step-completed w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold">1</div>
                    <div class="step-indicator step-completed w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold">2</div>
                    <div class="step-indicator step-completed w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold">3</div>
                    <div class="step-indicator step-active w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold">4</div>
                    <p class="text-sm text-gray-500">Pilih Items</p>
                </div>
            </div>
            <button onclick="backToSatuanModal()" class="p-2 text-gray-400 hover:text-gray-600">
                <i class="fas fa-arrow-left text-lg"></i>
            </button>
        </div>

        <!-- Items Container -->
        <div class="flex-1 overflow-y-auto">
            <div class="p-4 space-y-3" id="satuanItemsContainer">
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
// Load category items
function loadCategoryItems(categoryId) {
    const container = document.getElementById('satuanItemsContainer');
    const title = document.getElementById('satuanItemsTitle');
    
    if (title && transactionData.selected_category) {
        title.textContent = `Pilih Items - ${transactionData.selected_category.name}`;
    }
    
    container.innerHTML = `
        <div class="text-center py-8">
            <i class="fas fa-spinner fa-spin text-blue-500 text-2xl"></i>
            <p class="text-gray-500 mt-2">Memuat items...</p>
        </div>
    `;

    fetch(`/api/transactions/categories/${categoryId}/items`, {
        method: "GET",
        headers: {
            Accept: "application/json",
            "X-Requested-With": "XMLHttpRequest",
            "X-CSRF-TOKEN": '{{ csrf_token() }}',
        },
    })
    .then(response => {
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            return response.text().then(text => {
                throw new Error(`Server returned HTML instead of JSON. Status: ${response.status}`);
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            const items = data.data;
            
            if (items.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-8">
                        <i class="fas fa-box-open text-gray-400 text-2xl"></i>
                        <p class="text-gray-500 mt-2">Tidak ada items dalam kategori ini</p>
                    </div>
                `;
                return;
            }

            // Set service untuk satuan (gunakan service dari item pertama)
            if (items.length > 0 && items[0].service) {
                transactionData.service = items[0].service;
            }
            
            container.innerHTML = items.map(item => `
                <div class="item-card bg-white border border-gray-200 rounded-xl p-4 mb-3">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex-1">
                            <h4 class="font-semibold text-gray-800">${item.name}</h4>
                            <p class="text-sm text-gray-500">Rp ${formatPrice(item.price)} / ${item.unit}</p>
                            ${item.description ? `<p class="text-xs text-gray-400 mt-1">${item.description}</p>` : ''}
                        </div>
                        <span class="text-lg font-bold text-blue-600 item-total" id="itemTotal-${item.id}">Rp 0</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <button onclick="decreaseSatuanItemQuantity(${item.id})" 
                                    class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center hover:bg-gray-300 transition-colors">
                                <i class="fas fa-minus text-gray-600 text-xs"></i>
                            </button>
                            <span id="itemQty-${item.id}" class="font-semibold w-8 text-center">0</span>
                            <button onclick="increaseSatuanItemQuantity(${item.id})" 
                                    class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center hover:bg-gray-300 transition-colors">
                                <i class="fas fa-plus text-gray-600 text-xs"></i>
                            </button>
                        </div>
                        <span class="text-sm text-gray-500">${item.unit}</span>
                    </div>
                </div>
            `).join('');

            // Reset items data
            transactionData.items = [];
            updateSatuanTotal();

        } else {
            throw new Error(data.message);
        }
    })
    .catch(error => {
        console.error('Error loading category items:', error);
        container.innerHTML = `
            <div class="text-center py-8">
                <i class="fas fa-exclamation-triangle text-red-500 text-2xl"></i>
                <p class="text-red-500 mt-2">Gagal memuat items</p>
                <p class="text-xs text-red-400 mt-1">${error.message}</p>
            </div>
        `;
    });
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
    // Get item price from displayed elements
    const itemElement = document.querySelector(`[onclick*="increaseSatuanItemQuantity(${itemId})"]`).closest('.item-card');
    const priceText = itemElement.querySelector('p.text-sm.text-gray-500').textContent;
    const price = parseInt(priceText.replace('Rp ', '').replace(/\./g, '').split(' /')[0]);
    
    const subtotal = quantity * price;

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
                item_name: itemElement.querySelector('h4').textContent,
                quantity: quantity,
                unit_price: price,
                subtotal: subtotal,
                unit: 'pcs'
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

// Format price helper (pastikan ada di global scope)
function formatPrice(price) {
    return new Intl.NumberFormat('id-ID').format(price);
}
</script>