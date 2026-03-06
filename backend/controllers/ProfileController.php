<?php
/**
 * Profile Controller - Handles user profile management
 */
ob_start();
require_once __DIR__ . '/../config/database.php';

class ProfileController {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    /** GET JOB SEEKER PROFILE */
    public function getJobSeekerProfile($userId) {
        try {
            $sql = "SELECT u.full_name, u.email, u.phone, u.created_at,
                           jsp.skills, jsp.experience_years, jsp.education,
                           jsp.bio, jsp.location, jsp.profile_id
                    FROM users u
                    LEFT JOIN job_seeker_profiles jsp ON u.user_id = jsp.user_id
                    WHERE u.user_id = :user_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':user_id'=>$userId]);
            $profile = $stmt->fetch();
            if (!$profile) return ['success'=>false,'error'=>'Profile not found'];
            return ['success'=>true,'profile'=>$profile];
        } catch (Exception $e) {
            error_log("getJobSeekerProfile error: ".$e->getMessage());
            return ['success'=>false,'error'=>'Failed to fetch profile'];
        }
    }

    /** UPDATE JOB SEEKER PROFILE */
    public function updateJobSeekerProfile($userId, $data) {
        try {
            // Update users table
            $userSql = "UPDATE users SET full_name=:full_name, phone=:phone, updated_at=NOW() WHERE user_id=:user_id";
            $userStmt = $this->conn->prepare($userSql);
            $userStmt->execute([
                ':full_name' => trim($data['full_name']),
                ':phone'     => !empty($data['phone']) ? trim($data['phone']) : null,
                ':user_id'   => $userId,
            ]);

            // Handle resume upload
            $resumePath = null;
            if (!empty($data['resume']) && !empty($data['resume']['data'])) {
                // Save to backend/uploads/resumes/ for XAMPP compatibility
                $uploadDir = __DIR__ . '/../uploads/resumes/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                $ext = pathinfo($data['resume']['name'], PATHINFO_EXTENSION);
                $filename = 'resume_' . $userId . '_' . time() . '.' . $ext;
                $filepath = $uploadDir . $filename;
                
                $fileData = base64_decode($data['resume']['data']);
                if (file_put_contents($filepath, $fileData) !== false) {
                    // Store relative path from project root
                    $resumePath = 'backend/uploads/resumes/' . $filename;
                }
            }

            // Check if profile exists
            $checkStmt = $this->conn->prepare("SELECT profile_id, resume_path FROM job_seeker_profiles WHERE user_id=:user_id");
            $checkStmt->execute([':user_id'=>$userId]);
            $exists = $checkStmt->fetch();

            if ($exists) {
                $profileSql = "UPDATE job_seeker_profiles SET
                               skills=:skills, experience_years=:experience_years,
                               education=:education, bio=:bio, location=:location" .
                               ($resumePath ? ", resume_path=:resume_path" : "") . ",
                               updated_at=NOW()
                               WHERE user_id=:user_id";
            } else {
                $profileSql = "INSERT INTO job_seeker_profiles (user_id,skills,experience_years,education,bio,location" .
                               ($resumePath ? ",resume_path" : "") . ")
                               VALUES (:user_id,:skills,:experience_years,:education,:bio,:location" .
                               ($resumePath ? ",:resume_path" : "") . ")";
            }

            $params = [
                ':user_id'          => $userId,
                ':skills'           => !empty($data['skills']) ? trim($data['skills']) : null,
                ':experience_years' => !empty($data['experience_years']) ? (int)$data['experience_years'] : 0,
                ':education'        => !empty($data['education']) ? trim($data['education']) : null,
                ':bio'              => !empty($data['bio']) ? trim($data['bio']) : null,
                ':location'         => !empty($data['location']) ? trim($data['location']) : null,
            ];
            if ($resumePath) {
                $params[':resume_path'] = $resumePath;
            }

            $profileStmt = $this->conn->prepare($profileSql);
            $profileStmt->execute($params);

            return ['success'=>true,'message'=>'Profile updated successfully'];
        } catch (Exception $e) {
            error_log("updateJobSeekerProfile error: ".$e->getMessage());
            return ['success'=>false,'error'=>'Failed to update profile'];
        }
    }

    /** GET EMPLOYER PROFILE */
    public function getEmployerProfile($userId) {
        try {
            $sql = "SELECT u.full_name, u.email, u.phone, u.created_at,
                           ep.company_name, ep.company_description, ep.company_website,
                           ep.industry, ep.company_address, ep.profile_id
                    FROM users u
                    LEFT JOIN employer_profiles ep ON u.user_id = ep.user_id
                    WHERE u.user_id = :user_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':user_id'=>$userId]);
            $profile = $stmt->fetch();
            if (!$profile) return ['success'=>false,'error'=>'Profile not found'];
            return ['success'=>true,'profile'=>$profile];
        } catch (Exception $e) {
            error_log("getEmployerProfile error: ".$e->getMessage());
            return ['success'=>false,'error'=>'Failed to fetch profile'];
        }
    }

    /** UPDATE EMPLOYER PROFILE */
    public function updateEmployerProfile($userId, $data) {
        try {
            $userSql = "UPDATE users SET full_name=:full_name, phone=:phone, updated_at=NOW() WHERE user_id=:user_id";
            $userStmt = $this->conn->prepare($userSql);
            $userStmt->execute([
                ':full_name' => trim($data['full_name']),
                ':phone'     => !empty($data['phone']) ? trim($data['phone']) : null,
                ':user_id'   => $userId,
            ]);

            $checkStmt = $this->conn->prepare("SELECT profile_id FROM employer_profiles WHERE user_id=:user_id");
            $checkStmt->execute([':user_id'=>$userId]);
            $exists = $checkStmt->fetch();

            if ($exists) {
                $profileSql = "UPDATE employer_profiles SET
                               company_name=:company_name, company_description=:company_description,
                               company_website=:company_website, industry=:industry,
                               company_address=:company_address, updated_at=NOW()
                               WHERE user_id=:user_id";
            } else {
                $profileSql = "INSERT INTO employer_profiles
                               (user_id,company_name,company_description,company_website,industry,company_address)
                               VALUES (:user_id,:company_name,:company_description,:company_website,:industry,:company_address)";
            }

            $profileStmt = $this->conn->prepare($profileSql);
            $profileStmt->execute([
                ':user_id'              => $userId,
                ':company_name'         => trim($data['company_name']),
                ':company_description'  => !empty($data['company_description']) ? trim($data['company_description']) : null,
                ':company_website'      => !empty($data['company_website']) ? trim($data['company_website']) : null,
                ':industry'             => !empty($data['industry']) ? trim($data['industry']) : null,
                ':company_address'      => !empty($data['company_address']) ? trim($data['company_address']) : null,
            ]);

            return ['success'=>true,'message'=>'Company profile updated successfully'];
        } catch (Exception $e) {
            error_log("updateEmployerProfile error: ".$e->getMessage());
            return ['success'=>false,'error'=>'Failed to update profile'];
        }
    }

    /** GET DASHBOARD STATS */
    public function getDashboardStats($userId, $userType) {
        try {
            $stats = [];
            if ($userType === 'job_seeker') {
                $stmt = $this->conn->prepare(
                    "SELECT
                     COUNT(*) as total_applications,
                     SUM(CASE WHEN status='pending' THEN 1 ELSE 0 END) as pending,
                     SUM(CASE WHEN status='reviewing' THEN 1 ELSE 0 END) as reviewing,
                     SUM(CASE WHEN status='accepted' THEN 1 ELSE 0 END) as accepted,
                     SUM(CASE WHEN status='rejected' THEN 1 ELSE 0 END) as rejected
                     FROM job_applications WHERE job_seeker_id=:user_id"
                );
                $stmt->execute([':user_id'=>$userId]);
                $stats = $stmt->fetch();

                // Total active jobs
                $jobStmt = $this->conn->prepare("SELECT COUNT(*) as total_jobs FROM job_listings WHERE is_active=1");
                $jobStmt->execute();
                $stats['total_jobs'] = $jobStmt->fetch()['total_jobs'];

            } else {
                // Employer stats
                $stmt = $this->conn->prepare(
                    "SELECT
                     COUNT(*) as total_jobs,
                     SUM(CASE WHEN is_active=1 THEN 1 ELSE 0 END) as active_jobs
                     FROM job_listings WHERE employer_id=:user_id"
                );
                $stmt->execute([':user_id'=>$userId]);
                $stats = $stmt->fetch();

                $appStmt = $this->conn->prepare(
                    "SELECT COUNT(*) as total_applications
                     FROM job_applications ja
                     JOIN job_listings jl ON ja.job_id = jl.job_id
                     WHERE jl.employer_id=:user_id"
                );
                $appStmt->execute([':user_id'=>$userId]);
                $stats['total_applications'] = $appStmt->fetch()['total_applications'];

                $pendingStmt = $this->conn->prepare(
                    "SELECT COUNT(*) as pending
                     FROM job_applications ja
                     JOIN job_listings jl ON ja.job_id = jl.job_id
                     WHERE jl.employer_id=:user_id AND ja.status='pending'"
                );
                $pendingStmt->execute([':user_id'=>$userId]);
                $stats['pending_applications'] = $pendingStmt->fetch()['pending'];
            }
            return ['success'=>true,'stats'=>$stats];
        } catch (Exception $e) {
            error_log("getDashboardStats error: ".$e->getMessage());
            return ['success'=>false,'error'=>'Failed to fetch stats'];
        }
    }
}
?>