<?php
/**
 * TEST SCRIPT - Verify JobController is working
 * Place in: backend/test-jobs.php
 * Access: http://localhost/job-listing-portal/backend/test-jobs.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/controllers/JobController.php';

echo "<h2>Testing JobController</h2>";

$controller = new JobController();

echo "<h3>Test 1: Get all jobs (no filters)</h3>";
$result1 = $controller->getJobs([]);
echo "<pre>";
print_r($result1);
echo "</pre>";

echo "<h3>Test 2: Search keyword 'developer'</h3>";
$result2 = $controller->getJobs(['search' => 'developer']);
echo "<pre>";
print_r($result2);
echo "</pre>";

echo "<h3>Test 3: Min salary 60000</h3>";
$result3 = $controller->getJobs(['salary_min' => '60000']);
echo "<pre>";
print_r($result3);
echo "</pre>";

echo "<h3>Test 4: Keyword + Salary</h3>";
$result4 = $controller->getJobs(['search' => 'developer', 'salary_min' => '60000']);
echo "<pre>";
print_r($result4);
echo "</pre>";

echo "<h2>Done!</h2>";
?>