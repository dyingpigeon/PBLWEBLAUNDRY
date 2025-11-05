<div id="addCustomerModal" class="fixed inset-0 bg-black bg-opacity-50 z-30 hidden">
    <div class="absolute bottom-0 left-0 right-0 bg-white rounded-t-2xl p-4 max-h-screen overflow-y-auto">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Tambah Pelanggan Baru</h3>
            <button onclick="closeAddCustomerModal()" class="p-2 text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>

        <form id="addCustomerForm" action="{{ route('customers.store') }}" method="POST">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nama Pelanggan *</label>
                    <input type="text" name="name" id="customerName" required 
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="Nama lengkap pelanggan">
                    <p class="text-xs text-gray-500 mt-1">Wajib diisi</p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Telepon *</label>
                        <input type="tel" name="phone" id="customerPhone" required
                               title="Nomor telepon harus 10-15 digit angka"
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="08xxx (10-15 digit)">
                        <p class="text-xs text-gray-500 mt-1">Wajib diisi, 10-15 digit angka</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                        <input type="email" name="email" 
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="email@contoh.com">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Alamat</label>
                    <textarea name="address" rows="3"
                              class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                              placeholder="Alamat lengkap"></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Catatan</label>
                    <textarea name="notes" rows="2"
                              class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                              placeholder="Catatan khusus"></textarea>
                </div>
            </div>

            <div class="flex space-x-3 mt-6">
                <button type="button" onclick="closeAddCustomerModal()" 
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