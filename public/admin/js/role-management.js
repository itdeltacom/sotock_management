'use strict';

document.addEventListener('DOMContentLoaded', function () {
    // Cache DOM elements
    const rolesTable = document.getElementById('roles-table');
    const roleForm = document.getElementById('roleForm');
    const roleModal = document.getElementById('roleModal');
    const createRoleBtn = document.getElementById('createRoleBtn');
    const saveBtn = document.getElementById('saveBtn');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    
    // Initialize DataTable
    let table = new DataTable('#roles-table', {
        processing: true,
        serverSide: true,
        ajax: {
            url: routes.dataUrl,
            type: 'GET'
        },
        columns: [
            { data: 'id', name: 'id' },
            { 
                data: 'name', 
                name: 'name',
                render: function(data) {
                    if (data.toLowerCase() === 'super admin') {
                        return `<span class="badge badge-primary">${data}</span>`;
                    }
                    return data;
                }
            },
            { data: 'guard_name', name: 'guard_name' },
            { data: 'permissions_list', name: 'permissions_list' },
            { data: 'permissions_count', name: 'permissions_count' },
            { 
                data: 'created_at', 
                name: 'created_at',
                render: function(data) {
                    return new Date(data).toLocaleDateString();
                }
            },
            { data: 'action', name: 'action', orderable: false, searchable: false }
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
    if (createRoleBtn) {
        createRoleBtn.addEventListener('click', function() {
            resetForm();
            document.getElementById('roleModalLabel').textContent = 'Add New Role';
            $(roleModal).modal('show');
        });
    }
    
    if (roleForm) {
        roleForm.addEventListener('submit', handleFormSubmit);
    }
    
    // Handle action buttons with event delegation
    document.addEventListener('click', function(e) {
        // View role
        if (e.target.closest('.btn-view')) {
            const button = e.target.closest('.btn-view');
            const roleId = button.getAttribute('data-id');
            if (roleId) handleViewRole(roleId);
        }
        
        // Edit role
        if (e.target.closest('.btn-edit')) {
            const button = e.target.closest('.btn-edit');
            const roleId = button.getAttribute('data-id');
            if (roleId) handleEditRole(roleId);
        }
        
        // Delete role
        if (e.target.closest('.btn-delete')) {
            const button = e.target.closest('.btn-delete');
            const roleId = button.getAttribute('data-id');
            const roleName = button.getAttribute('data-name') || 'this role';
            if (roleId) handleDeleteRole(roleId, roleName);
        }
    });
    
    // Select all permissions checkbox
    const selectAllCheckbox = document.getElementById('select-all-permissions');
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const isChecked = this.checked;
            document.querySelectorAll('.permission-checkbox').forEach(function(checkbox) {
                checkbox.checked = isChecked;
            });
        });
    }
    
 /**
 * Handle form submission with proper method handling for PUT requests
 */
function handleFormSubmit(e) {
    e.preventDefault();
    
    // Reset validation UI
    clearValidationErrors();
    
    // Get form data
    const formData = new FormData(e.target);
    
    // Get role ID and determine if this is an edit operation
    const roleId = document.getElementById('role_id').value;
    const isEdit = roleId && roleId !== '';
    
    // For PUT requests, Laravel doesn't process FormData the same way as POST
    // We need to explicitly handle this
    if (isEdit) {
        // Add _method field for Laravel to recognize this as a PUT request
        formData.append('_method', 'PUT');
    }
    
    // Set up request URL
    const url = isEdit ? routes.updateUrl.replace(':id', roleId) : routes.storeUrl;
    
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
        if (data.status === 'success') {
            // Hide modal
            $('#roleModal').modal('hide');
            
            // Reload table
            $('#roles-table').DataTable().ajax.reload();
            
            // Show success message
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: data.message || 'Role saved successfully',
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
     * Handle view role
     */
    function handleViewRole(roleId) {
        // Show modal with loading
        $('#viewRoleModal').modal('show');
        
        const roleInfo = document.querySelector('.role-info');
        if (roleInfo) {
            roleInfo.innerHTML = '<div class="text-center p-3"><div class="spinner-border text-primary"></div></div>';
        }
        
        // Fetch role data
        fetch(routes.showUrl.replace(':id', roleId), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success' && roleInfo) {
                const nameEl = document.getElementById('view-role-name');
                const guardEl = document.getElementById('view-role-guard');
                const createdEl = document.getElementById('view-role-created');
                const permsList = document.getElementById('view-role-permissions');
                
                if (nameEl) nameEl.textContent = data.role.name;
                if (guardEl) guardEl.textContent = data.role.guard_name;
                if (createdEl) createdEl.textContent = new Date(data.role.created_at).toLocaleString();
                
                if (permsList) {
                    permsList.innerHTML = '';
                    if (data.rolePermissions && data.rolePermissions.length > 0) {
                        data.rolePermissions.forEach(perm => {
                            permsList.innerHTML += `<li class="list-group-item py-2">${perm}</li>`;
                        });
                    } else {
                        permsList.innerHTML = '<li class="list-group-item py-2">No permissions assigned</li>';
                    }
                }
            } else {
                $('#viewRoleModal').modal('hide');
                Swal.fire('Error', 'Failed to load role details', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            $('#viewRoleModal').modal('hide');
            Swal.fire('Error', 'Failed to load role details', 'error');
        });
    }
    
    /**
     * Handle edit role
     */
    function handleEditRole(roleId) {
        resetForm();
        
        // Set ID and title
        document.getElementById('role_id').value = roleId;
        document.getElementById('roleModalLabel').textContent = 'Edit Role';
        
        // Show modal with loading overlay
        $(roleModal).modal('show');
        
        const modalBody = document.querySelector('#roleModal .modal-body');
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
        
        // Fetch role data
        fetch(routes.editUrl.replace(':id', roleId), {
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
            
            if (data.status === 'success') {
                // Set form values
                const nameInput = document.getElementById('name');
                if (nameInput) nameInput.value = data.role.name;
                
                // Clear any previously checked permissions
                document.querySelectorAll('.permission-checkbox').forEach(checkbox => {
                    checkbox.checked = false;
                });
                
                // Check permissions
                if (data.rolePermissions) {
                    data.rolePermissions.forEach(id => {
                        const checkbox = document.getElementById(`permission_${id}`);
                        if (checkbox) checkbox.checked = true;
                    });
                }
                
                // Update select all checkbox
                updateSelectAllCheckbox();
                
                // Disable name for super admin
                if (data.role.name.toLowerCase() === 'super admin' && nameInput) {
                    nameInput.setAttribute('readonly', true);
                    nameInput.parentNode.insertAdjacentHTML('beforeend', 
                        '<small class="text-danger d-block mt-1">The Super Admin role name cannot be changed.</small>');
                }
            } else {
                $(roleModal).modal('hide');
                Swal.fire('Error', 'Failed to load role data', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            $(roleModal).modal('hide');
            Swal.fire('Error', 'Failed to load role data', 'error');
        });
    }
    
    /**
     * Update select all checkbox state
     */
    function updateSelectAllCheckbox() {
        const selectAllCheckbox = document.getElementById('select-all-permissions');
        if (!selectAllCheckbox) return;
        
        const allCheckboxes = document.querySelectorAll('.permission-checkbox');
        const checkedCheckboxes = document.querySelectorAll('.permission-checkbox:checked');
        
        selectAllCheckbox.checked = allCheckboxes.length === checkedCheckboxes.length;
        selectAllCheckbox.indeterminate = checkedCheckboxes.length > 0 && checkedCheckboxes.length < allCheckboxes.length;
    }
    
    /**
     * Handle delete role
     */
    function handleDeleteRole(roleId, roleName) {
        Swal.fire({
            title: 'Confirm Delete',
            html: `Are you sure you want to delete <strong>${roleName}</strong>?<br><br>
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
                
                fetch(routes.destroyUrl.replace(':id', roleId), {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        table.ajax.reload(null, false);
                        Swal.fire('Deleted!', data.message || 'Role deleted successfully', 'success');
                    } else {
                        throw new Error(data.message || 'Failed to delete role');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire('Error', error.message || 'Failed to delete role', 'error');
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
        
        const permissionsContainer = document.querySelector('.permissions-container');
        if (permissionsContainer) permissionsContainer.classList.remove('border-danger');
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
            
            // Special handling for permissions
            if (field === 'permissions') {
                const container = document.querySelector('.permissions-container');
                const permError = document.getElementById('permissions-error');
                
                if (container) container.classList.add('border-danger');
                if (permError) {
                    permError.textContent = errors[field][0];
                    permError.style.display = 'block';
                }
            }
        });
    }
    
    /**
     * Reset form
     */
    function resetForm() {
        if (roleForm) roleForm.reset();
        
        document.getElementById('role_id').value = '';
        clearValidationErrors();
        
        // Remove any readonly attributes or warnings
        const nameInput = document.getElementById('name');
        if (nameInput) {
            nameInput.removeAttribute('readonly');
            const warning = nameInput.parentNode.querySelector('.text-danger');
            if (warning) warning.remove();
        }
    }
});