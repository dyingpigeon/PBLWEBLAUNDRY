<!-- Date Picker Modal -->
<div id="dateModal" class="fixed inset-0 bg-black bg-opacity-50 z-30 hidden">
    <div class="absolute bottom-0 left-0 right-0 bg-white rounded-t-2xl p-4 max-h-screen overflow-y-auto">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Pilih Periode</h3>
            <button type="button" class="close-modal-btn p-2 text-gray-400 hover:text-gray-600" data-modal="dateModal">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>

        <!-- Quick Selection -->
        <div class="grid grid-cols-2 gap-2 mb-4">
            <button type="button" class="quick-date-btn p-3 bg-gray-100 rounded-lg text-center hover:bg-gray-200 transition-colors" data-period="today">
                <p class="font-medium text-gray-800">Hari Ini</p>
            </button>
            <button type="button" class="quick-date-btn p-3 bg-gray-100 rounded-lg text-center hover:bg-gray-200 transition-colors" data-period="week">
                <p class="font-medium text-gray-800">Minggu Ini</p>
            </button>
            <button type="button" class="quick-date-btn p-3 bg-gray-100 rounded-lg text-center hover:bg-gray-200 transition-colors" data-period="month">
                <p class="font-medium text-gray-800">Bulan Ini</p>
            </button>
            <button type="button" class="quick-date-btn p-3 bg-gray-100 rounded-lg text-center hover:bg-gray-200 transition-colors" data-period="last_month">
                <p class="font-medium text-gray-800">Bulan Lalu</p>
            </button>
        </div>

        <!-- Custom Date Range -->
        <div class="mb-4">
            <h4 class="font-medium text-gray-700 mb-3">Periode Kustom</h4>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="customStartDate" class="block text-sm text-gray-600 mb-2">Dari Tanggal</label>
                    <input type="date" id="customStartDate" class="w-full p-2 border border-gray-300 rounded-lg">
                </div>
                <div>
                    <label for="customEndDate" class="block text-sm text-gray-600 mb-2">Sampai Tanggal</label>
                    <input type="date" id="customEndDate" class="w-full p-2 border border-gray-300 rounded-lg">
                </div>
            </div>
        </div>

        <button type="button" id="applyCustomDate" class="w-full bg-blue-500 text-white py-3 rounded-lg font-semibold hover:bg-blue-600 transition-colors">
            Terapkan
        </button>
    </div>
</div>