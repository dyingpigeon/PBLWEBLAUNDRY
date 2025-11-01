// newTransaction.js
// Handle wizard pembuatan transaksi baru

let currentStep = 0;
let transactionData = {
    customer: null,
    service: null,
    items: [],
    notes: "",
    total: 0,
};

// Helper function untuk get CSRF token
function getCsrfToken() {
    return (
        document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute("content") || ""
    );
}

// ===== MODAL NAVIGATION FUNCTIONS =====

// Start new transaction wizard
function startNewTransaction() {
    currentStep = 0;
    transactionData = {
        customer: null,
        service: null,
        items: [],
        notes: "",
        total: 0,
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

// Show service selection modal
function showServiceModal() {
    closeAllModals();
    document.getElementById("serviceModal").classList.remove("hidden");
    updateStepIndicator(1);

    // Load services if not already loaded
    const servicesGrid = document.getElementById("servicesGrid");
    if (servicesGrid && servicesGrid.children.length === 0) {
        loadServices();
    }
}

// Show items input modal
function showItemsModal() {
    closeAllModals();
    document.getElementById("itemsModal").classList.remove("hidden");
    updateStepIndicator(2);

    // Initialize items form
    initializeItemsForm();
}

// Show review modal
function showReviewModal() {
    closeAllModals();
    document.getElementById("reviewModal").classList.remove("hidden");
    updateStepIndicator(3);

    // Update review summary
    updateReviewSummary();
}

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
    document.getElementById("successService").textContent =
        transactionData.service.name;
}

// Close all modals
function closeAllModals() {
    const modals = [
        "customerModal",
        "serviceModal",
        "itemsModal",
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
// function filterCustomers(query) {
//     const container = document.getElementById("customersList");
//     if (!container) return;

//     // Show loading
//     container.innerHTML = `
//         <div class="text-center py-4">
//             <i class="fas fa-spinner fa-spin text-blue-500 text-xl"></i>
//             <p class="text-gray-500 mt-2">Memuat data...</p>
//         </div>
//     `;

//     // Create form data for POST request
//     const formData = new FormData();
//     formData.append("search", query);
//     formData.append("_token", getCsrfToken());

//     // Fetch data from server
//     fetch('{{ route("transactions.getCustomers") }}', {
//         method: "POST",
//         body: formData,
//     })
//         .then((response) => response.json())
//         .then((data) => {
//             if (data.success) {
//                 const customers = data.data;

//                 if (customers.length === 0) {
//                     container.innerHTML = `
//                         <div class="text-center py-4">
//                             <i class="fas fa-user-slash text-gray-400 text-xl"></i>
//                             <p class="text-gray-500 mt-2">Tidak ada pelanggan ditemukan</p>
//                         </div>
//                     `;
//                     return;
//                 }

//                 container.innerHTML = customers
//                     .map((customer) => {
//                         const safeName = customer.name
//                             .replace(/'/g, "&#39;")
//                             .replace(/"/g, "&quot;");
//                         const safePhone = customer.phone
//                             .replace(/'/g, "&#39;")
//                             .replace(/"/g, "&quot;");
//                         const safeAddress = (customer.address || "")
//                             .replace(/'/g, "&#39;")
//                             .replace(/"/g, "&quot;");

//                         return `
//                         <div class="customer-item flex items-center space-x-3 p-3 bg-white border border-gray-200 rounded-xl hover:border-blue-500 cursor-pointer"
//                              onclick="selectCustomerSafe(${
//                                  customer.id
//                              }, '${safeName}', '${safePhone}', '${safeAddress}')">
//                             <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
//                                 <i class="fas fa-user text-blue-600"></i>
//                             </div>
//                             <div class="flex-1">
//                                 <h4 class="font-semibold text-gray-800">${
//                                     customer.name
//                                 }</h4>
//                                 <p class="text-sm text-gray-500">${
//                                     customer.phone
//                                 }</p>
//                                 <p class="text-xs text-gray-400 truncate">${
//                                     customer.address || "-"
//                                 }</p>
//                             </div>
//                             <i class="fas fa-chevron-right text-gray-400"></i>
//                         </div>
//                     `;
//                     })
//                     .join("");
//             } else {
//                 throw new Error(data.message);
//             }
//         })
//         .catch((error) => {
//             console.error("Error loading customers:", error);
//             container.innerHTML = `
//                 <div class="text-center py-4">
//                     <i class="fas fa-exclamation-triangle text-red-500 text-xl"></i>
//                     <p class="text-red-500 mt-2">Gagal memuat data pelanggan</p>
//                 </div>
//             `;
//         });
// }

// Filter customers from database
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

    // GUNAKAN ROUTE YANG BENAR
    const params = new URLSearchParams({
        search: query,
    });

    console.log("🟡 [DEBUG] Fetching customers with query:", query);

    // GUNAKAN URL YANG BENAR: /api/transactions/customers
    fetch(`/api/transactions/customers?${params}`, {
        method: "GET",
        headers: {
            Accept: "application/json",
            "X-Requested-With": "XMLHttpRequest",
            "X-CSRF-TOKEN": getCsrfToken(),
        },
    })
        .then((response) => {
            console.log("🟡 [DEBUG] Response status:", response.status);

            // Cek content type sebelum parse JSON
            const contentType = response.headers.get("content-type");
            if (!contentType || !contentType.includes("application/json")) {
                return response.text().then((text) => {
                    console.error(
                        "🔴 [DEBUG] Expected JSON but got:",
                        text.substring(0, 200)
                    );
                    throw new Error(
                        `Server returned HTML instead of JSON. Status: ${response.status}`
                    );
                });
            }
            return response.json();
        })
        .then((data) => {
            console.log("🟢 [DEBUG] Customers data received:", data);

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
                        // PERBAIKAN: Handle null values dengan safe default
                        const safeName = (customer.name || "Tanpa Nama")
                            .replace(/'/g, "&#39;")
                            .replace(/"/g, "&quot;");
                        const safePhone = (customer.phone || "No Phone")
                            .replace(/'/g, "&#39;")
                            .replace(/"/g, "&quot;");
                        const safeAddress = (customer.address || "-")
                            .replace(/'/g, "&#39;")
                            .replace(/"/g, "&quot;");

                        // PERBAIKAN: Validasi customer.id
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
            console.error("🔴 [DEBUG] Error loading customers:", error);
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
    console.log("🔵 [DEBUG] Customer selected (safe):", customer);
    selectCustomer(customer);
}

// Select customer from modal
function selectCustomer(customer) {
    console.log("🔵 [DEBUG] selectCustomer called with:", customer);

    transactionData.customer = customer;
    console.log("🔵 [DEBUG] transactionData after select:", transactionData);

    // Update selected customer preview
    const selectedCustomerElement = document.getElementById("selectedCustomer");
    const selectedCustomerPreview = document.getElementById(
        "selectedCustomerPreview"
    );

    console.log("🔵 [DEBUG] selectedCustomerElement:", selectedCustomerElement);
    console.log("🔵 [DEBUG] selectedCustomerPreview:", selectedCustomerPreview);

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
        console.log("🔵 [DEBUG] Updated selectedCustomer innerHTML");
    }

    if (selectedCustomerPreview) {
        selectedCustomerPreview.classList.remove("hidden");
        console.log(
            "🔵 [DEBUG] Removed hidden class from selectedCustomerPreview"
        );
    } else {
        console.log("🔴 [DEBUG] selectedCustomerPreview is NULL!");
    }
}

// Add new customer
// Add new customer
// function addNewCustomer() {
//     const customerName = prompt("Masukkan nama pelanggan baru:");
//     if (!customerName) return;

//     const customerPhone = prompt("Masukkan nomor telepon:");
//     if (!customerPhone) return;

//     const customerAddress = prompt("Masukkan alamat (opsional):") || "";

//     // Create form data
//     const formData = new FormData();
//     formData.append("name", customerName);
//     formData.append("phone", customerPhone);
//     formData.append("address", customerAddress);
//     formData.append("_token", getCsrfToken());

//     console.log(
//         "🟡 [DEBUG] Adding new customer, CSRF Token:",
//         getCsrfToken() ? "Available" : "MISSING"
//     );

//     // Save to database
//     fetch("/customers", {
//         method: "POST",
//         body: formData,
//     })
//         .then((response) => response.json())
//         .then((data) => {
//             if (data.success) {
//                 // Select the newly created customer
//                 selectCustomer(data.data);
//                 // Refresh customer list
//                 filterCustomers("");
//             } else {
//                 alert("Gagal menambah pelanggan: " + data.message);
//             }
//         })
//         .catch((error) => {
//             console.error("Error:", error);
//             alert("Terjadi kesalahan saat menambah pelanggan");
//         });
// }

// ===== SERVICE FUNCTIONS =====

// Load services from database
// Load services from database
function loadServices() {
    const container = document.getElementById("servicesGrid");
    if (!container) return;

    // Show loading
    container.innerHTML = `
        <div class="col-span-2 text-center py-8">
            <i class="fas fa-spinner fa-spin text-blue-500 text-2xl"></i>
            <p class="text-gray-500 mt-2">Memuat layanan...</p>
        </div>
    `;

    // GUNAKAN ROUTE YANG BENAR: /api/transactions/services
    fetch("/api/transactions/services", {
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
                        <div class="col-span-2 text-center py-8">
                            <i class="fas fa-concierge-bell text-gray-400 text-2xl"></i>
                            <p class="text-gray-500 mt-2">Tidak ada layanan tersedia</p>
                        </div>
                    `;
                    return;
                }

                container.innerHTML = services
                    .map((service) => {
                        const color = getServiceColor(service.name);
                        const icon = getServiceIcon(service.name);
                        const itemCount = service.items
                            ? service.items.length
                            : 0;

                        return `
                        <div class="service-card bg-white rounded-xl p-4 border-2 border-gray-200 hover:border-blue-500 cursor-pointer text-center"
                             onclick="selectServiceSafe(${
                                 service.id
                             }, '${service.name.replace(/'/g, "\\'")}', '${(
                            service.description || "Layanan laundry"
                        ).replace(/'/g, "\\'")}', ${itemCount})">
                            <div class="w-16 h-16 ${color} rounded-full flex items-center justify-center mx-auto mb-3">
                                <i class="${icon} text-white text-xl"></i>
                            </div>
                            <h4 class="font-semibold text-gray-800 mb-1">${
                                service.name
                            }</h4>
                            <p class="text-sm text-gray-500 mb-2">${
                                service.description || "Layanan laundry"
                            }</p>
                            <div class="text-xs text-gray-400">
                                ${itemCount} item
                            </div>
                        </div>
                    `;
                    })
                    .join("");
            } else {
                throw new Error(data.message);
            }
        })
        .catch((error) => {
            console.error("Error loading services:", error);
            container.innerHTML = `
                <div class="col-span-2 text-center py-8">
                    <i class="fas fa-exclamation-triangle text-red-500 text-2xl"></i>
                    <p class="text-red-500 mt-2">Gagal memuat data layanan</p>
                </div>
            `;
        });
}

// Fungsi aman untuk memilih service
function selectServiceSafe(id, name, description, itemCount) {
    const service = {
        id: id,
        name: name,
        description: description,
        items: [],
    };
    console.log("🟢 [DEBUG] Service selected (safe):", service);
    selectService(service);
}

// Select service
function selectService(service) {
    transactionData.service = service;

    // Update selected service preview
    const selectedServiceElement = document.getElementById("selectedService");
    const selectedServicePreview = document.getElementById(
        "selectedServicePreview"
    );

    if (selectedServiceElement) {
        selectedServiceElement.innerHTML = `
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 ${getServiceColor(
                    service.name
                )} rounded-full flex items-center justify-center">
                    <i class="${getServiceIcon(service.name)} text-white"></i>
                </div>
                <div>
                    <p class="font-semibold text-gray-800">${service.name}</p>
                    <p class="text-sm text-gray-500">${
                        service.description || "Layanan laundry"
                    }</p>
                </div>
            </div>
        `;
    }

    if (selectedServicePreview) {
        selectedServicePreview.classList.remove("hidden");
    }
}

// Helper function to determine service color
function getServiceColor(serviceName) {
    const colors = {
        "Cuci Setrika": "bg-green-500",
        "Cuci Kering": "bg-blue-500",
        "Setrika Saja": "bg-yellow-500",
        Express: "bg-red-500",
        Premium: "bg-purple-500",
    };
    return colors[serviceName] || "bg-blue-500";
}

// Helper function to determine service icon
function getServiceIcon(serviceName) {
    const icons = {
        "Cuci Setrika": "fas fa-tshirt",
        "Cuci Kering": "fas fa-wind",
        "Setrika Saja": "fas fa-fire",
        Express: "fas fa-bolt",
        Premium: "fas fa-crown",
    };
    return icons[serviceName] || "fas fa-tshirt";
}

// ===== ITEMS MANAGEMENT FUNCTIONS =====

// Initialize items form
async function initializeItemsForm() {
    const container = document.getElementById("itemsContainer");
    if (!container) return;

    // Show loading
    container.innerHTML = `
        <div class="text-center py-8">
            <i class="fas fa-spinner fa-spin text-blue-500 text-xl"></i>
            <p class="text-gray-500 mt-2">Memuat items...</p>
        </div>
    `;

    try {
        // GUNAKAN ROUTE YANG BENAR: /api/transactions/services
        const response = await fetch("/api/transactions/services", {
            method: "GET",
            headers: {
                Accept: "application/json",
                "X-Requested-With": "XMLHttpRequest",
                "X-CSRF-TOKEN": getCsrfToken(),
            },
        });

        const contentType = response.headers.get("content-type");
        if (!contentType || !contentType.includes("application/json")) {
            const text = await response.text();
            throw new Error(
                `Server returned HTML instead of JSON. Status: ${response.status}`
            );
        }

        const data = await response.json();

        if (data.success) {
            // Find the selected service with its items
            const selectedService = data.data.find(
                (service) => service.id == transactionData.service.id
            );

            if (
                !selectedService ||
                !selectedService.items ||
                selectedService.items.length === 0
            ) {
                container.innerHTML =
                    '<p class="text-center text-gray-500 py-8">Tidak ada items tersedia untuk layanan ini</p>';
                return;
            }

            transactionData.service.items = selectedService.items;

            // Render items form
            container.innerHTML = selectedService.items
                .map(
                    (item) => `
                <div class="item-card bg-white border border-gray-200 rounded-xl p-4 mb-3">
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <h4 class="font-semibold text-gray-800">${
                                item.name
                            }</h4>
                            <p class="text-sm text-gray-500">Rp ${formatPrice(
                                item.price
                            )}/${item.unit || "item"}</p>
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
                            <button onclick="decreaseItemQuantity(${item.id})" 
                                    class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center hover:bg-gray-300 transition-colors">
                                <i class="fas fa-minus text-gray-600 text-xs"></i>
                            </button>
                            <span id="itemQty-${
                                item.id
                            }" class="font-semibold w-8 text-center">0</span>
                            <button onclick="increaseItemQuantity(${item.id})" 
                                    class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center hover:bg-gray-300 transition-colors">
                                <i class="fas fa-plus text-gray-600 text-xs"></i>
                            </button>
                        </div>
                        <span class="text-sm text-gray-500">${
                            item.unit || "pcs"
                        }</span>
                    </div>
                </div>
            `
                )
                .join("");

            // Reset items data
            transactionData.items = [];
            updateTotal();
        } else {
            throw new Error(data.message);
        }
    } catch (error) {
        console.error("Error loading service items:", error);
        container.innerHTML = `
            <div class="text-center py-8">
                <i class="fas fa-exclamation-triangle text-red-500 text-xl"></i>
                <p class="text-red-500 mt-2">Gagal memuat data items</p>
            </div>
        `;
    }
}

// Increase item quantity
function increaseItemQuantity(itemId) {
    const qtyElement = document.getElementById(`itemQty-${itemId}`);
    if (!qtyElement) return;

    let quantity = parseInt(qtyElement.textContent) || 0;
    quantity++;
    qtyElement.textContent = quantity;

    updateItemCalculation(itemId, quantity);
}

// Decrease item quantity
function decreaseItemQuantity(itemId) {
    const qtyElement = document.getElementById(`itemQty-${itemId}`);
    if (!qtyElement) return;

    let quantity = parseInt(qtyElement.textContent) || 0;
    if (quantity > 0) {
        quantity--;
        qtyElement.textContent = quantity;
        updateItemCalculation(itemId, quantity);
    }
}

// Update item calculation
function updateItemCalculation(itemId, quantity) {
    const item = transactionData.service.items.find((i) => i.id == itemId);
    if (!item) return;

    const subtotal = quantity * item.price;

    // Update item total display
    const itemTotalElement = document.getElementById(`itemTotal-${itemId}`);
    if (itemTotalElement) {
        itemTotalElement.textContent = `Rp ${formatPrice(subtotal)}`;
    }

    // Update transaction data
    const existingIndex = transactionData.items.findIndex(
        (i) => i.id == itemId
    );

    if (quantity > 0) {
        if (existingIndex >= 0) {
            transactionData.items[existingIndex].quantity = quantity;
            transactionData.items[existingIndex].subtotal = subtotal;
        } else {
            transactionData.items.push({
                id: item.id,
                name: item.name,
                price: item.price,
                quantity: quantity,
                subtotal: subtotal,
            });
        }
    } else {
        transactionData.items = transactionData.items.filter(
            (i) => i.id != itemId
        );
    }

    updateTotal();
}

// Update total amount
function updateTotal() {
    transactionData.total = transactionData.items.reduce(
        (sum, item) => sum + item.subtotal,
        0
    );
    const itemsTotalElement = document.getElementById("itemsTotal");
    if (itemsTotalElement) {
        itemsTotalElement.textContent = `Rp ${formatPrice(
            transactionData.total
        )}`;
    }
}

// ===== REVIEW FUNCTIONS =====

// Update review summary
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

    // Update service
    const reviewService = document.getElementById("reviewService");
    if (reviewService && transactionData.service) {
        reviewService.innerHTML = `
            <div class="flex items-center space-x-3 bg-gray-50 rounded-xl p-3">
                <div class="w-10 h-10 ${getServiceColor(
                    transactionData.service.name
                )} rounded-full flex items-center justify-center">
                    <i class="${getServiceIcon(
                        transactionData.service.name
                    )} text-white"></i>
                </div>
                <div>
                    <p class="font-semibold text-gray-800">${
                        transactionData.service.name
                    }</p>
                    <p class="text-sm text-gray-500">${
                        transactionData.service.description || "Layanan laundry"
                    }</p>
                </div>
            </div>
        `;
    }

    // Update items
    const itemsContainer = document.getElementById("reviewItems");
    if (itemsContainer) {
        if (transactionData.items.length > 0) {
            itemsContainer.innerHTML = transactionData.items
                .map(
                    (item) => `
                <div class="flex justify-between items-center py-2 border-b border-gray-100 last:border-b-0">
                    <div>
                        <p class="font-medium text-gray-800">${item.name}</p>
                        <p class="text-sm text-gray-500">${
                            item.quantity
                        } × Rp ${formatPrice(item.price)}</p>
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

// ===== SUCCESS MODAL FUNCTIONS =====

// Print receipt
function printReceipt() {
    alert("Fitur cetak struk akan diimplementasi!");
    closeAllModals();
    // Refresh transactions list dari file transactionPage.js
    if (typeof loadTransactions === "function") {
        loadTransactions();
    }
}

// Create new transaction
function createNewTransaction() {
    closeAllModals();
    startNewTransaction();
}

// Submit transaction to backend
async function submitTransaction() {
    try {
        // Validasi data sebelum submit
        if (
            !transactionData.customer ||
            !transactionData.service ||
            transactionData.items.length === 0
        ) {
            alert("Harap lengkapi data pelanggan, layanan, dan items!");
            return;
        }

        const formData = {
            customer_id: transactionData.customer.id,
            service_id: transactionData.service.id,
            items: transactionData.items.map((item) => ({
                id: item.id,
                quantity: item.quantity,
                price: item.price,
            })),
            notes: transactionData.notes,
            total_amount: transactionData.total,
        };

        console.log("🟡 [DEBUG] Submitting transaction:", formData);

        const response = await fetch("/transactions", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": getCsrfToken(),
                Accept: "application/json",
            },
            body: JSON.stringify(formData),
        });

        const result = await response.json();

        if (response.ok) {
            console.log("🟢 [DEBUG] Transaction created successfully:", result);
            showSuccessModal();
            // Refresh the transactions list dari file transactionPage.js
            if (typeof loadTransactions === "function") {
                loadTransactions();
            }
            if (typeof loadTodaySummary === "function") {
                loadTodaySummary();
            }
        } else {
            console.error("🔴 [DEBUG] Transaction failed:", result);
            alert(
                "Gagal membuat transaksi: " +
                    (result.message || "Unknown error")
            );
        }
    } catch (error) {
        console.error("🔴 [DEBUG] Error submitting transaction:", error);
        alert("Terjadi kesalahan saat membuat transaksi: " + error.message);
    }
}

// ===== UTILITY FUNCTIONS =====

// Format price to Indonesian format
function formatPrice(price) {
    return new Intl.NumberFormat("id-ID").format(price);
}
