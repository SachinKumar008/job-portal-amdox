-- Create users table
CREATE TABLE IF NOT EXISTS users (
    user_id SERIAL PRIMARY KEY,
    user_type VARCHAR(20) NOT NULL CHECK (user_type IN ('job_seeker', 'employer')),
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create job_seeker_profiles table
CREATE TABLE IF NOT EXISTS job_seeker_profiles (
    profile_id SERIAL PRIMARY KEY,
    user_id INTEGER UNIQUE NOT NULL REFERENCES users(user_id) ON DELETE CASCADE,
    resume_path VARCHAR(500),
    skills TEXT,
    experience TEXT,
    education TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create employer_profiles table
CREATE TABLE IF NOT EXISTS employer_profiles (
    profile_id SERIAL PRIMARY KEY,
    user_id INTEGER UNIQUE NOT NULL REFERENCES users(user_id) ON DELETE CASCADE,
    company_name VARCHAR(255) NOT NULL,
    company_description TEXT,
    company_website VARCHAR(255),
    industry VARCHAR(100),
    company_address TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create job_listings table
CREATE TABLE IF NOT EXISTS job_listings (
    job_id SERIAL PRIMARY KEY,
    employer_id INTEGER NOT NULL REFERENCES users(user_id) ON DELETE CASCADE,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    requirements TEXT,
    location VARCHAR(255),
    job_type VARCHAR(50),
    salary_min DECIMAL(10,2),
    salary_max DECIMAL(10,2),
    status VARCHAR(20) DEFAULT 'active' CHECK (status IN ('active', 'closed', 'draft')),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create job_applications table
CREATE TABLE IF NOT EXISTS job_applications (
    application_id SERIAL PRIMARY KEY,
    job_id INTEGER NOT NULL REFERENCES job_listings(job_id) ON DELETE CASCADE,
    applicant_id INTEGER NOT NULL REFERENCES users(user_id) ON DELETE CASCADE,
    cover_letter TEXT,
    resume_path VARCHAR(500),
    status VARCHAR(20) DEFAULT 'pending' CHECK (status IN ('pending', 'reviewed', 'shortlisted', 'rejected')),
    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(job_id, applicant_id)
);

-- Create indexes
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_job_listings_employer ON job_listings(employer_id);
CREATE INDEX idx_job_listings_status ON job_listings(status);
CREATE INDEX idx_applications_job ON job_applications(job_id);
CREATE INDEX idx_applications_applicant ON job_applications(applicant_id);