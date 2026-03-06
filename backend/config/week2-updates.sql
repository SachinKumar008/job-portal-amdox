-- ============================================
-- WEEK 2-3 DATABASE UPDATES
-- Run this in phpMyAdmin → job_portal database → SQL tab
-- ============================================

USE job_portal;

-- Add updated_at to job_seeker_profiles if missing
ALTER TABLE job_seeker_profiles
  ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- Add updated_at to employer_profiles if missing
ALTER TABLE employer_profiles
  ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- Add updated_at to job_applications if missing
ALTER TABLE job_applications
  ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- Insert some sample jobs for testing
INSERT INTO job_listings (employer_id, job_title, job_description, job_type, location, salary_min, salary_max, qualifications, responsibilities, is_active)
SELECT 
  u.user_id,
  'Frontend Developer',
  'We are looking for a talented Frontend Developer to join our growing team. You will be responsible for building and maintaining web applications.',
  'full_time',
  'Mumbai, India',
  40000,
  70000,
  'Proficiency in HTML, CSS, JavaScript. Experience with React or Vue. Good understanding of responsive design.',
  'Develop new user-facing features. Build reusable components. Optimize applications for speed. Collaborate with backend team.',
  1
FROM users u WHERE u.user_type = 'employer' LIMIT 1;

INSERT INTO job_listings (employer_id, job_title, job_description, job_type, location, salary_min, salary_max, qualifications, responsibilities, is_active)
SELECT 
  u.user_id,
  'Backend PHP Developer',
  'Join our backend team to build scalable APIs and web services using PHP and MySQL.',
  'full_time',
  'Bangalore, India',
  45000,
  80000,
  'Strong PHP skills. MySQL experience. Knowledge of REST APIs. Understanding of MVC pattern.',
  'Design and develop APIs. Optimize database queries. Write clean maintainable code. Perform code reviews.',
  1
FROM users u WHERE u.user_type = 'employer' LIMIT 1;

INSERT INTO job_listings (employer_id, job_title, job_description, job_type, location, salary_min, salary_max, qualifications, responsibilities, is_active)
SELECT 
  u.user_id,
  'UI/UX Design Intern',
  'Great opportunity for a creative designer to work on real-world projects and build their portfolio.',
  'internship',
  'Remote',
  10000,
  15000,
  'Portfolio of design work. Familiarity with Figma or Adobe XD. Basic understanding of HTML/CSS.',
  'Create wireframes and prototypes. Design UI components. Conduct user research. Collaborate with developers.',
  1
FROM users u WHERE u.user_type = 'employer' LIMIT 1;