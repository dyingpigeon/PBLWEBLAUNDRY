// reportsPage.js
class ReportsPage {
    constructor() {
        this.currentPeriod = "week";
        this.currentDateRange = this.getCurrentWeekRange();
        this.revenueChart = null;
        this.servicesChart = null;

        this.init();
    }

    init() {
        this.initializeCharts();
        this.setupEventListeners();
        this.loadReportData();
    }

    // Initialize Charts
    initializeCharts() {
        console.log("Initializing charts...");

        // Revenue Chart
        const revenueCtx = document.getElementById("revenueChart");
        if (revenueCtx) {
            this.revenueChart = new Chart(revenueCtx, {
                type: "bar",
                data: {
                    labels: ["Loading..."],
                    datasets: [
                        {
                            label: "Pendapatan",
                            data: [0],
                            backgroundColor: "#3b82f6",
                            borderColor: "#3b82f6",
                            borderWidth: 0,
                            borderRadius: 4,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: (context) =>
                                    `Rp ${this.formatPrice(context.raw)}`,
                            },
                        },
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: "rgba(0,0,0,0.1)" },
                            ticks: {
                                callback: (value) => "Rp " + value / 1000 + "k",
                            },
                        },
                        x: { grid: { display: false } },
                    },
                },
            });
        }

        // Services Chart
        const servicesCtx = document.getElementById("servicesChart");
        if (servicesCtx) {
            this.servicesChart = new Chart(servicesCtx, {
                type: "doughnut",
                data: {
                    labels: ["Loading..."],
                    datasets: [
                        {
                            data: [100],
                            backgroundColor: ["#E5E7EB"],
                            borderWidth: 0,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: "60%",
                    plugins: {
                        legend: {
                            position: "bottom",
                            labels: { boxWidth: 12, padding: 15 },
                        },
                    },
                },
            });
        }
    }

    // Setup Event Listeners
    setupEventListeners() {
        // Period tabs
        document.querySelectorAll(".period-tab").forEach((tab) => {
            tab.addEventListener("click", (e) => {
                document
                    .querySelectorAll(".period-tab")
                    .forEach((t) => t.classList.remove("active-period"));
                e.target.classList.add("active-period");

                const periodText = e.target.textContent.trim().toLowerCase();
                this.currentPeriod = this.mapPeriodType(periodText);
                this.loadReportData();
            });
        });

        // Date range picker
        document
            .getElementById("dateRangeBtn")
            ?.addEventListener("click", () => {
                document.getElementById("dateModal").classList.remove("hidden");
            });

        // Navigation buttons
        document
            .getElementById("prevPeriod")
            ?.addEventListener("click", () => this.navigatePeriod("prev"));
        document
            .getElementById("nextPeriod")
            ?.addEventListener("click", () => this.navigatePeriod("next"));

        // Export button
        document.getElementById("exportBtn")?.addEventListener("click", () => {
            document.getElementById("exportModal").classList.remove("hidden");
        });

        // Quick date selection - PERBAIKAN 1: Update untuk modal yang diperbaiki
        document.querySelectorAll(".quick-date-btn").forEach((btn) => {
            btn.addEventListener("click", (e) => {
                const period = e.target.closest("button").dataset.period;
                this.selectQuickDate(period);
            });
        });

        // Apply custom date range - PERBAIKAN 2: Update untuk modal yang diperbaiki
        document
            .getElementById("applyCustomDate")
            ?.addEventListener("click", () => {
                this.applyCustomDateRange();
            });

        // Export format buttons
        document.querySelectorAll(".export-format-btn").forEach((btn) => {
            btn.addEventListener("click", (e) => {
                const format = e.target.closest("button").dataset.format;
                this.exportReport(format);
            });
        });

        // Modal close buttons
        document.querySelectorAll(".close-modal-btn").forEach((btn) => {
            btn.addEventListener("click", (e) => {
                const modalId = e.target.closest("button").dataset.modal;
                document.getElementById(modalId).classList.add("hidden");
            });
        });

        // Close modals on backdrop click
        document.getElementById("dateModal")?.addEventListener("click", (e) => {
            if (e.target.id === "dateModal") this.closeModal("dateModal");
        });

        document
            .getElementById("exportModal")
            ?.addEventListener("click", (e) => {
                if (e.target.id === "exportModal")
                    this.closeModal("exportModal");
            });
    }

    // Load Report Data from API - PERBAIKAN 3: Update URL ke API route
    async loadReportData() {
        this.showLoading();

        try {
            const params = new URLSearchParams({
                period: this.currentPeriod,
            });

            // Add dates for custom period
            if (
                this.currentPeriod === "custom" &&
                this.currentDateRange.start &&
                this.currentDateRange.end
            ) {
                params.append(
                    "start_date",
                    this.formatDateForAPI(this.currentDateRange.start)
                );
                params.append(
                    "end_date",
                    this.formatDateForAPI(this.currentDateRange.end)
                );
            }

            // PERBAIKAN 4: Ganti URL ke API route
            const response = await fetch(
                `/api/reports/financial-summary?${params}`,
                {
                    headers: {
                        "X-Requested-With": "XMLHttpRequest",
                        Accept: "application/json",
                    },
                }
            );

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();

            if (data.success) {
                this.updateUI(data.data);
            } else {
                throw new Error(data.message || "Gagal memuat data");
            }
        } catch (error) {
            console.error("Error loading report data:", error);
            this.showError("Gagal memuat data laporan: " + error.message);
        } finally {
            this.hideLoading();
        }
    }

    // Update UI with API data
    updateUI(apiData) {
        // Update summary cards
        this.updateSummaryCards(apiData.summary);

        // Update charts
        this.updateCharts(apiData);

        // Update transactions list
        this.updateTransactionsList(apiData.recent_transactions);

        // Update date range display
        this.updateDateRangeDisplay(apiData.date_range);
    }

    updateSummaryCards(summary) {
        const totalIncomeEl = document.getElementById("totalIncome");
        const totalOrdersEl = document.getElementById("totalOrders");

        if (totalIncomeEl) {
            totalIncomeEl.textContent = `Rp ${this.formatPrice(
                summary.total_income
            )}`;
        }
        if (totalOrdersEl) {
            totalOrdersEl.textContent = summary.total_orders;
        }

        // Update growth indicators
        const incomeGrowthEl = document.getElementById("incomeGrowth");
        const ordersGrowthEl = document.getElementById("ordersGrowth");

        if (incomeGrowthEl) {
            incomeGrowthEl.innerHTML =
                '<i class="fas fa-arrow-up mr-1"></i>0% vs periode lalu';
        }
        if (ordersGrowthEl) {
            ordersGrowthEl.innerHTML =
                '<i class="fas fa-chart-line mr-1"></i>0% vs periode lalu';
        }
    }

    updateCharts(apiData) {
        // Update revenue chart
        if (this.revenueChart && apiData.revenue_chart) {
            const revenueData = this.processRevenueChartData(
                apiData.revenue_chart
            );
            this.revenueChart.data.labels = revenueData.labels;
            this.revenueChart.data.datasets[0].data = revenueData.data;
            this.revenueChart.update();
        }

        // Update services chart
        if (this.servicesChart && apiData.services_distribution) {
            const servicesData = this.processServicesChartData(
                apiData.services_distribution
            );
            this.servicesChart.data.labels = servicesData.labels;
            this.servicesChart.data.datasets[0].data = servicesData.data;
            this.servicesChart.data.datasets[0].backgroundColor =
                servicesData.colors;
            this.servicesChart.update();
        }
    }

    processRevenueChartData(revenueChartData) {
        if (!revenueChartData || revenueChartData.length === 0) {
            return { labels: ["No Data"], data: [0] };
        }

        const labels = revenueChartData.map((item) => {
            const date = new Date(item.date);
            return date.toLocaleDateString("id-ID", { weekday: "short" });
        });

        const data = revenueChartData.map(
            (item) => parseFloat(item.daily_income) || 0
        );

        return { labels, data };
    }

    processServicesChartData(servicesData) {
        if (!servicesData || servicesData.length === 0) {
            return { labels: ["No Data"], data: [100], colors: ["#E5E7EB"] };
        }

        const labels = servicesData.map((service) => service.service_name);
        const data = servicesData.map((service) => service.order_count);
        const colors = this.generateChartColors(servicesData.length);

        return { labels, data, colors };
    }

    updateTransactionsList(transactions) {
        const container = document.getElementById("transactionsList");
        if (!container) return;

        if (!transactions || transactions.length === 0) {
            container.innerHTML = `
                <div class="p-4 text-center text-gray-500">
                    <i class="fas fa-receipt text-2xl mb-2"></i>
                    <p>Tidak ada transaksi pada periode ini</p>
                </div>
            `;
            return;
        }

        container.innerHTML = transactions
            .map(
                (transaction) => `
            <div class="p-4 border-b border-gray-100">
                <div class="flex justify-between items-start mb-2">
                    <div>
                        <h4 class="font-semibold text-gray-800">${
                            transaction.transaction_number
                        }</h4>
                        <p class="text-sm text-gray-600">${
                            transaction.customer_name
                        }</p>
                    </div>
                    <div class="text-right">
                        <p class="font-bold text-gray-800">Rp ${this.formatPrice(
                            transaction.total_amount
                        )}</p>
                        <span class="inline-block px-2 py-1 text-xs rounded-full ${this.getStatusBadgeClass(
                            transaction.payment_status
                        )}">
                            ${this.translatePaymentStatus(
                                transaction.payment_status
                            )}
                        </span>
                    </div>
                </div>
                
                <div class="flex items-center justify-between text-sm text-gray-600">
                    <div class="flex items-center space-x-4">
                        <span class="flex items-center">
                            <i class="fas fa-tag mr-1 text-xs"></i>
                            ${transaction.service_name}
                        </span>
                        <span class="flex items-center">
                            <i class="fas fa-calendar mr-1 text-xs"></i>
                            ${new Date(
                                transaction.created_at
                            ).toLocaleDateString("id-ID")}
                        </span>
                    </div>
                    <span class="text-xs ${this.getStatusColor(
                        transaction.status
                    )}">
                        ${this.translateStatus(transaction.status)}
                    </span>
                </div>
            </div>
        `
            )
            .join("");
    }

    updateDateRangeDisplay(dateRange) {
        const rangeText = document.getElementById("dateRangeText");
        if (!rangeText) return;

        if (this.currentPeriod === "week") {
            rangeText.textContent = "Minggu Ini";
        } else if (this.currentPeriod === "month") {
            rangeText.textContent = "Bulan Ini";
        } else if (this.currentPeriod === "quarter") {
            rangeText.textContent = "3 Bulan Ini";
        } else {
            const start = new Date(dateRange.start);
            const end = new Date(dateRange.end);
            rangeText.textContent = `${this.formatDateDisplay(
                start
            )} - ${this.formatDateDisplay(end)}`;
        }
    }

    // Date Range Functions
    getCurrentWeekRange() {
        const now = new Date();
        const start = new Date(now);
        start.setDate(now.getDate() - now.getDay());

        const end = new Date(now);
        end.setDate(now.getDate() + (6 - now.getDay()));

        return { start, end };
    }

    selectQuickDate(period) {
        const now = new Date();
        let start, end;

        switch (period) {
            case "today":
                start = end = new Date(now);
                this.currentPeriod = "custom";
                break;
            case "week":
                start = new Date(now);
                start.setDate(now.getDate() - now.getDay());
                end = new Date(now);
                end.setDate(now.getDate() + (6 - now.getDay()));
                this.currentPeriod = "week";
                break;
            case "month":
                start = new Date(now.getFullYear(), now.getMonth(), 1);
                end = new Date(now.getFullYear(), now.getMonth() + 1, 0);
                this.currentPeriod = "month";
                break;
            case "last_month":
                start = new Date(now.getFullYear(), now.getMonth() - 1, 1);
                end = new Date(now.getFullYear(), now.getMonth(), 0);
                this.currentPeriod = "custom";
                break;
        }

        this.currentDateRange = { start, end };
        this.loadReportData();
        this.closeModal("dateModal");
    }

    applyCustomDateRange() {
        const startInput = document.getElementById("customStartDate");
        const endInput = document.getElementById("customEndDate");

        if (!startInput?.value || !endInput?.value) {
            this.showError("Harap pilih tanggal mulai dan tanggal akhir");
            return;
        }

        const startDate = new Date(startInput.value);
        const endDate = new Date(endInput.value);

        if (startDate > endDate) {
            this.showError("Tanggal mulai tidak boleh setelah tanggal akhir");
            return;
        }

        this.currentDateRange = { start: startDate, end: endDate };
        this.currentPeriod = "custom";
        this.loadReportData();
        this.closeModal("dateModal");
    }

    navigatePeriod(direction) {
        const { start, end } = this.currentDateRange;
        const diffTime = end - start;
        const diffDays = diffTime / (1000 * 60 * 60 * 24);

        if (direction === "prev") {
            start.setDate(start.getDate() - diffDays - 1);
            end.setDate(end.getDate() - diffDays - 1);
        } else {
            start.setDate(start.getDate() + diffDays + 1);
            end.setDate(end.getDate() + diffDays + 1);
        }

        this.loadReportData();
    }

    // Export Function - PERBAIKAN 5: Update URL ke API route
    // Export Function - PERBAIKAN: Handle semua format export
    async exportReport(format) {
        try {
            this.showLoading();

            const formData = new FormData();
            formData.append("period", this.currentPeriod);
            formData.append("format", format);

            if (
                this.currentPeriod === "custom" &&
                this.currentDateRange.start &&
                this.currentDateRange.end
            ) {
                formData.append(
                    "start_date",
                    this.formatDateForAPI(this.currentDateRange.start)
                );
                formData.append(
                    "end_date",
                    this.formatDateForAPI(this.currentDateRange.end)
                );
            }

            // PERBAIKAN: Ganti URL ke API route
            const response = await fetch("/api/reports/export", {
                method: "POST",
                body: formData,
                headers: {
                    "X-CSRF-TOKEN": this.getCsrfToken(),
                    "X-Requested-With": "XMLHttpRequest",
                },
            });

            if (response.ok) {
                // PERBAIKAN: Handle semua format file download
                const blob = await response.blob();
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement("a");
                a.href = url;

                // Tentukan nama file berdasarkan format
                const timestamp = new Date().toISOString().split("T")[0];
                let filename = `laporan-keuangan-${timestamp}`;

                switch (format) {
                    case "csv":
                        filename += ".csv";
                        break;
                    case "pdf":
                        filename += ".pdf";
                        break;
                    case "excel":
                        filename += ".xlsx";
                        break;
                    default:
                        filename += ".csv";
                }

                a.download = filename;
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                window.URL.revokeObjectURL(url);

                this.showToast("Laporan berhasil diexport");
            } else {
                // Handle error response
                const errorData = await response.json();
                throw new Error(errorData.message || "Export gagal");
            }
        } catch (error) {
            console.error("Error exporting report:", error);
            this.showError("Gagal mengekspor laporan: " + error.message);
        } finally {
            this.hideLoading();
            this.closeModal("exportModal");
        }
    }

    // Utility Functions
    mapPeriodType(periodText) {
        const periodMap = {
            minggu: "week",
            bulan: "month",
            "3 bulan": "quarter",
            custom: "custom",
        };
        return periodMap[periodText] || "week";
    }

    formatPrice(price) {
        return new Intl.NumberFormat("id-ID").format(price);
    }

    formatDateForAPI(date) {
        return date.toISOString().split("T")[0];
    }

    formatDateDisplay(date) {
        return date.toLocaleDateString("id-ID", {
            day: "numeric",
            month: "short",
        });
    }

    // PERBAIKAN 7: Update status translation sesuai dengan database
    translateStatus(status) {
        const statusMap = {
            new: "Baru",
            process: "Diproses",
            ready: "Siap Diambil",
            done: "Selesai",
            cancelled: "Dibatalkan",
        };
        return statusMap[status] || status;
    }

    translatePaymentStatus(paymentStatus) {
        const translations = {
            pending: "Belum Bayar",
            paid: "Lunas",
            partial: "DP",
            overpaid: "Kelebihan",
        };
        return translations[paymentStatus] || paymentStatus;
    }

    getStatusBadgeClass(paymentStatus) {
        const classes = {
            paid: "bg-green-100 text-green-800",
            pending: "bg-yellow-100 text-yellow-800",
            partial: "bg-blue-100 text-blue-800",
        };
        return classes[paymentStatus] || "bg-gray-100 text-gray-800";
    }

    // PERBAIKAN 8: Update status color sesuai dengan database
    getStatusColor(status) {
        const colors = {
            done: "text-green-600",
            ready: "text-green-600",
            process: "text-orange-600",
            new: "text-yellow-600",
            cancelled: "text-red-600",
        };
        return colors[status] || "text-gray-600";
    }

    generateChartColors(count) {
        const colors = [
            "#3b82f6",
            "#10b981",
            "#f59e0b",
            "#8b5cf6",
            "#ef4444",
            "#06b6d4",
            "#84cc16",
            "#f97316",
        ];
        return colors.slice(0, count);
    }

    getCsrfToken() {
        return (
            document
                .querySelector('meta[name="csrf-token"]')
                ?.getAttribute("content") || ""
        );
    }

    showLoading() {
        const transactionsList = document.getElementById("transactionsList");
        if (transactionsList) {
            transactionsList.innerHTML = `
                <div class="p-4 text-center text-gray-500">
                    <i class="fas fa-spinner fa-spin text-xl mb-2"></i>
                    <p>Memuat data transaksi...</p>
                </div>
            `;
        }
    }

    hideLoading() {
        // Loading state akan diganti dengan data actual
    }

    showError(message) {
        const transactionsList = document.getElementById("transactionsList");
        if (transactionsList) {
            transactionsList.innerHTML = `
                <div class="p-4 text-center text-red-500">
                    <i class="fas fa-exclamation-triangle text-xl mb-2"></i>
                    <p>${message}</p>
                    <button onclick="reportsPage.loadReportData()" class="mt-2 px-4 py-2 bg-blue-500 text-white rounded-lg text-sm">
                        Coba Lagi
                    </button>
                </div>
            `;
        }
    }

    showToast(message) {
        const toast = document.createElement("div");
        toast.className =
            "fixed top-4 right-4 px-6 py-3 rounded-lg text-white font-medium z-50 bg-green-500";
        toast.textContent = message;
        document.body.appendChild(toast);

        setTimeout(() => {
            toast.remove();
        }, 3000);
    }

    closeModal(modalId) {
        document.getElementById(modalId).classList.add("hidden");
    }
}

// Initialize Reports Page
document.addEventListener("DOMContentLoaded", function () {
    window.reportsPage = new ReportsPage();
});
