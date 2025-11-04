// Settings Page JavaScript
class SettingsApp {
    constructor() {
        console.log("🚀 SettingsApp constructor called");
        this.init();
    }

    init() {
        console.log("🔧 SettingsApp init started");
        this.loadBusinessSettings();
        this.loadBusinessHours();
        this.loadReceiptSettings();
        this.loadNotificationSettings();
        this.attachEventListeners();
        this.attachReceiptEventListeners(); // Tambahkan ini
        this.attachHoursEventListeners();
        this.attachHoursEventListeners();
        console.log("✅ SettingsApp init completed");
    }

    // Modal Functions
    openModal(modalId) {
        console.log(`📂 openModal called for: ${modalId}`);
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove("hidden");
            document.body.style.overflow = "hidden";
            console.log(`✅ Modal ${modalId} opened`);
        } else {
            console.error(`❌ Modal ${modalId} not found`);
        }
    }

    closeModal(modalId) {
        console.log(`📂 closeModal called for: ${modalId}`);
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add("hidden");
            document.body.style.overflow = "auto";
            console.log(`✅ Modal ${modalId} closed`);
        } else {
            console.error(`❌ Modal ${modalId} not found`);
        }
    }

    // Attach event listeners
    attachEventListeners() {
        console.log("🔧 attachEventListeners called");

        // Close modals when clicking outside
        document.addEventListener("click", (event) => {
            const modals = document.querySelectorAll('[id$="Modal"]');
            modals.forEach((modal) => {
                if (event.target === modal) {
                    console.log("🖱️ Click outside modal detected");
                    this.closeModal(modal.id);
                }
            });
        });

        // Close modals with Escape key
        document.addEventListener("keydown", (event) => {
            if (event.key === "Escape") {
                console.log("⌨️ Escape key pressed");
                const modals = document.querySelectorAll('[id$="Modal"]');
                modals.forEach((modal) => {
                    this.closeModal(modal.id);
                });
            }
        });

        // Notification toggles
        document.querySelectorAll(".notification-toggle").forEach((toggle) => {
            toggle.addEventListener("change", (e) => {
                console.log(
                    "🔔 Notification toggle changed:",
                    e.target.dataset.setting,
                    e.target.checked
                );
                this.saveNotificationSettings();
            });
        });

        // Auto print toggle
        const autoPrintToggle = document.querySelector(
            '.setting-toggle[data-setting="auto_print"]'
        );
        if (autoPrintToggle) {
            autoPrintToggle.addEventListener("change", (e) => {
                console.log("🖨️ Auto print toggle changed:", e.target.checked);
                this.saveReceiptSettings();
            });
        }

        console.log("✅ Event listeners attached");
    }

    // Show loading state
    showLoading(button) {
        console.log("⏳ showLoading called for button");
        const originalText = button.innerHTML;
        button.innerHTML =
            '<i class="fas fa-spinner fa-spin mr-2"></i>Loading...';
        button.disabled = true;
        return originalText;
    }

    // Hide loading state
    hideLoading(button, originalText) {
        console.log("✅ hideLoading called for button");
        button.innerHTML = originalText;
        button.disabled = false;
    }

    // Show notification
    showNotification(message, type = "success") {
        console.log(`📢 showNotification called: ${message} (${type})`);

        // Remove existing notification
        const existingNotification = document.querySelector(
            ".settings-notification"
        );
        if (existingNotification) {
            existingNotification.remove();
        }

        const notification = document.createElement("div");
        notification.className = `settings-notification fixed top-4 right-4 z-50 px-6 py-3 rounded-lg shadow-lg transition-all duration-300 ${
            type === "success"
                ? "bg-green-500 text-white"
                : "bg-red-500 text-white"
        }`;
        notification.innerHTML = `
            <div class="flex items-center space-x-2">
                <i class="fas ${
                    type === "success"
                        ? "fa-check-circle"
                        : "fa-exclamation-circle"
                }"></i>
                <span>${message}</span>
            </div>
        `;

        document.body.appendChild(notification);
        console.log("✅ Notification shown");

        // Auto remove after 3 seconds
        setTimeout(() => {
            notification.style.opacity = "0";
            notification.style.transform = "translateY(-10px)";
            setTimeout(() => {
                notification.remove();
                console.log("🗑️ Notification removed");
            }, 300);
        }, 3000);
    }

    // Business Settings
    async loadBusinessSettings() {
        console.log("📂 loadBusinessSettings called");
        try {
            console.log(
                "🌐 Fetching business settings from /settings/business"
            );
            const response = await fetch("/settings/business");
            console.log("📡 Response status:", response.status);
            const data = await response.json();
            console.log("📊 Business settings data:", data);

            if (response.ok) {
                // Populate form fields if they exist
                const businessName = document.getElementById("businessName");
                const businessAddress =
                    document.getElementById("businessAddress");
                const businessPhone = document.getElementById("businessPhone");
                const businessEmail = document.getElementById("businessEmail");

                console.log("📝 Populating business form fields");
                if (businessName && data.business_name) {
                    businessName.value = data.business_name;
                    console.log("✅ businessName set to:", data.business_name);
                }
                if (businessAddress && data.business_address) {
                    businessAddress.value = data.business_address;
                    console.log("✅ businessAddress set");
                }
                if (businessPhone && data.business_phone) {
                    businessPhone.value = data.business_phone;
                    console.log(
                        "✅ businessPhone set to:",
                        data.business_phone
                    );
                }
                if (businessEmail && data.business_email) {
                    businessEmail.value = data.business_email;
                    console.log(
                        "✅ businessEmail set to:",
                        data.business_email
                    );
                }
                console.log("✅ Business settings loaded successfully");
            } else {
                console.error("❌ Failed to load business settings:", data);
            }
        } catch (error) {
            console.error("❌ Error loading business settings:", error);
        }
    }

    async saveBusinessSettings() {
        console.log("💾 saveBusinessSettings called");

        try {
            // Debug: Cek elemen exists
            const businessName = document.getElementById("businessName");
            const businessAddress = document.getElementById("businessAddress");
            const businessPhone = document.getElementById("businessPhone");
            const businessEmail = document.getElementById("businessEmail");

            console.log("🔍 Element check:", {
                businessName: businessName?.value,
                businessAddress: businessAddress?.value,
                businessPhone: businessPhone?.value,
                businessEmail: businessEmail?.value,
            });

            if (
                !businessName ||
                !businessAddress ||
                !businessPhone ||
                !businessEmail
            ) {
                console.error("❌ One or more form elements not found");
                this.showNotification("Form tidak lengkap", "error");
                return;
            }

            const formData = {
                business_name: businessName.value,
                business_address: businessAddress.value,
                business_phone: businessPhone.value,
                business_email: businessEmail.value,
            };

            console.log("📤 Sending business data:", formData);

            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            console.log("🔑 CSRF Token exists:", !!csrfToken);

            if (!csrfToken) {
                console.error("❌ CSRF token not found");
                this.showNotification(
                    "Token keamanan tidak ditemukan",
                    "error"
                );
                return;
            }

            const response = await fetch("/settings/business", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": csrfToken.getAttribute("content"),
                    "X-Requested-With": "XMLHttpRequest",
                },
                body: JSON.stringify(formData),
            });

            console.log("📥 Response status:", response.status);

            const result = await response.json();
            console.log("📥 Business save response:", result);

            if (result.success) {
                this.showNotification("Pengaturan bisnis berhasil disimpan");
                this.closeModal("businessModal");
            } else {
                this.showNotification(
                    result.message || "Gagal menyimpan pengaturan",
                    "error"
                );
            }
        } catch (error) {
            console.error("❌ Error saving business settings:", error);
            this.showNotification(
                "Terjadi kesalahan saat menyimpan: " + error.message,
                "error"
            );
        }
    }

    // Business Hours
    async loadBusinessHours() {
        console.log("📂 loadBusinessHours called");
        try {
            console.log("🌐 Fetching business hours from /settings/hours");
            const response = await fetch("/settings/hours");
            console.log("📡 Response status:", response.status);

            if (response.ok) {
                const data = await response.json();
                console.log("📊 Business hours data:", data);

                data.forEach((day) => {
                    const closedCheckbox = document.getElementById(
                        `${day.day}Closed`
                    );
                    const openInput = document.getElementById(`${day.day}Open`);
                    const closeInput = document.getElementById(
                        `${day.day}Close`
                    );

                    console.log(
                        `📝 Setting ${day.day}: closed=${day.is_closed}, open=${day.open_time}, close=${day.close_time}`
                    );

                    if (closedCheckbox) {
                        closedCheckbox.checked = day.is_closed;
                        console.log(
                            `✅ ${day.day} closed checkbox set to:`,
                            day.is_closed
                        );
                    }
                    if (openInput) {
                        openInput.value = day.open_time.substring(0, 5);
                        console.log(
                            `✅ ${day.day} open time set to:`,
                            day.open_time.substring(0, 5)
                        );
                    }
                    if (closeInput) {
                        closeInput.value = day.close_time.substring(0, 5);
                        console.log(
                            `✅ ${day.day} close time set to:`,
                            day.close_time.substring(0, 5)
                        );
                    }

                    // Update disabled state setelah set values
                    this.toggleTimeInputs(day.day);
                });
                console.log("✅ Business hours loaded successfully");
            } else {
                console.error(
                    "❌ Failed to load business hours:",
                    response.status
                );
            }
        } catch (error) {
            console.error("❌ Error loading business hours:", error);
        }
    }

    async saveBusinessHours() {
        console.log("💾 saveBusinessHours called");
        const form = document.querySelector("#hoursModal form");
        if (!form) {
            console.error("❌ Hours form not found");
            return;
        }

        const submitButton = form.querySelector('button[type="submit"]');
        const originalText = this.showLoading(submitButton);

        try {
            const days = [
                "monday",
                "tuesday",
                "wednesday",
                "thursday",
                "friday",
                "saturday",
                "sunday",
            ];
            const formData = {};

            days.forEach((day) => {
                const closedCheckbox = document.getElementById(`${day}Closed`);
                const openInput = document.getElementById(`${day}Open`);
                const closeInput = document.getElementById(`${day}Close`);

                formData[`${day}_closed`] = closedCheckbox
                    ? closedCheckbox.checked
                    : false;
                formData[`${day}_open`] = openInput ? openInput.value : "08:00";
                formData[`${day}_close`] = closeInput
                    ? closeInput.value
                    : "20:00";

                console.log(
                    `📝 ${day}: closed=${formData[`${day}_closed`]}, open=${
                        formData[`${day}_open`]
                    }, close=${formData[`${day}_close`]}`
                );
            });

            console.log("📤 Sending hours data:", formData);

            const response = await fetch("/settings/hours", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document
                        .querySelector('meta[name="csrf-token"]')
                        .getAttribute("content"),
                },
                body: JSON.stringify(formData),
            });

            const result = await response.json();
            console.log("📥 Hours save response:", result);

            if (result.success) {
                this.showNotification("Jam operasional berhasil disimpan");
                this.closeModal("hoursModal");
            } else {
                this.showNotification(
                    result.message || "Gagal menyimpan jam operasional",
                    "error"
                );
            }
        } catch (error) {
            console.error("❌ Error saving business hours:", error);
            this.showNotification("Terjadi kesalahan saat menyimpan", "error");
        } finally {
            this.hideLoading(submitButton, originalText);
        }
    }

    // Receipt Settings - TAMBAHKAN METHOD INI DI DALAM CLASS
    // Update receipt preview
    updateReceiptPreview() {
        console.log("📝 updateReceiptPreview called");
        const preview = document.getElementById("receiptPreview");
        if (!preview) return;

        const header =
            document.getElementById("receiptHeader")?.value ||
            "LAUNDRYKU\nJl. Contoh No. 123, Jakarta\nTelp: 081234567890";
        const footer =
            document.getElementById("receiptFooter")?.value ||
            "Terima kasih atas kunjungan Anda\n*** Barang yang sudah dicuci tidak dapat ditukar ***";
        const showLogo = document.getElementById("showLogo")?.checked || false;

        const previewHTML = `
            <div class="text-center font-bold">${
                header.split("\n")[0] || "LAUNDRYKU"
            }</div>
            ${header
                .split("\n")
                .slice(1)
                .map((line) => `<div class="text-center">${line}</div>`)
                .join("")}
            ${showLogo ? '<div class="text-center my-2">[LOGO]</div>' : ""}
            <div class="border-t border-dashed border-gray-300 my-2"></div>
            <div>No: LAUNDRY-0012</div>
            <div>Tanggal: 15 Jan 2024</div>
            <div>Customer: Budi Santoso</div>
            <div class="border-t border-dashed border-gray-300 my-2"></div>
            <div>Cuci Setrika - 2 kg x Rp 20.000 = Rp 40.000</div>
            <div class="border-t border-dashed border-gray-300 my-2"></div>
            <div class="font-bold">TOTAL: Rp 40.000</div>
            <div class="border-t border-dashed border-gray-300 my-2"></div>
            <div class="text-center text-xs">${footer.replace(
                /\n/g,
                "<br>"
            )}</div>
        `;

        preview.innerHTML = previewHTML;
        console.log("✅ Receipt preview updated");
    }

    // Attach event listeners untuk real-time preview
    attachReceiptEventListeners() {
        console.log("🔧 attachReceiptEventListeners called");

        const receiptHeader = document.getElementById("receiptHeader");
        const receiptFooter = document.getElementById("receiptFooter");
        const showLogo = document.getElementById("showLogo");

        if (receiptHeader) {
            receiptHeader.addEventListener("input", () =>
                this.updateReceiptPreview()
            );
        }
        if (receiptFooter) {
            receiptFooter.addEventListener("input", () =>
                this.updateReceiptPreview()
            );
        }
        if (showLogo) {
            showLogo.addEventListener("change", () =>
                this.updateReceiptPreview()
            );
        }

        console.log("✅ Receipt event listeners attached");
    }

    async loadReceiptSettings() {
        console.log("📂 loadReceiptSettings called");
        try {
            console.log("🌐 Fetching receipt settings from /settings/receipt");
            const response = await fetch("/settings/receipt");
            console.log("📡 Response status:", response.status);

            if (response.ok) {
                const data = await response.json();
                console.log("📊 Receipt settings data:", data);

                const receiptHeader = document.getElementById("receiptHeader");
                const receiptFooter = document.getElementById("receiptFooter");
                const showLogo = document.getElementById("showLogo");
                const autoPrint = document.getElementById("autoPrint");

                console.log("📝 Populating receipt form fields");
                if (receiptHeader && data.receipt_header) {
                    receiptHeader.value = data.receipt_header;
                    console.log("✅ receiptHeader set");
                }
                if (receiptFooter && data.receipt_footer) {
                    receiptFooter.value = data.receipt_footer;
                    console.log("✅ receiptFooter set");
                }
                if (showLogo) {
                    showLogo.checked = data.show_logo || false;
                    console.log("✅ showLogo set to:", showLogo.checked);
                }
                if (autoPrint) {
                    autoPrint.checked = data.auto_print || false;
                    console.log("✅ autoPrint set to:", autoPrint.checked);
                }

                // Update preview setelah load data
                this.updateReceiptPreview();
                console.log("✅ Receipt settings loaded successfully");
            } else {
                console.error(
                    "❌ Failed to load receipt settings:",
                    response.status
                );
                // Set default values jika gagal load
                this.setDefaultReceiptValues();
            }
        } catch (error) {
            console.error("❌ Error loading receipt settings:", error);
            this.setDefaultReceiptValues();
        }
    }

    // Set default values untuk struk
    setDefaultReceiptValues() {
        console.log("⚙️ Setting default receipt values");
        const receiptHeader = document.getElementById("receiptHeader");
        const receiptFooter = document.getElementById("receiptFooter");
        const showLogo = document.getElementById("showLogo");
        const autoPrint = document.getElementById("autoPrint");

        if (receiptHeader)
            receiptHeader.value =
                "LAUNDRYKU\nJl. Contoh No. 123, Jakarta\nTelp: 081234567890";
        if (receiptFooter)
            receiptFooter.value =
                "Terima kasih atas kunjungan Anda\n*** Barang yang sudah dicuci tidak dapat ditukar ***";
        if (showLogo) showLogo.checked = true;
        if (autoPrint) autoPrint.checked = false;

        this.updateReceiptPreview();
    }

    async saveReceiptSettings() {
        console.log("💾 saveReceiptSettings called");
        const form = document.querySelector("#receiptModal form");
        if (!form) {
            console.error("❌ Receipt form not found");
            return;
        }

        const submitButton = form.querySelector('button[type="submit"]');
        const originalText = this.showLoading(submitButton);

        try {
            const receiptHeader = document.getElementById("receiptHeader");
            const receiptFooter = document.getElementById("receiptFooter");
            const showLogo = document.getElementById("showLogo");
            const autoPrint = document.getElementById("autoPrint");

            console.log("🔍 Receipt form values:", {
                receiptHeader: receiptHeader?.value,
                receiptFooter: receiptFooter?.value,
                showLogo: showLogo?.checked,
                autoPrint: autoPrint?.checked,
            });

            if (!receiptHeader || !receiptFooter || !showLogo || !autoPrint) {
                console.error("❌ One or more receipt form elements not found");
                this.showNotification("Form struk tidak lengkap", "error");
                return;
            }

            const formData = {
                receipt_header: receiptHeader.value,
                receipt_footer: receiptFooter.value,
                show_logo: showLogo.checked,
                auto_print: autoPrint.checked,
            };

            console.log("📤 Sending receipt data:", formData);

            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            if (!csrfToken) {
                console.error("❌ CSRF token not found");
                this.showNotification(
                    "Token keamanan tidak ditemukan",
                    "error"
                );
                return;
            }

            const response = await fetch("/settings/receipt", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": csrfToken.getAttribute("content"),
                    "X-Requested-With": "XMLHttpRequest",
                },
                body: JSON.stringify(formData),
            });

            console.log("📥 Response status:", response.status);

            const result = await response.json();
            console.log("📥 Receipt save response:", result);

            if (result.success) {
                this.showNotification("Pengaturan struk berhasil disimpan");
                this.closeModal("receiptModal");
            } else {
                this.showNotification(
                    result.message || "Gagal menyimpan pengaturan struk",
                    "error"
                );
            }
        } catch (error) {
            console.error("❌ Error saving receipt settings:", error);
            this.showNotification(
                "Terjadi kesalahan saat menyimpan: " + error.message,
                "error"
            );
        } finally {
            this.hideLoading(submitButton, originalText);
        }
    }

    // Notification Settings
    async loadNotificationSettings() {
        console.log("📂 loadNotificationSettings called");
        try {
            console.log(
                "🌐 Fetching notification settings from /settings/notifications"
            );
            const response = await fetch("/settings/notifications");
            console.log("📡 Response status:", response.status);
            const data = await response.json();
            console.log("📊 Notification settings data:", data);

            if (response.ok) {
                const newOrderToggle = document.querySelector(
                    '.notification-toggle[data-setting="new_order"]'
                );
                const statusChangeToggle = document.querySelector(
                    '.notification-toggle[data-setting="status_change"]'
                );

                console.log("📝 Setting notification toggles");
                if (newOrderToggle) {
                    newOrderToggle.checked =
                        data.new_order_notification || true;
                    console.log(
                        "✅ new_order_notification set to:",
                        newOrderToggle.checked
                    );
                }
                if (statusChangeToggle) {
                    statusChangeToggle.checked =
                        data.status_change_notification || true;
                    console.log(
                        "✅ status_change_notification set to:",
                        statusChangeToggle.checked
                    );
                }
                console.log("✅ Notification settings loaded successfully");
            } else {
                console.error("❌ Failed to load notification settings:", data);
            }
        } catch (error) {
            console.error("❌ Error loading notification settings:", error);
        }
    }

    async saveNotificationSettings() {
        console.log("💾 saveNotificationSettings called");
        try {
            const formData = {
                new_order_notification: document.querySelector(
                    '.notification-toggle[data-setting="new_order"]'
                ).checked,
                status_change_notification: document.querySelector(
                    '.notification-toggle[data-setting="status_change"]'
                ).checked,
            };

            console.log("📤 Sending notification data:", formData);

            const response = await fetch("/settings/notifications", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document
                        .querySelector('meta[name="csrf-token"]')
                        .getAttribute("content"),
                },
                body: JSON.stringify(formData),
            });

            const result = await response.json();
            console.log("📥 Notification save response:", result);

            if (result.success) {
                this.showNotification(
                    "Pengaturan notifikasi berhasil disimpan"
                );
            } else {
                this.showNotification(
                    result.message || "Gagal menyimpan pengaturan notifikasi",
                    "error"
                );
            }
        } catch (error) {
            console.error("❌ Error saving notification settings:", error);
            this.showNotification("Terjadi kesalahan saat menyimpan", "error");
        }
    }

    // Backup Functionality
    // Backup Functionality - IMPROVED
    async performBackup() {
        console.log("💾 performBackup called");

        // PERBAIKAN: Hapus backupType selection, gunakan default "full"
        const backupType = "full";
        const button = document.querySelector(
            "#backupModal button:first-child"
        );

        if (!button) {
            console.error("❌ Backup button not found");
            return;
        }

        const originalText = this.showLoading(button);

        try {
            console.log("🌐 Starting backup process, type:", backupType);

            const response = await fetch("/settings/backup", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document
                        .querySelector('meta[name="csrf-token"]')
                        .getAttribute("content"),
                },
                body: JSON.stringify({ type: backupType }),
            });

            const result = await response.json();
            console.log("📥 Backup response:", result);

            if (result.success) {
                this.showNotification(
                    `Backup berhasil! File: ${result.filename} (${result.record_count} records)`
                );
                this.closeModal("backupModal");
            } else {
                this.showNotification(
                    result.message || "Gagal melakukan backup",
                    "error"
                );
            }
        } catch (error) {
            console.error("❌ Error performing backup:", error);
            this.showNotification("Terjadi kesalahan saat backup", "error");
        } finally {
            this.hideLoading(button, originalText);
        }
    }

    // Reset Data Functionality
    async confirmReset() {
        console.log("💾 confirmReset called");
        const confirmation = prompt(
            'Ketik "HAPUS-SEMUA-DATA" untuk mengonfirmasi penghapusan semua data:'
        );
        console.log("📝 Reset confirmation input:", confirmation);

        if (confirmation === "HAPUS-SEMUA-DATA") {
            const button = document.querySelector(
                "#resetModal button:first-child"
            );
            if (!button) {
                console.error("❌ Reset button not found");
                return;
            }

            const originalText = this.showLoading(button);

            try {
                console.log("🌐 Starting reset process");
                const response = await fetch("/settings/reset", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": document
                            .querySelector('meta[name="csrf-token"]')
                            .getAttribute("content"),
                    },
                    body: JSON.stringify({ confirmation: confirmation }),
                });

                const result = await response.json();
                console.log("📥 Reset response:", result);

                if (result.success) {
                    this.showNotification("Semua data berhasil direset");
                    this.closeModal("resetModal");
                } else {
                    this.showNotification(
                        result.message || "Gagal mereset data",
                        "error"
                    );
                }
            } catch (error) {
                console.error("❌ Error resetting data:", error);
                this.showNotification(
                    "Terjadi kesalahan saat reset data",
                    "error"
                );
            } finally {
                this.hideLoading(button, originalText);
            }
        } else {
            console.log("❌ Reset confirmation failed - invalid input");
            this.showNotification(
                "Konfirmasi tidak valid. Data tidak direset.",
                "error"
            );
        }
    }

    // Enable/disable time inputs based on closed checkbox
    toggleTimeInputs(day) {
        const closedCheckbox = document.getElementById(`${day}Closed`);
        const openInput = document.getElementById(`${day}Open`);
        const closeInput = document.getElementById(`${day}Close`);

        if (closedCheckbox && openInput && closeInput) {
            const isClosed = closedCheckbox.checked;
            openInput.disabled = isClosed;
            closeInput.disabled = isClosed;

            if (isClosed) {
                openInput.classList.add("opacity-50", "cursor-not-allowed");
                closeInput.classList.add("opacity-50", "cursor-not-allowed");
            } else {
                openInput.classList.remove("opacity-50", "cursor-not-allowed");
                closeInput.classList.remove("opacity-50", "cursor-not-allowed");
            }
        }
    }

    // Attach event listeners untuk business hours
    attachHoursEventListeners() {
        console.log("🔧 attachHoursEventListeners called");

        const days = [
            "monday",
            "tuesday",
            "wednesday",
            "thursday",
            "friday",
            "saturday",
            "sunday",
        ];

        days.forEach((day) => {
            const closedCheckbox = document.getElementById(`${day}Closed`);
            if (closedCheckbox) {
                closedCheckbox.addEventListener("change", () => {
                    this.toggleTimeInputs(day);
                });

                // Set initial state
                this.toggleTimeInputs(day);
            }
        });

        console.log("✅ Hours event listeners attached");
    }

    // Enable/disable time inputs based on closed checkbox
    toggleTimeInputs(day) {
        console.log(`🕒 toggleTimeInputs called for: ${day}`);
        const closedCheckbox = document.getElementById(`${day}Closed`);
        const openInput = document.getElementById(`${day}Open`);
        const closeInput = document.getElementById(`${day}Close`);

        if (closedCheckbox && openInput && closeInput) {
            const isClosed = closedCheckbox.checked;
            openInput.disabled = isClosed;
            closeInput.disabled = isClosed;

            if (isClosed) {
                openInput.classList.add("opacity-50", "cursor-not-allowed");
                closeInput.classList.add("opacity-50", "cursor-not-allowed");
                console.log(`✅ ${day} time inputs disabled`);
            } else {
                openInput.classList.remove("opacity-50", "cursor-not-allowed");
                closeInput.classList.remove("opacity-50", "cursor-not-allowed");
                console.log(`✅ ${day} time inputs enabled`);
            }
        }
    }

    // Attach event listeners untuk business hours
    attachHoursEventListeners() {
        console.log("🔧 attachHoursEventListeners called");

        const days = [
            "monday",
            "tuesday",
            "wednesday",
            "thursday",
            "friday",
            "saturday",
            "sunday",
        ];

        days.forEach((day) => {
            const closedCheckbox = document.getElementById(`${day}Closed`);
            if (closedCheckbox) {
                closedCheckbox.addEventListener("change", () => {
                    this.toggleTimeInputs(day);
                });

                // Set initial state
                this.toggleTimeInputs(day);
            }
        });

        console.log("✅ Hours event listeners attached");
    }
}

// Initialize the app when DOM is loaded
document.addEventListener("DOMContentLoaded", function () {
    console.log("📄 DOM Content Loaded - Initializing SettingsApp");
    window.settingsApp = new SettingsApp();
    console.log("🎉 SettingsApp initialized and ready");
});

// Global functions for modal handling (for backward compatibility)
function openModal(modalId) {
    console.log(`🌍 Global openModal called: ${modalId}`);
    if (window.settingsApp) {
        window.settingsApp.openModal(modalId);
    } else {
        console.error("❌ settingsApp not initialized");
    }
}

function closeModal(modalId) {
    console.log(`🌍 Global closeModal called: ${modalId}`);
    if (window.settingsApp) {
        window.settingsApp.closeModal(modalId);
    } else {
        console.error("❌ settingsApp not initialized");
    }
}

function saveBusinessSettings() {
    console.log("🌍 Global saveBusinessSettings called");
    if (window.settingsApp) {
        window.settingsApp.saveBusinessSettings();
    } else {
        console.error("❌ settingsApp not initialized");
    }
}

function saveHoursSettings() {
    console.log("🌍 Global saveHoursSettings called");
    if (window.settingsApp) {
        window.settingsApp.saveBusinessHours();
    } else {
        console.error("❌ settingsApp not initialized");
    }
}

function saveReceiptSettings() {
    console.log("🌍 Global saveReceiptSettings called");
    if (window.settingsApp) {
        window.settingsApp.saveReceiptSettings();
    } else {
        console.error("❌ settingsApp not initialized");
    }
}

function performBackup() {
    console.log("🌍 Global performBackup called");
    if (window.settingsApp) {
        window.settingsApp.performBackup();
    } else {
        console.error("❌ settingsApp not initialized");
    }
}

function confirmReset() {
    console.log("🌍 Global confirmReset called");
    if (window.settingsApp) {
        window.settingsApp.confirmReset();
    } else {
        console.error("❌ settingsApp not initialized");
    }
}

function closeBusinessModal() {
    console.log("🌍 Global closeBusinessModal called");
    closeModal("businessModal");
}

function closeHoursModal() {
    console.log("🌍 Global closeHoursModal called");
    closeModal("hoursModal");
}

function closeReceiptModal() {
    console.log("🌍 Global closeReceiptModal called");
    closeModal("receiptModal");
}

function closeBackupModal() {
    console.log("🌍 Global closeBackupModal called");
    closeModal("backupModal");
}

function closeResetModal() {
    console.log("🌍 Global closeResetModal called");
    closeModal("resetModal");
}

// Debug info
console.log("🔧 settingsPage.js loaded successfully");
