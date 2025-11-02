// servicesPage.js
// Handle halaman layanan dan harga (service management, pricing)

let currentCategory = 'all';
let priceItemCount = 1;

// ===== INITIALIZATION FUNCTIONS =====

// Initialize on page load
document.addEventListener('DOMContentLoaded', function () {
    setupEventListeners();
    checkEmptyState();
    initializePriceItems();
});

// Setup event listeners
function setupEventListeners() {
    // Category filter functionality
    document.querySelectorAll('.category-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const category = this.getAttribute('data-category');
            filterServices(category);

            // Update active state
            document.querySelectorAll('.category-btn').forEach(b => {
                b.classList.remove('active', 'bg-blue-500', 'text-white');
                b.classList.add('bg-gray-100', 'text-gray-600');
            });
            this.classList.add('active', 'bg-blue-500', 'text-white');
            this.classList.remove('bg-gray-100', 'text-gray-600');
        });
    });

    // Service toggle functionality
    document.querySelectorAll('.service-toggle').forEach(toggle => {
        toggle.addEventListener('change', function () {
            const serviceId = this.getAttribute('data-service-id');
            const isActive = this.checked;

            toggleService(serviceId, isActive);
        });
    });

    // Edit item form submission
    const editItemForm = document.getElementById('editItemForm');
    if (editItemForm) {
        editItemForm.addEventListener('submit', function (e) {
            e.preventDefault();
            updateServiceItem();
        });
    }

    // Add service form submission
    const addServiceForm = document.getElementById('addServiceForm');
    if (addServiceForm) {
        addServiceForm.addEventListener('submit', function (e) {
            e.preventDefault();
            handleAddService(e);
        });
    }
}

// ===== MODAL MANAGEMENT FUNCTIONS =====

// Show Add Service Modal
function showAddServiceModal() {
    const modal = document.getElementById('addServiceModal');
    if (modal) {
        modal.classList.remove('hidden');
        document.getElementById('serviceName').focus();
    }
}

// Close Add Service Modal
function closeAddServiceModal() {
    const modal = document.getElementById('addServiceModal');
    if (modal) {
        modal.classList.add('hidden');
        document.getElementById('addServiceForm').reset();
        resetPriceItems();
    }
}

// Show Edit Item Modal
function editServiceItem(serviceId, itemId, itemName, itemPrice) {
    document.getElementById('editItemServiceId').value = serviceId;
    document.getElementById('editItemId').value = itemId;
    document.getElementById('editItemName').value = itemName;
    document.getElementById('editItemPrice').value = itemPrice;
    
    const modal = document.getElementById('editItemModal');
    if (modal) {
        modal.classList.remove('hidden');
    }
}

// Close Edit Item Modal
function closeEditItemModal() {
    const modal = document.getElementById('editItemModal');
    if (modal) {
        modal.classList.add('hidden');
    }
}

// ===== PRICE ITEMS MANAGEMENT FUNCTIONS =====

// Initialize price items
function initializePriceItems() {
    addPriceItem(); // Add one initial item
}

// Add new price item row
function addPriceItem() {
    const container = document.getElementById('priceItems');
    if (!container) return;

    const itemId = priceItemCount++;

    const itemDiv = document.createElement('div');
    itemDiv.className = 'flex space-x-2 price-item';
    itemDiv.innerHTML = `
        <input type="text" name="items[${itemId}][name]" placeholder="Nama item" 
               class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-1 focus:ring-blue-500" required>
        <input type="number" name="items[${itemId}][price]" placeholder="Harga" 
               class="w-24 px-3 py-2 border border-gray-300 rounded-lg focus:ring-1 focus:ring-blue-500" min="0" required>
        <button type="button" onclick="removePriceItem(this)" 
                class="w-10 h-10 bg-red-500 text-white rounded-lg flex items-center justify-center ${priceItemCount === 1 ? 'hidden' : ''}">
            <i class="fas fa-times"></i>
        </button>
    `;
    container.appendChild(itemDiv);

    // Show remove buttons if there's more than one item
    updateRemoveButtons();
}

// Remove price item row
function removePriceItem(button) {
    if (button && button.closest('.price-item')) {
        button.closest('.price-item').remove();
        updateRemoveButtons();
    }
}

// Update remove buttons visibility
function updateRemoveButtons() {
    const items = document.querySelectorAll('.price-item');
    const removeButtons = document.querySelectorAll('.price-item button');

    removeButtons.forEach(button => {
        if (items.length > 1) {
            button.classList.remove('hidden');
        } else {
            button.classList.add('hidden');
        }
    });
}

// Reset price items to initial state
function resetPriceItems() {
    const container = document.getElementById('priceItems');
    if (container) {
        container.innerHTML = '';
        priceItemCount = 1;
        addPriceItem();
    }
}

// ===== SERVICE FILTERING FUNCTIONS =====

// Filter services by category
function filterServices(category) {
    const serviceCards = document.querySelectorAll('.service-card');
    let visibleCount = 0;

    serviceCards.forEach(card => {
        const cardCategory = card.getAttribute('data-category');

        if (category === 'all' || cardCategory.includes(category)) {
            card.style.display = 'block';
            visibleCount++;
        } else {
            card.style.display = 'none';
        }
    });

    // Show/hide empty state
    updateEmptyState(visibleCount);
}

// Check and update empty state
function checkEmptyState() {
    const serviceCards = document.querySelectorAll('.service-card');
    updateEmptyState(serviceCards.length);
}

// Update empty state visibility
function updateEmptyState(visibleCount) {
    const emptyState = document.getElementById('emptyState');
    const servicesGrid = document.getElementById('servicesGrid');

    if (emptyState && servicesGrid) {
        if (visibleCount === 0) {
            servicesGrid.classList.add('hidden');
            emptyState.classList.remove('hidden');
        } else {
            servicesGrid.classList.remove('hidden');
            emptyState.classList.add('hidden');
        }
    }
}

// ===== API INTEGRATION FUNCTIONS =====

// Handle add service form submission
function handleAddService(event) {
    event.preventDefault();

    const items = collectPriceItems();
    if (items.length === 0) {
        alert('Minimal harus ada satu item harga');
        return;
    }

    const serviceData = {
        name: document.getElementById('serviceName').value,
        category: document.getElementById('serviceCategory').value,
        icon: document.getElementById('serviceIcon').value,
        color: document.getElementById('serviceColor').value,
        items: items
    };

    // Send AJAX request
    fetch('{{ route("services.store") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': getCsrfToken()
        },
        body: JSON.stringify(serviceData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Service berhasil ditambahkan!');
            closeAddServiceModal();
            window.location.reload(); // Reload to show new data
        } else {
            throw new Error(data.message || 'Gagal menambahkan service');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showError('Terjadi kesalahan saat menambahkan service: ' + error.message);
    });
}

// Collect price items from form
function collectPriceItems() {
    const items = [];
    
    document.querySelectorAll('.price-item').forEach((item, index) => {
        const nameInput = item.querySelector('input[type="text"]');
        const priceInput = item.querySelector('input[type="number"]');

        if (nameInput && nameInput.value && priceInput && priceInput.value) {
            items.push({
                name: nameInput.value,
                price: parseFloat(priceInput.value)
            });
        }
    });
    
    return items;
}

// Toggle service active status
function toggleService(serviceId, active) {
    fetch(`/services/${serviceId}/toggle`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': getCsrfToken()
        },
        body: JSON.stringify({
            active: active
        })
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            throw new Error('Gagal mengupdate status service');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        // Reset toggle state
        const toggle = document.querySelector(`.service-toggle[data-service-id="${serviceId}"]`);
        if (toggle) {
            toggle.checked = !active;
        }
        showError('Gagal mengupdate status service');
    });
}

// Update service item
function updateServiceItem() {
    const serviceId = document.getElementById('editItemServiceId').value;
    const itemId = document.getElementById('editItemId').value;
    const name = document.getElementById('editItemName').value;
    const price = document.getElementById('editItemPrice').value;

    if (!serviceId || !itemId || !name || !price) {
        showError('Data tidak lengkap');
        return;
    }

    fetch(`/services/${serviceId}/items/${itemId}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': getCsrfToken()
        },
        body: JSON.stringify({
            name: name,
            price: price
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Harga berhasil diupdate');
            closeEditItemModal();
            location.reload(); // Refresh untuk melihat perubahan
        } else {
            throw new Error(data.message || 'Gagal mengupdate harga');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showError('Terjadi kesalahan saat mengupdate harga');
    });
}

// ===== UTILITY FUNCTIONS =====

// Get CSRF token
function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
}

// Show toast notification
function showToast(message) {
    // Simple toast implementation
    const toast = document.createElement('div');
    toast.className = 'fixed top-4 left-4 right-4 bg-green-500 text-white p-3 rounded-lg shadow-lg text-center z-50';
    toast.textContent = message;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        if (toast.parentNode) {
            document.body.removeChild(toast);
        }
    }, 3000);
}

// Show error message
function showError(message) {
    alert(message); // Bisa diganti dengan modal error yang lebih baik
}

// Show price history (placeholder)
function showPriceHistory(serviceId) {
    // Implementasi riwayat harga
    alert(`Riwayat harga untuk service ${serviceId} akan diimplementasikan`);
}
