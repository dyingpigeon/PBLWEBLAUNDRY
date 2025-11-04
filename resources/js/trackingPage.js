// public/js/trackingPage.js
class TrackingApp {
    constructor() {
        this.currentOrderId = null;
        this.initEventListeners();
        this.initSwipeTabs();
        this.initPaymentEventListeners();
    }

    initEventListeners() {
        // Event delegation untuk transaction items
        const ordersList = document.getElementById("ordersList");
        if (ordersList) {
            ordersList.addEventListener("click", (e) => {
                const transactionItem = e.target.closest(".transaction-item");
                if (transactionItem) {
                    const transactionId = transactionItem.dataset.transactionId;
                    this.showStatusModal(transactionId);
                }
            });
        }

        // Search form submission dengan debounce
        const searchInput = document.querySelector('input[name="search"]');
        if (searchInput) {
            searchInput.addEventListener(
                "input",
                this.debounce((e) => {
                    if (
                        e.target.value.length === 0 ||
                        e.target.value.length >= 3
                    ) {
                        e.target.closest("form").submit();
                    }
                }, 500)
            );
        }
    }

    initSwipeTabs() {
        // Inisialisasi swipe functionality untuk tabs (jika diperlukan)
        const tabsContainer = document.querySelector(".swipeable-tabs");
        if (tabsContainer) {
            this.setupSwipeNavigation(tabsContainer);
        }
    }

    async showStatusModal(orderId) {
        try {
            this.setLoadingState(true);

            // ✅ PERBAIKAN: Gunakan route tracking yang benar
            const response = await fetch(`/tracking/${orderId}`);
            if (!response.ok) throw new Error("Failed to fetch transaction");

            const data = await response.json();

            if (data.success) {
                const transaction = data.data;
                this.currentOrderId = transaction.id;

                // Set modal content dengan data real
                this.updateModalContent(transaction);
                this.updateStatusOptions(transaction.status);

                // Show modal
                document
                    .getElementById("statusModal")
                    .classList.remove("hidden");
            } else {
                throw new Error(data.message || "Failed to load transaction");
            }
        } catch (error) {
            console.error("Error loading transaction detail:", error);
            this.showError("Gagal memuat detail transaksi");
        } finally {
            this.setLoadingState(false);
        }
    }

    updateModalContent(transaction) {
        document.getElementById("statusOrderId").value = transaction.id;
        document.getElementById("statusOrderCode").textContent =
            transaction.transaction_number;
        document.getElementById("statusCustomerName").textContent =
            transaction.customer_name || "N/A";
        document.getElementById("statusServiceName").textContent =
            transaction.service_name || "N/A";
        document.getElementById(
            "statusTotalAmount"
        ).textContent = `Rp ${this.formatPrice(transaction.total_amount)}`;

        // Update order type info
        const orderTypeElement = document.getElementById("statusOrderType");
        if (orderTypeElement) {
            orderTypeElement.textContent =
                transaction.order_type === "kiloan" ? "Kiloan" : "Satuan";
            orderTypeElement.className =
                "font-semibold " +
                (transaction.order_type === "kiloan"
                    ? "text-blue-600"
                    : "text-green-600");
        }

        // Update weight info jika kiloan
        const weightElement = document.getElementById("statusWeight");
        if (weightElement) {
            const weightSpan = weightElement.querySelector("span");
            if (transaction.order_type === "kiloan" && transaction.weight) {
                weightSpan.textContent = `${transaction.weight} kg`;
                weightElement.classList.remove("hidden");
            } else {
                weightElement.classList.add("hidden");
            }
        }

        // Update payment info
        const paymentInfoElement = document.getElementById("statusPaymentInfo");
        if (paymentInfoElement) {
            paymentInfoElement.innerHTML = `
                <div class="flex justify-between">
                    <span>Status Bayar:</span>
                    <span class="font-semibold ${
                        transaction.payment_status === "paid"
                            ? "text-green-600"
                            : transaction.payment_status === "partial"
                            ? "text-blue-600"
                            : "text-orange-600"
                    }">
                        ${this.getPaymentStatusText(transaction.payment_status)}
                    </span>
                </div>
                <div class="flex justify-between">
                    <span>Dibayar:</span>
                    <span>Rp ${this.formatPrice(
                        transaction.paid_amount || 0
                    )}</span>
                </div>
                ${
                    transaction.payment_method
                        ? `
                <div class="flex justify-between">
                    <span>Metode:</span>
                    <span class="font-semibold">${this.getPaymentMethodText(
                        transaction.payment_method
                    )}</span>
                </div>
                `
                        : ""
                }
            `;
        }
    }

    updateStatusOptions(currentStatus) {
        const statusOptions = document.getElementById("statusOptions");
        const statusOrder = ["new", "process", "ready", "done"];
        const currentIndex = statusOrder.indexOf(currentStatus);

        statusOptions.innerHTML = statusOrder
            .map((status, index) => {
                const statusInfo = this.getStatusInfo(status);
                const isCompleted = index < currentIndex;
                const isCurrent = index === currentIndex;
                const isNext = index === currentIndex + 1;
                const isDisabled =
                    index > currentIndex + 1 || status === currentStatus;

                return `
                <button 
                    class="status-option w-full text-left p-3 rounded-lg mb-2 transition-all duration-200 ${
                        isCompleted
                            ? "bg-green-50 text-green-700 border border-green-200"
                            : isCurrent
                            ? "bg-blue-50 text-blue-700 border-2 border-blue-500"
                            : isNext
                            ? "bg-orange-50 text-orange-700 border border-orange-200"
                            : "bg-gray-50 text-gray-500 border border-gray-200"
                    } ${
                    isDisabled && !isCurrent
                        ? "opacity-50 cursor-not-allowed"
                        : "hover:shadow-md"
                }" 
                    data-status="${status}"
                    ${isDisabled && !isCurrent ? "disabled" : ""}
                    onclick="trackingApp.updateOrderStatus('${status}')"
                >
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-8 h-8 rounded-full ${
                                isCompleted
                                    ? "bg-green-500"
                                    : isCurrent
                                    ? "bg-blue-500"
                                    : isNext
                                    ? "bg-orange-500"
                                    : "bg-gray-300"
                            } flex items-center justify-center mr-3">
                                ${
                                    isCompleted
                                        ? '<i class="fas fa-check text-white text-sm"></i>'
                                        : isCurrent
                                        ? '<i class="fas fa-spinner text-white text-sm"></i>'
                                        : isNext
                                        ? '<i class="fas fa-arrow-right text-white text-sm"></i>'
                                        : ""
                                }
                            </div>
                            <div>
                                <div class="font-medium">${
                                    statusInfo.text
                                }</div>
                                <div class="text-xs opacity-75">${
                                    statusInfo.description
                                }</div>
                            </div>
                        </div>
                        ${
                            isCurrent
                                ? '<i class="fas fa-check-circle text-blue-500 text-lg"></i>'
                                : ""
                        }
                    </div>
                </button>
            `;
            })
            .join("");

        // Tambahkan option untuk cancel
        if (currentStatus !== "cancelled" && currentStatus !== "done") {
            statusOptions.innerHTML += `
                <button 
                    class="status-option w-full text-left p-3 rounded-lg mb-2 transition-all duration-200 bg-red-50 text-red-700 border border-red-200 hover:bg-red-100 hover:shadow-md"
                    data-status="cancelled"
                    onclick="trackingApp.updateOrderStatus('cancelled')"
                >
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-8 h-8 rounded-full bg-red-500 flex items-center justify-center mr-3">
                                <i class="fas fa-times text-white text-sm"></i>
                            </div>
                            <div>
                                <div class="font-medium">Batalkan Pesanan</div>
                                <div class="text-xs opacity-75">Pesanan tidak dapat dilanjutkan</div>
                            </div>
                        </div>
                    </div>
                </button>
            `;
        }
    }

    // Tambahkan method ini ke class TrackingApp
    async updatePayment() {
        if (!this.currentOrderId) {
            this.showError("Tidak ada transaksi yang dipilih");
            return;
        }

        const paymentStatus = document.getElementById("paymentStatus").value;
        const paymentMethod = document.getElementById("paymentMethod").value;
        const paidAmount =
            parseFloat(document.getElementById("paidAmount").value) || 0;

        // Validasi
        if (!paymentMethod && paymentStatus !== "pending") {
            this.showError("Pilih metode pembayaran");
            return;
        }

        if (paidAmount < 0) {
            this.showError("Jumlah pembayaran tidak valid");
            return;
        }

        try {
            this.setLoadingState(true);

            const response = await fetch(
                `/tracking/${this.currentOrderId}/payment`,
                {
                    method: "PUT",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": this.getCsrfToken(),
                    },
                    body: JSON.stringify({
                        payment_status: paymentStatus,
                        payment_method: paymentMethod,
                        paid_amount: paidAmount,
                    }),
                }
            );

            const data = await response.json();

            if (data.success) {
                this.showSuccess("Status pembayaran berhasil diupdate");
                // Refresh payment info section
                this.updatePaymentInfo(data.data);
            } else {
                throw new Error(data.message || "Failed to update payment");
            }
        } catch (error) {
            console.error("Error updating payment:", error);
            this.showError("Gagal mengupdate pembayaran: " + error.message);
        } finally {
            this.setLoadingState(false);
        }
    }

    // Update payment info display
    updatePaymentInfo(transaction) {
        const paymentInfoElement = document.getElementById("statusPaymentInfo");
        if (paymentInfoElement) {
            paymentInfoElement.innerHTML = `
            <div class="flex justify-between">
                <span>Status Bayar:</span>
                <span class="font-semibold ${
                    transaction.payment_status === "paid"
                        ? "text-green-600"
                        : transaction.payment_status === "partial"
                        ? "text-blue-600"
                        : "text-orange-600"
                }">
                    ${this.getPaymentStatusText(transaction.payment_status)}
                </span>
            </div>
            <div class="flex justify-between">
                <span>Dibayar:</span>
                <span>Rp ${this.formatPrice(
                    transaction.paid_amount || 0
                )}</span>
            </div>
            ${
                transaction.payment_method
                    ? `
            <div class="flex justify-between">
                <span>Metode:</span>
                <span class="font-semibold">${this.getPaymentMethodText(
                    transaction.payment_method
                )}</span>
            </div>
            `
                    : ""
            }
            ${
                transaction.change_amount > 0
                    ? `
            <div class="flex justify-between">
                <span>Kembalian:</span>
                <span class="text-green-600">Rp ${this.formatPrice(
                    transaction.change_amount
                )}</span>
            </div>
            `
                    : ""
            }
        `;
        }

        // Update form values
        document.getElementById("paymentStatus").value =
            transaction.payment_status;
        document.getElementById("paymentMethod").value =
            transaction.payment_method || "";
        document.getElementById("paidAmount").value =
            transaction.paid_amount || 0;

        // Update change amount display
        this.calculateChangeAmount();
    }

    // Calculate change amount
    calculateChangeAmount() {
        const paidAmount =
            parseFloat(document.getElementById("paidAmount").value) || 0;
        const totalAmount =
            parseFloat(
                document
                    .getElementById("statusTotalAmount")
                    .textContent.replace(/[^\d]/g, "")
            ) || 0;
        const changeAmount = Math.max(0, paidAmount - totalAmount);

        const changeDisplay = document.getElementById("changeAmountDisplay");
        const changeAmountElement = document.getElementById("changeAmount");

        if (changeAmount > 0) {
            changeAmountElement.textContent = `Rp ${this.formatPrice(
                changeAmount
            )}`;
            changeDisplay.classList.remove("hidden");
        } else {
            changeDisplay.classList.add("hidden");
        }
    }

    // Add event listener for paid amount input
    initPaymentEventListeners() {
        const paidAmountInput = document.getElementById("paidAmount");
        if (paidAmountInput) {
            paidAmountInput.addEventListener("input", () => {
                this.calculateChangeAmount();
            });
        }
    }

    // Panggil di constructor

    async updateOrderStatus(newStatus) {
        if (!this.currentOrderId) {
            this.showError("Tidak ada transaksi yang dipilih");
            return;
        }

        // Konfirmasi untuk status tertentu
        const confirmMessages = {
            cancelled: "Apakah Anda yakin ingin membatalkan pesanan ini?",
            done: "Apakah Anda yakin pesanan sudah diambil customer?",
            ready: "Apakah Anda yakin pesanan sudah selesai dan siap diambil?",
        };

        if (confirmMessages[newStatus]) {
            if (!confirm(confirmMessages[newStatus])) {
                return;
            }
        }

        try {
            this.setLoadingState(true);

            // ✅ PERBAIKAN: Gunakan route tracking yang benar
            const response = await fetch(
                `/tracking/${this.currentOrderId}/status`,
                {
                    method: "PUT",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": this.getCsrfToken(),
                    },
                    body: JSON.stringify({
                        status: newStatus,
                        // Tambahkan cancellation reason jika status cancelled
                        ...(newStatus === "cancelled" && {
                            cancellation_reason: "Dibatalkan via tracking",
                        }),
                    }),
                }
            );

            const data = await response.json();

            if (data.success) {
                this.showSuccess(
                    `Status berhasil diupdate menjadi: ${
                        this.getStatusInfo(newStatus).text
                    }`
                );
                this.closeStatusModal();

                // Refresh page untuk update data setelah 1.5 detik
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                throw new Error(data.message || "Failed to update status");
            }
        } catch (error) {
            console.error("Error updating status:", error);
            this.showError("Gagal mengupdate status: " + error.message);
        } finally {
            this.setLoadingState(false);
        }
    }

    closeStatusModal() {
        document.getElementById("statusModal").classList.add("hidden");
        this.currentOrderId = null;

        // Reset modal content
        const statusOptions = document.getElementById("statusOptions");
        if (statusOptions) {
            statusOptions.innerHTML = "";
        }
    }

    // Helper functions
    getStatusInfo(status) {
        const statusMap = {
            new: {
                text: "Pesanan Baru",
                description: "Menunggu diproses",
            },
            process: {
                text: "Sedang Diproses",
                description: "Dalam pengerjaan",
            },
            ready: {
                text: "Siap Diambil",
                description: "Pesanan sudah selesai",
            },
            done: {
                text: "Sudah Diambil",
                description: "Pesanan selesai",
            },
            cancelled: {
                text: "Dibatalkan",
                description: "Pesanan dibatalkan",
            },
        };
        return statusMap[status] || { text: "Unknown", description: "" };
    }

    getPaymentStatusText(paymentStatus) {
        const paymentMap = {
            pending: "Belum Bayar",
            paid: "Lunas",
            partial: "DP",
        };
        return paymentMap[paymentStatus] || "Unknown";
    }

    getPaymentMethodText(paymentMethod) {
        const methodMap = {
            cash: "Tunai",
            transfer: "Transfer",
            qris: "QRIS",
        };
        return methodMap[paymentMethod] || "Unknown";
    }

    getCsrfToken() {
        return (
            document
                .querySelector('meta[name="csrf-token"]')
                ?.getAttribute("content") || ""
        );
    }

    formatPrice(price) {
        return parseFloat(price || 0).toLocaleString("id-ID");
    }

    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    setupSwipeNavigation(container) {
        // Implement swipe navigation untuk tabs mobile
        let startX = 0;
        let currentX = 0;

        container.addEventListener("touchstart", (e) => {
            startX = e.touches[0].clientX;
        });

        container.addEventListener("touchmove", (e) => {
            currentX = e.touches[0].clientX;
        });

        container.addEventListener("touchend", () => {
            const diff = startX - currentX;
            if (Math.abs(diff) > 50) {
                this.handleSwipeNavigation(diff > 0);
            }
        });
    }

    handleSwipeNavigation(isSwipeLeft) {
        const tabs = document.querySelectorAll(".status-tab");
        const currentTab = document.querySelector(
            ".status-tab.border-blue-600, .status-tab.text-blue-600"
        );
        if (!currentTab) return;

        const currentIndex = Array.from(tabs).indexOf(currentTab);
        let newIndex = isSwipeLeft ? currentIndex + 1 : currentIndex - 1;
        newIndex = Math.max(0, Math.min(newIndex, tabs.length - 1));

        if (newIndex !== currentIndex && tabs[newIndex]) {
            tabs[newIndex].click();
        }
    }

    setLoadingState(isLoading) {
        // Tambahkan loading indicator jika diperlukan
        const modal = document.getElementById("statusModal");
        if (modal) {
            if (isLoading) {
                modal.classList.add("loading");
            } else {
                modal.classList.remove("loading");
            }
        }
    }

    showError(message) {
        // Bisa diganti dengan toast notification yang lebih elegant
        this.showNotification(message, "error");
    }

    showSuccess(message) {
        // Bisa diganti dengan toast notification yang lebih elegant
        this.showNotification(message, "success");
    }

    showNotification(message, type = "info") {
        // Simple notification - bisa dienhance dengan library toast
        const alertClass =
            type === "error"
                ? "alert-error"
                : type === "success"
                ? "alert-success"
                : "alert-info";

        // Create temporary alert
        const alertDiv = document.createElement("div");
        alertDiv.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 ${alertClass} animate-fade-in`;
        alertDiv.textContent = message;

        document.body.appendChild(alertDiv);

        // Remove after 3 seconds
        setTimeout(() => {
            alertDiv.remove();
        }, 3000);
    }
}

// Initialize the app when DOM is loaded
document.addEventListener("DOMContentLoaded", function () {
    window.trackingApp = new TrackingApp();
});

// Global functions untuk dipanggil dari HTML
function closeStatusModal() {
    if (window.trackingApp) {
        window.trackingApp.closeStatusModal();
    }
}

// Tambahkan CSS untuk loading state jika diperlukan
const style = document.createElement("style");
style.textContent = `
    .loading {
        pointer-events: none;
        opacity: 0.7;
    }
    
    .animate-fade-in {
        animation: fadeIn 0.3s ease-in-out;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .alert-success {
        background-color: #d1fae5;
        color: #065f46;
        border: 1px solid #a7f3d0;
    }
    
    .alert-error {
        background-color: #fee2e2;
        color: #991b1b;
        border: 1px solid #fecaca;
    }
    
    .alert-info {
        background-color: #dbeafe;
        color: #1e40af;
        border: 1px solid #93c5fd;
    }
`;
document.head.appendChild(style);
