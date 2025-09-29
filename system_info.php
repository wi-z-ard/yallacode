<?php
/**
 * System Information Page
 * Shows current configuration and system status
 */

require_once 'config/config.php';
require_once 'config/database.php';

// Security check - only allow in development mode
if (!DEBUG_MODE) {
    http_response_code(404);
    die('Page not found');
}

$db_info = db_info();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>معلومات النظام - <?php echo SITE_NAME; ?></title>
    <script src="<?php echo TAILWIND_CDN; ?>"></script>
    <link href="<?php echo FONTAWESOME_CDN; ?>" rel="stylesheet">
    <style>
        @import url('<?php echo GOOGLE_FONTS_CDN; ?>');
        body { font-family: 'Cairo', sans-serif; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-6xl mx-auto">
            <!-- Header -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-800">معلومات النظام</h1>
                        <p class="text-gray-600 mt-2"><?php echo SITE_NAME; ?> - الإصدار <?php echo SITE_VERSION; ?></p>
                    </div>
                    <div class="text-left">
                        <div class="text-sm text-gray-500">البيئة: <?php echo ENVIRONMENT; ?></div>
                        <div class="text-sm text-gray-500">التوقيت: <?php echo date('Y-m-d H:i:s'); ?></div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Site Configuration -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-cog text-blue-500 ml-2"></i>
                        إعدادات الموقع
                    </h2>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">رابط الموقع:</span>
                            <span class="font-mono text-sm"><?php echo SITE_URL; ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">اسم الموقع:</span>
                            <span><?php echo SITE_NAME; ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">الإصدار:</span>
                            <span><?php echo SITE_VERSION; ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">البيئة:</span>
                            <span class="px-2 py-1 rounded text-xs <?php echo ENVIRONMENT === 'development' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800'; ?>">
                                <?php echo ENVIRONMENT; ?>
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">وضع التطوير:</span>
                            <span class="px-2 py-1 rounded text-xs <?php echo DEBUG_MODE ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'; ?>">
                                <?php echo DEBUG_MODE ? 'مفعل' : 'معطل'; ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Database Configuration -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-database text-green-500 ml-2"></i>
                        قاعدة البيانات
                    </h2>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">الخادم:</span>
                            <span class="font-mono text-sm"><?php echo $db_info['host']; ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">قاعدة البيانات:</span>
                            <span class="font-mono text-sm"><?php echo $db_info['database']; ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">الإصدار:</span>
                            <span class="font-mono text-sm"><?php echo $db_info['version']; ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">الترميز:</span>
                            <span class="font-mono text-sm"><?php echo $db_info['charset']; ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">الحالة:</span>
                            <span class="px-2 py-1 rounded text-xs <?php echo strpos($db_info['status'], 'Connected') !== false ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                <?php echo $db_info['status']; ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Security Configuration -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-shield-alt text-red-500 ml-2"></i>
                        إعدادات الأمان
                    </h2>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">اسم الجلسة:</span>
                            <span class="font-mono text-sm"><?php echo SESSION_NAME; ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">مدة الجلسة:</span>
                            <span><?php echo SESSION_LIFETIME / 3600; ?> ساعة</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">الحد الأدنى لكلمة المرور:</span>
                            <span><?php echo PASSWORD_MIN_LENGTH; ?> أحرف</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">تكلفة التشفير:</span>
                            <span><?php echo PASSWORD_HASH_COST; ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">مدة رمز CSRF:</span>
                            <span><?php echo CSRF_TOKEN_LIFETIME / 60; ?> دقيقة</span>
                        </div>
                    </div>
                </div>

                <!-- Application Configuration -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-mobile-alt text-purple-500 ml-2"></i>
                        إعدادات التطبيق
                    </h2>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">اللغة الافتراضية:</span>
                            <span><?php echo DEFAULT_LANGUAGE; ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">المنطقة الزمنية:</span>
                            <span class="font-mono text-sm"><?php echo DEFAULT_TIMEZONE; ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">العملة الافتراضية:</span>
                            <span><?php echo DEFAULT_CURRENCY; ?> (<?php echo CURRENCY_SYMBOL; ?>)</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">حجم الصفحة:</span>
                            <span><?php echo DEFAULT_PAGE_SIZE; ?> عنصر</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">تحديث لوحة التحكم:</span>
                            <span><?php echo DASHBOARD_REFRESH_INTERVAL / 60000; ?> دقيقة</span>
                        </div>
                    </div>
                </div>

                <!-- Features Status -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-toggle-on text-indigo-500 ml-2"></i>
                        حالة الميزات
                    </h2>
                    <div class="space-y-3">
                        <?php
                        $features = [
                            'FEATURE_MULTI_CURRENCY' => 'العملات المتعددة',
                            'FEATURE_NOTIFICATIONS' => 'الإشعارات',
                            'FEATURE_EXPORT_PDF' => 'تصدير PDF',
                            'FEATURE_EXPORT_EXCEL' => 'تصدير Excel',
                            'FEATURE_BACKUP' => 'النسخ الاحتياطي',
                            'FEATURE_TWO_FACTOR_AUTH' => 'المصادقة الثنائية'
                        ];
                        
                        foreach ($features as $feature => $name) {
                            $enabled = defined($feature) && constant($feature);
                            echo '<div class="flex justify-between">';
                            echo '<span class="text-gray-600">' . $name . ':</span>';
                            echo '<span class="px-2 py-1 rounded text-xs ' . ($enabled ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800') . '">';
                            echo $enabled ? 'مفعل' : 'معطل';
                            echo '</span>';
                            echo '</div>';
                        }
                        ?>
                    </div>
                </div>

                <!-- PHP Information -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fab fa-php text-blue-600 ml-2"></i>
                        معلومات PHP
                    </h2>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">إصدار PHP:</span>
                            <span class="font-mono text-sm"><?php echo PHP_VERSION; ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">الحد الأقصى للذاكرة:</span>
                            <span class="font-mono text-sm"><?php echo ini_get('memory_limit'); ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">الحد الأقصى لرفع الملفات:</span>
                            <span class="font-mono text-sm"><?php echo ini_get('upload_max_filesize'); ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">الحد الأقصى لوقت التنفيذ:</span>
                            <span class="font-mono text-sm"><?php echo ini_get('max_execution_time'); ?>s</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">PDO متاح:</span>
                            <span class="px-2 py-1 rounded text-xs <?php echo extension_loaded('pdo') ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                <?php echo extension_loaded('pdo') ? 'نعم' : 'لا'; ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-lg shadow-md p-6 mt-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-tools text-orange-500 ml-2"></i>
                    إجراءات سريعة
                </h2>
                <div class="flex flex-wrap gap-4">
                    <a href="install.php" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-sm">
                        <i class="fas fa-database ml-1"></i>
                        إعادة تثبيت قاعدة البيانات
                    </a>
                    <a href="reset_admin.php" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg text-sm">
                        <i class="fas fa-key ml-1"></i>
                        إعادة تعيين كلمة مرور المدير
                    </a>
                    <a href="login.php" class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded-lg text-sm">
                        <i class="fas fa-sign-in-alt ml-1"></i>
                        تسجيل الدخول
                    </a>
                    <a href="index.php" class="bg-cyan-500 hover:bg-cyan-600 text-white px-4 py-2 rounded-lg text-sm">
                        <i class="fas fa-home ml-1"></i>
                        لوحة التحكم
                    </a>
                </div>
            </div>

            <!-- Footer -->
            <div class="text-center mt-8 text-gray-500 text-sm">
                <p><?php echo SITE_NAME; ?> - تم إنشاؤه بواسطة <?php echo SITE_AUTHOR; ?></p>
                <p class="mt-1">هذه الصفحة متاحة فقط في وضع التطوير</p>
            </div>
        </div>
    </div>
</body>
</html>
