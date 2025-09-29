<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
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
$action = $_GET['action'] ?? 'overview';

try {
    switch ($action) {
        case 'overview':
            getDashboardOverview($user);
            break;
        case 'monthly_spending':
            getMonthlySpending($user);
            break;
        case 'category_breakdown':
            getCategoryBreakdown($user);
            break;
        case 'cash_flow':
            getCashFlow($user);
            break;
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'إجراء غير صحيح']);
    }
} catch (Exception $e) {
    error_log("Dashboard API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'حدث خطأ في الخادم']);
}

function getDashboardOverview($user) {
    global $pdo;
    
    $currentMonth = date('Y-m-01');
    $nextMonth = date('Y-m-01', strtotime('+1 month'));
    $lastMonth = date('Y-m-01', strtotime('-1 month'));
    
    // Current month spending
    $stmt = $pdo->prepare("
        SELECT 
            COALESCE(SUM(amount), 0) as total_spending,
            COUNT(*) as transaction_count
        FROM transactions 
        WHERE user_id = ? 
            AND transaction_date >= ? 
            AND transaction_date < ?
            AND status = 'cleared'
    ");
    $stmt->execute([$user['id'], $currentMonth, $nextMonth]);
    $currentSpending = $stmt->fetch();
    
    // Last month spending for comparison
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(amount), 0) as total_spending
        FROM transactions 
        WHERE user_id = ? 
            AND transaction_date >= ? 
            AND transaction_date < ?
            AND status = 'cleared'
    ");
    $stmt->execute([$user['id'], $lastMonth, $currentMonth]);
    $lastMonthSpending = $stmt->fetchColumn();
    
    // Budget vs actual
    $stmt = $pdo->prepare("
        SELECT 
            COALESCE(SUM(b.amount), 0) as total_budget,
            COALESCE(SUM(spending.actual_amount), 0) as total_actual,
            CASE 
                WHEN SUM(b.amount) > 0 THEN ROUND(SUM(spending.actual_amount) / SUM(b.amount) * 100, 2)
                ELSE 0 
            END as budget_percentage
        FROM budgets b
        LEFT JOIN (
            SELECT 
                category_id,
                SUM(amount) as actual_amount
            FROM transactions
            WHERE user_id = ? 
                AND transaction_date >= ? 
                AND transaction_date < ?
                AND status = 'cleared'
            GROUP BY category_id
        ) spending ON b.category_id = spending.category_id
        WHERE b.user_id = ? 
            AND b.start_date <= ? 
            AND b.end_date >= ?
    ");
    $stmt->execute([$user['id'], $currentMonth, $nextMonth, $user['id'], $currentMonth, $currentMonth]);
    $budgetData = $stmt->fetch();
    
    // Savings goal progress (mock data for now)
    $savingsGoal = 500; // This should come from savings_goals table
    $currentSavings = max(0, $budgetData['total_budget'] - $budgetData['total_actual']);
    $savingsPercentage = $savingsGoal > 0 ? min(100, ($currentSavings / $savingsGoal) * 100) : 0;
    
    // Calculate spending change percentage
    $spendingChange = 0;
    if ($lastMonthSpending > 0) {
        $spendingChange = (($currentSpending['total_spending'] - $lastMonthSpending) / $lastMonthSpending) * 100;
    }
    
    echo json_encode([
        'success' => true,
        'data' => [
            'current_month_spending' => $currentSpending['total_spending'],
            'transaction_count' => $currentSpending['transaction_count'],
            'spending_change_percentage' => round($spendingChange, 1),
            'total_budget' => $budgetData['total_budget'],
            'budget_used_percentage' => $budgetData['budget_percentage'],
            'savings_goal' => $savingsGoal,
            'savings_achieved' => $currentSavings,
            'savings_percentage' => round($savingsPercentage, 1),
            'month_name' => date('F Y')
        ]
    ]);
}

function getMonthlySpending($user) {
    global $pdo;
    
    $months = intval($_GET['months'] ?? 6);
    
    $stmt = $pdo->prepare("
        SELECT 
            DATE_FORMAT(transaction_date, '%Y-%m') as month,
            DATE_FORMAT(transaction_date, '%M %Y') as month_name,
            SUM(amount) as total_amount,
            COUNT(*) as transaction_count
        FROM transactions 
        WHERE user_id = ? 
            AND transaction_date >= DATE_SUB(CURDATE(), INTERVAL ? MONTH)
            AND status = 'cleared'
        GROUP BY DATE_FORMAT(transaction_date, '%Y-%m')
        ORDER BY month ASC
    ");
    $stmt->execute([$user['id'], $months]);
    $monthlyData = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'data' => $monthlyData
    ]);
}

function getCategoryBreakdown($user) {
    global $pdo;
    
    $period = $_GET['period'] ?? 'month'; // month, quarter, year
    
    switch ($period) {
        case 'quarter':
            $startDate = date('Y-m-01', strtotime('-3 months'));
            break;
        case 'year':
            $startDate = date('Y-01-01');
            break;
        default:
            $startDate = date('Y-m-01');
    }
    
    $stmt = $pdo->prepare("
        SELECT 
            c.name_ar as category_name,
            c.color as category_color,
            c.icon as category_icon,
            SUM(t.amount) as total_amount,
            COUNT(t.id) as transaction_count,
            ROUND(SUM(t.amount) / (
                SELECT SUM(amount) 
                FROM transactions 
                WHERE user_id = ? 
                    AND transaction_date >= ? 
                    AND status = 'cleared'
            ) * 100, 2) as percentage
        FROM transactions t
        LEFT JOIN categories c ON t.category_id = c.id
        WHERE t.user_id = ? 
            AND t.transaction_date >= ?
            AND t.status = 'cleared'
        GROUP BY t.category_id, c.name_ar, c.color, c.icon
        HAVING total_amount > 0
        ORDER BY total_amount DESC
    ");
    $stmt->execute([$user['id'], $startDate, $user['id'], $startDate]);
    $categoryData = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'data' => $categoryData,
        'period' => $period,
        'start_date' => $startDate
    ]);
}

function getCashFlow($user) {
    global $pdo;
    
    $months = intval($_GET['months'] ?? 6);
    
    // Get monthly cash flow by category
    $stmt = $pdo->prepare("
        SELECT 
            DATE_FORMAT(t.transaction_date, '%Y-%m') as month,
            DATE_FORMAT(t.transaction_date, '%M') as month_name,
            c.name_ar as category_name,
            c.color as category_color,
            SUM(t.amount) as amount
        FROM transactions t
        LEFT JOIN categories c ON t.category_id = c.id
        WHERE t.user_id = ? 
            AND t.transaction_date >= DATE_SUB(CURDATE(), INTERVAL ? MONTH)
            AND t.status = 'cleared'
        GROUP BY DATE_FORMAT(t.transaction_date, '%Y-%m'), t.category_id
        ORDER BY month ASC, amount DESC
    ");
    $stmt->execute([$user['id'], $months]);
    $cashFlowData = $stmt->fetchAll();
    
    // Organize data by month
    $organizedData = [];
    foreach ($cashFlowData as $row) {
        $month = $row['month'];
        if (!isset($organizedData[$month])) {
            $organizedData[$month] = [
                'month' => $month,
                'month_name' => $row['month_name'],
                'categories' => [],
                'total' => 0
            ];
        }
        
        $organizedData[$month]['categories'][] = [
            'category_name' => $row['category_name'],
            'category_color' => $row['category_color'],
            'amount' => $row['amount']
        ];
        $organizedData[$month]['total'] += $row['amount'];
    }
    
    echo json_encode([
        'success' => true,
        'data' => array_values($organizedData)
    ]);
}