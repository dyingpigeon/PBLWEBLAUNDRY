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
                <a href="{{ route('tracking.index', ['status' => 'washing']) }}"
                    class="status-tab flex-shrink-0 px-4 py-3 font-medium border-b-2 transition-all duration-200 {{ request('status') == 'washing' ? 'text-blue-600 border-blue-600' : 'text-gray-500 border-transparent' }}">
                    Dicuci
                </a>
                <a href="{{ route('tracking.index', ['status' => 'ironing']) }}"
                    class="status-tab flex-shrink-0 px-4 py-3 font-medium border-b-2 transition-all duration-200 {{ request('status') == 'ironing' ? 'text-blue-600 border-blue-600' : 'text-gray-500 border-transparent' }}">
                    Disetrika
                </a>
                <a href="{{ route('tracking.index', ['status' => 'ready']) }}"
                    class="status-tab flex-shrink-0 px-4 py-3 font-medium border-b-2 transition-all duration-200 {{ request('status') == 'ready' ? 'text-blue-600 border-blue-600' : 'text-gray-500 border-transparent' }}">
                    Selesai
                </a>
                <a href="{{ route('tracking.index', ['status' => 'picked_up']) }}"
                    class="status-tab flex-shrink-0 px-4 py-3 font-medium border-b-2 transition-all duration-200 {{ request('status') == 'picked_up' ? 'text-blue-600 border-blue-600' : 'text-gray-500 border-transparent' }}">
                    Diambil
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
                    <p class="text-2xl font-bold text-green-600">{{ $stats['ready'] + $stats['picked_up'] }}</p>
                    <p class="text-xs text-gray-500">Selesai</p>
                </div>
            </div>
        </div>

        <!-- Orders List -->
        <div class="space-y-3 px-4 mt-3">
            @forelse($transactions as $transaction)
                <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100"
                    onclick="showStatusModal({{ $transaction->id }})">
                    <!-- Order Header -->
                    <div class="flex justify-between items-start mb-3">
                        <div>
                            <h3 class="font-semibold text-gray-800">{{ $transaction->customer_name ?? 'N/A' }}</h3>
                            <p class="text-sm text-gray-500">{{ $transaction->transaction_number }}</p>
                            <p class="text-xs text-gray-400">
                                @if(property_exists($transaction, 'created_at'))
                                    {{ \Carbon\Carbon::parse($transaction->created_at)->format('d/m/Y H:i') }}
                                @else
                                    {{ date('d/m/Y H:i') }}
                                @endif
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="font-semibold text-gray-800">Rp
                                {{ number_format($transaction->total_amount, 0, ',', '.') }}</p>
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

                    <!-- Service Info -->
                    <div class="flex items-center justify-between text-sm text-gray-600 mb-4">
                        <span>{{ $transaction->service_name ?? 'N/A' }}</span>
                        <span>{{ $transaction->total_items ?? 0 }} item</span>
                    </div>

                    <!-- Timeline Info -->
                    <div class="text-xs text-gray-500">
                        <i class="fas fa-clock mr-1"></i>
                        {{ getCurrentStepText($transaction->status) }}
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
        @if(isset($transactions->hasPages) && $transactions->hasPages())
            <div class="px-4 py-4">
                <div class="flex justify-between items-center">
                    @if($transactions->onFirstPage())
                        <span class="text-gray-400">Sebelumnya</span>
                    @else
                        <a href="{{ $transactions->previousPageUrl() }}&status={{ request('status', 'all') }}&search={{ request('search') }}"
                            class="text-blue-500 font-semibold">Sebelumnya</a>
                    @endif

                    <span class="text-sm text-gray-500">
                        Halaman {{ $transactions->currentPage() }} dari {{ $transactions->lastPage() }}
                    </span>

                    @if($transactions->hasMorePages())
                        <a href="{{ $transactions->nextPageUrl() }}&status={{ request('status', 'all') }}&search={{ request('search') }}"
                            class="text-blue-500 font-semibold">Selanjutnya</a>
                    @else
                        <span class="text-gray-400">Selanjutnya</span>
                    @endif
                </div>
            </div>
        @endif
    </div>

    <!-- Status Update Modal -->
    @include('partials.tracking-status-modal')
@endsection

@push('scripts')
    <script>
        // Fungsi JavaScript untuk modal
        async function showStatusModal(orderId) {
            try {
                // Fetch transaction detail dari API
                const response = await fetch(`/tracking/${orderId}`);
                const data = await response.json();

                if (data.success) {
                    const transaction = data.data;
                    
                    // Set modal content dengan data real
                    document.getElementById('statusOrderId').value = transaction.id;
                    document.getElementById('statusOrderCode').textContent = transaction.transaction_number;
                    document.getElementById('statusCustomerName').textContent = transaction.customer_name || 'N/A';
                    document.getElementById('statusServiceName').textContent = transaction.service_name || 'N/A';
                    document.getElementById('statusTotalAmount').textContent = `Rp ${formatPrice(transaction.total_amount)}`;
                    
                    // Update status options
                    updateStatusOptions(transaction.status);
                    
                    // Show modal
                    document.getElementById('statusModal').classList.remove('hidden');
                }
            } catch (error) {
                console.error('Error loading transaction detail:', error);
                alert('Gagal memuat detail transaksi');
            }
        }

        function closeStatusModal() {
            document.getElementById('statusModal').classList.add('hidden');
        }

        function updateStatusOptions(currentStatus) {
            const statusOptions = document.getElementById('statusOptions');
            const statusOrder = ['new', 'washing', 'ironing', 'ready', 'picked_up'];
            const currentIndex = statusOrder.indexOf(currentStatus);
            
            statusOptions.innerHTML = statusOrder.map((status, index) => {
                const statusInfo = getStatusInfo(status);
                const isCompleted = index < currentIndex;
                const isCurrent = index === currentIndex;
                const isDisabled = index > currentIndex + 1;
                
                return `
                    <button 
                        class="status-option w-full text-left p-3 rounded-lg mb-2 transition-all duration-200 ${isCompleted ? 'bg-green-50 text-green-700' : isCurrent ? 'bg-blue-50 text-blue-700' : 'bg-gray-50 text-gray-500'} ${isDisabled ? 'opacity-50 cursor-not-allowed' : 'hover:bg-blue-100'}" 
                        data-status="${status}"
                        ${isDisabled ? 'disabled' : ''}
                        onclick="updateOrderStatus('${status}')"
                    >
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="w-8 h-8 rounded-full ${isCompleted ? 'bg-green-500' : isCurrent ? 'bg-blue-500' : 'bg-gray-300'} flex items-center justify-center mr-3">
                                    ${isCompleted ? '<i class="fas fa-check text-white text-sm"></i>' : ''}
                                </div>
                                <span>${statusInfo.text}</span>
                            </div>
                            ${isCurrent ? '<i class="fas fa-check-circle text-blue-500"></i>' : ''}
                        </div>
                    </button>
                `;
            }).join('');
        }

        async function updateOrderStatus(newStatus) {
            const orderId = document.getElementById('statusOrderId').value;
            
            try {
                const response = await fetch(`/tracking/${orderId}/status`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ status: newStatus })
                });

                const data = await response.json();

                if (data.success) {
                    alert('Status berhasil diupdate!');
                    closeStatusModal();
                    location.reload(); // Reload page untuk update data
                } else {
                    alert('Gagal mengupdate status: ' + (data.message || 'Unknown error'));
                }
            } catch (error) {
                console.error('Error updating status:', error);
                alert('Terjadi kesalahan saat mengupdate status');
            }
        }

        // Helper functions
        function getStatusInfo(status) {
            const statusMap = {
                'new': { text: 'Baru' },
                'washing': { text: 'Dicuci' },
                'ironing': { text: 'Disetrika' },
                'ready': { text: 'Selesai' },
                'picked_up': { text: 'Diambil' }
            };
            return statusMap[status] || { text: 'Unknown' };
        }

        function formatPrice(price) {
            return parseFloat(price).toLocaleString('id-ID');
        }
    </script>
@endpush

<?php
// Helper functions untuk handle data dari query langsung
function getStatusBadgeClass($status) {
    $statusMap = [
        'new' => 'bg-blue-100 text-blue-600',
        'washing' => 'bg-orange-100 text-orange-600',
        'ironing' => 'bg-purple-100 text-purple-600',
        'ready' => 'bg-green-100 text-green-600',
        'picked_up' => 'bg-gray-100 text-gray-600',
        'cancelled' => 'bg-red-100 text-red-600'
    ];
    return $statusMap[$status] ?? 'bg-gray-100 text-gray-600';
}

function getPaymentStatusBadgeClass($paymentStatus) {
    $paymentMap = [
        'pending' => 'bg-yellow-100 text-yellow-600',
        'paid' => 'bg-green-100 text-green-600',
        'partial' => 'bg-blue-100 text-blue-600',
        'overpaid' => 'bg-purple-100 text-purple-600'
    ];
    return $paymentMap[$paymentStatus] ?? 'bg-gray-100 text-gray-600';
}

function getStatusText($status) {
    $statusMap = [
        'new' => 'Baru',
        'washing' => 'Dicuci',
        'ironing' => 'Disetrika',
        'ready' => 'Selesai',
        'picked_up' => 'Diambil',
        'cancelled' => 'Dibatalkan'
    ];
    return $statusMap[$status] ?? 'Unknown';
}

function getPaymentStatusText($paymentStatus) {
    $paymentMap = [
        'pending' => 'Belum Bayar',
        'paid' => 'Lunas',
        'partial' => 'DP',
        'overpaid' => 'Kelebihan'
    ];
    return $paymentMap[$paymentStatus] ?? 'Unknown';
}

function getCurrentStepText($status) {
    $statusText = [
        'new' => 'Menunggu diproses',
        'washing' => 'Sedang dicuci',
        'ironing' => 'Sedang disetrika',
        'ready' => 'Siap diambil',
        'picked_up' => 'Sudah diambil'
    ];
    return $statusText[$status] ?? 'Sedang diproses';
}
?>