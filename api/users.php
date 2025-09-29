<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/auth.php';

// Check if user is logged in and has admin privileges
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'غير مصرح لك بالوصول']);
    exit();
}

$user = getCurrentUser();

// Only super_admin can manage users
if ($user['role'] !== 'super_admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'غير مصرح لك بإدارة المستخدمين']);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    switch ($method) {
        case 'GET':
            handleGetRequest($action);
            break;
        case 'POST':
            handlePostRequest();
            break;
        case 'PUT':
            handlePutRequest();
            break;
        case 'DELETE':
            handleDeleteRequest();
            break;
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'طريقة غير مدعومة']);
    }
} catch (Exception $e) {
    error_log("Users API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'حدث خطأ في الخادم']);
}

function handleGetRequest($action) {
    global $pdo;
    
    switch ($action) {
        case 'all':
            $page = intval($_GET['page'] ?? 1);
            $limit = intval($_GET['limit'] ?? 20);
            $offset = ($page - 1) * $limit;
            $search = $_GET['search'] ?? '';
            $role = $_GET['role'] ?? '';
            
            $whereConditions = ['1=1'];
            $params = [];
            
            if (!empty($search)) {
                $whereConditions[] = '(name LIKE ? OR email LIKE ?)';
                $params[] = "%$search%";
                $params[] = "%$search%";
            }
            
            if (!empty($role)) {
                $whereConditions[] = 'role = ?';
                $params[] = $role;
            }
            
            $whereClause = implode(' AND ', $whereConditions);
            
            // Get total count
            $countStmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE $whereClause");
            $countStmt->execute($params);
            $totalCount = $countStmt->fetchColumn();
            
            // Get users
            $stmt = $pdo->prepare("
                SELECT 
                    id, name, email, role, avatar, created_at, updated_at, is_active,
                    (SELECT COUNT(*) FROM transactions WHERE user_id = users.id) as transaction_count,
                    (SELECT COALESCE(SUM(amount), 0) FROM transactions WHERE user_id = users.id AND status = 'cleared') as total_spending
                FROM users 
                WHERE $whereClause 
                ORDER BY created_at DESC 
                LIMIT ? OFFSET ?
            ");
            $params[] = $limit;
            $params[] = $offset;
            $stmt->execute($params);
            $users = $stmt->fetchAll();
            
            echo json_encode([
                'success' => true,
                'users' => $users,
                'pagination' => [
                    'current_page' => $page,
                    'total_pages' => ceil($totalCount / $limit),
                    'total_count' => $totalCount,
                    'per_page' => $limit
                ]
            ]);
            break;
            
        case 'stats':
            // Get user statistics
            $stmt = $pdo->prepare("
                SELECT 
                    COUNT(*) as total_users,
                    SUM(CASE WHEN role = 'user' THEN 1 ELSE 0 END) as regular_users,
                    SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) as admin_users,
                    SUM(CASE WHEN role = 'super_admin' THEN 1 ELSE 0 END) as super_admin_users,
                    SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_users,
                    SUM(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) as new_users_30_days
                FROM users
            ");
            $stmt->execute();
            $stats = $stmt->fetch();
            
            echo json_encode([
                'success' => true,
                'stats' => $stats
            ]);
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'إجراء غير صحيح']);
    }
}

function handlePostRequest() {
    global $pdo;
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'بيانات غير صحيحة']);
        return;
    }
    
    // Validate required fields
    $requiredFields = ['name', 'email', 'password', 'role'];
    foreach ($requiredFields as $field) {
        if (!isset($input[$field]) || empty(trim($input[$field]))) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => "الحقل $field مطلوب"]);
            return;
        }
    }
    
    // Validate email
    if (!validateEmail($input['email'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'البريد الإلكتروني غير صحيح']);
        return;
    }
    
    // Validate password
    if (strlen($input['password']) < 6) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'كلمة المرور يجب أن تكون 6 أحرف على الأقل']);
        return;
    }
    
    // Validate role
    if (!in_array($input['role'], ['user', 'admin', 'super_admin'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'الدور غير صحيح']);
        return;
    }
    
    // Check if email already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$input['email']]);
    
    if ($stmt->fetch()) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'البريد الإلكتروني مستخدم بالفعل']);
        return;
    }
    
    // Hash password
    $hashedPassword = password_hash($input['password'], PASSWORD_DEFAULT);
    
    // Insert new user
    $stmt = $pdo->prepare("
        INSERT INTO users (name, email, password, role, is_active) 
        VALUES (?, ?, ?, ?, ?)
    ");
    
    $success = $stmt->execute([
        sanitizeInput($input['name']),
        sanitizeInput($input['email']),
        $hashedPassword,
        $input['role'],
        isset($input['is_active']) ? $input['is_active'] : true
    ]);
    
    if ($success) {
        $userId = $pdo->lastInsertId();
        
        // Get the created user
        $stmt = $pdo->prepare("SELECT id, name, email, role, avatar, created_at, is_active FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $newUser = $stmt->fetch();
        
        echo json_encode([
            'success' => true,
            'message' => 'تم إضافة المستخدم بنجاح',
            'user' => $newUser
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'فشل في إضافة المستخدم']);
    }
}

function handlePutRequest() {
    global $pdo;
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'بيانات غير صحيحة']);
        return;
    }
    
    // Check if user exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ?");
    $stmt->execute([$input['id']]);
    if (!$stmt->fetch()) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'المستخدم غير موجود']);
        return;
    }
    
    // Build update query dynamically
    $updateFields = [];
    $params = [];
    
    $allowedFields = ['name', 'email', 'role', 'is_active'];
    
    foreach ($allowedFields as $field) {
        if (isset($input[$field])) {
            if ($field === 'email' && !validateEmail($input[$field])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'البريد الإلكتروني غير صحيح']);
                return;
            }
            
            if ($field === 'role' && !in_array($input[$field], ['user', 'admin', 'super_admin'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'الدور غير صحيح']);
                return;
            }
            
            $updateFields[] = "$field = ?";
            $params[] = $field === 'name' || $field === 'email' ? sanitizeInput($input[$field]) : $input[$field];
        }
    }
    
    // Handle password update separately
    if (isset($input['password']) && !empty($input['password'])) {
        if (strlen($input['password']) < 6) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'كلمة المرور يجب أن تكون 6 أحرف على الأقل']);
            return;
        }
        
        $updateFields[] = "password = ?";
        $params[] = password_hash($input['password'], PASSWORD_DEFAULT);
    }
    
    if (empty($updateFields)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'لا توجد بيانات للتحديث']);
        return;
    }
    
    // Check for duplicate email if email is being updated
    if (isset($input['email'])) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$input['email'], $input['id']]);
        
        if ($stmt->fetch()) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'البريد الإلكتروني مستخدم بالفعل']);
            return;
        }
    }
    
    $params[] = $input['id'];
    
    $sql = "UPDATE users SET " . implode(', ', $updateFields) . ", updated_at = CURRENT_TIMESTAMP WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    
    if ($stmt->execute($params)) {
        echo json_encode(['success' => true, 'message' => 'تم تحديث المستخدم بنجاح']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'فشل في تحديث المستخدم']);
    }
}

function handleDeleteRequest() {
    global $pdo, $user;
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'معرف المستخدم مطلوب']);
        return;
    }
    
    // Prevent self-deletion
    if ($input['id'] == $user['id']) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'لا يمكنك حذف حسابك الخاص']);
        return;
    }
    
    // Check if user exists
    $stmt = $pdo->prepare("SELECT id, role FROM users WHERE id = ?");
    $stmt->execute([$input['id']]);
    $targetUser = $stmt->fetch();
    
    if (!$targetUser) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'المستخدم غير موجود']);
        return;
    }
    
    // Check if user has transactions (soft delete vs hard delete)
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM transactions WHERE user_id = ?");
    $stmt->execute([$input['id']]);
    $transactionCount = $stmt->fetchColumn();
    
    if ($transactionCount > 0) {
        // Soft delete - deactivate user
        $stmt = $pdo->prepare("UPDATE users SET is_active = 0, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        $message = 'تم إلغاء تفعيل المستخدم (يحتوي على معاملات)';
    } else {
        // Hard delete - remove user completely
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $message = 'تم حذف المستخدم بنجاح';
    }
    
    if ($stmt->execute([$input['id']])) {
        echo json_encode(['success' => true, 'message' => $message]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'فشل في حذف المستخدم']);
    }
}