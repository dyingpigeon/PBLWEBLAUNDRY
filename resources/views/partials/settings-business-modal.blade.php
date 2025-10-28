<!-- Business Settings Modal -->
<div id="businessModal" class="fixed inset-0 bg-black bg-opacity-50 z-30 hidden">
    <div class="absolute bottom-0 left-0 right-0 bg-white rounded-t-2xl p-4 max-h-screen overflow-y-auto">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Profile Laundry</h3>
            <button onclick="closeBusinessModal()" class="p-2 text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>

        <form onsubmit="saveBusinessSettings(); return false;">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nama Laundry</label>
                    <input type="text" id="businessName" value="LaundryKu" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Alamat</label>
                    <textarea id="businessAddress" rows="3" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">Jl. Contoh No. 123, Jakarta</textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Telepon</label>
                    <input type="tel" id="businessPhone" value="081234567890" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                    <input type="email" id="businessEmail" value="info@laundryku.com" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
            </div>

            <div class="flex space-x-3 mt-6">
                <button type="button" onclick="closeBusinessModal()" class="flex-1 py-3 border border-gray-300 text-gray-700 rounded-xl font-semibold">Batal</button>
                <button type="submit" class="flex-1 bg-blue-500 text-white py-3 rounded-xl font-semibold">Simpan</button>
            </div>
        </form>
    </div>
</div>