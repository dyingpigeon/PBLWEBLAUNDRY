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
let notifications = [];
let isRefreshing = false;
let startY;

// Sample notifications data
const sampleNotifications = [
    {
        id: 1,
        type: 'new_order',
        title: 'Pesanan Baru',
        message: 'Budi Santoso membuat pesanan cuci setrika 2 kg',
        time: '2 menit lalu',
        read: false,
        data: { order_id: 12, customer_name: 'Budi Santoso' }
    },
    {
        id: 2,
        type: 'status_update',
        title: 'Status Berubah',
        message: 'Pesanan #LAUNDRY-0011 sudah selesai dicuci',
        time: '1 jam lalu',
        read: false,
        data: { order_id: 11, status: 'washing_completed' }
    },
    {
        id: 3,
        type: 'reminder',
        title: 'Pengingat',
        message: 'Pesanan #LAUNDRY-0010 sudah siap diambil',
        time: '3 jam lalu',
        read: true,
        data: { order_id: 10, status: 'ready_for_pickup' }
    },
    {
        id: 4,
        type: 'new_order',
        title: 'Pesanan Baru',
        message: 'Siti Rahayu membuat pesanan setrika saja 3 kg',
        time: '5 jam lalu',
        read: true,
        data: { order_id: 9, customer_name: 'Siti Rahayu' }
    },
    {
        id: 5,
        type: 'system',
        title: 'System Update',
        message: 'Backup data otomatis berhasil dilakukan',
        time: '1 hari lalu',
        read: true,
        data: { type: 'backup_completed' }
    }
];

document.addEventListener('DOMContentLoaded', function() {
    loadNotifications();
    setupPullToRefresh();
    updateBadgeCounter();
});

// Load notifications
function loadNotifications() {
    notifications = sampleNotifications;
    renderNotifications();
}

// Render notifications to the list
function renderNotifications() {
    const container = document.getElementById('notificationsList');
    const emptyState = document.getElementById('emptyState');
    
    if (notifications.length === 0) {
        container.innerHTML = '';
        emptyState.classList.remove('hidden');
        return;
    }
    
    emptyState.classList.add('hidden');
    
    const unreadNotifications = notifications.filter(n => !n.read);
    document.getElementById('notificationCount').textContent = 
        `${unreadNotifications.length} notifikasi ${unreadNotifications.length === 1 ? 'baru' : 'baru'}`;
    
    container.innerHTML = notifications.map(notification => 
        createNotificationElement(notification)
    ).join('');
}

// Create notification element
function createNotificationElement(notification) {
    const icon = getNotificationIcon(notification.type);
    const bgColor = notification.read ? 'bg-white' : 'bg-blue-50';
    
    return `
        <div class="notification-item ${bgColor} relative overflow-hidden" data-id="${notification.id}">
            <div class="p-4 flex items-start space-x-3">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 ${icon.bg} rounded-full flex items-center justify-center">
                        <i class="${icon.icon} ${icon.color}"></i>
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-800 ${notification.read ? '' : 'font-bold'}">
                                ${notification.title}
                            </h3>
                            <p class="text-sm text-gray-600 mt-1">${notification.message}</p>
                            <p class="text-xs text-gray-400 mt-2">${notification.time}</p>
                        </div>
                        ${!notification.read ? `
                            <div class="badge-pulse w-2 h-2 bg-blue-500 rounded-full ml-2 flex-shrink-0"></div>
                        ` : ''}
                    </div>
                </div>
            </div>
            
            <!-- Swipe Actions -->
            <div class="swipe-actions absolute right-0 top-0 bottom-0 flex items-center space-x-1 pr-4">
                <button class="mark-read-btn w-10 h-10 bg-blue-500 text-white rounded-full flex items-center justify-center shadow-lg"
                        onclick="markAsRead(${notification.id})">
                    <i class="fas fa-check text-sm"></i>
                </button>
                <button class="delete-btn w-10 h-10 bg-red-500 text-white rounded-full flex items-center justify-center shadow-lg"
                        onclick="deleteNotification(${notification.id})">
                    <i class="fas fa-trash text-sm"></i>
                </button>
            </div>
        </div>
    `;
}

// Get notification icon based on type
function getNotificationIcon(type) {
    const icons = {
        'new_order': { icon: 'fas fa-shopping-bag', bg: 'bg-green-100', color: 'text-green-600' },
        'status_update': { icon: 'fas fa-sync-alt', bg: 'bg-blue-100', color: 'text-blue-600' },
        'reminder': { icon: 'fas fa-clock', bg: 'bg-orange-100', color: 'text-orange-600' },
        'system': { icon: 'fas fa-cog', bg: 'bg-purple-100', color: 'text-purple-600' }
    };
    return icons[type] || { icon: 'fas fa-bell', bg: 'bg-gray-100', color: 'text-gray-600' };
}

// Setup pull to refresh
function setupPullToRefresh() {
    const container = document.getElementById('notificationsList');
    let pullStartY;

    container.addEventListener('touchstart', e => {
        if (window.pageYOffset === 0) {
            pullStartY = e.touches[0].pageY;
        }
    });

    container.addEventListener('touchmove', e => {
        if (!pullStartY || isRefreshing) return;
        
        const pullDistance = e.touches[0].pageY - pullStartY;
        
        if (pullDistance > 50) {
            // Show pull indicator
            document.querySelector('.pull-indicator')?.classList.add('pulling');
        }
    });

    container.addEventListener('touchend', e => {
        if (!pullStartY || isRefreshing) return;
        
        const pullDistance = e.changedTouches[0].pageY - pullStartY;
        
        if (pullDistance > 100) {
            refreshNotifications();
        }
        
        pullStartY = null;
        document.querySelector('.pull-indicator')?.classList.remove('pulling');
    });
}

// Refresh notifications
function refreshNotifications() {
    if (isRefreshing) return;
    
    isRefreshing = true;
    const loadingIndicator = document.getElementById('loadingIndicator');
    loadingIndicator.classList.remove('hidden');
    
    // Simulate API call
    setTimeout(() => {
        // Add new sample notification
        const newNotification = {
            id: Date.now(),
            type: 'new_order',
            title: 'Pesanan Baru',
            message: 'Customer baru membuat pesanan',
            time: 'Baru saja',
            read: false,
            data: { order_id: Date.now(), customer_name: 'Customer Baru' }
        };
        
        notifications.unshift(newNotification);
        renderNotifications();
        
        loadingIndicator.classList.add('hidden');
        isRefreshing = false;
        
        // Show refresh success feedback
        showToast('Notifikasi diperbarui');
    }, 1500);
}

// Mark notification as read
function markAsRead(notificationId) {
    const notification = notifications.find(n => n.id === notificationId);
    if (notification) {
        notification.read = true;
        renderNotifications();
        updateBadgeCounter();
        showToast('Notifikasi ditandai sudah dibaca');
    }
}

// Mark all as read
function markAllAsRead() {
    notifications.forEach(notification => {
        notification.read = true;
    });
    renderNotifications();
    updateBadgeCounter();
    showToast('Semua notifikasi ditandai sudah dibaca');
}

// Delete notification
function deleteNotification(notificationId) {
    notifications = notifications.filter(n => n.id !== notificationId);
    renderNotifications();
    updateBadgeCounter();
    showToast('Notifikasi dihapus');
}

// Update badge counter in header
function updateBadgeCounter() {
    const unreadCount = notifications.filter(n => !n.read).length;
    const badgeElement = document.querySelector('header .bg-red-500');
    
    if (badgeElement) {
        if (unreadCount > 0) {
            badgeElement.textContent = unreadCount > 9 ? '9+' : unreadCount;
            badgeElement.classList.remove('w-2', 'h-2');
            badgeElement.classList.add('w-5', 'h-5', 'text-xs', 'flex', 'items-center', 'justify-center');
        } else {
            badgeElement.classList.add('w-2', 'h-2');
            badgeElement.classList.remove('w-5', 'h-5', 'text-xs', 'flex', 'items-center', 'justify-center');
            badgeElement.textContent = '';
        }
    }
}

// Setup swipe to dismiss
function setupSwipeToDismiss() {
    document.addEventListener('touchstart', handleTouchStart, { passive: false });
    document.addEventListener('touchmove', handleTouchMove, { passive: false });
    document.addEventListener('touchend', handleTouchEnd, { passive: false });
}

let xDown = null;
let yDown = null;
let currentSwipeElement = null;

function handleTouchStart(evt) {
    const firstTouch = evt.touches[0];
    xDown = firstTouch.clientX;
    yDown = firstTouch.clientY;
    
    // Find the notification item
    currentSwipeElement = evt.target.closest('.notification-item');
}

function handleTouchMove(evt) {
    if (!xDown || !yDown || !currentSwipeElement) return;
    
    const xUp = evt.touches[0].clientX;
    const yUp = evt.touches[0].clientY;
    
    const xDiff = xDown - xUp;
    const yDiff = yDown - yUp;
    
    // Check if it's a horizontal swipe
    if (Math.abs(xDiff) > Math.abs(yDiff)) {
        evt.preventDefault(); // Prevent vertical scroll during horizontal swipe
        
        if (xDiff > 0) {
            // Swipe left - show actions
            currentSwipeElement.style.transform = `translateX(-${Math.min(xDiff, 80)}px)`;
            if (xDiff > 30) {
                currentSwipeElement.classList.add('swiped');
            }
        } else {
            // Swipe right - hide actions
            currentSwipeElement.style.transform = `translateX(${Math.min(-xDiff, 0)}px)`;
            if (-xDiff > 30) {
                currentSwipeElement.classList.remove('swiped');
            }
        }
    }
}

function handleTouchEnd() {
    if (currentSwipeElement) {
        currentSwipeElement.style.transform = '';
        
        // Add a small delay before removing swiped class for smooth animation
        setTimeout(() => {
            if (currentSwipeElement.classList.contains('swiped')) {
                currentSwipeElement.style.transform = 'translateX(-80px)';
            }
        }, 10);
    }
    
    xDown = null;
    yDown = null;
}

// Toast notification
function showToast(message) {
    const toast = document.createElement('div');
    toast.className = 'fixed top-4 left-4 right-4 p-3 bg-green-500 text-white rounded-lg shadow-lg text-center font-medium z-50 transform transition-transform duration-300';
    toast.textContent = message;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.remove();
    }, 3000);
}

// Initialize swipe to dismiss after DOM is loaded
setTimeout(() => {
    setupSwipeToDismiss();
}, 100);
</script>
@endpush