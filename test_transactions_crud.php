<?php
/**
 * Test Transactions CRUD Operations
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
    <title>اختبار عمليات المعاملات - <?php echo SITE_NAME; ?></title>
    <script src="<?php echo TAILWIND_CDN; ?>"></script>
    <link href="<?php echo FONTAWESOME_CDN; ?>" rel="stylesheet">
    <style>
        @import url('<?php echo GOOGLE_FONTS_CDN; ?>');
        body { font-family: 'Cairo', sans-serif; }
    </style>
</head>
<body class="bg-gray-900 text-white min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-6xl mx-auto">
            <!-- Header -->
            <div class="bg-gray-800 rounded-lg p-6 mb-6">
                <h1 class="text-3xl font-bold text-blue-400 mb-2">اختبار عمليات المعاملات</h1>
                <p class="text-gray-300">اختبار التعديل والحذف للمعاملات</p>
                <div class="mt-4">
                    <a href="index.php" class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg text-sm">
                        <i class="fas fa-arrow-right ml-2"></i>
                        العودة للوحة التحكم
                    </a>
                </div>
            </div>

            <!-- Test Transactions -->
            <div class="bg-gray-800 rounded-lg p-6">
                <h2 class="text-xl font-semibold mb-4 text-green-400">
                    <i class="fas fa-list ml-2"></i>
                    المعاملات المتاحة للاختبار
                </h2>
                
                <div class="mb-4">
                    <button onclick="loadTestTransactions()" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                        <i class="fas fa-refresh ml-2"></i>
                        تحميل المعاملات
                    </button>
                    <button onclick="addTestTransaction()" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg">
                        <i class="fas fa-plus ml-2"></i>
                        إضافة معاملة تجريبية
                    </button>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-gray-400 border-b border-gray-700">
                                <th class="text-right py-3">المعرف</th>
                                <th class="text-right py-3">الوصف</th>
                                <th class="text-right py-3">المبلغ</th>
                                <th class="text-right py-3">الفئة</th>
                                <th class="text-right py-3">التاريخ</th>
                                <th class="text-right py-3">الحالة</th>
                                <th class="text-right py-3">الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody id="transactions-table">
                            <tr>
                                <td colspan="7" class="py-8 text-center text-gray-400">اضغط "تحميل المعاملات" لعرض البيانات</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Test Results -->
            <div class="bg-gray-800 rounded-lg p-6 mt-6">
                <h2 class="text-xl font-semibold mb-4 text-purple-400">
                    <i class="fas fa-code ml-2"></i>
                    نتائج الاختبار
                </h2>
                <div id="test-results" class="bg-gray-900 rounded-lg p-4 text-xs overflow-auto max-h-60">
                    <p class="text-gray-400">ستظهر نتائج الاختبارات هنا...</p>
                </div>
            </div>

            <!-- Messages -->
            <div id="messages" class="fixed top-4 left-4 z-50"></div>
        </div>
    </div>

    <script>
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

        // Load test transactions
        async function loadTestTransactions() {
            logTest('Loading transactions...');
            try {
                const data = await apiRequest('api/transactions.php?action=all&limit=10');
                const tbody = document.getElementById('transactions-table');
                
                if (data.success && data.transactions && data.transactions.length > 0) {
                    tbody.innerHTML = '';
                    data.transactions.forEach(transaction => {
                        const row = document.createElement('tr');
                        row.className = 'border-b border-gray-700 hover:bg-gray-700';
                        row.innerHTML = `
                            <td class="py-3 text-sm">${transaction.id}</td>
                            <td class="py-3 text-sm">${transaction.description}</td>
                            <td class="py-3 text-sm font-medium text-red-400">€${parseFloat(transaction.amount).toFixed(2)}</td>
                            <td class="py-3 text-sm">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs" style="background-color: ${transaction.category_color}20; color: ${transaction.category_color}">
                                    ${transaction.category_name}
                                </span>
                            </td>
                            <td class="py-3 text-sm">${formatDate(transaction.transaction_date)}</td>
                            <td class="py-3">
                                <span class="px-2 py-1 text-xs rounded-full ${getStatusClass(transaction.status)}">
                                    ${getStatusText(transaction.status)}
                                </span>
                            </td>
                            <td class="py-3">
                                <div class="flex space-x-2 space-x-reverse">
                                    <button onclick="testEditTransaction(${transaction.id})" class="text-blue-400 hover:text-blue-300 px-2 py-1 bg-blue-500 bg-opacity-20 rounded text-xs">
                                        <i class="fas fa-edit ml-1"></i> تعديل
                                    </button>
                                    <button onclick="testDeleteTransaction(${transaction.id})" class="text-red-400 hover:text-red-300 px-2 py-1 bg-red-500 bg-opacity-20 rounded text-xs">
                                        <i class="fas fa-trash ml-1"></i> حذف
                                    </button>
                                </div>
                            </td>
                        `;
                        tbody.appendChild(row);
                    });
                    logTest(`✅ Loaded ${data.transactions.length} transactions successfully`);
                } else {
                    tbody.innerHTML = '<tr><td colspan="7" class="py-8 text-center text-gray-400">لا توجد معاملات</td></tr>';
                    logTest('⚠️ No transactions found');
                }
            } catch (error) {
                logTest(`❌ Failed to load transactions: ${error.message}`);
                document.getElementById('transactions-table').innerHTML = '<tr><td colspan="7" class="py-8 text-center text-red-400">خطأ في تحميل البيانات</td></tr>';
            }
        }

        // Test edit transaction
        async function testEditTransaction(id) {
            logTest(`Testing edit transaction with ID: ${id}`);
            try {
                // First, get the transaction
                const response = await apiRequest(`api/transactions.php?action=get&id=${id}`);
                if (response.success && response.transaction) {
                    logTest(`✅ Successfully fetched transaction: ${response.transaction.description}`);
                    
                    // Test edit (just change description)
                    const editData = {
                        id: id,
                        description: response.transaction.description + ' (تم التعديل)',
                        amount: parseFloat(response.transaction.amount),
                        category_id: parseInt(response.transaction.category_id),
                        merchant: response.transaction.merchant,
                        transaction_date: response.transaction.transaction_date,
                        status: response.transaction.status,
                        notes: response.transaction.notes
                    };
                    
                    const editResponse = await apiRequest('api/transactions.php', {
                        method: 'PUT',
                        body: JSON.stringify(editData)
                    });
                    
                    if (editResponse.success) {
                        logTest(`✅ Successfully edited transaction ID: ${id}`);
                        showMessage('تم تعديل المعاملة بنجاح!', 'success');
                        loadTestTransactions(); // Reload to show changes
                    } else {
                        logTest(`❌ Failed to edit transaction: ${editResponse.message}`);
                    }
                } else {
                    logTest(`❌ Transaction not found: ${id}`);
                }
            } catch (error) {
                logTest(`❌ Edit test failed: ${error.message}`);
            }
        }

        // Test delete transaction
        async function testDeleteTransaction(id) {
            if (!confirm(`هل تريد حذف المعاملة رقم ${id} فعلاً؟`)) {
                return;
            }
            
            logTest(`Testing delete transaction with ID: ${id}`);
            try {
                const response = await apiRequest('api/transactions.php', {
                    method: 'DELETE',
                    body: JSON.stringify({ id: id })
                });
                
                if (response.success) {
                    logTest(`✅ Successfully deleted transaction ID: ${id}`);
                    showMessage('تم حذف المعاملة بنجاح!', 'success');
                    loadTestTransactions(); // Reload to show changes
                } else {
                    logTest(`❌ Failed to delete transaction: ${response.message}`);
                }
            } catch (error) {
                logTest(`❌ Delete test failed: ${error.message}`);
            }
        }

        // Add test transaction
        async function addTestTransaction() {
            logTest('Adding test transaction...');
            try {
                // Get categories first
                const categoriesResponse = await apiRequest('api/categories.php');
                if (!categoriesResponse.success || !categoriesResponse.categories.length) {
                    logTest('❌ No categories available');
                    return;
                }
                
                const category = categoriesResponse.categories.find(c => c.category_type === 'expense' || c.category_type === 'both');
                if (!category) {
                    logTest('❌ No expense categories available');
                    return;
                }
                
                const testData = {
                    description: 'معاملة تجريبية - ' + new Date().toLocaleTimeString('ar-SA'),
                    amount: Math.floor(Math.random() * 100) + 10,
                    category_id: category.id,
                    merchant: 'متجر تجريبي',
                    transaction_date: new Date().toISOString().split('T')[0],
                    status: 'cleared'
                };
                
                const response = await apiRequest('api/transactions.php', {
                    method: 'POST',
                    body: JSON.stringify(testData)
                });
                
                if (response.success) {
                    logTest(`✅ Successfully added test transaction`);
                    showMessage('تم إضافة المعاملة التجريبية!', 'success');
                    loadTestTransactions();
                } else {
                    logTest(`❌ Failed to add test transaction: ${response.message}`);
                }
            } catch (error) {
                logTest(`❌ Add test failed: ${error.message}`);
            }
        }

        // Utility functions
        function logTest(message) {
            const results = document.getElementById('test-results');
            const timestamp = new Date().toLocaleTimeString('ar-SA');
            results.innerHTML += `<div>[${timestamp}] ${message}</div>`;
            results.scrollTop = results.scrollHeight;
        }

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
                case 'cleared': return 'مؤكدة';
                case 'pending': return 'معلقة';
                case 'cancelled': return 'ملغية';
                default: return status;
            }
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

        // Auto-load on page load
        document.addEventListener('DOMContentLoaded', function() {
            logTest('🚀 Transaction CRUD Test Page Loaded');
            loadTestTransactions();
        });
    </script>
</body>
</html>
