<?php
/**
 * REGISTRATION API ENDPOINT - FINAL CLEAN VERSION
 */

// Start output buffering to catch any errors
ob_start();

// CORS Headers - MUST BE FIRST
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Accept');
header('Content-Type: application/json; charset=UTF-8');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    ob_end_clean();
    http_response_code(200);
    exit();
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ob_end_clean();
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

try {
    // Clear any previous output
    ob_clean();
    
    // Include controller
    require_once __DIR__ . '/../../controllers/AuthController.php';
    
    // Get POST data
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    // If JSON is not provided, try form data
    if (empty($data)) {
        $data = $_POST;
    }
    
    // Create controller and register
    $authController = new AuthController();
    $result = $authController->register($data);
    
    // Set response code
    if ($result['success']) {
        http_response_code(201);
    } else {
        http_response_code(400);
    }
    
    // Clear output buffer and send JSON
    ob_end_clean();
    echo json_encode($result);
    exit;
    
} catch (Exception $e) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'errors' => ['general' => 'Server error: ' . $e->getMessage()]
    ]);
    exit;
}
?>