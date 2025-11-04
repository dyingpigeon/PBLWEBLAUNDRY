<!-- Service Detail Modal -->
<div id="serviceDetailModal"
    class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50 hidden">
    <div class="bg-white rounded-2xl w-full max-w-md max-h-[90vh] overflow-hidden">
        <!-- Modal Header -->
        <div class="flex justify-between items-center p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Detail Layanan</h3>
            <button class="text-gray-400 hover:text-gray-600 transition-colors duration-200"
                onclick="closeServiceDetailModal()">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>

        <!-- Modal Content -->
        <div class="overflow-y-auto max-h-[70vh] p-6">
            <!-- Service Header -->
            <div class="flex items-center space-x-4 mb-6">
                <div id="detailServiceIcon" class="w-16 h-16 rounded-xl flex items-center justify-center bg-blue-500">
                    <i class="fas fa-tshirt text-white text-2xl"></i>
                </div>
                <div class="flex-1">
                    <h2 id="detailServiceName" class="text-xl font-bold text-gray-800 mb-1">Nama Layanan</h2>
                    <div class="flex items-center space-x-2">
                        <span id="detailServiceType"
                            class="text-xs px-2 py-1 rounded-full bg-blue-100 text-blue-600">Kiloan</span>
                        <span id="detailServiceCategory" class="text-sm text-gray-500">Cuci</span>
                    </div>
                </div>
                <div id="detailServiceStatus">
                    <span class="text-xs px-2 py-1 rounded-full bg-green-100 text-green-600 font-medium">Aktif</span>
                </div>
            </div>

            <!-- Service Description -->
            <div id="detailServiceDescriptionContainer" class="mb-6">
                <h4 class="text-sm font-semibold text-gray-700 mb-2">Deskripsi</h4>
                <p id="detailServiceDescription" class="text-sm text-gray-600 bg-gray-50 rounded-lg p-3">
                    Deskripsi layanan akan ditampilkan di sini. Ini adalah contoh deskripsi untuk layanan laundry.
                </p>
            </div>

            <!-- Service Statistics -->
            <div class="grid grid-cols-2 gap-4 mb-6">
                <div class="bg-blue-50 rounded-lg p-3 text-center">
                    <p class="text-2xl font-bold text-blue-600" id="detailTotalItems">5</p>
                    <p class="text-xs text-blue-500 mt-1">Total Item</p>
                </div>
                <div class="bg-green-50 rounded-lg p-3 text-center">
                    <p class="text-2xl font-bold text-green-600" id="detailAvgPrice">Rp 15.000</p>
                    <p class="text-xs text-green-500 mt-1">Rata-rata Harga</p>
                </div>
            </div>

            <!-- Service Items Header dengan Tombol Tambah -->
            <div class="mb-6">
                <div class="flex justify-between items-center mb-4">
                    <h4 class="text-sm font-semibold text-gray-700">Item Layanan</h4>
                    <div class="flex items-center space-x-2">
                        <span class="text-xs text-gray-500" id="detailItemsCount">5 items</span>
                        <button onclick="showAddItemModal()"
                            class="bg-green-500 text-white p-2 rounded-lg hover:bg-green-600 transition-colors duration-200 flex items-center space-x-1">
                            <i class="fas fa-plus text-xs"></i>
                            <span class="text-xs">Tambah Item</span>
                        </button>
                    </div>
                </div>
                <div id="detailServiceItems" class="space-y-3">
                    <!-- Item 1 -->
                    <div class="service-item-card bg-gray-50 rounded-lg p-3 border border-gray-200 cursor-pointer hover:bg-gray-100 transition-colors duration-200"
                        onclick="event.stopPropagation(); showEditItemModal({name: 'Cuci Reguler', price: 10000, unit: 'kg', estimation_time: 6})">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <h5 class="font-medium text-gray-800">Cuci Reguler</h5>
                                <div class="flex items-center space-x-3 mt-2 text-xs text-gray-500">
                                    <span class="flex items-center space-x-1">
                                        <i class="fas fa-tag"></i>
                                        <span>Rp 10.000</span>
                                    </span>
                                    <span class="flex items-center space-x-1">
                                        <i class="fas fa-ruler"></i>
                                        <span>kg</span>
                                    </span>
                                    <span class="flex items-center space-x-1">
                                        <i class="fas fa-clock"></i>
                                        <span>6 jam</span>
                                    </span>
                                </div>
                            </div>
                            <div class="text-blue-500">
                                <i class="fas fa-edit"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Item 2 -->
                    <div class="service-item-card bg-gray-50 rounded-lg p-3 border border-gray-200 cursor-pointer hover:bg-gray-100 transition-colors duration-200"
                        onclick="event.stopPropagation(); showEditItemModal({name: 'Cuci Express', price: 15000, unit: 'kg', estimation_time: 3})">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <h5 class="font-medium text-gray-800">Cuci Express</h5>
                                <div class="flex items-center space-x-3 mt-2 text-xs text-gray-500">
                                    <span class="flex items-center space-x-1">
                                        <i class="fas fa-tag"></i>
                                        <span>Rp 15.000</span>
                                    </span>
                                    <span class="flex items-center space-x-1">
                                        <i class="fas fa-ruler"></i>
                                        <span>kg</span>
                                    </span>
                                    <span class="flex items-center space-x-1">
                                        <i class="fas fa-clock"></i>
                                        <span>3 jam</span>
                                    </span>
                                </div>
                            </div>
                            <div class="text-blue-500">
                                <i class="fas fa-edit"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Item 3 -->
                    <div class="service-item-card bg-gray-50 rounded-lg p-3 border border-gray-200 cursor-pointer hover:bg-gray-100 transition-colors duration-200"
                        onclick="event.stopPropagation(); showEditItemModal({name: 'Setrika Saja', price: 8000, unit: 'kg', estimation_time: 4})">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <h5 class="font-medium text-gray-800">Setrika Saja</h5>
                                <div class="flex items-center space-x-3 mt-2 text-xs text-gray-500">
                                    <span class="flex items-center space-x-1">
                                        <i class="fas fa-tag"></i>
                                        <span>Rp 8.000</span>
                                    </span>
                                    <span class="flex items-center space-x-1">
                                        <i class="fas fa-ruler"></i>
                                        <span>kg</span>
                                    </span>
                                    <span class="flex items-center space-x-1">
                                        <i class="fas fa-clock"></i>
                                        <span>4 jam</span>
                                    </span>
                                </div>
                            </div>
                            <div class="text-blue-500">
                                <i class="fas fa-edit"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Service Information -->
            <div class="border-t border-gray-200 pt-4">
                <h4 class="text-sm font-semibold text-gray-700 mb-3">Informasi Layanan</h4>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Dibuat Pada</span>
                        <span id="detailCreatedAt" class="text-gray-700 font-medium">15 Jan 2024, 10:30</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Diupdate Pada</span>
                        <span id="detailUpdatedAt" class="text-gray-700 font-medium">20 Jan 2024, 14:15</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Status</span>
                        <span id="detailActiveStatus" class="text-green-600 font-medium">Aktif</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Footer -->
        <div class="flex space-x-3 p-6 border-t border-gray-200 bg-gray-50">
            <button id="editServiceFromDetail"
                class="flex-1 px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors duration-200 flex items-center justify-center space-x-2">
                <i class="fas fa-edit"></i>
                <span>Edit Layanan</span>
            </button>
            <button onclick="closeServiceDetailModal()"
                class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors duration-200">
                Tutup
            </button>
        </div>
    </div>
</div>