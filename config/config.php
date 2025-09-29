<?php
/**
 * Main Configuration File
 * نظام إدارة المصروفات - ملف الإعدادات الرئيسي
 */

// Prevent direct access
if (!defined('APP_INIT')) {
    define('APP_INIT', true);
}

// =============================================================================
// SITE CONFIGURATION
// =============================================================================

// Site URL (without trailing slash)
define('SITE_URL', 'http://localhost/pro-exp');

// Site Information
define('SITE_NAME', 'نظام إدارة المصروفات');
define('SITE_NAME_EN', 'Expenses Management System');
define('SITE_DESCRIPTION', 'نظام شامل لإدارة المصروفات والميزانيات');
define('SITE_KEYWORDS', 'إدارة المصروفات, الميزانية, المحاسبة, التقارير المالية');
define('SITE_AUTHOR', 'Expenses Management Team');
define('SITE_VERSION', '1.0.0');

// =============================================================================
// DATABASE CONFIGURATION
// =============================================================================

define('DB_HOST', 'localhost');
define('DB_NAME', 'exp');
define('DB_USER', 'root');
define('DB_PASS', '123');
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATE', 'utf8mb4_unicode_ci');

// Database connection options
define('DB_OPTIONS', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
]);

// =============================================================================
// SECURITY CONFIGURATION
// =============================================================================

// Session Configuration
define('SESSION_NAME', 'expenses_session');
define('SESSION_LIFETIME', 86400); // 24 hours in seconds
define('SESSION_COOKIE_HTTPONLY', true);
define('SESSION_COOKIE_SECURE', false); // Set to true for HTTPS
define('SESSION_COOKIE_SAMESITE', 'Strict');

// Password Configuration
define('PASSWORD_MIN_LENGTH', 6);
define('PASSWORD_HASH_ALGO', PASSWORD_DEFAULT);
define('PASSWORD_HASH_COST', 12);

// CSRF Protection
define('CSRF_TOKEN_NAME', 'csrf_token');
define('CSRF_TOKEN_LIFETIME', 3600); // 1 hour

// File Upload Security
define('MAX_FILE_SIZE', 5242880); // 5MB in bytes
define('ALLOWED_FILE_TYPES', ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx']);
define('UPLOAD_PATH', 'uploads/');

// =============================================================================
// APPLICATION CONFIGURATION
// =============================================================================

// Default Language and Locale
define('DEFAULT_LANGUAGE', 'ar');
define('DEFAULT_LOCALE', 'ar_EG');
define('DEFAULT_TIMEZONE', 'Africa/Cairo');

// Currency Settings
define('DEFAULT_CURRENCY', 'EGP');
define('CURRENCY_SYMBOL', 'LE');
define('CURRENCY_POSITION', 'before'); // 'before' or 'after'
define('DECIMAL_PLACES', 2);
define('THOUSANDS_SEPARATOR', ',');
define('DECIMAL_SEPARATOR', '.');

// Date and Time Formats
define('DATE_FORMAT', 'Y-m-d');
define('TIME_FORMAT', 'H:i:s');
define('DATETIME_FORMAT', 'Y-m-d H:i:s');
define('DISPLAY_DATE_FORMAT', 'd/m/Y');
define('DISPLAY_DATETIME_FORMAT', 'd/m/Y H:i');

// Pagination
define('DEFAULT_PAGE_SIZE', 20);
define('MAX_PAGE_SIZE', 100);

// =============================================================================
// EMAIL CONFIGURATION
// =============================================================================

define('MAIL_HOST', 'localhost');
define('MAIL_PORT', 587);
define('MAIL_USERNAME', '');
define('MAIL_PASSWORD', '');
define('MAIL_ENCRYPTION', 'tls'); // 'tls' or 'ssl'
define('MAIL_FROM_ADDRESS', 'noreply@expenses.local');
define('MAIL_FROM_NAME', SITE_NAME);

// =============================================================================
// API CONFIGURATION
// =============================================================================

define('API_VERSION', 'v1');
define('API_RATE_LIMIT', 1000); // Requests per hour
define('API_TIMEOUT', 30); // Seconds

// =============================================================================
// CACHE CONFIGURATION
// =============================================================================

define('CACHE_ENABLED', true);
define('CACHE_LIFETIME', 3600); // 1 hour
define('CACHE_PATH', 'cache/');

// =============================================================================
// LOG CONFIGURATION
// =============================================================================

define('LOG_ENABLED', true);
define('LOG_LEVEL', 'INFO'); // DEBUG, INFO, WARNING, ERROR
define('LOG_PATH', 'logs/');
define('LOG_MAX_SIZE', 10485760); // 10MB
define('LOG_MAX_FILES', 5);

// =============================================================================
// DEVELOPMENT CONFIGURATION
// =============================================================================

// Environment (development, staging, production)
define('ENVIRONMENT', 'development');

// Debug Mode
define('DEBUG_MODE', ENVIRONMENT === 'development');
define('SHOW_ERRORS', DEBUG_MODE);

// Error Reporting
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
}

// =============================================================================
// FEATURES CONFIGURATION
// =============================================================================

// Feature Flags
define('FEATURE_MULTI_CURRENCY', false);
define('FEATURE_NOTIFICATIONS', true);
define('FEATURE_EXPORT_PDF', true);
define('FEATURE_EXPORT_EXCEL', true);
define('FEATURE_BACKUP', true);
define('FEATURE_TWO_FACTOR_AUTH', false);

// Dashboard Configuration
define('DASHBOARD_REFRESH_INTERVAL', 300000); // 5 minutes in milliseconds
define('RECENT_TRANSACTIONS_LIMIT', 10);
define('CHART_COLORS', [
    '#06B6D4', '#3B82F6', '#10B981', '#8B5CF6', 
    '#F59E0B', '#EF4444', '#EC4899', '#84CC16'
]);

// =============================================================================
// PATHS CONFIGURATION
// =============================================================================

// Directory paths (relative to root)
define('CONFIG_PATH', 'config/');
define('INCLUDES_PATH', 'includes/');
define('API_PATH', 'api/');
define('ASSETS_PATH', 'assets/');
define('JS_PATH', 'js/');
define('CSS_PATH', 'css/');
define('IMAGES_PATH', 'images/');

// =============================================================================
// EXTERNAL SERVICES
// =============================================================================

// CDN URLs
define('TAILWIND_CDN', 'https://cdn.tailwindcss.com');
define('FONTAWESOME_CDN', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css');
define('CHARTJS_CDN', 'https://cdn.jsdelivr.net/npm/chart.js');
define('GOOGLE_FONTS_CDN', 'https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700&display=swap');

// =============================================================================
// BUSINESS RULES
// =============================================================================

// Transaction Rules
define('MAX_TRANSACTION_AMOUNT', 999999.99);
define('MIN_TRANSACTION_AMOUNT', 0.01);
define('TRANSACTION_DESCRIPTION_MAX_LENGTH', 255);

// Budget Rules
define('MAX_BUDGET_AMOUNT', 9999999.99);
define('MIN_BUDGET_AMOUNT', 1.00);
define('BUDGET_PERIODS', ['monthly', 'yearly']);

// User Rules
define('MAX_USERS_PER_ACCOUNT', 100);
define('USERNAME_MIN_LENGTH', 3);
define('USERNAME_MAX_LENGTH', 50);

// =============================================================================
// HELPER FUNCTIONS
// =============================================================================

/**
 * Get configuration value
 */
function config($key, $default = null) {
    return defined($key) ? constant($key) : $default;
}

/**
 * Get site URL with path
 */
function site_url($path = '') {
    return SITE_URL . ($path ? '/' . ltrim($path, '/') : '');
}

/**
 * Get asset URL
 */
function asset_url($path) {
    return site_url(ASSETS_PATH . ltrim($path, '/'));
}

/**
 * Format currency
 */
function format_currency($amount, $currency = null) {
    $currency = $currency ?: DEFAULT_CURRENCY;
    $symbol = CURRENCY_SYMBOL;
    
    $formatted = number_format(
        $amount, 
        DECIMAL_PLACES, 
        DECIMAL_SEPARATOR, 
        THOUSANDS_SEPARATOR
    );
    
    return CURRENCY_POSITION === 'before' 
        ? $symbol . $formatted 
        : $formatted . $symbol;
}

/**
 * Format date for display
 */
function format_date($date, $format = null) {
    $format = $format ?: DISPLAY_DATE_FORMAT;
    
    if (is_string($date)) {
        $date = new DateTime($date);
    }
    
    return $date->format($format);
}

/**
 * Check if feature is enabled
 */
function is_feature_enabled($feature) {
    $constant = 'FEATURE_' . strtoupper($feature);
    return defined($constant) && constant($constant);
}

/**
 * Get environment
 */
function is_development() {
    return ENVIRONMENT === 'development';
}

function is_production() {
    return ENVIRONMENT === 'production';
}

// =============================================================================
// INITIALIZATION
// =============================================================================

// Set timezone
date_default_timezone_set(DEFAULT_TIMEZONE);

// Set locale
if (function_exists('setlocale')) {
    setlocale(LC_ALL, DEFAULT_LOCALE);
}

// Start session with custom configuration
if (!session_id()) {
    session_name(SESSION_NAME);
    session_set_cookie_params([
        'lifetime' => SESSION_LIFETIME,
        'path' => '/',
        'domain' => '',
        'secure' => SESSION_COOKIE_SECURE,
        'httponly' => SESSION_COOKIE_HTTPONLY,
        'samesite' => SESSION_COOKIE_SAMESITE
    ]);
    session_start();
}

// Create necessary directories
$directories = [
    UPLOAD_PATH,
    CACHE_PATH,
    LOG_PATH
];

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

// =============================================================================
// CONSTANTS FOR BACKWARD COMPATIBILITY
// =============================================================================

// Old constants that might be used in existing code
if (!defined('SITE_ROOT')) {
    define('SITE_ROOT', dirname(__DIR__));
}

if (!defined('BASE_URL')) {
    define('BASE_URL', SITE_URL);
}
?>