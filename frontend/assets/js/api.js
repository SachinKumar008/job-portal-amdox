/**
 * Job Portal - Central API Client (FIXED)
 * Works on XAMPP (port 80) and PHP built-in server (port 8000)
 * Auth via X-User-Id / X-User-Type headers from localStorage (no PHP sessions)
 */

const API = {
    get base() { return window.API_BASE || 'http://localhost:8000/api'; },

    async request(endpoint, method = 'GET', body = null) {
        const user = Auth.get();
        const headers = { 'Content-Type': 'application/json', 'Accept': 'application/json' };
        if (user) {
            headers['X-User-Id']   = String(user.user_id);
            headers['X-User-Type'] = user.user_type;
            headers['X-User-Name'] = user.full_name;
        }
        const opts = { method, headers };
        if (body) opts.body = JSON.stringify(body);

        try {
            const res  = await fetch(this.base + endpoint, opts);
            const text = await res.text();
            try   { return JSON.parse(text); }
            catch (_) {
                console.error('Non-JSON from', endpoint, text.substring(0,300));
                return { success: false, error: 'Server error: ' + text.substring(0, 200) };
            }
        } catch (e) {
            console.error('Fetch error', endpoint, e);
            return { success: false, error: 'Network error. Is the server running?' };
        }
    },

    // Auth
    login:    (d) => API.request('/auth/login.php',    'POST', d),
    register: (d) => API.request('/auth/register.php', 'POST', d),
    logout:   ()  => API.request('/auth/logout.php',   'POST'),

    // Jobs
    getJobs:   (q='') => API.request('/jobs/index.php' + q),
    getJob:    (id)   => API.request('/jobs/index.php?id=' + id),
    getMyJobs: ()     => API.request('/jobs/index.php?mine=1'),
    createJob: (d)    => API.request('/jobs/index.php',        'POST',   d),
    updateJob: (id,d) => API.request('/jobs/index.php?id='+id, 'PUT',    d),
    deleteJob: (id)   => API.request('/jobs/index.php?id='+id, 'DELETE'),

    // Applications
    applyForJob:     (jobId,d)  => API.request('/applications/index.php?job_id='+jobId, 'POST', d),
    getMyApps:       ()         => API.request('/applications/index.php'),
    getApplicants:   (jobId)    => API.request('/applications/index.php?job_id='+jobId),
    checkApplied:    (jobId)    => API.request('/applications/index.php?check='+jobId),
    updateAppStatus: (id,status)=> API.request('/applications/index.php?id='+id, 'PUT', {status}),

    // Profile
    getProfile:    ()  => API.request('/profile/index.php'),
    updateProfile: (d) => API.request('/profile/index.php', 'PUT', d),
    getStats:      ()  => API.request('/profile/index.php?stats=1'),
};

const Auth = {
    save:       (u) => localStorage.setItem('jp_user', JSON.stringify(u)),
    get:        ()  => { try { return JSON.parse(localStorage.getItem('jp_user')); } catch { return null; } },
    clear:      ()  => localStorage.removeItem('jp_user'),
    isLoggedIn: ()  => !!Auth.get(),
    isEmployer: ()  => Auth.get()?.user_type === 'employer',
    isJobSeeker:()  => Auth.get()?.user_type === 'job_seeker',

    guard(requiredType = null) {
        const user = Auth.get();
        if (!user) { window.location.href = 'login.html'; return null; }
        if (requiredType && user.user_type !== requiredType) {
            alert('Access denied. This page is for ' + requiredType + ' accounts only.');
            window.location.href = 'dashboard.html'; return null;
        }
        return user;
    },

    updateNav() {
        const user = Auth.get();
        const g = document.getElementById('guest-nav');
        const l = document.getElementById('logged-nav');
        const n = document.getElementById('user-name');
        if (user) {
            if (g) g.style.display = 'none';
            if (l) l.style.display = 'flex';
            if (n) n.textContent   = user.full_name;
        } else {
            if (g) g.style.display = 'flex';
            if (l) l.style.display = 'none';
        }
    }
};

const UI = {
    toast(msg, type = 'success') {
        const t = document.createElement('div');
        t.className = 'toast toast-' + type;
        t.textContent = msg;
        document.body.appendChild(t);
        requestAnimationFrame(() => t.classList.add('show'));
        setTimeout(() => { t.classList.remove('show'); setTimeout(() => t.remove(), 400); }, 3500);
    },
    setLoading(btnId, on) {
        const b = document.getElementById(btnId);
        if (!b) return;
        b.disabled = on;
        if (on) { b.dataset.orig = b.textContent; b.textContent = 'Please wait…'; }
        else b.textContent = b.dataset.orig || 'Submit';
    },
    formatSalary(min, max) {
        if (!min && !max) return 'Not specified';
        const f = n => '₹' + Number(n).toLocaleString('en-IN');
        if (min && max) return f(min) + ' – ' + f(max) + '/mo';
        return min ? 'From ' + f(min) + '/mo' : 'Up to ' + f(max) + '/mo';
    },
    formatDate(d) {
        if (!d) return '';
        return new Date(d).toLocaleDateString('en-IN', {day:'numeric',month:'short',year:'numeric'});
    },
    jobTypeBadge(t) {
        const m = {full_time:['Full Time','badge-blue'],part_time:['Part Time','badge-yellow'],
                   contract:['Contract','badge-purple'],internship:['Internship','badge-green']};
        const [label,cls] = m[t] || [t,'badge-gray'];
        return `<span class="badge ${cls}">${label}</span>`;
    },
    statusBadge(s) {
        const m = {pending:['Pending','badge-yellow'],reviewing:['Reviewing','badge-blue'],
                   accepted:['Accepted','badge-green'],rejected:['Rejected','badge-red']};
        const [label,cls] = m[s] || [s,'badge-gray'];
        return `<span class="badge ${cls}">${label}</span>`;
    }
};

document.addEventListener('DOMContentLoaded', () => {
    Auth.updateNav();
    const lb = document.getElementById('logout-btn');
    if (lb) lb.addEventListener('click', async e => {
        e.preventDefault();
        Auth.clear();
        window.location.href = 'login.html';
    });
});