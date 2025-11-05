@extends('layouts.mobile')

@section('title', 'Dashboard')

@section('content')
    <div class="px-4">
        <!-- Welcome Section -->
        <div class="pt-4">
            <h2 class="text-lg font-semibold text-gray-700">Selamat {{ $waktu }}, {{ explode(' ', $user->name)[0] }}! 👋
            </h2>
            <p class="text-sm text-gray-500">{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</p>
        </div>

        <!-- Quick Actions -->
        <div class="mt-6">
            <h3 class="font-medium text-gray-700 mb-3">Quick Actions</h3>
            <div class="grid grid-cols-2 gap-3">
                <!-- Transaksi Baru -->
                <button onclick="startNewTransaction()"
                    class="bg-white rounded-xl p-4 shadow-sm border-2 border-blue-500 text-center active:scale-95 transition-all duration-200 hover:bg-blue-50 relative group">
                    <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center mx-auto mb-2 shadow-lg">
                        <i class="fas fa-plus text-white text-lg"></i>
                    </div>
                    <p class="text-sm font-medium text-blue-600">Transaksi Baru</p>
                    <div
                        class="absolute inset-0 rounded-xl border-2 border-transparent group-active:border-blue-300 transition-all duration-200">
                    </div>
                </button>

                <!-- Pelanggan Baru -->
                <button onclick="showAddCustomerModal()"
                    class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 text-center active:scale-95 transition-all duration-200 hover:bg-gray-50 group">
                    <div
                        class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-2 group-active:scale-110 transition-transform">
                        <i class="fas fa-user-plus text-green-600 text-lg"></i>
                    </div>
                    <p class="text-sm font-medium text-gray-700">Pelanggan Baru</p>
                </button>

                <!-- Layanan & Harga -->
                <a href="/services"
                    class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 text-center active:scale-95 transition-all duration-200 hover:bg-gray-50 block group">
                    <div
                        class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-2 group-active:scale-110 transition-transform">
                        <i class="fas fa-tshirt text-orange-600 text-lg"></i>
                    </div>
                    <p class="text-sm font-medium text-gray-700">Layanan & Harga</p>
                </a>

                <!-- Lihat Laporan -->
                <a href="/reports"
                    class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 text-center active:scale-95 transition-all duration-200 hover:bg-gray-50 block group">
                    <div
                        class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-2 group-active:scale-110 transition-transform">
                        <i class="fas fa-chart-bar text-purple-600 text-lg"></i>
                    </div>
                    <p class="text-sm font-medium text-gray-700">Lihat Laporan</p>
                </a>
            </div>
        </div>

        <!-- Statistik Cards -->
        <div class="mt-6">
            <h3 class="font-medium text-gray-700 mb-2">Statistik Hari Ini</h3>
            <div class="swipeable flex overflow-x-auto space-x-3 pb-2" style="scrollbar-width: none;">
                <!-- Pesanan Hari Ini -->
                <div class="stat-card bg-gradient-to-r from-blue-500 to-blue-600 rounded-2xl p-4 text-white shadow-lg flex-shrink-0"
                    style="width: 85%;">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm opacity-90">Pesanan Hari Ini</p>
                            <p class="text-2xl font-bold mt-1" id="todayOrders">0</p>
                            <p class="text-xs opacity-80 mt-1" id="todayComparison">+0 dari kemarin</p>
                        </div>
                        <div class="w-12 h-12 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                            <i class="fas fa-box text-xl"></i>
                        </div>
                    </div>
                </div>

                <!-- Sedang Diproses -->
                <div class="stat-card bg-gradient-to-r from-orange-500 to-orange-600 rounded-2xl p-4 text-white shadow-lg flex-shrink-0"
                    style="width: 85%;">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm opacity-90">Sedang Diproses</p>
                            <p class="text-2xl font-bold mt-1" id="processingOrders">0</p>
                            <p class="text-xs opacity-80 mt-1" id="processingBreakdown">0 cuci, 0 setrika</p>
                        </div>
                        <div class="w-12 h-12 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                            <i class="fas fa-spinner text-xl"></i>
                        </div>
                    </div>
                </div>

                <!-- Pendapatan Harian -->
                <div class="stat-card bg-gradient-to-r from-purple-500 to-purple-600 rounded-2xl p-4 text-white shadow-lg flex-shrink-0"
                    style="width: 85%;">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm opacity-90">Pendapatan Harian</p>
                            <p class="text-2xl font-bold mt-1" id="todayRevenue">Rp 0</p>
                            <p class="text-xs opacity-80 mt-1" id="avgOrder">Rata-rata Rp 0/order</p>
                        </div>
                        <div class="w-12 h-12 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                            <i class="fas fa-coins text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Orders -->
        <div class="mt-6">
            <div class="flex justify-between items-center mb-3">
                <h3 class="font-medium text-gray-700">Pesanan Terbaru</h3>
                <a href="/tracking" class="text-blue-500 text-sm">Lihat Semua</a>
            </div>
            <div id="recentOrders" class="space-y-2">
                <!-- Orders will be loaded dynamically -->
            </div>
            <div id="recentOrdersLoading" class="text-center py-4">
                <div class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-2">
                    <i class="fas fa-spinner fa-spin text-gray-400"></i>
                </div>
                <p class="text-gray-500 text-sm">Memuat pesanan...</p>
            </div>
        </div>
    </div>

    <!-- Transaction Wizard Modals -->
    @include('partials.transaction-customer-modal')
    @include('partials.transaction-service-modal')
    @include('partials.transaction-kiloan-modal')
    {{-- @include('partials.transaction-satuan-modal') --}}
    @include('partials.transaction-satuan-items-modal')
    @include('partials.transaction-payment-modal')
    @include('partials.transaction-review-modal')
    @include('partials.transaction-success-modal')

    <!-- Include Customer Modal -->
    @include('partials.customer-add-modal')
@endsection

@push('styles')
    <style>
        .step-indicator {
            transition: all 0.3s ease;
        }

        .step-active {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white;
        }

        .step-completed {
            background: #10b981;
            color: white;
        }

        .service-card {
            transition: all 0.2s ease;
        }

        .service-card:active {
            transform: scale(0.98);
        }

        .quantity-btn {
            min-width: 36px;
            min-height: 36px;
        }
    </style>
@endpush

@push('scripts')
    <script>
        {!! file_get_contents(resource_path('js/dashboard.js')) !!}
    </script>
    <script>
        {!! file_get_contents(resource_path('js/newTransaction.js')) !!}
    </script>
@endpush