// Event Management CRUD System - Enhanced JavaScript Functions
document.addEventListener('DOMContentLoaded', function() {
    
    // Initialize page animations
    initializeAnimations();
    
    // Auto-hide alerts after 5 seconds with smooth animation
    const alerts = document.querySelectorAll('.alert-success');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-20px)';
            setTimeout(function() {
                if (alert.parentNode) {
                    alert.parentNode.removeChild(alert);
                }
            }, 300);
        }, 5000);
    });
    
    // Enhanced confirm delete actions
    const deleteLinks = document.querySelectorAll('a[href*="delete_event.php"]');
    deleteLinks.forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            const eventTitle = link.closest('tr')?.querySelector('strong')?.textContent || 'this event';
            
            if (showCustomConfirm(`Are you sure you want to delete "${eventTitle}"?`, 'This action cannot be undone.')) {
                // Add loading state
                link.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Deleting...';
                link.style.pointerEvents = 'none';
                
                // Proceed with deletion after a brief delay for UX
                setTimeout(() => {
                    window.location.href = link.href;
                }, 1000);
            }
        });
    });
    
    // Enhanced form validation for create/edit event forms
    const eventForm = document.querySelector('form[method="POST"]');
    if (eventForm) {
        eventForm.addEventListener('submit', function(e) {
            if (!validateEventForm()) {
                e.preventDefault();
            }
        });
        
        // Real-time validation
        const inputs = eventForm.querySelectorAll('.form-control');
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                validateField(this);
            });
            
            input.addEventListener('input', function() {
                clearFieldError(this);
            });
        });
    }
    
    // Enhanced search functionality with debounce
    const searchInput = document.getElementById('search');
    if (searchInput) {
        let searchTimeout;
        
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const searchValue = this.value;
            
            // Add loading state for longer searches
            if (searchValue.length >= 2) {
                searchTimeout = setTimeout(function() {
                    // Could implement AJAX search here for real-time results
                    console.log('Searching for:', searchValue);
                    // For now, we'll just add some visual feedback
                    searchInput.style.borderColor = 'var(--accent-color)';
                }, 500);
            }
        });
        
        // Clear search functionality
        const searchForm = searchInput.closest('form');
        if (searchForm) {
            const clearBtn = document.createElement('button');
            clearBtn.type = 'button';
            clearBtn.className = 'btn btn-secondary';
            clearBtn.innerHTML = '<i class="fas fa-times"></i> Clear';
            clearBtn.style.display = searchInput.value ? 'inline-flex' : 'none';
            
            clearBtn.addEventListener('click', function() {
                searchInput.value = '';
                searchForm.submit();
            });
            
            searchInput.addEventListener('input', function() {
                clearBtn.style.display = this.value ? 'inline-flex' : 'none';
            });
            
            const submitBtn = searchForm.querySelector('button[type="submit"]');
            if (submitBtn && submitBtn.parentNode) {
                submitBtn.parentNode.insertBefore(clearBtn, submitBtn.nextSibling);
            }
        }
    }
    
    // Smooth scroll for anchor links
    const anchorLinks = document.querySelectorAll('a[href^="#"]');
    anchorLinks.forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(link.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // Add loading state to form submit buttons
    const submitButtons = document.querySelectorAll('button[type="submit"]');
    submitButtons.forEach(function(button) {
        const form = button.closest('form');
        if (form) {
            const originalText = button.innerHTML;
            form.addEventListener('submit', function(e) {
                if (form.checkValidity()) {
                    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
                    button.disabled = true;
                    button.style.opacity = '0.7';
                    
                    // Re-enable after timeout as fallback
                    setTimeout(() => {
                        button.innerHTML = originalText;
                        button.disabled = false;
                        button.style.opacity = '1';
                    }, 10000);
                }
            });
        }
    });
    
    // Auto-resize textareas with animation
    const textareas = document.querySelectorAll('textarea');
    textareas.forEach(function(textarea) {
        // Set initial height
        autoResize(textarea);
        
        textarea.addEventListener('input', function() {
            autoResize(this);
        });
    });
    
    // Enhanced table interactions
    const tableRows = document.querySelectorAll('.table tbody tr');
    tableRows.forEach(function(row, index) {
        // Add entrance animation delay
        row.style.animation = `fadeIn 0.6s ease-in-out ${index * 0.1}s both`;
        
        row.addEventListener('mouseenter', function() {
            this.style.transform = 'translateX(8px)';
        });
        
        row.addEventListener('mouseleave', function() {
            this.style.transform = '';
        });
    });
    
    // Status badge tooltips and interactions
    const statusBadges = document.querySelectorAll('.status-badge');
    statusBadges.forEach(function(badge) {
        const status = badge.textContent.trim().toLowerCase();
        let tooltip = '';
        
        if (status.includes('open')) {
            tooltip = 'This event is currently accepting registrations';
        } else if (status.includes('closed')) {
            tooltip = 'This event is no longer accepting registrations';
        }
        
        if (tooltip) {
            badge.setAttribute('title', tooltip);
            badge.style.cursor = 'help';
        }
    });
    
    // Click-to-copy functionality for event IDs
    const eventIds = document.querySelectorAll('td:first-child');
    eventIds.forEach(function(cell) {
        if (cell.textContent.match(/^\d+$/)) {
            cell.style.cursor = 'pointer';
            cell.setAttribute('title', 'Click to copy Event ID');
            
            cell.addEventListener('click', function() {
                copyToClipboard(cell.textContent);
                showNotification('Event ID copied to clipboard!', 'success');
                
                // Visual feedback
                cell.style.background = 'var(--success-light)';
                setTimeout(() => {
                    cell.style.background = '';
                }, 500);
            });
        }
    });
    
    // Add keyboard navigation
    document.addEventListener('keydown', function(e) {
        // Ctrl/Cmd + K for search focus
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            const searchInput = document.getElementById('search');
            if (searchInput) {
                searchInput.focus();
                searchInput.select();
            }
        }
        
        // Escape to clear search
        if (e.key === 'Escape') {
            const searchInput = document.getElementById('search');
            if (searchInput && document.activeElement === searchInput) {
                searchInput.value = '';
                searchInput.blur();
            }
        }
    });
    
    // Add progress indicator for forms
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        const requiredFields = form.querySelectorAll('[required]');
        if (requiredFields.length > 0) {
            addProgressIndicator(form, requiredFields);
        }
    });
    
    // Initialize tooltips for help text
    initializeTooltips();
    
    // Add scroll-to-top functionality
    addScrollToTop();
});

// Initialize page animations
function initializeAnimations() {
    const animatedElements = document.querySelectorAll('.card, .stat-card, .alert');
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('fade-in');
                observer.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    });
    
    animatedElements.forEach(el => {
        observer.observe(el);
    });
}

// Enhanced form validation
function validateEventForm() {
    const title = document.getElementById('title');
    const eventDate = document.getElementById('event_date');
    const eventTime = document.getElementById('event_time');
    const location = document.getElementById('location');
    const organizer = document.getElementById('organizer');
    const capacity = document.getElementById('capacity');
    const status = document.getElementById('status');
    
    let isValid = true;
    
    // Clear previous errors
    clearAllFieldErrors();
    
    // Validate title
    if (title && title.value.trim().length < 3) {
        showFieldError(title, 'Event title must be at least 3 characters long');
        isValid = false;
    }
    
    // Validate date (must be today or future)
    if (eventDate && eventDate.value) {
        const selectedDate = new Date(eventDate.value);
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        
        if (selectedDate < today) {
            showFieldError(eventDate, 'Event date cannot be in the past');
            isValid = false;
        }
    }
    
    // Validate capacity
    if (capacity && (capacity.value < 1 || capacity.value > 10000)) {
        showFieldError(capacity, 'Capacity must be between 1 and 10,000');
        isValid = false;
    }
    
    // Validate time for today's events
    if (eventDate && eventTime && eventDate.value) {
        const selectedDate = new Date(eventDate.value);
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        
        if (selectedDate.getTime() === today.getTime()) {
            const currentTime = new Date();
            const [hours, minutes] = eventTime.value.split(':');
            const eventDateTime = new Date();
            eventDateTime.setHours(parseInt(hours), parseInt(minutes), 0, 0);
            
            if (eventDateTime < currentTime) {
                showFieldError(eventTime, 'Event time cannot be in the past for today\'s date');
                isValid = false;
            }
        }
    }
    
    if (!isValid) {
        showNotification('Please correct the errors below', 'error');
    }
    
    return isValid;
}

// Validate individual field
function validateField(field) {
    clearFieldError(field);
    
    if (field.hasAttribute('required') && !field.value.trim()) {
        showFieldError(field, 'This field is required');
        return false;
    }
    
    return true;
}

// Helper functions

// Clear all field errors
function clearAllFieldErrors() {
    const existingErrors = document.querySelectorAll('.field-error');
    existingErrors.forEach(error => error.remove());
    
    const inputs = document.querySelectorAll('.form-control');
    inputs.forEach(input => {
        input.style.borderColor = '';
        input.classList.remove('error');
    });
}

// Clear individual field error
function clearFieldError(field) {
    field.style.borderColor = '';
    field.classList.remove('error');
    
    const existingError = field.parentNode.querySelector('.field-error');
    if (existingError) {
        existingError.remove();
    }
}

// Show field error with enhanced styling
function showFieldError(field, message) {
    clearFieldError(field);
    
    field.style.borderColor = 'var(--danger)';
    field.classList.add('error');
    
    const errorDiv = document.createElement('div');
    errorDiv.className = 'field-error';
    errorDiv.style.cssText = `
        color: var(--danger);
        font-size: 0.875rem;
        margin-top: 0.5rem;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        animation: fadeIn 0.3s ease;
    `;
    errorDiv.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${message}`;
    
    field.parentNode.appendChild(errorDiv);
    
    // Focus on the field and scroll into view
    field.focus();
    field.scrollIntoView({ behavior: 'smooth', block: 'center' });
    
    // Shake animation
    field.style.animation = 'shake 0.5s ease-in-out';
    setTimeout(() => {
        field.style.animation = '';
    }, 500);
}

// Enhanced notification system
function showNotification(message, type = 'info', duration = 4000) {
    // Remove existing notifications
    const existingNotifications = document.querySelectorAll('.notification');
    existingNotifications.forEach(notif => notif.remove());
    
    const notification = document.createElement('div');
    notification.className = `notification alert alert-${type}`;
    notification.style.cssText = `
        position: fixed;
        top: 2rem;
        right: 2rem;
        z-index: 10000;
        max-width: 400px;
        min-width: 300px;
        border-radius: var(--border-radius-md);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        box-shadow: var(--shadow-xl);
        animation: slideInRight 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        border: 1px solid rgba(255, 255, 255, 0.2);
    `;
    
    const icons = {
        success: 'check-circle',
        error: 'exclamation-circle',
        warning: 'exclamation-triangle',
        info: 'info-circle'
    };
    
    notification.innerHTML = `
        <i class="fas fa-${icons[type] || 'info-circle'}"></i>
        <span>${message}</span>
        <button type="button" onclick="this.parentElement.remove()" style="
            background: none;
            border: none;
            color: inherit;
            cursor: pointer;
            font-size: 1.2em;
            margin-left: auto;
            opacity: 0.7;
            transition: opacity 0.2s;
        " onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.7'">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    document.body.appendChild(notification);
    
    // Auto-remove after duration
    setTimeout(() => {
        if (notification.parentNode) {
            notification.style.animation = 'slideOutRight 0.4s cubic-bezier(0.4, 0, 0.2, 1)';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.remove();
                }
            }, 400);
        }
    }, duration);
}

// Enhanced copy to clipboard
function copyToClipboard(text) {
    if (navigator.clipboard && window.isSecureContext) {
        return navigator.clipboard.writeText(text);
    } else {
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = text;
        textArea.style.cssText = 'position:fixed;opacity:0;top:-999px;left:-999px;';
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        
        try {
            document.execCommand('copy');
        } catch (err) {
            console.error('Failed to copy text: ', err);
        }
        
        document.body.removeChild(textArea);
    }
}

// Custom confirm dialog
function showCustomConfirm(title, message) {
    return new Promise((resolve) => {
        const modal = document.createElement('div');
        modal.className = 'custom-modal';
        modal.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10000;
            backdrop-filter: blur(5px);
            animation: fadeIn 0.3s ease;
        `;
        
        const dialog = document.createElement('div');
        dialog.style.cssText = `
            background: var(--white);
            border-radius: var(--border-radius-lg);
            padding: 2rem;
            max-width: 400px;
            margin: 1rem;
            box-shadow: var(--shadow-xl);
            animation: scaleIn 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        `;
        
        dialog.innerHTML = `
            <div style="text-align: center;">
                <div style="color: var(--danger); font-size: 3rem; margin-bottom: 1rem;">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <h3 style="color: var(--text-dark); margin-bottom: 1rem; font-family: 'Poppins', sans-serif;">
                    ${title}
                </h3>
                <p style="color: var(--text-medium); margin-bottom: 2rem; line-height: 1.6;">
                    ${message}
                </p>
                <div style="display: flex; gap: 1rem; justify-content: center;">
                    <button class="btn btn-secondary" id="cancel-btn">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button class="btn btn-danger" id="confirm-btn">
                        <i class="fas fa-check"></i> Delete
                    </button>
                </div>
            </div>
        `;
        
        modal.appendChild(dialog);
        document.body.appendChild(modal);
        
        // Focus on cancel button by default
        const cancelBtn = dialog.querySelector('#cancel-btn');
        const confirmBtn = dialog.querySelector('#confirm-btn');
        
        cancelBtn.focus();
        
        cancelBtn.addEventListener('click', () => {
            modal.remove();
            resolve(false);
        });
        
        confirmBtn.addEventListener('click', () => {
            modal.remove();
            resolve(true);
        });
        
        // Close on Escape key
        modal.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                modal.remove();
                resolve(false);
            }
        });
        
        // Close on background click
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.remove();
                resolve(false);
            }
        });
    });
}

// Auto-resize textarea function
function autoResize(textarea) {
    textarea.style.height = 'auto';
    textarea.style.height = (textarea.scrollHeight) + 'px';
}

// Add progress indicator for forms
function addProgressIndicator(form, requiredFields) {
    const progressContainer = document.createElement('div');
    progressContainer.className = 'form-progress';
    progressContainer.style.cssText = `
        margin-bottom: 1.5rem;
        padding: 1rem;
        background: var(--secondary-light);
        border-radius: var(--border-radius-md);
        border-left: 4px solid var(--primary-color);
    `;
    
    const progressText = document.createElement('div');
    progressText.style.cssText = `
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--text-medium);
        margin-bottom: 0.5rem;
    `;
    
    const progressBar = document.createElement('div');
    progressBar.style.cssText = `
        height: 6px;
        background: var(--tertiary-color);
        border-radius: 3px;
        overflow: hidden;
    `;
    
    const progressFill = document.createElement('div');
    progressFill.style.cssText = `
        height: 100%;
        background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
        width: 0%;
        transition: width 0.3s ease;
        border-radius: 3px;
    `;
    
    progressBar.appendChild(progressFill);
    progressContainer.appendChild(progressText);
    progressContainer.appendChild(progressBar);
    
    // Insert at the beginning of the form
    form.insertBefore(progressContainer, form.firstChild);
    
    function updateProgress() {
        const filledFields = Array.from(requiredFields).filter(field => 
            field.value.trim() !== ''
        ).length;
        
        const percentage = (filledFields / requiredFields.length) * 100;
        progressFill.style.width = percentage + '%';
        progressText.textContent = `Form Progress: ${filledFields}/${requiredFields.length} required fields completed`;
        
        if (percentage === 100) {
            progressText.innerHTML = '<i class="fas fa-check-circle"></i> All required fields completed!';
            progressText.style.color = 'var(--success)';
        }
    }
    
    // Update progress on field changes
    requiredFields.forEach(field => {
        field.addEventListener('input', updateProgress);
        field.addEventListener('change', updateProgress);
    });
    
    // Initial update
    updateProgress();
}

// Initialize tooltips
function initializeTooltips() {
    const elements = document.querySelectorAll('[title]');
    
    elements.forEach(element => {
        let timeout;
        let tooltip;
        
        element.addEventListener('mouseenter', function(e) {
            const title = this.getAttribute('title');
            if (!title) return;
            
            // Remove default tooltip
            this.setAttribute('data-title', title);
            this.removeAttribute('title');
            
            timeout = setTimeout(() => {
                tooltip = document.createElement('div');
                tooltip.className = 'custom-tooltip';
                tooltip.textContent = title;
                tooltip.style.cssText = `
                    position: absolute;
                    background: var(--text-dark);
                    color: var(--white);
                    padding: 0.5rem 0.75rem;
                    border-radius: var(--border-radius-sm);
                    font-size: 0.8rem;
                    z-index: 10000;
                    pointer-events: none;
                    animation: fadeIn 0.2s ease;
                    box-shadow: var(--shadow-md);
                `;
                
                document.body.appendChild(tooltip);
                
                const rect = this.getBoundingClientRect();
                tooltip.style.left = rect.left + rect.width / 2 - tooltip.offsetWidth / 2 + 'px';
                tooltip.style.top = rect.top - tooltip.offsetHeight - 8 + 'px';
            }, 500);
        });
        
        element.addEventListener('mouseleave', function() {
            clearTimeout(timeout);
            if (tooltip) {
                tooltip.remove();
                tooltip = null;
            }
            
            // Restore title attribute
            const dataTitle = this.getAttribute('data-title');
            if (dataTitle) {
                this.setAttribute('title', dataTitle);
                this.removeAttribute('data-title');
            }
        });
    });
}

// Add scroll to top functionality
function addScrollToTop() {
    const scrollBtn = document.createElement('button');
    scrollBtn.innerHTML = '<i class="fas fa-chevron-up"></i>';
    scrollBtn.className = 'scroll-to-top';
    scrollBtn.style.cssText = `
        position: fixed;
        bottom: 2rem;
        right: 2rem;
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
        color: var(--white);
        border: none;
        cursor: pointer;
        font-size: 1.2rem;
        box-shadow: var(--shadow-lg);
        z-index: 1000;
        opacity: 0;
        transform: scale(0);
        transition: all 0.3s ease;
    `;
    
    document.body.appendChild(scrollBtn);
    
    // Show/hide based on scroll position
    window.addEventListener('scroll', () => {
        if (window.pageYOffset > 300) {
            scrollBtn.style.opacity = '1';
            scrollBtn.style.transform = 'scale(1)';
        } else {
            scrollBtn.style.opacity = '0';
            scrollBtn.style.transform = 'scale(0)';
        }
    });
    
    // Scroll to top on click
    scrollBtn.addEventListener('click', () => {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
}

// Add CSS animations
const additionalStyles = document.createElement('style');
additionalStyles.textContent = `
    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOutRight {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
    
    @keyframes scaleIn {
        from {
            transform: scale(0.7);
            opacity: 0;
        }
        to {
            transform: scale(1);
            opacity: 1;
        }
    }
    
    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        25% { transform: translateX(-5px); }
        75% { transform: translateX(5px); }
    }
    
    .field-error {
        animation: fadeIn 0.3s ease;
    }
    
    .form-control.error {
        animation: shake 0.5s ease-in-out;
    }
    
    .notification {
        animation: slideInRight 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    @media (max-width: 768px) {
        .notification {
            left: 1rem;
            right: 1rem;
            max-width: none;
            min-width: auto;
        }
        
        .scroll-to-top {
            bottom: 1rem !important;
            right: 1rem !important;
            width: 45px !important;
            height: 45px !important;
        }
        
        .custom-modal .btn {
            flex: 1;
        }
    }
`;
document.head.appendChild(additionalStyles);