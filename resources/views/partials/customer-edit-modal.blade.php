<!-- Edit Modal -->
<div id="editCustomerModal" class="fixed inset-0 bg-black bg-opacity-50 z-30 hidden">
    <div class="absolute bottom-0 left-0 right-0 bg-white rounded-t-2xl p-4 max-h-screen overflow-y-auto">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Edit Pelanggan</h3>
            <button onclick="closeEditCustomerModal()" class="p-2 text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>

        <form onsubmit="handleEditCustomer(event)">
            <input type="hidden" id="editCustomerId">
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap</label>
                    <input type="text" id="editCustomerName" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nomor Telepon</label>
                    <input type="tel" id="editCustomerPhone" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Alamat</label>
                    <textarea id="editCustomerAddress" rows="3" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                </div>
            </div>

            <div class="flex space-x-3 mt-6">
                <button type="button" onclick="closeEditCustomerModal()" class="flex-1 py-3 border border-gray-300 text-gray-700 rounded-xl font-semibold">Batal</button>
                <button type="submit" class="flex-1 bg-blue-500 text-white py-3 rounded-xl font-semibold">Update</button>
            </div>
        </form>
    </div>
</div>

<script>
function handleEditCustomer(event) {
    event.preventDefault();
    
    const formData = {
        id: document.getElementById('editCustomerId').value,
        name: document.getElementById('editCustomerName').value,
        phone: document.getElementById('editCustomerPhone').value,
        address: document.getElementById('editCustomerAddress').value,
    };

    console.log('Updating customer:', formData);
    alert('Data pelanggan berhasil diupdate!');
    closeEditCustomerModal();
    refreshCustomers();
}
</script>