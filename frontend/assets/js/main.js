/**
 * ================================================
 * JOB LISTING PORTAL - MAIN JAVASCRIPT (FIXED)
 * ================================================
 */

// ================ API CONFIGURATION ================
// IMPORTANT: Update this to match your setup
const API_BASE_URL = 'http://localhost:8000/api';

// ================ UTILITY FUNCTIONS ================
const $ = (selector) => document.querySelector(selector);
const $$ = (selector) => document.querySelectorAll(selector);

/**
 * Show error message for form field
 */
function showError(fieldId, message) {
    const field = $(`#${fieldId}`);
    const errorElement = $(`#${fieldId}-error`);
    
    if (field) field.classList.add('error');
    if (errorElement) {
        errorElement.textContent = message;
        errorElement.classList.add('show');
    }
}

/**
 * Clear error message for form field
 */
function clearError(fieldId) {
    const field = $(`#${fieldId}`);
    const errorElement = $(`#${fieldId}-error`);
    
    if (field) field.classList.remove('error');
    if (errorElement) {
        errorElement.textContent = '';
        errorElement.classList.remove('show');
    }
}

/**
 * Clear all errors in form
 */
function clearAllErrors(formId) {
    const form = $(`#${formId}`);
    if (!form) return;
    
    const errorMessages = form.querySelectorAll('.error-message');
    const errorFields = form.querySelectorAll('.error');
    
    errorMessages.forEach(error => {
        error.textContent = '';
        error.classList.remove('show');
    });
    
    errorFields.forEach(field => field.classList.remove('error'));
}

/**
 * Show message (success or error)
 */
function showMessage(message, containerId = 'message-container', isError = false) {
    const container = $(`#${containerId}`);
    if (!container) {
        console.log('Message:', message);
        return;
    }
    
    const messageDiv = document.createElement('div');
    messageDiv.className = isError ? 'error-message show' : 'success-message';
    messageDiv.textContent = message;
    messageDiv.style.display = 'block';
    messageDiv.style.marginBottom = '1rem';
    
    container.innerHTML = '';
    container.appendChild(messageDiv);
    
    setTimeout(() => messageDiv.remove(), 5000);
}

/**
 * Show loading spinner
 */
function showLoading(buttonId) {
    const button = $(`#${buttonId}`);
    if (!button) return;
    
    button.disabled = true;
    button.dataset.originalText = button.textContent;
    button.innerHTML = '<div class="spinner" style="width: 20px; height: 20px; margin: 0 auto;"></div>';
}

/**
 * Hide loading spinner
 */
function hideLoading(buttonId) {
    const button = $(`#${buttonId}`);
    if (!button) return;
    
    button.disabled = false;
    button.textContent = button.dataset.originalText || 'Submit';
}

/**
 * Validate email format
 */
function validateEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

/**
 * Validate password strength
 */
function validatePassword(password) {
    return password.length >= 8;
}

// ================ REGISTRATION FORM ================
/**
 * Validate registration form
 */
function validateRegistrationForm(formData) {
    let isValid = true;
    clearAllErrors('registration-form');
    
    if (!formData.full_name || formData.full_name.trim().length < 3) {
        showError('full_name', 'Full name must be at least 3 characters');
        isValid = false;
    }
    
    if (!formData.email || !validateEmail(formData.email)) {
        showError('email', 'Please enter a valid email address');
        isValid = false;
    }
    
    if (!formData.password || !validatePassword(formData.password)) {
        showError('password', 'Password must be at least 8 characters');
        isValid = false;
    }
    
    if (formData.password !== formData.confirm_password) {
        showError('confirm_password', 'Passwords do not match');
        isValid = false;
    }
    
    if (!formData.user_type) {
        showError('user_type', 'Please select a user type');
        isValid = false;
    }
    
    if (formData.user_type === 'employer' && !formData.company_name) {
        showError('company_name', 'Company name is required for employers');
        isValid = false;
    }
    
    return isValid;
}

/**
 * Handle registration form submission
 */
async function handleRegistration(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = {
        full_name: form.full_name.value.trim(),
        email: form.email.value.trim(),
        password: form.password.value,
        confirm_password: form.confirm_password.value,
        phone: form.phone.value.trim(),
        user_type: form.user_type.value,
        company_name: form.company_name ? form.company_name.value.trim() : null
    };
    
    console.log('Submitting registration:', { ...formData, password: '***', confirm_password: '***' });
    
    if (!validateRegistrationForm(formData)) {
        return;
    }
    
    showLoading('register-btn');
    
    try {
        const response = await fetch(`${API_BASE_URL}/auth/register.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(formData)
        });
        
        console.log('Response status:', response.status);
        
        const result = await response.json();
        console.log('Response data:', result);
        
        if (result.success) {
            showMessage('Registration successful! Redirecting to login...', 'message-container', false);
            form.reset();
            
            setTimeout(() => {
                window.location.href = 'login.html';
            }, 2000);
        } else {
            if (result.errors) {
                for (const [field, message] of Object.entries(result.errors)) {
                    if (field === 'general') {
                        showMessage(message, 'message-container', true);
                    } else {
                        showError(field, message);
                    }
                }
            } else {
                showMessage('Registration failed. Please try again.', 'message-container', true);
            }
        }
    } catch (error) {
        console.error('Registration error:', error);
        showMessage('Network error. Please check your connection and try again.', 'message-container', true);
    } finally {
        hideLoading('register-btn');
    }
}

// ================ LOGIN FORM ================
/**
 * Validate login form
 */
function validateLoginForm(formData) {
    let isValid = true;
    clearAllErrors('login-form');
    
    if (!formData.email || !validateEmail(formData.email)) {
        showError('email', 'Please enter a valid email address');
        isValid = false;
    }
    
    if (!formData.password) {
        showError('password', 'Password is required');
        isValid = false;
    }
    
    return isValid;
}

/**
 * Handle login form submission
 */
async function handleLogin(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = {
        email: form.email.value.trim(),
        password: form.password.value
    };
    
    console.log('Attempting login for:', formData.email);
    
    if (!validateLoginForm(formData)) {
        return;
    }
    
    showLoading('login-btn');
    
    try {
        const response = await fetch(`${API_BASE_URL}/auth/login.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(formData)
        });
        
        console.log('Response status:', response.status);
        
        const result = await response.json();
        console.log('Response data:', result);
        
        if (result.success) {
            showMessage('Login successful! Redirecting...', 'message-container', false);
            
            // Store user data
            localStorage.setItem('user', JSON.stringify(result.user));
            
            // Redirect to dashboard
            setTimeout(() => {
                window.location.href = 'index.html';
            }, 1500);
        } else {
            showMessage(result.error || 'Login failed. Please check your credentials.', 'message-container', true);
        }
    } catch (error) {
        console.error('Login error:', error);
        showMessage('Network error. Please check your connection and try again.', 'message-container', true);
    } finally {
        hideLoading('login-btn');
    }
}

// ================ LOGOUT ================
async function handleLogout() {
    try {
        await fetch(`${API_BASE_URL}/auth/logout.php`, { 
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            }
        });
        localStorage.removeItem('user');
        window.location.href = 'index.html';
    } catch (error) {
        console.error('Logout error:', error);
        localStorage.removeItem('user');
        window.location.href = 'index.html';
    }
}

// ================ TOGGLE COMPANY FIELD ================
function toggleCompanyField() {
    const userType = $('#user_type');
    const companyField = $('#company-field');
    
    if (!userType || !companyField) return;
    
    userType.addEventListener('change', function() {
        if (this.value === 'employer') {
            companyField.classList.remove('hidden');
        } else {
            companyField.classList.add('hidden');
            clearError('company_name');
        }
    });
}

// ================ REAL-TIME VALIDATION ================
function addRealTimeValidation() {
    const emailFields = $$('input[type="email"]');
    emailFields.forEach(field => {
        field.addEventListener('blur', function() {
            if (this.value && !validateEmail(this.value)) {
                showError(this.id, 'Please enter a valid email address');
            } else {
                clearError(this.id);
            }
        });
    });
    
    const password = $('#password');
    if (password) {
        password.addEventListener('input', function() {
            if (this.value.length > 0 && this.value.length < 8) {
                showError(this.id, 'Password must be at least 8 characters');
            } else {
                clearError(this.id);
            }
        });
    }
    
    const confirmPassword = $('#confirm_password');
    if (confirmPassword && password) {
        confirmPassword.addEventListener('input', function() {
            if (this.value !== password.value) {
                showError(this.id, 'Passwords do not match');
            } else {
                clearError(this.id);
            }
        });
    }
}

// ================ INITIALIZE ================
document.addEventListener('DOMContentLoaded', function() {
    console.log('Page loaded, API Base URL:', API_BASE_URL);
    
    const registrationForm = $('#registration-form');
    if (registrationForm) {
        console.log('Registration form found');
        registrationForm.addEventListener('submit', handleRegistration);
        toggleCompanyField();
    }
    
    const loginForm = $('#login-form');
    if (loginForm) {
        console.log('Login form found');
        loginForm.addEventListener('submit', handleLogin);
    }
    
    addRealTimeValidation();
    
    const logoutBtn = $('#logout-btn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', function(e) {
            e.preventDefault();
            if (confirm('Are you sure you want to logout?')) {
                handleLogout();
            }
        });
    }
});