/**
 * Cars Management JS for Moroccan Car Rental Application
 * Optimized for performance and handling Moroccan-specific requirements
 */
document.addEventListener('DOMContentLoaded', function() {
    // DOM Elements
    const carsTable = $('#cars-table');
    const carModal = $('#carModal');
    const deleteModal = $('#deleteModal');
    const carForm = $('#carForm');
    const methodInput = $('#method');
    const createCarBtn = $('#createCarBtn');
    const confirmDeleteBtn = $('#confirmDeleteBtn');
    const mainImagePreview = $('#main_image_preview');
    const galleryContainer = $('#gallery_container');
    const imageGallery = $('#image_gallery');
    const removedImagesInput = $('#removed_images');
    const saveBtnText = $('#saveBtn').text();

    // State variables
    let currentCarId = null;
    let editor = null;
    let carsDataTable = null;
    let formSubmitting = false;

    // Debug mode - set to true to enable console logs
    const debug = true;

    // Logger function
    const log = function(message, data = null) {
        if (debug && console && console.log) {
            if (data) {
                console.log(`[Car Manager] ${message}`, data);
            } else {
                console.log(`[Car Manager] ${message}`);
            }
        }
    };

    // Initialize CKEditor
    function initCKEditor() {
        return ClassicEditor
            .create(document.querySelector('#description'), {
                toolbar: ['heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', '|', 'undo', 'redo'],
                placeholder: 'Enter car description here...'
            })
            .then(newEditor => {
                editor = newEditor;
                log('CKEditor initialized');
                return editor;
            })
            .catch(error => {
                console.error('CKEditor initialization error:', error);
            });
    }

    // Initialize DataTable
    function initDataTable() {
        log('Initializing DataTable');
        
        // Get permissions from data attributes or global variables
        const canEdit = typeof canEditCars !== 'undefined' ? canEditCars : false;
        const canDelete = typeof canDeleteCars !== 'undefined' ? canDeleteCars : false;

        return carsTable.DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: routes.dataUrl,
                type: 'GET',
                error: function(xhr) {
                    log('DataTable AJAX error', xhr);
                    showAlert('Error', 'Failed to load cars data', 'error');
                }
            },
            columns: [
                { data: 'id' },
                { 
                    data: 'image', 
                    name: 'image',
                    orderable: false,
                    searchable: false,
                    defaultContent: '<div class="bg-light text-center p-2" style="width:80px;height:60px;"><i class="fas fa-car fa-2x text-muted"></i></div>'
                },
                { data: 'name' },
                { 
                    data: 'brand.name', 
                    defaultContent: '',
                    render: function(data, type, row) {
                        return data || row.brand_name || '';
                    }
                },
                { 
                    data: 'category.name', 
                    defaultContent: '' 
                },
                { 
                    data: 'price_per_day',
                    render: function(data) {
                        return Number(data).toLocaleString('fr-MA') + ' MAD';
                    }
                },
                { 
                    data: 'status',
                    render: function(data) {
                        const statusClass = {
                            'available': 'success',
                            'rented': 'primary',
                            'maintenance': 'warning',
                            'unavailable': 'danger'
                        }[data] || 'secondary';
                        
                        return `<span class="badge bg-${statusClass}">${data.charAt(0).toUpperCase() + data.slice(1)}</span>`;
                    }
                },
                { data: 'bookings_count', defaultContent: '0' },
                ...(canEdit || canDelete ? [{
                    data: null,
                    render: function(data, type, row) {
                        let actions = '';
                        
                        // View button
                        actions += `<a href="${routes.showUrl.replace(':id', row.id)}" class="btn btn-sm btn-info me-1" title="View">
                            <i class="fas fa-eye"></i>
                        </a>`;
                        
                        // Edit button (if permission)
                        if (canEdit) {
                            actions += `<button type="button" class="btn btn-sm btn-primary me-1 edit-btn" data-id="${row.id}" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>`;
                        }
                        
                        // Delete button (if permission)
                        if (canDelete) {
                            actions += `<button type="button" class="btn btn-sm btn-danger delete-btn" data-id="${row.id}" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>`;
                        }
                        
                        return `<div class="btn-group">${actions}</div>`;
                    },
                    orderable: false
                }] : [])
            ],
            language: {
                paginate: {
                    previous: '<i class="fas fa-angle-left"></i>',
                    next: '<i class="fas fa-angle-right"></i>'
                },
                emptyTable: 'No cars available',
                processing: '<i class="fas fa-spinner fa-spin fa-2x"></i>'
            },
            drawCallback: function() {
                // Re-attach event listeners after table redraw
                attachEditEventListeners();
                attachDeleteEventListeners();
                log('DataTable drawn, event listeners attached');
            }
        });
    }

    // Show alert message
    function showAlert(title, text, icon) {
        Swal.fire({
            title: title,
            text: text,
            icon: icon,
            confirmButtonColor: '#5e72e4',
            timer: 3000
        });
    }

    // Reset form and clear validation
    function resetForm() {
        log('Resetting form');
        
        // Reset form fields and validation styles
        carForm[0].reset();
        carForm.find('.is-invalid').removeClass('is-invalid');
        carForm.find('.invalid-feedback').text('');
        
        // Set POST method for new car creation
        methodInput.val('POST');
        
        // Reset CKEditor
        if (editor) {
            editor.setData('');
        }
        
        // Reset image previews
        mainImagePreview.addClass('d-none').find('img').attr('src', '');
        galleryContainer.addClass('d-none');
        imageGallery.empty();
        removedImagesInput.val('');
        
        // Reset car ID
        currentCarId = null;
        
        // Set today's date for date fields
        const today = new Date().toISOString().split('T')[0];
        $('#mise_en_service_date').val(today);
        $('#vignette_date').val(today);
        $('#technical_inspection_date').val(today);
        
        // Ensure all form fields are enabled
        carForm.find('input, select, textarea').prop('disabled', false).css('opacity', '');
        
        log('Form reset complete');
    }

    // Format Moroccan license plate
    function formatMatricule(value) {
        if (!value) return value;
        
        // First normalize by removing all separators
        const normalized = value.replace(/[-|]/g, '');
        
        // Check if it matches the Moroccan pattern for digits + letter + region
        // Supporting both Arabic and Latin letters
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
    
    // Validate Moroccan license plate
    function validateMatricule(value) {
        if (!value) return true; // Empty is handled by required attribute
        
        // Accept different separator styles
        const normalized = value.replace(/[-|]/g, '');
        
        // Pattern: digits + letter (Arabic or Latin) + region code
        const moroccanPlateRegex = /^(\d{1,5})([A-Za-z]|[\u0600-\u06FF])(\d{1,2})$/u;
        
        return moroccanPlateRegex.test(normalized);
    }

    // Serialize form data for debugging
    function serializeFormData(formData) {
        const serialized = {};
        for (let pair of formData.entries()) {
            // Skip file inputs for cleaner output
            if (pair[1] instanceof File) {
                serialized[pair[0]] = `[File: ${pair[1].name}, ${pair[1].size} bytes]`;
            } else {
                serialized[pair[0]] = pair[1];
            }
        }
        return serialized;
    }

    // Handle form submission
    function handleFormSubmit(e) {
        e.preventDefault();
        
        // Prevent double submission
        if (formSubmitting) {
            log('Form already submitting, ignoring duplicate submission');
            return;
        }
        
        log('Form submission started');
        formSubmitting = true;
        
        // Ensure all required fields are enabled
        carForm.find('input[required], select[required], textarea[required]').prop('disabled', false);
        
        // Update hidden CKEditor content before submit
        if (editor) {
            document.getElementById('description').value = editor.getData();
            log('CKEditor data updated');
        }
        
        // Validate form
        let isValid = true;
        carForm.find('[required]').each(function() {
            const field = $(this);
            if (!field.val()) {
                field.addClass('is-invalid');
                const errorId = `${field.attr('id')}-error`;
                $(`#${errorId}`).text('This field is required.');
                isValid = false;
                
                log(`Validation error: ${field.attr('id')} is required`);
            }
        });
        
        // Validate matricule
        const matricule = $('#matricule').val();
        if (matricule && !validateMatricule(matricule)) {
            $('#matricule').addClass('is-invalid');
            $('#matricule-error').text('Invalid format. Should be: numbers-letter-region code (e.g. 12345-A-6 or 12345-أ-6)');
            isValid = false;
            
            log('Validation error: Invalid matricule format');
        } else if (matricule) {
            // Format matricule for submission
            $('#matricule').val(formatMatricule(matricule));
            log(`Matricule formatted: ${$('#matricule').val()}`);
        }
        
        // Check if we need to show the first tab with errors
        if (!isValid) {
            const firstErrorField = carForm.find('.is-invalid').first();
            const tabPane = firstErrorField.closest('.tab-pane');
            if (tabPane.length) {
                const tabId = tabPane.attr('id');
                $(`#${tabId}-tab`).tab('show');
                
                log(`Showing tab with first error: ${tabId}`);
            }
            
            showAlert('Validation Error', 'Please check the form for errors.', 'error');
            formSubmitting = false;
            return;
        }
        
        // Create FormData for submission
        const formData = new FormData(carForm[0]);
        
        // Set the correct method
        const method = currentCarId ? 'PUT' : 'POST';
        formData.append('_method', method);
        
        const url = currentCarId ? routes.updateUrl.replace(':id', currentCarId) : routes.storeUrl;
        log(`Submitting to URL: ${url}, Method: ${method}`, serializeFormData(formData));
        
        // Show loading state
        const saveBtn = $('#saveBtn');
        saveBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...');
        
        $.ajax({
            url: url,
            type: 'POST', // Always POST, the _method field handles the actual method
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                log('Form submission success', response);
                showAlert('Success', response.message || 'Car saved successfully.', 'success');
                carModal.modal('hide');
                carsDataTable.ajax.reload(null, false); // Reload table without resetting pagination
                
                // Redirect if specified
                if (response.redirect) {
                    window.location.href = response.redirect;
                }
            },
            error: function(xhr) {
                log('Form submission error', xhr);
                
                if (xhr.status === 422) {
                    // Validation errors
                    const errors = xhr.responseJSON?.errors || {};
                    log('Validation errors from server', errors);
                    
                    let firstErrorTab = null;
                    
                    // Display validation errors
                    for (const field in errors) {
                        const input = $(`#${field}`);
                        if (input.length) {
                            input.addClass('is-invalid');
                            $(`#${field}-error`).text(errors[field][0]);
                            
                            // Find the tab containing this field
                            if (!firstErrorTab) {
                                const tabPane = input.closest('.tab-pane');
                                if (tabPane.length) {
                                    firstErrorTab = tabPane.attr('id');
                                }
                            }
                        } else {
                            log(`Field not found in form: ${field}`);
                        }
                    }
                    
                    // Show the first tab with errors
                    if (firstErrorTab) {
                        $(`#${firstErrorTab}-tab`).tab('show');
                        log(`Showing tab with server validation errors: ${firstErrorTab}`);
                    }
                    
                    showAlert('Validation Error', 'Please check the form for errors.', 'error');
                } else {
                    showAlert('Error', xhr.responseJSON?.message || 'An error occurred while saving the car.', 'error');
                }
            },
            complete: function() {
                saveBtn.prop('disabled', false).text(saveBtnText);
                formSubmitting = false;
                log('Form submission complete');
            }
        });
    }

    // Handle edit car
    function handleEditCar(carId) {
        log(`Edit car request for ID: ${carId}`);
        currentCarId = carId;
        
        // Set method to PUT for editing
        methodInput.val('PUT');
        
        $.get(routes.editUrl.replace(':id', carId))
            .done(function(response) {
                log('Edit car data received', response.car);
                
                // Set modal title
                $('#carModalLabel').text('Edit Car');
                
                // Fill form with car data
                const car = response.car;
                
                // Basic info tab
                $('#name').val(car.name);
                $('#model').val(car.model);
                $('#year').val(car.year);
                $('#brand_id').val(car.brand_id);
                $('#category_id').val(car.category_id);
                $('#color').val(car.color);
                $('#chassis_number').val(car.chassis_number);
                
                // Format matricule if needed
                if (car.matricule) {
                    $('#matricule').val(formatMatricule(car.matricule));
                } else {
                    $('#matricule').val('');
                }
                
                $('#price_per_day').val(car.price_per_day);
                $('#discount_percentage').val(car.discount_percentage || 0);
                $('#weekly_price').val(car.weekly_price);
                $('#monthly_price').val(car.monthly_price);
                $('#status').val(car.status);
                
                // Format dates properly
                if (car.mise_en_service_date) {
                    // Handle different date formats (MySQL and ISO)
                    const date = car.mise_en_service_date.split('T')[0].split(' ')[0];
                    $('#mise_en_service_date').val(date);
                }
                
                // Details tab
                $('#seats').val(car.seats);
                $('#transmission').val(car.transmission);
                $('#fuel_type').val(car.fuel_type);
                $('#engine_capacity').val(car.engine_capacity);
                $('#mileage').val(car.mileage);
                
                // Documents tab
                $('#insurance_number').val(car.insurance_number);
                $('#grey_card_number').val(car.grey_card_number);
                
                // Format dates properly for documents
                if (car.vignette_date) {
                    const date = car.vignette_date.split('T')[0].split(' ')[0];
                    $('#vignette_date').val(date);
                }
                
                if (car.technical_inspection_date) {
                    const date = car.technical_inspection_date.split('T')[0].split(' ')[0];
                    $('#technical_inspection_date').val(date);
                }
                
                // SEO tab
                $('#meta_title').val(car.meta_title);
                $('#meta_description').val(car.meta_description);
                $('#meta_keywords').val(car.meta_keywords);
                
                // Set CKEditor content
                if (editor) {
                    editor.setData(car.description || '');
                }
                
                // Set features
                $('input[name="features[]"]').prop('checked', false);
                if (car.features && car.features.length) {
                    car.features.forEach(feature => {
                        $(`input[name="features[]"][value="${feature}"]`).prop('checked', true);
                    });
                }
                
                // Show main image preview if exists
                if (car.main_image) {
                    const mainImageUrl = car.main_image.startsWith('http') 
                        ? car.main_image 
                        : `/storage/${car.main_image}`;
                    
                    mainImagePreview.removeClass('d-none').find('img').attr('src', mainImageUrl);
                }
                
                // Show gallery if additional images exist
                if (car.images && car.images.length) {
                    galleryContainer.removeClass('d-none');
                    imageGallery.empty();
                    
                    car.images.forEach(image => {
                        const imagePath = image.image_path || image.path;
                        const imageUrl = imagePath.startsWith('http') 
                            ? imagePath 
                            : `/storage/${imagePath}`;
                        
                        imageGallery.append(`
                            <div class="col-md-4 image-thumbnail" data-id="${image.id}">
                                <img src="${imageUrl}" class="img-fluid rounded" alt="Car Image">
                                <button type="button" class="btn btn-sm btn-remove-image btn-danger">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        `);
                    });
                    
                    // Attach remove image event listeners
                    $('.btn-remove-image').on('click', function() {
                        const imageId = $(this).closest('.image-thumbnail').data('id');
                        const currentRemoved = removedImagesInput.val();
                        removedImagesInput.val(currentRemoved ? `${currentRemoved},${imageId}` : imageId);
                        $(this).closest('.image-thumbnail').remove();
                        
                        // Hide gallery if no images left
                        if (imageGallery.children().length === 0) {
                            galleryContainer.addClass('d-none');
                        }
                    });
                }
                
                // Show modal
                carModal.modal('show');
                log('Edit car modal displayed');
            })
            .fail(function(xhr) {
                log('Failed to load car data', xhr);
                showAlert('Error', xhr.responseJSON?.message || 'Failed to load car data', 'error');
            });
    }

    // Handle delete car
    function handleDeleteCar(carId) {
        log(`Delete car request for ID: ${carId}`);
        currentCarId = carId;
        deleteModal.modal('show');
    }

    // Confirm delete
    function confirmDelete() {
        log(`Confirming delete for car ID: ${currentCarId}`);
        
        $.ajax({
            url: routes.deleteUrl.replace(':id', currentCarId),
            type: 'DELETE',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                log('Delete success', response);
                showAlert('Success', response.message, 'success');
                deleteModal.modal('hide');
                carsDataTable.ajax.reload(null, false);
            },
            error: function(xhr) {
                log('Delete error', xhr);
                const errorMessage = xhr.responseJSON?.message || 'Failed to delete car';
                
                // Handle specific error cases
                if (xhr.status === 400 && errorMessage.includes('active bookings')) {
                    showAlert('Error', 'This car has active bookings and cannot be deleted.', 'error');
                } else {
                    showAlert('Error', errorMessage, 'error');
                }
            }
        });
    }

    // Preview main image when selected
    function previewMainImage(event) {
        const input = event.target;
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                mainImagePreview.removeClass('d-none').find('img').attr('src', e.target.result);
            };
            reader.readAsDataURL(input.files[0]);
            log('Main image preview updated');
        }
    }

    // Preview additional images when selected
    function previewAdditionalImages(event) {
        const input = event.target;
        if (input.files && input.files.length > 0) {
            galleryContainer.removeClass('d-none');
            
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
                    
                    imageGallery.append(imageHtml);
                };
                
                reader.readAsDataURL(file);
            }
            
            log(`${input.files.length} additional images previewed`);
        }
    }

    // Check document expiry dates
    function checkDocumentExpiryDates() {
        log('Checking document expiry dates');
        const currentDate = new Date();
        const vignetteDate = new Date($('#vignette_date').val());
        const technicalInspectionDate = new Date($('#technical_inspection_date').val());
        const warningThreshold = 30; // days
        
        // Check if vignette is expired or about to expire
        if (vignetteDate < currentDate) {
            $('#vignette_date').addClass('is-invalid');
            $('#vignette_date-error').text('Vignette has expired!');
            log('Vignette has expired');
        } else if ((vignetteDate - currentDate) / (1000 * 60 * 60 * 24) < warningThreshold) {
            $('#vignette_date').addClass('is-warning');
            $('#vignette_date-error').text('Vignette will expire soon!');
            log('Vignette will expire soon');
        }
        
        // Check if technical inspection is expired or about to expire
        if (technicalInspectionDate < currentDate) {
            $('#technical_inspection_date').addClass('is-invalid');
            $('#technical_inspection_date-error').text('Technical inspection has expired!');
            log('Technical inspection has expired');
        } else if ((technicalInspectionDate - currentDate) / (1000 * 60 * 60 * 24) < warningThreshold) {
            $('#technical_inspection_date').addClass('is-warning');
            $('#technical_inspection_date-error').text('Technical inspection will expire soon!');
            log('Technical inspection will expire soon');
        }
    }

    // Attach event listeners for edit buttons
    function attachEditEventListeners() {
        $('.edit-btn').on('click', function() {
            handleEditCar($(this).data('id'));
        });
    }

    // Attach event listeners for delete buttons
    function attachDeleteEventListeners() {
        $('.delete-btn').on('click', function() {
            handleDeleteCar($(this).data('id'));
        });
    }

    // Tab navigation improvement
    function setupTabNavigation() {
        $('#carTabs .nav-link').on('click', function(e) {
            e.preventDefault();
            $(this).tab('show');
            log(`Tab changed to: ${$(this).attr('id')}`);
        });
        
        // Reset validation when changing tabs
        $('#carTabs .nav-link').on('shown.bs.tab', function() {
            const targetPane = $($(this).attr('data-bs-target'));
            targetPane.find('.is-invalid').removeClass('is-invalid');
            targetPane.find('.invalid-feedback').text('');
            log(`Validation cleared for tab: ${$(this).attr('id')}`);
        });
    }

    // Setup matricule field handling
    function setupMatriculeHandling() {
        const matriculeField = $('#matricule');
        
        // Format and validate on blur
        matriculeField.on('blur', function() {
            const value = $(this).val().trim();
            
            if (!value) return;
            
            const formattedValue = formatMatricule(value);
            $(this).val(formattedValue);
            
            // Validate after formatting
            if (!validateMatricule(formattedValue)) {
                $(this).addClass('is-invalid');
                $('#matricule-error').text('Invalid format. Should be: numbers-letter-region code (e.g. 12345-A-6 or 12345-أ-6)');
                log('Matricule validation failed');
            } else {
                $(this).removeClass('is-invalid');
                $('#matricule-error').text('');
                log('Matricule formatted and validated');
            }
        });
        
        // Remove validation errors as user types
        matriculeField.on('input', function() {
            $(this).removeClass('is-invalid');
            $('#matricule-error').text('');
        });
    }

    // Setup form field validation clearing
    function setupFormValidation() {
        carForm.find('input, select, textarea').on('input change', function() {
            $(this).removeClass('is-invalid');
            $(`#${$(this).attr('id')}-error`).text('');
        });
    }

    // Set default dates
    function setupDefaultDates() {
        // Set today's date as default for date fields
        const today = new Date().toISOString().split('T')[0];
        $('#mise_en_service_date').val(today);
        $('#vignette_date').val(today);
        $('#technical_inspection_date').val(today);
    }

    // Initialize the application
    async function init() {
        try {
            log('Initializing Car Management');
            
            // Initialize CKEditor
            await initCKEditor();
            
            // Initialize DataTable
            carsDataTable = initDataTable();
            
            // Setup tab navigation
            setupTabNavigation();
            
            // Setup matricule handling
            setupMatriculeHandling();
            
            // Setup form validation
            setupFormValidation();
            
            // Setup default dates
            setupDefaultDates();
            
            // Event listeners
            createCarBtn.on('click', function() {
                resetForm();
                $('#carModalLabel').text('Add New Car');
                carModal.modal('show');
                log('Create new car modal opened');
            });
            
            carForm.on('submit', handleFormSubmit);
            confirmDeleteBtn.on('click', confirmDelete);
            $('#main_image').on('change', previewMainImage);
            $('#additional_images').on('change', previewAdditionalImages);
            
            // Document expiry check
            $('#documents-tab').on('click', checkDocumentExpiryDates);
            
            // Modal hidden event
            carModal.on('hidden.bs.modal', function() {
                resetForm();
                log('Modal hidden, form reset');
            });
            
            // Attach initial event listeners
            attachEditEventListeners();
            attachDeleteEventListeners();
            
            log('Car Management initialized');
        } catch (error) {
            console.error('Error initializing Car Management:', error);
        }
    }

    // Start the application
    init();
});