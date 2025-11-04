<!-- Edit Service Modal -->
<div id="editServiceModal"
    class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50 hidden">
    <div class="bg-white rounded-2xl w-full max-w-md p-6 max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Edit Layanan</h3>
            <button class="text-gray-400 hover:text-gray-600 transition-colors duration-200" onclick="closeEditServiceModal()">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>
        <form id="editServiceForm">
            <input type="hidden" id="editServiceId">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipe Layanan</label>
                    <select id="editServiceType"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-200"
                        required>
                        <option value="kiloan">Laundry Kiloan</option>
                        <option value="satuan">Laundry Satuan</option>
                        <option value="khusus">Layanan Khusus</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Layanan</label>
                    <input type="text" id="editServiceName"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-200"
                        required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
                    <select id="editServiceCategory"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-200"
                        required>
                        <option value="Cuci">Cuci</option>
                        <option value="Setrika">Setrika</option>
                        <option value="Dry Clean">Dry Clean</option>
                        <option value="Khusus">Layanan Khusus</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                    <textarea id="editServiceDescription" rows="2"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-200"
                        placeholder="Deskripsi layanan..."></textarea>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Icon</label>
                        <select id="editServiceIcon"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-200"
                            required>
                            <option value="fas fa-weight">Weight</option>
                            <option value="fas fa-tshirt">T-Shirt</option>
                            <option value="fas fa-star">Star</option>
                            <option value="fas fa-soap">Soap</option>
                            <option value="fas fa-fire">Fire</option>
                            <option value="fas fa-wind">Wind</option>
                            <option value="fas fa-gem">Gem</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Warna</label>
                        <select id="editServiceColor"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-200"
                            required>
                            <option value="blue-500">Biru</option>
                            <option value="green-500">Hijau</option>
                            <option value="orange-500">Oranye</option>
                            <option value="purple-500">Ungu</option>
                            <option value="red-500">Merah</option>
                            <option value="pink-500">Pink</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="flex space-x-3 mt-6">
                <button type="button" onclick="closeEditServiceModal()"
                    class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors duration-200">Batal</button>
                <button type="submit"
                    class="flex-1 px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors duration-200">Simpan</button>
            </div>
        </form>
    </div>
</div>