/**
 * Practice 15 - Complete Web Portal JavaScript
 * Modern Interactive Features and Functionality
 */

// Global configuration
const PortalApp = {
    config: {
        apiBaseUrl: '/prac15/',
        csrfToken: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        refreshInterval: 30000, // 30 seconds
        chartColors: {
            primary: '#10B981',
            secondary: '#6EE7B7',
            accent: '#047857',
            warning: '#F59E0B',
            error: '#EF4444',
            info: '#3B82F6'
        }
    },
    
    // Initialize the application
    init() {
        this.bindEvents();
        this.initializeComponents();
        this.startAutoRefresh();
        console.log('Portal Application initialized successfully');
    },
    
    // Bind global event listeners
    bindEvents() {
        // Navigation events
        this.bindNavigationEvents();
        
        // Form events
        this.bindFormEvents();
        
        // Modal events
        this.bindModalEvents();
        
        // Table events
        this.bindTableEvents();
        
        // Search events
        this.bindSearchEvents();
        
        // Notification events
        this.bindNotificationEvents();
    },
    
    // Initialize components
    initializeComponents() {
        this.initCharts();
        this.initDataTables();
        this.initDatePickers();
        this.initTooltips();
        this.loadDashboardData();
    },
    
    // Navigation functionality
    bindNavigationEvents() {
        // Mobile menu toggle
        const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
        const sidebar = document.querySelector('.sidebar');
        
        if (mobileMenuToggle && sidebar) {
            mobileMenuToggle.addEventListener('click', () => {
                sidebar.classList.toggle('open');
            });
        }
        
        // Active navigation highlighting
        this.highlightActiveNavigation();
        
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
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
    },
    
    // Highlight active navigation item
    highlightActiveNavigation() {
        const currentPath = window.location.pathname;
        const navLinks = document.querySelectorAll('.sidebar-nav a');
        
        navLinks.forEach(link => {
            const href = link.getAttribute('href');
            if (href && currentPath.includes(href)) {
                link.classList.add('active');
            } else {
                link.classList.remove('active');
            }
        });
    },
    
    // Form handling
    bindFormEvents() {
        // Form validation
        const forms = document.querySelectorAll('form[data-validate]');
        forms.forEach(form => {
            form.addEventListener('submit', (e) => {
                if (!this.validateForm(form)) {
                    e.preventDefault();
                }
            });
            
            // Real-time validation
            const inputs = form.querySelectorAll('input, select, textarea');
            inputs.forEach(input => {
                input.addEventListener('blur', () => {
                    this.validateField(input);
                });
            });
        });
        
        // AJAX form submissions
        const ajaxForms = document.querySelectorAll('form[data-ajax]');
        ajaxForms.forEach(form => {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                this.submitFormAjax(form);
            });
        });
    },
    
    // Form validation
    validateForm(form) {
        let isValid = true;
        const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
        
        inputs.forEach(input => {
            if (!this.validateField(input)) {
                isValid = false;
            }
        });
        
        return isValid;
    },
    
    // Field validation
    validateField(field) {
        const value = field.value.trim();
        const type = field.type;
        let isValid = true;
        let message = '';
        
        // Required validation
        if (field.hasAttribute('required') && !value) {
            isValid = false;
            message = 'This field is required';
        }
        
        // Email validation
        if (type === 'email' && value) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(value)) {
                isValid = false;
                message = 'Please enter a valid email address';
            }
        }
        
        // Password validation
        if (type === 'password' && value) {
            if (value.length < 6) {
                isValid = false;
                message = 'Password must be at least 6 characters long';
            }
        }
        
        // Show validation feedback
        this.showFieldValidation(field, isValid, message);
        
        return isValid;
    },
    
    // Show field validation feedback
    showFieldValidation(field, isValid, message) {
        // Remove existing feedback
        const existingFeedback = field.parentNode.querySelector('.form-error');
        if (existingFeedback) {
            existingFeedback.remove();
        }
        
        // Update field styling
        field.classList.toggle('is-invalid', !isValid);
        field.classList.toggle('is-valid', isValid && field.value.trim() !== '');
        
        // Add error message if invalid
        if (!isValid && message) {
            const errorDiv = document.createElement('div');
            errorDiv.className = 'form-error';
            errorDiv.textContent = message;
            field.parentNode.appendChild(errorDiv);
        }
    },
    
    // AJAX form submission
    async submitFormAjax(form) {
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        
        try {
            // Show loading state
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="loading"></span> Processing...';
            
            // Prepare form data
            const formData = new FormData(form);
            if (this.config.csrfToken) {
                formData.append('csrf_token', this.config.csrfToken);
            }
            
            // Submit form
            const response = await fetch(form.action || window.location.href, {
                method: form.method || 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.showNotification('Success!', result.message || 'Operation completed successfully', 'success');
                
                // Reset form if specified
                if (form.hasAttribute('data-reset-on-success')) {
                    form.reset();
                }
                
                // Redirect if specified
                if (result.redirect) {
                    setTimeout(() => {
                        window.location.href = result.redirect;
                    }, 1500);
                }
                
                // Refresh data if specified
                if (form.hasAttribute('data-refresh-on-success')) {
                    this.refreshPageData();
                }
            } else {
                this.showNotification('Error', result.message || 'An error occurred', 'error');
                
                // Show field-specific errors
                if (result.errors) {
                    Object.keys(result.errors).forEach(fieldName => {
                        const field = form.querySelector(`[name="${fieldName}"]`);
                        if (field) {
                            this.showFieldValidation(field, false, result.errors[fieldName]);
                        }
                    });
                }
            }
        } catch (error) {
            console.error('Form submission error:', error);
            this.showNotification('Error', 'Network error occurred. Please try again.', 'error');
        } finally {
            // Restore button state
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    },
    
    // Modal functionality
    bindModalEvents() {
        // Modal triggers
        document.addEventListener('click', (e) => {
            if (e.target.matches('[data-modal-target]')) {
                const modalId = e.target.getAttribute('data-modal-target');
                this.openModal(modalId);
            }
            
            if (e.target.matches('[data-modal-close]') || e.target.closest('[data-modal-close]')) {
                this.closeModal();
            }
        });
        
        // Close modal on overlay click
        document.addEventListener('click', (e) => {
            if (e.target.matches('.modal-overlay')) {
                this.closeModal();
            }
        });
        
        // Close modal on escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.closeModal();
            }
        });
    },
    
    // Open modal
    openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('show');
            document.body.style.overflow = 'hidden';
            
            // Focus management
            const firstFocusable = modal.querySelector('input, button, select, textarea, [tabindex]:not([tabindex="-1"])');
            if (firstFocusable) {
                setTimeout(() => firstFocusable.focus(), 100);
            }
        }
    },
    
    // Close modal
    closeModal() {
        const activeModal = document.querySelector('.modal-overlay.show');
        if (activeModal) {
            activeModal.classList.remove('show');
            document.body.style.overflow = '';
        }
    },
    
    // Table functionality
    bindTableEvents() {
        // Sortable tables
        document.querySelectorAll('.table-sortable th[data-sort]').forEach(header => {
            header.addEventListener('click', () => {
                this.sortTable(header);
            });
            header.style.cursor = 'pointer';
        });
        
        // Row selection
        document.querySelectorAll('.table-selectable tbody tr').forEach(row => {
            row.addEventListener('click', () => {
                row.classList.toggle('selected');
                this.updateSelectionCount();
            });
        });
        
        // Select all checkbox
        const selectAllCheckbox = document.querySelector('.select-all-checkbox');
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', (e) => {
                const checkboxes = document.querySelectorAll('.row-checkbox');
                checkboxes.forEach(checkbox => {
                    checkbox.checked = e.target.checked;
                });
                this.updateSelectionCount();
            });
        }
    },
    
    // Sort table
    sortTable(header) {
        const table = header.closest('table');
        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));
        const columnIndex = Array.from(header.parentNode.children).indexOf(header);
        const sortType = header.getAttribute('data-sort');
        const isAscending = header.classList.contains('sort-asc');
        
        // Remove existing sort classes
        table.querySelectorAll('th').forEach(th => {
            th.classList.remove('sort-asc', 'sort-desc');
        });
        
        // Add sort class
        header.classList.add(isAscending ? 'sort-desc' : 'sort-asc');
        
        // Sort rows
        rows.sort((a, b) => {
            const aValue = a.children[columnIndex].textContent.trim();
            const bValue = b.children[columnIndex].textContent.trim();
            
            let result = 0;
            
            if (sortType === 'number') {
                result = parseFloat(aValue) - parseFloat(bValue);
            } else if (sortType === 'date') {
                result = new Date(aValue) - new Date(bValue);
            } else {
                result = aValue.localeCompare(bValue);
            }
            
            return isAscending ? -result : result;
        });
        
        // Reorder rows
        rows.forEach(row => tbody.appendChild(row));
    },
    
    // Update selection count
    updateSelectionCount() {
        const selectedRows = document.querySelectorAll('.table-selectable tbody tr.selected');
        const countElement = document.querySelector('.selection-count');
        if (countElement) {
            countElement.textContent = selectedRows.length;
        }
    },
    
    // Search functionality
    bindSearchEvents() {
        const searchInputs = document.querySelectorAll('[data-search-target]');
        
        searchInputs.forEach(input => {
            const targetSelector = input.getAttribute('data-search-target');
            const searchDelay = parseInt(input.getAttribute('data-search-delay')) || 300;
            
            let searchTimeout;
            
            input.addEventListener('input', () => {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    this.performSearch(input.value, targetSelector);
                }, searchDelay);
            });
        });
    },
    
    // Perform search
    performSearch(query, targetSelector) {
        const targets = document.querySelectorAll(targetSelector);
        const searchTerm = query.toLowerCase().trim();
        
        targets.forEach(target => {
            const text = target.textContent.toLowerCase();
            const isMatch = !searchTerm || text.includes(searchTerm);
            
            target.style.display = isMatch ? '' : 'none';
            target.classList.toggle('search-highlight', isMatch && searchTerm);
        });
        
        // Update search results count
        const visibleTargets = Array.from(targets).filter(target => target.style.display !== 'none');
        const countElement = document.querySelector('.search-results-count');
        if (countElement) {
            countElement.textContent = `${visibleTargets.length} of ${targets.length}`;
        }
    },
    
    // Notification system
    bindNotificationEvents() {
        // Auto-dismiss notifications
        document.querySelectorAll('.notification[data-auto-dismiss]').forEach(notification => {
            const delay = parseInt(notification.getAttribute('data-auto-dismiss')) || 5000;
            setTimeout(() => {
                this.dismissNotification(notification);
            }, delay);
        });
        
        // Manual dismiss
        document.addEventListener('click', (e) => {
            if (e.target.matches('.notification-close')) {
                const notification = e.target.closest('.notification');
                this.dismissNotification(notification);
            }
        });
    },
    
    // Show notification
    showNotification(title, message, type = 'info', duration = 5000) {
        const container = document.querySelector('.notification-container') || this.createNotificationContainer();
        
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <strong class="notification-title">${title}</strong>
                <p class="notification-message">${message}</p>
            </div>
            <button class="notification-close" aria-label="Close notification">
                <i class="fas fa-times"></i>
            </button>
        `;
        
        container.appendChild(notification);
        
        // Animate in
        setTimeout(() => {
            notification.classList.add('show');
        }, 10);
        
        // Auto-dismiss
        if (duration > 0) {
            setTimeout(() => {
                this.dismissNotification(notification);
            }, duration);
        }
    },
    
    // Create notification container
    createNotificationContainer() {
        const container = document.createElement('div');
        container.className = 'notification-container';
        container.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 10000;
            max-width: 400px;
        `;
        document.body.appendChild(container);
        return container;
    },
    
    // Dismiss notification
    dismissNotification(notification) {
        if (notification) {
            notification.classList.add('dismissing');
            setTimeout(() => {
                notification.remove();
            }, 300);
        }
    },
    
    // Chart initialization
    initCharts() {
        // Check if Chart.js is available
        if (typeof Chart === 'undefined') {
            console.warn('Chart.js not loaded. Charts will not be displayed.');
            return;
        }
        
        // Initialize dashboard charts
        this.initDashboardCharts();
        this.initAnalyticsCharts();
    },
    
    // Dashboard charts
    initDashboardCharts() {
        // Users growth chart
        const usersChartCanvas = document.getElementById('usersGrowthChart');
        if (usersChartCanvas) {
            new Chart(usersChartCanvas, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    datasets: [{
                        label: 'Users',
                        data: [10, 15, 25, 30, 45, 60],
                        borderColor: this.config.chartColors.primary,
                        backgroundColor: this.config.chartColors.primary + '20',
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }
        
        // Events distribution chart
        const eventsChartCanvas = document.getElementById('eventsDistributionChart');
        if (eventsChartCanvas) {
            new Chart(eventsChartCanvas, {
                type: 'doughnut',
                data: {
                    labels: ['Academic', 'Cultural', 'Sports', 'Workshop'],
                    datasets: [{
                        data: [30, 25, 20, 25],
                        backgroundColor: [
                            this.config.chartColors.primary,
                            this.config.chartColors.secondary,
                            this.config.chartColors.accent,
                            this.config.chartColors.warning
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }
    },
    
    // Analytics charts
    initAnalyticsCharts() {
        // Student enrollment by department
        const departmentChartCanvas = document.getElementById('departmentChart');
        if (departmentChartCanvas) {
            new Chart(departmentChartCanvas, {
                type: 'bar',
                data: {
                    labels: ['Computer Science', 'Information Technology', 'Electronics', 'Mechanical'],
                    datasets: [{
                        label: 'Students',
                        data: [120, 85, 95, 110],
                        backgroundColor: this.config.chartColors.primary
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }
    },
    
    // Data tables initialization
    initDataTables() {
        // Add responsive classes to tables
        document.querySelectorAll('.table').forEach(table => {
            if (!table.closest('.table-responsive')) {
                const wrapper = document.createElement('div');
                wrapper.className = 'table-responsive';
                table.parentNode.insertBefore(wrapper, table);
                wrapper.appendChild(table);
            }
        });
    },
    
    // Date picker initialization
    initDatePickers() {
        // Simple date picker functionality
        document.querySelectorAll('input[type="date"]').forEach(input => {
            // Add calendar icon
            const wrapper = document.createElement('div');
            wrapper.className = 'date-picker-wrapper';
            input.parentNode.insertBefore(wrapper, input);
            wrapper.appendChild(input);
            
            const icon = document.createElement('i');
            icon.className = 'fas fa-calendar-alt date-picker-icon';
            wrapper.appendChild(icon);
        });
    },
    
    // Tooltip initialization
    initTooltips() {
        document.querySelectorAll('[data-tooltip]').forEach(element => {
            element.addEventListener('mouseenter', (e) => {
                this.showTooltip(e.target, e.target.getAttribute('data-tooltip'));
            });
            
            element.addEventListener('mouseleave', () => {
                this.hideTooltip();
            });
        });
    },
    
    // Show tooltip
    showTooltip(element, text) {
        const tooltip = document.createElement('div');
        tooltip.className = 'tooltip';
        tooltip.textContent = text;
        document.body.appendChild(tooltip);
        
        const rect = element.getBoundingClientRect();
        tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
        tooltip.style.top = rect.top - tooltip.offsetHeight - 5 + 'px';
        
        setTimeout(() => tooltip.classList.add('show'), 10);
    },
    
    // Hide tooltip
    hideTooltip() {
        const tooltip = document.querySelector('.tooltip');
        if (tooltip) {
            tooltip.remove();
        }
    },
    
    // Load dashboard data
    async loadDashboardData() {
        try {
            const response = await fetch(this.config.apiBaseUrl + 'api/dashboard.php');
            if (response.ok) {
                const data = await response.json();
                this.updateDashboardStats(data);
            }
        } catch (error) {
            console.error('Failed to load dashboard data:', error);
        }
    },
    
    // Update dashboard statistics
    updateDashboardStats(data) {
        // Update stat cards
        Object.keys(data.stats || {}).forEach(key => {
            const element = document.querySelector(`[data-stat="${key}"]`);
            if (element) {
                this.animateNumber(element, data.stats[key]);
            }
        });
        
        // Update recent activities
        if (data.activities && data.activities.length > 0) {
            this.updateRecentActivities(data.activities);
        }
    },
    
    // Animate number counting
    animateNumber(element, target) {
        const current = parseInt(element.textContent) || 0;
        const increment = (target - current) / 30;
        let count = current;
        
        const timer = setInterval(() => {
            count += increment;
            if ((increment > 0 && count >= target) || (increment < 0 && count <= target)) {
                count = target;
                clearInterval(timer);
            }
            element.textContent = Math.floor(count);
        }, 50);
    },
    
    // Update recent activities
    updateRecentActivities(activities) {
        const container = document.querySelector('.recent-activities');
        if (container) {
            container.innerHTML = activities.map(activity => `
                <div class="activity-item">
                    <div class="activity-icon">
                        <i class="fas fa-${activity.icon}"></i>
                    </div>
                    <div class="activity-content">
                        <p class="activity-text">${activity.text}</p>
                        <small class="activity-time">${this.formatTimeAgo(activity.timestamp)}</small>
                    </div>
                </div>
            `).join('');
        }
    },
    
    // Format time ago
    formatTimeAgo(timestamp) {
        const now = new Date();
        const time = new Date(timestamp);
        const diff = Math.floor((now - time) / 1000);
        
        if (diff < 60) return 'Just now';
        if (diff < 3600) return `${Math.floor(diff / 60)} minutes ago`;
        if (diff < 86400) return `${Math.floor(diff / 3600)} hours ago`;
        return `${Math.floor(diff / 86400)} days ago`;
    },
    
    // Auto refresh functionality
    startAutoRefresh() {
        if (document.querySelector('.dashboard')) {
            setInterval(() => {
                this.refreshPageData();
            }, this.config.refreshInterval);
        }
    },
    
    // Refresh page data
    async refreshPageData() {
        const refreshElements = document.querySelectorAll('[data-auto-refresh]');
        
        refreshElements.forEach(async (element) => {
            const url = element.getAttribute('data-refresh-url');
            if (url) {
                try {
                    const response = await fetch(url);
                    if (response.ok) {
                        const html = await response.text();
                        element.innerHTML = html;
                    }
                } catch (error) {
                    console.error('Auto-refresh failed:', error);
                }
            }
        });
    },
    
    // Utility functions
    utils: {
        // Debounce function
        debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        },
        
        // Throttle function
        throttle(func, limit) {
            let inThrottle;
            return function() {
                const args = arguments;
                const context = this;
                if (!inThrottle) {
                    func.apply(context, args);
                    inThrottle = true;
                    setTimeout(() => inThrottle = false, limit);
                }
            };
        },
        
        // Format currency
        formatCurrency(amount) {
            return new Intl.NumberFormat('en-US', {
                style: 'currency',
                currency: 'USD'
            }).format(amount);
        },
        
        // Format date
        formatDate(date) {
            return new Intl.DateTimeFormat('en-US', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            }).format(new Date(date));
        },
        
        // Generate random ID
        generateId(prefix = 'id') {
            return `${prefix}_${Math.random().toString(36).substr(2, 9)}`;
        },
        
        // Copy to clipboard
        async copyToClipboard(text) {
            try {
                await navigator.clipboard.writeText(text);
                PortalApp.showNotification('Copied!', 'Text copied to clipboard', 'success', 2000);
            } catch (error) {
                console.error('Failed to copy to clipboard:', error);
                PortalApp.showNotification('Error', 'Failed to copy to clipboard', 'error');
            }
        }
    }
};

// Additional CSS for enhanced functionality
const additionalStyles = `
    /* Notification styles */
    .notification-container {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 10000;
        max-width: 400px;
    }
    
    .notification {
        background: white;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        margin-bottom: 10px;
        padding: 16px;
        display: flex;
        align-items: flex-start;
        gap: 12px;
        transform: translateX(100%);
        transition: transform 0.3s ease, opacity 0.3s ease;
        border-left: 4px solid;
    }
    
    .notification.show {
        transform: translateX(0);
        opacity: 1;
    }
    
    .notification.dismissing {
        transform: translateX(100%);
        opacity: 0;
    }
    
    .notification-success { border-left-color: #10B981; }
    .notification-error { border-left-color: #EF4444; }
    .notification-warning { border-left-color: #F59E0B; }
    .notification-info { border-left-color: #3B82F6; }
    
    .notification-content {
        flex: 1;
    }
    
    .notification-title {
        display: block;
        font-weight: 600;
        margin-bottom: 4px;
    }
    
    .notification-message {
        color: #6B7280;
        font-size: 14px;
        margin: 0;
    }
    
    .notification-close {
        background: none;
        border: none;
        color: #9CA3AF;
        cursor: pointer;
        padding: 4px;
    }
    
    .notification-close:hover {
        color: #6B7280;
    }
    
    /* Tooltip styles */
    .tooltip {
        position: absolute;
        background: rgba(0, 0, 0, 0.9);
        color: white;
        padding: 8px 12px;
        border-radius: 4px;
        font-size: 12px;
        pointer-events: none;
        z-index: 10001;
        opacity: 0;
        transition: opacity 0.2s ease;
    }
    
    .tooltip.show {
        opacity: 1;
    }
    
    .tooltip::after {
        content: '';
        position: absolute;
        top: 100%;
        left: 50%;
        transform: translateX(-50%);
        border: 4px solid transparent;
        border-top-color: rgba(0, 0, 0, 0.9);
    }
    
    /* Table sorting styles */
    .table-sortable th[data-sort] {
        position: relative;
        user-select: none;
    }
    
    .table-sortable th[data-sort]::after {
        content: '↕';
        position: absolute;
        right: 8px;
        opacity: 0.5;
    }
    
    .table-sortable th.sort-asc::after {
        content: '↑';
        opacity: 1;
    }
    
    .table-sortable th.sort-desc::after {
        content: '↓';
        opacity: 1;
    }
    
    /* Search highlight */
    .search-highlight {
        background-color: yellow;
        font-weight: bold;
    }
    
    /* Form validation styles */
    .form-control.is-valid {
        border-color: #10B981;
    }
    
    .form-control.is-invalid {
        border-color: #EF4444;
    }
    
    .form-error {
        color: #EF4444;
        font-size: 12px;
        margin-top: 4px;
    }
    
    /* Loading spinner */
    .loading {
        display: inline-block;
        width: 16px;
        height: 16px;
        border: 2px solid transparent;
        border-top: 2px solid currentColor;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    /* Date picker wrapper */
    .date-picker-wrapper {
        position: relative;
    }
    
    .date-picker-icon {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #9CA3AF;
        pointer-events: none;
    }
`;

// Inject additional styles
const styleSheet = document.createElement('style');
styleSheet.textContent = additionalStyles;
document.head.appendChild(styleSheet);

// Initialize the application when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    PortalApp.init();
});

// Export for global access
window.PortalApp = PortalApp;