<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/auth.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'logout':
            handleLogout();
            break;
        case 'profile':
            handleProfile();
            break;
        case 'update_profile':
            handleUpdateProfile();
            break;
        case 'change_password':
            handleChangePassword();
            break;
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'إجراء غير صحيح']);
    }
} catch (Exception $e) {
    error_log("Auth API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'حدث خطأ في الخادم']);
}

function handleLogout() {
    logout();
    
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
        echo json_encode(['success' => true, 'message' => 'تم تسجيل الخروج بنجاح']);
    } else {
        header('Location: ../login.php');
        exit();
    }
}

function handleProfile() {
    if (!isLoggedIn()) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'غير مصرح لك بالوصول']);
        return;
    }
    
    $user = getCurrentUser();
    
    if ($user) {
        // Remove sensitive information
        unset($user['password']);
        
        echo json_encode([
            'success' => true,
            'user' => $user
        ]);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'المستخدم غير موجود']);
    }
}

function handleUpdateProfile() {
    global $pdo;
    
    if (!isLoggedIn()) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'غير مصرح لك بالوصول']);
        return;
    }
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'طريقة غير مدعومة']);
        return;
    }
    
    $user = getCurrentUser();
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'بيانات غير صحيحة']);
        return;
    }
    
    // Validate required fields
    if (empty(trim($input['name']))) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'الاسم مطلوب']);
        return;
    }
    
    if (empty(trim($input['email'])) || !validateEmail($input['email'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'البريد الإلكتروني غير صحيح']);
        return;
    }
    
    // Check if email is already taken by another user
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $stmt->execute([$input['email'], $user['id']]);
    
    if ($stmt->fetch()) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'البريد الإلكتروني مستخدم بالفعل']);
        return;
    }
    
    // Update user profile
    $stmt = $pdo->prepare("
        UPDATE users 
        SET name = ?, email = ?, updated_at = CURRENT_TIMESTAMP 
        WHERE id = ?
    ");
    
    if ($stmt->execute([
        sanitizeInput($input['name']),
        sanitizeInput($input['email']),
        $user['id']
    ])) {
        // Update session data
        $_SESSION['user_name'] = $input['name'];
        
        echo json_encode(['success' => true, 'message' => 'تم تحديث الملف الشخصي بنجاح']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'فشل في تحديث الملف الشخصي']);
    }
}

function handleChangePassword() {
    global $pdo;
    
    if (!isLoggedIn()) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'غير مصرح لك بالوصول']);
        return;
    }
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'طريقة غير مدعومة']);
        return;
    }
    
    $user = getCurrentUser();
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'بيانات غير صحيحة']);
        return;
    }
    
    // Validate required fields
    $requiredFields = ['current_password', 'new_password', 'confirm_password'];
    foreach ($requiredFields as $field) {
        if (!isset($input[$field]) || empty($input[$field])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'جميع الحقول مطلوبة']);
            return;
        }
    }
    
    // Validate new password
    if (strlen($input['new_password']) < 6) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'كلمة المرور الجديدة يجب أن تكون 6 أحرف على الأقل']);
        return;
    }
    
    if ($input['new_password'] !== $input['confirm_password']) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'كلمة المرور الجديدة وتأكيدها غير متطابقتان']);
        return;
    }
    
    // Get current password hash
    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$user['id']]);
    $currentPasswordHash = $stmt->fetchColumn();
    
    // Verify current password
    if (!password_verify($input['current_password'], $currentPasswordHash)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'كلمة المرور الحالية غير صحيحة']);
        return;
    }
    
    // Update password
    $newPasswordHash = password_hash($input['new_password'], PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("
        UPDATE users 
        SET password = ?, updated_at = CURRENT_TIMESTAMP 
        WHERE id = ?
    ");
    
    if ($stmt->execute([$newPasswordHash, $user['id']])) {
        // Invalidate all existing sessions for security
        $stmt = $pdo->prepare("DELETE FROM user_sessions WHERE user_id = ?");
        $stmt->execute([$user['id']]);
        
        echo json_encode(['success' => true, 'message' => 'تم تغيير كلمة المرور بنجاح']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'فشل في تغيير كلمة المرور']);
    }
}