// servicesPage.js - UPDATED VERSION FOR NEW SERVICE SYSTEM
// Handle halaman layanan dan harga dengan fitur lengkap

let currentCategory = "all";
let priceItemCount = 1;
let currentServiceId = null;
let currentServiceType = "kiloan";

// ===== INITIALIZATION FUNCTIONS =====

// Initialize on page load
document.addEventListener("DOMContentLoaded", function () {
    setupEventListeners();
    checkEmptyState();
    initializePriceItems();
    loadServiceCategories();
    updateServiceCardBadges();
});

// Setup event listeners
function setupEventListeners() {
    // Category filter functionality
    document.querySelectorAll(".category-btn").forEach((btn) => {
        btn.addEventListener("click", function () {
            const category = this.getAttribute("data-category");
            filterServices(category);

            // Update active state
            document.querySelectorAll(".category-btn").forEach((b) => {
                b.classList.remove("active", "bg-blue-500", "text-white");
                b.classList.add("bg-gray-100", "text-gray-600");
            });
            this.classList.add("active", "bg-blue-500", "text-white");
            this.classList.remove("bg-gray-100", "text-gray-600");
        });
    });

    // Service toggle functionality
    document.querySelectorAll(".service-toggle").forEach((toggle) => {
        toggle.addEventListener("change", function () {
            const serviceId = this.getAttribute("data-service-id");
            const isActive = this.checked;

            toggleService(serviceId, isActive);
        });
    });

    // Edit item form submission
    const editItemForm = document.getElementById("editItemForm");
    if (editItemForm) {
        editItemForm.addEventListener("submit", function (e) {
            e.preventDefault();
            updateServiceItem();
        });
    }

    // Add service form submission
    const addServiceForm = document.getElementById("addServiceForm");
    if (addServiceForm) {
        addServiceForm.addEventListener("submit", function (e) {
            e.preventDefault();
            handleAddService(e);
        });
    }

    // Edit service form submission
    const editServiceForm = document.getElementById("editServiceForm");
    if (editServiceForm) {
        editServiceForm.addEventListener("submit", function (e) {
            e.preventDefault();
            updateService();
        });
    }

    // Add item form submission
    const addItemForm = document.getElementById("addItemForm");
    if (addItemForm) {
        addItemForm.addEventListener("submit", function (e) {
            e.preventDefault();
            handleAddItem();
        });
    }

    // Close modals when clicking outside
    document.addEventListener("click", function (e) {
        if (
            e.target.id === "serviceAddModal" ||
            e.target.id === "editItemModal" ||
            e.target.id === "editServiceModal" ||
            e.target.id === "serviceDetailModal" ||
            e.target.id === "addItemModal"
        ) {
            closeAllModals();
        }
    });

    // Keyboard shortcuts
    document.addEventListener("keydown", function (e) {
        if (e.key === "Escape") {
            closeAllModals();
        }
    });
}

// ===== MODAL MANAGEMENT FUNCTIONS =====

// Show Add Service Modal
// function showAddServiceModal() {
//     const modal = document.getElementById("serviceAddModal");
//     if (modal) {
//         modal.classList.remove("hidden");
//         document.getElementById("serviceName").focus();
//     }
// }

// // Close Add Service Modal
// function closeAddServiceModal() {
//     const modal = document.getElementById("serviceAddModal");
//     if (modal) {
//         modal.classList.add("hidden");
//         document.getElementById("addServiceForm").reset();
//         resetPriceItems();
//     }
// }

// Show Edit Item Modal
function showEditItemModal(itemId, serviceId) {
    // Load item data first
    loadItemData(itemId, serviceId);
}

// Close Edit Item Modal
function closeEditItemModal() {
    const modal = document.getElementById("editItemModal");
    if (modal) {
        modal.classList.add("hidden");
    }
}

// Show Edit Service Modal
function showEditServiceModal(serviceId) {
    loadServiceData(serviceId);
}

// Close Edit Service Modal
function closeEditServiceModal() {
    const modal = document.getElementById("editServiceModal");
    if (modal) {
        modal.classList.add("hidden");
    }
}

// Show Service Detail Modal
function showServiceDetail(serviceId) {
    loadServiceDetail(serviceId);
}

// Close Service Detail Modal
function closeServiceDetailModal() {
    const modal = document.getElementById("serviceDetailModal");
    if (modal) {
        modal.classList.add("hidden");
    }
}

// Show Add Item Modal
function showAddItemModal() {
    const modal = document.getElementById("addItemModal");
    if (modal) {
        modal.classList.remove("hidden");
        document.getElementById("addItemServiceId").value =
            currentServiceId || "";
        document.getElementById("addItemName").focus();
    }
}

// Close Add Item Modal
function closeAddItemModal() {
    const modal = document.getElementById("addItemModal");
    if (modal) {
        modal.classList.add("hidden");
        document.getElementById("addItemForm").reset();
    }
}

// Close all modals
function closeAllModals() {
    const modals = [
        "serviceAddModal",
        "editItemModal",
        "editServiceModal",
        "serviceDetailModal",
        "addItemModal",
    ];
    modals.forEach((modalId) => {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add("hidden");
        }
    });
}

// ===== SERVICE TYPE HANDLING =====

// Handle service type change
function handleServiceTypeChange(type) {
    currentServiceType = type;
    updateItemsSectionByType(type);
    updateIconSuggestions(type);
}

// Update items section based on service type
function updateItemsSectionByType(type) {
    const itemsSection = document.getElementById("itemsSection");
    const title = itemsSection.querySelector("label");

    switch (type) {
        case "kiloan":
            title.textContent = "Harga Kiloan";
            resetPriceItemsToKiloan();
            break;
        case "satuan":
            title.textContent = "Item Satuan";
            resetPriceItemsToSatuan();
            break;
    }
}

// Update icon suggestions based on service type
function updateIconSuggestions(type) {
    const iconSelect = document.getElementById("serviceIcon");
    if (!iconSelect) return;

    // Clear existing options except the first one
    while (iconSelect.options.length > 1) {
        iconSelect.remove(1);
    }

    const icons = {
        kiloan: [
            { value: "fas fa-weight", text: "Weight" },
            { value: "fas fa-balance-scale", text: "Balance Scale" },
            { value: "fas fa-weight-hanging", text: "Weight Hanging" },
        ],
        satuan: [
            { value: "fas fa-tshirt", text: "T-Shirt" },
            { value: "fas fa-tshirt", text: "Shirt" },
            { value: "fas fa-socks", text: "Socks" },
        ],
    };

    const defaultIcons = [
        { value: "fas fa-soap", text: "Soap" },
        { value: "fas fa-fire", text: "Fire" },
        { value: "fas fa-wind", text: "Wind" },
        { value: "fas fa-star", text: "Star" },
        { value: "fas fa-gem", text: "Gem" },
    ];

    const typeIcons = icons[type] || [];
    const allIcons = [...typeIcons, ...defaultIcons];

    allIcons.forEach((icon) => {
        const option = document.createElement("option");
        option.value = icon.value;
        option.textContent = icon.text;
        iconSelect.appendChild(option);
    });
}

// ===== PRICE ITEMS MANAGEMENT FUNCTIONS =====

// Initialize price items
function initializePriceItems() {
    addPriceItem("kiloan"); // Add one initial item
}

// Add new price item row dengan type-specific template
function addPriceItem(type = currentServiceType) {
    const container = document.getElementById("priceItems");
    if (!container) return;

    const itemId = priceItemCount++;

    let itemHTML = "";

    switch (type) {
        case "kiloan":
            itemHTML = `
                <div class="price-item bg-gray-50 p-3 rounded-lg border border-gray-200">
                    <div class="grid grid-cols-2 gap-2 mb-2">
                        <div>
                            <label class="block text-xs text-gray-600 mb-1">Nama Item</label>
                            <input type="text" name="items[${itemId}][name]" value="Cuci Reguler" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-1 focus:ring-blue-500" required>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-600 mb-1">Unit</label>
                            <select name="items[${itemId}][unit]" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-1 focus:ring-blue-500" required>
                                <option value="kg">kg</option>
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs text-gray-600 mb-1">Harga</label>
                            <input type="number" name="items[${itemId}][price]" placeholder="0" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-1 focus:ring-blue-500" min="0" required>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-600 mb-1">Estimasi (jam)</label>
                            <input type="number" name="items[${itemId}][estimation_time]" value="24" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-1 focus:ring-blue-500" min="1" required>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-600 mb-1">Deskripsi (Opsional)</label>
                        <textarea name="items[${itemId}][description]" rows="2"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-1 focus:ring-blue-500"
                            placeholder="Deskripsi item..."></textarea>
                    </div>
                    ${
                        priceItemCount > 1
                            ? `<button type="button" onclick="removePriceItem(this)" class="mt-2 w-full py-1 bg-red-500 text-white rounded text-xs hover:bg-red-600 transition-colors">Hapus Item</button>`
                            : ""
                    }
                </div>
            `;
            break;

        case "satuan":
            itemHTML = `
                <div class="price-item bg-gray-50 p-3 rounded-lg border border-gray-200">
                    <div class="mb-2">
                        <label class="block text-xs text-gray-600 mb-1">Nama Item</label>
                        <input type="text" name="items[${itemId}][name]" placeholder="Contoh: Baju, Celana, Jaket" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-1 focus:ring-blue-500" required>
                    </div>
                    <div class="grid grid-cols-3 gap-2">
                        <div>
                            <label class="block text-xs text-gray-600 mb-1">Harga</label>
                            <input type="number" name="items[${itemId}][price]" placeholder="0" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-1 focus:ring-blue-500" min="0" required>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-600 mb-1">Unit</label>
                            <select name="items[${itemId}][unit]" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-1 focus:ring-blue-500" required>
                                <option value="pcs">pcs</option>
                                <option value="set">set</option>
                                <option value="pasang">pasang</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-600 mb-1">Estimasi (jam)</label>
                            <input type="number" name="items[${itemId}][estimation_time]" value="24" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-1 focus:ring-blue-500" min="1" required>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-600 mb-1">Deskripsi (Opsional)</label>
                        <textarea name="items[${itemId}][description]" rows="2"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-1 focus:ring-blue-500"
                            placeholder="Deskripsi item..."></textarea>
                    </div>
                    ${
                        priceItemCount > 1
                            ? `<button type="button" onclick="removePriceItem(this)" class="mt-2 w-full py-1 bg-red-500 text-white rounded text-xs hover:bg-red-600 transition-colors">Hapus Item</button>`
                            : ""
                    }
                </div>
            `;
            break;
    }

    const itemDiv = document.createElement("div");
    itemDiv.className = "price-item-wrapper";
    itemDiv.innerHTML = itemHTML;
    container.appendChild(itemDiv);

    // Update remove buttons visibility
    updateRemoveButtons();
}

// Remove price item row
function removePriceItem(button) {
    if (button && button.closest(".price-item-wrapper")) {
        button.closest(".price-item-wrapper").remove();
        updateRemoveButtons();
    }
}

// Update remove buttons visibility
function updateRemoveButtons() {
    const items = document.querySelectorAll(".price-item-wrapper");
    const removeButtons = document.querySelectorAll(
        ".price-item-wrapper button"
    );

    removeButtons.forEach((button) => {
        if (items.length > 1) {
            button.classList.remove("hidden");
        } else {
            button.classList.add("hidden");
        }
    });
}

// Reset price items to initial state
function resetPriceItems() {
    const container = document.getElementById("priceItems");
    if (container) {
        container.innerHTML = "";
        priceItemCount = 1;
        addPriceItem(currentServiceType);
    }
}

// Reset items untuk kiloan
function resetPriceItemsToKiloan() {
    const container = document.getElementById("priceItems");
    if (container) {
        container.innerHTML = "";
        priceItemCount = 1;
        addPriceItem("kiloan");
    }
}

// Reset items untuk satuan
function resetPriceItemsToSatuan() {
    const container = document.getElementById("priceItems");
    if (container) {
        container.innerHTML = "";
        priceItemCount = 1;
        addPriceItem("satuan");
    }
}

// ===== SERVICE FILTERING FUNCTIONS =====

// Filter services by category
function filterServices(category) {
    const serviceCards = document.querySelectorAll(".service-card");
    let visibleCount = 0;

    serviceCards.forEach((card) => {
        const cardCategory = card.getAttribute("data-category");

        if (category === "all" || cardCategory === category) {
            card.style.display = "block";
            visibleCount++;
        } else {
            card.style.display = "none";
        }
    });

    // Show/hide empty state
    updateEmptyState(visibleCount);
}

// Check and update empty state
function checkEmptyState() {
    const serviceCards = document.querySelectorAll(".service-card");
    updateEmptyState(serviceCards.length);
}

// Update empty state visibility
function updateEmptyState(visibleCount) {
    const emptyState = document.getElementById("emptyState");
    const servicesGrid = document.getElementById("servicesGrid");

    if (emptyState && servicesGrid) {
        if (visibleCount === 0) {
            servicesGrid.classList.add("hidden");
            emptyState.classList.remove("hidden");
        } else {
            servicesGrid.classList.remove("hidden");
            emptyState.classList.add("hidden");
        }
    }
}

// ===== API INTEGRATION FUNCTIONS =====

// Handle add service form submission
// ===== FORM SUBMISSION HANDLERS =====

// Handle add service form submission
function handleAddService(event) {
    event.preventDefault();

    const items = collectPriceItems();
    if (items.length === 0) {
        showError("Minimal harus ada satu item");
        return;
    }

    const serviceData = {
        name: document.getElementById("serviceName").value,
        type: document.getElementById("serviceType").value,
        description: document.getElementById("serviceDescription").value,
        icon: document.getElementById("serviceIcon").value,
        color: document.getElementById("serviceColor").value,
        items: items,
    };

    // Validasi tambahan berdasarkan type
    if (!validateServiceData(serviceData)) {
        return;
    }

    // Show loading state
    const submitBtn = document.querySelector(
        '#addServiceForm button[type="submit"]'
    );
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML =
        '<i class="fas fa-spinner fa-spin mr-2"></i>Menyimpan...';
    submitBtn.disabled = true;

    // Send AJAX request
    fetch("/services", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": getCsrfToken(),
        },
        body: JSON.stringify(serviceData),
    })
        .then((response) => {
            if (!response.ok) {
                throw new Error("Network response was not ok");
            }
            return response.json();
        })
        .then((data) => {
            if (data.success) {
                showToast("Layanan berhasil ditambahkan!", "success");
                closeAddServiceModal();
                // Reload page setelah delay singkat
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                throw new Error(data.message || "Gagal menambahkan layanan");
            }
        })
        .catch((error) => {
            console.error("Error:", error);
            showError("Terjadi kesalahan: " + error.message);
        })
        .finally(() => {
            // Reset button state
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        });
}

// Update service item
function updateServiceItem() {
    const serviceId = document.getElementById("editItemServiceId")?.value;
    const itemId = document.getElementById("editItemId")?.value;
    const name = document.getElementById("editItemName")?.value;
    const price = document.getElementById("editItemPrice")?.value;
    const unit = document.getElementById("editItemUnit")?.value;
    const estimation = document.getElementById("editItemEstimation")?.value;
    const description = document.getElementById("editItemDescription")?.value;

    if (!serviceId || !itemId || !name || !price || !unit || !estimation) {
        showError("Data tidak lengkap");
        return;
    }

    // Show loading state
    const submitBtn = document.querySelector(
        '#editItemForm button[type="submit"]'
    );
    if (!submitBtn) {
        showError("Form tidak ditemukan");
        return;
    }

    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML =
        '<i class="fas fa-spinner fa-spin mr-2"></i>Menyimpan...';
    submitBtn.disabled = true;

    fetch(`/services/${serviceId}/items/${itemId}`, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": getCsrfToken(),
        },
        body: JSON.stringify({
            name: name,
            price: price,
            unit: unit,
            estimation_time: estimation,
            description: description,
        }),
    })
        .then((response) => response.json())
        .then((data) => {
            if (data.success) {
                showToast("Item berhasil diupdate!", "success");
                closeEditItemModal();
                // Reload page setelah delay singkat
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                throw new Error(data.message || "Gagal mengupdate item");
            }
        })
        .catch((error) => {
            console.error("Error:", error);
            showError("Terjadi kesalahan saat mengupdate item");
        })
        .finally(() => {
            // Reset button state
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        });
}

// Update service
function updateService() {
    const serviceId = document.getElementById("editServiceId")?.value;
    const name = document.getElementById("editServiceName")?.value;
    const description = document.getElementById(
        "editServiceDescription"
    )?.value;
    const icon = document.getElementById("editServiceIcon")?.value;
    const color = document.getElementById("editServiceColor")?.value;
    const active = document.getElementById("editServiceActive")?.checked;

    if (!serviceId || !name || !icon || !color) {
        showError("Data tidak lengkap");
        return;
    }

    // Show loading state
    const submitBtn = document.querySelector(
        '#editServiceForm button[type="submit"]'
    );
    if (!submitBtn) {
        showError("Form tidak ditemukan");
        return;
    }

    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML =
        '<i class="fas fa-spinner fa-spin mr-2"></i>Menyimpan...';
    submitBtn.disabled = true;

    fetch(`/services/${serviceId}`, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": getCsrfToken(),
        },
        body: JSON.stringify({
            name: name,
            description: description,
            icon: icon,
            color: color,
            active: active,
        }),
    })
        .then((response) => response.json())
        .then((data) => {
            if (data.success) {
                showToast("Service berhasil diupdate!", "success");
                closeEditServiceModal();
                // Reload page setelah delay singkat
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                throw new Error(data.message || "Gagal mengupdate service");
            }
        })
        .catch((error) => {
            console.error("Error:", error);
            showError("Terjadi kesalahan saat mengupdate service");
        })
        .finally(() => {
            // Reset button state
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        });
}

// Handle add item form submission
function handleAddItem() {
    const serviceId = document.getElementById("addItemServiceId")?.value;
    const name = document.getElementById("addItemName")?.value;
    const price = document.getElementById("addItemPrice")?.value;
    const unit = document.getElementById("addItemUnit")?.value;
    const estimation = document.getElementById("addItemEstimation")?.value;
    const description = document.getElementById("addItemDescription")?.value;

    if (!serviceId || !name || !price || !unit || !estimation) {
        showError("Data tidak lengkap");
        return;
    }

    // Show loading state
    const submitBtn = document.querySelector(
        '#addItemForm button[type="submit"]'
    );
    if (!submitBtn) {
        showError("Form tidak ditemukan");
        return;
    }

    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML =
        '<i class="fas fa-spinner fa-spin mr-2"></i>Menambahkan...';
    submitBtn.disabled = true;

    fetch(`/services/${serviceId}/items`, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": getCsrfToken(),
        },
        body: JSON.stringify({
            name: name,
            price: price,
            unit: unit,
            estimation_time: estimation,
            description: description,
        }),
    })
        .then((response) => response.json())
        .then((data) => {
            if (data.success) {
                showToast("Item berhasil ditambahkan!", "success");
                closeAddItemModal();
                // Reload page setelah delay singkat
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                throw new Error(data.message || "Gagal menambahkan item");
            }
        })
        .catch((error) => {
            console.error("Error:", error);
            showError("Terjadi kesalahan saat menambahkan item");
        })
        .finally(() => {
            // Reset button state
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        });
}



    // Service toggle functionality
    document.querySelectorAll(".service-toggle").forEach((toggle) => {
        toggle.addEventListener("change", function () {
            const serviceId = this.getAttribute("data-service-id");
            const isActive = this.checked;

            toggleService(serviceId, isActive);
        });
    });

    // Edit item form submission - dengan null check
    const editItemForm = document.getElementById("editItemForm");
    if (editItemForm) {
        editItemForm.addEventListener("submit", function (e) {
            e.preventDefault();
            updateServiceItem();
        });
    } else {
        console.warn("Edit item form not found");
    }

    // Add service form submission - dengan null check
    const addServiceForm = document.getElementById("addServiceForm");
    if (addServiceForm) {
        addServiceForm.addEventListener("submit", function (e) {
            e.preventDefault();
            handleAddService(e);
        });
    } else {
        console.warn("Add service form not found");
    }

    // Edit service form submission - dengan null check
    const editServiceForm = document.getElementById("editServiceForm");
    if (editServiceForm) {
        editServiceForm.addEventListener("submit", function (e) {
            e.preventDefault();
            updateService();
        });
    } else {
        console.warn("Edit service form not found");
    }

    // Add item form submission - dengan null check
    const addItemForm = document.getElementById("addItemForm");
    if (addItemForm) {
        addItemForm.addEventListener("submit", function (e) {
            e.preventDefault();
            handleAddItem();
        });
    } else {
        console.warn("Add item form not found");
    }

    // Close modals when clicking outside
    document.addEventListener("click", function (e) {
        if (
            e.target.id === "serviceAddModal" ||
            e.target.id === "editItemModal" ||
            e.target.id === "editServiceModal" ||
            e.target.id === "serviceDetailModal" ||
            e.target.id === "addItemModal"
        ) {
            closeAllModals();
        }
    });

    // Keyboard shortcuts
    document.addEventListener("keydown", function (e) {
        if (e.key === "Escape") {
            closeAllModals();
        }
    });

// ===== UTILITY FUNCTIONS DENGAN SAFETY CHECK =====

// Collect price items dengan data lengkap dan safety check
function collectPriceItems() {
    const items = [];
    const priceItems = document.querySelectorAll(".price-item");

    if (!priceItems || priceItems.length === 0) {
        console.warn("No price items found");
        return items;
    }

    priceItems.forEach((item, index) => {
        const nameInput = item.querySelector('input[name*="[name]"]');
        const priceInput = item.querySelector('input[name*="[price]"]');
        const unitSelect = item.querySelector('select[name*="[unit]"]');
        const estimationInput = item.querySelector(
            'input[name*="[estimation_time]"]'
        );
        const descriptionInput = item.querySelector(
            'textarea[name*="[description]"]'
        );

        if (nameInput && nameInput.value && priceInput && priceInput.value) {
            items.push({
                name: nameInput.value,
                price: parseFloat(priceInput.value),
                unit: unitSelect ? unitSelect.value : "kg",
                estimation_time: estimationInput
                    ? parseInt(estimationInput.value)
                    : 24,
                description: descriptionInput ? descriptionInput.value : null,
            });
        }
    });

    console.log("Collected items:", items);
    return items;
}

// Validate service data based on type dengan safety check
function validateServiceData(data) {
    if (!data) {
        showError("Data service tidak valid");
        return false;
    }

    if (!data.type) {
        showError("Tipe layanan harus dipilih");
        return false;
    }

    if (data.type === "kiloan" && data.items.length > 1) {
        showError("Layanan kiloan hanya boleh memiliki satu item harga");
        return false;
    }

    if (!data.name || data.name.trim() === "") {
        showError("Nama layanan harus diisi");
        return false;
    }

    if (!data.icon || data.icon.trim() === "") {
        showError("Icon harus dipilih");
        return false;
    }

    if (!data.color || data.color.trim() === "") {
        showError("Warna harus dipilih");
        return false;
    }

    return true;
}

// Get CSRF token dengan safety check
function getCsrfToken() {
    const token = document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute("content");
    if (!token) {
        console.error("CSRF token not found");
        showError("CSRF token tidak ditemukan. Silakan refresh halaman.");
        return "";
    }
    return token;
}

function loadServiceDataFallback(serviceId) {
    console.warn('Using fallback method for service data');
    // Alternative implementation atau show error message
    showError('Tidak dapat memuat data service. Silakan refresh halaman.');
}

// Toggle service active status
function toggleService(serviceId, active) {
    fetch(`/services/${serviceId}/toggle`, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": getCsrfToken(),
        },
        body: JSON.stringify({
            active: active,
        }),
    })
        .then((response) => response.json())
        .then((data) => {
            if (!data.success) {
                throw new Error("Gagal mengupdate status service");
            }
            showToast(
                `Service ${active ? "diaktifkan" : "dinonaktifkan"}!`,
                "success"
            );
        })
        .catch((error) => {
            console.error("Error:", error);
            // Reset toggle state
            const toggle = document.querySelector(
                `.service-toggle[data-service-id="${serviceId}"]`
            );
            if (toggle) {
                toggle.checked = !active;
            }
            showError("Gagal mengupdate status service");
        });
}

// Load service data for editing
function loadServiceData(serviceId) {
    // Show loading state
    showToast("Memuat data service...", "info");

    fetch(`/services/${serviceId}/edit`, {
        method: "GET",
        headers: {
            Accept: "application/json",
            "X-Requested-With": "XMLHttpRequest",
            "X-CSRF-TOKEN": getCsrfToken(),
        },
    })
        .then((response) => {
            // Check if response is JSON
            const contentType = response.headers.get("content-type");
            if (!contentType || !contentType.includes("application/json")) {
                return response.text().then((text) => {
                    throw new Error(
                        `Server returned HTML instead of JSON. Status: ${response.status}`
                    );
                });
            }

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            return response.json();
        })
        .then((data) => {
            if (data.success) {
                showEditServiceModal(data.service);
            } else {
                throw new Error(data.message || "Gagal memuat data service");
            }
        })
        .catch((error) => {
            console.error("Error loading service data:", error);
            showError(
                "Gagal memuat data service. Pastikan endpoint /services/{id}/edit tersedia."
            );
            // Fallback ke method alternatif
            loadServiceDataFallback(serviceId);
        });
}

// Show edit service modal dengan data
function showEditServiceModal(service) {
    if (!service) {
        showError("Data service tidak valid");
        return;
    }

    try {
        // Isi form dengan data service
        document.getElementById("editServiceId").value = service.id || "";
        document.getElementById("editServiceName").value = service.name || "";
        document.getElementById("editServiceType").value =
            service.type || "kiloan";
        document.getElementById("editServiceDescription").value =
            service.description || "";
        document.getElementById("editServiceIcon").value =
            service.icon || "fas fa-tshirt";
        document.getElementById("editServiceColor").value =
            service.color || "blue-500";
        document.getElementById("editServiceActive").checked =
            service.active || true;

        const modal = document.getElementById("editServiceModal");
        if (modal) {
            modal.classList.remove("hidden");
        }
    } catch (error) {
        console.error("Error showing edit modal:", error);
        showError("Gagal menampilkan form edit service");
    }
}

// Update service
function updateService() {
    const serviceId = document.getElementById("editServiceId").value;
    const name = document.getElementById("editServiceName").value;
    const description = document.getElementById("editServiceDescription").value;
    const icon = document.getElementById("editServiceIcon").value;
    const color = document.getElementById("editServiceColor").value;
    const active = document.getElementById("editServiceActive").checked;

    if (!serviceId || !name || !icon || !color) {
        showError("Data tidak lengkap");
        return;
    }

    // Show loading state
    const submitBtn = document.querySelector(
        '#editServiceForm button[type="submit"]'
    );
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML =
        '<i class="fas fa-spinner fa-spin mr-2"></i>Menyimpan...';
    submitBtn.disabled = true;

    fetch(`/services/${serviceId}`, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": getCsrfToken(),
        },
        body: JSON.stringify({
            name: name,
            description: description,
            icon: icon,
            color: color,
            active: active,
        }),
    })
        .then((response) => response.json())
        .then((data) => {
            if (data.success) {
                showToast("Service berhasil diupdate!", "success");
                closeEditServiceModal();
                // Reload page setelah delay singkat
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                throw new Error(data.message || "Gagal mengupdate service");
            }
        })
        .catch((error) => {
            console.error("Error:", error);
            showError("Terjadi kesalahan saat mengupdate service");
        })
        .finally(() => {
            // Reset button state
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        });
}

// Load item data for editing
function loadItemData(itemId, serviceId) {
    // First get service data to find the item
    fetch(`/services/${serviceId}`, {
        method: "GET",
        headers: {
            Accept: "application/json",
            "X-CSRF-TOKEN": getCsrfToken(),
        },
    })
        .then((response) => response.json())
        .then((data) => {
            if (data.success) {
                const item = data.service.items.find(
                    (item) => item.id == itemId
                );
                if (item) {
                    showEditItemModalForm(item, serviceId);
                } else {
                    showError("Item tidak ditemukan");
                }
            } else {
                throw new Error(data.message || "Gagal memuat data service");
            }
        })
        .catch((error) => {
            console.error("Error loading item data:", error);
            showError("Gagal memuat data item");
        });
}

// Show edit item modal dengan data
function showEditItemModalForm(item, serviceId) {
    document.getElementById("editItemServiceId").value = serviceId;
    document.getElementById("editItemId").value = item.id;
    document.getElementById("editItemName").value = item.name || "";
    document.getElementById("editItemPrice").value = item.price || "";
    document.getElementById("editItemUnit").value = item.unit || "kg";
    document.getElementById("editItemEstimation").value =
        item.estimation_time || 24;
    document.getElementById("editItemDescription").value =
        item.description || "";

    const modal = document.getElementById("editItemModal");
    if (modal) {
        modal.classList.remove("hidden");
    }
}

// Load service detail
function loadServiceDetail(serviceId) {
    fetch(`/services/${serviceId}`, {
        method: "GET",
        headers: {
            Accept: "application/json",
            "X-CSRF-TOKEN": getCsrfToken(),
        },
    })
        .then((response) => response.json())
        .then((data) => {
            if (data.success) {
                showServiceDetailModal(data.service);
                currentServiceId = serviceId;
            } else {
                throw new Error(data.message || "Gagal memuat detail service");
            }
        })
        .catch((error) => {
            console.error("Error loading service detail:", error);
            showError("Gagal memuat detail service");
        });
}

// Show service detail modal
function showServiceDetailModal(service) {
    // Populate basic service info
    document.getElementById("detailServiceName").textContent = service.name;

    // Set service type badge
    const typeBadge = document.getElementById("detailServiceType");
    typeBadge.textContent =
        service.type.charAt(0).toUpperCase() + service.type.slice(1);
    typeBadge.className = `text-xs px-2 py-1 rounded-full ${
        service.type === "kiloan"
            ? "bg-blue-100 text-blue-600"
            : service.type === "satuan"
            ? "bg-green-100 text-green-600"
            : "bg-purple-100 text-purple-600"
    }`;

    // Set service icon and color
    const iconContainer = document.getElementById("detailServiceIcon");
    iconContainer.className = `w-16 h-16 rounded-xl flex items-center justify-center ${service.color}`;
    iconContainer.querySelector("i").className =
        service.icon + " text-white text-2xl";

    // Set service description
    const descContainer = document.getElementById(
        "detailServiceDescriptionContainer"
    );
    const descElement = document.getElementById("detailServiceDescription");
    if (service.description && service.description.trim() !== "") {
        descElement.textContent = service.description;
        descContainer.classList.remove("hidden");
    } else {
        descContainer.classList.add("hidden");
    }

    // Set service status
    const statusElement = document.getElementById("detailActiveStatus");
    statusElement.textContent = service.active ? "Aktif" : "Nonaktif";
    statusElement.className = `font-medium ${
        service.active ? "text-green-600" : "text-red-600"
    }`;

    // Calculate and display statistics
    const totalItems = service.items.length;
    const avgPrice =
        totalItems > 0
            ? service.items.reduce((sum, item) => sum + item.price, 0) /
              totalItems
            : 0;

    document.getElementById("detailTotalItems").textContent = totalItems;
    document.getElementById("detailAvgPrice").textContent = `Rp ${Math.round(
        avgPrice
    ).toLocaleString("id-ID")}`;
    document.getElementById(
        "detailItemsCount"
    ).textContent = `${totalItems} item${totalItems > 1 ? "s" : ""}`;

    // Populate service items
    const itemsContainer = document.getElementById("detailServiceItems");
    itemsContainer.innerHTML = "";

    service.items.forEach((item, index) => {
        const itemElement = document.createElement("div");
        itemElement.className =
            "service-item-card bg-gray-50 rounded-lg p-3 border border-gray-200 cursor-pointer";
        itemElement.onclick = function (event) {
            event.stopPropagation();
            showEditItemModal(item.id, service.id);
        };
        itemElement.innerHTML = `
            <div class="flex justify-between items-start">
                <div class="flex-1">
                    <h5 class="font-medium text-gray-800">${item.name}</h5>
                    <div class="flex items-center space-x-3 mt-2 text-xs text-gray-500">
                        <span class="flex items-center space-x-1">
                            <i class="fas fa-tag"></i>
                            <span>Rp ${item.price.toLocaleString(
                                "id-ID"
                            )}</span>
                        </span>
                        <span class="flex items-center space-x-1">
                            <i class="fas fa-ruler"></i>
                            <span>${item.unit}</span>
                        </span>
                        <span class="flex items-center space-x-1">
                            <i class="fas fa-clock"></i>
                            <span>${item.estimation_time} jam</span>
                        </span>
                    </div>
                </div>
                <div class="text-blue-500">
                    <i class="fas fa-edit"></i>
                </div>
            </div>
        `;
        itemsContainer.appendChild(itemElement);
    });

    // Set additional info
    document.getElementById("detailServiceTypeFull").textContent =
        service.type === "kiloan" ? "Laundry Kiloan" : "Laundry Satuan";
    document.getElementById("detailCreatedAt").textContent = new Date(
        service.created_at
    ).toLocaleDateString("id-ID");
    document.getElementById("detailUpdatedAt").textContent = new Date(
        service.updated_at
    ).toLocaleDateString("id-ID");

    // Show modal
    const modal = document.getElementById("serviceDetailModal");
    modal.classList.remove("hidden");
}

// Handle add item form submission
function handleAddItem() {
    const serviceId = document.getElementById("addItemServiceId").value;
    const name = document.getElementById("addItemName").value;
    const price = document.getElementById("addItemPrice").value;
    const unit = document.getElementById("addItemUnit").value;
    const estimation = document.getElementById("addItemEstimation").value;
    const description = document.getElementById("addItemDescription").value;

    if (!serviceId || !name || !price || !unit || !estimation) {
        showError("Data tidak lengkap");
        return;
    }

    // Show loading state
    const submitBtn = document.querySelector(
        '#addItemForm button[type="submit"]'
    );
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML =
        '<i class="fas fa-spinner fa-spin mr-2"></i>Menambahkan...';
    submitBtn.disabled = true;

    fetch(`/services/${serviceId}/items`, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": getCsrfToken(),
        },
        body: JSON.stringify({
            name: name,
            price: price,
            unit: unit,
            estimation_time: estimation,
            description: description,
        }),
    })
        .then((response) => response.json())
        .then((data) => {
            if (data.success) {
                showToast("Item berhasil ditambahkan!", "success");
                closeAddItemModal();
                // Reload page setelah delay singkat
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                throw new Error(data.message || "Gagal menambahkan item");
            }
        })
        .catch((error) => {
            console.error("Error:", error);
            showError("Terjadi kesalahan saat menambahkan item");
        })
        .finally(() => {
            // Reset button state
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        });
}

// Delete service
function deleteService(serviceId, serviceName) {
    if (
        !confirm(`Apakah Anda yakin ingin menghapus service "${serviceName}"?`)
    ) {
        return;
    }

    fetch(`/services/${serviceId}`, {
        method: "DELETE",
        headers: {
            "X-CSRF-TOKEN": getCsrfToken(),
        },
    })
        .then((response) => response.json())
        .then((data) => {
            if (data.success) {
                showToast("Service berhasil dihapus!", "success");
                // Remove service card dari DOM
                const serviceCard = document.querySelector(
                    `[data-service-id="${serviceId}"]`
                );
                if (serviceCard) {
                    serviceCard.remove();
                }
                checkEmptyState();
            } else {
                throw new Error(data.message || "Gagal menghapus service");
            }
        })
        .catch((error) => {
            console.error("Error:", error);
            showError("Gagal menghapus service");
        });
}

// ===== UTILITY FUNCTIONS =====

// Collect price items dengan data lengkap
function collectPriceItems() {
    const items = [];

    document.querySelectorAll(".price-item").forEach((item, index) => {
        const nameInput = item.querySelector('input[name*="[name]"]');
        const priceInput = item.querySelector('input[name*="[price]"]');
        const unitSelect = item.querySelector('select[name*="[unit]"]');
        const estimationInput = item.querySelector(
            'input[name*="[estimation_time]"]'
        );
        const descriptionInput = item.querySelector(
            'textarea[name*="[description]"]'
        );

        if (nameInput && nameInput.value && priceInput && priceInput.value) {
            items.push({
                name: nameInput.value,
                price: parseFloat(priceInput.value),
                unit: unitSelect ? unitSelect.value : "kg",
                estimation_time: estimationInput
                    ? parseInt(estimationInput.value)
                    : 24,
                description: descriptionInput ? descriptionInput.value : null,
            });
        }
    });

    return items;
}

// Validate service data based on type
function validateServiceData(data) {
    if (!data.type) {
        showError("Tipe layanan harus dipilih");
        return false;
    }

    if (data.type === "kiloan" && data.items.length > 1) {
        showError("Layanan kiloan hanya boleh memiliki satu item harga");
        return false;
    }

    if (!data.name || data.name.trim() === "") {
        showError("Nama layanan harus diisi");
        return false;
    }

    return true;
}

// Load service categories dari API
function loadServiceCategories() {
    fetch("/services/categories")
        .then((response) => response.json())
        .then((data) => {
            if (data.success) {
                updateCategoryFilter(data.categories);
            }
        })
        .catch((error) => console.error("Error loading categories:", error));
}

// Update category filter dengan data dinamis
function updateCategoryFilter(categories) {
    const categoryContainer = document.querySelector(".swipeable-categories");
    if (!categoryContainer) return;

    // Keep "Semua" button
    const allButton = categoryContainer.querySelector('[data-category="all"]');
    categoryContainer.innerHTML = "";
    if (allButton) {
        categoryContainer.appendChild(allButton);
    }

    categories.forEach((category) => {
        const button = document.createElement("button");
        button.className =
            "category-btn flex-shrink-0 px-4 py-2 bg-gray-100 text-gray-600 rounded-full text-sm font-medium";

        // Gunakan original_name untuk filtering jika ada, otherwise use name
        const filterCategory =
            category.original_name || category.name.toLowerCase();
        button.setAttribute("data-category", filterCategory);

        button.innerHTML = `${category.name}`;

        button.addEventListener("click", function () {
            const category = this.getAttribute("data-category");
            filterServices(category);

            // Update active state
            document.querySelectorAll(".category-btn").forEach((b) => {
                b.classList.remove("active", "bg-blue-500", "text-white");
                b.classList.add("bg-gray-100", "text-gray-600");
            });
            this.classList.add("active", "bg-blue-500", "text-white");
            this.classList.remove("bg-gray-100", "text-gray-600");
        });

        categoryContainer.appendChild(button);
    });
}

// Tampilkan badge type pada service card
function updateServiceCardBadges() {
    document.querySelectorAll(".service-card").forEach((card) => {
        const serviceId = card.getAttribute("data-service-id");
        const serviceType = card.getAttribute("data-service-type");

        let badgeColor = "bg-gray-100 text-gray-600";
        let badgeText = "General";

        switch (serviceType) {
            case "kiloan":
                badgeColor = "bg-blue-100 text-blue-600";
                badgeText = "Kiloan";
                break;
            case "satuan":
                badgeColor = "bg-green-100 text-green-600";
                badgeText = "Satuan";
                break;
        }

        // Add badge if not exists
        if (!card.querySelector(".service-type-badge")) {
            const headerSection = card.querySelector(
                ".flex.items-start.justify-between"
            );
            if (headerSection) {
                const badge = document.createElement("span");
                badge.className = `service-type-badge text-xs px-2 py-1 rounded-full ${badgeColor}`;
                badge.textContent = badgeText;
                headerSection.appendChild(badge);
            }
        }
    });
}

// Get CSRF token
function getCsrfToken() {
    return (
        document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute("content") || ""
    );
}

// Show toast notification
function showToast(message, type = "success") {
    // Remove existing toasts
    document
        .querySelectorAll(".custom-toast")
        .forEach((toast) => toast.remove());

    const toast = document.createElement("div");
    toast.className = `custom-toast fixed top-4 left-4 right-4 p-3 rounded-lg shadow-lg text-center z-50 transform transition-all duration-300 ${
        type === "success"
            ? "bg-green-500 text-white"
            : type === "error"
            ? "bg-red-500 text-white"
            : "bg-blue-500 text-white"
    }`;
    toast.textContent = message;
    document.body.appendChild(toast);

    // Animate in
    setTimeout(() => {
        toast.classList.remove("transform", "translate-y-[-100%]");
    }, 10);

    // Remove after 3 seconds
    setTimeout(() => {
        toast.classList.add("transform", "translate-y-[-100%]", "opacity-0");
        setTimeout(() => {
            if (toast.parentNode) {
                document.body.removeChild(toast);
            }
        }, 300);
    }, 3000);
}

// Show error message
function showError(message) {
    showToast(message, "error");
}

// Show price history (placeholder)
function showPriceHistory(serviceId) {
    // Implementasi riwayat harga
    alert(`Riwayat harga untuk service ${serviceId} akan diimplementasikan`);
}

// Export functions untuk global access
window.showAddServiceModal = showAddServiceModal;
window.closeAddServiceModal = closeAddServiceModal;
window.showEditItemModal = showEditItemModal;
window.closeEditItemModal = closeEditItemModal;
window.showEditServiceModal = showEditServiceModal;
window.closeEditServiceModal = closeEditServiceModal;
window.showServiceDetail = showServiceDetail;
window.closeServiceDetailModal = closeServiceDetailModal;
window.showAddItemModal = showAddItemModal;
window.closeAddItemModal = closeAddItemModal;
window.closeAllModals = closeAllModals;
window.handleServiceTypeChange = handleServiceTypeChange;
window.addPriceItem = addPriceItem;
window.removePriceItem = removePriceItem;
window.showPriceHistory = showPriceHistory;
window.deleteService = deleteService;
window.toggleService = toggleService;
