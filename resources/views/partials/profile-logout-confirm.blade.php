<div id="logoutModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-2xl w-full max-w-sm mx-4">
        <!-- Content -->
        <div class="p-6 text-center">
            <div class="w-16 h-16 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-sign-out-alt text-orange-600 text-2xl"></i>
            </div>
            <h4 class="text-lg font-semibold text-gray-800 mb-2">Keluar dari Aplikasi?</h4>
            <p class="text-gray-600 text-sm mb-6">
                Anda akan keluar dari akun LaundryKu. Pastikan data transaksi terakhir telah disimpan.
            </p>
        </div>

        <!-- Footer -->
        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 rounded-b-2xl">
            <div class="flex space-x-3">
                <button type="button" onclick="closeLogoutModal()"
                    class="flex-1 px-4 py-3 border border-gray-300 text-gray-700 rounded-xl font-medium hover:bg-gray-50 transition-colors">
                    Batal
                </button>
                
                <!-- Form Logout yang Sesuai -->
                <form id="logoutForm" action="{{ route('logout') }}" method="POST" class="hidden">
                    @csrf
                </form>
                
                <button type="button" onclick="submitLogout()"
                    class="flex-1 px-4 py-3 bg-orange-600 text-white rounded-xl font-medium hover:bg-orange-700 transition-colors">
                    Keluar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function closeLogoutModal() {
        document.getElementById('logoutModal').classList.add('hidden');
    }

    function submitLogout() {
        // Tambahkan loading state
        const logoutBtn = document.querySelector('#logoutModal button[onclick="submitLogout()"]');
        const originalText = logoutBtn.innerHTML;
        logoutBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Keluar...';
        logoutBtn.disabled = true;
        
        // Submit form logout
        document.getElementById('logoutForm').submit();
    }

    // Close modal when clicking outside
    document.getElementById('logoutModal').addEventListener('click', function (e) {
        if (e.target === this) {
            closeLogoutModal();
        }
    });
</script>