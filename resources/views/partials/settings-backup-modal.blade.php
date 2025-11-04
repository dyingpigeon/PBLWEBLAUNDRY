<!-- Backup Modal -->
<div id="backupModal" class="fixed inset-0 bg-black bg-opacity-50 z-30 hidden">
    <div class="absolute bottom-0 left-0 right-0 bg-white rounded-t-2xl p-4">
        <div class="text-center mb-4">
            <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-3">
                <i class="fas fa-database text-yellow-500 text-xl"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-800 mb-2">Backup Data</h3>
            <p class="text-gray-600">Backup semua data transaksi dan pelanggan ke file CSV.</p>
        </div>

        <div class="space-y-3">
            <!-- PERBAIKAN: Tambah onclick event -->
            <button onclick="performBackup()" class="w-full bg-yellow-500 text-white py-3 rounded-xl font-semibold flex items-center justify-center">
                <i class="fas fa-download mr-2"></i>Backup Sekarang
            </button>
            <button onclick="closeModal('backupModal')" class="w-full border border-gray-300 text-gray-700 py-3 rounded-xl font-semibold">
                Batal
            </button>
        </div>
    </div>
</div>