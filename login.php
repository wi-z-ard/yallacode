<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/auth.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: index.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'login') {
            $email = sanitizeInput($_POST['email']);
            $password = $_POST['password'];
            
            if (empty($email) || empty($password)) {
                $error = 'يرجى ملء جميع الحقول';
            } elseif (!validateEmail($email)) {
                $error = 'البريد الإلكتروني غير صحيح';
            } else {
                if (login($email, $password)) {
                    header('Location: index.php');
                    exit();
                } else {
                    $error = 'البريد الإلكتروني أو كلمة المرور غير صحيحة';
                }
            }
        } elseif ($_POST['action'] === 'register') {
            $name = sanitizeInput($_POST['name']);
            $email = sanitizeInput($_POST['email']);
            $password = $_POST['password'];
            $confirmPassword = $_POST['confirm_password'];
            
            if (empty($name) || empty($email) || empty($password) || empty($confirmPassword)) {
                $error = 'يرجى ملء جميع الحقول';
            } elseif (!validateEmail($email)) {
                $error = 'البريد الإلكتروني غير صحيح';
            } elseif (strlen($password) < 6) {
                $error = 'كلمة المرور يجب أن تكون 6 أحرف على الأقل';
            } elseif ($password !== $confirmPassword) {
                $error = 'كلمة المرور وتأكيد كلمة المرور غير متطابقتان';
            } else {
                $result = register($name, $email, $password);
                if ($result['success']) {
                    $success = $result['message'];
                } else {
                    $error = $result['message'];
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول - إدارة المصروفات</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Cairo', sans-serif; }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full mx-4">
        <!-- Logo and Title -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-cyan-500 rounded-full mb-4">
                <i class="fas fa-chart-line text-white text-2xl"></i>
            </div>
            <h1 class="text-3xl font-bold text-white mb-2">إدارة المصروفات</h1>
            <p class="text-gray-400">نظام شامل لإدارة المصروفات والميزانيات</p>
        </div>

        <!-- Login/Register Form Container -->
        <div class="bg-gray-800 rounded-lg shadow-xl p-8">
            <!-- Tab Navigation -->
            <div class="flex mb-6 bg-gray-700 rounded-lg p-1">
                <button onclick="showLogin()" id="loginTab" class="flex-1 py-2 px-4 text-center rounded-md transition-colors duration-200 bg-cyan-500 text-white">
                    تسجيل الدخول
                </button>
                <button onclick="showRegister()" id="registerTab" class="flex-1 py-2 px-4 text-center rounded-md transition-colors duration-200 text-gray-300 hover:text-white">
                    إنشاء حساب
                </button>
            </div>

            <!-- Error/Success Messages -->
            <?php if ($error): ?>
                <div class="bg-red-500 bg-opacity-20 border border-red-500 text-red-300 px-4 py-3 rounded-lg mb-4">
                    <i class="fas fa-exclamation-circle ml-2"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="bg-green-500 bg-opacity-20 border border-green-500 text-green-300 px-4 py-3 rounded-lg mb-4">
                    <i class="fas fa-check-circle ml-2"></i>
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <!-- Login Form -->
            <form id="loginForm" method="POST" class="space-y-6">
                <input type="hidden" name="action" value="login">
                
                <div>
                    <label for="login_email" class="block text-sm font-medium text-gray-300 mb-2">
                        البريد الإلكتروني
                    </label>
                    <div class="relative">
                        <input type="email" id="login_email" name="email" required
                               class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 text-white pr-12"
                               placeholder="أدخل بريدك الإلكتروني">
                        <i class="fas fa-envelope absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    </div>
                </div>

                <div>
                    <label for="login_password" class="block text-sm font-medium text-gray-300 mb-2">
                        كلمة المرور
                    </label>
                    <div class="relative">
                        <input type="password" id="login_password" name="password" required
                               class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 text-white pr-12"
                               placeholder="أدخل كلمة المرور">
                        <i class="fas fa-lock absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <label class="flex items-center">
                        <input type="checkbox" class="rounded border-gray-600 text-cyan-500 focus:ring-cyan-500 focus:ring-offset-gray-800">
                        <span class="mr-2 text-sm text-gray-300">تذكرني</span>
                    </label>
                    <a href="#" class="text-sm text-cyan-400 hover:text-cyan-300">نسيت كلمة المرور؟</a>
                </div>

                <button type="submit" class="w-full bg-cyan-500 hover:bg-cyan-600 text-white font-medium py-3 px-4 rounded-lg transition-colors duration-200">
                    <i class="fas fa-sign-in-alt ml-2"></i>
                    تسجيل الدخول
                </button>
            </form>

            <!-- Register Form -->
            <form id="registerForm" method="POST" class="space-y-6 hidden">
                <input type="hidden" name="action" value="register">
                
                <div>
                    <label for="register_name" class="block text-sm font-medium text-gray-300 mb-2">
                        الاسم الكامل
                    </label>
                    <div class="relative">
                        <input type="text" id="register_name" name="name" required
                               class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 text-white pr-12"
                               placeholder="أدخل اسمك الكامل">
                        <i class="fas fa-user absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    </div>
                </div>

                <div>
                    <label for="register_email" class="block text-sm font-medium text-gray-300 mb-2">
                        البريد الإلكتروني
                    </label>
                    <div class="relative">
                        <input type="email" id="register_email" name="email" required
                               class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 text-white pr-12"
                               placeholder="أدخل بريدك الإلكتروني">
                        <i class="fas fa-envelope absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    </div>
                </div>

                <div>
                    <label for="register_password" class="block text-sm font-medium text-gray-300 mb-2">
                        كلمة المرور
                    </label>
                    <div class="relative">
                        <input type="password" id="register_password" name="password" required minlength="6"
                               class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 text-white pr-12"
                               placeholder="أدخل كلمة المرور (6 أحرف على الأقل)">
                        <i class="fas fa-lock absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    </div>
                </div>

                <div>
                    <label for="confirm_password" class="block text-sm font-medium text-gray-300 mb-2">
                        تأكيد كلمة المرور
                    </label>
                    <div class="relative">
                        <input type="password" id="confirm_password" name="confirm_password" required
                               class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 text-white pr-12"
                               placeholder="أعد إدخال كلمة المرور">
                        <i class="fas fa-lock absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    </div>
                </div>

                <div class="flex items-center">
                    <input type="checkbox" id="terms" required class="rounded border-gray-600 text-cyan-500 focus:ring-cyan-500 focus:ring-offset-gray-800">
                    <label for="terms" class="mr-2 text-sm text-gray-300">
                        أوافق على <a href="#" class="text-cyan-400 hover:text-cyan-300">الشروط والأحكام</a>
                    </label>
                </div>

                <button type="submit" class="w-full bg-cyan-500 hover:bg-cyan-600 text-white font-medium py-3 px-4 rounded-lg transition-colors duration-200">
                    <i class="fas fa-user-plus ml-2"></i>
                    إنشاء حساب
                </button>
            </form>
        </div>

        <!-- Demo Credentials -->
        <div class="mt-6 bg-gray-800 bg-opacity-50 rounded-lg p-4">
            <h3 class="text-sm font-medium text-gray-300 mb-2">بيانات تجريبية:</h3>
            <div class="text-xs text-gray-400 space-y-1">
                <div>المدير العام: admin@expenses.com / admin123</div>
            </div>
        </div>
    </div>

    <script>
        function showLogin() {
            document.getElementById('loginForm').classList.remove('hidden');
            document.getElementById('registerForm').classList.add('hidden');
            document.getElementById('loginTab').classList.add('bg-cyan-500', 'text-white');
            document.getElementById('loginTab').classList.remove('text-gray-300');
            document.getElementById('registerTab').classList.remove('bg-cyan-500', 'text-white');
            document.getElementById('registerTab').classList.add('text-gray-300');
        }

        function showRegister() {
            document.getElementById('registerForm').classList.remove('hidden');
            document.getElementById('loginForm').classList.add('hidden');
            document.getElementById('registerTab').classList.add('bg-cyan-500', 'text-white');
            document.getElementById('registerTab').classList.remove('text-gray-300');
            document.getElementById('loginTab').classList.remove('bg-cyan-500', 'text-white');
            document.getElementById('loginTab').classList.add('text-gray-300');
        }

        // Password confirmation validation
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('register_password').value;
            const confirmPassword = this.value;
            
            if (password !== confirmPassword) {
                this.setCustomValidity('كلمة المرور غير متطابقة');
            } else {
                this.setCustomValidity('');
            }
        });
    </script>

    <!-- Footer -->
    <footer class="fixed bottom-0 left-0 right-0 bg-gray-900 border-t border-gray-700">
        <div class="max-w-4xl mx-auto px-4 py-3">
            <div class="flex flex-col sm:flex-row justify-between items-center text-center">
                <p class="text-gray-400 text-xs mb-2 sm:mb-0">
                    © 2025 نظام إدارة المصروفات - لأغراض تعليمية فقط
                </p>
                <div class="flex items-center text-xs">
                    <span class="text-gray-500 ml-2">تطوير:</span>
                    <a href="https://facebook.com/mohammed.3awad" 
                       target="_blank" 
                       class="text-cyan-400 hover:text-cyan-300 transition-colors">
                        محمد عوض
                    </a>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>
