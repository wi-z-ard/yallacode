<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/auth.php';

// Check if user is logged in
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'غير مصرح لك بالوصول']);
    exit();
}

$user = getCurrentUser();
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            handleGetRequest($user);
            break;
        case 'POST':
            handlePostRequest($user);
            break;
        case 'PUT':
            handlePutRequest($user);
            break;
        case 'DELETE':
            handleDeleteRequest($user);
            break;
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'طريقة غير مدعومة']);
    }
} catch (Exception $e) {
    error_log("Categories API Error: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'حدث خطأ في الخادم',
        'debug' => DEBUG_MODE ? [
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ] : null
    ]);
} catch (Error $e) {
    error_log("Categories API Fatal Error: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'حدث خطأ فادح في الخادم',
        'debug' => DEBUG_MODE ? [
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ] : null
    ]);
}

function handleGetRequest($user) {
    global $pdo;
    
    // Check if requesting a specific category
    if (isset($_GET['id'])) {
        $categoryId = intval($_GET['id']);
        
        $stmt = $pdo->prepare("
            SELECT id, name, name_ar, icon, color, user_id, 
                   CASE WHEN user_id IS NULL THEN 'system' ELSE 'custom' END as type
            FROM categories 
            WHERE id = ? AND (user_id IS NULL OR user_id = ?)
        ");
        $stmt->execute([$categoryId, $user['id']]);
        $category = $stmt->fetch();
        
        if ($category) {
            echo json_encode([
                'success' => true,
                'category' => $category
            ]);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'الفئة غير موجودة']);
        }
        return;
    }
    
    // Get all categories (system + user custom categories)
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
    
    // Get spending statistics for each category (current month)
    $currentMonth = date('Y-m-01');
    $nextMonth = date('Y-m-01', strtotime('+1 month'));
    
    $stmt = $pdo->prepare("
        SELECT 
            c.id,
            COALESCE(SUM(t.amount), 0) as total_spent
        FROM categories c
        LEFT JOIN transactions t ON c.id = t.category_id 
            AND t.user_id = ? 
            AND t.transaction_date >= ? 
            AND t.transaction_date < ?
            AND t.status = 'cleared'
        WHERE c.user_id IS NULL OR c.user_id = ?
        GROUP BY c.id
    ");
    $stmt->execute([$user['id'], $currentMonth, $nextMonth, $user['id']]);
    $stats = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    
    // Merge stats with categories
    foreach ($categories as &$category) {
        $category['current_month_spending'] = $stats[$category['id']] ?? 0;
    }
    
    echo json_encode([
        'success' => true,
        'categories' => $categories
    ]);
}

function handlePostRequest($user) {
    global $pdo;
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'بيانات غير صحيحة']);
        return;
    }
    
    // Validate required fields
    $requiredFields = ['name', 'name_ar'];
    foreach ($requiredFields as $field) {
        if (!isset($input[$field]) || empty(trim($input[$field]))) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => "الحقل $field مطلوب"]);
            return;
        }
    }
    
    // Check if category name already exists for this user
    $stmt = $pdo->prepare("
        SELECT id FROM categories 
        WHERE (name_ar = ? OR name = ?) AND (user_id = ? OR user_id IS NULL)
    ");
    $stmt->execute([
        trim($input['name_ar']), 
        trim($input['name']), 
        $user['id']
    ]);
    
    if ($stmt->fetch()) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'اسم الفئة موجود بالفعل']);
        return;
    }
    
    // Insert new category
    $stmt = $pdo->prepare("
        INSERT INTO categories (name, name_ar, icon, color, user_id) 
        VALUES (?, ?, ?, ?, ?)
    ");
    
    $success = $stmt->execute([
        sanitizeInput($input['name']),
        sanitizeInput($input['name_ar']),
        sanitizeInput($input['icon'] ?? 'fas fa-tag'),
        sanitizeInput($input['color'] ?? '#3B82F6'),
        $user['id']
    ]);
    
    if ($success) {
        $categoryId = $pdo->lastInsertId();
        
        // Get the created category
        $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
        $stmt->execute([$categoryId]);
        $category = $stmt->fetch();
        
        echo json_encode([
            'success' => true,
            'message' => 'تم إضافة الفئة بنجاح',
            'category' => $category
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'فشل في إضافة الفئة']);
    }
}

function handlePutRequest($user) {
    global $pdo;
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'بيانات غير صحيحة']);
        return;
    }
    
    // Check if category belongs to user (can't edit system categories)
    $stmt = $pdo->prepare("SELECT id FROM categories WHERE id = ? AND user_id = ?");
    $stmt->execute([$input['id'], $user['id']]);
    if (!$stmt->fetch()) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'الفئة غير موجودة أو لا يمكن تعديلها']);
        return;
    }
    
    // Build update query dynamically
    $updateFields = [];
    $params = [];
    
    $allowedFields = ['name', 'name_ar', 'icon', 'color'];
    
    foreach ($allowedFields as $field) {
        if (isset($input[$field]) && !empty(trim($input[$field]))) {
            $updateFields[] = "$field = ?";
            $params[] = sanitizeInput($input[$field]);
        }
    }
    
    if (empty($updateFields)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'لا توجد بيانات للتحديث']);
        return;
    }
    
    // Check for duplicate names if name is being updated
    if (isset($input['name']) || isset($input['name_ar'])) {
        $stmt = $pdo->prepare("
            SELECT id FROM categories 
            WHERE (name_ar = ? OR name = ?) AND id != ? AND (user_id = ? OR user_id IS NULL)
        ");
        $stmt->execute([
            $input['name_ar'] ?? '',
            $input['name'] ?? '',
            $input['id'],
            $user['id']
        ]);
        
        if ($stmt->fetch()) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'اسم الفئة موجود بالفعل']);
            return;
        }
    }
    
    $params[] = $input['id'];
    $params[] = $user['id'];
    
    $sql = "UPDATE categories SET " . implode(', ', $updateFields) . " WHERE id = ? AND user_id = ?";
    $stmt = $pdo->prepare($sql);
    
    if ($stmt->execute($params)) {
        echo json_encode(['success' => true, 'message' => 'تم تحديث الفئة بنجاح']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'فشل في تحديث الفئة']);
    }
}

function handleDeleteRequest($user) {
    global $pdo;
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'معرف الفئة مطلوب']);
        return;
    }
    
    // Check if category belongs to user (can't delete system categories)
    $stmt = $pdo->prepare("SELECT id FROM categories WHERE id = ? AND user_id = ?");
    $stmt->execute([$input['id'], $user['id']]);
    if (!$stmt->fetch()) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'الفئة غير موجودة أو لا يمكن حذفها']);
        return;
    }
    
    // Check if category is being used in transactions
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM transactions WHERE category_id = ? AND user_id = ?");
    $stmt->execute([$input['id'], $user['id']]);
    $transactionCount = $stmt->fetchColumn();
    
    if ($transactionCount > 0) {
        http_response_code(400);
        echo json_encode([
            'success' => false, 
            'message' => "لا يمكن حذف الفئة لأنها مستخدمة في $transactionCount معاملة"
        ]);
        return;
    }
    
    // Check if category is being used in budgets
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM budgets WHERE category_id = ? AND user_id = ?");
    $stmt->execute([$input['id'], $user['id']]);
    $budgetCount = $stmt->fetchColumn();
    
    if ($budgetCount > 0) {
        http_response_code(400);
        echo json_encode([
            'success' => false, 
            'message' => "لا يمكن حذف الفئة لأنها مستخدمة في $budgetCount ميزانية"
        ]);
        return;
    }
    
    // Delete category
    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ? AND user_id = ?");
    
    if ($stmt->execute([$input['id'], $user['id']])) {
        echo json_encode(['success' => true, 'message' => 'تم حذف الفئة بنجاح']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'فشل في حذف الفئة']);
    }
}