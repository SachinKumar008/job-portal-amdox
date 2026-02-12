<?php


// Option 4: Different port
 define('DB_HOST', 'localhost:3307');  // or 3308
define('DB_USER', 'root');
 define('DB_PASS', '');
 define('DB_NAME', 'job_portal');

/**
 * Database Connection Class
 */
class Database {
    private $host = DB_HOST;
    private $user = DB_USER;
    private $pass = DB_PASS;
    private $dbname = DB_NAME;
    private $conn;

    public function __construct() {
        $this->connect();
    }

    private function connect() {
        $this->conn = null;
        
        try {
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->dbname . ";charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $this->conn = new PDO($dsn, $this->user, $this->pass, $options);
            
        } catch(PDOException $e) {
            // Log the actual error for debugging
            error_log("Database connection error: " . $e->getMessage());
            
            // Throw generic error to user
            throw new Exception("Database connection failed");
        }
    }

    public function getConnection() {
        return $this->conn;
    }
}
?>