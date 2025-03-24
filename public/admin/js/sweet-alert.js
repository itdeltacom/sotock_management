/**
 * BATI Car Rental - SweetAlert2 Helper Functions
 * 
 * This file contains JavaScript helper functions for SweetAlert2 notifications
 * and dialogs with BATI's brand styling.
 */

// Initialize default options for SweetAlert2
const BatiSwalDefaults = {
    toast: true,
    position: 'top-end',
    timer: 3000,
    timerProgressBar: true,
    showConfirmButton: false,
    customClass: {
        popup: 'swal2-toast',
    }
};

/**
 * Display a success notification
 * 
 * @param {string} title - The title of the notification
 * @param {string} message - The message to display (optional)
 * @param {Object} options - Additional options to override defaults
 */
function toastSuccess(title, message = '', options = {}) {
    const toast = {
        ...BatiSwalDefaults,
        icon: 'success',
        title: title,
        text: message,
        iconColor: '#34D399',
        customClass: {
            ...BatiSwalDefaults.customClass,
            popup: 'swal2-toast colored-toast swal2-icon-success',
        },
        ...options
    };
    
    Swal.fire(toast);
}

/**
 * Display an error notification
 * 
 * @param {string} title - The title of the notification
 * @param {string} message - The message to display (optional)
 * @param {Object} options - Additional options to override defaults
 */
function toastError(title, message = '', options = {}) {
    const toast = {
        ...BatiSwalDefaults,
        icon: 'error',
        title: title,
        text: message,
        iconColor: '#EF4444',
        timer: 5000, // Error messages stay longer
        customClass: {
            ...BatiSwalDefaults.customClass,
            popup: 'swal2-toast colored-toast swal2-icon-error',
        },
        ...options
    };
    
    Swal.fire(toast);
}

/**
 * Display a warning notification
 * 
 * @param {string} title - The title of the notification
 * @param {string} message - The message to display (optional)
 * @param {Object} options - Additional options to override defaults
 */
function toastWarning(title, message = '', options = {}) {
    const toast = {
        ...BatiSwalDefaults,
        icon: 'warning',
        title: title,
        text: message,
        iconColor: '#FBBF24',
        timer: 4000, // Warning messages stay a bit longer
        customClass: {
            ...BatiSwalDefaults.customClass,
            popup: 'swal2-toast colored-toast swal2-icon-warning',
        },
        ...options
    };
    
    Swal.fire(toast);
}

/**
 * Display an info notification
 * 
 * @param {string} title - The title of the notification
 * @param {string} message - The message to display (optional)
 * @param {Object} options - Additional options to override defaults
 */
function toastInfo(title, message = '', options = {}) {
    const toast = {
        ...BatiSwalDefaults,
        icon: 'info',
        title: title,
        text: message,
        iconColor: '#2D3FE0',
        customClass: {
            ...BatiSwalDefaults.customClass,
            popup: 'swal2-toast colored-toast swal2-icon-info',
        },
        ...options
    };
    
    Swal.fire(toast);
}

/**
 * Display a confirmation dialog
 * 
 * @param {string} title - The title of the dialog
 * @param {string} message - The message to display
 * @param {Function} confirmCallback - Function to call when confirmed
 * @param {Object} options - Additional options
 * @returns {Promise} - Promise representing the SweetAlert2 dialog
 */
function confirmDialog(title, message, confirmCallback, options = {}) {
    const defaults = {
        icon: 'question',
        title: title,
        text: message,
        showCancelButton: true,
        confirmButtonColor: '#2D3FE0',
        cancelButtonColor: '#EF4444',
        confirmButtonText: 'Yes',
        cancelButtonText: 'Cancel',
        customClass: {
            confirmButton: 'btn btn-primary',
            cancelButton: 'btn btn-danger'
        },
        buttonsStyling: true,
        ...options
    };
    
    return Swal.fire(defaults).then((result) => {
        if (result.isConfirmed && typeof confirmCallback === 'function') {
            confirmCallback();
        }
        return result;
    });
}

/**
 * Display a delete confirmation dialog
 * 
 * @param {string} title - The title of the dialog (default: 'Delete Confirmation')
 * @param {string} message - The message to display (default: 'Are you sure you want to delete this item?')
 * @param {Function} confirmCallback - Function to call when confirmed
 * @param {Object} options - Additional options
 * @returns {Promise} - Promise representing the SweetAlert2 dialog
 */
function confirmDelete(title = 'Delete Confirmation', message = 'Are you sure you want to delete this item? This action cannot be undone.', confirmCallback, options = {}) {
    return confirmDialog(
        title,
        message,
        confirmCallback,
        {
            confirmButtonText: 'Delete',
            confirmButtonColor: '#EF4444',
            icon: 'warning',
            ...options
        }
    );
}

/**
 * Display an input dialog
 * 
 * @param {string} title - The title of the dialog
 * @param {string} inputLabel - The label for the input field
 * @param {Function} confirmCallback - Function to call with input value when confirmed
 * @param {Object} options - Additional options
 * @returns {Promise} - Promise representing the SweetAlert2 dialog
 */
function inputDialog(title, inputLabel, confirmCallback, options = {}) {
    const defaults = {
        title: title,
        input: 'text',
        inputLabel: inputLabel,
        showCancelButton: true,
        confirmButtonColor: '#2D3FE0',
        cancelButtonColor: '#6B7280',
        confirmButtonText: 'Submit',
        cancelButtonText: 'Cancel',
        inputValidator: (value) => {
            if (!value) {
                return 'You need to write something!';
            }
        },
        ...options
    };
    
    return Swal.fire(defaults).then((result) => {
        if (result.isConfirmed && typeof confirmCallback === 'function') {
            confirmCallback(result.value);
        }
        return result;
    });
}

// Export the functions if using with module system
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        toastSuccess,
        toastError,
        toastWarning,
        toastInfo,
        confirmDialog,
        confirmDelete,
        inputDialog
    };
}