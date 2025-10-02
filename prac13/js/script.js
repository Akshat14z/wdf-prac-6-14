// Secure Authentication System JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Form toggle functionality
    const loginToggle = document.getElementById('login-toggle');
    const registerToggle = document.getElementById('register-toggle');
    const loginForm = document.getElementById('login-form');
    const registerForm = document.getElementById('register-form');
    
    loginToggle.addEventListener('click', function() {
        showForm('login');
    });
    
    registerToggle.addEventListener('click', function() {
        showForm('register');
    });
    
    function showForm(formType) {
        if (formType === 'login') {
            loginToggle.classList.add('active');
            registerToggle.classList.remove('active');
            loginForm.classList.add('active');
            registerForm.classList.remove('active');
            refreshCaptcha('login');
        } else {
            registerToggle.classList.add('active');
            loginToggle.classList.remove('active');
            registerForm.classList.add('active');
            loginForm.classList.remove('active');
            refreshCaptcha('register');
        }
        clearAllErrors();
    }
    
    // Initialize CAPTCHAs
    refreshCaptcha('login');
    refreshCaptcha('register');
    
    // Form submission handlers
    document.getElementById('loginForm').addEventListener('submit', function(e) {
        e.preventDefault();
        handleLogin();
    });
    
    document.getElementById('registerForm').addEventListener('submit', function(e) {
        e.preventDefault();
        handleRegister();
    });
    
    // Real-time validation
    document.getElementById('register_password').addEventListener('input', function() {
        validatePassword(this.value, 'register');
    });
    
    document.getElementById('register_confirm_password').addEventListener('input', function() {
        validateConfirmPassword();
    });
    
    document.getElementById('register_email').addEventListener('blur', function() {
        validateEmail(this.value, 'register');
    });
    
    document.getElementById('register_username').addEventListener('blur', function() {
        validateUsername(this.value, 'register');
    });
});

// CAPTCHA functions
function generateCaptcha() {
    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    let captcha = '';
    for (let i = 0; i < 6; i++) {
        captcha += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    return captcha;
}

function refreshCaptcha(formType) {
    const captcha = generateCaptcha();
    const display = document.getElementById(formType + '_captcha_display');
    display.textContent = captcha;
    display.setAttribute('data-captcha', captcha);
    
    // Clear captcha input
    const input = document.getElementById(formType + '_captcha');
    if (input) {
        input.value = '';
        clearError(formType + '_captcha');
    }
}

// Validation functions
function validateEmail(email, formType) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    const isValid = emailRegex.test(email);
    
    if (!isValid && email.length > 0) {
        showError(formType + '_email', 'Please enter a valid email address');
        return false;
    } else {
        clearError(formType + '_email');
        return true;
    }
}

function validatePassword(password, formType) {
    const requirements = {
        length: password.length >= 8,
        uppercase: /[A-Z]/.test(password),
        lowercase: /[a-z]/.test(password),
        number: /\d/.test(password),
        special: /[@$!%*?&]/.test(password)
    };
    
    const allValid = Object.values(requirements).every(req => req);
    
    if (!allValid && password.length > 0) {
        let errorMsg = 'Password must contain: ';
        const missing = [];
        if (!requirements.length) missing.push('8+ characters');
        if (!requirements.uppercase) missing.push('uppercase letter');
        if (!requirements.lowercase) missing.push('lowercase letter');
        if (!requirements.number) missing.push('number');
        if (!requirements.special) missing.push('special character');
        
        errorMsg += missing.join(', ');
        showError(formType + '_password', errorMsg);
        return false;
    } else {
        clearError(formType + '_password');
        return true;
    }
}

function validateConfirmPassword() {
    const password = document.getElementById('register_password').value;
    const confirmPassword = document.getElementById('register_confirm_password').value;
    
    if (confirmPassword.length > 0 && password !== confirmPassword) {
        showError('register_confirm_password', 'Passwords do not match');
        return false;
    } else {
        clearError('register_confirm_password');
        return true;
    }
}

function validateUsername(username, formType) {
    if (username.length > 0 && username.length < 3) {
        showError(formType + '_username', 'Username must be at least 3 characters long');
        return false;
    } else {
        clearError(formType + '_username');
        return true;
    }
}

function validateCaptcha(formType) {
    const input = document.getElementById(formType + '_captcha');
    const display = document.getElementById(formType + '_captcha_display');
    const expectedCaptcha = display.getAttribute('data-captcha');
    
    if (input.value !== expectedCaptcha) {
        showError(formType + '_captcha', 'CAPTCHA is incorrect');
        return false;
    } else {
        clearError(formType + '_captcha');
        return true;
    }
}

// Error handling functions
function showError(fieldId, message) {
    const errorElement = document.getElementById(fieldId + '_error');
    const inputElement = document.getElementById(fieldId);
    
    if (errorElement) {
        errorElement.textContent = message;
    }
    if (inputElement) {
        inputElement.classList.add('error');
    }
}

function clearError(fieldId) {
    const errorElement = document.getElementById(fieldId + '_error');
    const inputElement = document.getElementById(fieldId);
    
    if (errorElement) {
        errorElement.textContent = '';
    }
    if (inputElement) {
        inputElement.classList.remove('error');
    }
}

function clearAllErrors() {
    const errorElements = document.querySelectorAll('.error-message');
    const inputElements = document.querySelectorAll('input.error');
    
    errorElements.forEach(element => element.textContent = '');
    inputElements.forEach(element => element.classList.remove('error'));
}

// AJAX form submission handlers
function handleLogin() {
    const form = document.getElementById('loginForm');
    const submitBtn = document.getElementById('login-submit');
    
    // Validate CAPTCHA
    if (!validateCaptcha('login')) {
        return;
    }
    
    const formData = new FormData(form);
    formData.append('action', 'login');
    
    setLoading(submitBtn, true);
    
    fetch('auth.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        setLoading(submitBtn, false);
        
        if (data.success) {
            showMessage(data.message, 'success');
            setTimeout(() => {
                window.location.href = 'dashboard.php';
            }, 1500);
        } else {
            showMessage(data.message, 'error');
            if (data.errors) {
                Object.keys(data.errors).forEach(field => {
                    showError('login_' + field, data.errors[field]);
                });
            }
            refreshCaptcha('login');
        }
    })
    .catch(error => {
        setLoading(submitBtn, false);
        showMessage('An error occurred. Please try again.', 'error');
        refreshCaptcha('login');
    });
}

function handleRegister() {
    const form = document.getElementById('registerForm');
    const submitBtn = document.getElementById('register-submit');
    
    // Client-side validation
    const fullname = document.getElementById('register_fullname').value;
    const username = document.getElementById('register_username').value;
    const email = document.getElementById('register_email').value;
    const password = document.getElementById('register_password').value;
    
    let isValid = true;
    
    if (fullname.trim().length < 2) {
        showError('register_fullname', 'Full name must be at least 2 characters long');
        isValid = false;
    }
    
    if (!validateUsername(username, 'register')) isValid = false;
    if (!validateEmail(email, 'register')) isValid = false;
    if (!validatePassword(password, 'register')) isValid = false;
    if (!validateConfirmPassword()) isValid = false;
    if (!validateCaptcha('register')) isValid = false;
    
    if (!isValid) return;
    
    const formData = new FormData(form);
    formData.append('action', 'register');
    
    setLoading(submitBtn, true);
    
    fetch('auth.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        setLoading(submitBtn, false);
        
        if (data.success) {
            showMessage(data.message, 'success');
            form.reset();
            refreshCaptcha('register');
            setTimeout(() => {
                showForm('login');
            }, 2000);
        } else {
            showMessage(data.message, 'error');
            if (data.errors) {
                Object.keys(data.errors).forEach(field => {
                    showError('register_' + field, data.errors[field]);
                });
            }
            refreshCaptcha('register');
        }
    })
    .catch(error => {
        setLoading(submitBtn, false);
        showMessage('An error occurred. Please try again.', 'error');
        refreshCaptcha('register');
    });
}

// UI helper functions
function setLoading(button, isLoading) {
    if (isLoading) {
        button.disabled = true;
        button.classList.add('loading');
        button.textContent = 'Please wait...';
    } else {
        button.disabled = false;
        button.classList.remove('loading');
        button.textContent = button.id.includes('login') ? 'Login' : 'Register';
    }
}

function showMessage(message, type) {
    const messageContainer = document.getElementById('message-container');
    const messageContent = document.getElementById('message-content');
    const closeBtn = document.getElementById('message-close');
    
    messageContent.textContent = message;
    messageContainer.className = 'message-container ' + type;
    messageContainer.style.display = 'block';
    
    // Auto hide after 5 seconds
    setTimeout(() => {
        hideMessage();
    }, 5000);
    
    // Close button handler
    closeBtn.onclick = hideMessage;
}

function hideMessage() {
    const messageContainer = document.getElementById('message-container');
    messageContainer.style.display = 'none';
}