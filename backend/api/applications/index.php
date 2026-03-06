<?php
/**
 * Applications API
 * POST   /api/applications/index.php?job_id=X    → apply for job
 * GET    /api/applications/index.php             → my applications (job seeker)
 * GET    /api/applications/index.php?job_id=X   → job applicants (employer)
 * GET    /api/applications/index.php?check=X    → check if applied
 * PUT    /api/applications/index.php?id=X       → update status (employer)
 */

require_once __DIR__ . '/../helper.php';
require_once __DIR__ . '/../../controllers/ApplicationController.php';

$method     = $_SERVER['REQUEST_METHOD'];
$controller = new ApplicationController();
$user       = requireAuth();

try {
    if ($method === 'GET') {

        // Check if already applied
        if (isset($_GET['check'])) {
            $jobId  = (int)$_GET['check'];
            $result = $controller->hasApplied($jobId, $user['user_id']);
            jsonResponse($result);
        }

        // Employer - get applicants for a job
        if (isset($_GET['job_id']) && $user['user_type'] === 'employer') {
            $result = $controller->getApplicants((int)$_GET['job_id'], $user['user_id']);
            jsonResponse($result);
        }

        // Job seeker - get my applications
        $result = $controller->getMyApplications($user['user_id']);
        jsonResponse($result);

    } elseif ($method === 'POST') {
        if ($user['user_type'] !== 'job_seeker')
            jsonResponse(['success'=>false,'error'=>'Only job seekers can apply'], 403);

        $jobId  = isset($_GET['job_id']) ? (int)$_GET['job_id'] : 0;
        if (!$jobId) jsonResponse(['success'=>false,'error'=>'Job ID is required'], 400);

        $data   = getBody();
        $result = $controller->applyForJob($jobId, $user['user_id'], $data);
        jsonResponse($result, $result['success'] ? 201 : 400);

    } elseif ($method === 'PUT') {
        if ($user['user_type'] !== 'employer')
            jsonResponse(['success'=>false,'error'=>'Only employers can update status'], 403);

        $appId  = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $data   = getBody();
        $result = $controller->updateStatus($appId, $data['status'] ?? '', $user['user_id']);
        jsonResponse($result, $result['success'] ? 200 : 400);

    } else {
        jsonResponse(['success'=>false,'error'=>'Method not allowed'], 405);
    }

} catch (Exception $e) {
    error_log("Applications API error: " . $e->getMessage());
    jsonResponse(['success'=>false,'error'=>'Server error'], 500);
}
?>