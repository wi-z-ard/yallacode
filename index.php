<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/auth.php';

// Redirect to login if not authenticated
if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

// Get user data
$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة المصروفات - لوحة التحكم</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="js/app.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Cairo', sans-serif; }
        
        /* Smooth Wave Animation Styles */
        .wave-container {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            border-radius: inherit;
        }
        
        .wave {
            position: absolute;
            top: 0;
            left: -50%;
            width: 200%;
            height: 100%;
            background: linear-gradient(45deg, 
                rgba(6, 182, 212, 0.15) 0%, 
                rgba(59, 130, 246, 0.1) 25%, 
                rgba(16, 185, 129, 0.15) 50%, 
                rgba(139, 92, 246, 0.1) 75%, 
                rgba(6, 182, 212, 0.15) 100%);
            border-radius: 50%;
            animation: smooth-wave 20s cubic-bezier(0.25, 0.46, 0.45, 0.94) infinite;
            transform-origin: center center;
        }
        
        .wave1 {
            animation-delay: 0s;
            animation-duration: 25s;
            opacity: 0.8;
            background: radial-gradient(ellipse at center, 
                rgba(6, 182, 212, 0.2) 0%, 
                rgba(59, 130, 246, 0.15) 30%, 
                rgba(16, 185, 129, 0.1) 60%, 
                transparent 100%);
        }
        
        .wave2 {
            animation-delay: -8s;
            animation-duration: 30s;
            opacity: 0.6;
            background: radial-gradient(ellipse at center, 
                rgba(16, 185, 129, 0.18) 0%, 
                rgba(6, 182, 212, 0.12) 35%, 
                rgba(139, 92, 246, 0.08) 65%, 
                transparent 100%);
            animation-direction: reverse;
        }
        
        .wave3 {
            animation-delay: -15s;
            animation-duration: 35s;
            opacity: 0.4;
            background: radial-gradient(ellipse at center, 
                rgba(139, 92, 246, 0.15) 0%, 
                rgba(16, 185, 129, 0.1) 40%, 
                rgba(6, 182, 212, 0.05) 70%, 
                transparent 100%);
        }
        
        .wave4 {
            animation-delay: -22s;
            animation-duration: 40s;
            opacity: 0.3;
            background: radial-gradient(ellipse at center, 
                rgba(59, 130, 246, 0.12) 0%, 
                rgba(139, 92, 246, 0.08) 45%, 
                rgba(16, 185, 129, 0.04) 75%, 
                transparent 100%);
            animation-direction: reverse;
        }
        
        @keyframes smooth-wave {
            0% {
                transform: translateX(0) translateY(0) rotate(0deg) scale(1) skewX(0deg);
            }
            12.5% {
                transform: translateX(5%) translateY(-2%) rotate(45deg) scale(1.05) skewX(2deg);
            }
            25% {
                transform: translateX(10%) translateY(-4%) rotate(90deg) scale(1.1) skewX(0deg);
            }
            37.5% {
                transform: translateX(15%) translateY(-2%) rotate(135deg) scale(1.05) skewX(-2deg);
            }
            50% {
                transform: translateX(20%) translateY(0) rotate(180deg) scale(1) skewX(0deg);
            }
            62.5% {
                transform: translateX(15%) translateY(2%) rotate(225deg) scale(0.95) skewX(2deg);
            }
            75% {
                transform: translateX(10%) translateY(4%) rotate(270deg) scale(0.9) skewX(0deg);
            }
            87.5% {
                transform: translateX(5%) translateY(2%) rotate(315deg) scale(0.95) skewX(-2deg);
            }
            100% {
                transform: translateX(0) translateY(0) rotate(360deg) scale(1) skewX(0deg);
            }
        }
        
        /* Enhanced hover effects */
        .lg\:col-span-2:hover .wave {
            animation-duration: 15s;
            opacity: 1.2;
        }
        
        .lg\:col-span-2:hover .wave1 {
            animation-duration: 18s;
        }
        
        .lg\:col-span-2:hover .wave2 {
            animation-duration: 22s;
        }
        
        .lg\:col-span-2:hover .wave3 {
            animation-duration: 26s;
        }
        
        .lg\:col-span-2:hover .wave4 {
            animation-duration: 30s;
        }
        
        /* Subtle pulsing effect */
        .wave-container::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 60%;
            height: 60%;
            background: radial-gradient(circle, 
                rgba(6, 182, 212, 0.05) 0%, 
                rgba(59, 130, 246, 0.03) 50%, 
                transparent 100%);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            animation: gentle-pulse 8s ease-in-out infinite;
        }
        
        @keyframes gentle-pulse {
            0%, 100% {
                transform: translate(-50%, -50%) scale(1);
                opacity: 0.3;
            }
            50% {
                transform: translate(-50%, -50%) scale(1.2);
                opacity: 0.1;
            }
        }
    </style>
</head>
<body class="bg-gray-900 text-white">
    <!-- Navigation -->
    <nav class="bg-gradient-to-r from-gray-800 to-gray-900 border-b border-gray-700 shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0 flex items-center">
                        <div class="w-10 h-10 bg-gradient-to-br from-cyan-400 to-blue-500 rounded-lg flex items-center justify-center ml-3">
                            <i class="fas fa-chart-line text-white text-lg"></i>
                        </div>
                        <div class="hidden md:block">
                            <span class="text-xl font-bold">لوحة التحكم</span>
                            <div class="text-xs text-gray-400">Dashboard</div>
                        </div>
                    </div>
                </div>
                <div class="flex items-center space-x-4 space-x-reverse">
                    <!-- Savings Goal Button -->
                    <button onclick="openSavingsGoalModal()" class="bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-green-600 hover:to-emerald-700 px-3 py-2 sm:px-4 rounded-lg text-sm font-medium transition-all duration-200 shadow-md hover:shadow-lg">
                        <i class="fas fa-piggy-bank sm:ml-2"></i>
                        <span class="hidden sm:inline">هدف الادخار</span>
                    </button>
                    
                    <div class="flex items-center space-x-3 space-x-reverse">
                        <div class="w-8 h-8 bg-gradient-to-br from-cyan-400 to-blue-500 rounded-full flex items-center justify-center">
                            <i class="fas fa-user text-white text-sm"></i>
                        </div>
                        <div class="text-right hidden sm:block">
                            <div class="text-sm font-medium">مرحباً، <?php echo htmlspecialchars($user['name']); ?></div>
                            <div class="text-xs text-gray-400"><?php echo ucfirst($user['role']); ?></div>
                        </div>
                    </div>
                    <button onclick="logout()" class="bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 px-3 py-2 sm:px-4 rounded-lg text-sm font-medium transition-all duration-200 shadow-md hover:shadow-lg">
                        <i class="fas fa-sign-out-alt sm:ml-2"></i>
                        <span class="hidden sm:inline">تسجيل الخروج</span>
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Mobile menu button -->
    <div class="lg:hidden bg-gray-800 p-4">
        <button onclick="toggleMobileMenu()" class="text-white">
            <i class="fas fa-bars text-xl"></i>
        </button>
    </div>

    <div class="flex">
        <!-- Sidebar -->
        <div id="sidebar" class="fixed lg:static inset-y-0 right-0 z-50 w-64 bg-gradient-to-b from-gray-800 to-gray-900 min-h-screen transform translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out shadow-xl">
            <!-- Close button for mobile -->
            <div class="lg:hidden p-4 text-left border-b border-gray-700">
                <button onclick="toggleMobileMenu()" class="text-white hover:text-cyan-400 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <!-- Main Title Above Sidebar -->
            <div class="p-6 border-b border-gray-700 bg-gradient-to-r from-gray-800 to-gray-700">
                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-cyan-400 to-blue-500 rounded-xl flex items-center justify-center mx-auto mb-3 shadow-lg">
                        <i class="fas fa-chart-line text-white text-2xl"></i>
                    </div>
                    <h1 class="text-xl font-bold bg-gradient-to-r from-cyan-400 to-blue-400 bg-clip-text text-transparent mb-1">
                        إدارة المصروفات
                    </h1>
                    <p class="text-sm text-gray-400 font-medium">
                        نظام إدارة مالي متكامل
                    </p>
                </div>
            </div>
            
            <!-- Sidebar Header -->
            <div class="p-4 border-b border-gray-700">
                <div class="text-center">
                    <div class="text-sm font-medium text-gray-300">القائمة الرئيسية</div>
                </div>
            </div>
            
            <nav class="mt-4 px-3 space-y-1">
                <a href="#" onclick="showSection('overview')" class="nav-item group flex items-center px-3 py-3 text-sm font-medium rounded-lg text-gray-300 hover:bg-gray-700/50 hover:text-white transition-all duration-200">
                    <div class="w-8 h-8 bg-blue-500/20 rounded-lg flex items-center justify-center ml-3 group-hover:bg-blue-500/30 transition-colors">
                        <i class="fas fa-home text-blue-400 text-sm"></i>
                    </div>
                    نظرة عامة
                </a>
                <a href="#" onclick="showSection('transactions')" class="nav-item group flex items-center px-3 py-3 text-sm font-medium rounded-lg text-gray-300 hover:bg-gray-700/50 hover:text-white transition-all duration-200">
                    <div class="w-8 h-8 bg-green-500/20 rounded-lg flex items-center justify-center ml-3 group-hover:bg-green-500/30 transition-colors">
                        <i class="fas fa-exchange-alt text-green-400 text-sm"></i>
                    </div>
                    المعاملات
                </a>
                <a href="#" onclick="showSection('income')" class="nav-item group flex items-center px-3 py-3 text-sm font-medium rounded-lg text-gray-300 hover:bg-gray-700/50 hover:text-white transition-all duration-200">
                    <div class="w-8 h-8 bg-emerald-500/20 rounded-lg flex items-center justify-center ml-3 group-hover:bg-emerald-500/30 transition-colors">
                        <i class="fas fa-money-bill-wave text-emerald-400 text-sm"></i>
                    </div>
                    الدخل
                </a>
                <a href="#" onclick="showSection('budgets')" class="nav-item group flex items-center px-3 py-3 text-sm font-medium rounded-lg text-gray-300 hover:bg-gray-700/50 hover:text-white transition-all duration-200">
                    <div class="w-8 h-8 bg-purple-500/20 rounded-lg flex items-center justify-center ml-3 group-hover:bg-purple-500/30 transition-colors">
                        <i class="fas fa-wallet text-purple-400 text-sm"></i>
                    </div>
                    الميزانيات
                </a>
                <a href="#" onclick="showSection('reports')" class="nav-item group flex items-center px-3 py-3 text-sm font-medium rounded-lg text-gray-300 hover:bg-gray-700/50 hover:text-white transition-all duration-200">
                    <div class="w-8 h-8 bg-orange-500/20 rounded-lg flex items-center justify-center ml-3 group-hover:bg-orange-500/30 transition-colors">
                        <i class="fas fa-chart-bar text-orange-400 text-sm"></i>
                    </div>
                    التقارير
                </a>
                <?php if ($user['role'] === 'admin' || $user['role'] === 'super_admin'): ?>
                <div class="pt-4 pb-2">
                    <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider px-3">إدارة النظام</div>
                </div>
                <a href="#" onclick="showSection('categories')" class="nav-item group flex items-center px-3 py-3 text-sm font-medium rounded-lg text-gray-300 hover:bg-gray-700/50 hover:text-white transition-all duration-200">
                    <div class="w-8 h-8 bg-cyan-500/20 rounded-lg flex items-center justify-center ml-3 group-hover:bg-cyan-500/30 transition-colors">
                        <i class="fas fa-tags text-cyan-400 text-sm"></i>
                    </div>
                    إدارة الفئات
                </a>
                <?php endif; ?>
                <?php if ($user['role'] === 'super_admin'): ?>
                <a href="#" onclick="showSection('users')" class="nav-item group flex items-center px-3 py-3 text-sm font-medium rounded-lg text-gray-300 hover:bg-gray-700/50 hover:text-white transition-all duration-200">
                    <div class="w-8 h-8 bg-red-500/20 rounded-lg flex items-center justify-center ml-3 group-hover:bg-red-500/30 transition-colors">
                        <i class="fas fa-users text-red-400 text-sm"></i>
                    </div>
                    إدارة المستخدمين
                </a>
                <?php endif; ?>
            </nav>
            
            <!-- Sidebar Footer -->
            <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-gray-700">
                <div class="text-center text-xs text-gray-500">
                    <div>نسخة 1.0</div>
                    <div class="mt-1">© 2024 إدارة المصروفات</div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 lg:ml-0 p-4 lg:p-6">
            <!-- Overview Section -->
            <div id="overview-section" class="section">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                    <!-- Monthly Spending Overview -->
                    <div class="lg:col-span-2 bg-gray-800 rounded-lg p-4 sm:p-6 relative overflow-hidden">
                        <!-- Animated Wave Background -->
                        <div class="absolute inset-0 opacity-10">
                            <div class="wave-container">
                                <div class="wave wave1"></div>
                                <div class="wave wave2"></div>
                                <div class="wave wave3"></div>
                                <div class="wave wave4"></div>
                            </div>
                        </div>
                        
                        <!-- Content -->
                        <div class="relative z-10">
                            <h3 class="text-lg font-semibold mb-4">نظرة عامة على الإنفاق الشهري</h3>
                        
                        <!-- Mobile: Stack vertically, Desktop: Side by side -->
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-4">
                            <!-- Main spending amount -->
                            <div class="text-center sm:text-right">
                                <div id="monthly-spending" class="text-2xl sm:text-3xl font-bold text-cyan-400 mb-1">جاري التحميل...</div>
                                <div class="text-sm text-gray-400">إجمالي الإنفاق هذا الشهر</div>
                            </div>
                            
                            <!-- Budget and savings indicators -->
                            <div class="flex justify-center sm:justify-end items-center space-x-6 space-x-reverse">
                                <!-- Budget indicator -->
                                <div class="text-center">
                                    <div class="relative w-16 h-16 sm:w-20 sm:h-20 mx-auto">
                                        <canvas id="budgetChart" width="80" height="80"></canvas>
                                        <div class="absolute inset-0 flex items-center justify-center">
                                            <span id="budget-percentage" class="text-xs sm:text-sm font-semibold">--%</span>
                                        </div>
                                    </div>
                                    <div class="text-xs text-gray-400 mt-1">الميزانية</div>
                                </div>
                                
                                <!-- Savings indicator -->
                                <div class="text-center">
                                    <div id="savings-percentage" class="text-base sm:text-lg font-semibold text-green-400 mb-1">--%</div>
                                    <div class="text-xs text-gray-400">هدف الادخار</div>
                                    <div id="savings-status" class="text-xs text-green-400 mt-1">جاري الحساب</div>
                                </div>
                            </div>
                        </div>
                        </div>
                    </div>

                    <!-- Quick Add Transaction -->
                    <div class="bg-gray-800 rounded-lg p-6">
                        <h3 class="text-lg font-semibold mb-4">إضافة معاملة سريعة</h3>
                        <button onclick="openAddTransactionModal()" class="w-full bg-cyan-500 hover:bg-cyan-600 text-white font-medium py-2 px-4 rounded-lg">
                            إضافة مصروف
                        </button>
                    </div>
                </div>

                <!-- Recent Transactions and Spending by Category -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    <!-- Recent Transactions -->
                    <div class="bg-gray-800 rounded-lg p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold">المعاملات الأخيرة</h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="text-gray-400 border-b border-gray-700">
                                        <th class="text-right py-2">التاريخ</th>
                                        <th class="text-right py-2">الوصف</th>
                                        <th class="text-right py-2">التاجر</th>
                                        <th class="text-right py-2">المبلغ</th>
                                        <th class="text-right py-2">الحالة</th>
                                    </tr>
                                </thead>
                                <tbody id="recent-transactions">
                                    <!-- Transactions will be loaded here -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Spending by Category -->
                    <div class="bg-gray-800 rounded-lg p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold">الإنفاق حسب الفئة</h3>
                            <a href="#" class="text-cyan-400 text-sm">عرض الكل</a>
                        </div>
                        <div class="flex items-center justify-center mb-4">
                            <canvas id="categoryChart" width="200" height="200"></canvas>
                        </div>
                        <div id="category-spending-list" class="space-y-2">
                            <div class="text-center text-gray-400 py-4">جاري تحميل البيانات...</div>
                        </div>
                    </div>
                </div>

                <!-- Monthly Cash Flow -->
                <div class="bg-gray-800 rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-4">التدفق النقدي الشهري</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div id="budget-vs-actual" class="space-y-3">
                            <div class="text-center text-gray-400 py-4">جاري تحميل البيانات...</div>
                        </div>
                        <div>
                            <canvas id="cashFlowChart" width="400" height="200"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Other sections will be loaded dynamically -->
            <div id="transactions-section" class="section hidden">
                <h2 class="text-2xl font-bold mb-6">إدارة المعاملات</h2>
                <!-- Transactions content will be loaded here -->
            </div>

            <div id="income-section" class="section hidden">
                <h2 class="text-2xl font-bold mb-6">إدارة الدخل</h2>
                <!-- Income content will be loaded here -->
            </div>

            <div id="budgets-section" class="section hidden">
                <h2 class="text-2xl font-bold mb-6">إدارة الميزانيات</h2>
                <!-- Budgets content will be loaded here -->
            </div>

            <div id="reports-section" class="section hidden">
                <h2 class="text-2xl font-bold mb-6">التقارير والتحليلات</h2>
                <!-- Reports content will be loaded here -->
            </div>

            <?php if ($user['role'] === 'admin' || $user['role'] === 'super_admin'): ?>
            <div id="categories-section" class="section hidden">
                <h2 class="text-2xl font-bold mb-6">إدارة الفئات</h2>
                <!-- Categories management content will be loaded here -->
            </div>
            <?php endif; ?>

            <?php if ($user['role'] === 'super_admin'): ?>
            <div id="users-section" class="section hidden">
                <h2 class="text-2xl font-bold mb-6">إدارة المستخدمين</h2>
                <!-- Users management content will be loaded here -->
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Add Transaction Modal -->
    <div id="addTransactionModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-gray-800 rounded-lg p-6 w-full max-w-md">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold">إضافة معاملة جديدة</h3>
                    <button onclick="closeAddTransactionModal()" class="text-gray-400 hover:text-white">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form id="addTransactionForm">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-2">الوصف</label>
                            <input type="text" name="description" required class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:border-cyan-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">المبلغ</label>
                            <input type="number" name="amount" step="0.01" required class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:border-cyan-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">الفئة</label>
                            <select name="category_id" required class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:border-cyan-500">
                                <option value="">اختر الفئة</option>
                                <!-- Categories will be loaded dynamically -->
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">التاجر</label>
                            <input type="text" name="merchant" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:border-cyan-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">التاريخ</label>
                            <input type="date" name="transaction_date" required class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:border-cyan-500">
                        </div>
                    </div>
                    <div class="flex justify-end space-x-3 space-x-reverse mt-6">
                        <button type="button" onclick="closeAddTransactionModal()" class="px-4 py-2 text-gray-400 hover:text-white">
                            إلغاء
                        </button>
                        <button type="submit" class="px-4 py-2 bg-cyan-500 hover:bg-cyan-600 text-white rounded-lg">
                            إضافة
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-900 border-t border-gray-700 mt-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="text-center md:text-right mb-4 md:mb-0">
                    <p class="text-gray-400 text-sm">
                        © 2025 نظام إدارة المصروفات - لأغراض تعليمية فقط
                    </p>
                    <p class="text-gray-500 text-xs mt-1">
                        يُمنع بيع أو تسويق أي جزء من هذا النظام
                    </p>
                </div>
                <div class="text-center md:text-left">
                    <p class="text-gray-400 text-sm mb-2">
                        تطوير: <span class="text-cyan-400 font-semibold">محمد عوض</span>
                    </p>
                    <a href="https://facebook.com/mohammed.3awad" 
                       target="_blank" 
                       class="inline-flex items-center text-blue-400 hover:text-blue-300 text-sm transition-colors">
                        <i class="fab fa-facebook-f ml-2"></i>
                        تواصل معي على فيسبوك
                    </a>
                </div>
            </div>
            <div class="border-t border-gray-700 mt-4 pt-4 text-center">
                <p class="text-gray-500 text-xs">
                    Educational Use License - For Learning Purposes Only
                </p>
            </div>
        </div>
    </footer>

    <script src="js/app.js"></script>
</body>
</html>
