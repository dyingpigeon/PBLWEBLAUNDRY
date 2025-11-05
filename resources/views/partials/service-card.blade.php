<div class="service-card bg-white rounded-xl p-4 shadow-sm border border-gray-100 active:scale-95 transition-transform duration-200"
    data-service-id="{{ $service['id'] }}" data-category="{{ $service['category'] ?? 'general' }}"
    data-service-type="{{ $service['type'] }}" onclick="showServiceDetail({{ $service['id'] }})">

    <!-- Header dengan Status dan Type Badge -->
    <div class="flex items-start justify-between mb-3">
        <div class="flex items-center space-x-3 flex-1">
            <div class="w-12 h-12 rounded-xl flex items-center justify-center {{ $service['color'] ?? 'bg-blue-500' }}">
                <i class="{{ $service['icon'] ?? 'fas fa-tshirt' }} text-white text-lg"></i>
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center space-x-2 mb-1">
                    <h3 class="font-semibold text-gray-800 truncate">{{ $service['name'] }}</h3>
                </div>
                <div class="flex items-center space-x-2">
                    <span class="service-type-badge text-xs px-2 py-1 rounded-full 
                                {{ $service['type'] == 'kiloan' ? 'bg-blue-100 text-blue-600' :
    ($service['type'] == 'satuan' ? 'bg-green-100 text-green-600' :
        'bg-purple-100 text-purple-600') }}">
                        {{ ucfirst($service['type']) }}
                    </span>
                    @if(isset($service['category']))
                        <span class="text-sm text-gray-500">{{ $service['category'] }}</span>
                    @endif
                </div>
                @if(!empty($service['description']))
                    <p class="text-xs text-gray-400 mt-1">
                        {{ \Illuminate\Support\Str::limit($service['description'], 50) }}
                    </p>
                @endif
            </div>
        </div>
        <div class="flex items-center space-x-2">
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" {{ $service['active'] ? 'checked' : '' }} class="sr-only peer service-toggle"
                    data-service-id="{{ $service['id'] }}"
                    onchange="event.stopPropagation(); toggleServiceStatus({{ $service['id'] }}, this.checked)">
                <div
                    class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-500">
                </div>
            </label>
        </div>
    </div>
    
<!-- Service Items -->
<div class="space-y-2">
    @foreach($service['items'] as $item)
        <div class="flex items-center justify-between py-2 border-b border-gray-100 last:border-b-0">
            <div class="flex-1">
                <span class="text-sm text-gray-600">{{ $item['name'] }}</span>
                <div class="flex items-center space-x-2 text-xs text-gray-400 mt-1">
                    <span>{{ $item['unit'] }}</span>
                    <span>•</span>
                    <span><i class="fas fa-clock mr-1"></i>{{ $item['estimation_time'] ?? 24 }} jam</span>
                </div>
            </div>
            <div class="flex items-center space-x-2">
                <span class="font-semibold text-gray-800">Rp {{ number_format($item['price'], 0, ',', '.') }}</span>
                <button
                    class="edit-item-btn w-8 h-8 bg-blue-50 text-blue-600 rounded-lg flex items-center justify-center hover:bg-blue-100 transition-colors duration-200"
                    onclick="event.stopPropagation(); showEditItemModal({{ $item['id'] }}, {{ $service['id'] }})">
                    <i class="fas fa-edit text-xs"></i>
                </button>
            </div>
        </div>
    @endforeach
</div>

<!-- Footer Actions -->
<div class="mt-3 pt-3 border-t border-gray-100 flex justify-between items-center">
    <div class="flex space-x-3">
        <button
            class="text-xs text-gray-500 hover:text-gray-700 flex items-center space-x-1 transition-colors duration-200">
            <i class="fas fa-history"></i>
            <span>Riwayat</span>
        </button>
        <button
            class="edit-service-btn text-xs text-blue-500 hover:text-blue-700 flex items-center space-x-1 transition-colors duration-200"
            onclick="event.stopPropagation(); showEditServiceModal({{ $service['id'] }})">
            <i class="fas fa-cog"></i>
            <span>Edit</span>
        </button>
        <button
            class="delete-service-btn text-xs text-red-500 hover:text-red-700 flex items-center space-x-1 transition-colors duration-200"
            onclick="event.stopPropagation(); deleteService({{ $service['id'] }}, '{{ $service['name'] }}')">
            <i class="fas fa-trash"></i>
            <span>Hapus</span>
        </button>
    </div>
    <span
        class="text-xs px-2 py-1 rounded-full {{ count($service['items']) > 1 ? 'bg-purple-100 text-purple-600' : 'bg-gray-100 text-gray-600' }}">
        {{ count($service['items']) }} item
    </span>
</div>
</div>