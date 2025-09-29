<?php
/**
 * Simple Categories API Test (No Auth)
 * This will help us isolate the database issue
 */

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    require_once '../config/config.php';
    require_once '../config/database.php';
    
    // Test database connection
    $stmt = $pdo->query("SELECT DATABASE() as db_name");
    $result = $stmt->fetch();
    
    // Check if categories table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'categories'");
    if ($stmt->rowCount() == 0) {
        throw new Exception("Categories table does not exist. Please run install.php first.");
    }
    
    // Get categories
    $stmt = $pdo->query("SELECT id, name, name_ar, icon, color FROM categories LIMIT 10");
    $categories = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'database' => $result['db_name'],
        'categories_count' => count($categories),
        'categories' => $categories,
        'message' => 'Database connection and query successful'
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ]);
}
?>
