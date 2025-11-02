@extends('layouts.mobile')

@section('title', 'Notifikasi')

@section('content')
<div class="pb-4">
    <!-- Header dengan Badge Counter -->
    <div class="bg-white px-4 py-3 border-b border-gray-200 sticky top-0 z-10">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-800">Notifikasi</h2>
                <p class="text-sm text-gray-500" id="notificationCount">3 notifikasi baru</p>
            </div>
            <button onclick="markAllAsRead()" class="text-blue-500 text-sm font-medium">
                Tandai semua dibaca
            </button>
        </div>
    </div>

    <!-- Notifications List -->
    <div id="notificationsList" class="divide-y divide-gray-100">
        <!-- Notifications will be loaded here -->
    </div>

    <!-- Empty State -->
    <div id="emptyState" class="hidden px-4 py-8 text-center">
        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
            <i class="fas fa-bell-slash text-gray-400 text-xl"></i>
        </div>
        <h3 class="text-lg font-semibold text-gray-800 mb-2">Tidak ada notifikasi</h3>
        <p class="text-gray-500">Notifikasi baru akan muncul di sini</p>
    </div>

    <!-- Loading Indicator -->
    <div id="loadingIndicator" class="hidden py-6 text-center">
        <div class="inline-flex items-center space-x-2 text-gray-500">
            <i class="fas fa-spinner fa-spin"></i>
            <span>Memuat notifikasi...</span>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.notification-item {
    transition: all 0.3s ease;
    touch-action: pan-y;
}
.notification-item.swiped {
    transform: translateX(-80px);
}
.swipe-actions {
    opacity: 0;
    transition: opacity 0.2s ease;
}
.notification-item.swiped .swipe-actions {
    opacity: 1;
}
.pull-indicator {
    transition: transform 0.3s ease;
}
.pulling {
    transform: rotate(180deg);
}
.badge-pulse {
    animation: pulse 2s infinite;
}
@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}
</style>
@endpush

@push('scripts')
    <script>
        {!! file_get_contents(resource_path('js/notificationsPage.js')) !!}
    </script>
@endpush