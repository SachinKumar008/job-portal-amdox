<?php
/**
 * API Helper - FIXED VERSION
 * Reads auth from request headers (X-User-Id, X-User-Type, X-User-Name)
 * instead of PHP sessions — works on both XAMPP and PHP built-in server
 */

ob_start();

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Accept, X-User-Id, X-User-Type, X-User-Name');
header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    ob_end_clean();
    http_response_code(200);
    exit();
}

/**
 * Send JSON response and exit
 */
function jsonResponse($data, $code = 200) {
    ob_end_clean();
    http_response_code($code);
    echo json_encode($data);
    exit;
}

/**
 * Get JSON request body
 */
function getBody() {
    $input = file_get_contents('php://input');
    $data  = json_decode($input, true);
    if (empty($data)) $data = $_POST;
    return $data ?? [];
}

/**
 * Get auth user from request headers (sent by api.js)
 * Returns user array or null
 */
function getAuthUser() {
    // Try apache-style headers first
    $userId   = $_SERVER['HTTP_X_USER_ID']   ?? null;
    $userType = $_SERVER['HTTP_X_USER_TYPE'] ?? null;
    $userName = $_SERVER['HTTP_X_USER_NAME'] ?? null;

    // Some servers use different key format
    if (!$userId) {
        $userId   = $_SERVER['HTTP_X-USER-ID']   ?? null;
        $userType = $_SERVER['HTTP_X-USER-TYPE'] ?? null;
        $userName = $_SERVER['HTTP_X-USER-NAME'] ?? null;
    }

    // Also check getallheaders() which works on Apache/XAMPP
    if (!$userId && function_exists('getallheaders')) {
        $headers = getallheaders();
        // headers are case-insensitive — check both
        foreach ($headers as $k => $v) {
            $kl = strtolower($k);
            if ($kl === 'x-user-id')   $userId   = $v;
            if ($kl === 'x-user-type') $userType = $v;
            if ($kl === 'x-user-name') $userName = $v;
        }
    }

    if (!$userId || !$userType) return null;

    return [
        'user_id'   => (int)$userId,
        'user_type' => $userType,
        'full_name' => $userName ?? '',
    ];
}

/**
 * Require authentication — returns user array or sends 401
 */
function requireAuth() {
    $user = getAuthUser();
    if (!$user || !$user['user_id']) {
        jsonResponse(['success' => false, 'error' => 'Unauthorized. Please login.'], 401);
    }
    return $user;
}

/**
 * Require a specific user type
 */
function requireRole($role) {
    $user = requireAuth();
    if ($user['user_type'] !== $role) {
        jsonResponse(['success' => false, 'error' => "Access denied. {$role} only."], 403);
    }
    return $user;
}
?>