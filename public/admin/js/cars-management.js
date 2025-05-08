'use strict';

/**
 * Toggle all feature checkboxes
 * This function must be declared globally
 */
function toggleFeatures(checkAllElement) {
    const isChecked = checkAllElement.checked;
    document.querySelectorAll('input[name="features[]"]').forEach(checkbox => {
        checkbox.checked = isChecked;
    });
}

// Main initialization function
document.addEventListener('DOMContentLoaded', function() {
    // Cache DOM elements
    const carForm = document.getElementById('carForm');
    const carModal = document.getElementById('carModal');
    const createCarBtn = document.getElementById('createCarBtn');
    const saveBtn = document.getElementById('saveBtn');
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
    const mainImagePreview = document.getElementById('main_image_preview');
    const galleryContainer = document.getElementById('gallery_container');
    const imageGallery = document.getElementById('image_gallery');
    const removedImagesInput = document.getElementById('removed_images');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    let editor = null; // For CKEditor instance

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
            { data: 'brand_model', name: 'brand_model' },
            { data: 'brand.name', name: 'brand.name' },
            { data: 'category.name', name: 'category.name' },
            { data: 'price', name: 'price' },
            { data: 'status', name: 'status' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ],
        order: [[0, 'desc']],
        pageLength: 10,
        responsive: true,
        language: {
            paginate: {
                previous: '<i class="fas fa-angle-left"></i>',
                next: '<i class="fas fa-angle-right"></i>'
            }
        }
    });

    // Initialize CKEditor if available
    if (document.getElementById('description') && typeof ClassicEditor !== 'undefined') {
        ClassicEditor
            .create(document.querySelector('#description'))
            .then(newEditor => {
                editor = newEditor;
            })
            .catch(error => {
                console.error('CKEditor error:', error);
            });
    }

    // Add asterisks to required field labels
    document.querySelectorAll('#carForm [required]').forEach(element => {
        const labelFor = element.id;
        const label = document.querySelector(`label[for="${labelFor}"]`);
        if (label && !label.innerHTML.includes('*')) {
            label.innerHTML += ' <span class="text-danger">*</span>';
        }

        // Real-time validation
        element.addEventListener('blur', function() {
            validateField(this);
        });

        element.addEventListener('input', function() {
            this.classList.remove('is-invalid');
            const errorElement = document.getElementById(`${this.id}-error`);
            if (errorElement) {
                errorElement.textContent = '';
                errorElement.style.display = 'none';
            }
        });
    });

    // Feature checkboxes
    const featureCheckboxes = document.querySelectorAll('input[name="features[]"]');
    const checkAllElement = document.getElementById('feature_check_all');
    
    if (featureCheckboxes.length && checkAllElement) {
        // Add event listener to each feature checkbox
        featureCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                // Check if all checkboxes are checked
                const allChecked = Array.from(featureCheckboxes).every(cb => cb.checked);
                
                // Update the "Check All" checkbox
                checkAllElement.checked = allChecked;
            });
        });
    }

    // Validate Moroccan license plate
    const matriculeInput = document.getElementById('matricule');
    if (matriculeInput) {
        matriculeInput.addEventListener('blur', function() {
            const value = this.value.trim();
            if (value) {
                // Format the value
                this.value = formatMatricule(value);
                
                // Validate the formatted value
                if (!validateMatricule(this.value)) {
                    this.classList.add('is-invalid');
                    const errorElement = document.getElementById('matricule-error');
                    if (errorElement) {
                        errorElement.textContent = 'Invalid format. Should be: numbers-letter-region code (e.g. 12345-A-6 or 12345-أ-6)';
                        errorElement.style.display = 'block';
                    }
                }
            }
        });
    }

    // Setup event listeners
    if (createCarBtn) {
        createCarBtn.addEventListener('click', function() {
            resetForm();
            document.getElementById('carModalLabel').textContent = 'Add New Car';
            document.getElementById('method').value = 'POST';
            document.getElementById('car_id').value = '';
            
            // Reset CKEditor if available
            if (editor) {
                editor.setData('');
            }
            
            // Show modal
            $(carModal).modal('show');
        });
    }

    if (carForm) {
        carForm.addEventListener('submit', handleFormSubmit);
    }

    if (confirmDeleteBtn) {
        confirmDeleteBtn.addEventListener('click', function() {
            const carId = document.getElementById('car_id').value;
            if (carId) {
                deleteCarConfirmed(carId);
            }
        });
    }

    // Handle file previews
    const mainImageInput = document.getElementById('main_image');
    if (mainImageInput) {
        mainImageInput.addEventListener('change', function() {
            previewImage(this, 'main_image_preview');
        });
    }

    const additionalImagesInput = document.getElementById('additional_images');
    if (additionalImagesInput) {
        additionalImagesInput.addEventListener('change', function() {
            previewAdditionalImages(this);
        });
    }

    // Document update button
    document.getElementById('update_documents_btn')?.addEventListener('click', function() {
        const carId = document.getElementById('car_id').value;
        if (!carId) {
            showAlert('Warning', 'Please save the car first before updating documents', 'warning');
            return;
        }
        
        updateDocuments(carId);
    });

    // Document buttons and event delegation
    document.addEventListener('click', function(e) {
        // Edit button
        if (e.target.closest('.edit-btn') || e.target.closest('.btn-edit')) {
            const button = e.target.closest('.edit-btn') || e.target.closest('.btn-edit');
            const carId = button.getAttribute('data-id');
            
            if (carId) {
                handleEditCar(carId);
            }
        }

        // Delete button
        if (e.target.closest('.delete-btn') || e.target.closest('.btn-delete')) {
            const button = e.target.closest('.delete-btn') || e.target.closest('.btn-delete');
            const carId = button.getAttribute('data-id');
            
            if (carId) {
                document.getElementById('car_id').value = carId;
                $('#deleteModal').modal('show');
            }
        }

        // Image remove button
        if (e.target.closest('.btn-remove-image')) {
            const button = e.target.closest('.btn-remove-image');
            const imageContainer = button.closest('.image-thumbnail');
            const imageId = imageContainer.getAttribute('data-id');
            
            if (imageId) {
                const currentValue = removedImagesInput.value;
                removedImagesInput.value = currentValue ? `${currentValue},${imageId}` : imageId;
                imageContainer.remove();
                
                // Hide gallery if empty
                if (imageGallery.children.length === 0) {
                    galleryContainer.classList.add('d-none');
                }
            }
        }
    });

    // Tab navigation event handlers
    document.querySelectorAll('#carTabs .nav-link').forEach(tab => {
        tab.addEventListener('click', function() {
            // Get target tab content
            const targetId = this.getAttribute('data-bs-target');
            const targetPane = document.querySelector(targetId);
            
            // Hide all tab panes
            document.querySelectorAll('.tab-pane').forEach(pane => {
                pane.classList.remove('show', 'active');
            });
            
            // Remove active class from all tabs
            document.querySelectorAll('#carTabs .nav-link').forEach(t => {
                t.classList.remove('active');
            });
            
            // Show target tab pane and activate tab
            targetPane.classList.add('show', 'active');
            this.classList.add('active');
        });
    });

    /**
     * Format Moroccan license plate
     */
    function formatMatricule(value) {
        if (!value) return value;
        
        // First normalize by removing all separators
        const normalized = value.replace(/[-|]/g, '');
        
        // Check if it matches the Moroccan pattern for digits + letter + region
        const moroccanPlateRegex = /^(\d{1,5})([A-Za-z]|[\u0600-\u06FF])(\d{1,2})$/u;
        
        if (moroccanPlateRegex.test(normalized)) {
            const matches = normalized.match(moroccanPlateRegex);
            if (matches && matches.length === 4) {
                const digits = matches[1];
                const letter = matches[2]; 
                const regionCode = matches[3];
                
                // Format with hyphens for better readability
                return `${digits}-${letter}-${regionCode}`;
            }
        }
        
        return value;
    }
    
    /**
     * Validate Moroccan license plate
     */
    function validateMatricule(value) {
        if (!value) return true; // Empty is handled by required attribute
        
        // Accept different separator styles
        const normalized = value.replace(/[-|]/g, '');
        
        // Pattern: digits + letter (Arabic or Latin) + region code
        const moroccanPlateRegex = /^(\d{1,5})([A-Za-z]|[\u0600-\u06FF])(\d{1,2})$/u;
        
        return moroccanPlateRegex.test(normalized);
    }

    /**
     * Handle form submission with AJAX
     */
    function handleFormSubmit(e) {
        e.preventDefault();
        
        // Clear previous validation errors
        clearValidationErrors();
        
        // Perform client-side validation
        if (!validateForm()) {
            return;
        }
        
        // Update CKEditor content before submission
        if (editor) {
            document.getElementById('description').value = editor.getData();
        }
        
        // Create FormData
        const formData = new FormData(carForm);
        
        // Get car ID and determine if this is an edit operation
        const carId = document.getElementById('car_id').value;
        const isEdit = carId && carId !== '';
        
        // Set URL based on operation
        const url = isEdit ? routes.updateUrl.replace(':id', carId) : routes.storeUrl;
        
        // Show loading state on button
        const saveBtnText = saveBtn.innerHTML;
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...';
        
        // Send AJAX request
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
                $(carModal).modal('hide');
                
                // Reload table
                table.ajax.reload();
                
                // Show success toast
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: data.message || 'Car saved successfully',
                    timer: 3000,
                    timerProgressBar: true,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
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
                
                // Show error message
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: 'Please check the form for errors.',
                    confirmButtonColor: '#5e72e4'
                });
            } else {
                // Show general error message
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.data?.message || 'An error occurred while saving the car.',
                    confirmButtonColor: '#5e72e4'
                });
            }
        })
        .finally(() => {
            // Reset button state
            saveBtn.disabled = false;
            saveBtn.innerHTML = saveBtnText;
        });
    }

    /**
     * Client-side form validation
     */
    function validateForm() {
        let isValid = true;
        
        // Required fields
        document.querySelectorAll('#carForm [required]').forEach(element => {
            if (!element.value.trim()) {
                element.classList.add('is-invalid');
                const errorElement = document.getElementById(`${element.id}-error`);
                if (errorElement) {
                    errorElement.textContent = 'This field is required';
                    errorElement.style.display = 'block';
                }
                isValid = false;
            }
        });
        
        // Matricule validation
        const matricule = document.getElementById('matricule');
        if (matricule && matricule.value && !validateMatricule(matricule.value)) {
            matricule.classList.add('is-invalid');
            const errorElement = document.getElementById('matricule-error');
            if (errorElement) {
                errorElement.textContent = 'Invalid format. Should be: numbers-letter-region code (e.g. 12345-A-6 or 12345-أ-6)';
                errorElement.style.display = 'block';
            }
            isValid = false;
        }
        
        // Date validations
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        
        // Validate expiry dates
        const dateFields = [
            { field: 'assurance_expiry_date', name: 'Insurance expiry date' },
            { field: 'vignette_expiry_date', name: 'Vignette expiry date' },
            { field: 'visite_technique_expiry_date', name: 'Technical inspection expiry date' }
        ];
        
        dateFields.forEach(item => {
            const field = document.getElementById(item.field);
            if (field && field.value) {
                const date = new Date(field.value);
                if (date < today) {
                    field.classList.add('is-invalid');
                    const errorElement = document.getElementById(`${item.field}-error`);
                    if (errorElement) {
                        errorElement.textContent = `${item.name} must be in the future`;
                        errorElement.style.display = 'block';
                    }
                    isValid = false;
                }
            }
        });
        
        // If validation fails, show first tab with errors
        if (!isValid) {
            const firstErrorElement = document.querySelector('#carForm .is-invalid');
            if (firstErrorElement) {
                const tabPane = firstErrorElement.closest('.tab-pane');
                if (tabPane) {
                    const tabId = tabPane.id;
                    document.querySelector(`#carTabs button[data-bs-target="#${tabId}"]`).click();
                }
            }
        }
        
        return isValid;
    }

    /**
     * Validate a single field
     */
    function validateField(field) {
        // Required validation
        if (field.hasAttribute('required') && !field.value.trim()) {
            field.classList.add('is-invalid');
            const errorElement = document.getElementById(`${field.id}-error`);
            if (errorElement) {
                errorElement.textContent = 'This field is required';
                errorElement.style.display = 'block';
            }
            return false;
        }

        // Matricule validation
        if (field.id === 'matricule' && field.value && !validateMatricule(field.value)) {
            field.classList.add('is-invalid');
            const errorElement = document.getElementById('matricule-error');
            if (errorElement) {
                errorElement.textContent = 'Invalid format. Should be: numbers-letter-region code (e.g. 12345-A-6 or 12345-أ-6)';
                errorElement.style.display = 'block';
            }
            return false;
        }

        // Date validation for expiry dates
        if (['assurance_expiry_date', 'vignette_expiry_date', 'visite_technique_expiry_date'].includes(field.id) && field.value) {
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            const date = new Date(field.value);

            if (date < today) {
                field.classList.add('is-invalid');
                const errorElement = document.getElementById(`${field.id}-error`);
                if (errorElement) {
                    errorElement.textContent = 'Date must be in the future';
                    errorElement.style.display = 'block';
                }
                return false;
            }
        }

        // Field is valid
        field.classList.remove('is-invalid');
        const errorElement = document.getElementById(`${field.id}-error`);
        if (errorElement) {
            errorElement.textContent = '';
            errorElement.style.display = 'none';
        }
        return true;
    }

    /**
     * Handle edit car operation
     */
    function handleEditCar(carId) {
        resetForm();
        
        // Set ID and method
        document.getElementById('car_id').value = carId;
        document.getElementById('method').value = 'PUT';
        document.getElementById('carModalLabel').textContent = 'Edit Car';
        
        // Show loading indicator in modal
        const modalBody = document.querySelector('#carModal .modal-body');
        if (modalBody) {
            const loadingDiv = document.createElement('div');
            loadingDiv.id = 'loading-overlay';
            loadingDiv.className = 'loading-overlay';
            loadingDiv.innerHTML = '<div class="spinner-border text-primary"></div>';
            modalBody.appendChild(loadingDiv);
        }
        
        // Show modal
        $(carModal).modal('show');
        
        // Add debugging logs
        console.log('Fetching car data for ID:', carId);
        
        // Fetch car data
        fetch(routes.editUrl.replace(':id', carId), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            // Add more debugging logs
            console.log('Response data:', data);
            
            // Remove loading overlay
            const overlay = document.getElementById('loading-overlay');
            if (overlay) overlay.remove();
            
            if (data.success && data.car) {
                const car = data.car;
                
                // Fill car data
                fillCarForm(car);
            } else {
                // Show error
                $(carModal).modal('hide');
                showAlert('Error', data.message || 'Failed to load car data', 'error');
            }
        })
        .catch(error => {
            console.error('Error fetching car data:', error);
            
            // Remove loading overlay
            const overlay = document.getElementById('loading-overlay');
            if (overlay) overlay.remove();
            
            // Show error and hide modal
            $(carModal).modal('hide');
            showAlert('Error', 'Failed to load car data', 'error');
        });
    }

    /**
     * Fill car form with data, with special handling for documents
     */
    function fillCarForm(car) {
        // Basic info
        document.getElementById('name').value = car.name || '';
        document.getElementById('brand_id').value = car.brand_id || '';
        document.getElementById('model').value = car.model || '';
        document.getElementById('year').value = car.year || '';
        document.getElementById('category_id').value = car.category_id || '';
        document.getElementById('color').value = car.color || '';
        document.getElementById('chassis_number').value = car.chassis_number || '';
        document.getElementById('matricule').value = car.matricule || '';
        document.getElementById('status').value = car.status || 'available';
        document.getElementById('price_per_day').value = car.price_per_day || '';
        document.getElementById('weekly_price').value = car.weekly_price || '';
        document.getElementById('monthly_price').value = car.monthly_price || '';
        document.getElementById('discount_percentage').value = car.discount_percentage || 0;

        // Format dates
        if (car.mise_en_service_date) {
            document.getElementById('mise_en_service_date').value = formatDate(car.mise_en_service_date);
        }

        // Details
        document.getElementById('seats').value = car.seats || '';
        document.getElementById('transmission').value = car.transmission || '';
        document.getElementById('fuel_type').value = car.fuel_type || '';
        document.getElementById('engine_capacity').value = car.engine_capacity || '';
        document.getElementById('mileage').value = car.mileage || '';

        // Set description in CKEditor
        if (editor) {
            editor.setData(car.description || '');
        } else {
            document.getElementById('description').value = car.description || '';
        }

        // SEO
        document.getElementById('meta_title').value = car.meta_title || '';
        document.getElementById('meta_description').value = car.meta_description || '';
        document.getElementById('meta_keywords').value = car.meta_keywords || '';

        // Features
        document.querySelectorAll('input[name="features[]"]').forEach(checkbox => {
            checkbox.checked = car.features && car.features.includes(checkbox.value);
        });
        
        // Update the "Check All" checkbox
        updateCheckAllStatus();

        // Handle documents with special debugging and fallbacks
        console.log('Looking for document data in car object:', car);
        
        // Check all possible document properties
        let documentData = null;
        
        if (car.documents) {
            console.log('Found car.documents:', car.documents);
            documentData = car.documents;
        } else if (car.car_documents) {
            console.log('Found car.car_documents:', car.car_documents);
            documentData = car.car_documents;
        } else if (car.car_document) {
            console.log('Found car.car_document:', car.car_document);
            documentData = car.car_document;
        } else {
            // Make one more check for a documents field with a different name pattern
            const possibleDocumentFields = Object.keys(car).filter(key => key.includes('document'));
            if (possibleDocumentFields.length > 0) {
                console.log('Found possible document fields:', possibleDocumentFields);
                const firstField = possibleDocumentFields[0];
                documentData = car[firstField];
                console.log(`Using ${firstField} as document data:`, documentData);
            } else {
                console.warn('No document data found in car object');
                // Create empty document structure to prevent errors
                documentData = {
                    assurance_number: '',
                    assurance_company: '',
                    carte_grise_number: '',
                    assurance_expiry_date: null,
                    carte_grise_expiry_date: null,
                    vignette_expiry_date: null,
                    visite_technique_date: null,
                    visite_technique_expiry_date: null,
                    file_carte_grise: null,
                    file_assurance: null,
                    file_visite_technique: null,
                    file_vignette: null
                };
            }
        }
        
        // Process document data if found
        if (documentData) {
            console.log('Processing document data:', documentData);
            fillDocumentsForm(documentData);
        }

        // Images
        showCarImages(car);
    }

    /**
     * Show car images
     */
    function showCarImages(car) {
        // Main image
        if (car.main_image) {
            const mainImageUrl = car.main_image.startsWith('http')
                ? car.main_image
                : `/storage/${car.main_image}`;

            const preview = document.getElementById('main_image_preview');
            preview.classList.remove('d-none');
            preview.querySelector('img').src = mainImageUrl;
        }

        // Gallery images
        if (car.images && car.images.length) {
            const gallery = document.getElementById('image_gallery');
            const container = document.getElementById('gallery_container');

            gallery.innerHTML = '';
            container.classList.remove('d-none');

            car.images.forEach(image => {
                const imagePath = image.image_path || image.path;
                const imageUrl = imagePath.startsWith('http')
                    ? imagePath
                    : `/storage/${imagePath}`;

                const imageHtml = `
                    <div class="col-md-4 image-thumbnail" data-id="${image.id}">
                        <img src="${imageUrl}" class="img-fluid rounded" alt="Car Image">
                        <button type="button" class="btn btn-sm btn-remove-image btn-danger">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                `;

                gallery.insertAdjacentHTML('beforeend', imageHtml);
            });
        }
    }

    /**
     * Improved function to fill document form with better error handling
     */
    function fillDocumentsForm(documents) {
        try {
            console.log('Starting to fill document form with:', documents);
            
            // Use a delay to ensure DOM is ready (helps with tab content visibility issues)
            setTimeout(() => {
                try {
                    // Safely set values with error checking
                    const safeSetValue = (id, value) => {
                        try {
                            const element = document.getElementById(id);
                            if (element) {
                                console.log(`Setting ${id} to:`, value);
                                element.value = value || '';
                            } else {
                                console.warn(`Element #${id} not found`);
                            }
                        } catch (e) {
                            console.error(`Error setting value for #${id}:`, e);
                        }
                    };
        
                    // Safely set dates with error checking
                    const safeSetDate = (id, dateString) => {
                        try {
                            const element = document.getElementById(id);
                            if (element && dateString) {
                                console.log(`Setting date ${id} to:`, dateString);
                                element.value = formatDate(dateString);
                            } else if (element) {
                                element.value = '';
                            } else {
                                console.warn(`Element #${id} not found`);
                            }
                        } catch (e) {
                            console.error(`Error setting date for #${id}:`, e);
                        }
                    };
        
                    // Safely update file indicators with error checking
                    const safeUpdateIndicator = (id, filePath) => {
                        try {
                            const element = document.getElementById(id);
                            if (element && filePath) {
                                console.log(`Setting file indicator ${id} for:`, filePath);
                                element.classList.remove('d-none');
                                element.href = `/storage/${filePath}`;
                            } else if (element) {
                                element.classList.add('d-none');
                            } else {
                                console.warn(`Element #${id} not found`);
                            }
                        } catch (e) {
                            console.error(`Error updating indicator for #${id}:`, e);
                        }
                    };
        
                    // Set basic text fields
                    safeSetValue('assurance_number', documents.assurance_number);
                    safeSetValue('assurance_company', documents.assurance_company);
                    safeSetValue('carte_grise_number', documents.carte_grise_number);
                    
                    // Set date fields
                    safeSetDate('assurance_expiry_date', documents.assurance_expiry_date);
                    safeSetDate('carte_grise_expiry_date', documents.carte_grise_expiry_date);
                    safeSetDate('vignette_expiry_date', documents.vignette_expiry_date);
                    safeSetDate('visite_technique_date', documents.visite_technique_date);
                    safeSetDate('visite_technique_expiry_date', documents.visite_technique_expiry_date);
                    
                    // Update file indicators
                    safeUpdateIndicator('carte_grise_file_indicator', documents.file_carte_grise);
                    safeUpdateIndicator('assurance_file_indicator', documents.file_assurance);
                    safeUpdateIndicator('visite_technique_file_indicator', documents.file_visite_technique);
                    safeUpdateIndicator('vignette_file_indicator', documents.file_vignette);
        
                    console.log('Document form filled successfully');
                    
                    // Make sure Documents tab is updated even if not visible
                    const documentsTab = document.getElementById('documents');
                    if (documentsTab) {
                        // Force a redraw on the documents tab
                        documentsTab.style.display = 'none';
                        setTimeout(() => { documentsTab.style.display = ''; }, 10);
                    }
                } catch (innerError) {
                    console.error('Error in delayed document filling:', innerError);
                }
            }, 100); // Small delay to ensure DOM is ready
        } catch (error) {
            console.error('Error in fillDocumentsForm:', error);
        }
    }

    /**
     * Function to update the "Check All" checkbox status
     */
    function updateCheckAllStatus() {
        const featureCheckboxes = document.querySelectorAll('input[name="features[]"]');
        const checkAllElement = document.getElementById('feature_check_all');
        
        if (featureCheckboxes.length && checkAllElement) {
            const allChecked = Array.from(featureCheckboxes).every(cb => cb.checked);
            checkAllElement.checked = allChecked;
        }
    }

    /**
     * Handle delete car operation
     */
    function deleteCarConfirmed(carId) {
        // Show loading state
        const deleteBtn = document.getElementById('confirmDeleteBtn');
        const originalText = deleteBtn.innerHTML;
        deleteBtn.disabled = true;
        deleteBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Deleting...';
        
        fetch(routes.deleteUrl.replace(':id', carId), {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Hide modal and reload table
                $('#deleteModal').modal('hide');
                table.ajax.reload();
                
                // Show success message
                showAlert('Success', data.message || 'Car deleted successfully', 'success');
            } else {
                throw new Error(data.message || 'Failed to delete car');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            
            // Show error message
            showAlert('Error', error.message || 'Failed to delete car', 'error');
        })
        .finally(() => {
            // Reset button state
            deleteBtn.disabled = false;
            deleteBtn.innerHTML = originalText;
        });
    }

    /**
     * Format date for input fields (YYYY-MM-DD)
     */
    function formatDate(dateString) {
        if (!dateString) return '';
        
        // Check if the date is already in the correct format
        if (/^\d{4}-\d{2}-\d{2}$/.test(dateString)) {
            return dateString;
        }
        
        // Parse the date
        const date = new Date(dateString);
        if (isNaN(date.getTime())) {
            return ''; // Invalid date
        }
        
        // Format as YYYY-MM-DD
        return date.toISOString().split('T')[0];
    }

    /**
     * Preview main image
     */
    function previewImage(input, previewId) {
        const preview = document.getElementById(previewId);
        const previewImg = preview.querySelector('img');

        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                preview.classList.remove('d-none');
            }
            reader.readAsDataURL(input.files[0]);
        } else {
            preview.classList.add('d-none');
        }
    }

    /**
     * Preview additional images
     */
    function previewAdditionalImages(input) {
        if (input.files && input.files.length > 0) {
            const gallery = document.getElementById('image_gallery');
            const container = document.getElementById('gallery_container');
            
            container.classList.remove('d-none');
            
            for (let i = 0; i < input.files.length; i++) {
                const file = input.files[i];
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    const imageHtml = `
                        <div class="col-md-4 image-thumbnail">
                            <img src="${e.target.result}" class="img-fluid rounded" alt="New Car Image">
                            <small class="text-muted">New image (not yet saved)</small>
                        </div>
                    `;
                    
                    gallery.insertAdjacentHTML('beforeend', imageHtml);
                };
                
                reader.readAsDataURL(file);
            }
        }
    }

    /**
     * Update car documents separately
     */
    function updateDocuments(carId) {
        // Create FormData for documents only
        const formData = new FormData();
        
        // Add CSRF token
        formData.append('_token', csrfToken);
        
        // Add document fields
        formData.append('assurance_number', document.getElementById('assurance_number').value);
        formData.append('assurance_company', document.getElementById('assurance_company').value);
        formData.append('assurance_expiry_date', document.getElementById('assurance_expiry_date').value);
        formData.append('carte_grise_number', document.getElementById('carte_grise_number').value);
        formData.append('carte_grise_expiry_date', document.getElementById('carte_grise_expiry_date').value);
        formData.append('vignette_expiry_date', document.getElementById('vignette_expiry_date').value);
        formData.append('visite_technique_date', document.getElementById('visite_technique_date').value);
        formData.append('visite_technique_expiry_date', document.getElementById('visite_technique_expiry_date').value);
        
        // Add document files if selected
        const fileInputs = {
            'file_carte_grise': document.getElementById('file_carte_grise'),
            'file_assurance': document.getElementById('file_assurance'),
            'file_visite_technique': document.getElementById('file_visite_technique'),
            'file_vignette': document.getElementById('file_vignette')
        };
        
        for (const [name, input] of Object.entries(fileInputs)) {
            if (input.files && input.files[0]) {
                formData.append(name, input.files[0]);
            }
        }
        
        // Show loading state
        const btn = document.getElementById('update_documents_btn');
        const btnText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Updating...';
        
        // Send request
        fetch(`/admin/cars/${carId}/documents/update`, {
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
                // Show success message
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: data.message || 'Documents updated successfully',
                    confirmButtonColor: '#5e72e4'
                });
                
                // Update document file indicators
                if (data.documents) {
                    if (data.documents.file_carte_grise) {
                        document.getElementById('carte_grise_file_indicator').classList.remove('d-none');
                        document.getElementById('carte_grise_file_indicator').href = `/storage/${data.documents.file_carte_grise}`;
                    }
                    
                    if (data.documents.file_assurance) {
                        document.getElementById('assurance_file_indicator').classList.remove('d-none');
                        document.getElementById('assurance_file_indicator').href = `/storage/${data.documents.file_assurance}`;
                    }
                    
                    if (data.documents.file_visite_technique) {
                        document.getElementById('visite_technique_file_indicator').classList.remove('d-none');
                        document.getElementById('visite_technique_file_indicator').href = `/storage/${data.documents.file_visite_technique}`;
                    }
                    
                    if (data.documents.file_vignette) {
                        document.getElementById('vignette_file_indicator').classList.remove('d-none');
                        document.getElementById('vignette_file_indicator').href = `/storage/${data.documents.file_vignette}`;
                    }
                }
                
                // Clear file inputs
                for (const input of Object.values(fileInputs)) {
                    input.value = '';
                }
            } else {
                throw new Error(data.message || 'Failed to update documents');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            
            // Handle validation errors
            if (error.status === 422 && error.data && error.data.errors) {
                displayValidationErrors(error.data.errors);
                
                // Show error message
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: 'Please check the document fields for errors',
                    confirmButtonColor: '#5e72e4'
                });
            } else {
                // Show general error message
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.data?.message || 'Failed to update documents',
                    confirmButtonColor: '#5e72e4'
                });
            }
        })
        .finally(() => {
            // Reset button state
            btn.disabled = false;
            btn.innerHTML = btnText;
        });
    }
    
    /**
     * Reset form and clear validation
     */
    function resetForm() {
        // Reset form fields
        carForm.reset();
        
        // Clear validation errors
        clearValidationErrors();
        
        // Reset image previews
        document.getElementById('main_image_preview').classList.add('d-none');
        document.getElementById('gallery_container').classList.add('d-none');
        document.getElementById('image_gallery').innerHTML = '';
        document.getElementById('removed_images').value = '';
        
        // Reset document file indicators
        document.querySelectorAll('.document-file-indicator').forEach(element => {
            element.classList.add('d-none');
        });
        
        // Set default date values
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('mise_en_service_date').value = today;
        
        // Set default document expiry dates (1 year from today)
        const oneYearFromNow = new Date();
        oneYearFromNow.setFullYear(oneYearFromNow.getFullYear() + 1);
        const oneYearFromNowString = oneYearFromNow.toISOString().split('T')[0];
        
        document.getElementById('assurance_expiry_date').value = oneYearFromNowString;
        document.getElementById('vignette_expiry_date').value = oneYearFromNowString;
        document.getElementById('visite_technique_expiry_date').value = oneYearFromNowString;
        
        // Reset CKEditor if available
        if (editor) {
            editor.setData('');
        }
        
        // Reset the "Check All" checkbox
        const checkAllElement = document.getElementById('feature_check_all');
        if (checkAllElement) {
            checkAllElement.checked = false;
        }
    }
    
    /**
     * Clear validation errors
     */
    function clearValidationErrors() {
        document.querySelectorAll('.is-invalid').forEach(element => {
            element.classList.remove('is-invalid');
        });
        
        document.querySelectorAll('.invalid-feedback').forEach(element => {
            element.textContent = '';
            element.style.display = 'none';
        });
    }
    
    /**
     * Display validation errors
     */
    function displayValidationErrors(errors) {
        for (const field in errors) {
            const element = document.getElementById(field);
            const errorElement = document.getElementById(`${field}-error`);
            
            if (element) {
                element.classList.add('is-invalid');
            }
            
            if (errorElement && errors[field][0]) {
                errorElement.textContent = errors[field][0];
                errorElement.style.display = 'block';
            }
        }
        
        // Show the first tab with errors
        const firstErrorElement = document.querySelector('#carForm .is-invalid');
        if (firstErrorElement) {
            const tabPane = firstErrorElement.closest('.tab-pane');
            if (tabPane) {
                const tabId = tabPane.id;
                document.querySelector(`#carTabs button[data-bs-target="#${tabId}"]`).click();
            }
        }
    }
    
    /**
     * Show alert message
     */
    function showAlert(title, text, icon) {
        Swal.fire({
            title: title,
            text: text,
            icon: icon,
            confirmButtonColor: '#5e72e4',
            confirmButtonText: 'OK'
        });
    }
    
    // Make functions available globally
    window.handleEditCar = handleEditCar;
    window.handleDeleteCar = handleDeleteCar;
    window.showAlert = showAlert;
    window.toggleFeatures = toggleFeatures; 
});