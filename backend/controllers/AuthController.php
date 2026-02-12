<?php
/**
 * Authentication Controller - COMPLETE VERIFIED VERSION
 */

require_once __DIR__ . '/../config/database.php';

class AuthController {
    private $db;
    private $conn;

    public function __construct() {
        try {
            $this->db = new Database();
            $this->conn = $this->db->getConnection();
        } catch (Exception $e) {
            error_log("Database connection error: " . $e->getMessage());
            throw new Exception("Database connection failed");
        }
    }

    /**
     * REGISTER NEW USER
     */
    public function register($data) {
        try {
            // Validate input
            $errors = $this->validateRegistration($data);
            if (!empty($errors)) {
                return ['success' => false, 'errors' => $errors];
            }

            // Check if email exists
            if ($this->emailExists($data['email'])) {
                return ['success' => false, 'errors' => ['email' => 'Email already registered']];
            }

            // Hash password
            $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

            // Insert user
            $sql = "INSERT INTO users (email, password, user_type, full_name, phone) 
                    VALUES (:email, :password, :user_type, :full_name, :phone)";
            
            $stmt = $this->conn->prepare($sql);
            $phone = isset($data['phone']) && !empty($data['phone']) ? $data['phone'] : null;
            
            $stmt->execute([
                ':email' => $data['email'],
                ':password' => $hashedPassword,
                ':user_type' => $data['user_type'],
                ':full_name' => $data['full_name'],
                ':phone' => $phone
            ]);

            $userId = $this->conn->lastInsertId();

            // Create profile
            if ($data['user_type'] === 'job_seeker') {
                $this->createJobSeekerProfile($userId);
            } else {
                $companyName = isset($data['company_name']) && !empty($data['company_name']) 
                    ? $data['company_name'] 
                    : 'Not Specified';
                $this->createEmployerProfile($userId, $companyName);
            }

            return [
                'success' => true,
                'message' => 'Registration successful',
                'user_id' => $userId
            ];

        } catch (PDOException $e) {
            error_log("Registration PDO error: " . $e->getMessage());
            return ['success' => false, 'errors' => ['general' => 'Database error occurred']];
        } catch (Exception $e) {
            error_log("Registration error: " . $e->getMessage());
            return ['success' => false, 'errors' => ['general' => 'Registration failed']];
        }
    }

    /**
     * LOGIN USER
     */
    public function login($email, $password) {
        try {
            $sql = "SELECT user_id, email, password, user_type, full_name, is_active 
                    FROM users WHERE email = :email";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch();

            if (!$user) {
                return ['success' => false, 'error' => 'Invalid email or password'];
            }

            if (!$user['is_active']) {
                return ['success' => false, 'error' => 'Account is deactivated'];
            }

            if (!password_verify($password, $user['password'])) {
                return ['success' => false, 'error' => 'Invalid email or password'];
            }

            // Update last login
            $updateSql = "UPDATE users SET last_login = NOW() WHERE user_id = :user_id";
            $updateStmt = $this->conn->prepare($updateSql);
            $updateStmt->execute([':user_id' => $user['user_id']]);

            // Start session
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['user_type'] = $user['user_type'];
            $_SESSION['full_name'] = $user['full_name'];

            return [
                'success' => true,
                'message' => 'Login successful',
                'user' => [
                    'user_id' => $user['user_id'],
                    'email' => $user['email'],
                    'user_type' => $user['user_type'],
                    'full_name' => $user['full_name']
                ]
            ];

        } catch (PDOException $e) {
            error_log("Login PDO error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Database error occurred'];
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Login failed'];
        }
    }

    /**
     * LOGOUT USER
     */
    public function logout() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_unset();
        session_destroy();
        return ['success' => true, 'message' => 'Logged out successfully'];
    }

    /**
     * VALIDATE REGISTRATION DATA
     */
    private function validateRegistration($data) {
        $errors = [];

        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Valid email is required';
        }

        if (empty($data['password']) || strlen($data['password']) < 8) {
            $errors['password'] = 'Password must be at least 8 characters';
        }

        if (empty($data['full_name']) || strlen($data['full_name']) < 3) {
            $errors['full_name'] = 'Full name must be at least 3 characters';
        }

        if (empty($data['user_type']) || !in_array($data['user_type'], ['job_seeker', 'employer'])) {
            $errors['user_type'] = 'Valid user type is required';
        }

        // Validate company name for employers
        if (isset($data['user_type']) && $data['user_type'] === 'employer') {
            if (empty($data['company_name']) || strlen(trim($data['company_name'])) < 2) {
                $errors['company_name'] = 'Company name is required for employers';
            }
        }

        return $errors;
    }

    /**
     * CHECK IF EMAIL EXISTS
     */
    private function emailExists($email) {
        $sql = "SELECT user_id FROM users WHERE email = :email";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':email' => $email]);
        return $stmt->rowCount() > 0;
    }

    /**
     * CREATE JOB SEEKER PROFILE
     */
    private function createJobSeekerProfile($userId) {
        $sql = "INSERT INTO job_seeker_profiles (user_id) VALUES (:user_id)";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
    }

    /**
     * CREATE EMPLOYER PROFILE
     */
    private function createEmployerProfile($userId, $companyName) {
        $sql = "INSERT INTO employer_profiles (user_id, company_name) VALUES (:user_id, :company_name)";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':user_id' => $userId, ':company_name' => $companyName]);
    }
}
?>