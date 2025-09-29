// Global variables
window.currentSection = 'overview';
window.charts = {};
window.incomeExpenseChart = null;

// SweetAlert2 configuration for dark mode
window.swalConfig = {
    background: '#1f2937',
    color: '#ffffff',
    confirmButtonColor: '#06b6d4',
    cancelButtonColor: '#ef4444',
    customClass: {
        popup: 'rounded-lg',
        title: 'text-white',
        content: 'text-gray-300',
        confirmButton: 'rounded-lg px-4 py-2',
        cancelButton: 'rounded-lg px-4 py-2'
    }
};

// Enhanced notification function using SweetAlert2
function showNotification(message, type = 'info', title = '') {
    const config = {
        ...window.swalConfig,
        text: message,
        icon: type,
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
    };
    
    if (title) {
        config.title = title;
    }
    
    Swal.fire(config);
}

// Confirmation dialog function
async function showConfirmation(title, text, confirmText = 'نعم', cancelText = 'إلغاء') {
    const result = await Swal.fire({
        ...window.swalConfig,
        title: title,
        text: text,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: confirmText,
        cancelButtonText: cancelText,
        reverseButtons: true
    });
    
    return result.isConfirmed;
}

document.addEventListener('DOMContentLoaded', function() {
    initializeCharts();
    loadRecentTransactions();
    loadCategories();
    setupEventListeners();
    loadDashboardData();
    
    // Set today's date as default for transaction form
    const today = new Date().toISOString().split('T')[0];
    const dateInput = document.querySelector('input[name="transaction_date"]');
    if (dateInput) {
        dateInput.value = today;
    }
});

// Navigation functions
function showSection(sectionName) {
    // Hide all sections
    document.querySelectorAll('.section').forEach(section => {
        section.classList.add('hidden');
    });
    
    // Show selected section
    const targetSection = document.getElementById(sectionName + '-section');
    if (targetSection) {
        targetSection.classList.remove('hidden');
    }
    
    // Update navigation active state
    document.querySelectorAll('.nav-item').forEach(item => {
        item.classList.remove('bg-gray-700', 'text-white');
        item.classList.add('text-gray-300');
    });
    
    event.target.classList.add('bg-gray-700', 'text-white');
    event.target.classList.remove('text-gray-300');
    
    window.currentSection = sectionName;
    
    // Load section-specific content
    switch(sectionName) {
        case 'overview':
            loadOverviewSection();
            break;
        case 'transactions':
            loadTransactionsSection();
            break;
        case 'income':
            loadIncomeSection();
            break;
        case 'budgets':
            loadBudgetsSection();
            break;
        case 'reports':
            loadReportsSection();
            break;
        case 'categories':
            loadCategoriesSection();
            break;
        case 'users':
            loadUsersSection();
            break;
    }
}

// Load overview section data
async function loadOverviewSection() {
    try {
        console.log('Loading overview section...');
        
        // Load monthly spending data
        await loadMonthlySummary();
        
        // Load budget usage and update chart
        await loadBudgetUsagePercentage();
        
        // Load recent transactions
        await loadRecentTransactions();
        
        console.log('Overview section loaded successfully');
    } catch (error) {
        console.error('Error loading overview section:', error);
    }
}

// Chart initialization
function initializeCharts() {
    // Budget Chart (Doughnut)
    const budgetCtx = document.getElementById('budgetChart');
    if (budgetCtx) {
        // Destroy existing chart if it exists
        if (window.charts.budget) {
            window.charts.budget.destroy();
        }
        
        // Clear any existing Chart.js instance on this canvas
        const existingChart = Chart.getChart(budgetCtx);
        if (existingChart) {
            existingChart.destroy();
        }
        
        window.charts.budget = new Chart(budgetCtx, {
            type: 'doughnut',
            data: {
                datasets: [{
                    data: [0, 100], // Start with 0% used, 100% remaining
                    backgroundColor: ['#06B6D4', '#374151'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    }

    // Category Chart (Doughnut)
    const categoryCtx = document.getElementById('categoryChart');
    if (categoryCtx) {
        // Destroy existing chart if it exists
        if (window.charts.category) {
            window.charts.category.destroy();
        }
        
        // Clear any existing Chart.js instance on this canvas
        const existingCategoryChart = Chart.getChart(categoryCtx);
        if (existingCategoryChart) {
            existingCategoryChart.destroy();
        }
        
        window.charts.category = new Chart(categoryCtx, {
            type: 'doughnut',
            data: {
                labels: ['الإيجار والمرافق', 'البقالة', 'الترفيه', 'الاشتراكات'],
                datasets: [{
                    data: [35, 25, 20, 20],
                    backgroundColor: ['#06B6D4', '#3B82F6', '#10B981', '#8B5CF6'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '60%',
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    }

    // Cash Flow Chart (Line)
    const cashFlowCtx = document.getElementById('cashFlowChart');
    if (cashFlowCtx) {
        // Destroy existing chart if it exists
        if (window.charts.cashFlow) {
            window.charts.cashFlow.destroy();
        }
        
        // Clear any existing Chart.js instance on this canvas
        const existingCashFlowChart = Chart.getChart(cashFlowCtx);
        if (existingCashFlowChart) {
            existingCashFlowChart.destroy();
        }
        
        window.charts.cashFlow = new Chart(cashFlowCtx, {
            type: 'line',
            data: {
                labels: ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو'],
                datasets: [{
                    label: 'التدفق النقدي',
                    data: [1200, 1400, 1100, 1600, 1300, 1850],
                    borderColor: '#06B6D4',
                    backgroundColor: 'rgba(6, 182, 212, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: '#374151'
                        },
                        ticks: {
                            color: '#9CA3AF'
                        }
                    },
                    x: {
                        grid: {
                            color: '#374151'
                        },
                        ticks: {
                            color: '#9CA3AF'
                        }
                    }
                }
            }
        });
    }
}

// Modal functions
function openAddTransactionModal() {
    document.getElementById('addTransactionModal').classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
}

function closeAddTransactionModal() {
    const modal = document.getElementById('addTransactionModal');
    const form = document.getElementById('addTransactionForm');
    const submitButton = form.querySelector('button[type="submit"]');
    
    modal.classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
    form.reset();
    
    // Reset submit button state
    if (submitButton) {
        submitButton.disabled = false;
        submitButton.innerHTML = '<i class="fas fa-save ml-2"></i>حفظ';
    }
}

// Event listeners setup
function setupEventListeners() {
    // Prevent duplicate event listeners
    if (window.eventListenersSetup) {
        return;
    }
    window.eventListenersSetup = true;
    
    // Add transaction form submission
    const transactionForm = document.getElementById('addTransactionForm');
    if (transactionForm) {
        transactionForm.addEventListener('submit', function(e) {
            e.preventDefault();
            submitTransaction();
        });
    }

    // Close modal when clicking outside
    document.getElementById('addTransactionModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeAddTransactionModal();
        }
    });

    // Escape key to close modals
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeAddTransactionModal();
        }
    });
}

// API functions
async function apiRequest(url, options = {}) {
    try {
        const response = await fetch(url, {
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                ...options.headers
            },
            ...options
        });

        // Try to get JSON response even for errors
        let data;
        try {
            data = await response.json();
        } catch (e) {
            data = { success: false, message: 'Invalid JSON response' };
        }

        if (!response.ok) {
            // Handle specific error codes
            if (response.status === 401) {
                showNotification('انتهت صلاحية الجلسة، يرجى تسجيل الدخول مرة أخرى', 'error');
                setTimeout(() => {
                    window.location.href = 'login.php';
                }, 2000);
                throw new Error(`Authentication required (${response.status})`);
            } else if (response.status === 403) {
                showNotification('غير مصرح لك بهذا الإجراء', 'error');
                throw new Error(`Forbidden (${response.status})`);
            } else if (response.status === 500) {
                showNotification('خطأ في الخادم: ' + (data.message || 'خطأ غير معروف'), 'error');
                console.error('Server error details:', data);
                throw new Error(`Server error (${response.status}): ${data.message || 'Unknown error'}`);
            } else {
                showNotification(`خطأ في الطلب (${response.status})`, 'error');
                throw new Error(`HTTP error! status: ${response.status}`);
            }
        }

        return data;
    } catch (error) {
        console.error('API request failed:', error);
        if (!error.message.includes('Authentication required') && 
            !error.message.includes('Forbidden') && 
            !error.message.includes('Server error')) {
            showNotification('حدث خطأ في الاتصال', 'error');
        }
        throw error;
    }
}

// Load recent transactions
async function loadRecentTransactions() {
    try {
        const data = await apiRequest('api/transactions.php?action=recent&limit=5');
        
        if (data.success) {
            const tbody = document.getElementById('recent-transactions');
            tbody.innerHTML = '';
            
            data.transactions.forEach(transaction => {
                const row = document.createElement('tr');
                row.className = 'border-b border-gray-700';
                row.innerHTML = `
                    <td class="py-2 text-sm">${formatDate(transaction.transaction_date)}</td>
                    <td class="py-2 text-sm">${transaction.description}</td>
                    <td class="py-2 text-sm">${transaction.merchant || '-'}</td>
                    <td class="py-2 text-sm font-medium">€${parseFloat(transaction.amount).toFixed(2)}</td>
                    <td class="py-2">
                        <span class="px-2 py-1 text-xs rounded-full ${getStatusClass(transaction.status)}">
                            ${getStatusText(transaction.status)}
                        </span>
                    </td>
                `;
                tbody.appendChild(row);
            });
        }
    } catch (error) {
        console.error('Failed to load recent transactions:', error);
    }
}

// Load categories for dropdown
async function loadCategories(modalId = null, selectedCategoryId = null) {
    try {
        const data = await apiRequest('api/categories.php');
        
        if (data.success && data.categories) {
            // Target specific modal or find any category select
            let select;
            if (modalId) {
                select = document.querySelector(`#${modalId} select[name="category_id"]`);
            } else {
                select = document.querySelector('select[name="category_id"]');
            }
            
            if (!select) {
                console.error('Category select element not found');
                return false;
            }
            
            select.innerHTML = '<option value="">اختر الفئة</option>';
            
            data.categories.forEach(category => {
                if (category.category_type === 'expense' || category.category_type === 'both') {
                    const option = document.createElement('option');
                    option.value = category.id;
                    option.textContent = category.name_ar || category.name;
                    
                    // Pre-select if specified
                    if (selectedCategoryId && category.id == selectedCategoryId) {
                        option.selected = true;
                    }
                    
                    select.appendChild(option);
                }
            });
            
            console.log(`Loaded ${data.categories.length} categories for ${modalId || 'default'} modal`);
            return true;
        }
        return false;
    } catch (error) {
        console.error('Failed to load categories:', error);
        return false;
    }
}

// Submit transaction
async function submitTransaction() {
    const form = document.getElementById('addTransactionForm');
    const submitButton = form.querySelector('button[type="submit"]');
    
    // Prevent multiple submissions
    if (submitButton.disabled) {
        return;
    }
    
    // Disable submit button
    submitButton.disabled = true;
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin ml-2"></i>جاري الحفظ...';
    
    const formData = new FormData(form);
    
    const transactionData = {
        description: formData.get('description'),
        amount: parseFloat(formData.get('amount')),
        category_id: parseInt(formData.get('category_id')),
        merchant: formData.get('merchant'),
        transaction_date: formData.get('transaction_date')
    };

    try {
        const data = await apiRequest('api/transactions.php', {
            method: 'POST',
            body: JSON.stringify(transactionData)
        });

        if (data.success) {
            showNotification('تم إضافة المعاملة بنجاح', 'success');
            closeAddTransactionModal();
            
            // Update all dashboard data
            loadRecentTransactions();
            loadDashboardData(); // Refresh main dashboard data
            
            // If we're on overview, also refresh charts
            if (window.currentSection === 'overview') {
                // Small delay to ensure data is updated
                setTimeout(() => {
                    loadIncomeExpenseChart();
                    loadMonthlyComparisonChart();
                    loadCategoryChart('expense');
                }, 500);
            }
            
            // If we're on transactions section, reload transactions
            if (window.currentSection === 'transactions') {
                loadAllTransactions();
            }
        } else {
            showNotification(data.message || 'حدث خطأ أثناء إضافة المعاملة', 'error');
        }
    } catch (error) {
        console.error('Failed to submit transaction:', error);
        showNotification('حدث خطأ أثناء إضافة المعاملة', 'error');
    } finally {
        // Re-enable submit button
        submitButton.disabled = false;
        submitButton.innerHTML = '<i class="fas fa-save ml-2"></i>حفظ';
    }
}

// Load sections
function loadTransactionsSection() {
    const section = document.getElementById('transactions-section');
    section.innerHTML = `
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold">إدارة المعاملات</h2>
            <button onclick="openAddTransactionModal()" class="bg-cyan-500 hover:bg-cyan-600 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-plus ml-2"></i>
                إضافة معاملة
            </button>
        </div>
        
        <div class="bg-gray-800 rounded-lg p-6">
            <div class="flex justify-between items-center mb-4">
                <div class="flex space-x-4 space-x-reverse">
                    <input type="text" placeholder="البحث في المعاملات..." class="px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:border-cyan-500">
                    <select class="px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:border-cyan-500">
                        <option value="">جميع الفئات</option>
                    </select>
                </div>
                <div class="text-sm text-gray-400">
                    عرض 1-10 من 50 معاملة
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-gray-400 border-b border-gray-700">
                            <th class="text-right py-3">التاريخ</th>
                            <th class="text-right py-3">الوصف</th>
                            <th class="text-right py-3">الفئة</th>
                            <th class="text-right py-3">التاجر</th>
                            <th class="text-right py-3">المبلغ</th>
                            <th class="text-right py-3">الحالة</th>
                            <th class="text-right py-3">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody id="transactions-table">
                        <!-- Transactions will be loaded here -->
                    </tbody>
                </table>
            </div>
        </div>
    `;
    
    loadAllTransactions();
}

function loadIncomeSection() {
    const section = document.getElementById('income-section');
    section.innerHTML = `
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold">إدارة الدخل</h2>
            <button onclick="openAddIncomeModal()" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-plus ml-2"></i>
                إضافة دخل
            </button>
        </div>
        
        <div class="bg-gray-800 rounded-lg p-6 mb-6">
            <div class="flex justify-between items-center mb-4">
                <div class="flex space-x-4 space-x-reverse">
                    <input type="text" placeholder="البحث في الدخل..." class="px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:border-green-500">
                    <select class="px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:border-green-500">
                        <option value="">جميع فئات الدخل</option>
                    </select>
                </div>
                <div class="text-sm text-gray-400">
                    عرض 1-10 من 0 دخل
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-gray-400 border-b border-gray-700">
                            <th class="text-right py-3">التاريخ</th>
                            <th class="text-right py-3">الوصف</th>
                            <th class="text-right py-3">الفئة</th>
                            <th class="text-right py-3">المصدر</th>
                            <th class="text-right py-3">المبلغ</th>
                            <th class="text-right py-3">الحالة</th>
                            <th class="text-right py-3">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody id="income-table">
                        <!-- Income will be loaded here -->
                    </tbody>
                </table>
            </div>
        </div>
    `;
    
    loadAllIncome();
}

function loadBudgetsSection() {
    const section = document.getElementById('budgets-section');
    section.innerHTML = `
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold">إدارة الميزانيات</h2>
            <button onclick="openAddBudgetModal()" class="bg-cyan-500 hover:bg-cyan-600 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-plus ml-2"></i>
                إضافة ميزانية
            </button>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="budgets-grid">
            <div class="bg-gray-800 rounded-lg p-6 text-center">
                <div class="text-gray-400 mb-4">
                    <i class="fas fa-spinner fa-spin text-2xl"></i>
                </div>
                <p class="text-gray-400">جاري تحميل الميزانيات...</p>
            </div>
        </div>
    `;
    
    loadBudgets();
}

function loadReportsSection() {
    const section = document.getElementById('reports-section');
    section.innerHTML = `
        <div class="mb-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold">التقارير والتحليلات</h2>
                <div class="flex space-x-3 space-x-reverse">
                    <select id="reportPeriod" class="px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:border-cyan-500">
                        <option value="month">هذا الشهر</option>
                        <option value="year">هذا العام</option>
                        <option value="all">جميع الفترات</option>
                    </select>
                    <button onclick="refreshReports()" class="bg-cyan-500 hover:bg-cyan-600 text-white px-4 py-2 rounded-lg">
                        <i class="fas fa-refresh ml-2"></i>
                        تحديث
                    </button>
                </div>
            </div>
            
            <!-- Financial Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6" id="financial-summary">
                <div class="bg-gray-800 rounded-lg p-6 text-center">
                    <div class="text-gray-400 mb-2">جاري التحميل...</div>
                </div>
            </div>
            
            <!-- Charts Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- Income vs Expenses Chart -->
                <div class="bg-gray-800 rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-4 text-green-400">
                        <i class="fas fa-chart-line ml-2"></i>
                        الدخل مقابل المصروفات
                    </h3>
                    <div class="relative" style="height: 300px; width: 100%;">
                        <canvas id="incomeExpenseChart"></canvas>
                    </div>
                </div>
                
                <!-- Category Breakdown -->
                <div class="bg-gray-800 rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-4 text-blue-400">
                        <i class="fas fa-chart-pie ml-2"></i>
                        توزيع الفئات
                    </h3>
                    <div class="flex justify-center mb-4">
                        <div class="flex space-x-2 space-x-reverse">
                            <button onclick="loadCategoryChart('expense')" class="px-3 py-1 bg-red-500 text-white rounded text-sm category-chart-btn active" data-type="expense">
                                المصروفات
                            </button>
                            <button onclick="loadCategoryChart('income')" class="px-3 py-1 bg-gray-600 text-white rounded text-sm category-chart-btn" data-type="income">
                                الدخل
                            </button>
                        </div>
                    </div>
                    <div class="relative" style="height: 300px; width: 100%;">
                        <canvas id="categoryChart"></canvas>
                    </div>
                </div>
            </div>
            
            <!-- Monthly Comparison -->
            <div class="bg-gray-800 rounded-lg p-6 mb-6">
                <h3 class="text-lg font-semibold mb-4 text-purple-400">
                    <i class="fas fa-chart-bar ml-2"></i>
                    المقارنة الشهرية (آخر 6 أشهر)
                </h3>
                <div class="relative" style="height: 400px; width: 100%;">
                    <canvas id="monthlyComparisonChart"></canvas>
                </div>
            </div>
            
            <!-- Top Categories Tables -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- Top Expense Categories -->
                <div class="bg-gray-800 rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-4 text-red-400">
                        <i class="fas fa-arrow-down ml-2"></i>
                        أكثر فئات الإنفاق
                    </h3>
                    <div id="top-expense-categories">
                        <div class="text-gray-400 text-center py-4">جاري التحميل...</div>
                    </div>
                </div>
                
                <!-- Top Income Sources -->
                <div class="bg-gray-800 rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-4 text-green-400">
                        <i class="fas fa-arrow-up ml-2"></i>
                        أكثر مصادر الدخل
                    </h3>
                    <div id="top-income-sources">
                        <div class="text-gray-400 text-center py-4">جاري التحميل...</div>
                    </div>
                </div>
            </div>
            
            <!-- Detailed Transactions -->
            <div class="bg-gray-800 rounded-lg p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-300">
                        <i class="fas fa-list ml-2"></i>
                        المعاملات التفصيلية (آخر 50 معاملة)
                    </h3>
                    <div class="flex space-x-2 space-x-reverse">
                        <select id="transactionType" class="px-3 py-1 bg-gray-700 border border-gray-600 rounded text-sm">
                            <option value="all">جميع المعاملات</option>
                            <option value="income">الدخل فقط</option>
                            <option value="expense">المصروفات فقط</option>
                        </select>
                        <button onclick="loadDetailedTransactions()" class="px-3 py-1 bg-gray-600 hover:bg-gray-500 text-white rounded text-sm">
                            تحديث
                        </button>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-gray-400 border-b border-gray-700">
                                <th class="text-right py-2">التاريخ</th>
                                <th class="text-right py-2">النوع</th>
                                <th class="text-right py-2">الوصف</th>
                                <th class="text-right py-2">الفئة</th>
                                <th class="text-right py-2">المبلغ</th>
                            </tr>
                        </thead>
                        <tbody id="detailed-transactions">
                            <tr><td colspan="5" class="py-4 text-center text-gray-400">جاري التحميل...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    `;
    
    // Destroy any existing charts first
    destroyAllCharts();
    
    // Initialize reports
    loadFinancialSummary();
    loadDetailedTransactions();
}

// Function to destroy all charts
function destroyAllCharts() {
    console.log('Destroying all charts...');
    
    // Reset loading flag
    window.isLoadingCharts = false;
    
    if (window.incomeExpenseChart) {
        console.log('Destroying incomeExpenseChart');
        window.incomeExpenseChart.destroy();
        window.incomeExpenseChart = null;
    }
    if (window.monthlyComparisonChart) {
        console.log('Destroying monthlyComparisonChart');
        window.monthlyComparisonChart.destroy();
        window.monthlyComparisonChart = null;
    }
    if (window.categoryChart) {
        console.log('Destroying categoryChart');
        window.categoryChart.destroy();
        window.categoryChart = null;
    }
    
    // Clear any remaining Chart.js instances
    ['incomeExpenseChart', 'monthlyComparisonChart', 'categoryChart'].forEach(canvasId => {
        const canvas = document.getElementById(canvasId);
        if (canvas) {
            const existingChart = Chart.getChart(canvas);
            if (existingChart) {
                console.log(`Destroying existing Chart.js instance on ${canvasId}`);
                existingChart.destroy();
            }
        }
    });
    
    console.log('All charts destroyed');
}

// Load dashboard data
async function loadDashboardData() {
    try {
        await Promise.all([
            loadDashboardSummary(),
            loadTopCategories(),
            loadBudgetVsActual()
        ]);
    } catch (error) {
        console.error('Error loading dashboard data:', error);
    }
}

// Load dashboard summary (monthly spending, budget percentage, savings)
async function loadDashboardSummary() {
    try {
        const data = await apiRequest('api/reports.php?action=financial_summary&period=month');
        
        if (data.success) {
            const summary = data.data.summary;
            
            // Update monthly spending
            const monthlySpendingEl = document.getElementById('monthly-spending');
            if (monthlySpendingEl) {
                monthlySpendingEl.textContent = formatCurrency(summary.total_expenses || 0);
            }
            
            // Calculate budget usage percentage (expenses vs total budget)
            const budgetPercentageEl = document.getElementById('budget-percentage');
            if (budgetPercentageEl) {
                // Get total budget from budgets API
                loadBudgetUsagePercentage();
            }
            
            // Update savings based on user's goal
            loadSavingsGoalProgress(summary);
        }
    } catch (error) {
        console.error('Error loading dashboard summary:', error);
    }
}

// Load top spending categories
async function loadTopCategories() {
    try {
        const data = await apiRequest('api/reports.php?action=category_breakdown&type=expense&period=month');
        
        if (data.success && data.data.categories) {
            const categories = data.data.categories.slice(0, 5); // Get top 5
            const listContainer = document.getElementById('category-spending-list');
            
            if (listContainer && categories.length > 0) {
                listContainer.innerHTML = categories.map(category => `
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-3 h-3 rounded-full ml-2" style="background-color: ${category.category_color}"></div>
                            <span class="text-sm">${category.category_name_ar}</span>
                        </div>
                        <span class="text-sm font-medium">${formatCurrency(category.total_amount)}</span>
                    </div>
                `).join('');
            } else if (listContainer) {
                listContainer.innerHTML = '<div class="text-center text-gray-400 py-4">لا توجد بيانات</div>';
            }
        }
    } catch (error) {
        console.error('Error loading top categories:', error);
        const listContainer = document.getElementById('category-spending-list');
        if (listContainer) {
            listContainer.innerHTML = '<div class="text-center text-red-400 py-4">خطأ في تحميل البيانات</div>';
        }
    }
}

// Load budget vs actual spending
async function loadBudgetVsActual() {
    try {
        const data = await apiRequest('api/reports.php?action=budget_performance');
        
        if (data.success && data.data.budgets) {
            const budgets = data.data.budgets.slice(0, 5); // Get top 5
            const container = document.getElementById('budget-vs-actual');
            
            if (container && budgets.length > 0) {
                container.innerHTML = budgets.map(budget => {
                    const percentage = parseFloat(budget.percentage_used) || 0;
                    const isOverBudget = percentage > 100;
                    
                    return `
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="w-3 h-3 rounded-full ml-2" style="background-color: ${budget.category_color}"></div>
                                <span class="text-sm">${budget.category_name}</span>
                            </div>
                            <span class="text-sm ${isOverBudget ? 'text-red-400' : 'text-green-400'}">${Math.round(percentage)}%</span>
                        </div>
                    `;
                }).join('');
            } else if (container) {
                container.innerHTML = '<div class="text-center text-gray-400 py-4">لا توجد ميزانيات</div>';
            }
        }
    } catch (error) {
        console.error('Error loading budget vs actual:', error);
        const container = document.getElementById('budget-vs-actual');
        if (container) {
            container.innerHTML = '<div class="text-center text-red-400 py-4">خطأ في تحميل البيانات</div>';
        }
    }
}

// Update budget chart with real data
function updateBudgetChart(percentage) {
    if (window.charts && window.charts.budget) {
        const chart = window.charts.budget;
        
        // Cap percentage at 100% for visual purposes
        const cappedPercentage = Math.min(percentage, 100);
        const remaining = Math.max(100 - cappedPercentage, 0);
        
        // Update chart data
        chart.data.datasets[0].data = [cappedPercentage, remaining];
        
        // Update colors based on percentage
        let usedColor, remainingColor;
        if (percentage > 100) {
            usedColor = '#EF4444'; // Red for over budget
            remainingColor = '#1F2937'; // Dark gray
        } else if (percentage > 80) {
            usedColor = '#F59E0B'; // Amber for warning
            remainingColor = '#374151'; // Gray
        } else {
            usedColor = '#06B6D4'; // Cyan for good
            remainingColor = '#374151'; // Gray
        }
        
        chart.data.datasets[0].backgroundColor = [usedColor, remainingColor];
        
        // Update the chart
        chart.update('none'); // Use 'none' for instant update without animation
        
        console.log(`Budget chart updated: ${cappedPercentage}% used, ${remaining}% remaining`);
    } else {
        console.warn('Budget chart not found for update. Charts object:', window.charts);
        // Try to reinitialize charts if they don't exist
        if (!window.charts || !window.charts.budget) {
            console.log('Attempting to reinitialize budget chart...');
            initializeCharts();
        }
    }
}

// Load budget usage percentage
async function loadBudgetUsagePercentage() {
    try {
        const data = await apiRequest('api/budgets.php?action=current');
        
        if (data.success && data.budgets) {
            const budgets = data.budgets;
            const totalBudget = budgets.reduce((sum, budget) => sum + parseFloat(budget.amount || 0), 0);
            const totalSpent = budgets.reduce((sum, budget) => sum + parseFloat(budget.actual_amount || 0), 0);
            
            const budgetPercentageEl = document.getElementById('budget-percentage');
            if (budgetPercentageEl && totalBudget > 0) {
                const percentage = (totalSpent / totalBudget) * 100;
                
                // Cap the percentage display at reasonable limits
                const displayPercentage = Math.min(Math.max(percentage, 0), 999);
                budgetPercentageEl.textContent = `${Math.round(displayPercentage)}%`;
                
                // Update color based on percentage
                if (percentage > 100) {
                    budgetPercentageEl.className = 'text-xs sm:text-sm font-semibold text-red-400';
                } else if (percentage > 80) {
                    budgetPercentageEl.className = 'text-xs sm:text-sm font-semibold text-yellow-400';
                } else {
                    budgetPercentageEl.className = 'text-xs sm:text-sm font-semibold text-green-400';
                }
                
                // Update the budget chart with real data
                updateBudgetChart(percentage);
            } else if (budgetPercentageEl) {
                budgetPercentageEl.textContent = 'لا توجد';
                budgetPercentageEl.className = 'text-xs font-semibold text-gray-400';
                
                // Update chart to show no data
                updateBudgetChart(0);
            }
        }
    } catch (error) {
        console.error('Error loading budget usage:', error);
    }
}

// Savings Goal System
async function loadSavingsGoalProgress(summary) {
    try {
        // Get user's savings goal from localStorage or API
        const savingsGoal = localStorage.getItem('savingsGoal');
        const goalData = savingsGoal ? JSON.parse(savingsGoal) : null;
        
        const savingsPercentageEl = document.getElementById('savings-percentage');
        const savingsStatusEl = document.getElementById('savings-status');
        
        if (savingsPercentageEl && savingsStatusEl) {
            const totalIncome = parseFloat(summary.total_income || 0);
            const totalExpenses = parseFloat(summary.total_expenses || 0);
            const actualSavings = totalIncome - totalExpenses;
            
            if (goalData && goalData.target_amount > 0) {
                // Calculate progress towards goal
                const progress = goalData.target_amount > 0 ? (actualSavings / goalData.target_amount) * 100 : 0;
                
                // Handle negative savings (spending more than earning)
                if (actualSavings < 0) {
                    savingsPercentageEl.textContent = '0%';
                    savingsStatusEl.textContent = 'إنفاق أكثر من الدخل';
                    savingsStatusEl.className = 'text-xs text-red-400';
                } else if (progress >= 100) {
                    savingsPercentageEl.textContent = `${Math.round(progress)}%`;
                    savingsStatusEl.textContent = 'تم تحقيق الهدف! 🎉';
                    savingsStatusEl.className = 'text-xs text-green-400';
                    
                    // Show achievement notification
                    if (!localStorage.getItem('goalAchieved_' + new Date().getMonth())) {
                        showNotification('مبروك! تم تحقيق هدف الادخار لهذا الشهر!', 'success', 'هدف محقق');
                        localStorage.setItem('goalAchieved_' + new Date().getMonth(), 'true');
                    }
                } else if (progress >= 75) {
                    savingsPercentageEl.textContent = `${Math.round(progress)}%`;
                    savingsStatusEl.textContent = 'قريب من الهدف';
                    savingsStatusEl.className = 'text-xs text-green-400';
                } else if (progress >= 50) {
                    savingsPercentageEl.textContent = `${Math.round(progress)}%`;
                    savingsStatusEl.textContent = 'في المسار الصحيح';
                    savingsStatusEl.className = 'text-xs text-yellow-400';
                } else if (progress >= 25) {
                    savingsPercentageEl.textContent = `${Math.round(progress)}%`;
                    savingsStatusEl.textContent = 'يحتاج جهد أكثر';
                    savingsStatusEl.className = 'text-xs text-orange-400';
                } else if (progress >= 0) {
                    savingsPercentageEl.textContent = `${Math.round(progress)}%`;
                    savingsStatusEl.textContent = 'بعيد عن الهدف';
                    savingsStatusEl.className = 'text-xs text-red-400';
                } else {
                    // Very negative progress (spending way more than target)
                    savingsPercentageEl.textContent = '0%';
                    savingsStatusEl.textContent = 'تجاوز الحد بكثير';
                    savingsStatusEl.className = 'text-xs text-red-400';
                }
                
                // Check for overspending alerts
                checkSpendingAlerts(totalExpenses, goalData);
                
            } else {
                // No goal set - show general savings rate
                const savingsRate = totalIncome > 0 ? (actualSavings / totalIncome) * 100 : 0;
                savingsPercentageEl.textContent = `${Math.round(savingsRate)}%`;
                savingsStatusEl.textContent = 'لم يتم تحديد هدف';
                savingsStatusEl.className = 'text-xs text-gray-400';
            }
        }
    } catch (error) {
        console.error('Error loading savings goal progress:', error);
    }
}

// Check spending alerts
function checkSpendingAlerts(currentExpenses, goalData) {
    if (!goalData || !goalData.expense_limit) return;
    
    const expenseLimit = parseFloat(goalData.expense_limit);
    const spendingPercentage = (currentExpenses / expenseLimit) * 100;
    
    const today = new Date().toDateString();
    const alertKey = 'spendingAlert_' + today;
    
    if (spendingPercentage >= 100 && !localStorage.getItem(alertKey + '_100')) {
        showNotification('تحذير! تم تجاوز حد الإنفاق المحدد لهذا الشهر', 'error', 'تجاوز الحد');
        localStorage.setItem(alertKey + '_100', 'true');
    } else if (spendingPercentage >= 80 && !localStorage.getItem(alertKey + '_80')) {
        showNotification('تنبيه: وصلت إلى 80% من حد الإنفاق المحدد', 'warning', 'اقتراب من الحد');
        localStorage.setItem(alertKey + '_80', 'true');
    }
}

// Open savings goal modal
function openSavingsGoalModal() {
    let modal = document.getElementById('savingsGoalModal');
    if (!modal) {
        modal = createSavingsGoalModal();
        document.body.appendChild(modal);
    }
    
    // Load current goal data
    const savingsGoal = localStorage.getItem('savingsGoal');
    if (savingsGoal) {
        const goalData = JSON.parse(savingsGoal);
        modal.querySelector('input[name="target_amount"]').value = goalData.target_amount || '';
        modal.querySelector('input[name="expense_limit"]').value = goalData.expense_limit || '';
        modal.querySelector('select[name="goal_type"]').value = goalData.goal_type || 'monthly';
    }
    
    modal.classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
}

// Create savings goal modal
function createSavingsGoalModal() {
    const modal = document.createElement('div');
    modal.id = 'savingsGoalModal';
    modal.className = 'fixed inset-0 bg-black bg-opacity-50 hidden z-50';
    modal.innerHTML = `
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-gray-800 rounded-lg p-6 w-full max-w-md">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold">تحديد هدف الادخار</h3>
                    <button onclick="closeSavingsGoalModal()" class="text-gray-400 hover:text-white">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form id="savingsGoalForm">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-2">نوع الهدف</label>
                            <select name="goal_type" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:border-cyan-500">
                                <option value="monthly">شهري</option>
                                <option value="yearly">سنوي</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">مبلغ الهدف للادخار</label>
                            <input type="number" name="target_amount" step="0.01" min="0" required class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:border-cyan-500" placeholder="مثال: 1000">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">حد الإنفاق الأقصى</label>
                            <input type="number" name="expense_limit" step="0.01" min="0" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:border-cyan-500" placeholder="مثال: 5000">
                            <div class="text-xs text-gray-400 mt-1">سيتم تنبيهك عند الاقتراب من هذا الحد</div>
                        </div>
                        <div class="bg-gray-700 p-3 rounded-lg">
                            <div class="text-sm text-gray-300 mb-2">💡 نصائح للادخار:</div>
                            <ul class="text-xs text-gray-400 space-y-1">
                                <li>• احرص على ادخار 20% من دخلك على الأقل</li>
                                <li>• راقب مصروفاتك اليومية</li>
                                <li>• ضع حد أقصى للمصروفات غير الضرورية</li>
                            </ul>
                        </div>
                    </div>
                    <div class="flex justify-end space-x-3 space-x-reverse mt-6">
                        <button type="button" onclick="closeSavingsGoalModal()" class="px-4 py-2 text-gray-400 hover:text-white">
                            إلغاء
                        </button>
                        <button type="submit" class="px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-lg">
                            حفظ الهدف
                        </button>
                    </div>
                </form>
            </div>
        </div>
    `;
    
    // Add event listener
    modal.querySelector('#savingsGoalForm').addEventListener('submit', function(e) {
        e.preventDefault();
        submitSavingsGoal();
    });
    
    return modal;
}

// Close savings goal modal
function closeSavingsGoalModal() {
    const modal = document.getElementById('savingsGoalModal');
    if (modal) {
        modal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }
}

// Submit savings goal
function submitSavingsGoal() {
    const form = document.getElementById('savingsGoalForm');
    const formData = new FormData(form);
    
    const goalData = {
        goal_type: formData.get('goal_type'),
        target_amount: parseFloat(formData.get('target_amount')),
        expense_limit: parseFloat(formData.get('expense_limit')) || null,
        created_at: new Date().toISOString()
    };
    
    // Save to localStorage (in a real app, this would be saved to database)
    localStorage.setItem('savingsGoal', JSON.stringify(goalData));
    
    showNotification('تم حفظ هدف الادخار بنجاح!', 'success');
    closeSavingsGoalModal();
    
    // Refresh dashboard to show new goal
    loadDashboardData();
}

function loadCategoriesSection() {
    const section = document.getElementById('categories-section');
    section.innerHTML = `
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold">إدارة الفئات</h2>
            <button onclick="openAddCategoryModal()" class="bg-cyan-500 hover:bg-cyan-600 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-plus ml-2"></i>
                إضافة فئة جديدة
            </button>
        </div>
        
        <!-- Categories Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
            <!-- System Categories -->
            <div class="bg-gray-800 rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4 text-blue-400">
                    <i class="fas fa-cog ml-2"></i>
                    الفئات الافتراضية
                </h3>
                <div id="system-categories" class="space-y-3">
                    <div class="text-center text-gray-400 py-4">جاري التحميل...</div>
                </div>
            </div>
            
            <!-- Custom Categories -->
            <div class="bg-gray-800 rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4 text-green-400">
                    <i class="fas fa-user ml-2"></i>
                    الفئات المخصصة
                </h3>
                <div id="custom-categories" class="space-y-3">
                    <div class="text-center text-gray-400 py-4">جاري التحميل...</div>
                </div>
            </div>
            
            <!-- Category Statistics -->
            <div class="bg-gray-800 rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4 text-purple-400">
                    <i class="fas fa-chart-pie ml-2"></i>
                    إحصائيات الفئات
                </h3>
                <div id="category-stats" class="space-y-3">
                    <div class="text-center text-gray-400 py-4">جاري التحميل...</div>
                </div>
            </div>
        </div>
        
        <!-- All Categories Table -->
        <div class="bg-gray-800 rounded-lg p-6">
            <h3 class="text-lg font-semibold mb-4">جميع الفئات</h3>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-700">
                            <th class="text-right py-3 px-4">الاسم</th>
                            <th class="text-right py-3 px-4">النوع</th>
                            <th class="text-right py-3 px-4">اللون</th>
                            <th class="text-right py-3 px-4">الاستخدام</th>
                            <th class="text-right py-3 px-4">تاريخ الإنشاء</th>
                            <th class="text-right py-3 px-4">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody id="categories-table">
                        <tr><td colspan="6" class="py-4 text-center text-gray-400">جاري التحميل...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    `;
    
    // Load categories data
    loadCategoriesData();
}

// Load categories data for management
async function loadCategoriesData() {
    try {
        console.log('Loading categories data...');
        const data = await apiRequest('api/categories.php');
        console.log('Categories data received:', data);
        
        if (data.success && data.categories) {
            console.log(`Found ${data.categories.length} categories`);
            displayCategoriesGrid(data.categories);
            displayCategoriesTable(data.categories);
            displayCategoryStats(data.categories);
        } else {
            console.error('Failed to load categories:', data.message || 'No categories found');
            
            // Show error in containers
            document.getElementById('system-categories').innerHTML = '<div class="text-center text-red-400 py-4">فشل في تحميل الفئات</div>';
            document.getElementById('custom-categories').innerHTML = '<div class="text-center text-red-400 py-4">فشل في تحميل الفئات</div>';
            document.getElementById('category-stats').innerHTML = '<div class="text-center text-red-400 py-4">فشل في تحميل الإحصائيات</div>';
            document.getElementById('categories-table').innerHTML = '<tr><td colspan="6" class="py-8 text-center text-red-400">فشل في تحميل الفئات</td></tr>';
            
            showNotification('فشل في تحميل الفئات: ' + (data.message || 'خطأ غير معروف'), 'error');
        }
    } catch (error) {
        console.error('Error loading categories:', error);
        
        // Show error in containers
        document.getElementById('system-categories').innerHTML = '<div class="text-center text-red-400 py-4">خطأ في الاتصال</div>';
        document.getElementById('custom-categories').innerHTML = '<div class="text-center text-red-400 py-4">خطأ في الاتصال</div>';
        document.getElementById('category-stats').innerHTML = '<div class="text-center text-red-400 py-4">خطأ في الاتصال</div>';
        document.getElementById('categories-table').innerHTML = '<tr><td colspan="6" class="py-8 text-center text-red-400">خطأ في الاتصال</td></tr>';
        
        showNotification('خطأ في تحميل الفئات: ' + error.message, 'error');
    }
}

// Display categories in grid format
function displayCategoriesGrid(categories) {
    const systemCategories = categories.filter(cat => cat.type === 'system');
    const customCategories = categories.filter(cat => cat.type === 'custom');
    
    // System Categories
    const systemContainer = document.getElementById('system-categories');
    if (systemCategories.length > 0) {
        systemContainer.innerHTML = systemCategories.map(category => `
            <div class="flex items-center justify-between p-3 bg-gray-700 rounded-lg">
                <div class="flex items-center">
                    <div class="w-4 h-4 rounded-full ml-3" style="background-color: ${category.color}"></div>
                    <div>
                        <div class="font-medium">${category.name_ar}</div>
                        <div class="text-xs text-gray-400">${category.name}</div>
                    </div>
                </div>
                <div class="flex space-x-2 space-x-reverse">
                    <button onclick="editCategory(${category.id})" class="text-blue-400 hover:text-blue-300">
                        <i class="fas fa-edit"></i>
                    </button>
                </div>
            </div>
        `).join('');
    } else {
        systemContainer.innerHTML = '<div class="text-center text-gray-400 py-4">لا توجد فئات افتراضية</div>';
    }
    
    // Custom Categories
    const customContainer = document.getElementById('custom-categories');
    if (customCategories.length > 0) {
        customContainer.innerHTML = customCategories.map(category => `
            <div class="flex items-center justify-between p-3 bg-gray-700 rounded-lg">
                <div class="flex items-center">
                    <div class="w-4 h-4 rounded-full mr-3" style="background-color: ${category.color}"></div>
                    <div>
                        <div class="font-medium">${category.name_ar}</div>
                        <div class="text-xs text-gray-400">${category.name}</div>
                    </div>
                </div>
                <div class="flex space-x-2 space-x-reverse">
                    <button onclick="editCategory(${category.id})" class="text-blue-400 hover:text-blue-300">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button onclick="deleteCategory(${category.id})" class="text-red-400 hover:text-red-300">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        `).join('');
    } else {
        customContainer.innerHTML = '<div class="text-center text-gray-400 py-4">لا توجد فئات مخصصة</div>';
    }
}

// Display categories statistics
function displayCategoryStats(categories) {
    const statsContainer = document.getElementById('category-stats');
    const totalCategories = categories.length;
    const systemCategories = categories.filter(cat => cat.type === 'system').length;
    const customCategories = categories.filter(cat => cat.type === 'custom').length;
    
    statsContainer.innerHTML = `
        <div class="space-y-3">
            <div class="flex justify-between items-center">
                <span class="text-sm">إجمالي الفئات</span>
                <span class="font-bold text-cyan-400">${totalCategories}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-sm">الفئات الافتراضية</span>
                <span class="font-bold text-blue-400">${systemCategories}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-sm">الفئات المخصصة</span>
                <span class="font-bold text-green-400">${customCategories}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-sm">الأكثر استخداماً</span>
                <span class="font-bold text-purple-400">${categories[0]?.name_ar || 'غير محدد'}</span>
            </div>
        </div>
    `;
}

// Display categories in table format
function displayCategoriesTable(categories) {
    const tbody = document.getElementById('categories-table');
    
    if (categories.length > 0) {
        tbody.innerHTML = categories.map(category => `
            <tr class="border-b border-gray-700 hover:bg-gray-700">
                <td class="py-3 px-4">
                    <div class="flex items-center">
                        <div class="w-4 h-4 rounded-full mr-3" style="background-color: ${category.color}"></div>
                        <div>
                            <div class="font-medium">${category.name_ar}</div>
                            <div class="text-xs text-gray-400">${category.name}</div>
                        </div>
                    </div>
                </td>
                <td class="py-3 px-4">
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs ${category.type === 'system' ? 'bg-blue-500 bg-opacity-20 text-blue-300' : 'bg-green-500 bg-opacity-20 text-green-300'}">
                        ${category.type === 'system' ? 'افتراضية' : 'مخصصة'}
                    </span>
                </td>
                <td class="py-3 px-4">
                    <div class="flex items-center">
                        <div class="w-6 h-6 rounded mr-2" style="background-color: ${category.color}"></div>
                        <span class="text-xs text-gray-400">${category.color}</span>
                    </div>
                </td>
                <td class="py-3 px-4">
                    <span class="text-sm">${category.current_month_spending || 0} معاملة</span>
                </td>
                <td class="py-3 px-4">
                    <span class="text-sm text-gray-400">${new Date().toLocaleDateString('ar-EG')}</span>
                </td>
                <td class="py-3 px-4">
                    <div class="flex space-x-2 space-x-reverse">
                        <button onclick="editCategory(${category.id})" class="text-blue-400 hover:text-blue-300">
                            <i class="fas fa-edit"></i>
                        </button>
                        ${category.type === 'custom' ? `
                            <button onclick="deleteCategory(${category.id})" class="text-red-400 hover:text-red-300">
                                <i class="fas fa-trash"></i>
                            </button>
                        ` : ''}
                    </div>
                </td>
            </tr>
        `).join('');
    } else {
        tbody.innerHTML = '<tr><td colspan="6" class="py-8 text-center text-gray-400">لا توجد فئات</td></tr>';
    }
}

// Category modal functions
function openAddCategoryModal() {
    let modal = document.getElementById('addCategoryModal');
    if (!modal) {
        modal = createCategoryModal();
        document.body.appendChild(modal);
    }
    
    modal.classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
    
    // Reset form
    const form = modal.querySelector('#addCategoryForm');
    form.reset();
    form.querySelector('input[name="id"]').value = '';
    modal.querySelector('h3').textContent = 'إضافة فئة جديدة';
}

function createCategoryModal() {
    const modal = document.createElement('div');
    modal.id = 'addCategoryModal';
    modal.className = 'fixed inset-0 bg-black bg-opacity-50 hidden z-50';
    modal.innerHTML = `
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-gray-800 rounded-lg p-6 w-full max-w-md">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold">إضافة فئة جديدة</h3>
                    <button onclick="closeCategoryModal()" class="text-gray-400 hover:text-white">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form id="addCategoryForm">
                    <input type="hidden" name="id" value="">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-2">الاسم بالعربية</label>
                            <input type="text" name="name_ar" required class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:border-cyan-500" placeholder="مثال: الطعام">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">الاسم بالإنجليزية</label>
                            <input type="text" name="name" required class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:border-cyan-500" placeholder="Example: Food">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">الأيقونة</label>
                            <input type="text" name="icon" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:border-cyan-500" placeholder="fas fa-utensils">
                            <div class="text-xs text-gray-400 mt-1">استخدم أيقونات Font Awesome مثل: fas fa-home, fas fa-car</div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">اللون</label>
                            <div class="flex items-center space-x-3 space-x-reverse">
                                <input type="color" name="color" value="#3B82F6" class="w-12 h-10 bg-gray-700 border border-gray-600 rounded cursor-pointer">
                                <input type="text" name="color_text" value="#3B82F6" class="flex-1 px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:border-cyan-500">
                            </div>
                        </div>
                        <div class="grid grid-cols-4 gap-2 mt-2">
                            <button type="button" onclick="selectColor('#EF4444')" class="w-8 h-8 rounded" style="background-color: #EF4444"></button>
                            <button type="button" onclick="selectColor('#10B981')" class="w-8 h-8 rounded" style="background-color: #10B981"></button>
                            <button type="button" onclick="selectColor('#3B82F6')" class="w-8 h-8 rounded" style="background-color: #3B82F6"></button>
                            <button type="button" onclick="selectColor('#8B5CF6')" class="w-8 h-8 rounded" style="background-color: #8B5CF6"></button>
                            <button type="button" onclick="selectColor('#F59E0B')" class="w-8 h-8 rounded" style="background-color: #F59E0B"></button>
                            <button type="button" onclick="selectColor('#EC4899')" class="w-8 h-8 rounded" style="background-color: #EC4899"></button>
                            <button type="button" onclick="selectColor('#06B6D4')" class="w-8 h-8 rounded" style="background-color: #06B6D4"></button>
                            <button type="button" onclick="selectColor('#84CC16')" class="w-8 h-8 rounded" style="background-color: #84CC16"></button>
                        </div>
                    </div>
                    <div class="flex justify-end space-x-3 space-x-reverse mt-6">
                        <button type="button" onclick="closeCategoryModal()" class="px-4 py-2 text-gray-400 hover:text-white">
                            إلغاء
                        </button>
                        <button type="submit" class="px-4 py-2 bg-cyan-500 hover:bg-cyan-600 text-white rounded-lg">
                            حفظ
                        </button>
                    </div>
                </form>
            </div>
        </div>
    `;
    
    // Add event listeners
    modal.querySelector('#addCategoryForm').addEventListener('submit', function(e) {
        e.preventDefault();
        submitCategory();
    });
    
    // Sync color inputs
    const colorInput = modal.querySelector('input[name="color"]');
    const colorTextInput = modal.querySelector('input[name="color_text"]');
    
    colorInput.addEventListener('change', function() {
        colorTextInput.value = this.value;
    });
    
    colorTextInput.addEventListener('change', function() {
        if (/^#[0-9A-F]{6}$/i.test(this.value)) {
            colorInput.value = this.value;
        }
    });
    
    return modal;
}

function closeCategoryModal() {
    const modal = document.getElementById('addCategoryModal');
    if (modal) {
        modal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
        modal.querySelector('#addCategoryForm').reset();
    }
}

function selectColor(color) {
    const modal = document.getElementById('addCategoryModal');
    if (modal) {
        modal.querySelector('input[name="color"]').value = color;
        modal.querySelector('input[name="color_text"]').value = color;
    }
}

async function submitCategory() {
    try {
        const form = document.getElementById('addCategoryForm');
        const formData = new FormData(form);
        const categoryId = formData.get('id');
        
        const categoryData = {
            name: formData.get('name'),
            name_ar: formData.get('name_ar'),
            icon: formData.get('icon') || null,
            color: formData.get('color') || '#3B82F6'
        };
        
        let response;
        if (categoryId) {
            // Update existing category
            response = await fetch('api/categories.php', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    id: parseInt(categoryId),
                    ...categoryData
                })
            });
        } else {
            // Create new category
            response = await fetch('api/categories.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(categoryData)
            });
        }
        
        const data = await response.json();
        
        if (data.success) {
            showNotification(categoryId ? 'تم تحديث الفئة بنجاح' : 'تم إضافة الفئة بنجاح', 'success');
            closeCategoryModal();
            loadCategoriesData(); // Reload categories
        } else {
            showNotification(data.message || 'حدث خطأ أثناء حفظ الفئة', 'error');
        }
    } catch (error) {
        console.error('Failed to submit category:', error);
        showNotification('خطأ في الاتصال بالخادم', 'error');
    }
}

async function editCategory(categoryId) {
    try {
        const data = await apiRequest(`api/categories.php?id=${categoryId}`);
        
        if (data.success && data.category) {
            const category = data.category;
            
            // Open modal
            openAddCategoryModal();
            
            // Fill form with category data
            const modal = document.getElementById('addCategoryModal');
            const form = modal.querySelector('#addCategoryForm');
            
            form.querySelector('input[name="id"]').value = category.id;
            form.querySelector('input[name="name_ar"]').value = category.name_ar;
            form.querySelector('input[name="name"]').value = category.name;
            form.querySelector('input[name="icon"]').value = category.icon || '';
            form.querySelector('input[name="color"]').value = category.color;
            form.querySelector('input[name="color_text"]').value = category.color;
            
            modal.querySelector('h3').textContent = 'تعديل الفئة';
        } else {
            showNotification('فشل في تحميل بيانات الفئة', 'error');
        }
    } catch (error) {
        console.error('Failed to load category for editing:', error);
        showNotification('خطأ في تحميل بيانات الفئة', 'error');
    }
}

async function deleteCategory(categoryId) {
    const confirmed = await showConfirmation(
        'حذف الفئة',
        'هل أنت متأكد من حذف هذه الفئة؟ سيتم حذف جميع المعاملات المرتبطة بها.',
        'حذف',
        'إلغاء'
    );
    
    if (!confirmed) {
        return;
    }
    
    try {
        const response = await fetch('api/categories.php', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id: categoryId })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification('تم حذف الفئة بنجاح', 'success');
            loadCategoriesData(); // Reload categories
        } else {
            showNotification(data.message || 'حدث خطأ أثناء حذف الفئة', 'error');
        }
    } catch (error) {
        console.error('Failed to delete category:', error);
        showNotification('خطأ في حذف الفئة', 'error');
    }
}

function loadUsersSection() {
    const section = document.getElementById('users-section');
    section.innerHTML = `
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold">إدارة المستخدمين</h2>
            <button onclick="openAddUserModal()" class="bg-cyan-500 hover:bg-cyan-600 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-user-plus ml-2"></i>
                إضافة مستخدم
            </button>
        </div>
        
        <div class="bg-gray-800 rounded-lg p-6">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-gray-400 border-b border-gray-700">
                            <th class="text-right py-3">الاسم</th>
                            <th class="text-right py-3">البريد الإلكتروني</th>
                            <th class="text-right py-3">الدور</th>
                            <th class="text-right py-3">تاريخ الإنشاء</th>
                            <th class="text-right py-3">الحالة</th>
                            <th class="text-right py-3">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody id="users-table">
                        <!-- Users will be loaded here -->
                    </tbody>
                </table>
            </div>
        </div>
    `;
    
    loadUsers();
}

// Utility functions
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('ar-SA');
}

function getStatusClass(status) {
    switch(status) {
        case 'cleared': return 'bg-green-500 bg-opacity-20 text-green-300';
        case 'pending': return 'bg-yellow-500 bg-opacity-20 text-yellow-300';
        case 'cancelled': return 'bg-red-500 bg-opacity-20 text-red-300';
        default: return 'bg-gray-500 bg-opacity-20 text-gray-300';
    }
}

function getStatusText(status) {
    switch(status) {
        case 'cleared': return 'مكتمل';
        case 'pending': return 'معلق';
        case 'cancelled': return 'ملغي';
        default: return 'غير محدد';
    }
}

function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 left-4 z-50 px-6 py-3 rounded-lg shadow-lg transition-all duration-300 ${
        type === 'success' ? 'bg-green-500' : 
        type === 'error' ? 'bg-red-500' : 
        'bg-blue-500'
    } text-white`;
    
    notification.innerHTML = `
        <div class="flex items-center">
            <i class="fas ${
                type === 'success' ? 'fa-check-circle' : 
                type === 'error' ? 'fa-exclamation-circle' : 
                'fa-info-circle'
            } ml-2"></i>
            ${message}
        </div>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 5000);
}

async function logout() {
    const confirmed = await showConfirmation(
        'تسجيل الخروج',
        'هل أنت متأكد من تسجيل الخروج؟ سيتم إنهاء جلستك الحالية.',
        'تسجيل الخروج',
        'إلغاء'
    );
    
    if (confirmed) {
        // Show loading state
        Swal.fire({
            ...window.swalConfig,
            title: 'جاري تسجيل الخروج...',
            text: 'يرجى الانتظار',
            icon: 'info',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        // Redirect after a short delay
        setTimeout(() => {
            window.location.href = 'api/auth.php?action=logout';
        }, 1000);
    }
}

// Mobile menu toggle
function toggleMobileMenu() {
    const sidebar = document.getElementById('sidebar');
    const isOpen = !sidebar.classList.contains('translate-x-full');
    
    if (isOpen) {
        sidebar.classList.add('translate-x-full');
    } else {
        sidebar.classList.remove('translate-x-full');
    }
}

// Load all transactions with pagination and filters
async function loadAllTransactions() {
    try {
        const urlParams = new URLSearchParams({
            action: 'all',
            page: 1,
            limit: 20
        });
        
        const data = await apiRequest(`api/transactions.php?${urlParams}`);
        
        if (data.success) {
            const tbody = document.getElementById('transactions-table');
            tbody.innerHTML = '';
            
            data.transactions.forEach(transaction => {
                const row = document.createElement('tr');
                row.className = 'border-b border-gray-700 hover:bg-gray-700';
                row.innerHTML = `
                    <td class="py-3 text-sm">${formatDate(transaction.transaction_date)}</td>
                    <td class="py-3 text-sm">${transaction.description}</td>
                    <td class="py-3 text-sm">
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs" style="background-color: ${transaction.category_color}20; color: ${transaction.category_color}">
                            ${transaction.category_name}
                        </span>
                    </td>
                    <td class="py-3 text-sm">${transaction.merchant || '-'}</td>
                    <td class="py-3 text-sm font-medium">€${parseFloat(transaction.amount).toFixed(2)}</td>
                    <td class="py-3">
                        <span class="px-2 py-1 text-xs rounded-full ${getStatusClass(transaction.status)}">
                            ${getStatusText(transaction.status)}
                        </span>
                    </td>
                    <td class="py-3">
                        <div class="flex space-x-2 space-x-reverse">
                            <button onclick="editTransaction(${transaction.id})" class="text-blue-400 hover:text-blue-300">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="deleteTransaction(${transaction.id})" class="text-red-400 hover:text-red-300">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                `;
                tbody.appendChild(row);
            });
        }
    } catch (error) {
        console.error('Failed to load transactions:', error);
    }
}

// Load all income records
async function loadAllIncome() {
    try {
        const data = await apiRequest('api/income.php?action=all&limit=20');
        
        if (data.success) {
            const tbody = document.getElementById('income-table');
            tbody.innerHTML = '';
            
            if (data.income && data.income.length > 0) {
                data.income.forEach(income => {
                    const row = document.createElement('tr');
                    row.className = 'border-b border-gray-700 hover:bg-gray-700';
                    row.innerHTML = `
                        <td class="py-3 text-sm">${formatDate(income.transaction_date)}</td>
                        <td class="py-3 text-sm">${income.description}</td>
                        <td class="py-3 text-sm">
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs" style="background-color: ${income.category_color}20; color: ${income.category_color}">
                                ${income.category_name}
                            </span>
                        </td>
                        <td class="py-3 text-sm">${income.merchant || '-'}</td>
                        <td class="py-3 text-sm font-medium text-green-400">€${parseFloat(income.amount).toFixed(2)}</td>
                        <td class="py-3">
                            <span class="px-2 py-1 text-xs rounded-full ${getStatusClass(income.status)}">
                                ${getStatusText(income.status)}
                            </span>
                        </td>
                        <td class="py-3">
                            <div class="flex space-x-2 space-x-reverse">
                                <button onclick="editIncome(${income.id})" class="text-blue-400 hover:text-blue-300">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="deleteIncome(${income.id})" class="text-red-400 hover:text-red-300">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    `;
                    tbody.appendChild(row);
                });
            } else {
                tbody.innerHTML = '<tr><td colspan="7" class="py-8 text-center text-gray-400">لا يوجد دخل مسجل</td></tr>';
            }
        }
    } catch (error) {
        console.error('Failed to load income:', error);
        const tbody = document.getElementById('income-table');
        if (tbody) {
            tbody.innerHTML = '<tr><td colspan="7" class="py-8 text-center text-red-400">خطأ في تحميل البيانات</td></tr>';
        }
    }
}

// Load budgets for budgets section
async function loadBudgets() {
    try {
        const data = await apiRequest('api/budgets.php?action=current');
        
        if (data.success) {
            const grid = document.getElementById('budgets-grid');
            grid.innerHTML = '';
            
            if (data.budgets && data.budgets.length > 0) {
                data.budgets.forEach(budget => {
                const budgetCard = document.createElement('div');
                budgetCard.className = 'bg-gray-800 rounded-lg p-6';
                
                const percentage = parseFloat(budget.percentage_used) || 0;
                const isOverBudget = percentage > 100;
                
                budgetCard.innerHTML = `
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center" style="background-color: ${budget.category_color}20">
                                <i class="${budget.category_icon} text-lg" style="color: ${budget.category_color}"></i>
                            </div>
                            <div class="ml-3">
                                <h3 class="font-semibold">${budget.category_name}</h3>
                                <p class="text-sm text-gray-400">${budget.period === 'monthly' ? 'شهري' : 'سنوي'}</p>
                            </div>
                        </div>
                        <div class="text-left">
                            <div class="text-lg font-bold">€${(parseFloat(budget.amount) || 0).toFixed(2)}</div>
                            <div class="text-sm text-gray-400">الميزانية</div>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <div class="flex justify-between text-sm mb-2">
                            <span>المستخدم: €${(parseFloat(budget.actual_amount) || 0).toFixed(2)}</span>
                            <span class="${isOverBudget ? 'text-red-400' : 'text-green-400'}">${percentage.toFixed(1)}%</span>
                        </div>
                        <div class="w-full bg-gray-700 rounded-full h-2">
                            <div class="h-2 rounded-full ${isOverBudget ? 'bg-red-500' : 'bg-green-500'}" 
                                 style="width: ${Math.min(percentage, 100)}%"></div>
                        </div>
                    </div>
                    
                    <div class="flex justify-between">
                        <button onclick="editBudget(${budget.id})" class="text-blue-400 hover:text-blue-300 text-sm">
                            <i class="fas fa-edit ml-1"></i> تعديل
                        </button>
                        <button onclick="deleteBudget(${budget.id})" class="text-red-400 hover:text-red-300 text-sm">
                            <i class="fas fa-trash ml-1"></i> حذف
                        </button>
                    </div>
                `;
                
                grid.appendChild(budgetCard);
                });
            } else {
                grid.innerHTML = '<div class="bg-gray-800 rounded-lg p-6 text-center col-span-full"><p class="text-gray-400">لا توجد ميزانيات مضافة</p></div>';
            }
        }
    } catch (error) {
        console.error('Failed to load budgets:', error);
        const grid = document.getElementById('budgets-grid');
        if (grid) {
            grid.innerHTML = '<div class="bg-gray-800 rounded-lg p-6 text-center col-span-full"><p class="text-red-400">خطأ في تحميل الميزانيات</p></div>';
        }
    }
}

// Load users for admin section
async function loadUsers() {
    try {
        const data = await apiRequest('api/users.php?action=all');
        
        if (data.success) {
            const tbody = document.getElementById('users-table');
            tbody.innerHTML = '';
            
            data.users.forEach(user => {
                const row = document.createElement('tr');
                row.className = 'border-b border-gray-700 hover:bg-gray-700';
                row.innerHTML = `
                    <td class="py-3 text-sm">
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-gray-600 rounded-full flex items-center justify-center ml-3">
                                <i class="fas fa-user text-sm"></i>
                            </div>
                            ${user.name}
                        </div>
                    </td>
                    <td class="py-3 text-sm">${user.email}</td>
                    <td class="py-3">
                        <span class="px-2 py-1 text-xs rounded-full ${getRoleClass(user.role)}">
                            ${getRoleText(user.role)}
                        </span>
                    </td>
                    <td class="py-3 text-sm">${formatDate(user.created_at)}</td>
                    <td class="py-3">
                        <span class="px-2 py-1 text-xs rounded-full ${user.is_active ? 'bg-green-500 bg-opacity-20 text-green-300' : 'bg-red-500 bg-opacity-20 text-red-300'}">
                            ${user.is_active ? 'نشط' : 'غير نشط'}
                        </span>
                    </td>
                    <td class="py-3">
                        <div class="flex space-x-2 space-x-reverse">
                            <button onclick="editUser(${user.id})" class="text-blue-400 hover:text-blue-300">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="deleteUser(${user.id})" class="text-red-400 hover:text-red-300">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                `;
                tbody.appendChild(row);
            });
        }
    } catch (error) {
        console.error('Failed to load users:', error);
    }
}

// Update dashboard statistics (deprecated - use loadDashboardData instead)
async function updateDashboardStats() {
    // Call the new dashboard data loading function
    await loadDashboardData();
}

// Modal functions for budgets
function openAddBudgetModal() {
    // Create budget modal if it doesn't exist
    let modal = document.getElementById('addBudgetModal');
    if (!modal) {
        modal = createBudgetModal();
        document.body.appendChild(modal);
    }
    
    modal.classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
    
    // Load categories after a small delay to ensure modal is rendered
    setTimeout(() => {
        loadCategoriesForBudget();
    }, 100);
}

function createBudgetModal() {
    const modal = document.createElement('div');
    modal.id = 'addBudgetModal';
    modal.className = 'fixed inset-0 bg-black bg-opacity-50 hidden z-50';
    modal.innerHTML = `
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-gray-800 rounded-lg p-6 w-full max-w-md">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold">إضافة ميزانية جديدة</h3>
                    <button onclick="closeAddBudgetModal()" class="text-gray-400 hover:text-white">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form id="addBudgetForm">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-2">الفئة</label>
                            <select name="category_id" required class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:border-cyan-500">
                                <option value="">جاري تحميل الفئات...</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">المبلغ</label>
                            <input type="number" name="amount" step="0.01" required class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:border-cyan-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">الفترة</label>
                            <select name="period" required class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:border-cyan-500">
                                <option value="monthly">شهري</option>
                                <option value="yearly">سنوي</option>
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium mb-2">تاريخ البداية</label>
                                <input type="date" name="start_date" required class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:border-cyan-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2">تاريخ النهاية</label>
                                <input type="date" name="end_date" required class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:border-cyan-500">
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-end space-x-3 space-x-reverse mt-6">
                        <button type="button" onclick="closeAddBudgetModal()" class="px-4 py-2 text-gray-400 hover:text-white">
                            إلغاء
                        </button>
                        <button type="submit" class="px-4 py-2 bg-cyan-500 hover:bg-cyan-600 text-white rounded-lg">
                            إضافة
                        </button>
                    </div>
                </form>
            </div>
        </div>
    `;
    
    // Add event listener for form submission
    modal.querySelector('#addBudgetForm').addEventListener('submit', function(e) {
        e.preventDefault();
        submitBudget();
    });
    
    return modal;
}

function closeAddBudgetModal() {
    const modal = document.getElementById('addBudgetModal');
    if (modal) {
        modal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
        const form = modal.querySelector('#addBudgetForm');
        form.reset();
        
        // Remove the budget_id field if it exists
        const idField = form.querySelector('input[name="budget_id"]');
        if (idField) {
            idField.remove();
        }
        
        // Reset modal title
        modal.querySelector('h3').textContent = 'إضافة ميزانية جديدة';
    }
}

// Modal functions for users
function openAddIncomeModal() {
    // Create income modal if it doesn't exist
    let modal = document.getElementById('addIncomeModal');
    if (!modal) {
        modal = createIncomeModal();
        document.body.appendChild(modal);
    }
    
    modal.classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
    loadCategoriesForIncome();
}

function openAddUserModal() {
    // Create user modal if it doesn't exist
    let modal = document.getElementById('addUserModal');
    if (!modal) {
        modal = createUserModal();
        document.body.appendChild(modal);
    }
    
    modal.classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
}

function createUserModal() {
    const modal = document.createElement('div');
    modal.id = 'addUserModal';
    modal.className = 'fixed inset-0 bg-black bg-opacity-50 hidden z-50';
    modal.innerHTML = `
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-gray-800 rounded-lg p-6 w-full max-w-md">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold">إضافة مستخدم جديد</h3>
                    <button onclick="closeAddUserModal()" class="text-gray-400 hover:text-white">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form id="addUserForm">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-2">الاسم الكامل</label>
                            <input type="text" name="name" required class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:border-cyan-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">البريد الإلكتروني</label>
                            <input type="email" name="email" required class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:border-cyan-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">كلمة المرور</label>
                            <input type="password" name="password" required minlength="6" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:border-cyan-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">الدور</label>
                            <select name="role" required class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:border-cyan-500">
                                <option value="user">مستخدم عادي</option>
                                <option value="admin">مدير</option>
                                <option value="super_admin">مدير عام</option>
                            </select>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" name="is_active" checked class="rounded border-gray-600 text-cyan-500 focus:ring-cyan-500 focus:ring-offset-gray-800">
                            <label class="mr-2 text-sm">حساب نشط</label>
                        </div>
                    </div>
                    <div class="flex justify-end space-x-3 space-x-reverse mt-6">
                        <button type="button" onclick="closeAddUserModal()" class="px-4 py-2 text-gray-400 hover:text-white">
                            إلغاء
                        </button>
                        <button type="submit" class="px-4 py-2 bg-cyan-500 hover:bg-cyan-600 text-white rounded-lg">
                            إضافة
                        </button>
                    </div>
                </form>
            </div>
        </div>
    `;
    
    // Add event listener for form submission
    modal.querySelector('#addUserForm').addEventListener('submit', function(e) {
        e.preventDefault();
        submitUser();
    });
    
    return modal;
}

function createIncomeModal() {
    const modal = document.createElement('div');
    modal.id = 'addIncomeModal';
    modal.className = 'fixed inset-0 bg-black bg-opacity-50 hidden z-50';
    modal.innerHTML = `
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-gray-800 rounded-lg p-6 w-full max-w-md">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-green-400">إضافة دخل جديد</h3>
                    <button onclick="closeAddIncomeModal()" class="text-gray-400 hover:text-white">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form id="addIncomeForm">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-2">الوصف</label>
                            <input type="text" name="description" required class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:border-green-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">المبلغ</label>
                            <input type="number" name="amount" step="0.01" required class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:border-green-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">فئة الدخل</label>
                            <select name="category_id" required class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:border-green-500">
                                <option value="">اختر فئة الدخل</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">المصدر</label>
                            <input type="text" name="merchant" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:border-green-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">التاريخ</label>
                            <input type="date" name="transaction_date" required class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:border-green-500">
                        </div>
                    </div>
                    <div class="flex justify-end space-x-3 space-x-reverse mt-6">
                        <button type="button" onclick="closeAddIncomeModal()" class="px-4 py-2 text-gray-400 hover:text-white">
                            إلغاء
                        </button>
                        <button type="submit" class="px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-lg">
                            إضافة
                        </button>
                    </div>
                </form>
            </div>
        </div>
    `;
    
    // Add event listener for form submission
    modal.querySelector('#addIncomeForm').addEventListener('submit', function(e) {
        e.preventDefault();
        submitIncome();
    });
    
    return modal;
}

function closeAddIncomeModal() {
    const modal = document.getElementById('addIncomeModal');
    if (modal) {
        modal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
        modal.querySelector('#addIncomeForm').reset();
    }
}

function createEditTransactionModal() {
    // Remove existing modal if it exists
    const existingModal = document.getElementById('editTransactionModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    const modal = document.createElement('div');
    modal.id = 'editTransactionModal';
    modal.className = 'fixed inset-0 bg-black bg-opacity-50 hidden z-50';
    modal.innerHTML = `
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-gray-800 rounded-lg p-6 w-full max-w-md max-h-screen overflow-y-auto">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-blue-400">تعديل المعاملة</h3>
                    <button onclick="closeEditTransactionModal()" class="text-gray-400 hover:text-white">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form id="editTransactionForm">
                    <input type="hidden" name="id">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-2">الوصف</label>
                            <input type="text" name="description" required class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">المبلغ</label>
                            <input type="number" name="amount" step="0.01" required class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">الفئة</label>
                            <select name="category_id" required class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:border-blue-500">
                                <option value="">اختر الفئة</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">التاجر/المكان</label>
                            <input type="text" name="merchant" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">التاريخ</label>
                            <input type="date" name="transaction_date" required class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">الحالة</label>
                            <select name="status" required class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:border-blue-500">
                                <option value="pending">معلقة</option>
                                <option value="cleared">مؤكدة</option>
                                <option value="cancelled">ملغية</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">ملاحظات</label>
                            <textarea name="notes" rows="3" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:border-blue-500"></textarea>
                        </div>
                    </div>
                    <div class="flex justify-end space-x-3 space-x-reverse mt-6">
                        <button type="button" onclick="closeEditTransactionModal()" class="px-4 py-2 text-gray-400 hover:text-white">
                            إلغاء
                        </button>
                        <button type="submit" class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg">
                            حفظ التغييرات
                        </button>
                    </div>
                </form>
            </div>
        </div>
    `;
    
    // Add event listener for form submission
    modal.querySelector('#editTransactionForm').addEventListener('submit', function(e) {
        e.preventDefault();
        submitEditTransaction();
    });
    
    return modal;
}

function closeEditTransactionModal() {
    const modal = document.getElementById('editTransactionModal');
    if (modal) {
        modal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
        modal.querySelector('#editTransactionForm').reset();
    }
}

function createEditIncomeModal() {
    const modal = document.createElement('div');
    modal.id = 'editIncomeModal';
    modal.className = 'fixed inset-0 bg-black bg-opacity-50 hidden z-50';
    modal.innerHTML = `
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-gray-800 rounded-lg p-6 w-full max-w-md max-h-screen overflow-y-auto">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-green-400">تعديل الدخل</h3>
                    <button onclick="closeEditIncomeModal()" class="text-gray-400 hover:text-white">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form id="editIncomeForm">
                    <input type="hidden" name="id">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-2">الوصف</label>
                            <input type="text" name="description" required class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:border-green-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">المبلغ</label>
                            <input type="number" name="amount" step="0.01" required class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:border-green-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">فئة الدخل</label>
                            <select name="category_id" required class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:border-green-500">
                                <option value="">اختر فئة الدخل</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">المصدر</label>
                            <input type="text" name="merchant" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:border-green-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">التاريخ</label>
                            <input type="date" name="transaction_date" required class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:border-green-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">الحالة</label>
                            <select name="status" required class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:border-green-500">
                                <option value="pending">معلقة</option>
                                <option value="cleared">مؤكدة</option>
                                <option value="cancelled">ملغية</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">ملاحظات</label>
                            <textarea name="notes" rows="3" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:border-green-500"></textarea>
                        </div>
                    </div>
                    <div class="flex justify-end space-x-3 space-x-reverse mt-6">
                        <button type="button" onclick="closeEditIncomeModal()" class="px-4 py-2 text-gray-400 hover:text-white">
                            إلغاء
                        </button>
                        <button type="submit" class="px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-lg">
                            حفظ التغييرات
                        </button>
                    </div>
                </form>
            </div>
        </div>
    `;
    
    // Add event listener for form submission
    modal.querySelector('#editIncomeForm').addEventListener('submit', function(e) {
        e.preventDefault();
        submitEditIncome();
    });
    
    return modal;
}

function closeEditIncomeModal() {
    const modal = document.getElementById('editIncomeModal');
    if (modal) {
        modal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
        modal.querySelector('#editIncomeForm').reset();
    }
}

function closeAddUserModal() {
    const modal = document.getElementById('addUserModal');
    if (modal) {
        modal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
        modal.querySelector('#addUserForm').reset();
    }
}

// Submit budget form
async function submitBudget() {
    const form = document.getElementById('addBudgetForm');
    const formData = new FormData(form);
    const budgetId = formData.get('budget_id');
    
    const budgetData = {
        category_id: parseInt(formData.get('category_id')),
        amount: parseFloat(formData.get('amount')),
        period: formData.get('period'),
        start_date: formData.get('start_date'),
        end_date: formData.get('end_date')
    };

    try {
        let response;
        if (budgetId) {
            // Update existing budget
            budgetData.id = parseInt(budgetId);
            response = await fetch('api/budgets.php', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(budgetData)
            });
        } else {
            // Create new budget
            response = await fetch('api/budgets.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(budgetData)
            });
        }

        const data = await response.json();

        if (data.success) {
            showNotification(budgetId ? 'تم تحديث الميزانية بنجاح' : 'تم إضافة الميزانية بنجاح', 'success');
            closeAddBudgetModal();
            if (window.currentSection === 'budgets') {
                loadBudgets();
            }
            updateDashboardStats();
        } else {
            showNotification(data.message || (budgetId ? 'حدث خطأ أثناء تحديث الميزانية' : 'حدث خطأ أثناء إضافة الميزانية'), 'error');
        }
    } catch (error) {
        console.error('Failed to submit budget:', error);
        showNotification('خطأ في الاتصال بالخادم', 'error');
    }
}

// Submit user form
async function submitUser() {
    const form = document.getElementById('addUserForm');
    const formData = new FormData(form);
    
    const userData = {
        name: formData.get('name'),
        email: formData.get('email'),
        password: formData.get('password'),
        role: formData.get('role'),
        is_active: formData.get('is_active') ? true : false
    };

    try {
        const data = await apiRequest('api/users.php', {
            method: 'POST',
            body: JSON.stringify(userData)
        });

        if (data.success) {
            showNotification('تم إضافة المستخدم بنجاح', 'success');
            closeAddUserModal();
            if (window.currentSection === 'users') {
                loadUsers();
            }
        } else {
            showNotification(data.message || 'حدث خطأ أثناء إضافة المستخدم', 'error');
        }
    } catch (error) {
        console.error('Failed to submit user:', error);
    }
}

// Submit income form
async function submitIncome() {
    const form = document.getElementById('addIncomeForm');
    const formData = new FormData(form);
    
    const incomeData = {
        description: formData.get('description'),
        amount: parseFloat(formData.get('amount')),
        category_id: parseInt(formData.get('category_id')),
        merchant: formData.get('merchant'),
        transaction_date: formData.get('transaction_date')
    };

    try {
        const data = await apiRequest('api/income.php', {
            method: 'POST',
            body: JSON.stringify(incomeData)
        });

        if (data.success) {
            showNotification('تم إضافة الدخل بنجاح', 'success');
            closeAddIncomeModal();
            if (window.currentSection === 'income') {
                loadAllIncome();
            }
            updateDashboardStats();
        } else {
            showNotification(data.message || 'حدث خطأ أثناء إضافة الدخل', 'error');
        }
    } catch (error) {
        console.error('Failed to submit income:', error);
    }
}

// Load categories for budget modal
async function loadCategoriesForBudget() {
    try {
        console.log('Loading categories for budget...');
        
        const select = document.querySelector('#addBudgetModal select[name="category_id"]');
        if (!select) {
            console.error('Category select element not found in budget modal');
            return;
        }
        
        // Show loading state
        select.innerHTML = '<option value="">جاري تحميل الفئات...</option>';
        
        const data = await apiRequest('api/categories.php');
        console.log('Categories API response:', data);
        
        if (data.success && data.categories && data.categories.length > 0) {
            select.innerHTML = '<option value="">اختر الفئة</option>';
            
            // Load all categories since budgets are typically for expense categories
            data.categories.forEach(category => {
                const option = document.createElement('option');
                option.value = category.id;
                option.textContent = category.name_ar;
                if (category.color) {
                    option.style.backgroundColor = category.color + '20';
                }
                select.appendChild(option);
                console.log('Added category:', category.name_ar, 'ID:', category.id);
            });
            
            console.log(`Loaded ${data.categories.length} categories for budget`);
            showNotification(`تم تحميل ${data.categories.length} فئة`, 'success');
        } else {
            console.error('No categories found or API failed:', data);
            select.innerHTML = '<option value="">لا توجد فئات متاحة</option>';
            
            // Show error message
            if (data.message) {
                showNotification(data.message, 'error');
            } else {
                showNotification('لا توجد فئات متاحة. يرجى إضافة فئات أولاً.', 'warning');
            }
        }
    } catch (error) {
        console.error('Failed to load categories for budget:', error);
        const select = document.querySelector('#addBudgetModal select[name="category_id"]');
        if (select) {
            select.innerHTML = '<option value="">خطأ في تحميل الفئات</option>';
        }
        showNotification('خطأ في تحميل الفئات', 'error');
    }
}

// Load categories for income modal
async function loadCategoriesForIncome() {
    try {
        const data = await apiRequest('api/income.php?action=categories');
        
        if (data.success) {
            const select = document.querySelector('#addIncomeModal select[name="category_id"]');
            select.innerHTML = '<option value="">اختر فئة الدخل</option>';
            
            data.categories.forEach(category => {
                const option = document.createElement('option');
                option.value = category.id;
                option.textContent = category.name_ar;
                select.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Failed to load income categories:', error);
    }
}

// Load categories for edit transaction modal
async function loadCategoriesForEditTransaction(selectedCategoryId = null) {
    try {
        const data = await apiRequest('api/categories.php');
        
        if (data.success && data.categories) {
            const select = document.querySelector('#editTransactionModal select[name="category_id"]');
            if (!select) {
                console.error('Category select element not found in edit modal');
                return false;
            }
            
            select.innerHTML = '<option value="">اختر الفئة</option>';
            
            data.categories.forEach(category => {
                if (category.category_type === 'expense' || category.category_type === 'both') {
                    const option = document.createElement('option');
                    option.value = category.id;
                    option.textContent = category.name_ar || category.name;
                    
                    // Pre-select if this is the current category
                    if (selectedCategoryId && category.id == selectedCategoryId) {
                        option.selected = true;
                    }
                    
                    select.appendChild(option);
                }
            });
            
            console.log(`Loaded ${data.categories.length} categories for edit transaction modal`);
            return true;
        } else {
            console.error('Failed to load categories: Invalid response', data);
            return false;
        }
    } catch (error) {
        console.error('Failed to load categories for edit transaction:', error);
        showNotification('خطأ في تحميل الفئات', 'error');
        return false;
    }
}

// Submit edit transaction form
async function submitEditTransaction() {
    const form = document.getElementById('editTransactionForm');
    const formData = new FormData(form);
    
    const transactionData = {
        id: parseInt(formData.get('id')),
        description: formData.get('description'),
        amount: parseFloat(formData.get('amount')),
        category_id: parseInt(formData.get('category_id')),
        merchant: formData.get('merchant'),
        transaction_date: formData.get('transaction_date'),
        status: formData.get('status'),
        notes: formData.get('notes')
    };

    try {
        const data = await apiRequest('api/transactions.php', {
            method: 'PUT',
            body: JSON.stringify(transactionData)
        });

        if (data.success) {
            showNotification('تم تحديث المعاملة بنجاح', 'success');
            closeEditTransactionModal();
            if (window.currentSection === 'transactions') {
                loadAllTransactions();
            }
            // Update dashboard stats
            updateDashboardStats();
        } else {
            showNotification(data.message || 'حدث خطأ أثناء تحديث المعاملة', 'error');
        }
    } catch (error) {
        console.error('Failed to submit edit transaction:', error);
        showNotification('حدث خطأ أثناء تحديث المعاملة', 'error');
    }
}

// Submit edit income form
async function submitEditIncome() {
    const form = document.getElementById('editIncomeForm');
    const formData = new FormData(form);
    
    const incomeData = {
        id: parseInt(formData.get('id')),
        description: formData.get('description'),
        amount: parseFloat(formData.get('amount')),
        category_id: parseInt(formData.get('category_id')),
        merchant: formData.get('merchant'),
        transaction_date: formData.get('transaction_date'),
        status: formData.get('status'),
        notes: formData.get('notes')
    };

    try {
        const data = await apiRequest('api/income.php', {
            method: 'PUT',
            body: JSON.stringify(incomeData)
        });

        if (data.success) {
            showNotification('تم تحديث الدخل بنجاح', 'success');
            closeEditIncomeModal();
            if (window.currentSection === 'income') {
                loadAllIncome();
            }
            // Update dashboard stats
            updateDashboardStats();
        } else {
            showNotification(data.message || 'حدث خطأ أثناء تحديث الدخل', 'error');
        }
    } catch (error) {
        console.error('Failed to submit edit income:', error);
        showNotification('حدث خطأ أثناء تحديث الدخل', 'error');
    }
}

// Load categories for edit income modal
async function loadCategoriesForEditIncome() {
    try {
        const data = await apiRequest('api/income.php?action=categories');
        
        if (data.success) {
            const select = document.querySelector('#editIncomeModal select[name="category_id"]');
            select.innerHTML = '<option value="">اختر فئة الدخل</option>';
            
            data.categories.forEach(category => {
                const option = document.createElement('option');
                option.value = category.id;
                option.textContent = category.name_ar;
                select.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Failed to load income categories for edit:', error);
    }
}

// CRUD operation functions
async function editTransaction(id) {
    try {
        // First, get the transaction data
        const response = await apiRequest(`api/transactions.php?action=get&id=${id}`);
        
        if (response.success && response.transaction) {
            const transaction = response.transaction;
            
            // Create edit modal if it doesn't exist
            let modal = document.getElementById('editTransactionModal');
            if (!modal) {
                modal = createEditTransactionModal();
                document.body.appendChild(modal);
                
                // Wait a bit for modal to be added to DOM
                await new Promise(resolve => setTimeout(resolve, 50));
            }
            
            // Populate the form with existing data first
            const form = modal.querySelector('#editTransactionForm');
            form.querySelector('input[name="id"]').value = transaction.id;
            form.querySelector('input[name="description"]').value = transaction.description;
            form.querySelector('input[name="amount"]').value = transaction.amount;
            form.querySelector('input[name="merchant"]').value = transaction.merchant || '';
            form.querySelector('input[name="transaction_date"]').value = transaction.transaction_date;
            form.querySelector('select[name="status"]').value = transaction.status;
            form.querySelector('textarea[name="notes"]').value = transaction.notes || '';
            
            // Load categories with pre-selection using the improved function
            const categoriesLoaded = await loadCategories('editTransactionModal', transaction.category_id);
            
            if (categoriesLoaded) {
                console.log(`Successfully loaded categories and pre-selected: ${transaction.category_id}`);
            } else {
                console.error('Failed to load categories for edit modal');
                showNotification('خطأ في تحميل الفئات', 'error');
            }
            
            modal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        } else {
            showNotification('لم يتم العثور على المعاملة', 'error');
        }
    } catch (error) {
        console.error('Failed to load transaction for editing:', error);
        showNotification('حدث خطأ أثناء تحميل بيانات المعاملة', 'error');
    }
}

async function deleteTransaction(id) {
    const confirmed = await showConfirmation(
        'حذف المعاملة',
        'هل أنت متأكد من حذف هذه المعاملة؟ لا يمكن التراجع عن هذا الإجراء.',
        'حذف',
        'إلغاء'
    );
    
    if (!confirmed) {
        return;
    }

    try {
        const data = await apiRequest('api/transactions.php', {
            method: 'DELETE',
            body: JSON.stringify({ id: id })
        });

        if (data.success) {
            showNotification('تم حذف المعاملة بنجاح', 'success');
            if (window.currentSection === 'transactions') {
                loadAllTransactions();
            }
            // Update dashboard stats if we're on overview
            updateDashboardStats();
        } else {
            showNotification(data.message || 'حدث خطأ أثناء حذف المعاملة', 'error');
        }
    } catch (error) {
        console.error('Failed to delete transaction:', error);
        showNotification('حدث خطأ أثناء حذف المعاملة', 'error');
    }
}

async function editBudget(id) {
    try {
        // Get budget data
        const data = await apiRequest(`api/budgets.php?action=get&id=${id}`);
        
        if (data.success && data.budget) {
            const budget = data.budget;
            
            // Open the add budget modal and populate with existing data
            openAddBudgetModal();
            
            // Fill the form with budget data
            const modal = document.getElementById('addBudgetModal');
            const form = modal.querySelector('#addBudgetForm');
            
            // Add hidden ID field if it doesn't exist
            let idField = form.querySelector('input[name="budget_id"]');
            if (!idField) {
                idField = document.createElement('input');
                idField.type = 'hidden';
                idField.name = 'budget_id';
                form.appendChild(idField);
            }
            idField.value = budget.id;
            
            // Populate form fields
            form.querySelector('select[name="category_id"]').value = budget.category_id;
            form.querySelector('input[name="amount"]').value = budget.amount;
            form.querySelector('select[name="period"]').value = budget.period;
            form.querySelector('input[name="start_date"]').value = budget.start_date;
            form.querySelector('input[name="end_date"]').value = budget.end_date;
            
            // Change modal title
            modal.querySelector('h3').textContent = 'تعديل الميزانية';
            
            showNotification('تم تحميل بيانات الميزانية للتعديل', 'info');
        } else {
            showNotification('فشل في تحميل بيانات الميزانية', 'error');
        }
    } catch (error) {
        console.error('Failed to load budget for editing:', error);
        showNotification('خطأ في تحميل بيانات الميزانية', 'error');
    }
}

async function deleteBudget(id) {
    const confirmed = await showConfirmation(
        'حذف الميزانية',
        'هل أنت متأكد من حذف هذه الميزانية؟ لا يمكن التراجع عن هذا الإجراء.',
        'حذف',
        'إلغاء'
    );
    
    if (!confirmed) {
        return;
    }

    try {
        const response = await fetch('api/budgets.php', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id: id })
        });

        const data = await response.json();

        if (data.success) {
            showNotification('تم حذف الميزانية بنجاح', 'success');
            if (window.currentSection === 'budgets') {
                loadBudgets();
            }
            updateDashboardStats();
        } else {
            showNotification(data.message || 'حدث خطأ أثناء حذف الميزانية', 'error');
        }
    } catch (error) {
        console.error('Failed to delete budget:', error);
        showNotification('خطأ في حذف الميزانية', 'error');
    }
}

async function editUser(id) {
    // Implementation for editing user
    showNotification('ميزة التعديل قيد التطوير', 'info');
}

async function deleteUser(id) {
    const confirmed = await showConfirmation(
        'حذف المستخدم',
        'هل أنت متأكد من حذف هذا المستخدم؟ سيتم حذف جميع بياناته.',
        'حذف',
        'إلغاء'
    );
    
    if (!confirmed) {
        return;
    }

    try {
        const data = await apiRequest('api/users.php', {
            method: 'DELETE',
            body: JSON.stringify({ id: id })
        });

        if (data.success) {
            showNotification(data.message, 'success');
            if (window.currentSection === 'users') {
                loadUsers();
            }
        } else {
            showNotification(data.message || 'حدث خطأ أثناء حذف المستخدم', 'error');
        }
    } catch (error) {
        console.error('Failed to delete user:', error);
    }
}

// Income CRUD functions
async function editIncome(id) {
    try {
        // First, get the income data
        const response = await apiRequest(`api/income.php?action=get&id=${id}`);
        
        if (response.success && response.income) {
            const income = response.income;
            
            // Create edit modal if it doesn't exist
            let modal = document.getElementById('editIncomeModal');
            if (!modal) {
                modal = createEditIncomeModal();
                document.body.appendChild(modal);
            }
            
            // Populate the form with existing data
            const form = modal.querySelector('#editIncomeForm');
            form.querySelector('input[name="id"]').value = income.id;
            form.querySelector('input[name="description"]').value = income.description;
            form.querySelector('input[name="amount"]').value = income.amount;
            form.querySelector('select[name="category_id"]').value = income.category_id;
            form.querySelector('input[name="merchant"]').value = income.merchant || '';
            form.querySelector('input[name="transaction_date"]').value = income.transaction_date;
            form.querySelector('select[name="status"]').value = income.status;
            form.querySelector('textarea[name="notes"]').value = income.notes || '';
            
            // Load categories and show modal
            await loadCategoriesForEditIncome();
            form.querySelector('select[name="category_id"]').value = income.category_id;
            
            modal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        } else {
            showNotification('لم يتم العثور على سجل الدخل', 'error');
        }
    } catch (error) {
        console.error('Failed to load income for editing:', error);
        showNotification('حدث خطأ أثناء تحميل بيانات الدخل', 'error');
    }
}

async function deleteIncome(id) {
    const confirmed = await showConfirmation(
        'حذف الدخل',
        'هل أنت متأكد من حذف هذا الدخل؟ لا يمكن التراجع عن هذا الإجراء.',
        'حذف',
        'إلغاء'
    );
    
    if (!confirmed) {
        return;
    }

    try {
        const data = await apiRequest('api/income.php', {
            method: 'DELETE',
            body: JSON.stringify({ id: id })
        });

        if (data.success) {
            showNotification('تم حذف الدخل بنجاح', 'success');
            if (window.currentSection === 'income') {
                loadAllIncome();
            }
            updateDashboardStats();
        } else {
            showNotification(data.message || 'حدث خطأ أثناء حذف الدخل', 'error');
        }
    } catch (error) {
        console.error('Failed to delete income:', error);
    }
}

// Global currency settings
window.currencySettings = {
    symbol: 'LE',
    position: 'before',
    decimal_places: 2
};

// Format currency function
function formatCurrency(amount) {
    const formatted = parseFloat(amount).toFixed(currencySettings.decimal_places);
    if (currencySettings.position === 'before') {
        return `${currencySettings.symbol} ${formatted}`;
    } else {
        return `${formatted} ${currencySettings.symbol}`;
    }
}

// Reports Functions
async function loadFinancialSummary() {
    try {
        const period = document.getElementById('reportPeriod')?.value || 'month';
        const data = await apiRequest(`api/reports.php?action=financial_summary&period=${period}`);
        
        if (data.success) {
            const summary = data.data.summary;
            const currency = data.data.currency;
            
            // Update global currency settings
            window.currencySettings = currency;
            
            const summaryContainer = document.getElementById('financial-summary');
            
            summaryContainer.innerHTML = `
                <!-- Total Income -->
                <div class="bg-gray-800 rounded-lg p-6 text-center">
                    <div class="text-green-400 text-2xl mb-2">
                        <i class="fas fa-arrow-up"></i>
                    </div>
                    <div class="text-2xl font-bold text-green-400">${formatCurrency(summary.total_income)}</div>
                    <div class="text-sm text-gray-400">إجمالي الدخل</div>
                    <div class="text-xs text-gray-500 mt-1">${summary.income_count} معاملة</div>
                </div>
                
                <!-- Total Expenses -->
                <div class="bg-gray-800 rounded-lg p-6 text-center">
                    <div class="text-red-400 text-2xl mb-2">
                        <i class="fas fa-arrow-down"></i>
                    </div>
                    <div class="text-2xl font-bold text-red-400">${formatCurrency(summary.total_expenses)}</div>
                    <div class="text-sm text-gray-400">إجمالي المصروفات</div>
                    <div class="text-xs text-gray-500 mt-1">${summary.expense_count} معاملة</div>
                </div>
                
                <!-- Net Income -->
                <div class="bg-gray-800 rounded-lg p-6 text-center">
                    <div class="text-${summary.net_income >= 0 ? 'cyan' : 'orange'}-400 text-2xl mb-2">
                        <i class="fas fa-${summary.net_income >= 0 ? 'plus' : 'minus'}"></i>
                    </div>
                    <div class="text-2xl font-bold text-${summary.net_income >= 0 ? 'cyan' : 'orange'}-400">${formatCurrency(summary.net_income)}</div>
                    <div class="text-sm text-gray-400">صافي الدخل</div>
                    <div class="text-xs text-gray-500 mt-1">${summary.net_income >= 0 ? 'ربح' : 'خسارة'}</div>
                </div>
                
                <!-- Savings Rate -->
                <div class="bg-gray-800 rounded-lg p-6 text-center">
                    <div class="text-purple-400 text-2xl mb-2">
                        <i class="fas fa-percentage"></i>
                    </div>
                    <div class="text-2xl font-bold text-purple-400">${summary.savings_rate.toFixed(1)}%</div>
                    <div class="text-sm text-gray-400">معدل الادخار</div>
                    <div class="text-xs text-gray-500 mt-1">${summary.savings_rate >= 20 ? 'ممتاز' : summary.savings_rate >= 10 ? 'جيد' : 'يحتاج تحسين'}</div>
                </div>
            `;
            
            // Load top categories and charts
            loadTopCategoriesReport(data.data.top_expense_categories, data.data.top_income_sources);
            
            // Load charts with delays to prevent conflicts
            setTimeout(() => loadIncomeExpenseChart(summary), 100);
            setTimeout(() => loadMonthlyComparisonChart(), 200);
            setTimeout(() => loadCategoryChart('expense'), 300);
        }
    } catch (error) {
        console.error('Failed to load financial summary:', error);
        document.getElementById('financial-summary').innerHTML = '<div class="bg-gray-800 rounded-lg p-6 text-center text-red-400">خطأ في تحميل الملخص المالي</div>';
    }
}

function loadTopCategoriesReport(expenseCategories, incomeCategories) {
    // Top Expense Categories
    const expenseContainer = document.getElementById('top-expense-categories');
    if (expenseCategories && expenseCategories.length > 0) {
        expenseContainer.innerHTML = expenseCategories.map(category => `
            <div class="flex justify-between items-center py-2 border-b border-gray-700 last:border-b-0">
                <div class="flex items-center">
                    <div class="w-3 h-3 rounded-full ml-3" style="background-color: ${category.category_color}"></div>
                    <span class="text-sm">${category.category_name}</span>
                </div>
                <div class="text-right">
                    <div class="text-sm font-bold text-red-400">${formatCurrency(category.total_amount)}</div>
                    <div class="text-xs text-gray-500">${category.transaction_count} معاملة</div>
                </div>
            </div>
        `).join('');
    } else {
        expenseContainer.innerHTML = '<div class="text-gray-400 text-center py-4">لا توجد مصروفات</div>';
    }
    
    // Top Income Sources
    const incomeContainer = document.getElementById('top-income-sources');
    if (incomeCategories && incomeCategories.length > 0) {
        incomeContainer.innerHTML = incomeCategories.map(category => `
            <div class="flex justify-between items-center py-2 border-b border-gray-700 last:border-b-0">
                <div class="flex items-center">
                    <div class="w-3 h-3 rounded-full ml-3" style="background-color: ${category.category_color}"></div>
                    <span class="text-sm">${category.category_name}</span>
                </div>
                <div class="text-right">
                    <div class="text-sm font-bold text-green-400">${formatCurrency(category.total_amount)}</div>
                    <div class="text-xs text-gray-500">${category.transaction_count} معاملة</div>
                </div>
            </div>
        `).join('');
    } else {
        incomeContainer.innerHTML = '<div class="text-gray-400 text-center py-4">لا يوجد دخل</div>';
    }
}

async function loadDetailedTransactions() {
    try {
        const type = document.getElementById('transactionType')?.value || 'all';
        const data = await apiRequest(`api/reports.php?action=detailed_transactions&type=${type}&limit=50`);
        
        if (data.success) {
            const tbody = document.getElementById('detailed-transactions');
            
            if (data.data.transactions && data.data.transactions.length > 0) {
                tbody.innerHTML = data.data.transactions.map(transaction => `
                    <tr class="border-b border-gray-700 hover:bg-gray-700">
                        <td class="py-2 text-sm">${formatDate(transaction.transaction_date)}</td>
                        <td class="py-2">
                            <span class="px-2 py-1 text-xs rounded-full ${transaction.transaction_type === 'income' ? 'bg-green-500 bg-opacity-20 text-green-300' : 'bg-red-500 bg-opacity-20 text-red-300'}">
                                ${transaction.transaction_type === 'income' ? 'دخل' : 'مصروف'}
                            </span>
                        </td>
                        <td class="py-2 text-sm">${transaction.description}</td>
                        <td class="py-2">
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs" style="background-color: ${transaction.category_color}20; color: ${transaction.category_color}">
                                ${transaction.category_name}
                            </span>
                        </td>
                        <td class="py-2 text-sm font-medium ${transaction.transaction_type === 'income' ? 'text-green-400' : 'text-red-400'}">
                            ${transaction.transaction_type === 'income' ? '+' : '-'}${formatCurrency(transaction.amount)}
                        </td>
                    </tr>
                `).join('');
            } else {
                tbody.innerHTML = '<tr><td colspan="5" class="py-8 text-center text-gray-400">لا توجد معاملات</td></tr>';
            }
        }
    } catch (error) {
        console.error('Failed to load detailed transactions:', error);
        document.getElementById('detailed-transactions').innerHTML = '<tr><td colspan="5" class="py-8 text-center text-red-400">خطأ في تحميل المعاملات</td></tr>';
    }
}

function refreshReports() {
    showNotification('جاري تحديث التقارير...', 'info');
    
    // Destroy existing charts before refreshing
    destroyAllCharts();
    
    // Reload data and charts
    loadFinancialSummary();
    loadDetailedTransactions();
}

// Chart.js functions
window.monthlyComparisonChart = null;
window.categoryChart = null;
window.isLoadingCharts = false;

async function loadIncomeExpenseChart(summary = null) {
    if (window.isLoadingCharts) {
        console.log('Charts are already loading, skipping...');
        return;
    }
    
    // If no summary provided, fetch it
    if (!summary) {
        try {
            const data = await apiRequest('api/reports.php?action=financial_summary&period=month');
            if (data.success) {
                summary = data.data.summary;
            } else {
                console.log('Failed to load financial summary for chart');
                return;
            }
        } catch (error) {
            console.error('Error loading financial summary for chart:', error);
            return;
        }
    }
    
    const ctx = document.getElementById('incomeExpenseChart');
    if (!ctx) {
        console.log('Canvas not found: incomeExpenseChart');
        return;
    }
    
    console.log('Loading income expense chart with data:', summary);
    window.isLoadingCharts = true;
    
    // Destroy existing chart completely
    if (window.incomeExpenseChart) {
        console.log('Destroying existing incomeExpenseChart');
        window.incomeExpenseChart.destroy();
        window.incomeExpenseChart = null;
    }
    
    // Clear any existing Chart.js instance on this canvas
    const existingChart = Chart.getChart(ctx);
    if (existingChart) {
        console.log('Destroying existing Chart.js instance on canvas');
        existingChart.destroy();
    }
    
    // Only create chart if we have data
    if (summary.total_income > 0 || summary.total_expenses > 0) {
        console.log('Creating new chart with income:', summary.total_income, 'expenses:', summary.total_expenses);
        
        // Set canvas size explicitly
        ctx.style.width = '100%';
        ctx.style.height = '100%';
        ctx.width = ctx.offsetWidth;
        ctx.height = ctx.offsetHeight;
        
        window.incomeExpenseChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['الدخل', 'المصروفات'],
                datasets: [{
                    data: [parseFloat(summary.total_income), parseFloat(summary.total_expenses)],
                    backgroundColor: ['#10b981', '#ef4444'],
                    borderColor: ['#059669', '#dc2626'],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: false,
                maintainAspectRatio: false,
                animation: false,
                interaction: {
                    intersect: false
                },
                layout: {
                    padding: 10
                },
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: '#9ca3af',
                            font: {
                                family: 'Cairo',
                                size: 12
                            },
                            padding: 15
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.label + ': ' + formatCurrency(context.raw);
                            }
                        }
                    }
                }
            }
        });
        
        console.log('Chart created successfully');
    } else {
        console.log('No data to display in chart');
    }
    
    // Reset loading flag
    setTimeout(() => {
        window.isLoadingCharts = false;
    }, 100);
}

async function loadMonthlyComparisonChart() {
    try {
        const data = await apiRequest('api/reports.php?action=monthly_comparison&months=6');
        
        if (data.success) {
            const ctx = document.getElementById('monthlyComparisonChart');
            if (!ctx) return;
            
            // Destroy existing chart completely
            if (window.monthlyComparisonChart) {
                window.monthlyComparisonChart.destroy();
                window.monthlyComparisonChart = null;
            }
            
            // Clear any existing Chart.js instance on this canvas
            const existingChart = Chart.getChart(ctx);
            if (existingChart) {
                existingChart.destroy();
            }
            
            const monthlyData = data.data;
            const labels = monthlyData.map(item => item.month_name);
            const incomeData = monthlyData.map(item => item.income);
            const expenseData = monthlyData.map(item => item.expenses);
            
            window.monthlyComparisonChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'الدخل',
                        data: incomeData,
                        backgroundColor: '#10b981',
                        borderColor: '#059669',
                        borderWidth: 1
                    }, {
                        label: 'المصروفات',
                        data: expenseData,
                        backgroundColor: '#ef4444',
                        borderColor: '#dc2626',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            labels: {
                                color: '#9ca3af',
                                font: {
                                    family: 'Cairo'
                                }
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': ' + formatCurrency(context.raw);
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                color: '#9ca3af',
                                callback: function(value) {
                                    return formatCurrency(value);
                                }
                            },
                            grid: {
                                color: '#374151'
                            }
                        },
                        x: {
                            ticks: {
                                color: '#9ca3af'
                            },
                            grid: {
                                color: '#374151'
                            }
                        }
                    }
                }
            });
        }
    } catch (error) {
        console.error('Failed to load monthly comparison chart:', error);
    }
}

async function loadCategoryChart(type) {
    try {
        const period = document.getElementById('reportPeriod')?.value || 'month';
        const data = await apiRequest(`api/reports.php?action=category_breakdown&type=${type}&period=${period}`);
        
        if (data.success) {
            const ctx = document.getElementById('categoryChart');
            if (!ctx) return;
            
            // Destroy existing chart completely
            if (window.categoryChart) {
                window.categoryChart.destroy();
                window.categoryChart = null;
            }
            
            // Clear any existing Chart.js instance on this canvas
            const existingChart = Chart.getChart(ctx);
            if (existingChart) {
                existingChart.destroy();
            }
            
            // Update button states
            document.querySelectorAll('.category-chart-btn').forEach(btn => {
                btn.classList.remove('active', 'bg-red-500', 'bg-green-500');
                btn.classList.add('bg-gray-600');
            });
            
            const activeBtn = document.querySelector(`[data-type="${type}"]`);
            if (activeBtn) {
                activeBtn.classList.remove('bg-gray-600');
                activeBtn.classList.add('active', type === 'expense' ? 'bg-red-500' : 'bg-green-500');
            }
            
            const categories = data.data.categories;
            const labels = categories.map(cat => cat.category_name_ar);
            const amounts = categories.map(cat => cat.total_amount);
            const colors = categories.map(cat => cat.category_color);
            
            window.categoryChart = new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: labels,
                    datasets: [{
                        data: amounts,
                        backgroundColor: colors,
                        borderColor: colors.map(color => color + '80'),
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                color: '#9ca3af',
                                font: {
                                    family: 'Cairo'
                                }
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const percentage = ((context.raw / data.data.total_amount) * 100).toFixed(1);
                                    return context.label + ': ' + formatCurrency(context.raw) + ' (' + percentage + '%)';
                                }
                            }
                        }
                    }
                }
            });
        }
    } catch (error) {
        console.error('Failed to load category chart:', error);
    }
}

// Additional utility functions
function getRoleClass(role) {
    switch(role) {
        case 'super_admin': return 'bg-purple-500 bg-opacity-20 text-purple-300';
        case 'admin': return 'bg-blue-500 bg-opacity-20 text-blue-300';
        case 'user': return 'bg-green-500 bg-opacity-20 text-green-300';
        default: return 'bg-gray-500 bg-opacity-20 text-gray-300';
    }
}

function getRoleText(role) {
    switch(role) {
        case 'super_admin': return 'مدير عام';
        case 'admin': return 'مدير';
        case 'user': return 'مستخدم';
        default: return 'غير محدد';
    }
}

// Auto-refresh dashboard data every 5 minutes
setInterval(() => {
    if (window.currentSection === 'overview') {
        updateDashboardStats();
        loadRecentTransactions();
    }
}, 300000); // 5 minutes

// Initialize date inputs with current month for budget modal
function initializeBudgetDates() {
    const startDate = document.querySelector('#addBudgetModal input[name="start_date"]');
    const endDate = document.querySelector('#addBudgetModal input[name="end_date"]');
    
    if (startDate && endDate) {
        const now = new Date();
        const firstDay = new Date(now.getFullYear(), now.getMonth(), 1);
        const lastDay = new Date(now.getFullYear(), now.getMonth() + 1, 0);
        
        startDate.value = firstDay.toISOString().split('T')[0];
        endDate.value = lastDay.toISOString().split('T')[0];
    }
}

// Update budget dates when period changes
document.addEventListener('change', function(e) {
    if (e.target.name === 'period' && e.target.closest('#addBudgetModal')) {
        const period = e.target.value;
        const startDate = document.querySelector('#addBudgetModal input[name="start_date"]');
        const endDate = document.querySelector('#addBudgetModal input[name="end_date"]');
        
        const now = new Date();
        
        if (period === 'monthly') {
            const firstDay = new Date(now.getFullYear(), now.getMonth(), 1);
            const lastDay = new Date(now.getFullYear(), now.getMonth() + 1, 0);
            startDate.value = firstDay.toISOString().split('T')[0];
            endDate.value = lastDay.toISOString().split('T')[0];
        } else if (period === 'yearly') {
            const firstDay = new Date(now.getFullYear(), 0, 1);
            const lastDay = new Date(now.getFullYear(), 11, 31);
            startDate.value = firstDay.toISOString().split('T')[0];
            endDate.value = lastDay.toISOString().split('T')[0];
        }
    }
});
