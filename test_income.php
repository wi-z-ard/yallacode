<?php
/**
 * Test Income Functionality
 */

require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/auth.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>اختبار ميزة الدخل - <?php echo SITE_NAME; ?></title>
    <script src="<?php echo TAILWIND_CDN; ?>"></script>
    <link href="<?php echo FONTAWESOME_CDN; ?>" rel="stylesheet">
    <style>
        @import url('<?php echo GOOGLE_FONTS_CDN; ?>');
        body { font-family: 'Cairo', sans-serif; }
    </style>
</head>
<body class="bg-gray-900 text-white min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <!-- Header -->
            <div class="bg-gray-800 rounded-lg p-6 mb-6">
                <h1 class="text-3xl font-bold text-cyan-400 mb-2">اختبار ميزة الدخل</h1>
                <p class="text-gray-300">اختبار إضافة وإدارة الدخل في النظام</p>
                <div class="mt-4">
                    <a href="index.php" class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg text-sm">
                        <i class="fas fa-arrow-right ml-2"></i>
                        العودة للوحة التحكم
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Add Income Form -->
                <div class="bg-gray-800 rounded-lg p-6">
                    <h2 class="text-xl font-semibold mb-4 text-green-400">
                        <i class="fas fa-plus-circle ml-2"></i>
                        إضافة دخل جديد
                    </h2>
                    <form id="addIncomeForm" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-2">الوصف</label>
                            <input type="text" name="description" required 
                                   class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:border-green-500"
                                   placeholder="مثال: راتب شهر ديسمبر">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">المبلغ</label>
                            <input type="number" name="amount" step="0.01" required 
                                   class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:border-green-500"
                                   placeholder="3000.00">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">فئة الدخل</label>
                            <select name="category_id" required 
                                    class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:border-green-500">
                                <option value="">اختر فئة الدخل</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">المصدر</label>
                            <input type="text" name="merchant" 
                                   class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:border-green-500"
                                   placeholder="مثال: شركة ABC">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">التاريخ</label>
                            <input type="date" name="transaction_date" required 
                                   class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:border-green-500">
                        </div>
                        <button type="submit" class="w-full bg-green-500 hover:bg-green-600 text-white font-medium py-2 px-4 rounded-lg">
                            <i class="fas fa-plus ml-2"></i>
                            إضافة الدخل
                        </button>
                    </form>
                </div>

                <!-- Recent Income -->
                <div class="bg-gray-800 rounded-lg p-6">
                    <h2 class="text-xl font-semibold mb-4 text-blue-400">
                        <i class="fas fa-list ml-2"></i>
                        آخر الدخل المضاف
                    </h2>
                    <div id="recentIncome" class="space-y-3">
                        <p class="text-gray-400 text-center">جاري التحميل...</p>
                    </div>
                    <button onclick="loadRecentIncome()" class="mt-4 w-full bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded-lg text-sm">
                        <i class="fas fa-refresh ml-2"></i>
                        تحديث القائمة
                    </button>
                </div>

                <!-- Income Statistics -->
                <div class="bg-gray-800 rounded-lg p-6">
                    <h2 class="text-xl font-semibold mb-4 text-purple-400">
                        <i class="fas fa-chart-bar ml-2"></i>
                        إحصائيات الدخل
                    </h2>
                    <div id="incomeStats" class="space-y-3">
                        <p class="text-gray-400 text-center">جاري التحميل...</p>
                    </div>
                    <button onclick="loadIncomeStats()" class="mt-4 w-full bg-purple-500 hover:bg-purple-600 text-white py-2 px-4 rounded-lg text-sm">
                        <i class="fas fa-calculator ml-2"></i>
                        حساب الإحصائيات
                    </button>
                </div>

                <!-- API Tests -->
                <div class="bg-gray-800 rounded-lg p-6">
                    <h2 class="text-xl font-semibold mb-4 text-orange-400">
                        <i class="fas fa-code ml-2"></i>
                        اختبار API
                    </h2>
                    <div class="space-y-2">
                        <button onclick="testAPI('categories')" class="w-full bg-orange-500 hover:bg-orange-600 text-white py-2 px-4 rounded-lg text-sm">
                            اختبار فئات الدخل
                        </button>
                        <button onclick="testAPI('recent')" class="w-full bg-orange-500 hover:bg-orange-600 text-white py-2 px-4 rounded-lg text-sm">
                            اختبار آخر الدخل
                        </button>
                        <button onclick="testAPI('stats')" class="w-full bg-orange-500 hover:bg-orange-600 text-white py-2 px-4 rounded-lg text-sm">
                            اختبار إحصائيات الدخل
                        </button>
                    </div>
                    <div id="apiResults" class="mt-4 p-3 bg-gray-900 rounded-lg text-xs overflow-auto max-h-40" style="display: none;">
                        <pre id="apiOutput"></pre>
                    </div>
                </div>
            </div>

            <!-- Messages -->
            <div id="messages" class="fixed top-4 left-4 z-50"></div>
        </div>
    </div>

    <script>
        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            loadIncomeCategories();
            loadRecentIncome();
            loadIncomeStats();
            
            // Set today's date
            document.querySelector('input[name="transaction_date"]').value = new Date().toISOString().split('T')[0];
            
            // Form submission
            document.getElementById('addIncomeForm').addEventListener('submit', function(e) {
                e.preventDefault();
                submitIncome();
            });
        });

        // API Helper
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

                const data = await response.json();
                
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${data.message || 'Unknown error'}`);
                }

                return data;
            } catch (error) {
                console.error('API request failed:', error);
                showMessage('خطأ في API: ' + error.message, 'error');
                throw error;
            }
        }

        // Load income categories
        async function loadIncomeCategories() {
            try {
                const data = await apiRequest('api/income.php?action=categories');
                const select = document.querySelector('select[name="category_id"]');
                select.innerHTML = '<option value="">اختر فئة الدخل</option>';
                
                data.categories.forEach(category => {
                    const option = document.createElement('option');
                    option.value = category.id;
                    option.textContent = category.name_ar;
                    select.appendChild(option);
                });
            } catch (error) {
                console.error('Failed to load income categories:', error);
            }
        }

        // Load recent income
        async function loadRecentIncome() {
            try {
                const data = await apiRequest('api/income.php?action=recent&limit=5');
                const container = document.getElementById('recentIncome');
                
                if (data.income && data.income.length > 0) {
                    container.innerHTML = '';
                    data.income.forEach(income => {
                        const item = document.createElement('div');
                        item.className = 'bg-gray-700 p-3 rounded-lg';
                        item.innerHTML = `
                            <div class="flex justify-between items-center">
                                <div>
                                    <div class="font-medium">${income.description}</div>
                                    <div class="text-sm text-gray-400">${income.category_name}</div>
                                </div>
                                <div class="text-green-400 font-bold">€${parseFloat(income.amount).toFixed(2)}</div>
                            </div>
                            <div class="text-xs text-gray-500 mt-1">${formatDate(income.transaction_date)}</div>
                        `;
                        container.appendChild(item);
                    });
                } else {
                    container.innerHTML = '<p class="text-gray-400 text-center">لا يوجد دخل مسجل</p>';
                }
            } catch (error) {
                document.getElementById('recentIncome').innerHTML = '<p class="text-red-400 text-center">خطأ في تحميل البيانات</p>';
            }
        }

        // Load income statistics
        async function loadIncomeStats() {
            try {
                const data = await apiRequest('api/income.php?action=stats&period=month');
                const container = document.getElementById('incomeStats');
                
                container.innerHTML = `
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span>إجمالي الدخل الشهري:</span>
                            <span class="font-bold text-green-400">€${parseFloat(data.stats.total_amount || 0).toFixed(2)}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>عدد المعاملات:</span>
                            <span class="font-bold">${data.stats.transaction_count || 0}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>متوسط الدخل:</span>
                            <span class="font-bold text-blue-400">€${data.stats.transaction_count > 0 ? (data.stats.total_amount / data.stats.transaction_count).toFixed(2) : '0.00'}</span>
                        </div>
                    </div>
                `;
            } catch (error) {
                document.getElementById('incomeStats').innerHTML = '<p class="text-red-400 text-center">خطأ في تحميل الإحصائيات</p>';
            }
        }

        // Submit income
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

                showMessage('تم إضافة الدخل بنجاح!', 'success');
                form.reset();
                document.querySelector('input[name="transaction_date"]').value = new Date().toISOString().split('T')[0];
                loadRecentIncome();
                loadIncomeStats();
            } catch (error) {
                console.error('Failed to submit income:', error);
            }
        }

        // Test API endpoints
        async function testAPI(action) {
            const resultsDiv = document.getElementById('apiResults');
            const outputPre = document.getElementById('apiOutput');
            
            resultsDiv.style.display = 'block';
            outputPre.textContent = 'جاري الاختبار...';
            
            try {
                const data = await apiRequest(`api/income.php?action=${action}`);
                outputPre.textContent = JSON.stringify(data, null, 2);
                showMessage(`اختبار ${action} نجح!`, 'success');
            } catch (error) {
                outputPre.textContent = `خطأ: ${error.message}`;
                showMessage(`اختبار ${action} فشل!`, 'error');
            }
        }

        // Utility functions
        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('ar-SA');
        }

        function showMessage(message, type = 'info') {
            const messagesContainer = document.getElementById('messages');
            const messageDiv = document.createElement('div');
            
            const colors = {
                success: 'bg-green-500',
                error: 'bg-red-500',
                info: 'bg-blue-500'
            };
            
            messageDiv.className = `${colors[type]} text-white px-4 py-2 rounded-lg mb-2 shadow-lg`;
            messageDiv.textContent = message;
            
            messagesContainer.appendChild(messageDiv);
            
            setTimeout(() => {
                messageDiv.remove();
            }, 5000);
        }
    </script>
</body>
</html>
