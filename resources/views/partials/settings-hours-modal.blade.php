<!-- Hours Settings Modal -->
<div id="hoursModal" class="fixed inset-0 bg-black bg-opacity-50 z-30 hidden">
    <div class="absolute bottom-0 left-0 right-0 bg-white rounded-t-2xl p-4 max-h-screen overflow-y-auto">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Jam Operasional</h3>
            <button onclick="closeModal('hoursModal')" class="p-2 text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>

        <!-- PERBAIKAN: Tambah onsubmit dan ID pada input fields -->
        <form onsubmit="event.preventDefault(); saveHoursSettings();">
            <div class="space-y-4">
                <?php
                $days = [
                    'monday' => 'Senin',
                    'tuesday' => 'Selasa', 
                    'wednesday' => 'Rabu',
                    'thursday' => 'Kamis',
                    'friday' => 'Jumat',
                    'saturday' => 'Sabtu',
                    'sunday' => 'Minggu'
                ];
                ?>

                @foreach($days as $key => $day)
                <div class="p-3 border border-gray-200 rounded-lg">
                    <div class="flex items-center justify-between mb-2">
                        <label class="font-medium text-gray-800">{{ $day }}</label>
                        <label class="flex items-center cursor-pointer">
                            <!-- PERBAIKAN: Tambah ID dan hapus value default -->
                            <input type="checkbox" id="{{ $key }}Closed" class="sr-only peer day-closed-checkbox">
                            <div class="w-10 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-red-500"></div>
                            <span class="ml-2 text-sm text-gray-600">Tutup</span>
                        </label>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs text-gray-600 mb-1">Buka</label>
                            <!-- PERBAIKAN: Tambah ID dan hapus value default -->
                            <input type="time" id="{{ $key }}Open" class="w-full p-2 border border-gray-300 rounded-lg text-sm time-input">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-600 mb-1">Tutup</label>
                            <!-- PERBAIKAN: Tambah ID dan hapus value default -->
                            <input type="time" id="{{ $key }}Close" class="w-full p-2 border border-gray-300 rounded-lg text-sm time-input">
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="flex space-x-3 mt-6">
                <button type="button" onclick="closeModal('hoursModal')" 
                        class="flex-1 py-3 border border-gray-300 text-gray-700 rounded-xl font-semibold">Batal</button>
                <button type="submit" 
                        class="flex-1 bg-blue-500 text-white py-3 rounded-xl font-semibold">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
// Function untuk toggle disabled state time inputs
function toggleTimeInputs(day) {
    const closedCheckbox = document.getElementById(day + 'Closed');
    const openInput = document.getElementById(day + 'Open');
    const closeInput = document.getElementById(day + 'Close');
    
    if (closedCheckbox && openInput && closeInput) {
        const isClosed = closedCheckbox.checked;
        openInput.disabled = isClosed;
        closeInput.disabled = isClosed;
        
        if (isClosed) {
            openInput.classList.add('opacity-50', 'cursor-not-allowed');
            closeInput.classList.add('opacity-50', 'cursor-not-allowed');
        } else {
            openInput.classList.remove('opacity-50', 'cursor-not-allowed');
            closeInput.classList.remove('opacity-50', 'cursor-not-allowed');
        }
    }
}

// Attach event listeners ketika modal dibuka
document.addEventListener('DOMContentLoaded', function() {
    // Event listener untuk checkbox tutup
    const checkboxes = document.querySelectorAll('.day-closed-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const day = this.id.replace('Closed', '');
            toggleTimeInputs(day);
        });
    });

    // Set initial state untuk semua hari
    const days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
    days.forEach(day => {
        toggleTimeInputs(day);
    });
});
</script>