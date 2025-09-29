<?php
/**
 * Debug Categories API with detailed error reporting
 */

header('Content-Type: application/json');

// Enable all error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

// Start output buffering to catch any output
ob_start();

$debug_info = [];
$errors = [];

try {
    $debug_info[] = "Starting debug...";
    
    // Step 1: Load config
    $debug_info[] = "Loading config...";
    require_once '../config/config.php';
    $debug_info[] = "Config loaded successfully";
    
    // Step 2: Load database
    $debug_info[] = "Loading database...";
    require_once '../config/database.php';
    $debug_info[] = "Database loaded successfully";
    
    // Step 3: Test database connection
    $debug_info[] = "Testing database connection...";
    $stmt = $pdo->query("SELECT DATABASE() as db_name");
    $result = $stmt->fetch();
    $debug_info[] = "Connected to database: " . $result['db_name'];
    
    // Step 4: Load auth
    $debug_info[] = "Loading auth...";
    require_once '../includes/auth.php';
    $debug_info[] = "Auth loaded successfully";
    
    // Step 5: Check session
    $debug_info[] = "Checking session...";
    $debug_info[] = "Session status: " . session_status();
    $debug_info[] = "Session ID: " . session_id();
    
    // Step 6: Check if logged in
    $debug_info[] = "Checking authentication...";
    if (function_exists('isLoggedIn')) {
        $debug_info[] = "isLoggedIn function exists";
        $isLoggedIn = isLoggedIn();
        $debug_info[] = "isLoggedIn result: " . ($isLoggedIn ? 'true' : 'false');
        
        if ($isLoggedIn) {
            $debug_info[] = "Getting current user...";
            $user = getCurrentUser();
            if ($user) {
                $debug_info[] = "Current user: " . $user['name'] . " (ID: " . $user['id'] . ")";
                
                // Step 7: Test the actual categories query
                $debug_info[] = "Testing categories query...";
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
                $debug_info[] = "Categories query successful. Found: " . count($categories) . " categories";
                
                // Success response
                echo json_encode([
                    'success' => true,
                    'message' => 'Debug successful',
                    'debug_info' => $debug_info,
                    'categories_count' => count($categories),
                    'sample_categories' => array_slice($categories, 0, 3)
                ]);
                
            } else {
                $errors[] = "getCurrentUser() returned null";
            }
        } else {
            $errors[] = "User is not logged in";
        }
    } else {
        $errors[] = "isLoggedIn function does not exist";
    }
    
} catch (Exception $e) {
    $errors[] = "Exception: " . $e->getMessage();
    $errors[] = "File: " . $e->getFile();
    $errors[] = "Line: " . $e->getLine();
    $errors[] = "Trace: " . $e->getTraceAsString();
} catch (Error $e) {
    $errors[] = "Fatal Error: " . $e->getMessage();
    $errors[] = "File: " . $e->getFile();
    $errors[] = "Line: " . $e->getLine();
    $errors[] = "Trace: " . $e->getTraceAsString();
}

// Get any output that was captured
$output = ob_get_clean();
if (!empty($output)) {
    $errors[] = "Unexpected output: " . $output;
}

// If we have errors, return them
if (!empty($errors)) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Debug failed',
        'debug_info' => $debug_info,
        'errors' => $errors,
        'php_version' => PHP_VERSION,
        'session_info' => [
            'status' => session_status(),
            'id' => session_id(),
            'name' => session_name(),
            'user_id' => $_SESSION['user_id'] ?? 'not set',
            'user_name' => $_SESSION['user_name'] ?? 'not set'
        ]
    ]);
}
?>
