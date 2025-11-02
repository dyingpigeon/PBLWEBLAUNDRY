<header class="bg-white shadow-sm sticky top-0 z-10">
    <div class="px-4 py-3 flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-gray-800">LaundryKu</h1>
            <p class="text-xs text-gray-500">Admin Dashboard</p>
        </div>
        <div class="flex items-center space-x-3">
            <!-- Notifications -->
            <a href="{{ route('notifications.index') }}" 
               class="relative p-2 text-gray-600 hover:text-gray-800 transition-colors {{ request()->routeIs('notifications.*') ? 'text-blue-600' : '' }}">
                <i class="fas fa-bell text-lg"></i>
                <span class="absolute top-0 right-0 w-2 h-2 bg-red-500 rounded-full"></span>
            </a>
            
            <!-- Settings -->
            <a href="{{ route('settings.index') }}" 
               class="p-2 text-gray-600 hover:text-gray-800 transition-colors {{ request()->routeIs('settings.*') ? 'text-blue-600' : '' }}">
                <i class="fas fa-cog text-lg"></i>
            </a>
            
            <!-- User Profile -->
            <a href="{{ route('profile.index') }}" 
               class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center text-white font-bold cursor-pointer hover:bg-blue-600 transition-colors {{ request()->routeIs('profile.*') ? 'ring-2 ring-blue-300' : '' }}">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </a>
        </div>
    </div>
</header>