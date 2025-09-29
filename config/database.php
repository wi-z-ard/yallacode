<?php
/**
 * Database Configuration and Connection
 * Uses main config file for database settings
 */

// Load main configuration
require_once __DIR__ . '/config.php';

class Database {
    private $conn;

    public function getConnection() {
        if ($this->conn !== null) {
            return $this->conn;
        }
        
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            
            $this->conn = new PDO($dsn, DB_USER, DB_PASS, DB_OPTIONS);
            
            // Set charset and collation
            $this->conn->exec("SET NAMES " . DB_CHARSET . " COLLATE " . DB_COLLATE);
            
            if (DEBUG_MODE) {
                error_log("Database connected successfully to " . DB_NAME);
            }
            
        } catch(PDOException $exception) {
            $error_message = "Database connection failed: " . $exception->getMessage();
            
            if (DEBUG_MODE) {
                error_log($error_message);
                die($error_message);
            } else {
                error_log($error_message);
                die("Database connection failed. Please check your configuration.");
            }
        }
        
        return $this->conn;
    }
    
    /**
     * Close database connection
     */
    public function closeConnection() {
        $this->conn = null;
    }
    
    /**
     * Test database connection
     */
    public function testConnection() {
        try {
            $conn = $this->getConnection();
            $stmt = $conn->query("SELECT 1");
            return $stmt !== false;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Get database info
     */
    public function getDatabaseInfo() {
        try {
            $conn = $this->getConnection();
            $stmt = $conn->query("SELECT VERSION() as version");
            $result = $stmt->fetch();
            
            return [
                'host' => DB_HOST,
                'database' => DB_NAME,
                'version' => $result['version'] ?? 'Unknown',
                'charset' => DB_CHARSET,
                'status' => 'Connected'
            ];
        } catch (Exception $e) {
            return [
                'host' => DB_HOST,
                'database' => DB_NAME,
                'version' => 'Unknown',
                'charset' => DB_CHARSET,
                'status' => 'Error: ' . $e->getMessage()
            ];
        }
    }
}

// Global database connection
$database = new Database();
$pdo = $database->getConnection();

// Helper function for database operations
function db() {
    global $database;
    return $database->getConnection();
}

// Helper function to get database info
function db_info() {
    global $database;
    return $database->getDatabaseInfo();
}
