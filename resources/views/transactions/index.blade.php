@extends('layouts.mobile')

@section('title', 'Transaksi')

@section('content')
    <div class="pb-4">
        <!-- Header Stats -->
        <div class="bg-white px-4 py-3 border-b border-gray-200">
            <div class="grid grid-cols-3 gap-4 text-center">
                <div>
                    <p class="text-2xl font-bold text-blue-600" id="todayCount">0</p>
                    <p class="text-xs text-gray-500">Hari Ini</p>
                </div>
                <div>
                    <p class="text-2xl font-bold text-orange-600" id="processingCount">0</p>
                    <p class="text-xs text-gray-500">Diproses</p>
                </div>
                <div>
                    <p class="text-2xl font-bold text-green-600" id="revenueCount">Rp 0</p>
                    <p class="text-xs text-gray-500">Pendapatan</p>
                </div>
            </div>
        </div>

        <!-- Search Bar -->
        <div class="bg-white px-4 py-3 border-b border-gray-200">
            <div class="relative">
                <input type="text" id="searchTransactions" placeholder="Cari transaksi..."
                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
            </div>
        </div>

        <!-- Transaction List -->
        <div class="space-y-2 p-4">
            <div id="transactionsList">
                <!-- Dynamic content -->
            </div>
        </div>

        <!-- Loading State -->
        <div id="loadingState" class="text-center py-8 px-4">
            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
                <i class="fas fa-spinner fa-spin text-gray-400 text-xl"></i>
            </div>
            <p class="text-gray-500">Memuat data transaksi...</p>
        </div>

        <!-- Empty State -->
        <div id="emptyState" class="hidden text-center py-8 px-4">
            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
                <i class="fas fa-exchange-alt text-gray-400 text-xl"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-700 mb-2">Belum Ada Transaksi</h3>
            <p class="text-gray-500 mb-4">Mulai buat transaksi pertama Anda</p>
            <button onclick="startNewTransaction()" class="bg-blue-500 text-white px-6 py-2 rounded-lg font-semibold">
                Buat Transaksi
            </button>
        </div>

        <!-- Load More -->
        <div id="loadMoreContainer" class="hidden px-4 py-4">
            <button id="loadMoreBtn" onclick="loadMoreTransactions()"
                class="w-full py-3 bg-white border border-gray-300 text-gray-700 rounded-xl font-semibold hover:bg-gray-50 transition-colors">
                <i class="fas fa-redo mr-2"></i>Muat Lebih Banyak
            </button>
        </div>
    </div>

    <!-- Floating Action Button -->
    <button id="fabButton"
        class="fixed bottom-20 right-4 w-14 h-14 bg-blue-500 text-white rounded-full shadow-lg flex items-center justify-center hover:bg-blue-600 active:scale-95 transition-all duration-200 z-20"
        onclick="startNewTransaction()">
        <i class="fas fa-plus text-lg"></i>
    </button>

    <!-- Transaction Wizard Modals -->
    @include('partials.transaction-customer-modal')
    @include('partials.transaction-service-modal')
    @include('partials.transaction-kiloan-modal')
    @include('partials.transaction-satuan-modal')
    @include('partials.transaction-satuan-items-modal')
    @include('partials.transaction-payment-modal')
    @include('partials.transaction-review-modal')
    @include('partials.transaction-success-modal')
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
        {!! file_get_contents(resource_path('js/transactionPage.js')) !!}
    </script>
    <script>
        {!! file_get_contents(resource_path('js/newTransaction.js')) !!}
    </script>
@endpush