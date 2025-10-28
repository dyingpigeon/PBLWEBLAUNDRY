<!-- Hours Settings Modal -->
<div id="hoursModal" class="fixed inset-0 bg-black bg-opacity-50 z-30 hidden">
    <div class="absolute bottom-0 left-0 right-0 bg-white rounded-t-2xl p-4 max-h-screen overflow-y-auto">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Jam Operasional</h3>
            <button onclick="closeHoursModal()" class="p-2 text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>

        <form onsubmit="saveHoursSettings(); return false;">
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
                        <label class="flex items-center">
                            <input type="checkbox" id="{{ $key }}Closed" class="sr-only peer">
                            <div class="w-10 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-red-500"></div>
                            <span class="ml-2 text-sm text-gray-600">Tutup</span>
                        </label>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs text-gray-600 mb-1">Buka</label>
                            <input type="time" id="{{ $key }}Open" value="08:00" class="w-full p-2 border border-gray-300 rounded-lg text-sm">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-600 mb-1">Tutup</label>
                            <input type="time" id="{{ $key }}Close" value="20:00" class="w-full p-2 border border-gray-300 rounded-lg text-sm">
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="flex space-x-3 mt-6">
                <button type="button" onclick="closeHoursModal()" class="flex-1 py-3 border border-gray-300 text-gray-700 rounded-xl font-semibold">Batal</button>
                <button type="submit" class="flex-1 bg-blue-500 text-white py-3 rounded-xl font-semibold">Simpan</button>
            </div>
        </form>
    </div>
</div>