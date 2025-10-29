<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - LaundryKu</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .swipeable {
            scroll-snap-type: x mandatory;
            -webkit-overflow-scrolling: touch;
        }

        .swipeable>div {
            scroll-snap-align: start;
            flex: 0 0 85%;
        }

        .stat-card {
            transition: transform 0.2s ease;
        }

        .stat-card:active {
            transform: scale(0.98);
        }

        .refresh-indicator {
            transition: transform 0.3s ease;
        }

        .refreshing {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        /* Modal animations */
        .modal-fade-in {
            animation: fadeIn 0.2s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: scale(0.95) translateY(-10px);
            }

            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }
    </style>
    @stack('styles')
</head>

<body class="bg-gray-50 min-h-screen">
    @include('partials.header')

    <main class="pb-20">
        <!-- Tambahkan di bagian atas content -->
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4 mx-4 mt-4"
                role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4 mx-4 mt-4"
                role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif
        @yield('content')
    </main>

    @include('partials.bottom-navigation')

    <!-- Global JavaScript Functions -->
    <script>
        // Customer Modal Functions - Bisa dipakai di semua page
        function showAddCustomerModal() {
            const modal = document.getElementById('addCustomerModal');
            if (modal) {
                modal.classList.remove('hidden');
                modal.classList.add('modal-fade-in');
                // Focus ke input name setelah modal muncul
                setTimeout(() => {
                    const nameInput = document.getElementById('customerName');
                    if (nameInput) nameInput.focus();
                }, 100);
            }
        }

        function closeAddCustomerModal() {
            const modal = document.getElementById('addCustomerModal');
            if (modal) {
                modal.classList.remove('modal-fade-in');
                setTimeout(() => {
                    modal.classList.add('hidden');
                    const form = document.getElementById('addCustomerForm');
                    if (form) form.reset();
                }, 150);
            }
        }

        function handleAddCustomer(event) {
            event.preventDefault();

            const formData = {
                name: document.getElementById('customerName')?.value || '',
                phone: document.getElementById('customerPhone')?.value || '',
                address: document.getElementById('customerAddress')?.value || ''
            };

            // Validasi sederhana
            if (!formData.name || !formData.phone) {
                alert('Nama dan telepon harus diisi!');
                return;
            }

            // Tampilkan loading state
            const submitBtn = event.target.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Menyimpan...';
            submitBtn.disabled = true;

            // Simulate API call
            setTimeout(() => {
                // Simpan ke database (simulasi)
                console.log('Menambah pelanggan:', formData);

                alert('Pelanggan berhasil ditambahkan!');
                closeAddCustomerModal();
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;

                // Refresh customers list jika di halaman customers
                if (typeof window.refreshCustomers === 'function') {
                    window.refreshCustomers();
                }
            }, 1000);
        }

        // Global modal close handlers
        document.addEventListener('DOMContentLoaded', function () {
            // Close modal when clicking outside
            document.addEventListener('click', function (e) {
                const modal = document.getElementById('addCustomerModal');
                if (modal && !modal.classList.contains('hidden') && e.target === modal) {
                    closeAddCustomerModal();
                }
            });

            // Close modal with Escape key
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') {
                    const modal = document.getElementById('addCustomerModal');
                    if (modal && !modal.classList.contains('hidden')) {
                        closeAddCustomerModal();
                    }
                }
            });

            // Auto-format phone number
            const phoneInput = document.getElementById('customerPhone');
            if (phoneInput) {
                phoneInput.addEventListener('input', function (e) {
                    let value = e.target.value.replace(/\D/g, '');
                    if (value.length > 0) {
                        value = value.replace(/(\d{4})(\d{4})(\d{0,4})/, '$1-$2-$3');
                    }
                    e.target.value = value;
                });
            }
        });
    </script>

    @stack('scripts')
</body>

</html>