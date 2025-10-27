<div class="grid grid-cols-2 gap-3">
    <!-- Transaksi Baru -->
    <button class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 text-center active:bg-gray-50 transition-colors">
        <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-2">
            <i class="fas fa-plus text-blue-600 text-lg"></i>
        </div>
        <p class="text-sm font-medium text-gray-700">Transaksi Baru</p>
    </button>

    <!-- Pelanggan Baru -->
    <button class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 text-center active:bg-gray-50 transition-colors">
        <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-2">
            <i class="fas fa-user-plus text-green-600 text-lg"></i>
        </div>
        <p class="text-sm font-medium text-gray-700">Pelanggan Baru</p>
    </button>

    <!-- Layanan & Harga -->
    <a href="{{ route('services.index') }}" class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 text-center active:bg-gray-50 transition-colors block">
        <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-2">
            <i class="fas fa-tshirt text-orange-600 text-lg"></i>
        </div>
        <p class="text-sm font-medium text-gray-700">Layanan & Harga</p>
    </a>

    <!-- Lihat Laporan -->
    <a href="{{ route('reports.index') }}" class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 text-center active:bg-gray-50 transition-colors block">
        <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-2">
            <i class="fas fa-chart-bar text-purple-600 text-lg"></i>
        </div>
        <p class="text-sm font-medium text-gray-700">Lihat Laporan</p>
    </a>
</div>