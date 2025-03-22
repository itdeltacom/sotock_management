/**
 * Admin Dashboard JavaScript
 */

// Wait for the DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    // Toggle password visibility
    initPasswordToggles();
    
    // Password strength meter
    initPasswordStrengthMeter();
    
    // Initialize spinners for form submissions
    initFormSpinners();
    
    // Initialize sidebar toggle
    initSidebarToggle();
    
    // Initialize user dropdown
    initUserDropdown();
    
    // Initialize tooltips and popovers if Bootstrap is available
    initBootstrapComponents();
    
    // Form validation
    initFormValidation();
});

/**
 * Initialize password toggle buttons
 */
function initPasswordToggles() {
    document.querySelectorAll('.toggle-password').forEach(button => {
        button.addEventListener('click', function() {
            const passwordInput = this.parentElement.querySelector('input');
            const icon = this.querySelector('i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });
}

/**
 * Initialize password strength meter
 */
function initPasswordStrengthMeter() {
    const passwordInput = document.getElementById('password');
    
    if (passwordInput) {
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            const meter = document.querySelector('.password-strength-meter');
            
            if (!meter) return;
            
            // Remove existing classes
            meter.classList.remove('strength-weak', 'strength-medium', 'strength-strong');
            
            // Check password strength
            if (password.length === 0) {
                meter.style.width = '0';
            } else if (password.length < 6) {
                meter.style.width = '30%';
                meter.classList.add('strength-weak');
            } else if (password.length < 10 || !/[A-Z]/.test(password) || !/[0-9]/.test(password)) {
                meter.style.width = '60%';
                meter.classList.add('strength-medium');
            } else {
                meter.style.width = '100%';
                meter.classList.add('strength-strong');
            }
        });
    }
}

/**
 * Initialize spinner for form submissions
 */
function initFormSpinners() {
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function() {
            // Create spinner overlay
            const spinner = document.createElement('div');
            spinner.id = 'spinner';
            spinner.className = 'spinner-overlay show';
            spinner.innerHTML = `
                <div class="spinner-content">
                    <div class="spinner-border text-primary mb-3" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p>Processing...</p>
                </div>
            `;
            
            // Add to body
            document.body.appendChild(spinner);
            
            // Remove spinner after 30 seconds (fallback)
            setTimeout(() => {
                if (document.getElementById('spinner')) {
                    document.getElementById('spinner').remove();
                }
            }, 30000);
        });
    });
}

/**
 * Initialize sidebar toggle functionality
 */
function initSidebarToggle() {
    const sidebarToggle = document.getElementById('sidebar-toggle');
    const sidebar = document.querySelector('.admin-sidebar');
    const content = document.querySelector('.admin-content');
    
    if (sidebarToggle && sidebar && content) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('collapsed');
            content.classList.toggle('expanded');
        });
        
        // For mobile: show/hide sidebar
        const mobileSidebarToggle = document.getElementById('mobile-sidebar-toggle');
        
        if (mobileSidebarToggle) {
            mobileSidebarToggle.addEventListener('click', function() {
                sidebar.classList.toggle('show');
            });
            
            // Close sidebar when clicking outside on mobile
            document.addEventListener('click', function(e) {
                const isSidebar = e.target.closest('.admin-sidebar');
                const isSidebarToggle = e.target.closest('#mobile-sidebar-toggle');
                
                if (!isSidebar && !isSidebarToggle && sidebar.classList.contains('show')) {
                    sidebar.classList.remove('show');
                }
            });
        }
    }
    
    // Initialize sidebar submenu toggles
    document.querySelectorAll('.sidebar-submenu-toggle').forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            
            const submenu = this.nextElementSibling;
            
            // Toggle arrow icon
            const icon = this.querySelector('.submenu-arrow');
            
            if (submenu) {
                submenu.classList.toggle('show');
                
                if (icon) {
                    icon.classList.toggle('fa-angle-down');
                    icon.classList.toggle('fa-angle-up');
                }
            }
        });
    });
}

/**
 * Initialize user dropdown menu
 */
function initUserDropdown() {
    const dropdownToggle = document.querySelector('.user-dropdown-toggle');
    const dropdownMenu = document.querySelector('.user-dropdown-menu');
    
    if (dropdownToggle && dropdownMenu) {
        dropdownToggle.addEventListener('click', function() {
            dropdownMenu.classList.toggle('show');
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            const isDropdown = e.target.closest('.user-dropdown');
            
            if (!isDropdown && dropdownMenu.classList.contains('show')) {
                dropdownMenu.classList.remove('show');
            }
        });
    }
}

/**
 * Initialize Bootstrap components if available
 */
function initBootstrapComponents() {
    if (typeof bootstrap !== 'undefined') {
        // Initialize tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        // Initialize popovers
        const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
        popoverTriggerList.map(function(popoverTriggerEl) {
            return new bootstrap.Popover(popoverTriggerEl);
        });
    }
}

/**
 * Initialize form validation
 */
function initFormValidation() {
    document.querySelectorAll('form.needs-validation').forEach(form => {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            
            form.classList.add('was-validated');
        }, false);
    });
}

/**
 * Show a custom toast notification
 * @param {string} message - The notification message
 * @param {string} type - The type of notification (success, error, warning, info)
 * @param {number} duration - Duration in milliseconds
 */
function showNotification(message, type = 'success', duration = 3000) {
    // Create toast element
    const toast = document.createElement('div');
    toast.className = `toast-notification toast-${type} fade-in`;
    
    // Icon based on type
    let icon = 'info-circle';
    
    switch (type) {
        case 'success':
            icon = 'check-circle';
            break;
        case 'error':
            icon = 'times-circle';
            break;
        case 'warning':
            icon = 'exclamation-triangle';
            break;
    }
    
    toast.innerHTML = `
        <div class="toast-icon">
            <i class="fas fa-${icon}"></i>
        </div>
        <div class="toast-content">
            <p>${message}</p>
        </div>
        <button class="toast-close">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    // Add to notification container or create one
    let container = document.querySelector('.toast-container');
    
    if (!container) {
        container = document.createElement('div');
        container.className = 'toast-container';
        document.body.appendChild(container);
    }
    
    container.appendChild(toast);
    
    // Close button functionality
    const closeButton = toast.querySelector('.toast-close');
    closeButton.addEventListener('click', function() {
        toast.classList.remove('fade-in');
        toast.classList.add('fade-out');
        
        setTimeout(() => {
            toast.remove();
            
            // Remove container if empty
            if (container.children.length === 0) {
                container.remove();
            }
        }, 300);
    });
    
    // Auto remove after duration
    setTimeout(() => {
        if (document.body.contains(toast)) {
            toast.classList.remove('fade-in');
            toast.classList.add('fade-out');
            
            setTimeout(() => {
                toast.remove();
                
                // Remove container if empty
                if (container.children.length === 0) {
                    container.remove();
                }
            }, 300);
        }
    }, duration);
}
/**
 * BATI Car Rental Admin Panel JavaScript
 */

// DOM Ready
document.addEventListener('DOMContentLoaded', function() {
    // Initialize components
    initializeComponents();
    
    // Setup auto-dismiss for alerts
    setupAlertDismiss();
    
    // Handle form submissions with loader
    setupFormSubmissions();
    
    // Initialize date pickers
    initializeDatePickers();
    
    // Handle mobile responsive behavior
    handleResponsiveBehavior();
});

/**
 * Initialize various UI components
 */
function initializeComponents() {
    // Tooltips
    document.querySelectorAll('[data-toggle="tooltip"]').forEach(function(element) {
        new bootstrap.Tooltip(element);
    });
    
    // Popovers
    document.querySelectorAll('[data-toggle="popover"]').forEach(function(element) {
        new bootstrap.Popover(element);
    });
    
    // Initialize Select2 if available
    if (typeof $.fn.select2 !== 'undefined') {
        $('.select2').select2({
            theme: 'bootstrap4',
            width: '100%'
        });
    }
    
    // Initialize datatable if available
    if (typeof $.fn.DataTable !== 'undefined') {
        $('.datatable').DataTable({
            responsive: true,
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search...",
                lengthMenu: "_MENU_ records per page",
                info: "Showing _START_ to _END_ of _TOTAL_ records",
                infoEmpty: "Showing 0 to 0 of 0 records",
                infoFiltered: "(filtered from _MAX_ total records)"
            }
        });
    }
}

/**
 * Auto-dismiss alerts after 5 seconds
 */
function setupAlertDismiss() {
    setTimeout(function() {
        document.querySelectorAll('.alert:not(.alert-permanent)').forEach(function(alert) {
            // Create and trigger fade out animation
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            
            // Remove from DOM after animation completes
            setTimeout(function() {
                if (alert.parentNode) {
                    alert.parentNode.removeChild(alert);
                }
            }, 500);
        });
    }, 5000);
}

/**
 * Show loader on form submissions
 */
function setupFormSubmissions() {
    document.querySelectorAll('form:not(.no-loader)').forEach(function(form) {
        form.addEventListener('submit', function() {
            // Validate form if validation function exists
            if (typeof validateForm === 'function') {
                if (!validateForm(form)) {
                    return false;
                }
            }
            
            // Show loader
            showLoader();
        });
    });
    
    // Handle AJAX form submissions
    document.querySelectorAll('form.ajax-form').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validate form if validation function exists
            if (typeof validateForm === 'function') {
                if (!validateForm(form)) {
                    return false;
                }
            }
            
            // Show loader
            showLoader();
            
            // Submit form via AJAX
            const formData = new FormData(form);
            const url = form.getAttribute('action');
            const method = form.getAttribute('method') || 'POST';
            
            fetch(url, {
                method: method,
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                hideLoader();
                
                if (data.success) {
                    // Show success notification
                    showNotification('success', data.message || 'Action completed successfully');
                    
                    // Redirect if provided
                    if (data.redirect) {
                        setTimeout(function() {
                            window.location.href = data.redirect;
                        }, 1000);
                    }
                    
                    // Reset form if needed
                    if (data.reset) {
                        form.reset();
                    }
                    
                    // Callback if needed
                    if (typeof formSubmitCallback === 'function') {
                        formSubmitCallback(data);
                    }
                } else {
                    // Show error notification
                    showNotification('danger', data.message || 'An error occurred');
                    
                    // Display validation errors
                    if (data.errors) {
                        Object.keys(data.errors).forEach(function(key) {
                            const input = form.querySelector(`[name="${key}"]`);
                            if (input) {
                                input.classList.add('is-invalid');
                                
                                // Create or update error message
                                let feedback = input.parentNode.querySelector('.invalid-feedback');
                                if (!feedback) {
                                    feedback = document.createElement('div');
                                    feedback.className = 'invalid-feedback';
                                    input.parentNode.appendChild(feedback);
                                }
                                feedback.textContent = data.errors[key][0];
                            }
                        });
                    }
                }
            })
            .catch(error => {
                hideLoader();
                showNotification('danger', 'An error occurred while processing your request');
                console.error('Form submission error:', error);
            });
        });
    });
}

/**
 * Initialize date pickers
 */
function initializeDatePickers() {
    // Initialize Flatpickr if available
    if (typeof flatpickr !== 'undefined') {
        flatpickr('.datepicker', {
            dateFormat: 'Y-m-d',
            allowInput: true
        });
        
        flatpickr('.datetimepicker', {
            dateFormat: 'Y-m-d H:i',
            enableTime: true,
            allowInput: true
        });
    }
}

/**
 * Handle responsive behavior
 */
function handleResponsiveBehavior() {
    // Adjust sidebar on window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth < 992) {
            document.getElementById('sidebar').classList.remove('show');
            document.getElementById('sidebarOverlay').classList.remove('show');
        }
    });
}

/**
 * Confirm action with modal
 */
function confirmAction(title, message, callback) {
    // Create modal element
    const modal = document.createElement('div');
    modal.className = 'modal-overlay';
    modal.innerHTML = `
        <div class="modal-dialog">
            <div class="modal-header">
                <h5 class="modal-title">${title || 'Confirm Action'}</h5>
                <button type="button" class="modal-close" data-dismiss="modal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                ${message || 'Are you sure you want to perform this action?'}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmButton">Confirm</button>
            </div>
        </div>
    `;
    
    // Add modal to body
    document.body.appendChild(modal);
    
    // Show modal
    setTimeout(() => {
        modal.classList.add('show');
    }, 10);
    
    // Handle close button click
    modal.querySelector('[data-dismiss="modal"]').addEventListener('click', function() {
        closeModal(modal);
    });
    
    // Handle confirm button click
    modal.querySelector('#confirmButton').addEventListener('click', function() {
        closeModal(modal);
        if (typeof callback === 'function') {
            callback();
        }
    });
    
    // Handle background click
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            closeModal(modal);
        }
    });
    
    // Close modal function
    function closeModal(modal) {
        modal.classList.remove('show');
        setTimeout(() => {
            document.body.removeChild(modal);
        }, 300);
    }
}

/**
 * Format currency
 */
function formatCurrency(amount, currency = 'USD') {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: currency
    }).format(amount);
}

/**
 * Format date
 */
function formatDate(date, format = 'long') {
    const d = new Date(date);
    
    switch (format) {
        case 'short':
            return new Intl.DateTimeFormat('en-US', {
                month: 'numeric', 
                day: 'numeric', 
                year: '2-digit'
            }).format(d);
        case 'time':
            return new Intl.DateTimeFormat('en-US', {
                hour: 'numeric', 
                minute: 'numeric',
                hour12: true
            }).format(d);
        case 'datetime':
            return new Intl.DateTimeFormat('en-US', {
                month: 'short', 
                day: 'numeric', 
                year: 'numeric',
                hour: 'numeric', 
                minute: 'numeric',
                hour12: true
            }).format(d);
        case 'long':
        default:
            return new Intl.DateTimeFormat('en-US', {
                month: 'long', 
                day: 'numeric', 
                year: 'numeric'
            }).format(d);
    }
}

/**
 * Check if element is in viewport
 */
function isInViewport(element) {
    const rect = element.getBoundingClientRect();
    return (
        rect.top >= 0 &&
        rect.left >= 0 &&
        rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
        rect.right <= (window.innerWidth || document.documentElement.clientWidth)
    );
}

/**
 * Debounce function for performance
 */
function debounce(func, wait = 300) {
    let timeout;
    return function(...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(this, args), wait);
    };
}

/**
 * Copy text to clipboard
 */
function copyToClipboard(text) {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(text)
            .then(() => {
                showNotification('success', 'Copied to clipboard!');
            })
            .catch(err => {
                console.error('Failed to copy text: ', err);
                showNotification('danger', 'Failed to copy text');
            });
    } else {
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = text;
        textArea.style.position = 'fixed';
        textArea.style.opacity = '0';
        document.body.appendChild(textArea);
        textArea.select();
        
        try {
            const successful = document.execCommand('copy');
            if (successful) {
                showNotification('success', 'Copied to clipboard!');
            } else {
                showNotification('danger', 'Failed to copy text');
            }
        } catch (err) {
            console.error('Failed to copy text: ', err);
            showNotification('danger', 'Failed to copy text');
        }
        
        document.body.removeChild(textArea);
    }
}

/**
 * Generate random string
 */
function generateRandomString(length = 8) {
    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    let result = '';
    for (let i = 0; i < length; i++) {
        result += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    return result;
}

/**
 * Toggle dark mode
 */
function toggleDarkMode() {
    document.body.classList.toggle('dark-mode');
    
    // Save preference
    const isDarkMode = document.body.classList.contains('dark-mode');
    localStorage.setItem('darkMode', isDarkMode ? 'enabled' : 'disabled');
}

// Check for saved dark mode preference
if (localStorage.getItem('darkMode') === 'enabled') {
    document.body.classList.add('dark-mode');
}