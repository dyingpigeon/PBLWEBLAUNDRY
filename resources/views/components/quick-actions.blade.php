@extends('layouts.mobile')

@section('title', 'Dashboard')

@section('content')
<div class="grid grid-cols-2 gap-3">
    <!-- Transaksi Baru - Modified to act like FAB -->
    <button onclick="showNewTransactionModal()"
        class="bg-white rounded-xl p-4 shadow-sm border-2 border-blue-500 text-center active:scale-95 transition-all duration-200 hover:bg-blue-50 relative group">
        <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center mx-auto mb-2 shadow-lg">
            <i class="fas fa-plus text-white text-lg"></i>
        </div>
        <p class="text-sm font-medium text-blue-600">Transaksi Baru</p>
        
        <!-- Floating effect indicator -->
        <div class="absolute inset-0 rounded-xl border-2 border-transparent group-active:border-blue-300 transition-all duration-200"></div>
    </button>

    <!-- Pelanggan Baru -->
    <button onclick="showAddCustomerModal()"
        class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 text-center active:bg-gray-50 transition-colors hover:bg-gray-50">
        <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-2">
            <i class="fas fa-user-plus text-green-600 text-lg"></i>
        </div>
        <p class="text-sm font-medium text-gray-700">Pelanggan Baru</p>
    </button>

    <!-- Layanan & Harga -->
    <a href="{{ route('services.index') }}"
        class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 text-center active:bg-gray-50 transition-colors block">
        <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-2">
            <i class="fas fa-tshirt text-orange-600 text-lg"></i>
        </div>
        <p class="text-sm font-medium text-gray-700">Layanan & Harga</p>
    </a>

    <!-- Lihat Laporan -->
    <a href="{{ route('reports.index') }}"
        class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 text-center active:bg-gray-50 transition-colors block">
        <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-2">
            <i class="fas fa-chart-bar text-purple-600 text-lg"></i>
        </div>
        <p class="text-sm font-medium text-gray-700">Lihat Laporan</p>
    </a>
</div>

<!-- New Transaction Modal -->
<div id="newTransactionModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl w-full max-w-md mx-auto">
        <!-- Header -->
        <div class="flex items-center justify-between p-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Transaksi Baru</h3>
            <button onclick="closeNewTransactionModal()" class="p-2 text-gray-400 hover:text-gray-600 transition-colors">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>

        <!-- Form -->
        <form id="newTransactionForm" onsubmit="handleNewTransaction(event)" class="p-4">
            <div class="space-y-4">
                <!-- Customer Selection -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Pilih Pelanggan <span class="text-red-500">*</span>
                    </label>
                    <select 
                        id="transactionCustomer"
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                    >
                        <option value="">Pilih Pelanggan</option>
                        <option value="new">+ Tambah Pelanggan Baru</option>
                        <!-- Customer options will be loaded here -->
                    </select>
                </div>

                <!-- Service Selection -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Pilih Layanan <span class="text-red-500">*</span>
                    </label>
                    <select 
                        id="transactionService"
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                    >
                        <option value="">Pilih Layanan</option>
                        <!-- Service options will be loaded here -->
                    </select>
                </div>

                <!-- Items & Quantity -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Item & Jumlah
                    </label>
                    <div class="space-y-2">
                        <div class="flex space-x-2">
                            <input 
                                type="text" 
                                placeholder="Nama item"
                                class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-1 focus:ring-blue-500"
                            >
                            <input 
                                type="number" 
                                placeholder="Qty"
                                class="w-16 px-3 py-2 border border-gray-300 rounded-lg focus:ring-1 focus:ring-blue-500"
                                min="1"
                                value="1"
                            >
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex space-x-3 mt-6">
                <button 
                    type="button"
                    onclick="closeNewTransactionModal()"
                    class="flex-1 py-3 border border-gray-300 text-gray-700 rounded-xl font-semibold hover:bg-gray-50 transition-colors"
                >
                    Batal
                </button>
                <button 
                    type="submit"
                    class="flex-1 bg-blue-500 text-white py-3 rounded-xl font-semibold hover:bg-blue-600 transition-colors"
                >
                    Buat Transaksi
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Add Customer Modal -->
<div id="addCustomerModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl w-full max-w-md mx-auto">
        <!-- Header -->
        <div class="flex items-center justify-between p-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Tambah Pelanggan Baru</h3>
            <button onclick="closeAddCustomerModal()" class="p-2 text-gray-400 hover:text-gray-600 transition-colors">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>

        <!-- Form -->
        <form id="addCustomerForm" onsubmit="handleAddCustomer(event)" class="p-4">
            <div class="space-y-4">
                <!-- Name -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Nama Lengkap <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="customerName"
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                        placeholder="Masukkan nama lengkap"
                    >
                </div>

                <!-- Phone -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Nomor Telepon <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="tel" 
                        id="customerPhone"
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                        placeholder="081234567890"
                        inputmode="tel"
                    >
                </div>

                <!-- Address -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Alamat
                    </label>
                    <textarea 
                        id="customerAddress"
                        rows="3"
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                        placeholder="Masukkan alamat lengkap"
                    ></textarea>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex space-x-3 mt-6">
                <button 
                    type="button"
                    onclick="closeAddCustomerModal()"
                    class="flex-1 py-3 border border-gray-300 text-gray-700 rounded-xl font-semibold hover:bg-gray-50 transition-colors"
                >
                    Batal
                </button>
                <button 
                    type="submit"
                    class="flex-1 bg-blue-500 text-white py-3 rounded-xl font-semibold hover:bg-blue-600 transition-colors"
                >
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// New Transaction Functions
function showNewTransactionModal() {
    document.getElementById('newTransactionModal').classList.remove('hidden');
}

function closeNewTransactionModal() {
    document.getElementById('newTransactionModal').classList.add('hidden');
}

function handleNewTransaction(event) {
    event.preventDefault();
    // Handle new transaction logic here
    alert('Transaksi berhasil dibuat!');
    closeNewTransactionModal();
}

// Existing Customer Functions
function showAddCustomerModal() {
    document.getElementById('addCustomerModal').classList.remove('hidden');
}

function closeAddCustomerModal() {
    document.getElementById('addCustomerModal').classList.add('hidden');
}

function handleAddCustomer(event) {
    event.preventDefault();
    // Handle add customer logic here
    alert('Pelanggan berhasil ditambahkan!');
    closeAddCustomerModal();
}
</script>
@endsection