@php
    // Default value untuk menghindari error
    $unreadCount = $unreadCount ?? 0;
@endphp

<header class="bg-white shadow-sm sticky top-0 z-10">
    <div class="px-4 py-3 flex items-center justify-between">
        <div>
            <h1 id="businessNameHeader" class="text-xl font-bold text-gray-800">Loading...</h1>
            <p class="text-xs text-gray-500">Admin Dashboard</p>
        </div>
        <div class="flex items-center space-x-3">
            <!-- Notifications -->
            <a href="{{ route('notifications.index') }}" 
               class="relative p-2 text-gray-600 hover:text-gray-800 transition-colors {{ request()->routeIs('notifications.*') ? 'text-blue-600' : '' }}">
                <i class="fas fa-bell text-lg"></i>
                <span id="notificationBadge" class="absolute top-0 right-0 w-2 h-2 bg-red-500 rounded-full {{ $unreadCount > 0 ? '' : 'hidden' }}"></span>
            </a>
            
            <!-- Settings -->
            <a href="{{ route('settings.index') }}" 
               class="p-2 text-gray-600 hover:text-gray-800 transition-colors {{ request()->routeIs('settings.*') ? 'text-blue-600' : '' }}">
                <i class="fas fa-cog text-lg"></i>
            </a>
            
            <!-- User Profile -->
            <a href="{{ route('profile.index') }}" 
               class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center text-white font-bold cursor-pointer hover:bg-blue-600 transition-colors {{ request()->routeIs('profile.*') ? 'ring-2 ring-blue-300' : '' }}"
               id="userInitial">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </a>
        </div>
    </div>
</header>

@push('scripts')
<script>
    // Load header data when page loads
    document.addEventListener('DOMContentLoaded', function() {
        console.log('🚀 Loading header data...');
        loadBusinessName();
        loadHeaderData();
        
        // Refresh header data every 30 seconds
        setInterval(loadHeaderData, 30000);
    });

    // Function khusus untuk load business name
    async function loadBusinessName() {
        try {
            console.log('🌐 Fetching business name from /header/business-name');
            const response = await fetch('/header/business-name');
            const data = await response.json();
            
            console.log('📊 Business name response:', data);
            
            if (data.success) {
                const businessNameElement = document.getElementById('businessNameHeader');
                if (businessNameElement && data.business_name) {
                    businessNameElement.textContent = data.business_name;
                    console.log('✅ Business name updated:', data.business_name);
                }
            } else {
                console.error('❌ Failed to load business name:', data);
                // Fallback ke default
                document.getElementById('businessNameHeader').textContent = 'LaundryKu';
            }
        } catch (error) {
            console.error('❌ Error loading business name:', error);
            // Fallback ke default
            document.getElementById('businessNameHeader').textContent = 'LaundryKu';
        }
    }

    async function loadHeaderData() {
        try {
            console.log('🌐 Fetching full header data from /header/data');
            const response = await fetch('/header/data');
            const data = await response.json();

            console.log('📊 Full header data response:', data);

            if (data.success) {
                // Update business name (sebagai backup)
                const businessNameElement = document.getElementById('businessNameHeader');
                if (businessNameElement && data.business_name) {
                    businessNameElement.textContent = data.business_name;
                }

                // Update notification badge
                const notificationBadge = document.getElementById('notificationBadge');
                if (notificationBadge) {
                    if (data.unread_notifications > 0) {
                        notificationBadge.classList.remove('hidden');
                        console.log('🔔 Notification badge shown');
                    } else {
                        notificationBadge.classList.add('hidden');
                        console.log('🔕 Notification badge hidden');
                    }
                }

                console.log('✅ Header data updated successfully');
            } else {
                console.error('❌ Failed to load header data:', data);
            }
        } catch (error) {
            console.error('❌ Error loading header data:', error);
        }
    }

    // Global function to update business name (bisa dipanggil dari settings page)
    function updateBusinessNameInHeader(newName) {
        console.log('🔄 Updating business name in header to:', newName);
        const businessNameElement = document.getElementById('businessNameHeader');
        if (businessNameElement) {
            businessNameElement.textContent = newName;
            console.log('✅ Business name updated in header:', newName);
        }
    }

    // Export function untuk bisa dipanggil dari file JS lain
    window.updateBusinessNameInHeader = updateBusinessNameInHeader;
</script>
@endpush