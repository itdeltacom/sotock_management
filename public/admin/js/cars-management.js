'use strict';

document.addEventListener('DOMContentLoaded', function () {
    // Cache DOM elements
    const carsTable = document.getElementById('cars-table');
    const carForm = document.getElementById('carForm');
    const carModal = document.getElementById('carModal');
    const createCarBtn = document.getElementById('createCarBtn');
    const saveBtn = document.getElementById('saveBtn');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const removedImagesField = document.getElementById('removed_images');
    
    // Track removed images
    let removedImages = [];
    
    // Initialize DataTable
    let table = new DataTable('#cars-table', {
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
            { data: 'brand_name', name: 'brand.name' },
            { data: 'category_name', name: 'category.name' },
            { data: 'price', name: 'price_per_day' },
            { data: 'status', name: 'is_available' },
            { data: 'booking_count', name: 'booking_count', searchable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        order: [[0, 'desc']],
        pageLength: 10,
        responsive: true
    });
    
    // Set up event listeners
    if (createCarBtn) {
        createCarBtn.addEventListener('click', function() {
            resetForm();
            document.getElementById('carModalLabel').textContent = 'Add New Car';
            
            // Reset tabs to first tab
            const tabTrigger = document.querySelector('#basic-tab');
            if (tabTrigger) {
                const bootstrapTab = new bootstrap.Tab(tabTrigger);
                bootstrapTab.show();
            }
            
            // Hide image previews
            document.getElementById('main_image_preview').classList.add('d-none');
            document.getElementById('gallery_container').classList.add('d-none');
            
            // Reset removed images tracking
            removedImages = [];
            removedImagesField.value = '';
            
            // Show modal using Bootstrap 5 modal
            const bsModal = new bootstrap.Modal(carModal);
            bsModal.show();
        });
    }
    
    if (carForm) {
        carForm.addEventListener('submit', handleFormSubmit);
    }
    
    // File input preview for main image
    const mainImageInput = document.getElementById('main_image');
    if (mainImageInput) {
        mainImageInput.addEventListener('change', function() {
            const imagePreview = document.getElementById('main_image_preview');
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
            if (typeof canEditCars !== 'undefined' && !canEditCars) {
                Swal.fire('Permission Denied', 'You do not have permission to edit cars.', 'warning');
                return;
            }
            
            const button = e.target.closest('.btn-edit');
            const carId = button.getAttribute('data-id');
            if (carId) handleEditCar(carId);
        }
        
        // Delete button
        if (e.target.closest('.btn-delete')) {
            // Check permission
            if (typeof canDeleteCars !== 'undefined' && !canDeleteCars) {
                Swal.fire('Permission Denied', 'You do not have permission to delete cars.', 'warning');
                return;
            }
            
            const button = e.target.closest('.btn-delete');
            const carId = button.getAttribute('data-id');
            const carName = button.getAttribute('data-name') || 'this car';
            if (carId) handleDeleteCar(carId, carName);
        }
        
        // Remove image button
        if (e.target.closest('.btn-remove-image')) {
            const button = e.target.closest('.btn-remove-image');
            const imageId = button.getAttribute('data-id');
            const imageContainer = button.closest('.col-md-3');
            
            if (imageId && imageContainer) {
                // Add to removed images list
                removedImages.push(imageId);
                removedImagesField.value = removedImages.join(',');
                // Remove the element from the DOM
                imageContainer.remove();
                
                Swal.fire({
                    icon: 'success',
                    title: 'Image removed',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                });
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
        
        // Get car ID and determine if this is an edit operation
        const carId = document.getElementById('car_id').value;
        const isEdit = carId && carId !== '';
        
        // For PUT requests, Laravel doesn't process FormData the same way as POST
        if (isEdit) {
            formData.append('_method', 'PUT');
        }
        
        // Set up request URL
        const url = isEdit ? routes.updateUrl.replace(':id', carId) : routes.storeUrl;
        
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
                const bsModal = bootstrap.Modal.getInstance(carModal);
                if (bsModal) bsModal.hide();
                
                // Reload table
                table.ajax.reload();
                
                // Show success message
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: data.message || 'Car saved successfully',
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
     * Handle edit car
     */
    function handleEditCar(carId) {
        resetForm();
        
        // Set ID and title
        document.getElementById('car_id').value = carId;
        document.getElementById('carModalLabel').textContent = 'Edit Car';
        
        // Reset tabs to first tab
        const tabTrigger = document.querySelector('#basic-tab');
        if (tabTrigger) {
            const bootstrapTab = new bootstrap.Tab(tabTrigger);
            bootstrapTab.show();
        }
        
        // Reset removed images tracking
        removedImages = [];
        removedImagesField.value = '';
        
        // Show modal with loading overlay
        const bsModal = new bootstrap.Modal(carModal);
        bsModal.show();
        
        const modalBody = document.querySelector('#carModal .modal-body');
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
        
        // Fetch car data
        fetch(routes.editUrl.replace(':id', carId), {
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
            
            if (data.success && data.car) {
                const car = data.car;
                
                // Set basic info
                document.getElementById('name').value = car.name || '';
                document.getElementById('brand_id').value = car.brand_id || '';
                document.getElementById('category_id').value = car.category_id || '';
                document.getElementById('price_per_day').value = car.price_per_day || '';
                document.getElementById('discount_percentage').value = car.discount_percentage || 0;
                document.getElementById('is_available').value = car.is_available ? '1' : '0';
                
                // Set the description field
                // CKEditor will get this value from the event listener set up in the Blade file
                document.getElementById('description').value = car.description || '';
                
                // Set details
                document.getElementById('seats').value = car.seats || 5;
                document.getElementById('transmission').value = car.transmission || 'automatic';
                document.getElementById('fuel_type').value = car.fuel_type || 'petrol';
                
                // Handle the rename from engine_type to engine_capacity
                const engineCapacityField = document.getElementById('engine_capacity');
                const engineTypeField = document.getElementById('engine_type');
                if (engineCapacityField) {
                    engineCapacityField.value = car.engine_capacity || car.engine_type || '';
                } else if (engineTypeField) {
                    engineTypeField.value = car.engine_type || car.engine_capacity || '';
                }
                
                document.getElementById('mileage').value = car.mileage || '';
                
                // Set features
                if (car.features && Array.isArray(car.features)) {
                    car.features.forEach(feature => {
                        const featureId = 'feature_' + feature.replace(/\s+/g, '_').toLowerCase();
                        const checkbox = document.getElementById(featureId);
                        if (checkbox) checkbox.checked = true;
                    });
                }
                
                // Set SEO
                document.getElementById('meta_title').value = car.meta_title || '';
                document.getElementById('meta_description').value = car.meta_description || '';
                document.getElementById('meta_keywords').value = car.meta_keywords || '';
                
                // Show main image preview if exists
                const mainImagePreview = document.getElementById('main_image_preview');
                const mainPreviewImg = mainImagePreview?.querySelector('img');
                
                if (mainImagePreview && mainPreviewImg && car.main_image) {
                    mainPreviewImg.src = '/storage/' + car.main_image;
                    mainImagePreview.classList.remove('d-none');
                } else if (mainImagePreview) {
                    mainImagePreview.classList.add('d-none');
                }
                
                // Show gallery images if exist
                if (car.images && car.images.length > 0) {
                    const galleryContainer = document.getElementById('gallery_container');
                    const imageGallery = document.getElementById('image_gallery');
                    
                    if (galleryContainer && imageGallery) {
                        // Clear existing images
                        imageGallery.innerHTML = '';
                        
                        // Add each image to the gallery
                        car.images.forEach(image => {
                            const imgCol = document.createElement('div');
                            imgCol.className = 'col-md-3';
                            imgCol.innerHTML = `
                                <div class="image-thumbnail">
                                    <img src="/storage/${image.image_path}" class="img-thumbnail" alt="${image.alt_text || 'Car Image'}">
                                    <button type="button" class="btn btn-sm btn-danger btn-remove-image" data-id="${image.id}">
                                        <i class="fas fa-times"></i>
                                    </button>
                                    ${image.is_featured ? '<span class="badge bg-success position-absolute bottom-0 start-0 m-2">Featured</span>' : ''}
                                </div>
                            `;
                            imageGallery.appendChild(imgCol);
                        });
                        
                        galleryContainer.classList.remove('d-none');
                    }
                }
            } else {
                const bsModal = bootstrap.Modal.getInstance(carModal);
                if (bsModal) bsModal.hide();
                Swal.fire('Error', 'Failed to load car data', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            const bsModal = bootstrap.Modal.getInstance(carModal);
            if (bsModal) bsModal.hide();
            Swal.fire('Error', 'Failed to load car data', 'error');
        });
    }
    
    /**
     * Handle delete car
     */
    function handleDeleteCar(carId, carName) {
        Swal.fire({
            title: 'Confirm Delete',
            html: `Are you sure you want to delete <strong>${carName}</strong>?<br><br>
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
                
                fetch(routes.deleteUrl.replace(':id', carId), {
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
                        Swal.fire('Deleted!', data.message || 'Car deleted successfully', 'success');
                    } else {
                        throw new Error(data.message || 'Failed to delete car');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire('Error', error.message || 'Failed to delete car', 'error');
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
            
            // Special handling for array fields (like images[])
            if (field.includes('.')) {
                const baseField = field.split('.')[0];
                const baseInput = document.getElementById(baseField);
                const baseErrorEl = document.getElementById(`${baseField}-error`);
                
                if (baseInput) baseInput.classList.add('is-invalid');
                
                if (baseErrorEl && errors[field][0]) {
                    baseErrorEl.textContent = errors[field][0];
                    baseErrorEl.style.display = 'block';
                }
            }
        });
        
        // Navigate to the tab with the first error
        const firstErrorField = Object.keys(errors)[0]?.split('.')[0] || '';
        if (firstErrorField) {
            navigateToTabWithField(firstErrorField);
        }
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
     * Reset form
     */
    function resetForm() {
        if (carForm) carForm.reset();
        document.getElementById('car_id').value = '';
        clearValidationErrors();
        
        // Uncheck all features
        document.querySelectorAll('input[name="features[]"]').forEach(checkbox => {
            checkbox.checked = false;
        });
    }
    
    // Expose functions for external use (needed for CKEditor integration)
    window.handleEditCar = handleEditCar;
});