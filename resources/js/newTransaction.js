// newTransaction.js - UPDATED FOR KILOAN/SATUAN FLOW
// Handle wizard pembuatan transaksi baru

let currentStep = 0;
let transactionData = {
    customer: null,
    order_type: null, // 'kiloan' or 'satuan'
    service: null,
    service_item: null, // untuk kiloan
    items: [], // untuk satuan
    weight: null, // untuk kiloan
    notes: "",
    total: 0,
    payment_type: "later", // 'now' or 'later'
    payment_method: null,
    selected_category: null,
};

// Helper function untuk get CSRF token - SESUAI DENGAN LAYOUT
function getCsrfToken() {
    const metaTag = document.querySelector('meta[name="csrf-token"]');
    return metaTag ? metaTag.getAttribute("content") : "";
}

// ===== MODAL NAVIGATION FUNCTIONS =====

// Start new transaction wizard
function startNewTransaction() {
    currentStep = 0;
    transactionData = {
        customer: null,
        order_type: null,
        service: null,
        service_item: null,
        items: [],
        weight: null,
        notes: "",
        total: 0,
        payment_type: "later",
        payment_method: null,
        selected_category: null,
    };

    // Reset form fields
    const notesElement = document.getElementById("transactionNotes");
    if (notesElement) notesElement.value = "";

    // Hide preview sections
    const customerPreview = document.getElementById("selectedCustomerPreview");
    const servicePreview = document.getElementById("selectedServicePreview");
    if (customerPreview) customerPreview.classList.add("hidden");
    if (servicePreview) servicePreview.classList.add("hidden");

    showCustomerModal();
}

// Show customer selection modal
function showCustomerModal() {
    closeAllModals();
    document.getElementById("customerModal").classList.remove("hidden");
    updateStepIndicator(0);

    // Load customers if not already loaded
    const customersList = document.getElementById("customersList");
    if (customersList && customersList.children.length === 0) {
        filterCustomers("");
    }
}

// Show service selection modal (pilih tipe: kiloan/satuan)
function showServiceModal() {
    closeAllModals();
    document.getElementById("serviceModal").classList.remove("hidden");
    updateStepIndicator(1);
}

// Show kiloan service modal
function showKiloanModal() {
    closeAllModals();
    document.getElementById("kiloanModal").classList.remove("hidden");
    updateStepIndicator(2);
    loadKiloanServices();
}

// Show satuan category modal
function showSatuanModal() {
    closeAllModals();
    document.getElementById("satuanModal").classList.remove("hidden");
    updateStepIndicator(2);
    loadCategories();
}

// Show satuan items modal
function showSatuanItemsModal(category) {
    closeAllModals();
    document.getElementById("satuanItemsModal").classList.remove("hidden");
    updateStepIndicator(3);
    loadCategoryItems(category.id);
}

// Show payment modal
function showPaymentModal() {
    closeAllModals();
    document.getElementById("paymentModal").classList.remove("hidden");
    updateStepIndicator(4);
    updatePaymentSummary();
}

// Show review modal
function showReviewModal() {
    closeAllModals();
    document.getElementById("reviewModal").classList.remove("hidden");
    updateStepIndicator(5);
    updateReviewSummary();
}

// Show success modal
// Show success modal
function showSuccessModal() {
    closeAllModals();
    document.getElementById("successModal").classList.remove("hidden");

    // Update success modal data
    document.getElementById("successTotal").textContent = formatPrice(
        transactionData.total
    );
    document.getElementById("successCustomer").textContent =
        transactionData.customer.name;

    // PERBAIKAN: Handle service name yang mungkin null untuk satuan
    let serviceText = "Laundry Satuan"; // Default untuk satuan

    if (transactionData.service && transactionData.service.name) {
        serviceText = transactionData.service.name;
    }

    if (transactionData.order_type === "kiloan") {
        serviceText += ` (${transactionData.weight} kg)`;
    } else {
        // Untuk satuan, tampilkan jumlah items
        const itemCount = transactionData.items.length;
        serviceText += ` (${itemCount} item${itemCount > 1 ? "s" : ""})`;
    }

    document.getElementById("successService").textContent = serviceText;
}

// Close all modals
function closeAllModals() {
    const modals = [
        "customerModal",
        "serviceModal",
        "kiloanModal",
        "satuanModal",
        "satuanItemsModal",
        "paymentModal",
        "reviewModal",
        "successModal",
    ];

    modals.forEach((modalId) => {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add("hidden");
        }
    });
}

// Update step indicator
function updateStepIndicator(step) {
    const steps = document.querySelectorAll(".step-indicator");
    steps.forEach((indicator, index) => {
        indicator.classList.remove(
            "step-active",
            "step-completed",
            "bg-gray-200",
            "text-gray-400"
        );

        if (index < step) {
            indicator.classList.add("step-completed");
        } else if (index === step) {
            indicator.classList.add("step-active");
        } else {
            indicator.classList.add("bg-gray-200", "text-gray-400");
        }
    });
    currentStep = step;
}

// ===== CUSTOMER FUNCTIONS =====

// Filter customers from database
function filterCustomers(query) {
    const container = document.getElementById("customersList");
    if (!container) return;

    // Show loading
    container.innerHTML = `
        <div class="text-center py-4">
            <i class="fas fa-spinner fa-spin text-blue-500 text-xl"></i>
            <p class="text-gray-500 mt-2">Memuat data...</p>
        </div>
    `;

    const params = new URLSearchParams({
        search: query,
    });

    fetch(`/api/transactions/customers?${params}`, {
        method: "GET",
        headers: {
            Accept: "application/json",
            "X-Requested-With": "XMLHttpRequest",
            "X-CSRF-TOKEN": getCsrfToken(),
        },
    })
        .then((response) => {
            const contentType = response.headers.get("content-type");
            if (!contentType || !contentType.includes("application/json")) {
                return response.text().then((text) => {
                    throw new Error(
                        `Server returned HTML instead of JSON. Status: ${response.status}`
                    );
                });
            }
            return response.json();
        })
        .then((data) => {
            if (data.success) {
                const customers = data.data;

                if (customers.length === 0) {
                    container.innerHTML = `
                    <div class="text-center py-4">
                        <i class="fas fa-user-slash text-gray-400 text-xl"></i>
                        <p class="text-gray-500 mt-2">Tidak ada pelanggan ditemukan</p>
                    </div>
                `;
                    return;
                }

                container.innerHTML = customers
                    .map((customer) => {
                        const safeName = (customer.name || "Tanpa Nama")
                            .replace(/'/g, "&#39;")
                            .replace(/"/g, "&quot;");
                        const safePhone = (customer.phone || "No Phone")
                            .replace(/'/g, "&#39;")
                            .replace(/"/g, "&quot;");
                        const safeAddress = (customer.address || "-")
                            .replace(/'/g, "&#39;")
                            .replace(/"/g, "&quot;");

                        const customerId = customer.id || 0;

                        return `
                    <div class="customer-item flex items-center space-x-3 p-3 bg-white border border-gray-200 rounded-xl hover:border-blue-500 cursor-pointer"
                         onclick="selectCustomerSafe(${customerId}, '${safeName}', '${safePhone}', '${safeAddress}')">
                        <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-user text-blue-600"></i>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-semibold text-gray-800">${safeName}</h4>
                            <p class="text-sm text-gray-500">${safePhone}</p>
                            <p class="text-xs text-gray-400 truncate">${safeAddress}</p>
                        </div>
                        <i class="fas fa-chevron-right text-gray-400"></i>
                    </div>
                `;
                    })
                    .join("");
            } else {
                throw new Error(data.message || "Unknown error from server");
            }
        })
        .catch((error) => {
            console.error("Error loading customers:", error);
            container.innerHTML = `
            <div class="text-center py-4">
                <i class="fas fa-exclamation-triangle text-red-500 text-xl"></i>
                <p class="text-red-500 mt-2">Gagal memuat data pelanggan</p>
                <p class="text-xs text-red-400 mt-1">${error.message}</p>
            </div>
        `;
        });
}

// Fungsi aman untuk memilih customer
function selectCustomerSafe(id, name, phone, address) {
    const customer = {
        id: id,
        name: name,
        phone: phone,
        address: address,
    };
    selectCustomer(customer);
}

// Select customer from modal
function selectCustomer(customer) {
    transactionData.customer = customer;

    // Update selected customer preview
    const selectedCustomerElement = document.getElementById("selectedCustomer");
    const selectedCustomerPreview = document.getElementById(
        "selectedCustomerPreview"
    );

    if (selectedCustomerElement) {
        selectedCustomerElement.innerHTML = `
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-user text-blue-600"></i>
                </div>
                <div>
                    <p class="font-semibold text-gray-800">${customer.name}</p>
                    <p class="text-sm text-gray-500">${customer.phone}</p>
                </div>
            </div>
        `;
    }

    if (selectedCustomerPreview) {
        selectedCustomerPreview.classList.remove("hidden");
    }
}

// ===== ORDER TYPE FUNCTIONS =====

// Select order type (kiloan/satuan)
function selectOrderType(type) {
    transactionData.order_type = type;

    if (type === "kiloan") {
        showKiloanModal();
    } else {
        showSatuanModal();
    }
}

// Back navigation functions
function backToServiceModal() {
    closeAllModals();
    showServiceModal();
}

function backToKiloanModal() {
    closeAllModals();
    showKiloanModal();
}

function backToSatuanModal() {
    closeAllModals();
    showSatuanModal();
}

// ===== KILOAN FUNCTIONS =====

// Load kiloan services
function loadKiloanServices() {
    const container = document.getElementById("kiloanServicesGrid");
    if (!container) return;

    container.innerHTML = `
        <div class="text-center py-8">
            <i class="fas fa-spinner fa-spin text-blue-500 text-2xl"></i>
            <p class="text-gray-500 mt-2">Memuat layanan kiloan...</p>
        </div>
    `;

    fetch("/api/transactions/services?type=kiloan", {
        method: "GET",
        headers: {
            Accept: "application/json",
            "X-Requested-With": "XMLHttpRequest",
            "X-CSRF-TOKEN": getCsrfToken(),
        },
    })
        .then((response) => {
            const contentType = response.headers.get("content-type");
            if (!contentType || !contentType.includes("application/json")) {
                return response.text().then((text) => {
                    throw new Error(
                        `Server returned HTML instead of JSON. Status: ${response.status}`
                    );
                });
            }
            return response.json();
        })
        .then((data) => {
            if (data.success) {
                const services = data.data;

                if (services.length === 0) {
                    container.innerHTML = `
                    <div class="text-center py-8">
                        <i class="fas fa-concierge-bell text-gray-400 text-2xl"></i>
                        <p class="text-gray-500 mt-2">Tidak ada layanan kiloan tersedia</p>
                    </div>
                `;
                    return;
                }

                container.innerHTML = services
                    .map((service) => {
                        const price =
                            service.items && service.items[0]
                                ? service.items[0].price
                                : 0;

                        // PERBAIKAN: Gunakan fungsi wrapper untuk menghindari JSON parsing error
                        const serviceJson = JSON.stringify(service).replace(
                            /"/g,
                            "&quot;"
                        );

                        return `
                <div class="service-card bg-white rounded-xl p-4 border-2 border-gray-200 hover:border-blue-500 cursor-pointer"
                     onclick="selectKiloanServiceSafe('${serviceJson}')">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center">
                                <i class="fas fa-weight text-white"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-800">${
                                    service.name
                                }</h4>
                                <p class="text-sm text-gray-500">Rp ${formatPrice(
                                    price
                                )} / kg</p>
                            </div>
                        </div>
                        <i class="fas fa-chevron-right text-gray-400"></i>
                    </div>
                </div>
                `;
                    })
                    .join("");
            }
        })
        .catch((error) => {
            console.error("Error loading kiloan services:", error);
            container.innerHTML = `
            <div class="text-center py-8">
                <i class="fas fa-exclamation-triangle text-red-500 text-2xl"></i>
                <p class="text-red-500 mt-2">Gagal memuat layanan</p>
            </div>
        `;
        });
}

// Fungsi aman untuk memilih service kiloan
function selectKiloanServiceSafe(serviceJson) {
    try {
        const service = JSON.parse(serviceJson.replace(/&quot;/g, '"'));
        selectKiloanService(service);
    } catch (error) {
        console.error("Error parsing service JSON:", error);
        alert("Terjadi kesalahan saat memilih layanan");
    }
}

// Select kiloan service
function selectKiloanService(service) {
    transactionData.service = service;
    transactionData.service_item = service.items[0]; // Kiloan hanya punya 1 item

    document.getElementById("selectedKiloanService").innerHTML = `
        <div>
            <p class="font-semibold text-gray-800">${service.name}</p>
            <p class="text-sm text-gray-600">Rp ${formatPrice(
                service.items[0].price
            )} / kg</p>
        </div>
    `;

    document.getElementById("weightInputSection").classList.remove("hidden");
    document.getElementById("kiloanPreview").classList.remove("hidden");
    calculateKiloanTotal();
}

// Weight functions untuk kiloan
function decreaseWeight() {
    const input = document.getElementById("weightInput");
    let value = parseFloat(input.value) - 0.5;
    if (value < 0.5) value = 0.5;
    input.value = value.toFixed(1);
    calculateKiloanTotal();
}

function increaseWeight() {
    const input = document.getElementById("weightInput");
    let value = parseFloat(input.value) + 0.5;
    input.value = value.toFixed(1);
    calculateKiloanTotal();
}

function calculateKiloanTotal() {
    const weight = parseFloat(document.getElementById("weightInput").value);
    const price = transactionData.service_item.price;
    const total = weight * price;

    transactionData.weight = weight;
    transactionData.total = total;
    transactionData.items = [
        {
            service_item_id: transactionData.service_item.id,
            item_name: transactionData.service_item.name,
            quantity: weight,
            unit_price: price,
            subtotal: total,
            unit: "kg",
        },
    ];

    document.getElementById("kiloanTotal").textContent = `Rp ${formatPrice(
        total
    )}`;
}

// ===== SATUAN FUNCTIONS =====

// Load categories untuk satuan
function loadCategories() {
    const container = document.getElementById("categoriesGrid");
    if (!container) return;

    container.innerHTML = `
        <div class="col-span-2 text-center py-8">
            <i class="fas fa-spinner fa-spin text-blue-500 text-2xl"></i>
            <p class="text-gray-500 mt-2">Memuat kategori...</p>
        </div>
    `;

    fetch("/api/transactions/categories", {
        method: "GET",
        headers: {
            Accept: "application/json",
            "X-Requested-With": "XMLHttpRequest",
            "X-CSRF-TOKEN": getCsrfToken(),
        },
    })
        .then((response) => {
            const contentType = response.headers.get("content-type");
            if (!contentType || !contentType.includes("application/json")) {
                return response.text().then((text) => {
                    throw new Error(
                        `Server returned HTML instead of JSON. Status: ${response.status}`
                    );
                });
            }
            return response.json();
        })
        .then((data) => {
            if (data.success) {
                const categories = data.data;

                container.innerHTML = categories
                    .map((category) => {
                        // PERBAIKAN: Gunakan fungsi wrapper untuk menghindari JSON parsing error
                        const categoryJson = JSON.stringify(category).replace(
                            /"/g,
                            "&quot;"
                        );

                        return `
                <div class="category-card bg-white rounded-xl p-4 border-2 border-gray-200 hover:border-blue-500 cursor-pointer text-center"
                     onclick="selectCategorySafe('${categoryJson}')">
                    <div class="w-16 h-16 bg-blue-500 rounded-full flex items-center justify-center mx-auto mb-3">
                        <i class="${
                            category.icon || "fas fa-tshirt"
                        } text-white text-xl"></i>
                    </div>
                    <h4 class="font-semibold text-gray-800 mb-1">${
                        category.name
                    }</h4>
                    <p class="text-xs text-gray-500">Pilih item ${category.name.toLowerCase()}</p>
                </div>
            `;
                    })
                    .join("");
            } else {
                throw new Error(data.message || "Unknown error");
            }
        })
        .catch((error) => {
            console.error("Error loading categories:", error);
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
        console.error("Error parsing category JSON:", error);
        alert("Terjadi kesalahan saat memilih kategori");
    }
}

// Select category untuk satuan
function selectCategory(category) {
    transactionData.selected_category = category;
    showSatuanItemsModal(category);
}

// Load category items untuk satuan
function loadCategoryItems(categoryId) {
    const container = document.getElementById("satuanItemsContainer");
    if (!container) return;

    container.innerHTML = `
        <div class="text-center py-8">
            <i class="fas fa-spinner fa-spin text-blue-500 text-2xl"></i>
            <p class="text-gray-500 mt-2">Memuat items...</p>
        </div>
    `;

    fetch(`/api/transactions/categories/${categoryId}/items`, {
        method: "GET",
        headers: {
            Accept: "application/json",
            "X-Requested-With": "XMLHttpRequest",
            "X-CSRF-TOKEN": getCsrfToken(),
        },
    })
        .then((response) => {
            const contentType = response.headers.get("content-type");
            if (!contentType || !contentType.includes("application/json")) {
                return response.text().then((text) => {
                    throw new Error(
                        `Server returned HTML instead of JSON. Status: ${response.status}`
                    );
                });
            }
            return response.json();
        })
        .then((data) => {
            if (data.success) {
                const items = data.data;

                if (items.length === 0) {
                    container.innerHTML = `
                    <div class="text-center py-8">
                        <i class="fas fa-box-open text-gray-400 text-2xl"></i>
                        <p class="text-gray-500 mt-2">Tidak ada items dalam kategori ini</p>
                    </div>
                `;
                    return;
                }

                // Set service untuk satuan (gunakan service dari item pertama)
                if (items.length > 0 && items[0].service) {
                    transactionData.service = items[0].service;
                }

                container.innerHTML = items
                    .map(
                        (item) => `
                <div class="item-card bg-white border border-gray-200 rounded-xl p-4 mb-3">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex-1">
                            <h4 class="font-semibold text-gray-800">${
                                item.name
                            }</h4>
                            <p class="text-sm text-gray-500">Rp ${formatPrice(
                                item.price
                            )} / ${item.unit}</p>
                            ${
                                item.description
                                    ? `<p class="text-xs text-gray-400">${item.description}</p>`
                                    : ""
                            }
                        </div>
                        <span class="text-lg font-bold text-blue-600 item-total" id="itemTotal-${
                            item.id
                        }">Rp 0</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <button onclick="decreaseSatuanItemQuantity(${
                                item.id
                            })" 
                                    class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center hover:bg-gray-300 transition-colors">
                                <i class="fas fa-minus text-gray-600 text-xs"></i>
                            </button>
                            <span id="itemQty-${
                                item.id
                            }" class="font-semibold w-8 text-center">0</span>
                            <button onclick="increaseSatuanItemQuantity(${
                                item.id
                            })" 
                                    class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center hover:bg-gray-300 transition-colors">
                                <i class="fas fa-plus text-gray-600 text-xs"></i>
                            </button>
                        </div>
                        <span class="text-sm text-gray-500">${item.unit}</span>
                    </div>
                </div>
            `
                    )
                    .join("");

                // Reset items data
                transactionData.items = [];
                updateSatuanTotal();
            } else {
                throw new Error(data.message || "Unknown error");
            }
        })
        .catch((error) => {
            console.error("Error loading category items:", error);
            container.innerHTML = `
            <div class="text-center py-8">
                <i class="fas fa-exclamation-triangle text-red-500 text-2xl"></i>
                <p class="text-red-500 mt-2">Gagal memuat items</p>
            </div>
        `;
        });
}

// Item quantity functions untuk satuan
function increaseSatuanItemQuantity(itemId) {
    const qtyElement = document.getElementById(`itemQty-${itemId}`);
    if (!qtyElement) return;

    let quantity = parseInt(qtyElement.textContent) || 0;
    quantity++;
    qtyElement.textContent = quantity;
    updateSatuanItemCalculation(itemId, quantity);
}

function decreaseSatuanItemQuantity(itemId) {
    const qtyElement = document.getElementById(`itemQty-${itemId}`);
    if (!qtyElement) return;

    let quantity = parseInt(qtyElement.textContent) || 0;
    if (quantity > 0) {
        quantity--;
        qtyElement.textContent = quantity;
        updateSatuanItemCalculation(itemId, quantity);
    }
}

function updateSatuanItemCalculation(itemId, quantity) {
    // Get item price from displayed elements
    const itemElement = document
        .querySelector(`[onclick*="increaseSatuanItemQuantity(${itemId})"]`)
        .closest(".item-card");
    const priceText = itemElement.querySelector(
        "p.text-sm.text-gray-500"
    ).textContent;
    const price = parseInt(
        priceText.replace("Rp ", "").replace(/\./g, "").split(" /")[0]
    );

    const subtotal = quantity * price;

    // Update item total display
    const itemTotalElement = document.getElementById(`itemTotal-${itemId}`);
    if (itemTotalElement) {
        itemTotalElement.textContent = `Rp ${formatPrice(subtotal)}`;
    }

    // Update transaction data
    const existingIndex = transactionData.items.findIndex(
        (i) => i.service_item_id == itemId
    );

    if (quantity > 0) {
        if (existingIndex >= 0) {
            transactionData.items[existingIndex].quantity = quantity;
            transactionData.items[existingIndex].subtotal = subtotal;
        } else {
            transactionData.items.push({
                service_item_id: itemId,
                item_name: itemElement.querySelector("h4").textContent,
                quantity: quantity,
                unit_price: price,
                subtotal: subtotal,
                unit: "pcs",
            });
        }
    } else {
        transactionData.items = transactionData.items.filter(
            (i) => i.service_item_id != itemId
        );
    }

    updateSatuanTotal();
}

function updateSatuanTotal() {
    transactionData.total = transactionData.items.reduce(
        (sum, item) => sum + item.subtotal,
        0
    );
    const itemsTotalElement = document.getElementById("satuanItemsTotal");
    if (itemsTotalElement) {
        itemsTotalElement.textContent = `Rp ${formatPrice(
            transactionData.total
        )}`;
    }
}

// ===== PAYMENT FUNCTIONS =====

// Update payment summary
function updatePaymentSummary() {
    // Update customer name
    if (transactionData.customer) {
        document.getElementById("paymentCustomerName").textContent =
            transactionData.customer.name;
    }

    // Update service name
    if (transactionData.service) {
        document.getElementById("paymentServiceName").textContent =
            transactionData.service.name;
    }

    // Update order type
    if (transactionData.order_type) {
        const typeText =
            transactionData.order_type === "kiloan" ? "Kiloan" : "Satuan";
        document.getElementById("paymentOrderType").textContent = typeText;
    }

    // Update total
    const paymentTotal = document.getElementById("paymentTotal");
    if (paymentTotal) {
        paymentTotal.textContent = `Rp ${formatPrice(transactionData.total)}`;
    }

    // Reset payment selections
    resetPaymentSelections();
}

// Reset payment selections
function resetPaymentSelections() {
    transactionData.payment_type = "later"; // Default to bayar nanti
    transactionData.payment_method = null;

    // Reset UI
    document.querySelectorAll(".payment-type-card").forEach((card) => {
        card.classList.remove(
            "border-green-500",
            "border-yellow-500",
            "bg-green-50",
            "bg-yellow-50"
        );
    });

    document.querySelectorAll(".payment-method-card").forEach((card) => {
        card.classList.remove(
            "border-blue-500",
            "border-green-500",
            "border-purple-500",
            "bg-blue-50",
            "bg-green-50",
            "bg-purple-50"
        );
    });

    // Hide payment method section
    document.getElementById("paymentMethodSection").classList.add("hidden");

    // Select "Bayar Nanti" by default
    const laterCard = document.querySelector(
        "[onclick=\"selectPaymentType('later')\"]"
    );
    if (laterCard) {
        laterCard.classList.add("border-yellow-500", "bg-yellow-50");
    }
}

// Select payment type
function selectPaymentType(type) {
    transactionData.payment_type = type;

    // Update UI for payment type
    document.querySelectorAll(".payment-type-card").forEach((card) => {
        card.classList.remove(
            "border-green-500",
            "border-yellow-500",
            "bg-green-50",
            "bg-yellow-50"
        );
    });

    const selectedCard = document.querySelector(
        `[onclick="selectPaymentType('${type}')"]`
    );
    if (selectedCard) {
        if (type === "now") {
            selectedCard.classList.add("border-green-500", "bg-green-50");
        } else {
            selectedCard.classList.add("border-yellow-500", "bg-yellow-50");
        }
    }

    // Show/hide payment method section
    const paymentMethodSection = document.getElementById(
        "paymentMethodSection"
    );
    if (paymentMethodSection) {
        if (type === "now") {
            paymentMethodSection.classList.remove("hidden");
            // Auto select cash as default payment method
            selectPaymentMethod("cash");
        } else {
            paymentMethodSection.classList.add("hidden");
            transactionData.payment_method = null;
        }
    }
}

// Select payment method
function selectPaymentMethod(method) {
    transactionData.payment_method = method;

    // Update UI untuk selected payment method
    document.querySelectorAll(".payment-method-card").forEach((card) => {
        card.classList.remove(
            "border-blue-500",
            "border-green-500",
            "border-purple-500",
            "bg-blue-50",
            "bg-green-50",
            "bg-purple-50"
        );
    });

    const selectedCard = document.querySelector(
        `[onclick="selectPaymentMethod('${method}')"]`
    );
    if (selectedCard) {
        switch (method) {
            case "cash":
                selectedCard.classList.add("border-blue-500", "bg-blue-50");
                break;
            case "transfer":
                selectedCard.classList.add("border-green-500", "bg-green-50");
                break;
            case "qris":
                selectedCard.classList.add("border-purple-500", "bg-purple-50");
                break;
        }
    }
}

// ===== REVIEW FUNCTIONS =====

// Update review summary untuk kedua tipe
function updateReviewSummary() {
    // Update customer
    const reviewCustomer = document.getElementById("reviewCustomer");
    if (reviewCustomer && transactionData.customer) {
        reviewCustomer.innerHTML = `
            <div class="flex items-center space-x-3 bg-gray-50 rounded-xl p-3">
                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-user text-blue-600"></i>
                </div>
                <div>
                    <p class="font-semibold text-gray-800">${transactionData.customer.name}</p>
                    <p class="text-sm text-gray-500">${transactionData.customer.phone}</p>
                </div>
            </div>
        `;
    }

    // Update service & type
    const reviewService = document.getElementById("reviewService");
    if (reviewService && transactionData.service) {
        const typeBadge =
            transactionData.order_type === "kiloan"
                ? '<span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full ml-2">Kiloan</span>'
                : '<span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full ml-2">Satuan</span>';

        reviewService.innerHTML = `
            <div class="flex items-center space-x-3 bg-gray-50 rounded-xl p-3">
                <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center">
                    <i class="fas fa-tshirt text-white"></i>
                </div>
                <div>
                    <p class="font-semibold text-gray-800">${
                        transactionData.service.name
                    } ${typeBadge}</p>
                    <p class="text-sm text-gray-500">${
                        transactionData.service.description || "Layanan laundry"
                    }</p>
                </div>
            </div>
        `;
    }

    // Update items berdasarkan tipe
    const itemsContainer = document.getElementById("reviewItems");
    const itemsTitle = document.getElementById("reviewItemsTitle");

    if (itemsContainer) {
        if (transactionData.order_type === "kiloan") {
            itemsTitle.textContent = "Detail Kiloan";
            itemsContainer.innerHTML = `
                <div class="space-y-2">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Berat:</span>
                        <span class="font-semibold">${
                            transactionData.weight
                        } kg</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Harga per kg:</span>
                        <span class="font-semibold">Rp ${formatPrice(
                            transactionData.service_item.price
                        )}</span>
                    </div>
                    <div class="flex justify-between items-center pt-2 border-t border-gray-200">
                        <span class="text-gray-800 font-semibold">Subtotal:</span>
                        <span class="font-semibold">Rp ${formatPrice(
                            transactionData.total
                        )}</span>
                    </div>
                </div>
            `;
        } else {
            itemsTitle.textContent = "Items Satuan";
            if (transactionData.items.length > 0) {
                itemsContainer.innerHTML = transactionData.items
                    .map(
                        (item) => `
                    <div class="flex justify-between items-center py-2 border-b border-gray-100 last:border-b-0">
                        <div>
                            <p class="font-medium text-gray-800">${
                                item.item_name
                            }</p>
                            <p class="text-sm text-gray-500">${item.quantity} ${
                            item.unit
                        } × Rp ${formatPrice(item.unit_price)}</p>
                        </div>
                        <span class="font-semibold text-gray-800">Rp ${formatPrice(
                            item.subtotal
                        )}</span>
                    </div>
                `
                    )
                    .join("");
            } else {
                itemsContainer.innerHTML =
                    '<p class="text-center text-gray-500 py-4">Tidak ada items</p>';
            }
        }
    }

    // Update notes
    const notesSection = document.getElementById("reviewNotesSection");
    const notesElement = document.getElementById("reviewNotes");
    if (notesSection && notesElement) {
        if (transactionData.notes) {
            notesSection.classList.remove("hidden");
            notesElement.textContent = transactionData.notes;
        } else {
            notesSection.classList.add("hidden");
        }
    }

    // Update total
    const reviewTotal = document.getElementById("reviewTotal");
    if (reviewTotal) {
        reviewTotal.textContent = `Rp ${formatPrice(transactionData.total)}`;
    }
}

// ===== TRANSACTION SUBMISSION =====

// Submit transaction to backend
// ===== TRANSACTION SUBMISSION =====

// Submit transaction to backend
async function submitTransaction() {
    try {
        // Validasi data sebelum submit
        if (!transactionData.customer) {
            alert("Harap pilih pelanggan!");
            return;
        }

        if (
            transactionData.order_type === "kiloan" &&
            !transactionData.service
        ) {
            alert("Harap pilih layanan kiloan!");
            return;
        }

        if (
            transactionData.order_type === "kiloan" &&
            !transactionData.weight
        ) {
            alert("Harap masukkan berat laundry!");
            return;
        }

        if (
            transactionData.order_type === "satuan" &&
            transactionData.items.length === 0
        ) {
            alert("Harap pilih minimal 1 item!");
            return;
        }

        // Prepare payload - service_id hanya untuk kiloan, untuk satuan bisa kosong
        const payload = {
            customer_id: transactionData.customer.id,
            service_id:
                transactionData.order_type === "kiloan"
                    ? transactionData.service.id
                    : null, // Bisa null untuk satuan
            order_type: transactionData.order_type,
            items:
                transactionData.order_type === "kiloan"
                    ? [
                          {
                              service_item_id: transactionData.service_item.id,
                              quantity: transactionData.weight,
                              unit_price: transactionData.service_item.price,
                          },
                      ]
                    : transactionData.items.map((item) => ({
                          service_item_id: item.service_item_id,
                          quantity: item.quantity,
                          unit_price: item.unit_price,
                      })),
            total_amount: transactionData.total,
            weight:
                transactionData.order_type === "kiloan"
                    ? transactionData.weight
                    : null,
            payment_type: transactionData.payment_type,
            payment_method: transactionData.payment_method,
            notes: transactionData.notes,
        };

        // Show loading
        const submitBtn = document.querySelector(
            '[onclick="submitTransaction()"]'
        );
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML =
            '<i class="fas fa-spinner fa-spin"></i> Memproses...';
        submitBtn.disabled = true;

        const response = await fetch("/api/transactions", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                Accept: "application/json",
                "X-CSRF-TOKEN": getCsrfToken(),
            },
            body: JSON.stringify(payload),
        });

        const data = await response.json();

        if (data.success) {
            showSuccessModal();
            // Reset form untuk transaksi baru
            setTimeout(() => {
                closeAllModals();
                // Refresh halaman atau reset state
                if (typeof loadTransactions === "function") {
                    loadTransactions();
                }
                if (typeof loadTodaySummary === "function") {
                    loadTodaySummary();
                }
            }, 3000);
        } else {
            throw new Error(data.message || "Gagal membuat transaksi");
        }
    } catch (error) {
        console.error("Error submitting transaction:", error);
        alert("Gagal membuat transaksi: " + error.message);

        // Reset button
        const submitBtn = document.querySelector(
            '[onclick="submitTransaction()"]'
        );
        submitBtn.innerHTML =
            '<i class="fas fa-check-circle mr-2"></i>Konfirmasi & Simpan';
        submitBtn.disabled = false;
    }
}

// ===== SUCCESS MODAL FUNCTIONS =====

// Print receipt
function printReceipt() {
    alert("Fitur cetak struk akan diimplementasi!");
    closeAllModals();
    if (typeof loadTransactions === "function") {
        loadTransactions();
    }
}

// Create new transaction
function createNewTransaction() {
    closeAllModals();
    startNewTransaction();
}

// ===== HELPER FUNCTIONS =====

// Format price to Indonesian format
function formatPrice(price) {
    return new Intl.NumberFormat("id-ID").format(price);
}

// Update notes
function updateNotes() {
    const notesElement = document.getElementById("transactionNotes");
    if (notesElement) {
        transactionData.notes = notesElement.value;
    }
}

// ===== EVENT LISTENERS =====

// Initialize event listeners when DOM is loaded
document.addEventListener("DOMContentLoaded", function () {
    // Customer search
    const customerSearch = document.getElementById("customerSearch");
    if (customerSearch) {
        customerSearch.addEventListener("input", function (e) {
            filterCustomers(e.target.value);
        });
    }

    // Weight input validation
    const weightInput = document.getElementById("weightInput");
    if (weightInput) {
        weightInput.addEventListener("input", function (e) {
            let value = parseFloat(e.target.value);
            if (isNaN(value) || value < 0.5) {
                value = 0.5;
            }
            e.target.value = value.toFixed(1);
            calculateKiloanTotal();
        });
    }

    // Notes input
    const notesInput = document.getElementById("transactionNotes");
    if (notesInput) {
        notesInput.addEventListener("input", updateNotes);
    }
});

// Close modal when clicking outside
document.addEventListener("click", function (e) {
    if (e.target.classList.contains("fixed") && e.target.id.includes("Modal")) {
        closeAllModals();
    }
});

// Keyboard shortcuts
document.addEventListener("keydown", function (e) {
    if (e.key === "Escape") {
        closeAllModals();
    }
});
