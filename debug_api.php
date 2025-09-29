<?php
/**
 * Debug API Script
 * Test API endpoints and database connection
 */

require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/auth.php';

// Start output buffering to catch any errors
ob_start();

echo "<h2>API Debug Information</h2>";

// Test database connection
echo "<h3>1. Database Connection Test</h3>";
try {
    $db_info = db_info();
    echo "<p style='color: green;'>✅ Database connected successfully</p>";
    echo "<pre>" . print_r($db_info, true) . "</pre>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Database connection failed: " . $e->getMessage() . "</p>";
}

// Test if tables exist
echo "<h3>2. Database Tables Check</h3>";
try {
    $tables = ['users', 'categories', 'transactions', 'budgets'];
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "<p style='color: green;'>✅ Table '$table' exists</p>";
        } else {
            echo "<p style='color: red;'>❌ Table '$table' missing</p>";
        }
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error checking tables: " . $e->getMessage() . "</p>";
}

// Test categories data
echo "<h3>3. Categories Data Test</h3>";
try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM categories");
    $result = $stmt->fetch();
    echo "<p style='color: green;'>✅ Categories table has " . $result['count'] . " records</p>";
    
    if ($result['count'] > 0) {
        $stmt = $pdo->query("SELECT * FROM categories LIMIT 3");
        $categories = $stmt->fetchAll();
        echo "<pre>" . print_r($categories, true) . "</pre>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error reading categories: " . $e->getMessage() . "</p>";
}

// Test session and auth
echo "<h3>4. Session and Authentication Test</h3>";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

echo "<p>Session ID: " . session_id() . "</p>";
echo "<p>Session Name: " . session_name() . "</p>";
echo "<p>Is Logged In: " . (isLoggedIn() ? 'Yes' : 'No') . "</p>";

if (isLoggedIn()) {
    $user = getCurrentUser();
    echo "<p>Current User: " . ($user ? $user['name'] : 'Unknown') . "</p>";
} else {
    echo "<p style='color: orange;'>⚠️ Not logged in - this will cause API 401 errors</p>";
}

// Test direct API call
echo "<h3>5. Direct API Test</h3>";
if (isLoggedIn()) {
    try {
        // Simulate categories API call
        $stmt = $pdo->prepare("
            SELECT id, name, name_ar, icon, color, user_id, 
                   CASE WHEN user_id IS NULL THEN 'system' ELSE 'custom' END as type
            FROM categories 
            WHERE user_id IS NULL OR user_id = ? 
            ORDER BY 
                CASE WHEN user_id IS NULL THEN 0 ELSE 1 END,
                name_ar
            LIMIT 5
        ");
        $user = getCurrentUser();
        $stmt->execute([$user['id']]);
        $categories = $stmt->fetchAll();
        
        echo "<p style='color: green;'>✅ API simulation successful</p>";
        echo "<pre>" . print_r($categories, true) . "</pre>";
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ API simulation failed: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p style='color: orange;'>⚠️ Cannot test API - not logged in</p>";
}

// Configuration check
echo "<h3>6. Configuration Check</h3>";
echo "<p>Database Name: " . DB_NAME . "</p>";
echo "<p>Database Host: " . DB_HOST . "</p>";
echo "<p>Site URL: " . SITE_URL . "</p>";
echo "<p>Debug Mode: " . (DEBUG_MODE ? 'Enabled' : 'Disabled') . "</p>";

// PHP Error log check
echo "<h3>7. Recent PHP Errors</h3>";
$error_log = ini_get('error_log');
if ($error_log && file_exists($error_log)) {
    $errors = file_get_contents($error_log);
    $recent_errors = array_slice(explode("\n", $errors), -10);
    echo "<pre>" . implode("\n", $recent_errors) . "</pre>";
} else {
    echo "<p>No error log found or configured</p>";
}

// Get any buffered output
$output = ob_get_clean();

?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Debug - <?php echo SITE_NAME; ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        h2, h3 { color: #333; }
        pre { background: #f0f0f0; padding: 10px; border-radius: 5px; overflow-x: auto; }
        p { margin: 5px 0; }
    </style>
</head>
<body>
    <?php echo $output; ?>
    
    <h3>8. Quick Actions</h3>
    <p>
        <a href="install.php" style="background: #007cba; color: white; padding: 5px 10px; text-decoration: none; border-radius: 3px;">Reinstall Database</a>
        <a href="reset_admin.php" style="background: #28a745; color: white; padding: 5px 10px; text-decoration: none; border-radius: 3px;">Reset Admin</a>
        <a href="login.php" style="background: #6f42c1; color: white; padding: 5px 10px; text-decoration: none; border-radius: 3px;">Login</a>
        <a href="system_info.php" style="background: #fd7e14; color: white; padding: 5px 10px; text-decoration: none; border-radius: 3px;">System Info</a>
    </p>
</body>
</html>
