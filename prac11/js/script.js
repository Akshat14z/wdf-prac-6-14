/**
 * Practice 11 - Student Data Management System
 * JavaScript functionality for enhanced user experience
 */

// DOM Ready handler
document.addEventListener('DOMContentLoaded', function() {
    initializeComponents();
});

// Initialize all JavaScript components
function initializeComponents() {
    initializeSearch();
    initializeFilters();
    initializeForms();
    initializeAnimations();
    initializeTooltips();
    initializeKeyboardShortcuts();
}

// Search functionality
function initializeSearch() {
    const searchInputs = document.querySelectorAll('input[type="text"][name*="search"], input[name="q"]');
    
    searchInputs.forEach(input => {
        // Auto-focus on search inputs
        if (input.hasAttribute('autofocus') || input.id === 'quick_search') {
            input.focus();
        }
        
        // Real-time search validation
        input.addEventListener('input', function() {
            const query = this.value.trim();
            validateSearchInput(this, query);
        });
        
        // Search suggestions (placeholder for future AJAX implementation)
        input.addEventListener('keyup', function(e) {
            if (e.key === 'Enter') {
                const form = this.closest('form');
                if (form) {
                    form.submit();
                }
            }
        });
    });
}

// Filter functionality
function initializeFilters() {
    const departmentSelects = document.querySelectorAll('select[name="department"]');
    const statusSelects = document.querySelectorAll('select[name="status"]');
    
    // Auto-submit on filter change
    [...departmentSelects, ...statusSelects].forEach(select => {
        select.addEventListener('change', function() {
            if (this.form && this.form.method.toLowerCase() === 'get') {
                this.form.submit();
            }
        });
    });
    
    // Course filtering based on department
    const departmentCourseFilters = document.querySelectorAll('#department_id');
    departmentCourseFilters.forEach(deptSelect => {
        const courseSelect = document.getElementById('course_id');
        if (courseSelect) {
            setupCourseFiltering(deptSelect, courseSelect);
        }
    });
}

// Course filtering functionality
function setupCourseFiltering(departmentSelect, courseSelect) {
    const allCourses = Array.from(courseSelect.options);
    
    function filterCourses() {
        const selectedDepartment = departmentSelect.value;
        
        // Clear current options except the first one
        courseSelect.innerHTML = '<option value="">Select Course</option>';
        
        if (selectedDepartment) {
            // Add courses for selected department
            allCourses.forEach(option => {
                if (option.dataset.department === selectedDepartment && option.value) {
                    courseSelect.appendChild(option.cloneNode(true));
                }
            });
        } else {
            // Add all courses if no department selected
            allCourses.forEach(option => {
                if (option.value) {
                    courseSelect.appendChild(option.cloneNode(true));
                }
            });
        }
    }
    
    departmentSelect.addEventListener('change', filterCourses);
    
    // Initialize on page load
    filterCourses();
}

// Form enhancements
function initializeForms() {
    // Student ID auto-generation
    const firstNameInput = document.getElementById('first_name');
    const lastNameInput = document.getElementById('last_name');
    const studentIdInput = document.getElementById('student_id');
    
    if (firstNameInput && lastNameInput && studentIdInput) {
        function generateStudentId() {
            if (!studentIdInput.value && firstNameInput.value && lastNameInput.value) {
                const firstName = firstNameInput.value.substring(0, 2).toUpperCase();
                const lastName = lastNameInput.value.substring(0, 2).toUpperCase();
                const random = Math.floor(Math.random() * 1000).toString().padStart(3, '0');
                studentIdInput.value = firstName + lastName + random;
            }
        }
        
        firstNameInput.addEventListener('blur', generateStudentId);
        lastNameInput.addEventListener('blur', generateStudentId);
    }
    
    // Graduation year auto-calculation
    const admissionDateInput = document.getElementById('admission_date');
    const graduationYearInput = document.getElementById('graduation_year');
    
    if (admissionDateInput && graduationYearInput) {
        admissionDateInput.addEventListener('change', function() {
            if (this.value && !graduationYearInput.value) {
                const admissionYear = new Date(this.value).getFullYear();
                graduationYearInput.value = admissionYear + 4; // Assuming 4-year program
            }
        });
    }
    
    // Form validation
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
            }
        });
    });
    
    // Real-time field validation
    const requiredFields = document.querySelectorAll('[required]');
    requiredFields.forEach(field => {
        field.addEventListener('blur', function() {
            validateField(this);
        });
        
        field.addEventListener('input', function() {
            if (this.classList.contains('error')) {
                validateField(this);
            }
        });
    });
}

// Form validation functions
function validateForm(form) {
    const requiredFields = form.querySelectorAll('[required]');
    let isValid = true;
    
    requiredFields.forEach(field => {
        if (!validateField(field)) {
            isValid = false;
        }
    });
    
    if (!isValid) {
        showAlert('Please fill in all required fields.', 'error');
    }
    
    return isValid;
}

function validateField(field) {
    const value = field.value.trim();
    let isValid = true;
    let message = '';
    
    // Required field validation
    if (field.hasAttribute('required') && !value) {
        isValid = false;
        message = 'This field is required.';
    }
    
    // Email validation
    if (field.type === 'email' && value && !isValidEmail(value)) {
        isValid = false;
        message = 'Please enter a valid email address.';
    }
    
    // Number range validation
    if (field.type === 'number' && value) {
        const min = parseFloat(field.min);
        const max = parseFloat(field.max);
        const numValue = parseFloat(value);
        
        if (!isNaN(min) && numValue < min) {
            isValid = false;
            message = `Value must be at least ${min}.`;
        }
        
        if (!isNaN(max) && numValue > max) {
            isValid = false;
            message = `Value must not exceed ${max}.`;
        }
    }
    
    // Date validation
    if (field.type === 'date' && value) {
        const date = new Date(value);
        if (isNaN(date.getTime())) {
            isValid = false;
            message = 'Please enter a valid date.';
        }
    }
    
    // Update field styling
    if (isValid) {
        field.classList.remove('error');
        field.style.borderColor = '';
        removeFieldError(field);
    } else {
        field.classList.add('error');
        field.style.borderColor = '#A08963';
        showFieldError(field, message);
    }
    
    return isValid;
}

function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

function showFieldError(field, message) {
    removeFieldError(field);
    
    const errorDiv = document.createElement('div');
    errorDiv.className = 'field-error';
    errorDiv.style.cssText = 'color: #A08963; font-size: 0.8rem; margin-top: 0.25rem;';
    errorDiv.textContent = message;
    
    field.parentNode.appendChild(errorDiv);
}

function removeFieldError(field) {
    const errorDiv = field.parentNode.querySelector('.field-error');
    if (errorDiv) {
        errorDiv.remove();
    }
}

// Search input validation
function validateSearchInput(input, query) {
    if (query.length >= 2) {
        input.style.borderColor = '#706D54';
        // Here you could implement real-time search suggestions
    } else if (query.length === 1) {
        input.style.borderColor = '#C9B194';
    } else {
        input.style.borderColor = '';
    }
}

// Animation effects
function initializeAnimations() {
    // Smooth scrolling for internal links
    document.querySelectorAll('a[href^="#"]').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // Loading animation for forms
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function() {
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn && !submitBtn.disabled) {
                showLoadingState(submitBtn);
            }
        });
    });
    
    // Fade in animation for cards
    const cards = document.querySelectorAll('.stat-card, .action-card, .dept-card');
    observeElements(cards, 'fadeInUp');
    
    // Hover effects for interactive elements
    addHoverEffects();
}

function showLoadingState(button) {
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
    button.disabled = true;
    
    // Re-enable after 3 seconds (fallback)
    setTimeout(() => {
        button.innerHTML = originalText;
        button.disabled = false;
    }, 3000);
}

function observeElements(elements, animationClass) {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add(animationClass);
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });
    
    elements.forEach(element => {
        observer.observe(element);
    });
}

function addHoverEffects() {
    // Table row hover effects
    const tableRows = document.querySelectorAll('.data-table tbody tr');
    tableRows.forEach(row => {
        row.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.01)';
            this.style.transition = 'transform 0.2s ease';
        });
        
        row.addEventListener('mouseleave', function() {
            this.style.transform = '';
        });
    });
}

// Tooltip functionality
function initializeTooltips() {
    const elementsWithTitles = document.querySelectorAll('[title]');
    
    elementsWithTitles.forEach(element => {
        let tooltip = null;
        
        element.addEventListener('mouseenter', function() {
            const title = this.getAttribute('title');
            if (title) {
                tooltip = createTooltip(title);
                document.body.appendChild(tooltip);
                positionTooltip(tooltip, this);
            }
        });
        
        element.addEventListener('mouseleave', function() {
            if (tooltip) {
                tooltip.remove();
                tooltip = null;
            }
        });
        
        element.addEventListener('mousemove', function(e) {
            if (tooltip) {
                tooltip.style.left = e.pageX + 10 + 'px';
                tooltip.style.top = e.pageY + 10 + 'px';
            }
        });
    });
}

function createTooltip(text) {
    const tooltip = document.createElement('div');
    tooltip.className = 'custom-tooltip';
    tooltip.textContent = text;
    tooltip.style.cssText = `
        position: absolute;
        background: #706D54;
        color: white;
        padding: 0.5rem 0.75rem;
        border-radius: 6px;
        font-size: 0.8rem;
        z-index: 1000;
        pointer-events: none;
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        max-width: 200px;
        word-wrap: break-word;
    `;
    return tooltip;
}

function positionTooltip(tooltip, element) {
    const rect = element.getBoundingClientRect();
    tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
    tooltip.style.top = rect.top - tooltip.offsetHeight - 10 + 'px';
}

// Keyboard shortcuts
function initializeKeyboardShortcuts() {
    document.addEventListener('keydown', function(e) {
        // Ctrl/Cmd + K to focus search
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            const searchInput = document.querySelector('input[name="q"], input[name="search"]');
            if (searchInput) {
                searchInput.focus();
                searchInput.select();
            }
        }
        
        // Ctrl/Cmd + N to add new student
        if ((e.ctrlKey || e.metaKey) && e.key === 'n') {
            e.preventDefault();
            window.location.href = 'add_student.php';
        }
        
        // Escape to clear search
        if (e.key === 'Escape') {
            const searchInput = document.querySelector('input[name="q"], input[name="search"]');
            if (searchInput && searchInput === document.activeElement) {
                searchInput.value = '';
                searchInput.blur();
            }
        }
    });
}

// Alert system
function showAlert(message, type = 'info', duration = 5000) {
    const alert = document.createElement('div');
    alert.className = `alert alert-${type} floating-alert`;
    alert.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-triangle' : 'info-circle'}"></i>
        ${message}
        <button type="button" class="alert-close" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    alert.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 1050;
        max-width: 400px;
        animation: slideIn 0.3s ease-out;
    `;
    
    document.body.appendChild(alert);
    
    // Auto-remove after duration
    setTimeout(() => {
        if (alert.parentElement) {
            alert.style.animation = 'slideOut 0.3s ease-in';
            setTimeout(() => alert.remove(), 300);
        }
    }, duration);
}

// Utility functions
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function formatDate(dateString, format = 'short') {
    const date = new Date(dateString);
    const options = format === 'long' ? 
        { year: 'numeric', month: 'long', day: 'numeric' } :
        { year: 'numeric', month: 'short', day: 'numeric' };
    return date.toLocaleDateString('en-US', options);
}

function formatGPA(gpa) {
    return parseFloat(gpa).toFixed(2);
}

// Export functions for global use
window.StudentDataSystem = {
    showAlert,
    validateForm,
    formatDate,
    formatGPA,
    debounce
};

// CSS animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
    
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .fadeInUp {
        animation: fadeInUp 0.6s ease-out;
    }
    
    .alert-close {
        background: none;
        border: none;
        color: inherit;
        cursor: pointer;
        margin-left: auto;
        padding: 0;
        font-size: 0.8rem;
    }
    
    .floating-alert {
        box-shadow: 0 8px 32px rgba(0,0,0,0.2);
    }
    
    .field-error {
        animation: fadeInUp 0.3s ease-out;
    }
`;
document.head.appendChild(style);

// Initialize on page load
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeComponents);
} else {
    initializeComponents();
}