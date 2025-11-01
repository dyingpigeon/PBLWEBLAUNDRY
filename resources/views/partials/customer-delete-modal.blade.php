<!-- Delete Confirmation Modal -->
<div id="deleteCustomerModal" class="fixed inset-0 bg-black bg-opacity-50 z-30 hidden">
    <div class="absolute bottom-0 left-0 right-0 bg-white rounded-t-2xl p-4">
        <div class="text-center mb-4">
            <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-3">
                <i class="fas fa-exclamation-triangle text-red-500 text-xl"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-800 mb-2">Hapus Pelanggan?</h3>
            <p class="text-gray-600 mb-2">Anda akan menghapus pelanggan:</p>
            <p class="font-semibold text-red-600 mb-3" id="deleteCustomerName"></p>
            <p class="text-sm text-gray-500">Data akan dihapus permanen dan tidak dapat dikembalikan.</p>
        </div>

        <form id="deleteCustomerForm" method="POST">
            @csrf
            @method('DELETE')
            <input type="hidden" name="id" id="deleteCustomerId">
        </form>
        
        <div class="flex space-x-3">
            <button 
                onclick="closeDeleteModal()"
                class="flex-1 py-3 border border-gray-300 text-gray-700 rounded-xl font-semibold hover:bg-gray-50 transition-colors"
            >
                Batal
            </button>
            <button 
                onclick="submitDeleteForm()"
                class="flex-1 bg-red-500 text-white py-3 rounded-xl font-semibold hover:bg-red-600 transition-colors"
            >
                Ya, Hapus
            </button>
        </div>
    </div>
</div>