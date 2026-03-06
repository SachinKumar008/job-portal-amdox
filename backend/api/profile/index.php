<?php
/**
 * Profile API
 * GET   /api/profile/index.php          → get my profile
 * PUT   /api/profile/index.php          → update my profile
 * GET   /api/profile/index.php?stats=1  → get dashboard stats
 */

require_once __DIR__ . '/../helper.php';
require_once __DIR__ . '/../../controllers/ProfileController.php';

$method     = $_SERVER['REQUEST_METHOD'];
$controller = new ProfileController();
$user       = requireAuth();

try {
    if ($method === 'GET') {

        if (isset($_GET['stats'])) {
            $result = $controller->getDashboardStats($user['user_id'], $user['user_type']);
            jsonResponse($result);
        }

        if ($user['user_type'] === 'employer') {
            $result = $controller->getEmployerProfile($user['user_id']);
        } else {
            $result = $controller->getJobSeekerProfile($user['user_id']);
        }
        jsonResponse($result);

    } elseif ($method === 'PUT') {
        $data = getBody();

        if ($user['user_type'] === 'employer') {
            $result = $controller->updateEmployerProfile($user['user_id'], $data);
        } else {
            $result = $controller->updateJobSeekerProfile($user['user_id'], $data);
        }
        jsonResponse($result, $result['success'] ? 200 : 400);

    } else {
        jsonResponse(['success'=>false,'error'=>'Method not allowed'], 405);
    }

} catch (Exception $e) {
    error_log("Profile API error: " . $e->getMessage());
    jsonResponse(['success'=>false,'error'=>'Server error'], 500);
}
?>