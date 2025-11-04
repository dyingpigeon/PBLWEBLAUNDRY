@extends('layouts.mobile')

@section('title', 'Pengaturan')

@section('content')
    <div class="pb-16"> <!-- Tambah padding bottom untuk navigasi -->
        <!-- Profile Section -->
        <div class="bg-white border-b border-gray-200 px-4 py-4">
            <div class="flex items-center space-x-3">
                <div
                    class="w-16 h-16 bg-gradient-to-r from-blue-500 to-purple-600 rounded-2xl flex items-center justify-center">
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
                    <button onclick="openModal('businessModal')"
                        class="w-full p-4 text-left flex items-center justify-between hover:bg-gray-50 transition-colors">
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

                    <button onclick="openModal('hoursModal')"
                        class="w-full p-4 text-left flex items-center justify-between hover:bg-gray-50 transition-colors">
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
                            <div
                                class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-500">
                            </div>
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
                            <div
                                class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-500">
                            </div>
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
                    <button onclick="openModal('receiptModal')"
                        class="w-full p-4 text-left flex items-center justify-between hover:bg-gray-50 transition-colors">
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
                            <div
                                class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-500">
                            </div>
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
                    <button onclick="openModal('backupModal')"
                        class="w-full p-4 text-left flex items-center justify-between hover:bg-gray-50 transition-colors">
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

                    <button onclick="openModal('resetModal')"
                        class="w-full p-4 text-left flex items-center justify-between hover:bg-gray-50 text-red-600 transition-colors">
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
                    <button
                        class="w-full p-4 text-left flex items-center justify-between hover:bg-gray-50 transition-colors">
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

                    <button
                        class="w-full p-4 text-left flex items-center justify-between hover:bg-gray-50 transition-colors">
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

    <!-- Include Bottom Navigation -->
    @include('partials.bottom-navigation')

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

    <script src="{{ asset('js/settingsPage.js') }}">
        // Function to open modal
        function openModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.remove('hidden');
            }
        }

        // Function to close modal
        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.add('hidden');
            }
        }

        // Close modal when clicking outside
        document.addEventListener('click', function (event) {
            const modals = document.querySelectorAll('[id$="Modal"]');
            modals.forEach(modal => {
                if (event.target === modal) {
                    modal.classList.add('hidden');
                }
            });
        });

        // Close modals with Escape key
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                const modals = document.querySelectorAll('[id$="Modal"]');
                modals.forEach(modal => {
                    modal.classList.add('hidden');
                });
            }
        });

    </script>
@endsection