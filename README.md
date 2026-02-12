# 🎯 COMPLETE SETUP GUIDE - Job Listing Portal

## ⚡ QUICK START (Follow These Exact Steps)

### STEP 1: Download and Extract Files

1. Download all the files I provided
2. Create this folder structure on your Desktop:

```
Desktop/
└── job-listing-portal/
    ├── backend/
    │   ├── config/
    │   ├── controllers/
    │   └── api/
    │       └── auth/
    └── frontend/
        └── assets/
            ├── css/
            └── js/
```

### STEP 2: Place Files in Correct Locations

Copy each file to its exact location:

**Backend Files:**
```
✅ database.php        → backend/config/database.php
✅ schema.sql          → backend/config/schema.sql
✅ AuthController.php  → backend/controllers/AuthController.php
✅ register.php        → backend/api/auth/register.php
✅ login.php           → backend/api/auth/login.php
✅ logout.php          → backend/api/auth/logout.php
```

**Frontend Files:**
```
✅ main.css            → frontend/assets/css/main.css
✅ main.js             → frontend/assets/js/main.js
✅ index.html          → frontend/index.html
✅ register.html       → frontend/register.html
✅ login.html          → frontend/login.html
```

---

### STEP 3: Set Up Database

1. **Open XAMPP/WAMP**
   - Start Apache
   - Start MySQL

2. **Open phpMyAdmin**
   - Go to: http://localhost/phpmyadmin

3. **Create Database**
   - Click "New" on left sidebar
   - Database name: `job_portal`
   - Collation: `utf8mb4_unicode_ci`
   - Click "Create"

4. **Import Schema**
   - Click on `job_portal` database
   - Click "Import" tab
   - Click "Choose File"
   - Select: `backend/config/schema.sql`
   - Click "Go" button at bottom
   - ✅ You should see: "Import has been successfully finished"

---

### STEP 4: Configure Database Connection

1. Open: `backend/config/database.php`

2. Update these lines with YOUR credentials:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');           // ← Your MySQL username
define('DB_PASS', '');               // ← Your MySQL password (usually empty for XAMPP)
define('DB_NAME', 'job_portal');
```

3. Save the file

---

### STEP 5: Start the Server

**Option A: Using PHP Built-in Server (Recommended)**

1. Open Command Prompt (Windows) or Terminal (Mac/Linux)

2. Navigate to backend folder:
```bash
cd Desktop/job-listing-portal/backend
```

3. Start server:
```bash
php -S localhost:8000
```

4. You should see:
```
PHP Development Server started on http://localhost:8000
```

**Option B: Using XAMPP**

1. Copy `job-listing-portal` folder to:
   - Windows: `C:/xampp/htdocs/`
   - Mac: `/Applications/XAMPP/htdocs/`

2. Access via: `http://localhost/job-listing-portal/frontend/index.html`

---

### STEP 6: Test the Application

1. **Open your browser**

2. **Go to:**
   - If using PHP server: `http://localhost:8000/../frontend/index.html`
   - If using XAMPP: `http://localhost/job-listing-portal/frontend/index.html`

3. **Test Registration:**
   - Click "Get Started"
   - Fill in the form
   - Click "Create Account"
   - ✅ Should redirect to login page

4. **Test Login:**
   - Use demo credentials:
     - Email: `admin@jobportal.com`
     - Password: `Admin@123`
   - Click "Login"
   - ✅ Should show success message

---

## 📋 COMPLETE FILE LIST

Here's what you should have:

### Backend (6 files)
```
backend/
├── config/
│   ├── database.php          ✅ Database connection
│   └── schema.sql            ✅ Database tables (run in phpMyAdmin)
├── controllers/
│   └── AuthController.php    ✅ Login/Register logic
└── api/
    └── auth/
        ├── register.php      ✅ Registration endpoint
        ├── login.php         ✅ Login endpoint
        └── logout.php        ✅ Logout endpoint
```

### Frontend (5 files)
```
frontend/
├── assets/
│   ├── css/
│   │   └── main.css          ✅ All styles
│   └── js/
│       └── main.js           ✅ All JavaScript
├── index.html                ✅ Homepage
├── register.html             ✅ Registration page
└── login.html                ✅ Login page
```

**Total: 11 files**

---

## 🔧 TROUBLESHOOTING

### Problem 1: "Connection failed"
**Solution:**
- Make sure MySQL is running in XAMPP/WAMP
- Check `database.php` credentials
- Verify database `job_portal` exists

### Problem 2: "Cannot find register.php"
**Solution:**
- Make sure PHP server is running
- Check file paths are correct
- Restart the server

### Problem 3: Forms not working
**Solution:**
- Open browser console (F12)
- Check for JavaScript errors
- Verify API_BASE_URL in `main.js` is correct:
  ```javascript
  const API_BASE_URL = 'http://localhost:8000/api';
  ```

### Problem 4: Password error on login
**Solution:**
- The demo password is: `Admin@123` (case-sensitive)
- Or register a new account

---

## ✅ VERIFICATION CHECKLIST

Before you start coding, verify:

- [ ] Database `job_portal` created
- [ ] Schema imported (5 tables should exist)
- [ ] All 11 files in correct locations
- [ ] Database credentials updated in `database.php`
- [ ] PHP server running
- [ ] Can access homepage in browser
- [ ] Can register new account
- [ ] Can login with demo credentials
- [ ] No errors in browser console

---

## 🎓 WHAT WORKS NOW

### ✅ Completed Features:
1. **User Registration**
   - Job seekers can register
   - Employers can register
   - Form validation (real-time)
   - Password strength check
   - Email format validation

2. **User Login**
   - Email/password authentication
   - Secure password hashing
   - Session management
   - Error handling

3. **Database**
   - 5 tables created
   - Proper relationships
   - Demo user inserted

4. **UI/UX**
   - Modern, clean design
   - Smooth animations
   - Responsive layout
   - Loading spinners
   - Error messages

---

## 🚀 NEXT STEPS (Week 2+)

Now that Week 1 is complete, you can build:

### Week 2: Job Management
- Create job posting form
- View all jobs
- Job details page
- Edit/delete jobs

### Week 3: Job Search
- Search functionality
- Filter by location, type, salary
- Advanced search

### Week 4: Applications
- Apply to jobs
- View applications
- Application status

---

## 👥 TEAM COLLABORATION

### Using Git

1. **Initialize Git:**
```bash
cd job-listing-portal
git init
git add .
git commit -m "Initial commit: Week 1 complete"
```

2. **Create GitHub Repository:**
   - Go to github.com
   - Create new repository
   - Copy the URL

3. **Push to GitHub:**
```bash
git remote add origin <your-repo-url>
git push -u origin main
```

4. **Team Members Clone:**
```bash
git clone <repo-url>
cd job-listing-portal
```

5. **Create Feature Branches:**
```bash
git checkout -b feature/your-name
# Make changes
git add .
git commit -m "Description"
git push origin feature/your-name
```

---

## 📞 NEED HELP?

**Common Resources:**
- PHP Docs: https://www.php.net/manual/en/
- MySQL Docs: https://dev.mysql.com/doc/
- JavaScript: https://developer.mozilla.org/

**For Issues:**
- Check browser console (F12)
- Check PHP errors
- Review file paths
- Verify database connection

**Contact:**
- Mentor: support@amdox.in

---

## 🎉 CONGRATULATIONS!

You now have a **fully functional authentication system** with:
- ✅ Beautiful modern UI
- ✅ Secure login/registration
- ✅ Database integration
- ✅ Form validation
- ✅ Error handling

**Week 1 Tasks: COMPLETE!** ✅

Start your server, test it out, and get ready for Week 2!

---

**Built for AMDOX Web Development Internship Program** 🚀