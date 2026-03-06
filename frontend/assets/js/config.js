/**
 * config.js — Auto-detects your server setup
 * Works whether you use XAMPP (port 80) or PHP built-in server (port 8000)
 *
 * Place this file at: frontend/assets/js/config.js
 * Then include it FIRST before any other JS in every HTML page
 */

(function() {
    const host = window.location.hostname; // localhost or 127.0.0.1
    const port = window.location.port;     // 80, 8000, or empty

    let apiBase;

    if (port === '8000') {
        // Running via: php -S localhost:8000  (from backend folder)
        // Frontend served from: http://localhost:8000/../frontend/
        apiBase = 'http://' + host + ':8000/api';
    } else {
        // Running via XAMPP/WAMP (port 80)
        // URL looks like: localhost/job-listing-portal/frontend/login.html
        // API is at:      localhost/job-listing-portal/backend/api/
        const pathParts = window.location.pathname.split('/');
        // pathParts = ['', 'job-listing-portal', 'frontend', 'login.html']
        const projectFolder = pathParts[1]; // 'job-listing-portal'
        apiBase = window.location.protocol + '//' + host + '/' + projectFolder + '/backend/api';
    }

    window.API_BASE = apiBase;
    console.log('[Config] API Base URL detected:', apiBase);
})();