<!-- Reset Confirmation Modal -->
<div id="resetModal" class="fixed inset-0 bg-black bg-opacity-50 z-30 hidden">
    <div class="absolute bottom-0 left-0 right-0 bg-white rounded-t-2xl p-4">
        <div class="text-center mb-4">
            <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-3">
                <i class="fas fa-exclamation-triangle text-red-500 text-xl"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-800 mb-2">Reset Data</h3>
            <p class="text-gray-600 mb-2">Anda akan menghapus semua data:</p>
            <ul class="text-sm text-gray-500 text-left space-y-1 mb-4">
                <li>• Semua transaksi dan pesanan</li>
                <li>• Data pelanggan</li>
                <li>• Riwayat laporan</li>
            </ul>
            <p class="text-red-500 font-medium">Tindakan ini tidak dapat dibatalkan!</p>
        </div>

        <div class="space-y-3">
            <button onclick="confirmReset()" class="w-full bg-red-500 text-white py-3 rounded-xl font-semibold">
                Ya, Hapus Semua Data
            </button>
            <button onclick="closeResetModal()" class="w-full border border-gray-300 text-gray-700 py-3 rounded-xl font-semibold">
                Batalkan
            </button>
        </div>
    </div>
</div>