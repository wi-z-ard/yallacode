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
$action = $_GET['action'] ?? '';

try {
    switch ($method) {
        case 'GET':
            handleGetRequest($action, $user);
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
    error_log("Budgets API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'حدث خطأ في الخادم']);
}

function handleGetRequest($action, $user) {
    global $pdo;
    
    switch ($action) {
        case 'current':
            // Get current month budgets with actual spending
            $currentMonth = date('Y-m-01');
            $nextMonth = date('Y-m-01', strtotime('+1 month'));
            
            $stmt = $pdo->prepare("
                SELECT 
                    b.*,
                    c.name_ar as category_name,
                    c.color as category_color,
                    c.icon as category_icon,
                    COALESCE(SUM(t.amount), 0) as actual_amount,
                    ROUND((COALESCE(SUM(t.amount), 0) / b.amount * 100), 2) as percentage_used
                FROM budgets b
                LEFT JOIN categories c ON b.category_id = c.id
                LEFT JOIN transactions t ON b.category_id = t.category_id 
                    AND b.user_id = t.user_id 
                    AND t.transaction_date BETWEEN b.start_date AND b.end_date
                    AND t.status = 'cleared'
                WHERE b.user_id = ? 
                    AND b.start_date <= ? 
                    AND b.end_date >= ?
                GROUP BY b.id
                ORDER BY percentage_used DESC
            ");
            $stmt->execute([$user['id'], $currentMonth, $currentMonth]);
            $budgets = $stmt->fetchAll();
            
            echo json_encode([
                'success' => true,
                'budgets' => $budgets
            ]);
            break;
            
        case 'get':
            // Get single budget by ID
            $budgetId = intval($_GET['id'] ?? 0);
            if (!$budgetId) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'معرف الميزانية مطلوب']);
                return;
            }
            
            $stmt = $pdo->prepare("
                SELECT 
                    b.*,
                    c.name_ar as category_name,
                    c.color as category_color,
                    c.icon as category_icon
                FROM budgets b
                LEFT JOIN categories c ON b.category_id = c.id
                WHERE b.id = ? AND b.user_id = ?
            ");
            $stmt->execute([$budgetId, $user['id']]);
            $budget = $stmt->fetch();
            
            if ($budget) {
                echo json_encode([
                    'success' => true,
                    'budget' => $budget
                ]);
            } else {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'الميزانية غير موجودة']);
            }
            break;
            
        case 'all':
            $page = intval($_GET['page'] ?? 1);
            $limit = intval($_GET['limit'] ?? 20);
            $offset = ($page - 1) * $limit;
            
            // Get total count
            $countStmt = $pdo->prepare("SELECT COUNT(*) FROM budgets WHERE user_id = ?");
            $countStmt->execute([$user['id']]);
            $totalCount = $countStmt->fetchColumn();
            
            // Get budgets
            $stmt = $pdo->prepare("
                SELECT 
                    b.*,
                    c.name_ar as category_name,
                    c.color as category_color,
                    c.icon as category_icon
                FROM budgets b
                LEFT JOIN categories c ON b.category_id = c.id
                WHERE b.user_id = ?
                ORDER BY b.created_at DESC
                LIMIT ? OFFSET ?
            ");
            $stmt->execute([$user['id'], $limit, $offset]);
            $budgets = $stmt->fetchAll();
            
            echo json_encode([
                'success' => true,
                'budgets' => $budgets,
                'pagination' => [
                    'current_page' => $page,
                    'total_pages' => ceil($totalCount / $limit),
                    'total_count' => $totalCount,
                    'per_page' => $limit
                ]
            ]);
            break;
            
        case 'summary':
            // Get budget summary for dashboard
            $currentMonth = date('Y-m-01');
            $nextMonth = date('Y-m-01', strtotime('+1 month'));
            
            $stmt = $pdo->prepare("
                SELECT 
                    COUNT(*) as total_budgets,
                    SUM(b.amount) as total_budget_amount,
                    SUM(COALESCE(spending.actual_amount, 0)) as total_actual_amount,
                    ROUND(AVG(COALESCE(spending.actual_amount, 0) / b.amount * 100), 2) as avg_percentage_used
                FROM budgets b
                LEFT JOIN (
                    SELECT 
                        t.category_id,
                        SUM(t.amount) as actual_amount
                    FROM transactions t
                    WHERE t.user_id = ? 
                        AND t.transaction_date >= ? 
                        AND t.transaction_date < ?
                        AND t.status = 'cleared'
                    GROUP BY t.category_id
                ) spending ON b.category_id = spending.category_id
                WHERE b.user_id = ? 
                    AND b.start_date <= ? 
                    AND b.end_date >= ?
            ");
            $stmt->execute([$user['id'], $currentMonth, $nextMonth, $user['id'], $currentMonth, $currentMonth]);
            $summary = $stmt->fetch();
            
            echo json_encode([
                'success' => true,
                'summary' => $summary
            ]);
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'إجراء غير صحيح']);
    }
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
    $requiredFields = ['category_id', 'amount', 'period', 'start_date', 'end_date'];
    foreach ($requiredFields as $field) {
        if (!isset($input[$field]) || empty($input[$field])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => "الحقل $field مطلوب"]);
            return;
        }
    }
    
    // Validate amount
    if (!is_numeric($input['amount']) || $input['amount'] <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'المبلغ يجب أن يكون رقم موجب']);
        return;
    }
    
    // Validate period
    if (!in_array($input['period'], ['monthly', 'yearly'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'الفترة غير صحيحة']);
        return;
    }
    
    // Validate dates
    if (strtotime($input['start_date']) >= strtotime($input['end_date'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'تاريخ البداية يجب أن يكون قبل تاريخ النهاية']);
        return;
    }
    
    // Validate category exists
    $stmt = $pdo->prepare("SELECT id FROM categories WHERE id = ? AND (user_id = ? OR user_id IS NULL)");
    $stmt->execute([$input['category_id'], $user['id']]);
    if (!$stmt->fetch()) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'الفئة غير موجودة']);
        return;
    }
    
    // Check for overlapping budgets for the same category
    $stmt = $pdo->prepare("
        SELECT id FROM budgets 
        WHERE user_id = ? AND category_id = ? 
        AND (
            (start_date <= ? AND end_date >= ?) OR
            (start_date <= ? AND end_date >= ?) OR
            (start_date >= ? AND end_date <= ?)
        )
    ");
    $stmt->execute([
        $user['id'], 
        $input['category_id'],
        $input['start_date'], $input['start_date'],
        $input['end_date'], $input['end_date'],
        $input['start_date'], $input['end_date']
    ]);
    
    if ($stmt->fetch()) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'يوجد ميزانية متداخلة لنفس الفئة في هذه الفترة']);
        return;
    }
    
    // Insert budget
    $stmt = $pdo->prepare("
        INSERT INTO budgets (user_id, category_id, amount, period, start_date, end_date) 
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    
    $success = $stmt->execute([
        $user['id'],
        $input['category_id'],
        $input['amount'],
        $input['period'],
        $input['start_date'],
        $input['end_date']
    ]);
    
    if ($success) {
        $budgetId = $pdo->lastInsertId();
        
        // Get the created budget with category info
        $stmt = $pdo->prepare("
            SELECT 
                b.*,
                c.name_ar as category_name,
                c.color as category_color,
                c.icon as category_icon
            FROM budgets b
            LEFT JOIN categories c ON b.category_id = c.id
            WHERE b.id = ?
        ");
        $stmt->execute([$budgetId]);
        $budget = $stmt->fetch();
        
        echo json_encode([
            'success' => true,
            'message' => 'تم إضافة الميزانية بنجاح',
            'budget' => $budget
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'فشل في إضافة الميزانية']);
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
    
    // Check if budget belongs to user
    $stmt = $pdo->prepare("SELECT id FROM budgets WHERE id = ? AND user_id = ?");
    $stmt->execute([$input['id'], $user['id']]);
    if (!$stmt->fetch()) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'الميزانية غير موجودة']);
        return;
    }
    
    // Build update query dynamically
    $updateFields = [];
    $params = [];
    
    $allowedFields = ['category_id', 'amount', 'period', 'start_date', 'end_date'];
    
    foreach ($allowedFields as $field) {
        if (isset($input[$field])) {
            $updateFields[] = "$field = ?";
            $params[] = $input[$field];
        }
    }
    
    if (empty($updateFields)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'لا توجد بيانات للتحديث']);
        return;
    }
    
    // Validate amount if provided
    if (isset($input['amount']) && (!is_numeric($input['amount']) || $input['amount'] <= 0)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'المبلغ يجب أن يكون رقم موجب']);
        return;
    }
    
    // Validate period if provided
    if (isset($input['period']) && !in_array($input['period'], ['monthly', 'yearly'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'الفترة غير صحيحة']);
        return;
    }
    
    $params[] = $input['id'];
    $params[] = $user['id'];
    
    $sql = "UPDATE budgets SET " . implode(', ', $updateFields) . ", updated_at = CURRENT_TIMESTAMP WHERE id = ? AND user_id = ?";
    $stmt = $pdo->prepare($sql);
    
    if ($stmt->execute($params)) {
        echo json_encode(['success' => true, 'message' => 'تم تحديث الميزانية بنجاح']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'فشل في تحديث الميزانية']);
    }
}

function handleDeleteRequest($user) {
    global $pdo;
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'معرف الميزانية مطلوب']);
        return;
    }
    
    // Check if budget belongs to user
    $stmt = $pdo->prepare("SELECT id FROM budgets WHERE id = ? AND user_id = ?");
    $stmt->execute([$input['id'], $user['id']]);
    if (!$stmt->fetch()) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'الميزانية غير موجودة']);
        return;
    }
    
    // Delete budget
    $stmt = $pdo->prepare("DELETE FROM budgets WHERE id = ? AND user_id = ?");
    
    if ($stmt->execute([$input['id'], $user['id']])) {
        echo json_encode(['success' => true, 'message' => 'تم حذف الميزانية بنجاح']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'فشل في حذف الميزانية']);
    }
}