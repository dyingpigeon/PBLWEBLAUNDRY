@extends('layouts.mobile')

@section('title', 'Profil Saya')

@section('content')
<div class="pb-4">
    <!-- Header Profile -->
    <div class="bg-white px-4 py-6 border-b border-gray-200">
        <div class="flex items-center space-x-4">
            <!-- Avatar -->
            <div class="w-20 h-20 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full flex items-center justify-center shadow-lg">
                <span class="text-white text-2xl font-bold">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </span>
            </div>

            <!-- User Info -->
            <div class="flex-1">
                <h1 class="text-xl font-bold text-gray-800">{{ auth()->user()->name }}</h1>
                <p class="text-gray-600 mt-1">{{ auth()->user()->email }}</p>
                <div class="flex items-center space-x-2 mt-2">
                    <span class="bg-green-100 text-green-600 text-xs px-2 py-1 rounded-full">
                        <i class="fas fa-check-circle mr-1"></i>Aktif
                    </span>
                    <span class="bg-blue-100 text-blue-600 text-xs px-2 py-1 rounded-full">
                        <i class="fas fa-calendar-alt mr-1"></i>
                        Bergabung {{ auth()->user()->created_at->translatedFormat('M Y') }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="px-4 py-4 bg-gray-50 border-b border-gray-200">
        <div class="grid grid-cols-2 gap-3">
            <div class="bg-white rounded-xl p-3 text-center shadow-sm">
                <p class="text-2xl font-bold text-blue-600">{{ $stats['total_transactions'] }}</p>
                <p class="text-xs text-gray-500 mt-1">Total Transaksi</p>
            </div>
            <div class="bg-white rounded-xl p-3 text-center shadow-sm">
                <p class="text-2xl font-bold text-green-600">{{ $stats['today_transactions'] }}</p>
                <p class="text-xs text-gray-500 mt-1">Hari Ini</p>
            </div>
            <div class="bg-white rounded-xl p-3 text-center shadow-sm">
                <p class="text-2xl font-bold text-purple-600">Rp {{ number_format($stats['month_revenue'], 0, ',', '.') }}</p>
                <p class="text-xs text-gray-500 mt-1">Bulan Ini</p>
            </div>
            <div class="bg-white rounded-xl p-3 text-center shadow-sm">
                <p class="text-2xl font-bold text-orange-600">{{ $stats['total_customers'] }}</p>
                <p class="text-xs text-gray-500 mt-1">Total Pelanggan</p>
            </div>
        </div>
    </div>

    <!-- Menu Options -->
    <div class="space-y-2 px-4 mt-4">
        <!-- Edit Profile -->
        <div class="menu-item bg-white rounded-xl p-4 shadow-sm border border-gray-100" onclick="showEditProfileModal()">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-user-edit text-blue-600"></i>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-800">Edit Profil</h3>
                        <p class="text-sm text-gray-500">Ubah nama dan informasi akun</p>
                    </div>
                </div>
                <i class="fas fa-chevron-right text-gray-400"></i>
            </div>
        </div>

        <!-- Change Password -->
        <div class="menu-item bg-white rounded-xl p-4 shadow-sm border border-gray-100" onclick="showChangePasswordModal()">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-lock text-green-600"></i>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-800">Ubah Password</h3>
                        <p class="text-sm text-gray-500">Perbarui kata sandi akun</p>
                    </div>
                </div>
                <i class="fas fa-chevron-right text-gray-400"></i>
            </div>
        </div>

        <!-- Notification Settings -->
        <div class="menu-item bg-white rounded-xl p-4 shadow-sm border border-gray-100" onclick="showNotifications()">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-orange-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-bell text-orange-600"></i>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-800">Notifikasi</h3>
                        <p class="text-sm text-gray-500">Kelura pengaturan notifikasi</p>
                    </div>
                </div>
                <i class="fas fa-chevron-right text-gray-400"></i>
            </div>
        </div>

        <!-- App Settings -->
        <div class="menu-item bg-white rounded-xl p-4 shadow-sm border border-gray-100" onclick="showAppSettings()">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-cog text-purple-600"></i>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-800">Pengaturan Aplikasi</h3>
                        <p class="text-sm text-gray-500">Tema, bahasa, dan lainnya</p>
                    </div>
                </div>
                <i class="fas fa-chevron-right text-gray-400"></i>
            </div>
        </div>

        <!-- Help & Support -->
        <div class="menu-item bg-white rounded-xl p-4 shadow-sm border border-gray-100" onclick="showHelpSupport()">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-question-circle text-indigo-600"></i>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-800">Bantuan & Dukungan</h3>
                        <p class="text-sm text-gray-500">Pusat bantuan dan kontak</p>
                    </div>
                </div>
                <i class="fas fa-chevron-right text-gray-400"></i>
            </div>
        </div>

        <!-- Delete Account -->
        <div class="menu-item bg-white rounded-xl p-4 shadow-sm border border-gray-100 mt-4" onclick="showDeleteAccountModal()">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-trash-alt text-red-600"></i>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-800">Hapus Akun</h3>
                        <p class="text-sm text-gray-500">Hapus akun permanen</p>
                    </div>
                </div>
                <i class="fas fa-chevron-right text-gray-400"></i>
            </div>
        </div>

        <!-- Logout -->
        <div class="menu-item bg-white rounded-xl p-4 shadow-sm border border-gray-100" onclick="confirmLogout()">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-sign-out-alt text-red-600"></i>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-800">Keluar</h3>
                        <p class="text-sm text-gray-500">Keluar dari akun Anda</p>
                    </div>
                </div>
                <i class="fas fa-chevron-right text-gray-400"></i>
            </div>
        </div>
    </div>

    <!-- App Info -->
    <div class="px-4 mt-6 text-center">
        <p class="text-xs text-gray-400">
            LaundryKu App v1.0.0<br>
            &copy; {{ date('Y') }} LaundryKu. All rights reserved.
        </p>
    </div>
</div>

<!-- Form Logout - DI LUAR semua div content -->
<form id="logoutForm" action="{{ route('logout') }}" method="POST" class="hidden">
    @csrf
</form>

<!-- Include Modals -->
@include('partials.profile-edit-profile')
@include('partials.profile-change-password')
@include('partials.profile-delete-account')
@include('partials.profile-logout-confirm')
@endsection

@push('scripts')
<script>
// Menu item click handlers
function showEditProfileModal() {
    document.getElementById('editProfileModal').classList.remove('hidden');
}

function showChangePasswordModal() {
    document.getElementById('changePasswordModal').classList.remove('hidden');
}

function showDeleteAccountModal() {
    document.getElementById('deleteAccountModal').classList.remove('hidden');
}

function showNotifications() {
    // Redirect to notifications page
    window.location.href = '/notifications';
}

function showAppSettings() {
    // Redirect to settings page
    window.location.href = '/settings';
}

function showHelpSupport() {
    // Redirect to help page
    window.location.href = '/help';
}

// Logout functions
function confirmLogout() {
    document.getElementById('logoutModal').classList.remove('hidden');
}

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

// Utility function to format price
function formatPrice(price) {
    return new Intl.NumberFormat('id-ID').format(price);
}

// Close modals when clicking outside
document.addEventListener('click', function(e) {
    // Edit Profile Modal
    const editModal = document.getElementById('editProfileModal');
    if (editModal && !editModal.classList.contains('hidden') && e.target === editModal) {
        editModal.classList.add('hidden');
    }
    
    // Change Password Modal
    const passwordModal = document.getElementById('changePasswordModal');
    if (passwordModal && !passwordModal.classList.contains('hidden') && e.target === passwordModal) {
        passwordModal.classList.add('hidden');
    }
    
    // Delete Account Modal
    const deleteModal = document.getElementById('deleteAccountModal');
    if (deleteModal && !deleteModal.classList.contains('hidden') && e.target === deleteModal) {
        deleteModal.classList.add('hidden');
    }
    
    // Logout Modal
    const logoutModal = document.getElementById('logoutModal');
    if (logoutModal && !logoutModal.classList.contains('hidden') && e.target === logoutModal) {
        logoutModal.classList.add('hidden');
    }
});
</script>

<style>
.profile-avatar {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.stat-card {
    transition: transform 0.2s ease;
}

.stat-card:hover {
    transform: translateY(-2px);
}

.menu-item {
    transition: all 0.2s ease;
    cursor: pointer;
}

.menu-item:active {
    transform: scale(0.98);
    background-color: #f9fafb;
}
</style>
@endpush