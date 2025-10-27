<nav class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 py-2">
    <div class="flex justify-around items-center">
        <a href="{{ route('dashboard') }}" 
           class="flex flex-col items-center {{ request()->routeIs('dashboard') ? 'text-blue-600' : 'text-gray-400' }}">
            <i class="fas fa-home text-lg"></i>
            <span class="text-xs mt-1">Home</span>
        </a>
        <a href="{{ route('transactions.index') }}" 
           class="flex flex-col items-center {{ request()->routeIs('transactions.*') ? 'text-blue-600' : 'text-gray-400' }}">
            <i class="fas fa-exchange-alt text-lg"></i>
            <span class="text-xs mt-1">Transaksi</span>
        </a>
        <a href="{{ route('customers.index') }}" 
           class="flex flex-col items-center {{ request()->routeIs('customers.*') ? 'text-blue-600' : 'text-gray-400' }}">
            <i class="fas fa-users text-lg"></i>
            <span class="text-xs mt-1">Pelanggan</span>
        </a>
        <a href="{{ route('services.index') }}" 
           class="flex flex-col items-center {{ request()->routeIs('services.*') ? 'text-blue-600' : 'text-gray-400' }}">
            <i class="fas fa-tshirt text-lg"></i>
            <span class="text-xs mt-1">Layanan</span>
        </a>
        <a href="{{ route('reports.index') }}" 
           class="flex flex-col items-center {{ request()->routeIs('reports.*') ? 'text-blue-600' : 'text-gray-400' }}">
            <i class="fas fa-chart-bar text-lg"></i>
            <span class="text-xs mt-1">Laporan</span>
        </a>
    </div>
</nav>