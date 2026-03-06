<?php
/**
 * Application Controller
 * Email notifications handled by frontend EmailJS
 */
ob_start();
require_once __DIR__ . '/../config/database.php';

class ApplicationController {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    /** APPLY FOR JOB */
    public function applyForJob($jobId, $jobSeekerId, $data) {
        try {
            $check = $this->conn->prepare(
                "SELECT application_id FROM job_applications WHERE job_id=:job_id AND job_seeker_id=:user_id"
            );
            $check->execute([':job_id'=>$jobId,':user_id'=>$jobSeekerId]);
            if ($check->rowCount() > 0)
                return ['success'=>false,'error'=>'You have already applied for this job'];

            $jobCheck = $this->conn->prepare("SELECT job_id FROM job_listings WHERE job_id=:job_id AND is_active=1");
            $jobCheck->execute([':job_id'=>$jobId]);
            if ($jobCheck->rowCount() === 0)
                return ['success'=>false,'error'=>'Job not found or no longer active'];

            $sql = "INSERT INTO job_applications (job_id, job_seeker_id, cover_letter, status)
                    VALUES (:job_id, :job_seeker_id, :cover_letter, 'pending')";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                ':job_id'       => $jobId,
                ':job_seeker_id'=> $jobSeekerId,
                ':cover_letter' => !empty($data['cover_letter']) ? trim($data['cover_letter']) : null,
            ]);
            
            return ['success'=>true,'message'=>'Application submitted successfully!'];
        } catch (Exception $e) {
            error_log("applyForJob error: ".$e->getMessage());
            return ['success'=>false,'error'=>'Failed to submit application'];
        }
    }

    /** GET JOB SEEKER'S APPLICATIONS */
    public function getMyApplications($jobSeekerId) {
        try {
            $sql = "SELECT ja.*, jl.job_title, jl.location, ep.company_name, u.full_name as employer_name
                    FROM job_applications ja
                    JOIN job_listings jl ON ja.job_id = jl.job_id
                    JOIN users u ON jl.employer_id = u.user_id
                    LEFT JOIN employer_profiles ep ON jl.employer_id = ep.user_id
                    WHERE ja.job_seeker_id = :user_id
                    ORDER BY ja.applied_at DESC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':user_id'=>$jobSeekerId]);
            $apps = $stmt->fetchAll();
            return ['success'=>true,'applications'=>$apps];
        } catch (Exception $e) {
            error_log("getMyApplications error: ".$e->getMessage());
            return ['success'=>false,'error'=>'Failed to fetch applications'];
        }
    }

    /** GET APPLICANTS FOR A JOB */
    public function getApplicants($jobId, $employerId) {
        try {
            $check = $this->conn->prepare("SELECT employer_id FROM job_listings WHERE job_id=:job_id");
            $check->execute([':job_id'=>$jobId]);
            $job = $check->fetch();
            if (!$job || $job['employer_id'] != $employerId)
                return ['success'=>false,'error'=>'Unauthorized'];

            $sql = "SELECT ja.*, u.full_name, u.email, u.phone,
                           jsp.location, jsp.education, jsp.experience_years, jsp.skills, jsp.resume_path
                    FROM job_applications ja
                    JOIN users u ON ja.job_seeker_id = u.user_id
                    LEFT JOIN job_seeker_profiles jsp ON ja.job_seeker_id = jsp.user_id
                    WHERE ja.job_id = :job_id
                    ORDER BY ja.applied_at DESC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':job_id'=>$jobId]);
            $applicants = $stmt->fetchAll();
            return ['success'=>true,'applicants'=>$applicants];
        } catch (Exception $e) {
            error_log("getApplicants error: ".$e->getMessage());
            return ['success'=>false,'error'=>'Failed to fetch applicants'];
        }
    }

    /** UPDATE APPLICATION STATUS */
    public function updateStatus($applicationId, $status, $employerId) {
        try {
            $validStatuses = ['pending','reviewing','accepted','rejected'];
            if (!in_array($status, $validStatuses))
                return ['success'=>false,'error'=>'Invalid status'];

            $check = $this->conn->prepare(
                "SELECT jl.employer_id FROM job_applications ja
                 JOIN job_listings jl ON ja.job_id = jl.job_id
                 WHERE ja.application_id = :app_id"
            );
            $check->execute([':app_id'=>$applicationId]);
            $row = $check->fetch();
            if (!$row || $row['employer_id'] != $employerId)
                return ['success'=>false,'error'=>'Unauthorized'];

            $stmt = $this->conn->prepare(
                "UPDATE job_applications SET status=:status, updated_at=NOW() WHERE application_id=:id"
            );
            $stmt->execute([':status'=>$status,':id'=>$applicationId]);
            
            return ['success'=>true,'message'=>'Status updated successfully'];
        } catch (Exception $e) {
            error_log("updateStatus error: ".$e->getMessage());
            return ['success'=>false,'error'=>'Failed to update status'];
        }
    }

    /** CHECK IF ALREADY APPLIED */
    public function hasApplied($jobId, $jobSeekerId) {
        try {
            $stmt = $this->conn->prepare(
                "SELECT application_id, status FROM job_applications WHERE job_id=:job_id AND job_seeker_id=:user_id"
            );
            $stmt->execute([':job_id'=>$jobId,':user_id'=>$jobSeekerId]);
            $result = $stmt->fetch();
            return ['success'=>true,'applied'=>(bool)$result,'status'=>$result['status'] ?? null];
        } catch (Exception $e) {
            return ['success'=>true,'applied'=>false,'status'=>null];
        }
    }
}
?>