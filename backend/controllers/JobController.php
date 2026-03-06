<?php
/**
 * Job Controller - FINAL FIXED VERSION
 * Simple, working salary filter + keyword search
 */
ob_start();
require_once __DIR__ . '/../config/database.php';

class JobController {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    /** CREATE JOB LISTING */
    public function createJob($data, $employerId) {
        try {
            $required = ['job_title','job_description','job_type','location'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    return ['success'=>false,'error'=>"$field is required"];
                }
            }
            $sql = "INSERT INTO job_listings 
                    (employer_id, job_title, job_description, job_type, location, salary_min, salary_max, qualifications, responsibilities, is_active)
                    VALUES (:employer_id,:job_title,:job_description,:job_type,:location,:salary_min,:salary_max,:qualifications,:responsibilities,1)";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                ':employer_id'      => $employerId,
                ':job_title'        => trim($data['job_title']),
                ':job_description'  => trim($data['job_description']),
                ':job_type'         => $data['job_type'],
                ':location'         => trim($data['location']),
                ':salary_min'       => !empty($data['salary_min']) ? $data['salary_min'] : null,
                ':salary_max'       => !empty($data['salary_max']) ? $data['salary_max'] : null,
                ':qualifications'   => !empty($data['qualifications']) ? trim($data['qualifications']) : null,
                ':responsibilities' => !empty($data['responsibilities']) ? trim($data['responsibilities']) : null,
            ]);
            return ['success'=>true,'message'=>'Job posted successfully','job_id'=>$this->conn->lastInsertId()];
        } catch (Exception $e) {
            error_log("createJob error: ".$e->getMessage());
            return ['success'=>false,'error'=>'Failed to create job'];
        }
    }

    /** GET ALL ACTIVE JOBS (with optional search/filter) */
    public function getJobs($filters=[]) {
        try {
            $where = ['jl.is_active = 1'];
            $params = [];

            // Keyword search
            if (!empty($filters['search'])) {
                $where[] = "(jl.job_title LIKE :search1 OR jl.job_description LIKE :search2 OR jl.location LIKE :search3)";
                $searchTerm = '%'.$filters['search'].'%';
                $params[':search1'] = $searchTerm;
                $params[':search2'] = $searchTerm;
                $params[':search3'] = $searchTerm;
            }
            
            // Job type filter
            if (!empty($filters['job_type'])) {
                $where[] = "jl.job_type = :job_type";
                $params[':job_type'] = $filters['job_type'];
            }
            
            // Location filter
            if (!empty($filters['location'])) {
                $where[] = "jl.location LIKE :location";
                $params[':location'] = '%'.$filters['location'].'%';
            }
            
            // Min salary filter
            // Logic: Show jobs where the MAX salary is at least what user wants
            // User wants ₹60,000 minimum → Show jobs that pay UP TO ₹60,000 or more
            if (!empty($filters['salary_min'])) {
                $salaryMin = (int)$filters['salary_min'];
                // Show jobs where salary_max >= user's requirement OR salary_min >= user's requirement
                // But handle NULL properly
                $where[] = "((jl.salary_max IS NOT NULL AND jl.salary_max >= :salary_val) OR (jl.salary_min IS NOT NULL AND jl.salary_min >= :salary_val2))";
                $params[':salary_val'] = $salaryMin;
                $params[':salary_val2'] = $salaryMin;
            }

            $whereStr = implode(' AND ', $where);
            $sql = "SELECT jl.*, ep.company_name, u.full_name as employer_name
                    FROM job_listings jl
                    JOIN users u ON jl.employer_id = u.user_id
                    LEFT JOIN employer_profiles ep ON jl.employer_id = ep.user_id
                    WHERE $whereStr
                    ORDER BY jl.created_at DESC";

            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            $jobs = $stmt->fetchAll();
            
            return ['success'=>true,'jobs'=>$jobs,'count'=>count($jobs)];
            
        } catch (Exception $e) {
            error_log("getJobs error: ".$e->getMessage());
            // Return the actual error so we can debug
            return ['success'=>false,'error'=>'Database error: ' . $e->getMessage()];
        }
    }

    /** GET SINGLE JOB */
    public function getJob($jobId) {
        try {
            $sql = "SELECT jl.*, ep.company_name, ep.company_description, ep.company_website,
                           ep.industry, u.full_name as employer_name
                    FROM job_listings jl
                    JOIN users u ON jl.employer_id = u.user_id
                    LEFT JOIN employer_profiles ep ON jl.employer_id = ep.user_id
                    WHERE jl.job_id = :job_id AND jl.is_active = 1";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':job_id'=>$jobId]);
            $job = $stmt->fetch();
            if (!$job) return ['success'=>false,'error'=>'Job not found'];

            // Get application count
            $countSql = "SELECT COUNT(*) as app_count FROM job_applications WHERE job_id = :job_id";
            $countStmt = $this->conn->prepare($countSql);
            $countStmt->execute([':job_id'=>$jobId]);
            $job['application_count'] = $countStmt->fetch()['app_count'];

            return ['success'=>true,'job'=>$job];
        } catch (Exception $e) {
            error_log("getJob error: ".$e->getMessage());
            return ['success'=>false,'error'=>'Failed to fetch job'];
        }
    }

    /** GET EMPLOYER'S JOBS */
    public function getEmployerJobs($employerId) {
        try {
            $sql = "SELECT jl.*,
                    (SELECT COUNT(*) FROM job_applications ja WHERE ja.job_id = jl.job_id) as application_count
                    FROM job_listings jl
                    WHERE jl.employer_id = :employer_id
                    ORDER BY jl.created_at DESC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':employer_id'=>$employerId]);
            $jobs = $stmt->fetchAll();
            return ['success'=>true,'jobs'=>$jobs];
        } catch (Exception $e) {
            error_log("getEmployerJobs error: ".$e->getMessage());
            return ['success'=>false,'error'=>'Failed to fetch jobs'];
        }
    }

    /** UPDATE JOB */
    public function updateJob($jobId, $data, $employerId) {
        try {
            // verify ownership
            $check = $this->conn->prepare("SELECT employer_id FROM job_listings WHERE job_id=:id");
            $check->execute([':id'=>$jobId]);
            $job = $check->fetch();
            if (!$job || $job['employer_id'] != $employerId)
                return ['success'=>false,'error'=>'Unauthorized'];

            $sql = "UPDATE job_listings SET
                    job_title=:job_title, job_description=:job_description, job_type=:job_type,
                    location=:location, salary_min=:salary_min, salary_max=:salary_max,
                    qualifications=:qualifications, responsibilities=:responsibilities,
                    is_active=:is_active, updated_at=NOW()
                    WHERE job_id=:job_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                ':job_title'        => trim($data['job_title']),
                ':job_description'  => trim($data['job_description']),
                ':job_type'         => $data['job_type'],
                ':location'         => trim($data['location']),
                ':salary_min'       => !empty($data['salary_min']) ? $data['salary_min'] : null,
                ':salary_max'       => !empty($data['salary_max']) ? $data['salary_max'] : null,
                ':qualifications'   => !empty($data['qualifications']) ? trim($data['qualifications']) : null,
                ':responsibilities' => !empty($data['responsibilities']) ? trim($data['responsibilities']) : null,
                ':is_active'        => isset($data['is_active']) ? (int)$data['is_active'] : 1,
                ':job_id'           => $jobId,
            ]);
            return ['success'=>true,'message'=>'Job updated successfully'];
        } catch (Exception $e) {
            error_log("updateJob error: ".$e->getMessage());
            return ['success'=>false,'error'=>'Failed to update job'];
        }
    }

    /** DELETE JOB */
    public function deleteJob($jobId, $employerId) {
        try {
            $check = $this->conn->prepare("SELECT employer_id FROM job_listings WHERE job_id=:id");
            $check->execute([':id'=>$jobId]);
            $job = $check->fetch();
            if (!$job || $job['employer_id'] != $employerId)
                return ['success'=>false,'error'=>'Unauthorized'];

            $stmt = $this->conn->prepare("UPDATE job_listings SET is_active=0 WHERE job_id=:job_id");
            $stmt->execute([':job_id'=>$jobId]);
            return ['success'=>true,'message'=>'Job deleted successfully'];
        } catch (Exception $e) {
            error_log("deleteJob error: ".$e->getMessage());
            return ['success'=>false,'error'=>'Failed to delete job'];
        }
    }
}
?>