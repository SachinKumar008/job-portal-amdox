<?php
/**
 * LOGOUT API ENDPOINT
 * URL: /api/auth/logout.php
 */

// CORS Headers - MUST BE FIRST
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only allow POST requests for actual logout
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

require_once __DIR__ . '/../../controllers/AuthController.php';

// Create controller and logout
$authController = new AuthController();
$result = $authController->logout();

http_response_code(200);
echo json_encode($result);
?>