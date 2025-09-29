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
    error_log("Income API Error: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
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
    error_log("Income API Fatal Error: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
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

function handleGetRequest($action, $user) {
    global $pdo;
    
    switch ($action) {
        case 'get':
            $id = intval($_GET['id'] ?? 0);
            if (!$id) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'معرف الدخل مطلوب']);
                return;
            }
            
            $stmt = $pdo->prepare("
                SELECT t.*, c.name_ar as category_name, c.color as category_color 
                FROM transactions t 
                LEFT JOIN categories c ON t.category_id = c.id 
                WHERE t.id = ? AND t.user_id = ? AND t.transaction_type = 'income'
            ");
            $stmt->execute([$id, $user['id']]);
            $income = $stmt->fetch();
            
            if ($income) {
                echo json_encode([
                    'success' => true,
                    'income' => $income
                ]);
            } else {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'سجل الدخل غير موجود']);
            }
            break;
            
        case 'recent':
            $limit = intval($_GET['limit'] ?? 10);
            $stmt = $pdo->prepare("
                SELECT t.*, c.name_ar as category_name, c.color as category_color 
                FROM transactions t 
                LEFT JOIN categories c ON t.category_id = c.id 
                WHERE t.user_id = ? AND t.transaction_type = 'income'
                ORDER BY t.transaction_date DESC, t.created_at DESC 
                LIMIT ?
            ");
            $stmt->execute([$user['id'], $limit]);
            $income = $stmt->fetchAll();
            
            echo json_encode([
                'success' => true,
                'income' => $income
            ]);
            break;
            
        case 'all':
            $page = intval($_GET['page'] ?? 1);
            $limit = intval($_GET['limit'] ?? 20);
            $offset = ($page - 1) * $limit;
            $search = $_GET['search'] ?? '';
            $category_id = $_GET['category_id'] ?? '';
            $status = $_GET['status'] ?? '';
            
            $whereConditions = ['t.user_id = ?', 't.transaction_type = ?'];
            $params = [$user['id'], 'income'];
            
            if (!empty($search)) {
                $whereConditions[] = '(t.description LIKE ? OR t.merchant LIKE ?)';
                $params[] = "%$search%";
                $params[] = "%$search%";
            }
            
            if (!empty($category_id)) {
                $whereConditions[] = 't.category_id = ?';
                $params[] = $category_id;
            }
            
            if (!empty($status)) {
                $whereConditions[] = 't.status = ?';
                $params[] = $status;
            }
            
            $whereClause = implode(' AND ', $whereConditions);
            
            // Get total count
            $countStmt = $pdo->prepare("SELECT COUNT(*) FROM transactions t WHERE $whereClause");
            $countStmt->execute($params);
            $totalCount = $countStmt->fetchColumn();
            
            // Get income records
            $stmt = $pdo->prepare("
                SELECT t.*, c.name_ar as category_name, c.color as category_color 
                FROM transactions t 
                LEFT JOIN categories c ON t.category_id = c.id 
                WHERE $whereClause 
                ORDER BY t.transaction_date DESC, t.created_at DESC 
                LIMIT ? OFFSET ?
            ");
            $params[] = $limit;
            $params[] = $offset;
            $stmt->execute($params);
            $income = $stmt->fetchAll();
            
            echo json_encode([
                'success' => true,
                'income' => $income,
                'pagination' => [
                    'current_page' => $page,
                    'total_pages' => ceil($totalCount / $limit),
                    'total_count' => $totalCount,
                    'per_page' => $limit
                ]
            ]);
            break;
            
        case 'stats':
            $period = $_GET['period'] ?? 'month'; // month, year
            $date = $_GET['date'] ?? date('Y-m-d');
            
            if ($period === 'month') {
                $startDate = date('Y-m-01', strtotime($date));
                $endDate = date('Y-m-t', strtotime($date));
            } else {
                $startDate = date('Y-01-01', strtotime($date));
                $endDate = date('Y-12-31', strtotime($date));
            }
            
            // Total income
            $stmt = $pdo->prepare("
                SELECT SUM(amount) as total_amount, COUNT(*) as transaction_count 
                FROM transactions 
                WHERE user_id = ? AND transaction_type = 'income' 
                AND transaction_date BETWEEN ? AND ? AND status = 'cleared'
            ");
            $stmt->execute([$user['id'], $startDate, $endDate]);
            $totals = $stmt->fetch();
            
            // Income by category
            $stmt = $pdo->prepare("
                SELECT c.name_ar as category_name, c.color, SUM(t.amount) as amount 
                FROM transactions t 
                LEFT JOIN categories c ON t.category_id = c.id 
                WHERE t.user_id = ? AND t.transaction_type = 'income' 
                AND t.transaction_date BETWEEN ? AND ? AND t.status = 'cleared' 
                GROUP BY t.category_id 
                ORDER BY amount DESC
            ");
            $stmt->execute([$user['id'], $startDate, $endDate]);
            $categoryIncome = $stmt->fetchAll();
            
            echo json_encode([
                'success' => true,
                'stats' => [
                    'total_amount' => $totals['total_amount'] ?? 0,
                    'transaction_count' => $totals['transaction_count'] ?? 0,
                    'category_income' => $categoryIncome,
                    'period' => $period,
                    'start_date' => $startDate,
                    'end_date' => $endDate
                ]
            ]);
            break;
            
        case 'categories':
            // Get income categories
            $stmt = $pdo->prepare("
                SELECT id, name, name_ar, icon, color, category_type
                FROM categories 
                WHERE category_type = 'income' OR category_type = 'both'
                ORDER BY name_ar
            ");
            $stmt->execute();
            $categories = $stmt->fetchAll();
            
            echo json_encode([
                'success' => true,
                'categories' => $categories
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
    $requiredFields = ['description', 'amount', 'category_id', 'transaction_date'];
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
    
    // Validate category exists and is income type
    $stmt = $pdo->prepare("
        SELECT id FROM categories 
        WHERE id = ? AND (category_type = 'income' OR category_type = 'both')
    ");
    $stmt->execute([$input['category_id']]);
    if (!$stmt->fetch()) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'فئة الدخل غير موجودة']);
        return;
    }
    
    // Insert income transaction
    $stmt = $pdo->prepare("
        INSERT INTO transactions (user_id, category_id, description, amount, transaction_type, merchant, transaction_date, status) 
        VALUES (?, ?, ?, ?, 'income', ?, ?, ?)
    ");
    
    $success = $stmt->execute([
        $user['id'],
        $input['category_id'],
        sanitizeInput($input['description']),
        $input['amount'],
        sanitizeInput($input['merchant'] ?? ''),
        $input['transaction_date'],
        $input['status'] ?? 'cleared'
    ]);
    
    if ($success) {
        $incomeId = $pdo->lastInsertId();
        
        // Get the created income record with category info
        $stmt = $pdo->prepare("
            SELECT t.*, c.name_ar as category_name, c.color as category_color 
            FROM transactions t 
            LEFT JOIN categories c ON t.category_id = c.id 
            WHERE t.id = ?
        ");
        $stmt->execute([$incomeId]);
        $income = $stmt->fetch();
        
        echo json_encode([
            'success' => true,
            'message' => 'تم إضافة الدخل بنجاح',
            'income' => $income
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'فشل في إضافة الدخل']);
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
    
    // Check if income record belongs to user
    $stmt = $pdo->prepare("
        SELECT id FROM transactions 
        WHERE id = ? AND user_id = ? AND transaction_type = 'income'
    ");
    $stmt->execute([$input['id'], $user['id']]);
    if (!$stmt->fetch()) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'سجل الدخل غير موجود']);
        return;
    }
    
    // Build update query dynamically
    $updateFields = [];
    $params = [];
    
    $allowedFields = ['description', 'amount', 'category_id', 'merchant', 'transaction_date', 'status', 'notes'];
    
    foreach ($allowedFields as $field) {
        if (isset($input[$field])) {
            $updateFields[] = "$field = ?";
            $params[] = $field === 'description' || $field === 'merchant' || $field === 'notes' 
                ? sanitizeInput($input[$field]) 
                : $input[$field];
        }
    }
    
    if (empty($updateFields)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'لا توجد بيانات للتحديث']);
        return;
    }
    
    $params[] = $input['id'];
    $params[] = $user['id'];
    
    $sql = "UPDATE transactions SET " . implode(', ', $updateFields) . ", updated_at = CURRENT_TIMESTAMP WHERE id = ? AND user_id = ? AND transaction_type = 'income'";
    $stmt = $pdo->prepare($sql);
    
    if ($stmt->execute($params)) {
        echo json_encode(['success' => true, 'message' => 'تم تحديث الدخل بنجاح']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'فشل في تحديث الدخل']);
    }
}

function handleDeleteRequest($user) {
    global $pdo;
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'معرف الدخل مطلوب']);
        return;
    }
    
    // Check if income record belongs to user
    $stmt = $pdo->prepare("
        SELECT id FROM transactions 
        WHERE id = ? AND user_id = ? AND transaction_type = 'income'
    ");
    $stmt->execute([$input['id'], $user['id']]);
    if (!$stmt->fetch()) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'سجل الدخل غير موجود']);
        return;
    }
    
    // Delete income record
    $stmt = $pdo->prepare("
        DELETE FROM transactions 
        WHERE id = ? AND user_id = ? AND transaction_type = 'income'
    ");
    
    if ($stmt->execute([$input['id'], $user['id']])) {
        echo json_encode(['success' => true, 'message' => 'تم حذف الدخل بنجاح']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'فشل في حذف الدخل']);
    }
}
