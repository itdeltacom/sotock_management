'use strict';

document.addEventListener('DOMContentLoaded', function () {
    // Cache DOM elements
    const tagsTable = document.getElementById('tags-table');
    const tagForm = document.getElementById('tagForm');
    const tagModal = document.getElementById('tagModal');
    const createTagBtn = document.getElementById('createTagBtn');
    const saveBtn = document.getElementById('saveBtn');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    
    // Initialize DataTable
    let table = new DataTable('#tags-table', {
        processing: true,
        serverSide: true,
        ajax: {
            url: routes.dataUrl,
            type: 'GET'
        },
        columns: [
            { data: 'id', name: 'id' },
            { data: 'name', name: 'name' },
            { data: 'slug', name: 'slug' },
            { 
                data: 'description', 
                name: 'description',
                render: function (data, type, row) {
                    if (!data) return '-';
                    return data.length > 100 ? data.substr(0, 100) + '...' : data;
                }
            },
            { data: 'post_count', name: 'posts_count', searchable: false },
            { data: 'status', name: 'is_active' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        order: [[0, 'desc']],
        pageLength: 10,
        responsive: true
    });
    
    // Set up event listeners
    if (createTagBtn) {
        createTagBtn.addEventListener('click', function() {
            resetForm();
            document.getElementById('tagModalLabel').textContent = 'Add New Tag';
            
            // Reset tabs to first tab
            const tabTrigger = document.querySelector('#basic-tab');
            if (tabTrigger) {
                const bootstrapTab = new bootstrap.Tab(tabTrigger);
                bootstrapTab.show();
            }
            
            // Show modal using Bootstrap 5 modal
            const bsModal = new bootstrap.Modal(tagModal);
            bsModal.show();
        });
    }
    
    if (tagForm) {
        tagForm.addEventListener('submit', handleFormSubmit);
    }
    
    // Handle action buttons with event delegation
    document.addEventListener('click', function(e) {
        // Edit button
        if (e.target.closest('.btn-edit')) {
            // Check permission
            if (typeof canEditTags !== 'undefined' && !canEditTags) {
                Swal.fire('Permission Denied', 'You do not have permission to edit blog tags.', 'warning');
                return;
            }
            
            const button = e.target.closest('.btn-edit');
            const tagId = button.getAttribute('data-id');
            if (tagId) handleEditTag(tagId);
        }
        
        // Delete button
        if (e.target.closest('.btn-delete')) {
            // Check permission
            if (typeof canDeleteTags !== 'undefined' && !canDeleteTags) {
                Swal.fire('Permission Denied', 'You do not have permission to delete blog tags.', 'warning');
                return;
            }
            
            const button = e.target.closest('.btn-delete');
            const tagId = button.getAttribute('data-id');
            const tagName = button.getAttribute('data-name') || 'this tag';
            if (tagId) handleDeleteTag(tagId, tagName);
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
        
        // Get tag ID and determine if this is an edit operation
        const tagId = document.getElementById('tag_id').value;
        const isEdit = tagId && tagId !== '';
        
        // For PUT requests, Laravel doesn't process FormData the same way as POST
        if (isEdit) {
            formData.append('_method', 'PUT');
        }
        
        // Set up request URL
        const url = isEdit ? routes.updateUrl.replace(':id', tagId) : routes.storeUrl;
        
        // Show loading state
        if (saveBtn) {
            saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...';
            saveBtn.disabled = true;
        }
        
        // Send request
        fetch(url, {
            method: 'POST',
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
            if (data.success) {
                // Hide modal
                const bsModal = bootstrap.Modal.getInstance(tagModal);
                if (bsModal) bsModal.hide();
                
                // Reload table
                table.ajax.reload();
                
                // Show success message
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: data.message || 'Tag saved successfully',
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
                
                // Show notification
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: 'Please check the form for errors',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 5000
                });
            } else {
                // Show error notification
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.data?.message || 'An error occurred',
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
     * Handle edit tag
     */
    function handleEditTag(tagId) {
        resetForm();
        
        // Set ID and title
        document.getElementById('tag_id').value = tagId;
        document.getElementById('tagModalLabel').textContent = 'Edit Tag';
        
        // Reset tabs to first tab
        const tabTrigger = document.querySelector('#basic-tab');
        if (tabTrigger) {
            const bootstrapTab = new bootstrap.Tab(tabTrigger);
            bootstrapTab.show();
        }
        
        // Show modal with loading overlay
        const bsModal = new bootstrap.Modal(tagModal);
        bsModal.show();
        
        const modalBody = document.querySelector('#tagModal .modal-body');
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
        
        // Fetch tag data
        fetch(routes.editUrl.replace(':id', tagId), {
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
            
            if (data.success && data.tag) {
                const tag = data.tag;
                
                // Set basic info
                document.getElementById('name').value = tag.name || '';
                document.getElementById('description').value = tag.description || '';
                document.getElementById('is_active').value = tag.is_active ? '1' : '0';
                
                // Set SEO info
                document.getElementById('meta_title').value = tag.meta_title || '';
                document.getElementById('meta_description').value = tag.meta_description || '';
                document.getElementById('meta_keywords').value = tag.meta_keywords || '';
                
                // If CKEditor is initialized (for description), set its content
                if (window.editor) {
                    window.editor.setData(tag.description || '');
                }
            } else {
                const bsModal = bootstrap.Modal.getInstance(tagModal);
                if (bsModal) bsModal.hide();
                Swal.fire('Error', 'Failed to load tag data', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            const bsModal = bootstrap.Modal.getInstance(tagModal);
            if (bsModal) bsModal.hide();
            Swal.fire('Error', 'Failed to load tag data', 'error');
        });
    }
    
    /**
     * Handle delete tag
     */
    function handleDeleteTag(tagId, tagName) {
        Swal.fire({
            title: 'Confirm Delete',
            html: `Are you sure you want to delete <strong>${tagName}</strong>?<br><br>
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
                
                fetch(routes.destroyUrl.replace(':id', tagId), {
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
                        table.ajax.reload();
                        Swal.fire('Deleted!', data.message || 'Tag deleted successfully', 'success');
                    } else {
                        throw new Error(data.message || 'Failed to delete tag');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire('Error', error.message || 'Failed to delete tag', 'error');
                });
            }
        });
    }
    
    /**
     * Navigate to tab containing a specific field
     */
    function navigateToTabWithField(fieldId) {
        // Find which tab contains the field with error
        const field = document.getElementById(fieldId);
        if (!field) return;
        
        const tabPane = field.closest('.tab-pane');
        if (!tabPane) return;
        
        const tabId = tabPane.id;
        const tabLink = document.querySelector(`button[data-bs-target="#${tabId}"]`);
        
        if (tabLink) {
            const bootstrapTab = new bootstrap.Tab(tabLink);
            bootstrapTab.show();
        }
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
        
        // Navigate to the tab with the first error
        const firstErrorField = Object.keys(errors)[0] || '';
        if (firstErrorField) {
            navigateToTabWithField(firstErrorField);
        }
    }
    
    /**
     * Reset form
     */
    function resetForm() {
        if (tagForm) tagForm.reset();
        document.getElementById('tag_id').value = '';
        clearValidationErrors();
        
        // If CKEditor exists, clear it
        if (window.editor) {
            window.editor.setData('');
        }
    }
    
    // Expose functions for external use (needed for CKEditor integration)
    window.handleEditTag = handleEditTag;
});