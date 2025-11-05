<!-- Add Service Modal -->
<div id="serviceAddModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
    <div class="absolute bottom-0 left-0 right-0 bg-white rounded-t-2xl p-4 max-h-screen overflow-y-auto">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Tambah Layanan Baru</h3>
            <button type="button" class="p-2 text-gray-400 hover:text-gray-600" onclick="closeAddServiceModal()">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>

        <form id="addServiceForm">
            <div class="space-y-4">
                <!-- Service Type -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tipe Layanan</label>
                    <select id="serviceType" name="type" required 
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            onchange="handleServiceTypeChange(this.value)">
                        <option value="">Pilih Tipe Layanan</option>
                        <option value="kiloan">Laundry Kiloan</option>
                        <option value="satuan">Laundry Satuan</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nama Layanan</label>
                    <input type="text" id="serviceName" name="name" required 
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="Contoh: Cuci Setrika Kilat">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
                    <textarea id="serviceDescription" name="description" rows="2"
                              class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                              placeholder="Deskripsi singkat layanan..."></textarea>
                </div>

                <!-- Service Items Section -->
                <div id="itemsSection">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Item Layanan</label>
                    <div id="priceItems" class="space-y-3 mb-3">
                        <!-- Price items akan di-generate oleh JavaScript -->
                    </div>
                    <button type="button" onclick="addPriceItem()" 
                            class="w-full py-2 border-2 border-dashed border-gray-300 text-gray-500 rounded-lg hover:border-gray-400 hover:text-gray-600 transition-colors duration-200 flex items-center justify-center space-x-2">
                        <i class="fas fa-plus"></i>
                        <span>Tambah Item</span>
                    </button>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Icon</label>
                        <select id="serviceIcon" name="icon" required 
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="fas fa-weight">Weight (Kiloan)</option>
                            <option value="fas fa-tshirt">T-Shirt (Satuan)</option>
                            <option value="fas fa-soap">Soap</option>
                            <option value="fas fa-fire">Fire</option>
                            <option value="fas fa-wind">Wind</option>
                            <option value="fas fa-star">Star</option>
                            <option value="fas fa-gem">Gem</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Warna</label>
                        <select id="serviceColor" name="color" required 
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="blue-500">Biru</option>
                            <option value="green-500">Hijau</option>
                            <option value="orange-500">Oranye</option>
                            <option value="purple-500">Ungu</option>
                            <option value="red-500">Merah</option>
                            <option value="yellow-500">Kuning</option>
                            <option value="pink-500">Pink</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="flex space-x-3 mt-6">
                <button type="button" onclick="closeAddServiceModal()"
                        class="flex-1 py-3 border border-gray-300 text-gray-700 rounded-xl font-semibold">
                    Batal
                </button>
                <button type="submit" 
                        class="flex-1 bg-blue-500 text-white py-3 rounded-xl font-semibold">
                    Simpan Layanan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
let priceItemCount = 1;
let currentServiceType = 'kiloan';

function handleServiceTypeChange(type) {
    currentServiceType = type;
    updateItemsSectionByType(type);
}

function updateItemsSectionByType(type) {
    const itemsSection = document.getElementById('itemsSection');
    const title = itemsSection.querySelector('label');
    
    // Clear existing items
    document.getElementById('priceItems').innerHTML = '';
    priceItemCount = 1;

    switch (type) {
        case 'kiloan':
            title.textContent = 'Harga Kiloan';
            addPriceItem('kiloan');
            break;
        case 'satuan':
            title.textContent = 'Item Satuan';
            addPriceItem('satuan');
            break;
    }
}

function addPriceItem(type = currentServiceType) {
    const container = document.getElementById('priceItems');
    const itemId = priceItemCount++;

    let itemHTML = '';

    if (type === 'kiloan') {
        itemHTML = `
            <div class="price-item bg-gray-50 p-3 rounded-lg border border-gray-200">
                <div class="grid grid-cols-2 gap-2 mb-2">
                    <div>
                        <label class="block text-xs text-gray-600 mb-1">Nama Item</label>
                        <input type="text" name="items[${itemId}][name]" value="Cuci Reguler" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-1 focus:ring-blue-500" required>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-600 mb-1">Unit</label>
                        <select name="items[${itemId}][unit]" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-1 focus:ring-blue-500" required>
                            <option value="kg">kg</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-xs text-gray-600 mb-1">Harga</label>
                        <input type="number" name="items[${itemId}][price]" placeholder="0" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-1 focus:ring-blue-500" min="0" required>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-600 mb-1">Estimasi (jam)</label>
                        <input type="number" name="items[${itemId}][estimation_time]" value="24" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-1 focus:ring-blue-500" min="1" required>
                    </div>
                </div>
                ${priceItemCount > 2 ? `<button type="button" onclick="removePriceItem(this)" class="mt-2 w-full py-1 bg-red-500 text-white rounded text-xs hover:bg-red-600 transition-colors">Hapus Item</button>` : ''}
            </div>
        `;
    } else {
        itemHTML = `
            <div class="price-item bg-gray-50 p-3 rounded-lg border border-gray-200">
                <div class="mb-2">
                    <label class="block text-xs text-gray-600 mb-1">Nama Item</label>
                    <input type="text" name="items[${itemId}][name]" placeholder="Contoh: Baju, Celana, Jaket" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-1 focus:ring-blue-500" required>
                </div>
                <div class="grid grid-cols-3 gap-2">
                    <div>
                        <label class="block text-xs text-gray-600 mb-1">Harga</label>
                        <input type="number" name="items[${itemId}][price]" placeholder="0" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-1 focus:ring-blue-500" min="0" required>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-600 mb-1">Unit</label>
                        <select name="items[${itemId}][unit]" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-1 focus:ring-blue-500" required>
                            <option value="pcs">pcs</option>
                            <option value="set">set</option>
                            <option value="pasang">pasang</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-600 mb-1">Estimasi (jam)</label>
                        <input type="number" name="items[${itemId}][estimation_time]" value="24" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-1 focus:ring-blue-500" min="1" required>
                    </div>
                </div>
                ${priceItemCount > 2 ? `<button type="button" onclick="removePriceItem(this)" class="mt-2 w-full py-1 bg-red-500 text-white rounded text-xs hover:bg-red-600 transition-colors">Hapus Item</button>` : ''}
            </div>
        `;
    }

    const itemDiv = document.createElement('div');
    itemDiv.className = 'price-item-wrapper';
    itemDiv.innerHTML = itemHTML;
    container.appendChild(itemDiv);
}

function removePriceItem(button) {
    if (button && button.closest('.price-item-wrapper')) {
        button.closest('.price-item-wrapper').remove();
    }
}

// Initialize with kiloan type
document.addEventListener('DOMContentLoaded', function() {
    updateItemsSectionByType('kiloan');
});
</script>