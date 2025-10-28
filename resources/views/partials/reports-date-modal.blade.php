<!-- Date Picker Modal -->
<div id="dateModal" class="fixed inset-0 bg-black bg-opacity-50 z-30 hidden">
    <div class="absolute bottom-0 left-0 right-0 bg-white rounded-t-2xl p-4 max-h-screen overflow-y-auto">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Pilih Periode</h3>
            <button onclick="closeDateModal()" class="p-2 text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>

        <!-- Quick Selection -->
        <div class="grid grid-cols-2 gap-2 mb-4">
            <button class="p-3 bg-gray-100 rounded-lg text-center" onclick="selectQuickDate('today')">
                <p class="font-medium text-gray-800">Hari Ini</p>
            </button>
            <button class="p-3 bg-gray-100 rounded-lg text-center" onclick="selectQuickDate('week')">
                <p class="font-medium text-gray-800">Minggu Ini</p>
            </button>
            <button class="p-3 bg-gray-100 rounded-lg text-center" onclick="selectQuickDate('month')">
                <p class="font-medium text-gray-800">Bulan Ini</p>
            </button>
            <button class="p-3 bg-gray-100 rounded-lg text-center" onclick="selectQuickDate('last_month')">
                <p class="font-medium text-gray-800">Bulan Lalu</p>
            </button>
        </div>

        <!-- Custom Date Range -->
        <div class="mb-4">
            <h4 class="font-medium text-gray-700 mb-3">Periode Kustom</h4>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm text-gray-600 mb-2">Dari Tanggal</label>
                    <input type="date" class="w-full p-2 border border-gray-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-2">Sampai Tanggal</label>
                    <input type="date" class="w-full p-2 border border-gray-300 rounded-lg">
                </div>
            </div>
        </div>

        <button class="w-full bg-blue-500 text-white py-3 rounded-lg font-semibold">
            Terapkan
        </button>
    </div>
</div>

<script>
function closeDateModal() {
    document.getElementById('dateModal').classList.add('hidden');
}

function selectQuickDate(period) {
    const now = new Date();
    let start, end;

    switch(period) {
        case 'today':
            start = end = new Date(now);
            break;
        case 'week':
            start = new Date(now);
            start.setDate(now.getDate() - now.getDay());
            end = new Date(now);
            end.setDate(now.getDate() + (6 - now.getDay()));
            break;
        case 'month':
            start = new Date(now.getFullYear(), now.getMonth(), 1);
            end = new Date(now.getFullYear(), now.getMonth() + 1, 0);
            break;
        case 'last_month':
            start = new Date(now.getFullYear(), now.getMonth() - 1, 1);
            end = new Date(now.getFullYear(), now.getMonth(), 0);
            break;
    }

    currentDateRange = { start, end };
    loadReportData();
    closeDateModal();
}
</script>