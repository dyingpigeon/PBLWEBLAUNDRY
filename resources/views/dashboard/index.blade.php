@extends('layouts.mobile')

@section('title', 'Dashboard')

@section('content')
    <!-- Welcome & Date -->
    <div class="px-4 pt-4">
        <h2 class="text-lg font-semibold text-gray-700">Selamat Pagi, Admin! 👋</h2>
        <p class="text-sm text-gray-500">Senin, 15 Jan 2024</p>
    </div>

    <!-- Statistik Cards -->
    @include('components.stat-cards')
    
    <!-- Quick Actions -->
    @include('components.quick-actions')
    
    <!-- Mini Chart -->
    @include('components.mini-chart')
    
    <!-- Recent Orders -->
    @include('components.recent-orders')
@endsection

@push('scripts')
<script>
    // Simple swipe to refresh simulation
    let startY;
    const ordersList = document.getElementById('orders-list');
    
    ordersList.addEventListener('touchstart', e => {
        startY = e.touches[0].clientY;
    });

    ordersList.addEventListener('touchmove', e => {
        if (!startY) return;
        
        const currentY = e.touches[0].clientY;
        const diff = startY - currentY;
        
        if (diff < -50) { // Pull down to refresh
            document.querySelector('.refresh-indicator').classList.add('refreshing');
            
            // Simulate API call
            setTimeout(() => {
                document.querySelector('.refresh-indicator').classList.remove('refreshing');
                // Here you would update the orders list
            }, 1000);
        }
    });

    // Card swipe functionality
    const statCards = document.querySelector('.swipeable');
    let isScrolling;
    
    statCards.addEventListener('scroll', () => {
        window.clearTimeout(isScrolling);
        isScrolling = setTimeout(() => {
            // Snap to nearest card
            const scrollLeft = statCards.scrollLeft;
            const cardWidth = statCards.children[0].offsetWidth + 12; // width + gap
            const index = Math.round(scrollLeft / cardWidth);
            statCards.scrollTo({
                left: index * cardWidth,
                behavior: 'smooth'
            });
        }, 100);
    });
</script>
@endpush