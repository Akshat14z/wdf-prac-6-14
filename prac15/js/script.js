/**
 * Practice 15 - Complete Web Portal JavaScript
 * Main application functionality and interactions
 */

class WebPortal {
    constructor() {
        this.init();
    }

    init() {
        this.bindEvents();
        this.initializeComponents();
        this.setupCSRFProtection();
        this.startSessionChecker();
    }

    bindEvents() {
        // Form submissions
        document.addEventListener('submit', (e) => {
            if (e.target.classList.contains('ajax-form')) {
                e.preventDefault();
                this.handleAjaxForm(e.target);
            }
        });

        // Confirmation dialogs
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('confirm-action')) {
                e.preventDefault();
                this.showConfirmDialog(e.target);
            }
        });

        // Dropdown toggles
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('dropdown-toggle')) {
                e.preventDefault();
                this.toggleDropdown(e.target);
            }
        });

        // Auto-save forms
        document.addEventListener('input', (e) => {
            if (e.target.closest('.auto-save-form')) {
                this.debounce(this.autoSaveForm.bind(this), 1000)(e.target.closest('form'));
            }
        });

        // File upload preview
        document.addEventListener('change', (e) => {
            if (e.target.type === 'file' && e.target.classList.contains('preview-upload')) {
                this.previewFile(e.target);
            }
        });

        // Search functionality
        document.addEventListener('input', (e) => {
            if (e.target.classList.contains('search-input')) {
                this.debounce(this.performSearch.bind(this), 300)(e.target);
            }
        });

        // Close dropdowns when clicking outside
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.dropdown')) {
                this.closeAllDropdowns();
            }
        });

        // Notification close buttons
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('notification-close')) {
                this.closeNotification(e.target.closest('.notification'));
            }
        });
    }

    initializeComponents() {
        // Initialize tooltips
        this.initTooltips();
        
        // Initialize charts if chart library is loaded
        if (typeof Chart !== 'undefined') {
            this.initCharts();
        }
        
        // Initialize data tables
        this.initDataTables();
        
        // Load notifications
        this.loadNotifications();
        
        // Initialize real-time features
        this.initRealTimeFeatures();
    }

    setupCSRFProtection() {
        // Add CSRF token to all AJAX requests
        const token = document.querySelector('meta[name="csrf-token"]');
        if (token) {
            this.csrfToken = token.getAttribute('content');
        }
    }

    startSessionChecker() {
        // Check session every 5 minutes
        setInterval(() => {
            this.checkSession();
        }, 300000);
    }

    // Form handling
    async handleAjaxForm(form) {
        const formData = new FormData(form);
        const submitBtn = form.querySelector('[type="submit"]');
        const originalText = submitBtn.textContent;

        try {
            // Show loading state
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner"></span> Processing...';

            // Add CSRF token
            if (this.csrfToken) {
                formData.append('csrf_token', this.csrfToken);
            }

            const response = await fetch(form.action, {
                method: form.method || 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const result = await response.json();

            if (result.success) {
                this.showNotification(result.message || 'Operation completed successfully', 'success');
                
                // Handle redirects
                if (result.redirect) {
                    setTimeout(() => {
                        window.location.href = result.redirect;
                    }, 1000);
                }
                
                // Reset form if specified
                if (result.reset_form) {
                    form.reset();
                }
                
                // Reload data if specified
                if (result.reload_data) {
                    this.reloadPageData();
                }
            } else {
                this.showNotification(result.message || 'An error occurred', 'error');
                
                // Show field errors
                if (result.errors) {
                    this.showFieldErrors(form, result.errors);
                }
            }
        } catch (error) {
            console.error('AJAX form error:', error);
            this.showNotification('Network error occurred', 'error');
        } finally {
            // Restore button state
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        }
    }

    showFieldErrors(form, errors) {
        // Clear previous errors
        form.querySelectorAll('.field-error').forEach(el => el.remove());
        form.querySelectorAll('.form-control.error').forEach(el => el.classList.remove('error'));

        // Show new errors
        Object.entries(errors).forEach(([field, message]) => {
            const input = form.querySelector(`[name="${field}"]`);
            if (input) {
                input.classList.add('error');
                const errorDiv = document.createElement('div');
                errorDiv.className = 'field-error text-danger mt-1';
                errorDiv.textContent = message;
                input.parentNode.appendChild(errorDiv);
            }
        });
    }

    // Confirmation dialogs
    showConfirmDialog(element) {
        const message = element.getAttribute('data-confirm') || 'Are you sure?';
        const action = element.getAttribute('data-action') || 'proceed';
        
        if (confirm(`${message}\n\nClick OK to ${action}, or Cancel to abort.`)) {
            // If it's a link, follow it
            if (element.tagName === 'A') {
                window.location.href = element.href;
            }
            // If it's a form button, submit the form
            else if (element.type === 'submit') {
                element.closest('form').submit();
            }
            // If it has a data-url, make an AJAX call
            else if (element.getAttribute('data-url')) {
                this.performConfirmedAction(element);
            }
        }
    }

    async performConfirmedAction(element) {
        const url = element.getAttribute('data-url');
        const method = element.getAttribute('data-method') || 'POST';
        
        try {
            const response = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-Token': this.csrfToken
                }
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.showNotification(result.message, 'success');
                if (result.reload) {
                    setTimeout(() => location.reload(), 1000);
                }
            } else {
                this.showNotification(result.message, 'error');
            }
        } catch (error) {
            this.showNotification('An error occurred', 'error');
        }
    }

    // Dropdown functionality
    toggleDropdown(toggle) {
        const dropdown = toggle.closest('.dropdown');
        const isActive = dropdown.classList.contains('active');
        
        // Close all dropdowns first
        this.closeAllDropdowns();
        
        // Toggle current dropdown
        if (!isActive) {
            dropdown.classList.add('active');
        }
    }

    closeAllDropdowns() {
        document.querySelectorAll('.dropdown.active').forEach(dropdown => {
            dropdown.classList.remove('active');
        });
    }

    // Auto-save functionality
    async autoSaveForm(form) {
        const formData = new FormData(form);
        formData.append('auto_save', '1');
        
        try {
            const response = await fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            if (response.ok) {
                this.showAutoSaveStatus('Saved', 'success');
            }
        } catch (error) {
            this.showAutoSaveStatus('Save failed', 'error');
        }
    }

    showAutoSaveStatus(message, type) {
        let indicator = document.querySelector('.auto-save-indicator');
        if (!indicator) {
            indicator = document.createElement('div');
            indicator.className = 'auto-save-indicator';
            document.body.appendChild(indicator);
        }
        
        indicator.textContent = message;
        indicator.className = `auto-save-indicator ${type}`;
        
        setTimeout(() => {
            indicator.classList.add('fade-out');
            setTimeout(() => indicator.remove(), 300);
        }, 2000);
    }

    // File upload preview
    previewFile(input) {
        const file = input.files[0];
        if (!file) return;

        const previewContainer = input.closest('.form-group').querySelector('.file-preview');
        if (!previewContainer) return;

        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = (e) => {
                previewContainer.innerHTML = `
                    <div class="image-preview">
                        <img src="${e.target.result}" alt="Preview" style="max-width: 200px; max-height: 200px;">
                        <div class="file-info">
                            <strong>${file.name}</strong><br>
                            Size: ${this.formatFileSize(file.size)}
                        </div>
                    </div>
                `;
            };
            reader.readAsDataURL(file);
        } else {
            previewContainer.innerHTML = `
                <div class="file-info">
                    <strong>${file.name}</strong><br>
                    Size: ${this.formatFileSize(file.size)}<br>
                    Type: ${file.type}
                </div>
            `;
        }
    }

    formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    // Search functionality
    async performSearch(input) {
        const query = input.value.trim();
        const searchType = input.getAttribute('data-search-type') || 'general';
        const resultsContainer = document.querySelector(input.getAttribute('data-results-target') || '.search-results');
        
        if (query.length < 2) {
            if (resultsContainer) resultsContainer.innerHTML = '';
            return;
        }

        try {
            const response = await fetch('search.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    query: query,
                    type: searchType,
                    csrf_token: this.csrfToken
                })
            });

            const results = await response.json();
            
            if (resultsContainer && results.success) {
                this.displaySearchResults(resultsContainer, results.data);
            }
        } catch (error) {
            console.error('Search error:', error);
        }
    }

    displaySearchResults(container, results) {
        if (results.length === 0) {
            container.innerHTML = '<div class="no-results">No results found</div>';
            return;
        }

        const html = results.map(result => `
            <div class="search-result-item">
                <h4><a href="${result.url}">${result.title}</a></h4>
                <p>${result.description}</p>
                <small class="text-muted">${result.type}</small>
            </div>
        `).join('');

        container.innerHTML = html;
    }

    // Notifications
    showNotification(message, type = 'info', duration = 5000) {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <span class="notification-message">${message}</span>
                <button class="notification-close">&times;</button>
            </div>
        `;

        const container = document.querySelector('.notifications-container') || this.createNotificationsContainer();
        container.appendChild(notification);

        // Auto-remove after duration
        setTimeout(() => {
            this.closeNotification(notification);
        }, duration);

        // Animate in
        setTimeout(() => notification.classList.add('show'), 10);
    }

    createNotificationsContainer() {
        const container = document.createElement('div');
        container.className = 'notifications-container';
        document.body.appendChild(container);
        return container;
    }

    closeNotification(notification) {
        notification.classList.add('hiding');
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }

    async loadNotifications() {
        try {
            const response = await fetch('api/notifications.php');
            const result = await response.json();
            
            if (result.success) {
                this.updateNotificationBadge(result.unread_count);
                this.displayNotifications(result.notifications);
            }
        } catch (error) {
            console.error('Failed to load notifications:', error);
        }
    }

    updateNotificationBadge(count) {
        const badge = document.querySelector('.notification-badge');
        if (badge) {
            badge.textContent = count;
            badge.style.display = count > 0 ? 'inline' : 'none';
        }
    }

    // Session management
    async checkSession() {
        try {
            const response = await fetch('api/check_session.php');
            const result = await response.json();
            
            if (!result.valid) {
                this.showNotification('Your session has expired. Please log in again.', 'warning');
                setTimeout(() => {
                    window.location.href = 'login.php';
                }, 3000);
            }
        } catch (error) {
            console.error('Session check failed:', error);
        }
    }

    // Chart initialization
    initCharts() {
        // Initialize analytics charts
        const chartElements = document.querySelectorAll('[data-chart]');
        chartElements.forEach(element => {
            const chartType = element.getAttribute('data-chart');
            this.createChart(element, chartType);
        });
    }

    async createChart(element, type) {
        try {
            const response = await fetch(`api/chart_data.php?type=${type}`);
            const data = await response.json();
            
            if (data.success) {
                this.renderChart(element, data.chart_data);
            }
        } catch (error) {
            console.error('Chart creation failed:', error);
        }
    }

    renderChart(element, data) {
        // This would be implemented based on the chart library being used
        // For example, with Chart.js:
        new Chart(element, {
            type: data.type,
            data: data.data,
            options: data.options
        });
    }

    // Data table initialization
    initDataTables() {
        document.querySelectorAll('.data-table').forEach(table => {
            this.enhanceTable(table);
        });
    }

    enhanceTable(table) {
        // Add sorting functionality
        const headers = table.querySelectorAll('th[data-sortable]');
        headers.forEach(header => {
            header.style.cursor = 'pointer';
            header.addEventListener('click', () => {
                this.sortTable(table, header);
            });
        });
    }

    sortTable(table, header) {
        const columnIndex = Array.from(header.parentNode.children).indexOf(header);
        const rows = Array.from(table.querySelectorAll('tbody tr'));
        const isAscending = header.classList.contains('sort-asc');
        
        // Clear all sort classes
        table.querySelectorAll('th').forEach(th => th.classList.remove('sort-asc', 'sort-desc'));
        
        // Add appropriate sort class
        header.classList.add(isAscending ? 'sort-desc' : 'sort-asc');
        
        // Sort rows
        rows.sort((a, b) => {
            const aValue = a.children[columnIndex].textContent.trim();
            const bValue = b.children[columnIndex].textContent.trim();
            
            if (isAscending) {
                return bValue.localeCompare(aValue, undefined, {numeric: true});
            } else {
                return aValue.localeCompare(bValue, undefined, {numeric: true});
            }
        });
        
        // Reinsert sorted rows
        const tbody = table.querySelector('tbody');
        rows.forEach(row => tbody.appendChild(row));
    }

    // Real-time features
    initRealTimeFeatures() {
        // Update live data every 30 seconds
        setInterval(() => {
            this.updateLiveData();
        }, 30000);
    }

    async updateLiveData() {
        const liveElements = document.querySelectorAll('[data-live]');
        
        for (const element of liveElements) {
            const dataType = element.getAttribute('data-live');
            try {
                const response = await fetch(`api/live_data.php?type=${dataType}`);
                const result = await response.json();
                
                if (result.success) {
                    element.textContent = result.value;
                }
            } catch (error) {
                console.error(`Failed to update live data for ${dataType}:`, error);
            }
        }
    }

    // Page data reload
    async reloadPageData() {
        const reloadElements = document.querySelectorAll('[data-reload]');
        
        for (const element of reloadElements) {
            const url = element.getAttribute('data-reload');
            try {
                const response = await fetch(url);
                const html = await response.text();
                element.innerHTML = html;
            } catch (error) {
                console.error('Failed to reload page data:', error);
            }
        }
    }

    // Tooltip initialization
    initTooltips() {
        document.querySelectorAll('[data-tooltip]').forEach(element => {
            element.addEventListener('mouseenter', (e) => {
                this.showTooltip(e.target);
            });
            
            element.addEventListener('mouseleave', (e) => {
                this.hideTooltip();
            });
        });
    }

    showTooltip(element) {
        const text = element.getAttribute('data-tooltip');
        const tooltip = document.createElement('div');
        tooltip.className = 'tooltip';
        tooltip.textContent = text;
        document.body.appendChild(tooltip);
        
        const rect = element.getBoundingClientRect();
        tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
        tooltip.style.top = rect.top - tooltip.offsetHeight - 10 + 'px';
        
        this.currentTooltip = tooltip;
    }

    hideTooltip() {
        if (this.currentTooltip) {
            this.currentTooltip.remove();
            this.currentTooltip = null;
        }
    }

    // Utility functions
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
    }

    formatDate(date) {
        return new Intl.DateTimeFormat('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        }).format(new Date(date));
    }

    formatNumber(number) {
        return new Intl.NumberFormat().format(number);
    }
}

// Initialize the portal when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.portal = new WebPortal();
});

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = WebPortal;
}