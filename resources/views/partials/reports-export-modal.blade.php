<!-- Export Options Modal -->
<div id="exportModal" class="fixed inset-0 bg-black bg-opacity-50 z-30 hidden">
    <div class="absolute bottom-0 left-0 right-0 bg-white rounded-t-2xl p-4">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Export Laporan</h3>
            <button onclick="closeExportModal()" class="p-2 text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>

        <div class="space-y-3">
            <button 
                onclick="exportReport('pdf')"
                class="w-full p-4 bg-white border border-gray-300 rounded-xl text-left flex items-center justify-between hover:bg-gray-50 transition-colors"
            >
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center mr-3">
                        <i class="fas fa-file-pdf text-red-600"></i>
                    </div>
                    <div>
                        <p class="font-medium text-gray-800">Export PDF</p>
                        <p class="text-sm text-gray-500">Format dokumen</p>
                    </div>
                </div>
                <i class="fas fa-chevron-right text-gray-400"></i>
            </button>

            <button 
                onclick="exportReport('excel')"
                class="w-full p-4 bg-white border border-gray-300 rounded-xl text-left flex items-center justify-between hover:bg-gray-50 transition-colors"
            >
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center mr-3">
                        <i class="fas fa-file-excel text-green-600"></i>
                    </div>
                    <div>
                        <p class="font-medium text-gray-800">Export Excel</p>
                        <p class="text-sm text-gray-500">Format spreadsheet</p>
                    </div>
                </div>
                <i class="fas fa-chevron-right text-gray-400"></i>
            </button>
        </div>
    </div>
</div>

<script>
function closeExportModal() {
    document.getElementById('exportModal').classList.add('hidden');
}
</script>