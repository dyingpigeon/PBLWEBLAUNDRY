<!-- Add Service Modal -->
<div id="addServiceModal" class="fixed inset-0 bg-black bg-opacity-50 z-30 hidden">
    <div class="absolute bottom-0 left-0 right-0 bg-white rounded-t-2xl p-4 max-h-screen overflow-y-auto">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Tambah Layanan Baru</h3>
            <button onclick="closeAddServiceModal()" class="p-2 text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>

        <form id="addServiceForm" onsubmit="handleAddService(event)">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nama Layanan</label>
                    <input type="text" id="serviceName" required 
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="Contoh: Cuci Setrika Kilat">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Kategori</label>
                    <select id="serviceCategory" required 
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Pilih Kategori</option>
                        <option value="Cuci">Cuci</option>
                        <option value="Setrika">Setrika</option>
                        <option value="Dry Clean">Dry Clean</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Icon</label>
                        <select id="serviceIcon" required 
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="fas fa-soap">Soap</option>
                            <option value="fas fa-tshirt">T-Shirt</option>
                            <option value="fas fa-fire">Fire</option>
                            <option value="fas fa-wind">Wind</option>
                            <option value="fas fa-star">Star</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Warna</label>
                        <select id="serviceColor" required 
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="blue-500">Biru</option>
                            <option value="green-500">Hijau</option>
                            <option value="orange-500">Oranye</option>
                            <option value="purple-500">Ungu</option>
                            <option value="red-500">Merah</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Item & Harga</label>
                    <div id="priceItems" class="space-y-2">
                        <div class="flex space-x-2">
                            <input type="text" placeholder="Nama item" 
                                   class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-1 focus:ring-blue-500">
                            <input type="number" placeholder="Harga" 
                                   class="w-24 px-3 py-2 border border-gray-300 rounded-lg focus:ring-1 focus:ring-blue-500">
                            <button type="button" class="w-10 h-10 bg-red-500 text-white rounded-lg flex items-center justify-center">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    <button type="button" class="mt-2 text-blue-500 text-sm font-medium">
                        <i class="fas fa-plus mr-1"></i>Tambah Item
                    </button>
                </div>
            </div>

            <div class="flex space-x-3 mt-6">
                <button type="button" onclick="closeAddServiceModal()" 
                        class="flex-1 py-3 border border-gray-300 text-gray-700 rounded-xl font-semibold">
                    Batal
                </button>
                <button type="submit" 
                        class="flex-1 bg-blue-500 text-white py-3 rounded-xl font-semibold">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function handleAddService(event) {
    event.preventDefault();
    // Handle form submission
    alert('Layanan berhasil ditambahkan!');
    closeAddServiceModal();
    loadServices();
}
</script>