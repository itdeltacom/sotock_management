'use strict';

/**
 * BATI Car Rental Admin Panel - Performance Optimized JavaScript
 */

// Create a single DOM cache object
const DOM = {};

// Use a more efficient DOMContentLoaded approach
document.addEventListener('DOMContentLoaded', init);

/**
 * Single initialization function to start everything
 */
function init() {
    // Cache DOM elements only once
    cacheDOM();
    
    // Setup event delegation for better performance
    setupEventDelegation();
    
    // Initialize core UI components
    initializeUIComponents();
    
    // Setup AJAX CSRF token once
    setupAjaxCSRF();
    
    // Handle window resize with throttling
    window.addEventListener('resize', throttle(handleResponsiveBehavior, 250));
    
    // Apply dark mode if saved
    applyDarkModePreference();
}

/**
 * Cache DOM elements - only get references once
 */
function cacheDOM() {
    // Core elements
    DOM.body = document.body;
    DOM.sidebar = document.getElementById('sidebar');
    DOM.sidebarOverlay = document.getElementById('sidebarOverlay');
    DOM.toggleSidebar = document.getElementById('toggleSidebar');
    DOM.content = document.getElementById('content');
    DOM.userDropdown = document.getElementById('userDropdown');
    DOM.userDropdownMenu = document.getElementById('userDropdownMenu');
    DOM.loader = document.getElementById('loader');
    DOM.notificationsContainer = document.getElementById('notificationsContainer');
    
    // Get csrf token once
    DOM.csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
}

/**
 * Setup event delegation for better performance 
 * instead of attaching many individual event listeners
 */
function setupEventDelegation() {
    // Document-level click handler with event delegation
    document.addEventListener('click', (e) => {
        // Handle sidebar toggle
        if (e.target.closest('#toggleSidebar')) {
            e.preventDefault();
            toggleSidebar();
        }
        
        // Handle sidebar overlay clicks
        if (e.target.closest('#sidebarOverlay')) {
            closeSidebar();
        }
        
        // Handle user dropdown
        if (e.target.closest('#userDropdown')) {
            e.preventDefault();
            e.stopPropagation();
            toggleUserDropdown();
        } else if (DOM.userDropdownMenu?.classList.contains('show') && 
                  !e.target.closest('#userDropdownMenu')) {
            closeUserDropdown();
        }
        
        // Handle submenu toggles
        const menuToggle = e.target.closest('.sidebar-menu-item[data-toggle="collapse"]');
        if (menuToggle) {
            toggleSubmenu(menuToggle);
        }
        
        // Handle password toggles with event delegation
        const passwordToggle = e.target.closest('.password-toggle');
        if (passwordToggle) {
            togglePassword(passwordToggle);
        }
        
        // Handle delete confirmation buttons
        const deleteBtn = e.target.closest('.btn-delete');
        if (deleteBtn) {
            const deleteId = deleteBtn.dataset.id;
            if (deleteId) {
                showDeleteConfirmation(deleteId);
            }
        }
        
        // Handle notification close buttons
        const notificationClose = e.target.closest('.notification-close');
        if (notificationClose) {
            const notification = notificationClose.closest('.notification');
            if (notification) {
                hideNotification(notification);
            }
        }
    });
    
    // Delegate form submissions
    document.addEventListener('submit', (e) => {
        const form = e.target;
        
        // Handle regular forms with loader
        if (!form.classList.contains('no-loader')) {
            showLoader();
        }
        
        // Handle AJAX forms
        if (form.classList.contains('ajax-form')) {
            e.preventDefault();
            handleAjaxFormSubmit(form);
        }
    });
    
    // Setup password strength meter with event delegation
    const passwordInput = document.getElementById('password');
    if (passwordInput) {
        passwordInput.addEventListener('input', updatePasswordStrength);
    }
}

/**
 * Initialize core UI components selectively
 */
function initializeUIComponents() {
    // Initialize only what's available on the page
    setupAlertDismiss();
    
    // Initialize bootstrap components if available
    if (typeof bootstrap !== 'undefined') {
        initBootstrapComponents();
    }
    
    // Initialize third-party components only if they exist
    initThirdPartyComponents();
}

/**
 * Initialize Bootstrap tooltips and popovers if available
 */
function initBootstrapComponents() {
    // Add observers to initialize components only when they appear in viewport
    if ('IntersectionObserver' in window) {
        // Use Intersection Observer to initialize components only when visible
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    // Initialize visible tooltips
                    if (entry.target.hasAttribute('data-toggle') && 
                        entry.target.getAttribute('data-toggle') === 'tooltip') {
                        new bootstrap.Tooltip(entry.target);
                        observer.unobserve(entry.target);
                    }
                    
                    // Initialize visible popovers
                    if (entry.target.hasAttribute('data-toggle') && 
                        entry.target.getAttribute('data-toggle') === 'popover') {
                        new bootstrap.Popover(entry.target);
                        observer.unobserve(entry.target);
                    }
                }
            });
        });
        
        // Observe tooltips
        document.querySelectorAll('[data-toggle="tooltip"], [data-bs-toggle="tooltip"]')
            .forEach(el => observer.observe(el));
            
        // Observe popovers
        document.querySelectorAll('[data-toggle="popover"], [data-bs-toggle="popover"]')
            .forEach(el => observer.observe(el));
    } else {
        // Fallback for browsers without IntersectionObserver
        lazyInitialize('[data-toggle="tooltip"], [data-bs-toggle="tooltip"]', 
                      (el) => new bootstrap.Tooltip(el));
        lazyInitialize('[data-toggle="popover"], [data-bs-toggle="popover"]', 
                      (el) => new bootstrap.Popover(el));
    }
}

/**
 * Initialize third-party components only if they exist
 */
function initThirdPartyComponents() {
    // Initialize Select2 only if already loaded
    if (typeof jQuery !== 'undefined' && typeof jQuery.fn.select2 !== 'undefined') {
        jQuery('.select2').select2({
            theme: 'bootstrap4',
            width: '100%'
        });
    }
    
    // Initialize DataTables only if already loaded
    if (typeof jQuery !== 'undefined' && typeof jQuery.fn.DataTable !== 'undefined') {
        const tables = document.querySelectorAll('.datatable:not(.dataTable)');
        if (tables.length > 0) {
            jQuery('.datatable:not(.dataTable)').DataTable({
                responsive: true,
                processing: true,
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search..."
                }
            });
        }
    }
    
    // Initialize Flatpickr only if already loaded
    if (typeof flatpickr !== 'undefined') {
        // Datepickers - only initialize visible ones
        lazyInitialize('.datepicker:not(.flatpickr-input)', 
                      (el) => flatpickr(el, {
                          dateFormat: 'Y-m-d',
                          allowInput: true
                      }));
                      
        // DateTime pickers
        lazyInitialize('.datetimepicker:not(.flatpickr-input)', 
                      (el) => flatpickr(el, {
                          dateFormat: 'Y-m-d H:i',
                          enableTime: true,
                          allowInput: true
                      }));
    }
}

/**
 * Lazy initialize components only when needed
 */
function lazyInitialize(selector, initFunction) {
    const elements = document.querySelectorAll(selector);
    if (elements.length === 0) return;
    
    // Initialize only the first few elements immediately
    const immediate = Math.min(5, elements.length);
    for (let i = 0; i < immediate; i++) {
        initFunction(elements[i]);
    }
    
    // Initialize the rest after a short delay
    if (elements.length > immediate) {
        setTimeout(() => {
            for (let i = immediate; i < elements.length; i++) {
                initFunction(elements[i]);
            }
        }, 100);
    }
}

/**
 * Toggle sidebar efficiently
 */
function toggleSidebar() {
    if (!DOM.sidebar || !DOM.content) return;
    
    DOM.sidebar.classList.toggle('collapsed');
    DOM.content.classList.toggle('expanded');
    
    // On mobile, show/hide sidebar and overlay
    if (window.innerWidth < 992) {
        DOM.sidebar.classList.toggle('show');
        DOM.sidebarOverlay?.classList.toggle('show');
    }
}

/**
 * Close sidebar
 */
function closeSidebar() {
    if (!DOM.sidebar || !DOM.sidebarOverlay) return;
    
    DOM.sidebar.classList.remove('show');
    DOM.sidebarOverlay.classList.remove('show');
}

/**
 * Toggle user dropdown
 */
function toggleUserDropdown() {
    if (!DOM.userDropdownMenu) return;
    DOM.userDropdownMenu.classList.toggle('show');
}

/**
 * Close user dropdown
 */
function closeUserDropdown() {
    if (!DOM.userDropdownMenu) return;
    DOM.userDropdownMenu.classList.remove('show');
}

/**
 * Toggle submenu efficiently
 */
function toggleSubmenu(menuItem) {
    const targetId = menuItem.getAttribute('data-target');
    const target = document.querySelector(targetId);
    
    if (target) {
        menuItem.classList.toggle('expanded');
        target.classList.toggle('show');
    }
}

/**
 * Toggle password visibility
 */
function togglePassword(button) {
    const passwordInput = button.parentElement?.querySelector('input');
    const icon = button.querySelector('i');
    
    if (!passwordInput || !icon) return;
    
    const isPassword = passwordInput.type === 'password';
    passwordInput.type = isPassword ? 'text' : 'password';
    
    // Update icon efficiently without multiple classlist operations
    if (isPassword) {
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}

/**
 * Update password strength meter
 */
function updatePasswordStrength() {
    const meter = document.querySelector('.password-strength-meter');
    if (!meter) return;
    
    const password = this.value || '';
    
    // Remove existing classes with a single classList.remove
    meter.className = meter.className.replace(/strength-(weak|medium|strong)/g, '').trim() + ' password-strength-meter';
    
    // Set width and class in a more efficient way
    let width, strengthClass;
    
    if (password.length === 0) {
        width = '0';
        strengthClass = '';
    } else if (password.length < 6) {
        width = '30%';
        strengthClass = 'strength-weak';
    } else if (password.length < 10 || !/[A-Z]/.test(password) || !/[0-9]/.test(password)) {
        width = '60%';
        strengthClass = 'strength-medium';
    } else {
        width = '100%';
        strengthClass = 'strength-strong';
    }
    
    // Set properties efficiently
    meter.style.width = width;
    if (strengthClass) {
        meter.classList.add(strengthClass);
    }
}

/**
 * Handle AJAX form submission more efficiently
 */
function handleAjaxFormSubmit(form) {
    // Validate form if validation function exists
    if (typeof validateForm === 'function' && !validateForm(form)) {
        return false;
    }
    
    // Show loader
    showLoader();
    
    // Use FormData for efficiency
    const formData = new FormData(form);
    const url = form.getAttribute('action');
    const method = form.getAttribute('method') || 'POST';
    
    // Use fetch API for better performance
    fetch(url, {
        method: method,
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': DOM.csrfToken
        }
    })
    .then(response => response.json())
    .then(data => {
        hideLoader();
        
        if (data.success) {
            // Use SweetAlert2 for notifications if available
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: data.message || 'Action completed successfully',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                });
            } else {
                showNotification('success', data.message || 'Action completed successfully');
            }
            
            // Handle redirect
            if (data.redirect) {
                setTimeout(() => window.location.href = data.redirect, 1000);
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
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: data.message || 'An error occurred',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                });
            } else {
                showNotification('danger', data.message || 'An error occurred');
            }
            
            // Handle validation errors more efficiently
            if (data.errors) {
                clearPreviousErrors(form);
                displayValidationErrors(form, data.errors);
            }
        }
    })
    .catch(error => {
        hideLoader();
        console.error('Form submission error:', error);
        showNotification('danger', 'An error occurred while processing your request');
    });
}

/**
 * Clear previous validation errors
 */
function clearPreviousErrors(form) {
    form.querySelectorAll('.is-invalid').forEach(input => {
        input.classList.remove('is-invalid');
    });
    
    form.querySelectorAll('.invalid-feedback').forEach(feedback => {
        feedback.textContent = '';
    });
}

/**
 * Display validation errors efficiently
 */
function displayValidationErrors(form, errors) {
    // Create a document fragment to minimize DOM operations
    const fragment = document.createDocumentFragment();
    
    // Process all errors
    Object.entries(errors).forEach(([key, messages]) => {
        const input = form.querySelector(`[name="${key}"]`);
        if (!input) return;
        
        // Add invalid class
        input.classList.add('is-invalid');
        
        // Get or create feedback element
        let feedback = input.parentNode.querySelector('.invalid-feedback');
        if (!feedback) {
            feedback = document.createElement('div');
            feedback.className = 'invalid-feedback';
            fragment.appendChild(feedback);
            
            // Append feedback after input processing
            setTimeout(() => input.parentNode.appendChild(feedback), 0);
        }
        
        // Set error message
        feedback.textContent = messages[0];
    });
}

/**
 * Set up alerts to auto-dismiss efficiently
 */
function setupAlertDismiss() {
    const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
    if (!alerts || alerts.length === 0) return;
    
    // Use requestAnimationFrame for smoother animations
    window.requestAnimationFrame(() => {
        setTimeout(() => {
            alerts.forEach(alert => {
                // Prepare for animation
                alert.style.transition = 'opacity 0.5s';
                
                // Use requestAnimationFrame for animation
                window.requestAnimationFrame(() => {
                    alert.style.opacity = '0';
                    
                    // Remove after animation
                    setTimeout(() => {
                        if (alert.parentNode) {
                            alert.parentNode.removeChild(alert);
                        }
                    }, 500);
                });
            });
        }, 5000);
    });
}

/**
 * Show delete confirmation
 */
function showDeleteConfirmation(id) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Delete Confirmation',
            text: 'Are you sure you want to delete this item? This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Delete',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                deleteItem(id);
            }
        });
    } else {
        if (confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
            deleteItem(id);
        }
    }
}

/**
 * Delete item via AJAX
 */
function deleteItem(id) {
    showLoader();
    
    const url = document.querySelector(`[data-id="${id}"]`).dataset.url || 
               (window.deleteUrl ? window.deleteUrl.replace(':id', id) : null);
    
    if (!url) {
        hideLoader();
        showNotification('danger', 'Delete URL not found');
        return;
    }
    
    fetch(url, {
        method: 'DELETE',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': DOM.csrfToken
        }
    })
    .then(response => response.json())
    .then(data => {
        hideLoader();
        
        if (data.success) {
            showNotification('success', data.message || 'Item deleted successfully');
            
            // Refresh table if it exists
            if (typeof jQuery !== 'undefined' && jQuery.fn.DataTable) {
                jQuery('.datatable').DataTable().ajax.reload();
            } else {
                // Fallback to page reload after delay
                setTimeout(() => window.location.reload(), 1000);
            }
        } else {
            showNotification('danger', data.message || 'Failed to delete item');
        }
    })
    .catch(error => {
        hideLoader();
        console.error('Delete error:', error);
        showNotification('danger', 'An error occurred while deleting');
    });
}

/**
 * Show loader overlay
 */
function showLoader() {
    if (!DOM.loader) return;
    
    // Use class manipulation instead of adding inline styles
    DOM.loader.classList.add('show');
}

/**
 * Hide loader overlay
 */
function hideLoader() {
    if (!DOM.loader) return;
    
    // Use class manipulation for better performance
    DOM.loader.classList.remove('show');
}

/**
 * Show notification more efficiently
 */
function showNotification(type, message) {
    if (!DOM.notificationsContainer) return;
    
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    
    // Set icon based on type
    let icon;
    switch (type) {
        case 'success': icon = '<i class="fas fa-check-circle"></i>'; break;
        case 'warning': icon = '<i class="fas fa-exclamation-triangle"></i>'; break;
        case 'danger': icon = '<i class="fas fa-times-circle"></i>'; break;
        default: icon = '<i class="fas fa-info-circle"></i>';
    }
    
    // Set HTML content
    notification.innerHTML = `
        <div class="notification-icon">${icon}</div>
        <div class="notification-content">
            <div class="notification-title">${type.charAt(0).toUpperCase() + type.slice(1)}</div>
            <div class="notification-text">${message}</div>
        </div>
        <button type="button" class="notification-close">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    // Append to container
    DOM.notificationsContainer.appendChild(notification);
    
    // Use requestAnimationFrame for smoother animation
    requestAnimationFrame(() => {
        // Force a reflow before adding the show class
        notification.offsetHeight;
        notification.classList.add('show');
    });
    
    // Auto-close after timeout
    setTimeout(() => {
        hideNotification(notification);
    }, 5000);
}

/**
 * Hide notification with animation
 */
function hideNotification(notification) {
    notification.classList.remove('show');
    
    // Remove from DOM after animation completes
    notification.addEventListener('transitionend', function handler() {
        notification.removeEventListener('transitionend', handler);
        if (notification.parentNode) {
            notification.parentNode.removeChild(notification);
        }
    });
}

/**
 * Handle responsive behavior
 */
function handleResponsiveBehavior() {
    if (!DOM.sidebar || !DOM.sidebarOverlay) return;
    
    if (window.innerWidth < 992) {
        DOM.sidebar.classList.remove('show');
        DOM.sidebarOverlay.classList.remove('show');
    }
}

/**
 * Apply dark mode preference
 */
function applyDarkModePreference() {
    if (localStorage.getItem('darkMode') === 'enabled') {
        document.body.classList.add('dark-mode');
    }
}

/**
 * Setup AJAX CSRF token
 */
function setupAjaxCSRF() {
    if (typeof $ !== 'undefined' && DOM.csrfToken) {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': DOM.csrfToken
            }
        });
    }
}

/**
 * Throttle function for performance (better than debounce for some UI interactions)
 */
function throttle(func, limit = 300) {
    let inThrottle;
    return function(...args) {
        if (!inThrottle) {
            func.apply(this, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}

/**
 * Optimize jQuery usage if jQuery is available
 */
if (typeof jQuery !== 'undefined') {
    // Define a fast selector method
    jQuery.fn.findFast = function(selector) {
        return this.find(selector);
    };
    
    // Setup once for all jQuery AJAX requests
    jQuery(document).ajaxStart(function() {
        showLoader();
    }).ajaxStop(function() {
        hideLoader();
    });
}