'use strict';

document.addEventListener('DOMContentLoaded', function () {
    // Cache DOM elements
    const permissionsTable = document.getElementById('permissions-table');
    const permissionForm = document.getElementById('permissionForm');
    const permissionModal = document.getElementById('permissionModal');
    const createPermissionBtn = document.getElementById('createPermissionBtn');
    const saveBtn = document.getElementById('saveBtn');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    
    // Initialize DataTable
    let table = new DataTable('#permissions-table', {
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
                    if (systemPermissions.includes(data)) {
                        return `<span class="badge badge-info">${data}</span>`;
                    }
                    return data;
                }
            },
            { data: 'guard_name', name: 'guard_name' },
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
        createdRow: function(row, data) {
            // Highlight system permissions
            if (systemPermissions.includes(data.name)) {
                $(row).addClass('system-permission');
            }
        }
    });
    
    // Set up event listeners
    if (createPermissionBtn) {
        createPermissionBtn.addEventListener('click', function() {
            resetForm();
            document.getElementById('permissionModalLabel').textContent = 'Add New Permission';
            $(permissionModal).modal('show');
        });
    }
    
    if (permissionForm) {
        permissionForm.addEventListener('submit', handleFormSubmit);
    }
    
    // Handle action buttons with event delegation
    document.addEventListener('click', function(e) {
        // Edit button
        if (e.target.closest('.btn-edit')) {
            const button = e.target.closest('.btn-edit');
            const permissionId = button.getAttribute('data-id');
            if (permissionId) handleEditPermission(permissionId);
        }
        
        // Delete button
        if (e.target.closest('.btn-delete')) {
            const button = e.target.closest('.btn-delete');
            const permissionId = button.getAttribute('data-id');
            const permissionName = button.getAttribute('data-name') || 'this permission';
            if (permissionId) handleDeletePermission(permissionId, permissionName);
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
        
        // Get permission ID and determine if this is an edit operation
        const permissionId = document.getElementById('permission_id').value;
        const isEdit = permissionId && permissionId !== '';
        
        // For PUT requests, Laravel doesn't process FormData the same way as POST
        // We need to explicitly handle this
        if (isEdit) {
            // Add _method field for Laravel to recognize this as a PUT request
            formData.append('_method', 'PUT');
        }
        
        // Validate the name field
        const nameInput = document.getElementById('name');
        const nameValue = nameInput.value.trim();
        
        if (!nameValue) {
            nameInput.classList.add('is-invalid');
            const nameError = document.getElementById('name-error');
            if (nameError) {
                nameError.textContent = 'Permission name is required';
                nameError.style.display = 'block';
            }
            return false;
        }
        
        // Check name format - lowercase with spaces or underscores only
        const nameRegex = /^[a-z0-9\s_]+$/;
        if (!nameRegex.test(nameValue)) {
            nameInput.classList.add('is-invalid');
            const nameError = document.getElementById('name-error');
            if (nameError) {
                nameError.textContent = 'Name should only contain lowercase letters, numbers, spaces, and underscores';
                nameError.style.display = 'block';
            }
            return false;
        }
        
        // Set up request URL
        const url = isEdit ? routes.updateUrl.replace(':id', permissionId) : routes.storeUrl;
        
        // Show loading state
        if (saveBtn) {
            saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...';
            saveBtn.disabled = true;
        }
        
        // Send request - always use POST method, but include _method field for PUT
        fetch(url, {
            method: 'POST', // Always POST for Laravel form handling
            body: formData,
            headers: {
                'X-CSRF-TOKEN': csrfToken,
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
                $('#permissionModal').modal('hide');
                
                // Reload table
                table.ajax.reload(null, false);
                
                // Show success message
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: data.message || 'Permission saved successfully',
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
     * Handle edit permission
     */
    function handleEditPermission(permissionId) {
        resetForm();
        
        // Set ID and title
        document.getElementById('permission_id').value = permissionId;
        document.getElementById('permissionModalLabel').textContent = 'Edit Permission';
        
        // Show modal with loading overlay
        $(permissionModal).modal('show');
        
        const modalBody = document.querySelector('#permissionModal .modal-body');
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
        
        // Fetch permission data
        fetch(routes.editUrl.replace(':id', permissionId), {
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
            
            if (data.permission) {
                // Set form values
                const nameInput = document.getElementById('name');
                if (nameInput) {
                    nameInput.value = data.permission.name;
                    
                    // Disable name field for system permissions
                    if (systemPermissions.includes(data.permission.name)) {
                        nameInput.setAttribute('readonly', true);
                        nameInput.parentNode.insertAdjacentHTML('beforeend', 
                            '<small class="text-danger d-block mt-1">System permissions cannot be renamed.</small>');
                    }
                }
            } else {
                $(permissionModal).modal('hide');
                Swal.fire('Error', 'Failed to load permission data', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            $(permissionModal).modal('hide');
            Swal.fire('Error', 'Failed to load permission data', 'error');
        });
    }
    
    /**
     * Handle delete permission
     */
    function handleDeletePermission(permissionId, permissionName) {
        // Fetch permission to check if it's a system permission
        fetch(routes.editUrl.replace(':id', permissionId), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.permission) {
                // Check if it's a system permission
                if (systemPermissions.includes(data.permission.name)) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Cannot Delete',
                        text: 'System permissions cannot be deleted',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000
                    });
                    return;
                }
                
                // Confirm and delete
                Swal.fire({
                    title: 'Confirm Delete',
                    html: `Are you sure you want to delete <strong>${data.permission.name}</strong>?<br><br>
                          <span class="text-danger font-weight-bold">This will remove it from all roles that use it!</span>`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Delete',
                    focusCancel: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        deletePermission(permissionId);
                    }
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire('Error', 'Failed to load permission data', 'error');
        });
    }
    
    /**
     * Delete permission
     */
    function deletePermission(permissionId) {
        Swal.fire({
            title: 'Deleting...',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });
                
        fetch(routes.destroyUrl.replace(':id', permissionId), {
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
                Swal.fire({
                    icon: 'success',
                    title: 'Deleted!',
                    text: data.message || 'Permission deleted successfully',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                });
            } else {
                throw new Error(data.message || 'Failed to delete permission');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire('Error', error.message || 'Failed to delete permission', 'error');
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
        });
    }
    
    /**
     * Reset form
     */
    function resetForm() {
        if (permissionForm) permissionForm.reset();
        
        document.getElementById('permission_id').value = '';
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