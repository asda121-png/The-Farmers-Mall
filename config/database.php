<?php
/**
 * Supabase Database Configuration
 * 
 * This file handles the connection to Supabase PostgreSQL database.
 * All team members will connect to the same cloud database.
 */

// Load environment variables
require_once __DIR__ . '/env.php';

class Database {
    private static $instance = null;
    private $conn;
    
    private $host;
    private $port;
    private $database;
    private $username;
    private $password;
    
    private function __construct() {
        // Get Supabase credentials from environment
        $this->host = getenv('SUPABASE_DB_HOST');
        $this->port = getenv('SUPABASE_DB_PORT') ?: '5432';
        $this->database = getenv('SUPABASE_DB_NAME');
        $this->username = getenv('SUPABASE_DB_USER');
        $this->password = getenv('SUPABASE_DB_PASSWORD');
        
        $this->connect();
    }
    
    private function connect() {
        try {
            // PostgreSQL connection string for Supabase
            $dsn = "pgsql:host={$this->host};port={$this->port};dbname={$this->database};sslmode=require";
            
            $this->conn = new PDO($dsn, $this->username, $this->password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_PERSISTENT => false
            ]);
            
        } catch(PDOException $e) {
            error_log("Connection Error: " . $e->getMessage());
            die("Database connection failed. Please check your Supabase credentials.");
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->conn;
    }
    
    // Prevent cloning of the instance
    private function __clone() {}
    
    // Prevent unserialization
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}

// Helper function for quick access
function getDB() {
    return Database::getInstance()->getConnection();
}
