<!-- Statistik Cards (Swipeable) -->
<div class="mt-4">
    <div class="flex justify-between items-center px-4 mb-2">
        <h3 class="font-medium text-gray-700">Statistik Hari Ini</h3>
        <span class="text-xs text-blue-500">Geser →</span>
    </div>
    
    <div class="swipeable flex overflow-x-auto space-x-3 px-4 pb-2" style="scrollbar-width: none;">
        <!-- Pesanan Hari Ini -->
        <div class="stat-card bg-gradient-to-r from-blue-500 to-blue-600 rounded-2xl p-4 text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-90">Pesanan Hari Ini</p>
                    <p class="text-2xl font-bold mt-1">24</p>
                    <p class="text-xs opacity-80 mt-1">+3 dari kemarin</p>
                </div>
                <div class="w-12 h-12 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                    <i class="fas fa-box text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Sedang Diproses -->
        <div class="stat-card bg-gradient-to-r from-orange-500 to-orange-600 rounded-2xl p-4 text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-90">Sedang Diproses</p>
                    <p class="text-2xl font-bold mt-1">12</p>
                    <p class="text-xs opacity-80 mt-1">4 cuci, 8 setrika</p>
                </div>
                <div class="w-12 h-12 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                    <i class="fas fa-spinner text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Selesai Hari Ini -->
        <div class="stat-card bg-gradient-to-r from-green-500 to-green-600 rounded-2xl p-4 text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-90">Selesai Hari Ini</p>
                    <p class="text-2xl font-bold mt-1">8</p>
                    <p class="text-xs opacity-80 mt-1">Semua sudah diambil</p>
                </div>
                <div class="w-12 h-12 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                    <i class="fas fa-check-circle text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Pendapatan Harian -->
        <div class="stat-card bg-gradient-to-r from-purple-500 to-purple-600 rounded-2xl p-4 text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-90">Pendapatan Harian</p>
                    <p class="text-2xl font-bold mt-1">Rp 480K</p>
                    <p class="text-xs opacity-80 mt-1">Rata-rata Rp 20K/order</p>
                </div>
                <div class="w-12 h-12 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                    <i class="fas fa-coins text-xl"></i>
                </div>
            </div>
        </div>
    </div>
</div>