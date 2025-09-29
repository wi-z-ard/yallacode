<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/currency.php';

// Check if user is logged in
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'غير مصرح لك بالوصول']);
    exit();
}

$user = getCurrentUser();
$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'financial_summary':
            handleFinancialSummary($user);
            break;
        case 'monthly_comparison':
            handleMonthlyComparison($user);
            break;
        case 'category_breakdown':
            handleCategoryBreakdown($user);
            break;
        case 'balance_history':
            handleBalanceHistory($user);
            break;
        case 'detailed_transactions':
            handleDetailedTransactions($user);
            break;
        case 'budget_performance':
            handleBudgetPerformance($user);
            break;
        case 'currency_settings':
            echo json_encode([
                'success' => true,
                'currency' => getCurrencySettings()
            ]);
            break;
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'نوع التقرير غير صحيح']);
    }
} catch (Exception $e) {
    error_log("Reports API Error: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'حدث خطأ في الخادم',
        'debug' => DEBUG_MODE ? [
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ] : null
    ]);
}

function handleFinancialSummary($user) {
    global $pdo;
    
    $period = $_GET['period'] ?? 'month'; // month, year, all
    $date = $_GET['date'] ?? date('Y-m-d');
    
    // Set date range based on period
    switch ($period) {
        case 'month':
            $startDate = date('Y-m-01', strtotime($date));
            $endDate = date('Y-m-t', strtotime($date));
            break;
        case 'year':
            $startDate = date('Y-01-01', strtotime($date));
            $endDate = date('Y-12-31', strtotime($date));
            break;
        case 'all':
        default:
            $startDate = '1900-01-01';
            $endDate = '2099-12-31';
            break;
    }
    
    // Get detailed breakdown
    $stmt = $pdo->prepare("
        SELECT 
            transaction_type,
            SUM(amount) as total_amount,
            COUNT(*) as transaction_count,
            AVG(amount) as avg_amount
        FROM transactions 
        WHERE user_id = ? 
        AND transaction_date BETWEEN ? AND ? 
        AND status = 'cleared'
        GROUP BY transaction_type
    ");
    $stmt->execute([$user['id'], $startDate, $endDate]);
    $results = $stmt->fetchAll();
    
    // Calculate totals
    $totalIncome = 0;
    $totalExpenses = 0;
    $incomeCount = 0;
    $expenseCount = 0;
    $avgIncome = 0;
    $avgExpense = 0;
    
    foreach ($results as $result) {
        if ($result['transaction_type'] === 'income') {
            $totalIncome = $result['total_amount'];
            $incomeCount = $result['transaction_count'];
            $avgIncome = $result['avg_amount'];
        } else {
            $totalExpenses = $result['total_amount'];
            $expenseCount = $result['transaction_count'];
            $avgExpense = $result['avg_amount'];
        }
    }
    
    $netIncome = $totalIncome - $totalExpenses;
    $savingsRate = $totalIncome > 0 ? ($netIncome / $totalIncome) * 100 : 0;
    
    // Get top spending categories
    $stmt = $pdo->prepare("
        SELECT 
            c.name_ar as category_name,
            c.color as category_color,
            SUM(t.amount) as total_amount,
            COUNT(t.id) as transaction_count
        FROM transactions t
        LEFT JOIN categories c ON t.category_id = c.id
        WHERE t.user_id = ? 
        AND t.transaction_date BETWEEN ? AND ? 
        AND t.status = 'cleared'
        AND t.transaction_type = 'expense'
        GROUP BY t.category_id
        ORDER BY total_amount DESC
        LIMIT 5
    ");
    $stmt->execute([$user['id'], $startDate, $endDate]);
    $topCategories = $stmt->fetchAll();
    
    // Get top income sources
    $stmt = $pdo->prepare("
        SELECT 
            c.name_ar as category_name,
            c.color as category_color,
            SUM(t.amount) as total_amount,
            COUNT(t.id) as transaction_count
        FROM transactions t
        LEFT JOIN categories c ON t.category_id = c.id
        WHERE t.user_id = ? 
        AND t.transaction_date BETWEEN ? AND ? 
        AND t.status = 'cleared'
        AND t.transaction_type = 'income'
        GROUP BY t.category_id
        ORDER BY total_amount DESC
        LIMIT 5
    ");
    $stmt->execute([$user['id'], $startDate, $endDate]);
    $topIncomeSources = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'data' => [
            'period' => $period,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'currency' => getCurrencySettings(),
            'summary' => [
                'total_income' => $totalIncome ?: 0,
                'total_expenses' => $totalExpenses ?: 0,
                'net_income' => $netIncome,
                'savings_rate' => round($savingsRate, 2),
                'income_count' => $incomeCount ?: 0,
                'expense_count' => $expenseCount ?: 0,
                'avg_income' => $avgIncome ?? 0,
                'avg_expense' => $avgExpense ?? 0
            ],
            'top_expense_categories' => $topCategories,
            'top_income_sources' => $topIncomeSources
        ]
    ]);
}

function handleMonthlyComparison($user) {
    global $pdo;
    
    $months = intval($_GET['months'] ?? 6);
    
    $stmt = $pdo->prepare("
        SELECT 
            YEAR(transaction_date) as year,
            MONTH(transaction_date) as month,
            transaction_type,
            SUM(amount) as total_amount,
            COUNT(*) as transaction_count
        FROM transactions 
        WHERE user_id = ? 
        AND transaction_date >= DATE_SUB(CURDATE(), INTERVAL ? MONTH)
        AND status = 'cleared'
        GROUP BY YEAR(transaction_date), MONTH(transaction_date), transaction_type
        ORDER BY year DESC, month DESC
    ");
    $stmt->execute([$user['id'], $months]);
    $results = $stmt->fetchAll();
    
    // Organize data by month
    $monthlyData = [];
    foreach ($results as $result) {
        $monthKey = $result['year'] . '-' . str_pad($result['month'], 2, '0', STR_PAD_LEFT);
        if (!isset($monthlyData[$monthKey])) {
            $monthlyData[$monthKey] = [
                'year' => $result['year'],
                'month' => $result['month'],
                'month_name' => getArabicMonthName($result['month']),
                'income' => 0,
                'expenses' => 0,
                'net' => 0,
                'income_count' => 0,
                'expense_count' => 0
            ];
        }
        
        if ($result['transaction_type'] === 'income') {
            $monthlyData[$monthKey]['income'] = $result['total_amount'];
            $monthlyData[$monthKey]['income_count'] = $result['transaction_count'];
        } else {
            $monthlyData[$monthKey]['expenses'] = $result['total_amount'];
            $monthlyData[$monthKey]['expense_count'] = $result['transaction_count'];
        }
        
        $monthlyData[$monthKey]['net'] = $monthlyData[$monthKey]['income'] - $monthlyData[$monthKey]['expenses'];
    }
    
    echo json_encode([
        'success' => true,
        'data' => array_values($monthlyData)
    ]);
}

function handleCategoryBreakdown($user) {
    global $pdo;
    
    $period = $_GET['period'] ?? 'month';
    $date = $_GET['date'] ?? date('Y-m-d');
    $type = $_GET['type'] ?? 'expense'; // income or expense
    
    // Set date range
    switch ($period) {
        case 'month':
            $startDate = date('Y-m-01', strtotime($date));
            $endDate = date('Y-m-t', strtotime($date));
            break;
        case 'year':
            $startDate = date('Y-01-01', strtotime($date));
            $endDate = date('Y-12-31', strtotime($date));
            break;
        default:
            $startDate = '1900-01-01';
            $endDate = '2099-12-31';
            break;
    }
    
    $stmt = $pdo->prepare("
        SELECT 
            c.id as category_id,
            c.name as category_name,
            c.name_ar as category_name_ar,
            c.color as category_color,
            c.icon as category_icon,
            SUM(t.amount) as total_amount,
            COUNT(t.id) as transaction_count,
            AVG(t.amount) as avg_amount,
            MIN(t.amount) as min_amount,
            MAX(t.amount) as max_amount
        FROM transactions t
        LEFT JOIN categories c ON t.category_id = c.id
        WHERE t.user_id = ? 
        AND t.transaction_date BETWEEN ? AND ? 
        AND t.status = 'cleared'
        AND t.transaction_type = ?
        GROUP BY t.category_id
        ORDER BY total_amount DESC
    ");
    $stmt->execute([$user['id'], $startDate, $endDate, $type]);
    $categories = $stmt->fetchAll();
    
    // Calculate percentages
    $totalAmount = array_sum(array_column($categories, 'total_amount'));
    
    foreach ($categories as &$category) {
        $category['percentage'] = $totalAmount > 0 ? ($category['total_amount'] / $totalAmount) * 100 : 0;
    }
    
    echo json_encode([
        'success' => true,
        'data' => [
            'period' => $period,
            'type' => $type,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'total_amount' => $totalAmount,
            'categories' => $categories
        ]
    ]);
}

function handleBalanceHistory($user) {
    global $pdo;
    
    $months = intval($_GET['months'] ?? 12);
    
    // Get monthly balance progression
    $stmt = $pdo->prepare("
        SELECT 
            DATE(transaction_date) as date,
            SUM(CASE WHEN transaction_type = 'income' THEN amount ELSE -amount END) as daily_net
        FROM transactions 
        WHERE user_id = ? 
        AND transaction_date >= DATE_SUB(CURDATE(), INTERVAL ? MONTH)
        AND status = 'cleared'
        GROUP BY DATE(transaction_date)
        ORDER BY date ASC
    ");
    $stmt->execute([$user['id'], $months]);
    $dailyData = $stmt->fetchAll();
    
    // Calculate running balance
    $balance = 0;
    $balanceHistory = [];
    
    foreach ($dailyData as $day) {
        $balance += $day['daily_net'];
        $balanceHistory[] = [
            'date' => $day['date'],
            'daily_net' => $day['daily_net'],
            'running_balance' => $balance
        ];
    }
    
    echo json_encode([
        'success' => true,
        'data' => [
            'current_balance' => $balance,
            'history' => $balanceHistory
        ]
    ]);
}

function handleDetailedTransactions($user) {
    global $pdo;
    
    $startDate = $_GET['start_date'] ?? date('Y-m-01');
    $endDate = $_GET['end_date'] ?? date('Y-m-t');
    $type = $_GET['type'] ?? 'all'; // all, income, expense
    $category = $_GET['category'] ?? '';
    $limit = intval($_GET['limit'] ?? 100);
    
    $whereConditions = ['t.user_id = ?', 't.transaction_date BETWEEN ? AND ?', 't.status = ?'];
    $params = [$user['id'], $startDate, $endDate, 'cleared'];
    
    if ($type !== 'all') {
        $whereConditions[] = 't.transaction_type = ?';
        $params[] = $type;
    }
    
    if (!empty($category)) {
        $whereConditions[] = 't.category_id = ?';
        $params[] = $category;
    }
    
    $whereClause = implode(' AND ', $whereConditions);
    
    $stmt = $pdo->prepare("
        SELECT 
            t.*,
            c.name_ar as category_name,
            c.color as category_color,
            c.icon as category_icon
        FROM transactions t
        LEFT JOIN categories c ON t.category_id = c.id
        WHERE $whereClause
        ORDER BY t.transaction_date DESC, t.created_at DESC
        LIMIT ?
    ");
    $params[] = $limit;
    $stmt->execute($params);
    $transactions = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'data' => [
            'transactions' => $transactions,
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'type' => $type,
                'category' => $category
            ]
        ]
    ]);
}

function handleBudgetPerformance($user) {
    global $pdo;
    
    $period = $_GET['period'] ?? 'month';
    $date = $_GET['date'] ?? date('Y-m-d');
    
    // Set date range
    switch ($period) {
        case 'month':
            $startDate = date('Y-m-01', strtotime($date));
            $endDate = date('Y-m-t', strtotime($date));
            break;
        case 'year':
            $startDate = date('Y-01-01', strtotime($date));
            $endDate = date('Y-12-31', strtotime($date));
            break;
    }
    
    // Get budget vs actual spending
    $stmt = $pdo->prepare("
        SELECT 
            b.id as budget_id,
            b.amount as budget_amount,
            b.period,
            c.name_ar as category_name,
            c.color as category_color,
            COALESCE(SUM(t.amount), 0) as actual_amount,
            COUNT(t.id) as transaction_count
        FROM budgets b
        LEFT JOIN categories c ON b.category_id = c.id
        LEFT JOIN transactions t ON b.category_id = t.category_id 
            AND b.user_id = t.user_id 
            AND t.transaction_date BETWEEN ? AND ?
            AND t.status = 'cleared'
            AND t.transaction_type = 'expense'
        WHERE b.user_id = ?
        AND b.start_date <= ? AND b.end_date >= ?
        GROUP BY b.id
        ORDER BY c.name_ar
    ");
    $stmt->execute([$startDate, $endDate, $user['id'], $endDate, $startDate]);
    $budgets = $stmt->fetchAll();
    
    // Calculate performance metrics
    foreach ($budgets as &$budget) {
        $budget['percentage_used'] = $budget['budget_amount'] > 0 ? 
            ($budget['actual_amount'] / $budget['budget_amount']) * 100 : 0;
        $budget['remaining_amount'] = $budget['budget_amount'] - $budget['actual_amount'];
        $budget['status'] = $budget['percentage_used'] > 100 ? 'over' : 
            ($budget['percentage_used'] > 80 ? 'warning' : 'good');
    }
    
    echo json_encode([
        'success' => true,
        'data' => [
            'period' => $period,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'budgets' => $budgets
        ]
    ]);
}

function getArabicMonthName($month) {
    $months = [
        1 => 'يناير', 2 => 'فبراير', 3 => 'مارس', 4 => 'أبريل',
        5 => 'مايو', 6 => 'يونيو', 7 => 'يوليو', 8 => 'أغسطس',
        9 => 'سبتمبر', 10 => 'أكتوبر', 11 => 'نوفمبر', 12 => 'ديسمبر'
    ];
    return $months[$month] ?? '';
}
