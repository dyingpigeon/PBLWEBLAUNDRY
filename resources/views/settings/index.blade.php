@extends('layouts.mobile')

@section('title', 'Pengaturan')

@section('content')
<div class="pb-4">
    <!-- Profile Section -->
    <div class="bg-white border-b border-gray-200 px-4 py-4">
        <div class="flex items-center space-x-3">
            <div class="w-16 h-16 bg-gradient-to-r from-blue-500 to-purple-600 rounded-2xl flex items-center justify-center">
                <i class="fas fa-tshirt text-white text-2xl"></i>
            </div>
            <div class="flex-1">
                <h2 class="text-lg font-bold text-gray-800">LaundryKu</h2>
                <p class="text-sm text-gray-500">Admin Dashboard</p>
                <p class="text-xs text-gray-400 mt-1">Version 1.0.0</p>
            </div>
        </div>
    </div>

    <!-- Settings List -->
    <div class="space-y-2 px-4 mt-2">
        <!-- Business Settings -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-800">Informasi Bisnis</h3>
            </div>
            <div class="divide-y divide-gray-100">
                <button onclick="showBusinessModal()" class="w-full p-4 text-left flex items-center justify-between hover:bg-gray-50 transition-colors">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-store text-blue-600"></i>
                        </div>
                        <div>
                            <p class="font-medium text-gray-800">Profile Laundry</p>
                            <p class="text-sm text-gray-500">Nama, alamat, telepon</p>
                        </div>
                    </div>
                    <i class="fas fa-chevron-right text-gray-400"></i>
                </button>
                
                <button onclick="showHoursModal()" class="w-full p-4 text-left flex items-center justify-between hover:bg-gray-50 transition-colors">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-clock text-green-600"></i>
                        </div>
                        <div>
                            <p class="font-medium text-gray-800">Jam Operasional</p>
                            <p class="text-sm text-gray-500">Atur waktu buka-tutup</p>
                        </div>
                    </div>
                    <i class="fas fa-chevron-right text-gray-400"></i>
                </button>
            </div>
        </div>

        <!-- Notification Settings -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-800">Notifikasi</h3>
            </div>
            <div class="divide-y divide-gray-100">
                <div class="p-4 flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-bell text-purple-600"></i>
                        </div>
                        <div>
                            <p class="font-medium text-gray-800">Notifikasi Pesanan Baru</p>
                            <p class="text-sm text-gray-500">Notif saat ada pesanan masuk</p>
                        </div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" class="sr-only peer" checked>
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-500"></div>
                    </label>
                </div>
                
                <div class="p-4 flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-bell-slash text-orange-600"></i>
                        </div>
                        <div>
                            <p class="font-medium text-gray-800">Notifikasi Status</p>
                            <p class="text-sm text-gray-500">Notif saat status berubah</p>
                        </div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" class="sr-only peer" checked>
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-500"></div>
                    </label>
                </div>
            </div>
        </div>

        <!-- Receipt Settings -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-800">Struk & Nota</h3>
            </div>
            <div class="divide-y divide-gray-100">
                <button onclick="showReceiptModal()" class="w-full p-4 text-left flex items-center justify-between hover:bg-gray-50 transition-colors">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-receipt text-red-600"></i>
                        </div>
                        <div>
                            <p class="font-medium text-gray-800">Template Struk</p>
                            <p class="text-sm text-gray-500">Atur header, footer, logo</p>
                        </div>
                    </div>
                    <i class="fas fa-chevron-right text-gray-400"></i>
                </button>
                
                <div class="p-4 flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-print text-indigo-600"></i>
                        </div>
                        <div>
                            <p class="font-medium text-gray-800">Auto Print Struk</p>
                            <p class="text-sm text-gray-500">Print otomatis setelah transaksi</p>
                        </div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-500"></div>
                    </label>
                </div>
            </div>
        </div>

        <!-- Data Management -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-800">Data & Backup</h3>
            </div>
            <div class="divide-y divide-gray-100">
                <button onclick="showBackupModal()" class="w-full p-4 text-left flex items-center justify-between hover:bg-gray-50 transition-colors">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-database text-yellow-600"></i>
                        </div>
                        <div>
                            <p class="font-medium text-gray-800">Backup Data</p>
                            <p class="text-sm text-gray-500">Backup database manual</p>
                        </div>
                    </div>
                    <i class="fas fa-chevron-right text-gray-400"></i>
                </button>
                
                <button onclick="showResetModal()" class="w-full p-4 text-left flex items-center justify-between hover:bg-gray-50 text-red-600 transition-colors">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-trash-alt text-red-600"></i>
                        </div>
                        <div>
                            <p class="font-medium">Reset Data</p>
                            <p class="text-sm text-red-400">Hapus semua data transaksi</p>
                        </div>
                    </div>
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>

        <!-- App Info -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="divide-y divide-gray-100">
                <button class="w-full p-4 text-left flex items-center justify-between hover:bg-gray-50 transition-colors">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-info-circle text-gray-600"></i>
                        </div>
                        <div>
                            <p class="font-medium text-gray-800">Tentang Aplikasi</p>
                            <p class="text-sm text-gray-500">Version 1.0.0</p>
                        </div>
                    </div>
                    <i class="fas fa-chevron-right text-gray-400"></i>
                </button>
                
                <button class="w-full p-4 text-left flex items-center justify-between hover:bg-gray-50 transition-colors">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-question-circle text-blue-600"></i>
                        </div>
                        <div>
                            <p class="font-medium text-gray-800">Bantuan & Support</p>
                            <p class="text-sm text-gray-500">Panduan penggunaan</p>
                        </div>
                    </div>
                    <i class="fas fa-chevron-right text-gray-400"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Business Settings Modal -->
@include('partials.settings-business-modal')

<!-- Hours Settings Modal -->
@include('partials.settings-hours-modal')

<!-- Receipt Settings Modal -->
@include('partials.settings-receipt-modal')

<!-- Backup Modal -->
@include('partials.settings-backup-modal')

<!-- Reset Confirmation Modal -->
@include('partials.settings-reset-modal')
@endsection

@push('scripts')
<script>
// Toggle switch functionality
document.addEventListener('DOMContentLoaded', function() {
    // Initialize all toggle switches
    const toggleSwitches = document.querySelectorAll('input[type="checkbox"]');
    toggleSwitches.forEach(switchElement => {
        switchElement.addEventListener('change', function() {
            const settingName = this.closest('.flex.items-center.justify-between').querySelector('.font-medium').textContent;
            const isEnabled = this.checked;
            
            // Simulate saving setting
            console.log(`Setting "${settingName}" ${isEnabled ? 'enabled' : 'disabled'}`);
            
            // Show feedback
            if (isEnabled) {
                showToast('Pengaturan berhasil diaktifkan');
            }
        });
    });
});

// Show modals
function showBusinessModal() {
    document.getElementById('businessModal').classList.remove('hidden');
}

function showHoursModal() {
    document.getElementById('hoursModal').classList.remove('hidden');
}

function showReceiptModal() {
    document.getElementById('receiptModal').classList.remove('hidden');
}

function showBackupModal() {
    document.getElementById('backupModal').classList.remove('hidden');
}

function showResetModal() {
    document.getElementById('resetModal').classList.remove('hidden');
}

// Close modals
function closeBusinessModal() {
    document.getElementById('businessModal').classList.add('hidden');
}

function closeHoursModal() {
    document.getElementById('hoursModal').classList.add('hidden');
}

function closeReceiptModal() {
    document.getElementById('receiptModal').classList.add('hidden');
}

function closeBackupModal() {
    document.getElementById('backupModal').classList.add('hidden');
}

function closeResetModal() {
    document.getElementById('resetModal').classList.add('hidden');
}

// Save business settings
function saveBusinessSettings() {
    const formData = {
        name: document.getElementById('businessName').value,
        address: document.getElementById('businessAddress').value,
        phone: document.getElementById('businessPhone').value,
        email: document.getElementById('businessEmail').value
    };
    
    console.log('Saving business settings:', formData);
    showToast('Data bisnis berhasil disimpan');
    closeBusinessModal();
}

// Save hours settings
function saveHoursSettings() {
    const days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
    const hours = {};
    
    days.forEach(day => {
        hours[day] = {
            open: document.getElementById(`${day}Open`).value,
            close: document.getElementById(`${day}Close`).value,
            closed: document.getElementById(`${day}Closed`).checked
        };
    });
    
    console.log('Saving hours settings:', hours);
    showToast('Jam operasional berhasil disimpan');
    closeHoursModal();
}

// Save receipt settings
function saveReceiptSettings() {
    const formData = {
        header: document.getElementById('receiptHeader').value,
        footer: document.getElementById('receiptFooter').value,
        showLogo: document.getElementById('showLogo').checked
    };
    
    console.log('Saving receipt settings:', formData);
    showToast('Template struk berhasil disimpan');
    closeReceiptModal();
}

// Perform backup
function performBackup() {
    showToast('Memulai backup data...');
    
    // Simulate backup process
    setTimeout(() => {
        showToast('Backup data berhasil!', 'success');
        closeBackupModal();
    }, 2000);
}

// Perform reset
function confirmReset() {
    showToast('Menghapus semua data...', 'warning');
    
    // Simulate reset process
    setTimeout(() => {
        showToast('Data berhasil direset!', 'success');
        closeResetModal();
    }, 3000);
}

// Toast notification
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `fixed top-4 left-4 right-4 p-3 rounded-lg shadow-lg text-white font-medium z-50 transform transition-transform duration-300 ${
        type === 'success' ? 'bg-green-500' : 
        type === 'warning' ? 'bg-orange-500' : 'bg-blue-500'
    }`;
    toast.textContent = message;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.remove();
    }, 3000);
}
</script>
@endpush