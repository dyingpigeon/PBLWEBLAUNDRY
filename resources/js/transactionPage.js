// transactionPage.js
// Handle halaman utama transaksi (list, search, stats)

let currentPage = 1;
let hasMore = true;
let isLoading = false;

// Load data on page load
document.addEventListener('DOMContentLoaded', function () {
    loadTodaySummary();
    loadTransactions();
    setupSearch();
});

// Setup search functionality
function setupSearch() {
    const searchInput = document.getElementById('searchTransactions');
    let searchTimeout;

    searchInput.addEventListener('input', function (e) {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            currentPage = 1;
            hasMore = true;
            loadTransactions();
        }, 500);
    });
}

// Load today's summary from API
async function loadTodaySummary() {
    try {
        const response = await fetch('/api/transactions/today-summary');
        const data = await response.json();

        if (data.success) {
            document.getElementById('todayCount').textContent = data.data.total_transactions;
            document.getElementById('processingCount').textContent = data.data.processing_count;
            document.getElementById('revenueCount').textContent = `Rp ${formatPrice(data.data.total_income)}`;
        }
    } catch (error) {
        console.error('Error loading summary:', error);
    }
}

// Load transactions from API
async function loadTransactions() {
    if (isLoading) return;

    isLoading = true;
    showLoading();

    try {
        const searchQuery = document.getElementById('searchTransactions').value;
        const params = new URLSearchParams({
            page: currentPage,
            search: searchQuery
        });

        const response = await fetch(`/api/transactions/recent?${params}`);
        const data = await response.json();

        if (data.success) {
            if (currentPage === 1) {
                renderTransactions(data.data);
            } else {
                appendTransactions(data.data);
            }

            hasMore = data.data.length === 10; // Assuming 10 per page
            updateLoadMoreButton();
        }
    } catch (error) {
        console.error('Error loading transactions:', error);
    } finally {
        hideLoading();
        isLoading = false;
    }
}

// Load more transactions
async function loadMoreTransactions() {
    if (isLoading || !hasMore) return;

    currentPage++;
    await loadTransactions();
}

// Render transactions to the list
function renderTransactions(transactions) {
    const container = document.getElementById('transactionsList');
    const emptyState = document.getElementById('emptyState');

    if (transactions.length === 0) {
        container.innerHTML = '';
        emptyState.classList.remove('hidden');
        return;
    }

    emptyState.classList.add('hidden');
    container.innerHTML = transactions.map(transaction => createTransactionElement(transaction)).join('');
}

// Append transactions to the list
function appendTransactions(transactions) {
    const container = document.getElementById('transactionsList');

    if (transactions.length === 0) {
        hasMore = false;
        updateLoadMoreButton();
        return;
    }

    container.innerHTML += transactions.map(transaction => createTransactionElement(transaction)).join('');
}

// Create transaction list item
function createTransactionElement(transaction) {
    const statusInfo = getStatusInfo(transaction.status);
    const paymentInfo = getPaymentInfo(transaction.payment_status);
    const date = new Date(transaction.created_at).toLocaleDateString('id-ID', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });

    return `
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100" 
             onclick="showTransactionDetail(${transaction.id})">
            <div class="flex justify-between items-start mb-2">
                <div>
                    <h3 class="font-semibold text-gray-800">${transaction.customer_name}</h3>
                    <p class="text-sm text-gray-500">${transaction.transaction_number}</p>
                    <p class="text-sm text-gray-500">${transaction.service_name}</p>
                </div>
                <div class="text-right">
                    <p class="font-bold text-gray-800">Rp ${formatPrice(transaction.total_amount)}</p>
                    <div class="flex space-x-1 mt-1">
                        <span class="text-xs px-2 py-1 rounded-full ${statusInfo.color}">
                            ${statusInfo.text}
                        </span>
                        <span class="text-xs px-2 py-1 rounded-full ${paymentInfo.color}">
                            ${paymentInfo.text}
                        </span>
                    </div>
                </div>
            </div>
            <div class="flex justify-between items-center text-sm text-gray-500">
                <span>${date}</span>
                <button class="text-blue-500 font-medium">Detail</button>
            </div>
        </div>
    `;
}

// Get status info
function getStatusInfo(status) {
    const statusMap = {
        'new': { text: 'Baru', color: 'bg-blue-100 text-blue-600' },
        'washing': { text: 'Dicuci', color: 'bg-orange-100 text-orange-600' },
        'ironing': { text: 'Disetrika', color: 'bg-purple-100 text-purple-600' },
        'ready': { text: 'Selesai', color: 'bg-green-100 text-green-600' },
        'picked_up': { text: 'Diambil', color: 'bg-gray-100 text-gray-600' }
    };
    return statusMap[status] || { text: 'Unknown', color: 'bg-gray-100 text-gray-600' };
}

// Get payment status info
function getPaymentInfo(paymentStatus) {
    const paymentMap = {
        'pending': { text: 'Belum Bayar', color: 'bg-yellow-100 text-yellow-600' },
        'paid': { text: 'Lunas', color: 'bg-green-100 text-green-600' },
        'partial': { text: 'DP', color: 'bg-blue-100 text-blue-600' },
        'overpaid': { text: 'Kelebihan', color: 'bg-purple-100 text-purple-600' }
    };
    return paymentMap[paymentStatus] || { text: 'Unknown', color: 'bg-gray-100 text-gray-600' };
}

// Show transaction detail
async function showTransactionDetail(transactionId) {
    try {
        const response = await fetch(`/transactions/${transactionId}`);
        const data = await response.json();

        if (data.success) {
            // Redirect to tracking page or show detail modal
            window.location.href = `/tracking?highlight=${transactionId}`;
        }
    } catch (error) {
        console.error('Error loading transaction detail:', error);
    }
}

// ===== UTILITY FUNCTIONS =====

// Format price to Indonesian format
function formatPrice(price) {
    return new Intl.NumberFormat('id-ID').format(price);
}

function showLoading() {
    document.getElementById('loadingState').classList.remove('hidden');
    document.getElementById('transactionsList').classList.add('hidden');
}

function hideLoading() {
    document.getElementById('loadingState').classList.add('hidden');
    document.getElementById('transactionsList').classList.remove('hidden');
}

function updateLoadMoreButton() {
    const container = document.getElementById('loadMoreContainer');
    if (hasMore) {
        container.classList.remove('hidden');
    } else {
        container.classList.add('hidden');
    }
}