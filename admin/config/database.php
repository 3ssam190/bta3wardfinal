<?php
class Database {
    private static $instance = null;
    private $pdo;
    
    private function __construct() {
        // Load environment variables
        $this->loadEnv();
        
        try {
            $this->pdo = new PDO(
                'mysql:host=' . getenv('DB_HOST') . ';dbname=' . getenv('DB_NAME'),
                getenv('DB_USER'),
                getenv('DB_PASS'),
                [
                    PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4',
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::ATTR_PERSISTENT => false
                ]
            );
        } catch (PDOException $e) {
            // Log error to file
            error_log(date('[Y-m-d H:i:s] ') . 'Database Error: ' . $e->getMessage() . "\n", 3, __DIR__ . '/database_errors.log');
            
            // Display user-friendly message
            if (strpos($_SERVER['REQUEST_URI'], '/admin/') !== false) {
                die("Database connection error. Please check the admin configuration.");
            } else {
                die("We're experiencing technical difficulties. Please try again later.");
            }
        }
    }
    
    private function loadEnv() {
        // Check if .env file exists
        $envPath = __DIR__ . '/.env';
        if (!file_exists($envPath)) {
            throw new RuntimeException('.env file not found');
        }
        
        // Read .env file
        $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            // Skip comments
            if (strpos(trim($line), '#') === 0) {
                continue;
            }
            
            // Split name and value
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            
            // Remove quotes if present
            if (preg_match('/^"(.*)"$/', $value, $matches)) {
                $value = $matches[1];
            } elseif (preg_match('/^\'(.*)\'$/', $value, $matches)) {
                $value = $matches[1];
            }
            
            // Set environment variable if not already set
            if (!array_key_exists($name, $_ENV)) {
                putenv("$name=$value");
                $_ENV[$name] = $value;
                $_SERVER[$name] = $value;
            }
        }
    }
    
    public static function connect() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance->pdo;
    }
    
    // Prevent cloning and serialization
    private function __clone() {}
    public function __wakeup() {
        throw new Exception("Cannot unserialize a singleton.");
    }
}