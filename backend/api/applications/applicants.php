<?php
/**
 * Get Applicants for a Job (Employer only)
 * No email notifications
 */
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../controllers/ApplicationController.php';

session_start();

// Check if logged in as employer
if (!isset($_SESSION['user']) || $_SESSION['user']['user_type'] !== 'employer') {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized - Employer access only']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$jobId = isset($_GET['job_id']) ? intval($_GET['job_id']) : 0;

if (!$jobId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Job ID required']);
    exit;
}

$controller = new ApplicationController();
$result = $controller->getApplicants($jobId, $_SESSION['user']['user_id']);

echo json_encode($result);
?>