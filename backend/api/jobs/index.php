<?php
/**
 * Jobs API
 * GET    /api/jobs/index.php          → all jobs (with ?search=&job_type=&location=)
 * GET    /api/jobs/index.php?id=X     → single job
 * GET    /api/jobs/index.php?mine=1   → employer's own jobs
 * POST   /api/jobs/index.php          → create job (employer)
 * PUT    /api/jobs/index.php?id=X     → update job (employer)
 * DELETE /api/jobs/index.php?id=X     → delete job (employer)
 */

require_once __DIR__ . '/../helper.php';
require_once __DIR__ . '/../../controllers/JobController.php';

$method    = $_SERVER['REQUEST_METHOD'];
$jobId     = isset($_GET['id']) ? (int)$_GET['id'] : null;
$controller = new JobController();

try {
    if ($method === 'GET') {

        // Get employer's own jobs
        if (isset($_GET['mine'])) {
            $user = requireRole('employer');
            $result = $controller->getEmployerJobs($user['user_id']);
            jsonResponse($result);
        }

        // Get single job
        if ($jobId) {
            $result = $controller->getJob($jobId);
            jsonResponse($result);
        }

        // Get all jobs with optional filters
        $filters = [
            'search'     => $_GET['search']     ?? '',
            'job_type'   => $_GET['job_type']   ?? '',
            'location'   => $_GET['location']   ?? '',
            'salary_min' => $_GET['salary_min'] ?? '',
        ];
        $result = $controller->getJobs($filters);
        jsonResponse($result);

    } elseif ($method === 'POST') {
        $user   = requireRole('employer');
        $data   = getBody();
        $result = $controller->createJob($data, $user['user_id']);
        jsonResponse($result, $result['success'] ? 201 : 400);

    } elseif ($method === 'PUT') {
        $user   = requireRole('employer');
        $data   = getBody();
        $result = $controller->updateJob($jobId, $data, $user['user_id']);
        jsonResponse($result, $result['success'] ? 200 : 400);

    } elseif ($method === 'DELETE') {
        $user   = requireRole('employer');
        $result = $controller->deleteJob($jobId, $user['user_id']);
        jsonResponse($result, $result['success'] ? 200 : 400);

    } else {
        jsonResponse(['success'=>false,'error'=>'Method not allowed'], 405);
    }

} catch (Exception $e) {
    error_log("Jobs API error: " . $e->getMessage());
    jsonResponse(['success'=>false,'error'=>'Server error'], 500);
}
?>