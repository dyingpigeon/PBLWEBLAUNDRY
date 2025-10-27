<!-- Delete Confirmation Modal -->
<div id="deleteCustomerModal" class="fixed inset-0 bg-black bg-opacity-50 z-30 hidden">
    <div class="absolute bottom-0 left-0 right-0 bg-white rounded-t-2xl p-4">
        <div class="text-center mb-4">
            <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-3">
                <i class="fas fa-exclamation-triangle text-red-500 text-xl"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-800 mb-2">Hapus Pelanggan?</h3>
            <p class="text-gray-600">Data pelanggan akan dihapus permanen. Tindakan ini tidak dapat dibatalkan.</p>
        </div>

        <input type="hidden" id="deleteCustomerId">
        
        <div class="flex space-x-3">
            <button 
                onclick="closeDeleteModal()"
                class="flex-1 py-3 border border-gray-300 text-gray-700 rounded-xl font-semibold hover:bg-gray-50"
            >
                Batal
            </button>
            <button 
                onclick="confirmDelete()"
                class="flex-1 bg-red-500 text-white py-3 rounded-xl font-semibold hover:bg-red-600"
            >
                Hapus
            </button>
        </div>
    </div>
</div>

<script>
function confirmDelete() {
    const customerId = document.getElementById('deleteCustomerId').value;
    
    console.log('Deleting customer:', customerId);
    alert('Pelanggan berhasil dihapus!');
    closeDeleteModal();
    refreshCustomers();
}
</script>