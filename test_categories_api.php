<?php
/**
 * Test Categories API directly
 * This will help us see the exact error
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Testing Categories API</h2>";

try {
    require_once 'config/config.php';
    echo "<p>✅ Config loaded</p>";
    
    require_once 'config/database.php';
    echo "<p>✅ Database config loaded</p>";
    
    require_once 'includes/auth.php';
    echo "<p>✅ Auth included</p>";
    
    // Test database connection
    echo "<h3>Database Connection Test:</h3>";
    $stmt = $pdo->query("SELECT DATABASE() as db_name");
    $result = $stmt->fetch();
    echo "<p>Connected to database: " . $result['db_name'] . "</p>";
    
    // Check if categories table exists
    echo "<h3>Categories Table Test:</h3>";
    $stmt = $pdo->query("SHOW TABLES LIKE 'categories'");
    if ($stmt->rowCount() > 0) {
        echo "<p>✅ Categories table exists</p>";
        
        // Count categories
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM categories");
        $result = $stmt->fetch();
        echo "<p>Categories count: " . $result['count'] . "</p>";
        
        if ($result['count'] == 0) {
            echo "<p style='color: red;'>❌ No categories found! Run install.php to populate data.</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ Categories table does not exist! Run install.php first.</p>";
    }
    
    // Test session
    echo "<h3>Session Test:</h3>";
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    echo "<p>Session ID: " . session_id() . "</p>";
    
    // Test authentication
    echo "<h3>Authentication Test:</h3>";
    if (function_exists('isLoggedIn')) {
        if (isLoggedIn()) {
            echo "<p>✅ User is logged in</p>";
            $user = getCurrentUser();
            if ($user) {
                echo "<p>User: " . $user['name'] . " (ID: " . $user['id'] . ")</p>";
                
                // Test the actual query from categories API
                echo "<h3>Categories Query Test:</h3>";
                $stmt = $pdo->prepare("
                    SELECT id, name, name_ar, icon, color, user_id, 
                           CASE WHEN user_id IS NULL THEN 'system' ELSE 'custom' END as type
                    FROM categories 
                    WHERE user_id IS NULL OR user_id = ? 
                    ORDER BY 
                        CASE WHEN user_id IS NULL THEN 0 ELSE 1 END,
                        name_ar
                ");
                $stmt->execute([$user['id']]);
                $categories = $stmt->fetchAll();
                
                echo "<p>✅ Query executed successfully</p>";
                echo "<p>Found " . count($categories) . " categories</p>";
                
                if (count($categories) > 0) {
                    echo "<h4>Sample categories:</h4>";
                    echo "<pre>";
                    foreach (array_slice($categories, 0, 3) as $cat) {
                        print_r($cat);
                    }
                    echo "</pre>";
                }
                
            } else {
                echo "<p style='color: red;'>❌ Cannot get current user data</p>";
            }
        } else {
            echo "<p style='color: red;'>❌ User is not logged in</p>";
            echo "<p>You need to login first: <a href='login.php'>Login Page</a></p>";
        }
    } else {
        echo "<p style='color: red;'>❌ isLoggedIn function not found</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
    echo "<p>File: " . $e->getFile() . "</p>";
    echo "<p>Line: " . $e->getLine() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<hr>";
echo "<h3>Quick Actions:</h3>";
echo "<a href='install.php' style='background: #007cba; color: white; padding: 5px 10px; text-decoration: none; margin: 5px;'>Install Database</a>";
echo "<a href='reset_admin.php' style='background: #28a745; color: white; padding: 5px 10px; text-decoration: none; margin: 5px;'>Reset Admin</a>";
echo "<a href='login.php' style='background: #6f42c1; color: white; padding: 5px 10px; text-decoration: none; margin: 5px;'>Login</a>";
echo "<a href='debug_api.php' style='background: #fd7e14; color: white; padding: 5px 10px; text-decoration: none; margin: 5px;'>Debug API</a>";
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
h2, h3, h4 { color: #333; }
pre { background: #f0f0f0; padding: 10px; border-radius: 5px; overflow-x: auto; }
p { margin: 5px 0; }
</style>
