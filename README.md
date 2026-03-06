# 💼 Job Portal - AMDOX Technologies

A modern job portal web application connecting job seekers with employers.

![Status](https://img.shields.io/badge/Status-Production%20Ready-success)
![PHP](https://img.shields.io/badge/PHP-7.4+-blue)

---

## 🌟 Features

### For Job Seekers
- ✅ Browse and search jobs with advanced filters
- ✅ Apply to jobs with cover letter
- ✅ Upload and manage resume
- ✅ Track application status
- ✅ Personalized dashboard

### For Employers
- ✅ Post and manage job listings
- ✅ View and filter applicants
- ✅ Bulk applicant management
- ✅ Export applicant data to CSV
- ✅ Company profile management

### General
- ✅ Responsive design (Mobile, Tablet, Desktop)
- ✅ Resume upload system
- ✅ Advanced search filters
- ✅ Professional UI/UX
- ✅ Secure authentication

---

## 🛠️ Tech Stack

**Frontend:** HTML5, CSS3, JavaScript (ES6+)  
**Backend:** PHP 7.4+, MySQL  
**Deployment:** Vercel (Frontend), InfinityFree (Backend)

---

## 📦 Installation

### Prerequisites
- PHP 7.4+
- MySQL 5.7+
- Web server (Apache/Nginx)

### Local Setup

1. **Clone repository**
```bash
git clone https://github.com/YOUR-USERNAME/job-portal-amdox.git
cd job-portal-amdox
```

2. **Setup database**
```sql
CREATE DATABASE job_portal;
-- Import database/job_portal.sql
```

3. **Configure database**
Edit `backend/config/database.php`:
```php
private $host = "localhost";
private $db_name = "job_portal";
private $username = "root";
private $password = "";
```

4. **Run application**
```
http://localhost/job-listing-portal/frontend/
```

---

## 🚀 Deployment

### Frontend (Vercel)
1. Push code to GitHub
2. Import repository in Vercel
3. Set output directory: `frontend`
4. Deploy!

### Backend (InfinityFree)
1. Upload `backend` folder
2. Create MySQL database
3. Import SQL schema
4. Update database config

---

## 📊 Database Schema

- `users` - User authentication
- `job_seeker_profiles` - Resume & skills
- `employer_profiles` - Company info
- `job_listings` - Job postings
- `job_applications` - Application tracking

---

## 🔒 Security

- ✅ Password hashing
- ✅ SQL injection prevention (PDO)
- ✅ XSS protection
- ✅ File upload validation
- ✅ Session management

---

## 📱 Responsive Design

Optimized for all screen sizes:
- Mobile: 320px+
- Tablet: 768px+
- Desktop: 1024px+

---

## 👨‍💻 Developer

**Sachin Rao**  
Organization: AMDOX Technologies  
Email: sachinrao787885@gmail.com  
Internship: 2026

---

## 📄 License

© 2026 AMDOX Technologies. All rights reserved.

---

## 🌐 Live Demo

**Frontend:** https://job-portal-amdox.vercel.app  
**GitHub:** https://github.com/YOUR-USERNAME/job-portal-amdox

---

⭐ **If you found this project helpful, please give it a star!**