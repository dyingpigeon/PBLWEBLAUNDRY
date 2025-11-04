@extends('layouts.mobile')

@section('title', 'Tracking Pesanan')

@section('content')
    <div class="pb-4">
        <!-- Status Tabs -->
        <div class="bg-white border-b border-gray-200 sticky top-0 z-10">
            <div class="swipeable-tabs flex overflow-x-auto px-4" style="scrollbar-width: none;">
                <a href="{{ route('tracking.index', ['status' => 'all']) }}"
                    class="status-tab flex-shrink-0 px-4 py-3 font-medium border-b-2 transition-all duration-200 {{ request('status', 'all') == 'all' ? 'text-blue-600 border-blue-600' : 'text-gray-500 border-transparent' }}">
                    Semua
                </a>
                <a href="{{ route('tracking.index', ['status' => 'new']) }}"
                    class="status-tab flex-shrink-0 px-4 py-3 font-medium border-b-2 transition-all duration-200 {{ request('status') == 'new' ? 'text-blue-600 border-blue-600' : 'text-gray-500 border-transparent' }}">
                    Baru
                </a>
                <a href="{{ route('tracking.index', ['status' => 'process']) }}"
                    class="status-tab flex-shrink-0 px-4 py-3 font-medium border-b-2 transition-all duration-200 {{ request('status') == 'process' ? 'text-blue-600 border-blue-600' : 'text-gray-500 border-transparent' }}">
                    Proses
                </a>
                <a href="{{ route('tracking.index', ['status' => 'ready']) }}"
                    class="status-tab flex-shrink-0 px-4 py-3 font-medium border-b-2 transition-all duration-200 {{ request('status') == 'ready' ? 'text-blue-600 border-blue-600' : 'text-gray-500 border-transparent' }}">
                    Selesai
                </a>
                <a href="{{ route('tracking.index', ['status' => 'done']) }}"
                    class="status-tab flex-shrink-0 px-4 py-3 font-medium border-b-2 transition-all duration-200 {{ request('status') == 'done' ? 'text-blue-600 border-blue-600' : 'text-gray-500 border-transparent' }}">
                    Diambil
                </a>
                <a href="{{ route('tracking.index', ['status' => 'cancelled']) }}"
                    class="status-tab flex-shrink-0 px-4 py-3 font-medium border-b-2 transition-all duration-200 {{ request('status') == 'cancelled' ? 'text-blue-600 border-blue-600' : 'text-gray-500 border-transparent' }}">
                    Dibatalkan
                </a>
            </div>
        </div>

        <!-- Search Box -->
        <form method="GET" action="{{ route('tracking.index') }}" class="px-4 py-3 bg-white border-b border-gray-200">
            <div class="relative">
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Cari no. transaksi atau nama pelanggan..."
                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                @if(request('search'))
                    <a href="{{ route('tracking.index', ['status' => request('status', 'all')]) }}"
                        class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </a>
                @endif
            </div>
        </form>

        <!-- Statistics Cards -->
        <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
            <div class="grid grid-cols-3 gap-2 text-center">
                <div class="bg-white rounded-lg p-3 shadow-sm">
                    <p class="text-2xl font-bold text-blue-600">{{ $stats['total'] }}</p>
                    <p class="text-xs text-gray-500">Total</p>
                </div>
                <div class="bg-white rounded-lg p-3 shadow-sm">
                    <p class="text-2xl font-bold text-orange-600">{{ $stats['processing'] }}</p>
                    <p class="text-xs text-gray-500">Proses</p>
                </div>
                <div class="bg-white rounded-lg p-3 shadow-sm">
                    <p class="text-2xl font-bold text-green-600">{{ $stats['ready'] + $stats['done'] }}</p>
                    <p class="text-xs text-gray-500">Selesai</p>
                </div>
            </div>
        </div>

        <!-- Orders List -->
        <div class="space-y-3 px-4 mt-3" id="ordersList">
            @forelse($transactions as $transaction)
                <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 transaction-item"
                    data-transaction-id="{{ $transaction->id }}">
                    <!-- Order Header -->
                    <div class="flex justify-between items-start mb-3">
                        <div>
                            <h3 class="font-semibold text-gray-800">{{ $transaction->customer_name ?? 'N/A' }}</h3>
                            <p class="text-sm text-gray-500">{{ $transaction->transaction_number }}</p>
                            <p class="text-xs text-gray-400">
                                {{ $transaction->order_date_formatted ?? $transaction->created_at_formatted }}
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="font-semibold text-gray-800">Rp
                                {{ number_format($transaction->total_amount, 0, ',', '.') }}
                            </p>
                            <div class="flex space-x-1 mt-1">
                                <span
                                    class="inline-block px-2 py-1 {{ getStatusBadgeClass($transaction->status) }} text-xs rounded-full">
                                    {{ getStatusText($transaction->status) }}
                                </span>
                                <span
                                    class="inline-block px-2 py-1 {{ getPaymentStatusBadgeClass($transaction->payment_status) }} text-xs rounded-full">
                                    {{ getPaymentStatusText($transaction->payment_status) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Service & Order Type Info -->
                    <div class="flex items-center justify-between text-sm text-gray-600 mb-2">
                        <div class="flex items-center space-x-2">
                            <span>{{ $transaction->service_name ?? 'N/A' }}</span>
                            <span class="inline-block px-2 py-1 bg-blue-50 text-blue-600 text-xs rounded-full">
                                {{ $transaction->order_type == 'kiloan' ? 'Kiloan' : 'Satuan' }}
                            </span>
                        </div>
                        <div class="flex items-center space-x-2">
                            @if($transaction->order_type == 'kiloan' && $transaction->weight)
                                <span>{{ $transaction->weight }} kg</span>
                            @endif
                            <span>{{ $transaction->total_items ?? 0 }} item</span>
                        </div>
                    </div>

                    <!-- Payment Info -->
                    <div class="flex items-center justify-between text-xs text-gray-500 mb-3">
                        <div class="flex items-center space-x-2">
                            <span
                                class="inline-block px-2 py-1 {{ getPaymentTypeBadgeClass($transaction->payment_type) }} rounded-full">
                                {{ getPaymentTypeText($transaction->payment_type) }}
                            </span>
                            @if($transaction->payment_method)
                                <span class="inline-block px-2 py-1 bg-gray-100 text-gray-600 rounded-full">
                                    {{ getPaymentMethodText($transaction->payment_method) }}
                                </span>
                            @endif
                        </div>
                        <div class="text-right">
                            <span class="{{ $transaction->payment_status == 'paid' ? 'text-green-600' : 'text-orange-600' }}">
                                Bayar: Rp {{ number_format($transaction->paid_amount, 0, ',', '.') }}
                            </span>
                        </div>
                    </div>

                    <!-- Timeline Info -->
                    <div class="text-xs text-gray-500">
                        <i class="fas fa-clock mr-1"></i>
                        {{ getCurrentStepText($transaction->status) }}
                        @if($transaction->estimated_completion_formatted)
                            • Estimasi: {{ $transaction->estimated_completion_formatted }}
                        @endif
                    </div>

                    <!-- Notes -->
                    @if($transaction->notes)
                        <div class="mt-2 text-xs text-gray-500 bg-yellow-50 p-2 rounded">
                            <i class="fas fa-sticky-note mr-1"></i>{{ $transaction->notes }}
                        </div>
                    @endif
                </div>
            @empty
                <div class="text-center py-8 px-4">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-box-open text-gray-400 text-xl"></i>
                    </div>
                    <p class="text-gray-500 font-medium">Tidak ada pesanan</p>
                    <p class="text-gray-400 text-sm mt-1">Belum ada pesanan dengan status ini</p>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($pagination['last_page'] > 1)
            <div class="px-4 py-4">
                <div class="flex justify-between items-center">
                    @if($pagination['current_page'] == 1)
                        <span class="text-gray-400">Sebelumnya</span>
                    @else
                        <a href="{{ $pagination['prev_page_url'] }}" class="text-blue-500 font-semibold">Sebelumnya</a>
                    @endif

                    <span class="text-sm text-gray-500">
                        Halaman {{ $pagination['current_page'] }} dari {{ $pagination['last_page'] }}
                    </span>

                    @if($pagination['current_page'] < $pagination['last_page'])
                        <a href="{{ $pagination['next_page_url'] }}" class="text-blue-500 font-semibold">Selanjutnya</a>
                    @else
                        <span class="text-gray-400">Selanjutnya</span>
                    @endif
                </div>
            </div>
        @endif

        <!-- Status Update Modal -->
        @include('partials.tracking-status-modal')
@endsection

    @push('scripts')
        <script src="{{ asset('js/trackingPage.js') }}"></script>
    @endpush

    <?php
// Helper functions untuk handle data dari query langsung
function getStatusBadgeClass($status)
{
    $statusMap = [
        'new' => 'bg-blue-100 text-blue-600',
        'process' => 'bg-orange-100 text-orange-600',
        'ready' => 'bg-green-100 text-green-600',
        'done' => 'bg-gray-100 text-gray-600',
        'cancelled' => 'bg-red-100 text-red-600'
    ];
    return $statusMap[$status] ?? 'bg-gray-100 text-gray-600';
}

function getPaymentStatusBadgeClass($paymentStatus)
{
    $paymentMap = [
        'pending' => 'bg-yellow-100 text-yellow-600',
        'paid' => 'bg-green-100 text-green-600',
        'partial' => 'bg-blue-100 text-blue-600'
    ];
    return $paymentMap[$paymentStatus] ?? 'bg-gray-100 text-gray-600';
}

function getPaymentTypeBadgeClass($paymentType)
{
    $paymentTypeMap = [
        'now' => 'bg-green-100 text-green-600',
        'later' => 'bg-orange-100 text-orange-600'
    ];
    return $paymentTypeMap[$paymentType] ?? 'bg-gray-100 text-gray-600';
}

function getStatusText($status)
{
    $statusMap = [
        'new' => 'Baru',
        'process' => 'Proses',
        'ready' => 'Selesai',
        'done' => 'Diambil',
        'cancelled' => 'Dibatalkan'
    ];
    return $statusMap[$status] ?? 'Unknown';
}

function getPaymentStatusText($paymentStatus)
{
    $paymentMap = [
        'pending' => 'Belum Bayar',
        'paid' => 'Lunas',
        'partial' => 'DP'
    ];
    return $paymentMap[$paymentStatus] ?? 'Unknown';
}

function getPaymentTypeText($paymentType)
{
    $paymentTypeMap = [
        'now' => 'Bayar Sekarang',
        'later' => 'Bayar Nanti'
    ];
    return $paymentTypeMap[$paymentType] ?? 'Unknown';
}

function getPaymentMethodText($paymentMethod)
{
    $paymentMethodMap = [
        'cash' => 'Tunai',
        'transfer' => 'Transfer',
        'qris' => 'QRIS'
    ];
    return $paymentMethodMap[$paymentMethod] ?? 'Unknown';
}

function getCurrentStepText($status)
{
    $statusText = [
        'new' => 'Menunggu diproses',
        'process' => 'Sedang diproses',
        'ready' => 'Siap diambil',
        'done' => 'Sudah diambil',
        'cancelled' => 'Pesanan dibatalkan'
    ];
    return $statusText[$status] ?? 'Sedang diproses';
}
?>