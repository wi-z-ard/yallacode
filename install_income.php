<?php
// Income feature installation script
require_once 'config/config.php';

try {
    require_once 'config/database.php';
    
    echo "<h2>تثبيت ميزة الدخل</h2>";
    
    // Read and execute income update schema
    $schema = file_get_contents('database/income_update.sql');
    
    if ($schema === false) {
        throw new Exception('فشل في قراءة ملف تحديث الدخل');
    }
    
    // Split SQL statements
    $statements = array_filter(array_map('trim', explode(';', $schema)));
    
    foreach ($statements as $statement) {
        if (!empty($statement) && !preg_match('/^\/\*.*\*\/$/s', trim($statement))) {
            try {
                $pdo->exec($statement);
                echo "<p style='color: green;'>✓ تم تنفيذ: " . substr($statement, 0, 50) . "...</p>";
            } catch (PDOException $e) {
                // Skip errors for statements that might already exist
                if (strpos($e->getMessage(), 'Duplicate column name') !== false ||
                    strpos($e->getMessage(), 'already exists') !== false ||
                    strpos($e->getMessage(), 'Table') !== false && strpos($e->getMessage(), 'already exists') !== false) {
                    echo "<p style='color: orange;'>⚠ تم تخطي (موجود بالفعل): " . substr($statement, 0, 50) . "...</p>";
                } else {
                    echo "<p style='color: red;'>❌ خطأ: " . $e->getMessage() . "</p>";
                    echo "<p style='color: gray;'>الاستعلام: " . substr($statement, 0, 100) . "...</p>";
                }
            }
        }
    }
    
    // Verify the installation
    echo "<h3>التحقق من التثبيت:</h3>";
    
    // Check if transaction_type column exists
    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM transactions LIKE 'transaction_type'");
        if ($stmt->rowCount() > 0) {
            echo "<p style='color: green;'>✓ عمود نوع المعاملة موجود</p>";
        } else {
            echo "<p style='color: red;'>❌ عمود نوع المعاملة غير موجود</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ خطأ في التحقق من عمود نوع المعاملة</p>";
    }
    
    // Check if category_type column exists
    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM categories LIKE 'category_type'");
        if ($stmt->rowCount() > 0) {
            echo "<p style='color: green;'>✓ عمود نوع الفئة موجود</p>";
        } else {
            echo "<p style='color: red;'>❌ عمود نوع الفئة غير موجود</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ خطأ في التحقق من عمود نوع الفئة</p>";
    }
    
    // Check income categories
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM categories WHERE category_type = 'income'");
        $result = $stmt->fetch();
        if ($result['count'] > 0) {
            echo "<p style='color: green;'>✓ فئات الدخل موجودة (" . $result['count'] . " فئة)</p>";
        } else {
            echo "<p style='color: red;'>❌ لا توجد فئات دخل</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ خطأ في التحقق من فئات الدخل</p>";
    }
    
    // Check income_budgets table
    try {
        $stmt = $pdo->query("SHOW TABLES LIKE 'income_budgets'");
        if ($stmt->rowCount() > 0) {
            echo "<p style='color: green;'>✓ جدول ميزانيات الدخل موجود</p>";
        } else {
            echo "<p style='color: red;'>❌ جدول ميزانيات الدخل غير موجود</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ خطأ في التحقق من جدول ميزانيات الدخل</p>";
    }
    
    echo "<h3 style='color: green;'>✅ تم تثبيت ميزة الدخل بنجاح!</h3>";
    
    echo "<h4>الميزات الجديدة:</h4>";
    echo "<ul>";
    echo "<li>✓ تتبع الدخل والمصروفات منفصلة</li>";
    echo "<li>✓ فئات دخل مخصصة (راتب، عمل حر، استثمارات، إلخ)</li>";
    echo "<li>✓ ميزانيات الدخل المستهدفة</li>";
    echo "<li>✓ تقارير الدخل مقابل المصروفات</li>";
    echo "<li>✓ حساب صافي الدخل</li>";
    echo "</ul>";
    
    echo "<h4>اختبار الميزات الجديدة:</h4>";
    echo "<p><a href='api/income.php?action=categories' target='_blank' style='background: #10B981; color: white; padding: 5px 10px; text-decoration: none; border-radius: 3px;'>اختبار فئات الدخل</a></p>";
    echo "<p><a href='test_income.php' style='background: #3B82F6; color: white; padding: 5px 10px; text-decoration: none; border-radius: 3px;'>اختبار إضافة دخل</a></p>";
    echo "<p><a href='index.php' style='background: #06B6D4; color: white; padding: 5px 10px; text-decoration: none; border-radius: 3px;'>العودة للوحة التحكم</a></p>";
    
} catch (Exception $e) {
    echo "<h3 style='color: red;'>❌ خطأ في التثبيت:</h3>";
    echo "<p style='color: red;'>" . $e->getMessage() . "</p>";
    echo "<h4>تأكد من:</h4>";
    echo "<ul>";
    echo "<li>تشغيل خادم MySQL</li>";
    echo "<li>صحة بيانات الاتصال في config/database.php</li>";
    echo "<li>وجود صلاحيات تعديل قاعدة البيانات</li>";
    echo "</ul>";
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تثبيت ميزة الدخل - <?php echo SITE_NAME; ?></title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        h2, h3, h4 {
            color: #333;
        }
        p {
            margin: 10px 0;
        }
        ul {
            margin: 10px 0;
            padding-right: 20px;
        }
    </style>
</head>
<body>
    <!-- Content is generated by PHP above -->
</body>
</html>
