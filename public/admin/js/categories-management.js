'use strict';

document.addEventListener('DOMContentLoaded', function () {
    // Cache DOM elements
    const categoriesTable = document.getElementById('categories-table');
    const categoryForm = document.getElementById('categoryForm');
    const categoryModal = document.getElementById('categoryModal');
    const createCategoryBtn = document.getElementById('createCategoryBtn');
    const saveBtn = document.getElementById('saveBtn');
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
            { data: 'image', name: 'image', orderable: false, searchable: false },
            { data: 'name', name: 'name' },
            { data: 'slug', name: 'slug' },
            { data: 'cars_count', name: 'cars_count', searchable: false },
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
            
            // Hide image preview
            const imagePreview = document.getElementById('image-preview');
            if (imagePreview) imagePreview.classList.add('d-none');
            
            $(categoryModal).modal('show');
        });
    }
    
    if (categoryForm) {
        categoryForm.addEventListener('submit', handleFormSubmit);
    }
    
    // File input preview
    const imageInput = document.getElementById('image');
    if (imageInput) {
        imageInput.addEventListener('change', function() {
            const imagePreview = document.getElementById('image-preview');
            const previewImg = imagePreview.querySelector('img');
            
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    imagePreview.classList.remove('d-none');
                }
                
                reader.readAsDataURL(this.files[0]);
            } else {
                imagePreview.classList.add('d-none');
            }
        });
    }
    
    // Handle action buttons with event delegation
    document.addEventListener('click', function(e) {
        // Edit button
        if (e.target.closest('.btn-edit')) {
            // Check permission
            if (typeof canEditCategories !== 'undefined' && !canEditCategories) {
                Swal.fire('Permission Denied', 'You do not have permission to edit categories.', 'warning');
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
                Swal.fire('Permission Denied', 'You do not have permission to delete categories.', 'warning');
                return;
            }
            
            const button = e.target.closest('.btn-delete');
            const categoryId = button.getAttribute('data-id');
            const categoryName = button.getAttribute('data-name') || 'this category';
            if (categoryId) handleDeleteCategory(categoryId, categoryName);
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
                $('#categoryModal').modal('hide');
                
                // Reload table
                $('#categories-table').DataTable().ajax.reload();
                
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
        $(categoryModal).modal('show');
        
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
                
                // Set form values
                document.getElementById('name').value = category.name || '';
                document.getElementById('description').value = category.description || '';
                document.getElementById('meta_title').value = category.meta_title || '';
                document.getElementById('meta_description').value = category.meta_description || '';
                document.getElementById('meta_keywords').value = category.meta_keywords || '';
                document.getElementById('is_active').value = category.is_active ? '1' : '0';
                
                // Show image preview if exists
                const imagePreview = document.getElementById('image-preview');
                const previewImg = imagePreview?.querySelector('img');
                
                if (imagePreview && previewImg && category.image) {
                    previewImg.src = '/storage/' + category.image;
                    imagePreview.classList.remove('d-none');
                } else if (imagePreview) {
                    imagePreview.classList.add('d-none');
                }
            } else {
                $(categoryModal).modal('hide');
                Swal.fire('Error', 'Failed to load category data', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            $(categoryModal).modal('hide');
            Swal.fire('Error', 'Failed to load category data', 'error');
        });
    }
    
    /**
     * Handle delete category
     */
    function handleDeleteCategory(categoryId, categoryName) {
        Swal.fire({
            title: 'Confirm Delete',
            html: `Are you sure you want to delete <strong>${categoryName}</strong>?<br><br>
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
                
                fetch(routes.deleteUrl.replace(':id', categoryId), {
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