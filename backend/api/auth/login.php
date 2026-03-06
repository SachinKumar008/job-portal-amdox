<?php
ob_start();
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Accept, X-User-Id, X-User-Type, X-User-Name');
header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { ob_end_clean(); http_response_code(200); exit(); }
if ($_SERVER['REQUEST_METHOD'] !== 'POST')    { ob_end_clean(); http_response_code(405); echo json_encode(['success'=>false,'error'=>'Method not allowed']); exit; }

try {
    require_once __DIR__ . '/../../controllers/AuthController.php';

    $input = file_get_contents('php://input');
    $data  = json_decode($input, true);
    if (empty($data)) $data = $_POST;

    if (empty($data['email']) || empty($data['password'])) {
        ob_end_clean(); http_response_code(400);
        echo json_encode(['success'=>false,'error'=>'Email and password are required']); exit;
    }

    $auth   = new AuthController();
    $result = $auth->login($data['email'], $data['password']);

    ob_end_clean();
    http_response_code($result['success'] ? 200 : 401);
    echo json_encode($result);

} catch (Exception $e) {
    ob_end_clean(); http_response_code(500);
    echo json_encode(['success'=>false,'error'=>'Server error: '.$e->getMessage()]);
}
?>