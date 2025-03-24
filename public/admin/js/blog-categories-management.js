'use strict';

document.addEventListener('DOMContentLoaded', function () {
    // Cache DOM elements
    const categoriesTable = document.getElementById('categories-table');
    const categoryForm = document.getElementById('categoryForm');
    const categoryModal = document.getElementById('categoryModal');
    const createCategoryBtn = document.getElementById('createCategoryBtn');
    const saveBtn = document.getElementById('saveBtn');
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
    const deleteModal = document.getElementById('deleteModal');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    
    // Initialize DataTable
    let table = new DataTable('#categories-table', {
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
                data: 'parent_id', 
                name: 'parent_id',
                render: function (data, type, row) {
                    return row.parent ? row.parent.name : '-';
                }
            },
            { 
                data: 'description', 
                name: 'description',
                render: function (data, type, row) {
                    if (!data) return '-';
                    return data.length > 100 ? data.substr(0, 100) + '...' : data;
                }
            },
            { data: 'status', name: 'is_active' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        order: [[0, 'desc']],
        pageLength: 10,
        responsive: true
    });
    
    // Set up event listeners
    if (createCategoryBtn) {
        createCategoryBtn.addEventListener('click', function() {
            resetForm();
            document.getElementById('categoryModalLabel').textContent = 'Add New Category';
            
            // Show the modal
            $('#categoryModal').modal('show');
        });
    }
    
    if (categoryForm) {
        categoryForm.addEventListener('submit', handleFormSubmit);
    }
    
    // Handle action buttons with event delegation
    document.addEventListener('click', function(e) {
        // Edit button
        if (e.target.closest('.btn-edit')) {
            // Check permission
            if (typeof canEditCategories !== 'undefined' && !canEditCategories) {
                Swal.fire('Permission Denied', 'You do not have permission to edit blog categories.', 'warning');
                return;
            }
            
            const button = e.target.closest('.btn-edit');
            const categoryId = button.getAttribute('data-id');
            if (categoryId) handleEditCategory(categoryId);
        }
        
        // Delete button
        if (e.target.closest('.btn-delete')) {
            // Check permission
            if (typeof canDeleteCategories !== 'undefined' && !canDeleteCategories) {
                Swal.fire('Permission Denied', 'You do not have permission to delete blog categories.', 'warning');
                return;
            }
            
            const button = e.target.closest('.btn-delete');
            const categoryId = button.getAttribute('data-id');
            const categoryName = button.getAttribute('data-name') || 'this category';
            
            // Set category name in the modal
            const categoryNameEl = document.querySelector('.category-name');
            if (categoryNameEl) {
                categoryNameEl.textContent = `Category: ${categoryName}`;
            }
            
            // Set data-id on confirm delete button
            if (confirmDeleteBtn) {
                confirmDeleteBtn.setAttribute('data-id', categoryId);
            }
            
            // Show the modal
            $('#deleteModal').modal('show');
        }
    });
    
    // Handle delete confirmation
    if (confirmDeleteBtn) {
        confirmDeleteBtn.addEventListener('click', function() {
            const categoryId = this.getAttribute('data-id');
            if (categoryId) handleDeleteCategory(categoryId);
        });
    }
    
    /**
     * Handle form submission
     */
    function handleFormSubmit(e) {
        e.preventDefault();
        
        // Reset validation UI
        clearValidationErrors();
        
        // Get form data
        const formData = new FormData(e.target);
        
        // Get category ID and determine if this is an edit operation
        const categoryId = document.getElementById('category_id').value;
        const isEdit = categoryId && categoryId !== '';
        
        // For PUT requests, Laravel doesn't process FormData the same way as POST
        if (isEdit) {
            formData.append('_method', 'PUT');
        }
        
        // Set up request URL
        const url = isEdit ? routes.updateUrl.replace(':id', categoryId) : routes.storeUrl;
        
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
                $('#categoryModal').modal('hide');
                
                // Reload table
                table.ajax.reload();
                
                // Show success message
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: data.message || 'Category saved successfully',
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
     * Handle edit category
     */
    function handleEditCategory(categoryId) {
        resetForm();
        
        // Set ID and title
        document.getElementById('category_id').value = categoryId;
        document.getElementById('categoryModalLabel').textContent = 'Edit Category';
        
        // Show modal with loading overlay
        $('#categoryModal').modal('show');
        
        const modalBody = document.querySelector('#categoryModal .modal-body');
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
        
        // Fetch category data
        fetch(routes.editUrl.replace(':id', categoryId), {
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
            
            if (data.success && data.category) {
                const category = data.category;
                
                // Set basic info
                document.getElementById('name').value = category.name || '';
                document.getElementById('parent_id').value = category.parent_id || '';
                document.getElementById('description').value = category.description || '';
                document.getElementById('is_active').value = category.is_active ? '1' : '0';
                
                // Set SEO info
                document.getElementById('meta_title').value = category.meta_title || '';
                document.getElementById('meta_description').value = category.meta_description || '';
                document.getElementById('meta_keywords').value = category.meta_keywords || '';
            } else {
                $('#categoryModal').modal('hide');
                Swal.fire('Error', 'Failed to load category data', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            $('#categoryModal').modal('hide');
            Swal.fire('Error', 'Failed to load category data', 'error');
        });
    }
    
    /**
     * Handle delete category
     */
    function handleDeleteCategory(categoryId) {
        // Close delete modal
        $('#deleteModal').modal('hide');
        
        // Show loading indicator
        Swal.fire({
            title: 'Deleting...',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });
        
        fetch(routes.destroyUrl.replace(':id', categoryId), {
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
                Swal.fire('Deleted!', data.message || 'Category deleted successfully', 'success');
            } else {
                throw new Error(data.message || 'Failed to delete category');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire('Error', error.message || 'Failed to delete category', 'error');
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
        if (categoryForm) categoryForm.reset();
        document.getElementById('category_id').value = '';
        clearValidationErrors();
    }
});