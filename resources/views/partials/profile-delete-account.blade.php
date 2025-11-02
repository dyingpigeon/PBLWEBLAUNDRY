<div id="deleteAccountModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-2xl w-full max-w-md mx-4">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-red-600">Hapus Akun</h3>
                <button onclick="closeDeleteAccountModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>

        <!-- Content -->
        <div class="p-6">
            <div class="text-center mb-4">
                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-exclamation-triangle text-red-600 text-2xl"></i>
                </div>
                <h4 class="text-lg font-semibold text-gray-800 mb-2">Apakah Anda yakin?</h4>
                <p class="text-gray-600 text-sm mb-4">
                    Tindakan ini tidak dapat dibatalkan. Semua data Anda termasuk transaksi, pelanggan, dan informasi akun akan dihapus secara permanen.
                </p>
            </div>

            <!-- Warning List -->
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
                <p class="text-sm font-medium text-red-800 mb-2">Yang akan terjadi:</p>
                <ul class="text-xs text-red-700 space-y-1">
                    <li class="flex items-start">
                        <i class="fas fa-times-circle mt-0.5 mr-2 flex-shrink-0"></i>
                        Semua data transaksi akan dihapus
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-times-circle mt-0.5 mr-2 flex-shrink-0"></i>
                        Data pelanggan akan dihapus
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-times-circle mt-0.5 mr-2 flex-shrink-0"></i>
                        Akun tidak dapat dipulihkan
                    </li>
                </ul>
            </div>

            <!-- Confirmation Input -->
            <div class="mb-4">
                <label for="confirmDelete" class="block text-sm font-medium text-gray-700 mb-2">
                    Ketik <span class="font-mono text-red-600">HAPUS AKUN</span> untuk konfirmasi
                </label>
                <input type="text" id="confirmDelete" 
                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors"
                       placeholder="HAPUS AKUN">
            </div>
        </div>

        <!-- Footer -->
        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 rounded-b-2xl">
            <div class="flex space-x-3">
                <button type="button" onclick="closeDeleteAccountModal()" 
                        class="flex-1 px-4 py-3 border border-gray-300 text-gray-700 rounded-xl font-medium hover:bg-gray-50 transition-colors">
                    Batalkan
                </button>
                <button type="button" onclick="submitDeleteAccount()" id="deleteAccountBtn" disabled
                        class="flex-1 px-4 py-3 bg-red-600 text-white rounded-xl font-medium hover:bg-red-700 transition-colors opacity-50 cursor-not-allowed">
                    Hapus Akun
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function closeDeleteAccountModal() {
    document.getElementById('deleteAccountModal').classList.add('hidden');
    document.getElementById('confirmDelete').value = '';
    document.getElementById('deleteAccountBtn').disabled = true;
    document.getElementById('deleteAccountBtn').classList.add('opacity-50', 'cursor-not-allowed');
}

function submitDeleteAccount() {
    const confirmationText = document.getElementById('confirmDelete').value;
    const password = prompt('Masukkan password Anda untuk konfirmasi:');
    
    if (!password) {
        return;
    }
    
    if (confirmationText === 'HAPUS AKUN') {
        // Add loading state
        const deleteBtn = document.getElementById('deleteAccountBtn');
        deleteBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Menghapus...';
        deleteBtn.disabled = true;
        
        // Submit delete request
        fetch('{{ route("profile.destroy") }}', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                password: password
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                window.location.href = data.redirect_url;
            } else {
                alert(data.message);
                closeDeleteAccountModal();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat menghapus akun.');
            closeDeleteAccountModal();
        });
    } else {
        alert('Teks konfirmasi tidak sesuai!');
    }
}

// Close modal when clicking outside
document.getElementById('deleteAccountModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeDeleteAccountModal();
    }
});

// Enable delete button only when confirmation text matches
document.getElementById('confirmDelete').addEventListener('input', function(e) {
    const deleteBtn = document.getElementById('deleteAccountBtn');
    const confirmationText = e.target.value;
    
    if (confirmationText === 'HAPUS AKUN') {
        deleteBtn.disabled = false;
        deleteBtn.classList.remove('opacity-50', 'cursor-not-allowed');
    } else {
        deleteBtn.disabled = true;
        deleteBtn.classList.add('opacity-50', 'cursor-not-allowed');
    }
});
</script>