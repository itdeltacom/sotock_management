'use strict';

document.addEventListener('DOMContentLoaded', function () {
    // Cache DOM elements
    const adminsTable = document.getElementById('admins-table');
    const adminForm = document.getElementById('adminForm');
    const adminModal = document.getElementById('adminModal');
    const createAdminBtn = document.getElementById('createAdminBtn');
    const saveBtn = document.getElementById('saveBtn');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    
    // Initialize DataTable
    let table = new DataTable('#admins-table', {
        processing: true,
        serverSide: true,
        ajax: {
            url: routes.dataUrl,
            type: 'GET'
        },
        columns: [
            { data: 'id', name: 'id' },
            { data: 'name', name: 'name' },
            { data: 'email', name: 'email' },
            {
                data: 'roles',
                name: 'roles',
                render: function (data) {
                    if (!data.length) return '<span class="badge badge-secondary">No roles</span>';
                    
                    return data.map(role => 
                        `<span class="badge badge-primary badge-role">${role}</span>`
                    ).join(' ');
                }
            },
            {
                data: 'is_active',
                name: 'is_active',
                render: function (data) {
                    return data == 1
                        ? '<span class="badge badge-success">Active</span>'
                        : '<span class="badge badge-danger">Inactive</span>';
                }
            },
            {
                data: 'action',
                name: 'action',
                orderable: false,
                searchable: false
            }
        ],
        order: [[0, 'desc']],
        pageLength: 10,
        responsive: true,
        language: {
            paginate: {
                next: '<i class="fas fa-angle-right"></i>',
                previous: '<i class="fas fa-angle-left"></i>'
            }
        }
    });
    
    // Set up event listeners
    if (createAdminBtn) {
        createAdminBtn.addEventListener('click', function() {
            resetForm();
            document.getElementById('adminModalLabel').textContent = 'Add New Admin';
            
            // Set password fields as required for new admin
            const passwordInput = document.getElementById('password');
            const passwordConfirmInput = document.getElementById('password_confirmation');
            
            if (passwordInput) passwordInput.setAttribute('required', 'required');
            if (passwordConfirmInput) passwordConfirmInput.setAttribute('required', 'required');
            
            // Hide password help text
            const passwordHelp = document.querySelector('.password-help');
            if (passwordHelp) passwordHelp.style.display = 'none';
            
            $(adminModal).modal('show');
        });
    }
    
    if (adminForm) {
        adminForm.addEventListener('submit', handleFormSubmit);
    }
    
    // Handle action buttons with event delegation
    document.addEventListener('click', function(e) {
        // Edit button
        if (e.target.closest('.btn-edit')) {
            const button = e.target.closest('.btn-edit');
            const adminId = button.getAttribute('data-id');
            if (adminId) handleEditAdmin(adminId);
        }
        
        // Delete button
        if (e.target.closest('.btn-delete')) {
            const button = e.target.closest('.btn-delete');
            const adminId = button.getAttribute('data-id');
            const adminName = button.getAttribute('data-name') || 'this admin';
            if (adminId) handleDeleteAdmin(adminId, adminName);
        }
        
        // Toggle password visibility
        if (e.target.closest('.toggle-password')) {
            const button = e.target.closest('.toggle-password');
            const input = button.closest('.input-group').querySelector('input');
            const icon = button.querySelector('i');
            
            if (!input || !icon) return;
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    });
    
    /**
     * Handle form submission
     */
    function handleFormSubmit(e) {
        e.preventDefault();
        
        // Reset validation UI
        clearValidationErrors();
        
        // Get form data
        const formData = new FormData(e.target);
        
        // Get admin ID and determine if this is an edit operation
        const adminId = document.getElementById('admin_id').value;
        const isEdit = adminId && adminId !== '';
        
        // For PUT requests, Laravel doesn't process FormData the same way as POST
        // We need to explicitly handle this
        if (isEdit) {
            // Add _method field for Laravel to recognize this as a PUT request
            formData.append('_method', 'PUT');
        }
        
        // Set up request URL
        const url = isEdit ? routes.updateUrl.replace(':id', adminId) : routes.storeUrl;
        
        // Show loading state
        const saveBtn = document.getElementById('saveBtn');
        if (saveBtn) {
            saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...';
            saveBtn.disabled = true;
        }
        
        // Send request - always use POST method, but include _method field for PUT
        fetch(url, {
            method: 'POST', // Always POST for Laravel form handling
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(data => {
                    throw { status: response.status, data: data };
                });
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Hide modal
                $('#adminModal').modal('hide');
                
                // Reload table
                $('#admins-table').DataTable().ajax.reload();
                
                // Show success message
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: data.message || 'Admin saved successfully',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                });
            } else {
                throw new Error(data.message || 'An error occurred');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            
            // Handle validation errors
            if (error.status === 422 && error.data && error.data.errors) {
                displayValidationErrors(error.data.errors);
            } else {
                // Show error notification
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.message || 'An error occurred',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 5000
                });
            }
        })
        .finally(() => {
            // Reset button state
            if (saveBtn) {
                saveBtn.innerHTML = 'Save';
                saveBtn.disabled = false;
            }
        });
    }
    
    /**
     * Handle edit admin
     */
    function handleEditAdmin(adminId) {
        resetForm();
        
        // Set ID and title
        document.getElementById('admin_id').value = adminId;
        document.getElementById('adminModalLabel').textContent = 'Edit Admin User';
        
        // Remove required attribute from password fields
        const passwordInput = document.getElementById('password');
        const passwordConfirmInput = document.getElementById('password_confirmation');
        
        if (passwordInput) passwordInput.removeAttribute('required');
        if (passwordConfirmInput) passwordConfirmInput.removeAttribute('required');
        
        // Show password help text
        const passwordHelp = document.querySelector('.password-help');
        if (passwordHelp) passwordHelp.style.display = 'block';
        
        // Show modal with loading overlay
        $(adminModal).modal('show');
        
        const modalBody = document.querySelector('#adminModal .modal-body');
        if (modalBody) {
            const loadingDiv = document.createElement('div');
            loadingDiv.id = 'loading-overlay';
            loadingDiv.className = 'position-absolute bg-white d-flex justify-content-center align-items-center';
            loadingDiv.style.cssText = 'left: 0; top: 0; right: 0; bottom: 0; z-index: 10;';
            loadingDiv.innerHTML = '<div class="spinner-border text-primary"></div>';
            
            // Add loading overlay
            modalBody.style.position = 'relative';
            modalBody.appendChild(loadingDiv);
        }
        
        // Fetch admin data
        fetch(routes.editUrl.replace(':id', adminId), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            // Remove loading overlay
            const overlay = document.getElementById('loading-overlay');
            if (overlay) overlay.remove();
            
            if (data.success) {
                // Set form values
                const nameInput = document.getElementById('name');
                const emailInput = document.getElementById('email');
                const phoneInput = document.getElementById('phone');
                const positionInput = document.getElementById('position');
                const departmentInput = document.getElementById('department');
                const isActiveInput = document.getElementById('is_active');
                
                if (nameInput) nameInput.value = data.admin.name;
                if (emailInput) emailInput.value = data.admin.email;
                if (phoneInput) phoneInput.value = data.admin.phone || '';
                if (positionInput) positionInput.value = data.admin.position || '';
                if (departmentInput) departmentInput.value = data.admin.department || '';
                if (isActiveInput) isActiveInput.value = data.admin.is_active;
                
                // Clear any previously checked roles
                document.querySelectorAll('input[name="roles[]"]').forEach(checkbox => {
                    checkbox.checked = false;
                });
                
                // Check admin roles
                if (data.admin.roles) {
                    data.admin.roles.forEach(roleId => {
                        const checkbox = document.getElementById(`role_${roleId}`);
                        if (checkbox) checkbox.checked = true;
                    });
                }
            } else {
                $(adminModal).modal('hide');
                Swal.fire('Error', 'Failed to load admin data', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            $(adminModal).modal('hide');
            Swal.fire('Error', 'Failed to load admin data', 'error');
        });
    }
    
    /**
     * Handle delete admin
     */
    function handleDeleteAdmin(adminId, adminName) {
        Swal.fire({
            title: 'Confirm Delete',
            html: `Are you sure you want to delete <strong>${adminName}</strong>?<br><br>
                  <span class="text-danger font-weight-bold">This action cannot be undone!</span>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Delete',
            focusCancel: true
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Deleting...',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });
                
                fetch(routes.deleteUrl.replace(':id', adminId), {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        table.ajax.reload(null, false);
                        Swal.fire('Deleted!', data.message || 'Admin deleted successfully', 'success');
                    } else {
                        throw new Error(data.message || 'Failed to delete admin');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire('Error', error.message || 'Failed to delete admin', 'error');
                });
            }
        });
    }
    
    /**
     * Clear validation errors
     */
    function clearValidationErrors() {
        document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        document.querySelectorAll('.invalid-feedback').forEach(el => {
            el.textContent = '';
            el.style.display = 'none';
        });
        
        const rolesContainer = document.querySelector('.roles-container');
        if (rolesContainer) rolesContainer.classList.remove('border-danger');
    }
    
    /**
     * Display validation errors
     */
    function displayValidationErrors(errors) {
        clearValidationErrors();
        
        Object.keys(errors).forEach(field => {
            const input = document.getElementById(field);
            const errorEl = document.getElementById(`${field}-error`);
            
            if (input) input.classList.add('is-invalid');
            
            if (errorEl && errors[field][0]) {
                errorEl.textContent = errors[field][0];
                errorEl.style.display = 'block';
            }
            
            // Special handling for roles
            if (field === 'roles') {
                const container = document.querySelector('.roles-container');
                const rolesError = document.getElementById('roles-error');
                
                if (container) container.classList.add('border-danger');
                if (rolesError) {
                    rolesError.textContent = errors[field][0];
                    rolesError.style.display = 'block';
                }
            }
        });
    }
    
    /**
     * Reset form
     */
    function resetForm() {
        if (adminForm) adminForm.reset();
        
        document.getElementById('admin_id').value = '';
        clearValidationErrors();
        
        // Uncheck all roles
        const roleCheckboxes = document.querySelectorAll('input[name="roles[]"]');
        roleCheckboxes.forEach(checkbox => {
            checkbox.checked = false;
        });
    }
});