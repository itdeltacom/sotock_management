'use strict';

document.addEventListener('DOMContentLoaded', function () {
    // Cache DOM elements
    const brandsTable = document.getElementById('brands-table');
    const brandForm = document.getElementById('brandForm');
    const brandModal = document.getElementById('brandModal');
    const createBrandBtn = document.getElementById('createBrandBtn');
    const saveBtn = document.getElementById('saveBtn');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    
    // Initialize DataTable
    let table = new DataTable('#brands-table', {
        processing: true,
        serverSide: true,
        ajax: {
            url: routes.dataUrl,
            type: 'GET'
        },
        columns: [
            { data: 'id', name: 'id' },
            { data: 'logo', name: 'logo', orderable: false, searchable: false },
            { data: 'name', name: 'name' },
            { data: 'slug', name: 'slug' },
            { data: 'cars_count', name: 'cars_count', searchable: false },
            { data: 'status', name: 'is_active' },
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
    if (createBrandBtn) {
        createBrandBtn.addEventListener('click', function() {
            resetForm();
            document.getElementById('brandModalLabel').textContent = 'Add New Brand';
            
            // Hide logo preview
            const logoPreview = document.getElementById('logo-preview');
            if (logoPreview) logoPreview.classList.add('d-none');
            
            $(brandModal).modal('show');
        });
    }
    
    if (brandForm) {
        brandForm.addEventListener('submit', handleFormSubmit);
    }
    
    // File input preview for logo
    const logoInput = document.getElementById('logo');
    if (logoInput) {
        logoInput.addEventListener('change', function() {
            const logoPreview = document.getElementById('logo-preview');
            const previewImg = logoPreview.querySelector('img');
            
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    logoPreview.classList.remove('d-none');
                }
                
                reader.readAsDataURL(this.files[0]);
            } else {
                logoPreview.classList.add('d-none');
            }
        });
    }
    
    // Handle action buttons with event delegation
    document.addEventListener('click', function(e) {
        // Edit button
        if (e.target.closest('.btn-edit')) {
            // Check permission
            if (typeof canEditBrands !== 'undefined' && !canEditBrands) {
                Swal.fire('Permission Denied', 'You do not have permission to edit brands.', 'warning');
                return;
            }
            
            const button = e.target.closest('.btn-edit');
            const brandId = button.getAttribute('data-id');
            if (brandId) handleEditBrand(brandId);
        }
        
        // Delete button
        if (e.target.closest('.btn-delete')) {
            // Check permission
            if (typeof canDeleteBrands !== 'undefined' && !canDeleteBrands) {
                Swal.fire('Permission Denied', 'You do not have permission to delete brands.', 'warning');
                return;
            }
            
            const button = e.target.closest('.btn-delete');
            const brandId = button.getAttribute('data-id');
            const brandName = button.getAttribute('data-name') || 'this brand';
            if (brandId) handleDeleteBrand(brandId, brandName);
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
        
        // Get brand ID and determine if this is an edit operation
        const brandId = document.getElementById('brand_id').value;
        const isEdit = brandId && brandId !== '';
        
        // For PUT requests, Laravel doesn't process FormData the same way as POST
        if (isEdit) {
            formData.append('_method', 'PUT');
        }
        
        // Set up request URL
        const url = isEdit ? routes.updateUrl.replace(':id', brandId) : routes.storeUrl;
        
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
                $(brandModal).modal('hide');
                
                // Reload table
                table.ajax.reload();
                
                // Show success message
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: data.message || 'Brand saved successfully',
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
     * Handle edit brand
     */
    function handleEditBrand(brandId) {
        resetForm();
        
        // Set ID and title
        document.getElementById('brand_id').value = brandId;
        document.getElementById('brandModalLabel').textContent = 'Edit Brand';
        
        // Show modal with loading overlay
        $(brandModal).modal('show');
        
        const modalBody = document.querySelector('#brandModal .modal-body');
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
        
        // Fetch brand data
        fetch(routes.editUrl.replace(':id', brandId), {
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
            
            if (data.success && data.brand) {
                const brand = data.brand;
                
                // Set form values
                document.getElementById('name').value = brand.name || '';
                document.getElementById('description').value = brand.description || '';
                document.getElementById('meta_title').value = brand.meta_title || '';
                document.getElementById('meta_description').value = brand.meta_description || '';
                document.getElementById('meta_keywords').value = brand.meta_keywords || '';
                document.getElementById('is_active').value = brand.is_active ? '1' : '0';
                
                // Show logo preview if exists
                const logoPreview = document.getElementById('logo-preview');
                const previewImg = logoPreview?.querySelector('img');
                
                if (logoPreview && previewImg && brand.logo) {
                    previewImg.src = '/storage/' + brand.logo;
                    logoPreview.classList.remove('d-none');
                } else if (logoPreview) {
                    logoPreview.classList.add('d-none');
                }
            } else {
                $(brandModal).modal('hide');
                Swal.fire('Error', 'Failed to load brand data', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            $(brandModal).modal('hide');
            Swal.fire('Error', 'Failed to load brand data', 'error');
        });
    }
    
    /**
     * Handle delete brand
     */
    function handleDeleteBrand(brandId, brandName) {
        Swal.fire({
            title: 'Confirm Delete',
            html: `Are you sure you want to delete <strong>${brandName}</strong>?<br><br>
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
                
                fetch(routes.deleteUrl.replace(':id', brandId), {
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
                        Swal.fire('Deleted!', data.message || 'Brand deleted successfully', 'success');
                    } else {
                        throw new Error(data.message || 'Failed to delete brand');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire('Error', error.message || 'Failed to delete brand', 'error');
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
        if (brandForm) brandForm.reset();
        document.getElementById('brand_id').value = '';
        clearValidationErrors();
    }
});