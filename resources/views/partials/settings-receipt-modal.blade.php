<!-- Receipt Settings Modal -->
<div id="receiptModal" class="fixed inset-0 bg-black bg-opacity-50 z-30 hidden">
    <div class="absolute bottom-0 left-0 right-0 bg-white rounded-t-2xl p-4 max-h-screen overflow-y-auto">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Template Struk</h3>
            <button onclick="closeReceiptModal()" class="p-2 text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>

        <form onsubmit="saveReceiptSettings(); return false;">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Header Struk</label>
                    <textarea id="receiptHeader" rows="2"
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">LAUNDRYKU
Jl. Contoh No. 123, Jakarta
Telp: 081234567890</textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Footer Struk</label>
                    <textarea id="receiptFooter" rows="2"
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">Terima kasih atas kunjungan Anda
*** Barang yang sudah dicuci tidak dapat ditukar ***</textarea>
                </div>

                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div>
                        <p class="font-medium text-gray-800">Tampilkan Logo</p>
                        <p class="text-sm text-gray-500">Tampilkan logo di struk</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" id="showLogo" class="sr-only peer" checked>
                        <div
                            class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-500">
                        </div>
                    </label>
                </div>

                <!-- Preview -->
                <div class="border-2 border-dashed border-gray-300 rounded-lg p-4">
                    <h4 class="font-medium text-gray-700 mb-3">Preview Struk</h4>
                    <div class="bg-white border border-gray-200 p-3 text-xs font-mono">
                        <div class="text-center font-bold">LAUNDRYKU</div>
                        <div class="text-center">Jl. Contoh No. 123, Jakarta</div>
                        <div class="text-center">Telp: 081234567890</div>
                        <div class="border-t border-dashed border-gray-300 my-2"></div>
                        <div>No: LAUNDRY-0012</div>
                        <div>Tanggal: 15 Jan 2024</div>
                        <div>Customer: Budi Santoso</div>
                        <div class="border-t border-dashed border-gray-300 my-2"></div>
                        <div>Cuci Setrika - 2 kg x Rp 20.000 = Rp 40.000</div>
                        <div class="border-t border-dashed border-gray-300 my-2"></div>
                        <div class="font-bold">TOTAL: Rp 40.000</div>
                        <div class="border-t border-dashed border-gray-300 my-2"></div>
                        <div class="text-center text-xs">Terima kasih atas kunjungan Anda</div>
                        <div class="text-center text-xs">*** Barang yang sudah dicuci tidak dapat ditukar ***</div>
                    </div>
                </div>
            </div>

            <div class="flex space-x-3 mt-6">
                <button type="button" onclick="closeReceiptModal()"
                    class="flex-1 py-3 border border-gray-300 text-gray-700 rounded-xl font-semibold">Batal</button>
                <button type="submit"
                    class="flex-1 bg-blue-500 text-white py-3 rounded-xl font-semibold">Simpan</button>
            </div>
        </form>
    </div>
</div>