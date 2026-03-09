<?php
class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $port;
    public $conn;

    public function __construct() {
        // Use environment variables from Render
        $this->host = getenv('DB_HOST') ?: 'localhost';
        $this->db_name = getenv('DB_NAME') ?: 'job_portal';
        $this->username = getenv('DB_USER') ?: 'root';
        $this->password = getenv('DB_PASSWORD') ?: '';
        $this->port = getenv('DB_PORT') ?: '3306';
    }

    public function getConnection() {
        $this->conn = null;
        
        try {
            // Check if PostgreSQL or MySQL
            if ($this->port == '5432') {
                // PostgreSQL (Render)
                $dsn = "pgsql:host={$this->host};port={$this->port};dbname={$this->db_name}";
            } else {
                // MySQL (Local)
                $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->db_name}";
            }
            
            $this->conn = new PDO(
                $dsn,
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]
            );
            
            // Set charset based on database type
            if ($this->port == '5432') {
                $this->conn->exec("SET NAMES 'UTF8'");
            } else {
                $this->conn->exec("SET NAMES utf8");
            }
            
        } catch(PDOException $e) {
            error_log("Connection error: " . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Database connection failed']);
            exit;
        }
        
        return $this->conn;
    }
}
?>