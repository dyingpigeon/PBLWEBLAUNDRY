<!-- resources/views/partials/transaction-satuan-modal.blade.php -->
<div id="satuanModal" class="fixed inset-0 bg-black bg-opacity-50 z-30 hidden">
    <div class="absolute bottom-0 left-0 right-0 bg-white rounded-t-2xl max-h-screen overflow-hidden flex flex-col">
        <!-- Header -->
        <div class="flex items-center justify-between p-4 border-b border-gray-200">
            <div>
                <h3 class="text-lg font-semibold text-gray-800">Laundry Satuan</h3>
                <div class="flex items-center space-x-2 mt-1">
                    <div class="step-indicator step-completed w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold">1</div>
                    <div class="step-indicator step-completed w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold">2</div>
                    <div class="step-indicator step-active w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold">3</div>
                    <p class="text-sm text-gray-500">Pilih Kategori</p>
                </div>
            </div>
            <button onclick="backToServiceModal()" class="p-2 text-gray-400 hover:text-gray-600">
                <i class="fas fa-arrow-left text-lg"></i>
            </button>
        </div>

        <!-- Categories Grid -->
        <div class="flex-1 overflow-y-auto p-4">
            <div class="grid grid-cols-2 gap-3" id="categoriesGrid">
                <!-- Categories will be loaded here -->
            </div>
        </div>
    </div>
</div>

<script>
// Load categories
function loadCategories() {
    const container = document.getElementById('categoriesGrid');
    
    container.innerHTML = `
        <div class="col-span-2 text-center py-8">
            <i class="fas fa-spinner fa-spin text-blue-500 text-2xl"></i>
            <p class="text-gray-500 mt-2">Memuat kategori...</p>
        </div>
    `;

    // PERBAIKAN: Ganti dengan URL langsung
    fetch('/api/transactions/categories', {
        method: "GET",
        headers: {
            Accept: "application/json",
            "X-Requested-With": "XMLHttpRequest",
            "X-CSRF-TOKEN": '{{ csrf_token() }}',
        },
    })
    .then(response => {
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            return response.text().then(text => {
                throw new Error(`Server returned HTML instead of JSON. Status: ${response.status}`);
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            const categories = data.data;
            
            container.innerHTML = categories.map(category => {
                // PERBAIKAN: Gunakan escape yang benar untuk JSON
                const categoryJson = JSON.stringify(category).replace(/"/g, '&quot;');
                
                return `
                <div class="category-card bg-white rounded-xl p-4 border-2 border-gray-200 hover:border-blue-500 cursor-pointer text-center"
                     onclick="selectCategorySafe('${categoryJson}')">
                    <div class="w-16 h-16 bg-blue-500 rounded-full flex items-center justify-center mx-auto mb-3">
                        <i class="${category.icon || 'fas fa-tshirt'} text-white text-xl"></i>
                    </div>
                    <h4 class="font-semibold text-gray-800 mb-1">${category.name}</h4>
                    <p class="text-xs text-gray-500">Pilih item ${category.name.toLowerCase()}</p>
                </div>
                `;
            }).join('');
        } else {
            throw new Error(data.message);
        }
    })
    .catch(error => {
        console.error('Error loading categories:', error);
        container.innerHTML = `
            <div class="col-span-2 text-center py-8">
                <i class="fas fa-exclamation-triangle text-red-500 text-2xl"></i>
                <p class="text-red-500 mt-2">Gagal memuat kategori</p>
            </div>
        `;
    });
}

// Fungsi aman untuk memilih kategori
function selectCategorySafe(categoryJson) {
    try {
        const category = JSON.parse(categoryJson.replace(/&quot;/g, '"'));
        selectCategory(category);
    } catch (error) {
        console.error('Error parsing category JSON:', error);
        alert('Terjadi kesalahan saat memilih kategori');
    }
}

// Select category
function selectCategory(category) {
    transactionData.selected_category = category;
    showSatuanItemsModal(category);
}
</script>