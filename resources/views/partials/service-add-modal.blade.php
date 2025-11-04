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
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Pilih Tipe Layanan</option>
                        <option value="kiloan">Laundry Kiloan</option>
                        <option value="satuan">Laundry Satuan</option>
                        <option value="khusus">Layanan Khusus</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nama Layanan</label>
                    <input type="text" id="serviceName" name="name" required 
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="Contoh: Cuci Setrika Kilat">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Kategori</label>
                    <select id="serviceCategory" name="category" required 
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Pilih Kategori</option>
                        <option value="Cuci">Cuci</option>
                        <option value="Setrika">Setrika</option>
                        <option value="Dry Clean">Dry Clean</option>
                        <option value="Khusus">Layanan Khusus</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
                    <textarea id="serviceDescription" name="description" rows="2"
                              class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                              placeholder="Deskripsi singkat layanan..."></textarea>
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
                            <option value="fas fa-star">Star (Khusus)</option>
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