<!-- Add Item Modal -->
<div id="addItemModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50 hidden">
    <div class="bg-white rounded-2xl w-full max-w-md p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Tambah Item Layanan</h3>
            <button class="text-gray-400 hover:text-gray-600 transition-colors duration-200" onclick="closeAddItemModal()">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>
        <form id="addItemForm">
            <input type="hidden" id="addItemServiceId">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Item</label>
                    <input type="text" id="addItemName"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-200"
                        placeholder="Contoh: Cuci Express, Setrika Kiloan" required>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Harga</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">Rp</span>
                            <input type="number" id="addItemPrice"
                                class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-200"
                                min="0" placeholder="0" required>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Unit</label>
                        <select id="addItemUnit"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-200">
                            <option value="kg">Kilogram (kg)</option>
                            <option value="pcs">Piece (pcs)</option>
                            <option value="set">Set</option>
                            <option value="pasang">Pasang</option>
                            <option value="meter">Meter</option>
                            <option value="lusin">Lusin</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Estimasi Pengerjaan (jam)</label>
                    <input type="number" id="addItemEstimation"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-200"
                        min="1" max="168" placeholder="Contoh: 6" required>
                    <p class="text-xs text-gray-500 mt-1">Estimasi waktu pengerjaan dalam jam (1-168 jam)</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi Item (Opsional)</label>
                    <textarea id="addItemDescription" rows="2"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-200"
                        placeholder="Deskripsi tambahan untuk item ini..."></textarea>
                </div>
            </div>
            <div class="flex space-x-3 mt-6">
                <button type="button" onclick="closeAddItemModal()"
                    class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors duration-200">
                    Batal
                </button>
                <button type="submit"
                    class="flex-1 px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors duration-200 flex items-center justify-center space-x-2">
                    <i class="fas fa-plus"></i>
                    <span>Tambah Item</span>
                </button>
            </div>
        </form>
    </div>
</div>