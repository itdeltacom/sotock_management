'use strict';

document.addEventListener('DOMContentLoaded', function () {
    // Cache DOM elements
    const bookingsTable = document.getElementById('bookings-table');
    const bookingForm = document.getElementById('bookingForm');
    const bookingModal = document.getElementById('bookingModal');
    const viewBookingModal = document.getElementById('viewBookingModal');
    const createBookingBtn = document.getElementById('createBookingBtn');
    const saveBtn = document.getElementById('saveBtn');
    const filterForm = document.getElementById('filterForm');
    const resetFiltersBtn = document.getElementById('resetFiltersBtn');
    const exportBtn = document.getElementById('exportBtn');
    const viewEditBtn = document.getElementById('viewEditBtn');
    
    // Vehicle selection and date fields for price calculation
    const carIdSelect = document.getElementById('car_id');
    const pickupDateInput = document.getElementById('pickup_date');
    const dropoffDateInput = document.getElementById('dropoff_date');
    const insurancePlanSelect = document.getElementById('insurance_plan');
    const additionalDriverCheck = document.getElementById('additional_driver');
    const deliveryOptionSelect = document.getElementById('delivery_option');
    const gpsEnabledCheck = document.getElementById('gps_enabled');
    const childSeatCheck = document.getElementById('child_seat');
    
    // Customer fields
    const userIdSelect = document.getElementById('user_id');
    const customerNameInput = document.getElementById('customer_name');
    const customerEmailInput = document.getElementById('customer_email');
    const customerPhoneInput = document.getElementById('customer_phone');
    const customerIdNumberInput = document.getElementById('customer_id_number');
    
    // Status fields
    const statusSelect = document.getElementById('status');
    const cancellationReasonContainer = document.getElementById('cancellation_reason_container');
    
    // Store current filters
    let currentFilters = {
        car_id: '',
        status: '',
        payment_status: '',
        date_from: '',
        date_to: ''
    };
    
    // Initialize DataTable
    let table = new DataTable('#bookings-table', {
        processing: true,
        serverSide: true,
        ajax: {
            url: routes.dataUrl,
            type: 'GET',
            data: function (d) {
                d.car_id = currentFilters.car_id;
                d.status = currentFilters.status;
                d.payment_status = currentFilters.payment_status;
                d.date_from = currentFilters.date_from;
                d.date_to = currentFilters.date_to;
                return d;
            }
        },
        columns: [
            { data: 'id', name: 'id' },
            { data: 'booking_info', name: 'booking_number' },
            { data: 'customer', name: 'customer_name' },
            { data: 'car_name', name: 'car.name' },
            { data: 'rental_period', name: 'pickup_date' },
            { data: 'amount', name: 'total_amount' },
            { data: 'status_badge', name: 'status' },
            { data: 'payment_badge', name: 'payment_status' },
            { data: 'status_actions', name: 'status_actions', orderable: false, searchable: false },
            { data: 'payment_actions', name: 'payment_actions', orderable: false, searchable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        order: [[0, 'desc']],
        pageLength: 25,
        responsive: true,
        language: {
            processing: "Traitement en cours...",
            search: "Rechercher&nbsp;:",
            lengthMenu: "Afficher _MENU_ éléments",
            info: "Affichage de l'élément _START_ à _END_ sur _TOTAL_ éléments",
            infoEmpty: "Affichage de l'élément 0 à 0 sur 0 élément",
            infoFiltered: "(filtré de _MAX_ éléments au total)",
            infoPostFix: "",
            loadingRecords: "Chargement en cours...",
            zeroRecords: "Aucun élément à afficher",
            emptyTable: "Aucune donnée disponible dans le tableau",
            paginate: {
                next: '<i class="fas fa-angle-right"></i>',
                previous: '<i class="fas fa-angle-left"></i>'
            },
            aria: {
                sortAscending: ": activer pour trier la colonne par ordre croissant",
                sortDescending: ": activer pour trier la colonne par ordre décroissant"
            }
        }
    });
    
    // Set up event listeners
    if (createBookingBtn) {
        createBookingBtn.addEventListener('click', function() {
            resetForm();
            // Set default dates
            const today = new Date().toISOString().split('T')[0];
            const tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);
            const tomorrowStr = tomorrow.toISOString().split('T')[0];
            
            pickupDateInput.value = today;
            dropoffDateInput.value = tomorrowStr;
            
            // Set default times
            document.getElementById('pickup_time').value = '10:00';
            document.getElementById('dropoff_time').value = '10:00';
            
            // Default insurance plan
            insurancePlanSelect.value = 'basic';
            
            // Default delivery option
            deliveryOptionSelect.value = 'none';
            
            // Default language
            document.getElementById('language_preference').value = 'fr';
            
            // Set default deposit
            document.getElementById('deposit_amount').value = 1000;
            document.getElementById('deposit_status').value = 'pending';
            
            // Update modal title
            document.getElementById('bookingModalLabel').textContent = 'Nouvelle Réservation';
            
            // Show modal using Bootstrap 5 modal
            const bsModal = new bootstrap.Modal(bookingModal);
            bsModal.show();
        });
    }
    
    // Listen for status change to show/hide cancellation reason
    if (statusSelect) {
        statusSelect.addEventListener('change', function() {
            if (this.value === 'cancelled') {
                cancellationReasonContainer.style.display = 'block';
                document.getElementById('cancellation_reason').setAttribute('required', 'required');
            } else {
                cancellationReasonContainer.style.display = 'none';
                document.getElementById('cancellation_reason').removeAttribute('required');
            }
        });
    }
    
    // Delivery option handling
    if (deliveryOptionSelect) {
        deliveryOptionSelect.addEventListener('change', function() {
            const deliveryAddressField = document.getElementById('delivery_address');
            if (this.value === 'home' || this.value === 'airport') {
                deliveryAddressField.classList.remove('d-none');
                deliveryAddressField.setAttribute('required', 'required');
            } else {
                deliveryAddressField.classList.add('d-none');
                deliveryAddressField.removeAttribute('required');
            }
            
            // Recalculate prices when option changes
            calculatePrices();
        });
    }
    
    // Additional driver checkbox
    if (additionalDriverCheck) {
        additionalDriverCheck.addEventListener('change', function() {
            const fieldsContainer = document.getElementById('additional_driver_fields');
            const nameField = document.getElementById('additional_driver_name');
            const licenseField = document.getElementById('additional_driver_license');
            
            if (this.checked) {
                fieldsContainer.classList.remove('d-none');
                nameField.removeAttribute('disabled');
                licenseField.removeAttribute('disabled');
                nameField.setAttribute('required', 'required');
                licenseField.setAttribute('required', 'required');
            } else {
                fieldsContainer.classList.add('d-none');
                nameField.setAttribute('disabled', 'disabled');
                licenseField.setAttribute('disabled', 'disabled');
                nameField.removeAttribute('required');
                licenseField.removeAttribute('required');
            }
            
            // Recalculate prices when option changes
            calculatePrices();
        });
    }
    
    // GPS and child seat options
    if (gpsEnabledCheck) {
        gpsEnabledCheck.addEventListener('change', calculatePrices);
    }
    
    if (childSeatCheck) {
        childSeatCheck.addEventListener('change', calculatePrices);
    }
    
    // Insurance plan
    if (insurancePlanSelect) {
        insurancePlanSelect.addEventListener('change', calculatePrices);
    }
    
    if (bookingForm) {
        bookingForm.addEventListener('submit', handleFormSubmit);
    }
    
    if (filterForm) {
        filterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            applyFilters();
        });
    }
    
    if (resetFiltersBtn) {
        resetFiltersBtn.addEventListener('click', function() {
            document.getElementById('filter_car').value = '';
            document.getElementById('filter_status').value = '';
            document.getElementById('filter_payment_status').value = '';
            document.getElementById('filter_date_from').value = '';
            document.getElementById('filter_date_to').value = '';
            applyFilters();
        });
    }
    
    if (exportBtn) {
        exportBtn.addEventListener('click', function() {
            const queryParams = new URLSearchParams({
                car_id: currentFilters.car_id,
                status: currentFilters.status,
                payment_status: currentFilters.payment_status,
                date_from: currentFilters.date_from,
                date_to: currentFilters.date_to
            }).toString();
            
            window.location.href = `${routes.exportUrl}?${queryParams}`;
        });
    }
    
    if (carIdSelect) {
        carIdSelect.addEventListener('change', calculatePrices);
    }
    
    if (pickupDateInput) {
        pickupDateInput.addEventListener('change', calculatePrices);
    }
    
    if (dropoffDateInput) {
        dropoffDateInput.addEventListener('change', calculatePrices);
    }
    
    if (userIdSelect) {
        userIdSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption.value) {
                customerNameInput.value = selectedOption.getAttribute('data-name') || '';
                customerEmailInput.value = selectedOption.getAttribute('data-email') || '';
                customerPhoneInput.value = selectedOption.getAttribute('data-phone') || '';
            } else {
                // Only clear fields when creating a new booking
                if (!document.getElementById('booking_id').value) {
                    customerNameInput.value = '';
                    customerEmailInput.value = '';
                    customerPhoneInput.value = '';
                    if (customerIdNumberInput) {
                        customerIdNumberInput.value = '';
                    }
                }
            }
        });
    }
    
    if (viewEditBtn) {
        viewEditBtn.addEventListener('click', function() {
            const bookingId = this.getAttribute('data-id');
            if (bookingId) {
                // Close view modal
                const viewModalInstance = bootstrap.Modal.getInstance(viewBookingModal);
                if (viewModalInstance) viewModalInstance.hide();
                
                // Open edit modal with a small delay to avoid visual glitches
                setTimeout(() => {
                    handleEditBooking(bookingId);
                }, 300);
            }
        });
    }
    
    // Handle location select fields
    const locationSelects = document.querySelectorAll('.location-select');
    locationSelects.forEach(select => {
        select.addEventListener('change', function() {
            const customInputId = this.id + '_custom';
            const customInput = document.getElementById(customInputId);
            
            if (this.value === 'custom') {
                customInput.classList.remove('d-none');
                customInput.setAttribute('required', 'required');
                this.setAttribute('name', this.id + '_select');
                customInput.setAttribute('name', this.id);
            } else {
                customInput.classList.add('d-none');
                customInput.removeAttribute('required');
                this.setAttribute('name', this.id);
                customInput.setAttribute('name', customInputId);
            }
        });
    });
    
    // Handle action buttons with event delegation
    document.addEventListener('click', function(e) {
        // View button
        if (e.target.closest('.btn-view')) {
            const button = e.target.closest('.btn-view');
            const bookingId = button.getAttribute('data-id');
            if (bookingId) handleViewBooking(bookingId);
        }
        
        // Edit button
        if (e.target.closest('.btn-edit')) {
            const button = e.target.closest('.btn-edit');
            const bookingId = button.getAttribute('data-id');
            if (bookingId) handleEditBooking(bookingId);
        }
        
        // Delete button
        if (e.target.closest('.btn-delete')) {
            const button = e.target.closest('.btn-delete');
            const bookingId = button.getAttribute('data-id');
            const bookingNumber = button.getAttribute('data-number') || 'cette réservation';
            if (bookingId) handleDeleteBooking(bookingId, bookingNumber);
        }
        
        // Status change button
        if (e.target.closest('.btn-status')) {
            const button = e.target.closest('.btn-status');
            const bookingId = button.getAttribute('data-id');
            const status = button.getAttribute('data-status');
            if (bookingId && status) handleStatusChange(bookingId, status);
        }
        
        // Payment status change button
        if (e.target.closest('.btn-payment')) {
            const button = e.target.closest('.btn-payment');
            const bookingId = button.getAttribute('data-id');
            const status = button.getAttribute('data-status');
            if (bookingId && status) handlePaymentStatusChange(bookingId, status);
        }
        
        // Deposit status change button
        if (e.target.closest('.btn-deposit')) {
            const button = e.target.closest('.btn-deposit');
            const bookingId = button.getAttribute('data-id');
            const status = button.getAttribute('data-status');
            if (bookingId && status) handleDepositStatusChange(bookingId, status);
        }
        
        // Start rental button
        if (e.target.closest('.btn-start-rental')) {
            const button = e.target.closest('.btn-start-rental');
            const bookingId = button.getAttribute('data-id');
            if (bookingId) handleStartRental(bookingId);
        }
        
        // Complete rental button
        if (e.target.closest('.btn-complete-rental')) {
            const button = e.target.closest('.btn-complete-rental');
            const bookingId = button.getAttribute('data-id');
            if (bookingId) handleCompleteRental(bookingId);
        }
    });
    
    /**
     * Apply filters to the DataTable
     */
    function applyFilters() {
        currentFilters = {
            car_id: document.getElementById('filter_car').value,
            status: document.getElementById('filter_status').value,
            payment_status: document.getElementById('filter_payment_status').value,
            date_from: document.getElementById('filter_date_from').value,
            date_to: document.getElementById('filter_date_to').value
        };
        
        table.ajax.reload();
    }
    
    /**
     * Calculate prices based on selected car and dates
     */
    function calculatePrices() {
        console.log('Calculating prices...'); // Debugging

        const carId = document.getElementById('car_id').value;
        const pickupDate = document.getElementById('pickup_date').value;
        const dropoffDate = document.getElementById('dropoff_date').value;
        const bookingId = document.getElementById('booking_id').value;
        
        // Get optional parameters if they exist
        const insurancePlan = document.getElementById('insurance_plan') ? document.getElementById('insurance_plan').value : 'basic';
        const additionalDriver = document.getElementById('additional_driver') ? document.getElementById('additional_driver').checked : false;
        const deliveryOption = document.getElementById('delivery_option') ? document.getElementById('delivery_option').value : 'none';
        const gpsEnabled = document.getElementById('gps_enabled') ? document.getElementById('gps_enabled').checked : false;
        const childSeat = document.getElementById('child_seat') ? document.getElementById('child_seat').checked : false;
        
        // Basic validation
        if (!carId || !pickupDate || !dropoffDate) {
            console.log('Missing required fields'); // Debugging
            resetPricing();
            return;
        }
        
        // Validate dates
        if (new Date(pickupDate) > new Date(dropoffDate)) {
            document.getElementById('availability_display').innerHTML = `
                <span class="badge bg-danger">Dates Invalides</span>
            `;
            resetPricing();
            return;
        }
        
        // Show loading state
        document.getElementById('availability_display').innerHTML = `
            <span class="badge bg-secondary">
                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                Vérification de la disponibilité...
            </span>
        `;
        
        // Prepare form data
        const formData = new FormData();
        formData.append('car_id', carId);
        formData.append('pickup_date', pickupDate);
        formData.append('dropoff_date', dropoffDate);
        formData.append('insurance_plan', insurancePlan);
        formData.append('additional_driver', additionalDriver ? 1 : 0);
        formData.append('delivery_option', deliveryOption);
        formData.append('gps_enabled', gpsEnabled ? 1 : 0);
        formData.append('child_seat', childSeat ? 1 : 0);
        
        if (bookingId) {
            formData.append('booking_id', bookingId);
        }
        
        // Make AJAX request
        fetch(routes.calculateUrl, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            console.log('Response data:', data); // Debugging
            
            if (data.success) {
                // Update availability display
                const isAvailable = data.data.is_available;
                document.getElementById('availability_display').innerHTML = isAvailable
                    ? '<span class="badge bg-success">Véhicule Disponible</span>'
                    : '<span class="badge bg-danger">Véhicule Indisponible</span>';
                
                // Update pricing fields
                document.getElementById('total_days').value = data.data.total_days;
                document.getElementById('base_price').value = data.data.base_price.toFixed(2);
                document.getElementById('discount_amount').value = data.data.discount_amount.toFixed(2);
                document.getElementById('tax_amount').value = data.data.tax_amount.toFixed(2);
                document.getElementById('total_amount').value = data.data.total_amount.toFixed(2);
                
                // Update deposit amount if provided
                if (data.data.deposit_amount) {
                    document.getElementById('deposit_amount').value = data.data.deposit_amount.toFixed(2);
                }
            } else {
                console.error('Error in calculation response:', data.message); // Debugging
                document.getElementById('availability_display').innerHTML = `
                    <span class="badge bg-warning">Erreur lors de la vérification</span>
                `;
                resetPricing();
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            document.getElementById('availability_display').innerHTML = `
                <span class="badge bg-danger">Erreur</span>
            `;
            resetPricing();
        });
    }

    /**
     * Direct fix for the price calculation section
     */
    console.log('Initializing booking price calculation...');
    
    // Check if we're on a page with the booking form
    if (!bookingForm) {
        console.log('No booking form found on this page');
    } else {
        // Verify the pricing fields exist
        const pricingFields = [
            'car_id', 'pickup_date', 'dropoff_date', 
            'total_days', 'base_price', 'discount_amount', 
            'tax_amount', 'total_amount', 'deposit_amount'
        ];
        
        let missingFields = [];
        pricingFields.forEach(field => {
            if (!document.getElementById(field)) {
                missingFields.push(field);
            }
        });
        
        if (missingFields.length > 0) {
            console.error('Missing pricing fields:', missingFields);
        } else {
            // Check if routes are properly defined
            if (typeof routes === 'undefined' || !routes.calculateUrl) {
                console.error('Routes are not properly defined. Make sure routes.calculateUrl is set.');
            } else {
                console.log('All pricing fields found, setting up event listeners');
                
                // Setup main calculation triggers
                const carSelect = document.getElementById('car_id');
                const pickupDateInput = document.getElementById('pickup_date');
                const dropoffDateInput = document.getElementById('dropoff_date');
                
                // Add event listeners to the main calculation triggers
                carSelect.addEventListener('change', triggerPriceCalculation);
                pickupDateInput.addEventListener('change', triggerPriceCalculation);
                dropoffDateInput.addEventListener('change', triggerPriceCalculation);
                
                // Add listeners to optional features if they exist
                const optionalFeatures = [
                    'insurance_plan', 'additional_driver', 
                    'delivery_option', 'gps_enabled', 'child_seat'
                ];
                
                optionalFeatures.forEach(feature => {
                    const element = document.getElementById(feature);
                    if (element) {
                        if (element.type === 'checkbox') {
                            element.addEventListener('change', triggerPriceCalculation);
                        } else {
                            element.addEventListener('change', triggerPriceCalculation);
                        }
                    }
                });
                
                // If form is in edit mode (has pre-filled values), calculate prices immediately
                if (carSelect.value && pickupDateInput.value && dropoffDateInput.value) {
                    console.log('Form has initial values, triggering immediate calculation');
                    triggerPriceCalculation();
                }
            }
        }
    }
    
    // The main calculation trigger function
    function triggerPriceCalculation() {
        const carSelect = document.getElementById('car_id');
        const pickupDateInput = document.getElementById('pickup_date');
        const dropoffDateInput = document.getElementById('dropoff_date');
        
        const carId = carSelect.value;
        const pickupDate = pickupDateInput.value;
        const dropoffDate = dropoffDateInput.value;
        
        // Basic validation
        if (!carId || !pickupDate || !dropoffDate) {
            console.log('Missing required fields for calculation');
            resetPricingFields();
            return;
        }
        
        // Check date order
        if (new Date(pickupDate) > new Date(dropoffDate)) {
            document.getElementById('availability_display').innerHTML = 
                '<span class="badge bg-danger">Dates Invalides</span>';
            resetPricingFields();
            return;
        }
        
        // Show loading indicator
        document.getElementById('availability_display').innerHTML = 
            '<span class="badge bg-secondary"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Calcul en cours...</span>';
        
        // Collect all form data
        const formData = new FormData();
        formData.append('car_id', carId);
        formData.append('pickup_date', pickupDate);
        formData.append('dropoff_date', dropoffDate);
        
        // Add optional parameters
        if (document.getElementById('insurance_plan')) {
            formData.append('insurance_plan', document.getElementById('insurance_plan').value);
        } else {
            formData.append('insurance_plan', 'basic'); // Default value
        }
        
        if (document.getElementById('additional_driver')) {
            formData.append('additional_driver', document.getElementById('additional_driver').checked ? 1 : 0);
        } else {
            formData.append('additional_driver', 0); // Default value
        }
        
        if (document.getElementById('delivery_option')) {
            formData.append('delivery_option', document.getElementById('delivery_option').value);
        } else {
            formData.append('delivery_option', 'none'); // Default value
        }
        
        if (document.getElementById('gps_enabled')) {
            formData.append('gps_enabled', document.getElementById('gps_enabled').checked ? 1 : 0);
        } else {
            formData.append('gps_enabled', 0); // Default value
        }
        
        if (document.getElementById('child_seat')) {
            formData.append('child_seat', document.getElementById('child_seat').checked ? 1 : 0);
        } else {
            formData.append('child_seat', 0); // Default value
        }
        
        // Include booking ID if we're editing an existing booking
        const bookingId = document.getElementById('booking_id').value;
        if (bookingId) {
            formData.append('booking_id', bookingId);
        }
        
        // Get CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (!csrfToken) {
            console.error('CSRF token not found!');
            return;
        }
        
        // Make the AJAX request
        console.log('Sending price calculation request to:', routes.calculateUrl);
        
        fetch(routes.calculateUrl, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Price calculation response:', data);
            
            if (data.success) {
                // Update display with calculated values
                document.getElementById('total_days').value = data.data.total_days;
                document.getElementById('base_price').value = data.data.base_price.toFixed(2);
                document.getElementById('discount_amount').value = data.data.discount_amount.toFixed(2);
                document.getElementById('tax_amount').value = data.data.tax_amount.toFixed(2);
                document.getElementById('total_amount').value = data.data.total_amount.toFixed(2);
                
                // Update deposit if provided
                if (data.data.deposit_amount) {
                    document.getElementById('deposit_amount').value = data.data.deposit_amount.toFixed(2);
                }
                
                // Update availability badge
                document.getElementById('availability_display').innerHTML = data.data.is_available
                    ? '<span class="badge bg-success">Véhicule Disponible</span>'
                    : '<span class="badge bg-danger">Véhicule Indisponible</span>';
            } else {
                // Show error
                document.getElementById('availability_display').innerHTML = 
                    '<span class="badge bg-warning">Erreur de Calcul</span>';
                console.error('Calculation error:', data.message || 'Unknown error');
                resetPricingFields();
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            document.getElementById('availability_display').innerHTML = 
                '<span class="badge bg-danger">Erreur de Connexion</span>';
            resetPricingFields();
        });
    }
    
    // Reset all pricing fields
    function resetPricingFields() {
        document.getElementById('total_days').value = '';
        document.getElementById('base_price').value = '';
        document.getElementById('discount_amount').value = '';
        document.getElementById('tax_amount').value = '';
        document.getElementById('total_amount').value = '';
        // Leave deposit value as is, usually has a default value
    }
    
    /**
     * Reset pricing fields
     */
    function resetPricing() {
        document.getElementById('total_days').value = '';
        document.getElementById('base_price').value = '';
        document.getElementById('discount_amount').value = '';
        document.getElementById('tax_amount').value = '';
        document.getElementById('total_amount').value = '';
    }
    
    /**
     * Handle form submission
     */
    function handleFormSubmit(e) {
        e.preventDefault();
        
        // Reset validation UI
        clearValidationErrors();
        
        // Handle custom locations before submitting
        const pickupLocationSelect = document.getElementById('pickup_location');
        const dropoffLocationSelect = document.getElementById('dropoff_location');
        
        if (pickupLocationSelect && pickupLocationSelect.value === 'custom') {
            const customPickup = document.getElementById('pickup_location_custom').value;
            pickupLocationSelect.value = customPickup;
        }
        
        if (dropoffLocationSelect && dropoffLocationSelect.value === 'custom') {
            const customDropoff = document.getElementById('dropoff_location_custom').value;
            dropoffLocationSelect.value = customDropoff;
        }
        
        // Get form data
        const formData = new FormData(e.target);
        
        // Get booking ID and determine if this is an edit operation
        const bookingId = document.getElementById('booking_id').value;
        const isEdit = bookingId && bookingId !== '';
        
        // For PUT requests, Laravel doesn't process FormData the same way as POST
        if (isEdit) {
            formData.append('_method', 'PUT');
        }
        
        // Set up request URL
        const url = isEdit ? routes.updateUrl.replace(':id', bookingId) : routes.storeUrl;
        
        // Show loading state
        if (saveBtn) {
            saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Enregistrement...';
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
                const bsModal = bootstrap.Modal.getInstance(bookingModal);
                if (bsModal) bsModal.hide();
                
                // Reload table
                table.ajax.reload();
                
                // Show success message
                Swal.fire({
                    icon: 'success',
                    title: 'Succès',
                    text: data.message || 'Réservation enregistrée avec succès',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                });
            } else {
                throw new Error(data.message || 'Une erreur est survenue');
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
                    title: 'Erreur de Validation',
                    text: 'Veuillez vérifier les erreurs dans le formulaire',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 5000
                });
            } else {
                // Show error notification
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    text: error.data?.message || 'Une erreur est survenue',
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
                saveBtn.innerHTML = 'Enregistrer';
                saveBtn.disabled = false;
            }
        });
    }
    
    /**
     * Handle view booking
     */
    function handleViewBooking(bookingId) {
        // Show modal with loading overlay
        const bsModal = new bootstrap.Modal(viewBookingModal);
        bsModal.show();
        
        const modalBody = document.querySelector('#viewBookingModal .modal-body');
        if (modalBody) {
            const loadingDiv = document.createElement('div');
            loadingDiv.id = 'view-loading-overlay';
            loadingDiv.className = 'position-absolute bg-white d-flex justify-content-center align-items-center';
            loadingDiv.style.cssText = 'left: 0; top: 0; right: 0; bottom: 0; z-index: 10;';
            loadingDiv.innerHTML = '<div class="spinner-border text-primary"></div>';
            
            // Add loading overlay
            modalBody.style.position = 'relative';
            modalBody.appendChild(loadingDiv);
        }
        
        // Fetch booking data
        fetch(routes.showUrl.replace(':id', bookingId), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            // Remove loading overlay
            const overlay = document.getElementById('view-loading-overlay');
            if (overlay) overlay.remove();
            
            if (data.success && data.booking) {
                displayBookingDetails(data.booking);
            } else {
                const bsModal = bootstrap.Modal.getInstance(viewBookingModal);
                if (bsModal) bsModal.hide();
                Swal.fire('Erreur', 'Impossible de charger les données de la réservation', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            const bsModal = bootstrap.Modal.getInstance(viewBookingModal);
            if (bsModal) bsModal.hide();
            Swal.fire('Erreur', 'Impossible de charger les données de la réservation', 'error');
        });
    }
    
    /**
     * Display booking details in the view modal
     */
    function displayBookingDetails(booking) {
        // Set booking number and status badges
        document.getElementById('view-booking-number').textContent = `Réservation #${booking.booking_number}`;
        
        // Set status badge
        const statusClasses = {
            'pending': 'bg-warning',
            'confirmed': 'bg-success',
            'in_progress': 'bg-primary',
            'completed': 'bg-info',
            'cancelled': 'bg-danger',
            'no_show': 'bg-dark'
        };
        const statusClass = statusClasses[booking.status] || 'bg-secondary';
        const statusLabels = {
            'pending': 'En attente',
            'confirmed': 'Confirmée',
            'in_progress': 'En cours',
            'completed': 'Terminée',
            'cancelled': 'Annulée',
            'no_show': 'Non présenté'
        };
        document.getElementById('view-status-badge').innerHTML = `
            <span class="badge ${statusClass}">${statusLabels[booking.status] || capitalize(booking.status)}</span>
        `;
        
        // Set payment badge
        const paymentClasses = {
            'paid': 'bg-success',
            'unpaid': 'bg-danger',
            'pending': 'bg-warning',
            'refunded': 'bg-info'
        };
        const paymentClass = paymentClasses[booking.payment_status] || 'bg-secondary';
        const paymentLabels = {
            'paid': 'Payé',
            'unpaid': 'Non payé',
            'pending': 'En attente',
            'refunded': 'Remboursé'
        };
        document.getElementById('view-payment-badge').innerHTML = `
            <span class="badge ${paymentClass}">${paymentLabels[booking.payment_status] || capitalize(booking.payment_status)}</span>
        `;
        
        // Set dates
        document.getElementById('view-created-at').textContent = formatDate(booking.created_at);
        document.getElementById('view-updated-at').textContent = formatDate(booking.updated_at);
        
        // Set car information
        document.getElementById('view-car-name').textContent = booking.car ? booking.car.name : 'N/A';
        document.getElementById('view-car-details').textContent = booking.car ? 
            `Prix: ${booking.car.price_per_day} MAD/jour, Remise: ${booking.car.discount_percentage}%` : '';
        
        // Set customer information
        document.getElementById('view-customer-name').textContent = booking.customer_name;
        document.getElementById('view-customer-email').textContent = booking.customer_email;
        document.getElementById('view-customer-phone').textContent = booking.customer_phone || 'Non fourni';
        document.getElementById('view-customer-id-number').textContent = `CIN: ${booking.customer_id_number || 'Non fourni'}`;
        document.getElementById('view-customer-account').innerHTML = booking.user ? 
            `<span class="badge bg-info">Utilisateur Enregistré</span>` : 
            `<span class="badge bg-secondary">Invité</span>`;
        
        // Set rental details
        document.getElementById('view-pickup-details').innerHTML = `
            ${formatDate(booking.pickup_date)} à ${booking.pickup_time}<br>
            <small class="text-muted">${booking.pickup_location}</small>
        `;
        document.getElementById('view-dropoff-details').innerHTML = `
            ${formatDate(booking.dropoff_date)} à ${booking.dropoff_time}<br>
            <small class="text-muted">${booking.dropoff_location}</small>
        `;
        document.getElementById('view-duration').textContent = `${booking.total_days} jours`;
        
        // Set options
        let optionsHtml = '';
        if (booking.insurance_plan) {
            const insuranceLabels = {
                'basic': 'Basique',
                'standard': 'Standard',
                'premium': 'Premium'
            };
            optionsHtml += `<div>Assurance: <span class="badge bg-info">${insuranceLabels[booking.insurance_plan] || booking.insurance_plan}</span></div>`;
        }
        
        if (booking.delivery_option && booking.delivery_option !== 'none') {
            const deliveryLabels = {
                'home': 'Livraison à domicile',
                'airport': 'Livraison à l\'aéroport'
            };
            optionsHtml += `<div>${deliveryLabels[booking.delivery_option] || booking.delivery_option}</div>`;
        }
        
        if (booking.additional_driver) {
            optionsHtml += `<div>Conducteur supplémentaire: ${booking.additional_driver_name || 'Oui'}</div>`;
        }
        
        if (booking.gps_enabled) {
            optionsHtml += `<div>GPS inclus</div>`;
        }
        
        if (booking.child_seat) {
            optionsHtml += `<div>Siège enfant inclus</div>`;
        }
        
        document.getElementById('view-options').innerHTML = optionsHtml || 'Aucune option sélectionnée';
        
        // Set payment information
        document.getElementById('view-base-price').textContent = `${parseFloat(booking.base_price).toFixed(2)} MAD`;
        document.getElementById('view-discount').textContent = `${parseFloat(booking.discount_amount).toFixed(2)} MAD`;
        document.getElementById('view-tax').textContent = `${parseFloat(booking.tax_amount).toFixed(2)} MAD`;
        document.getElementById('view-total').textContent = `${parseFloat(booking.total_amount).toFixed(2)} MAD`;
        document.getElementById('view-payment-method').textContent = getPaymentMethodLabel(booking.payment_method);
        document.getElementById('view-transaction-id').textContent = booking.transaction_id || 'N/A';
        
        // Set deposit information
        const depositStatusLabels = {
            'pending': 'En attente',
            'paid': 'Payée',
            'refunded': 'Remboursée',
            'forfeited': 'Perdue'
        };
        const depositStatusClass = {
            'pending': 'bg-warning',
            'paid': 'bg-success',
            'refunded': 'bg-info',
            'forfeited': 'bg-danger'
        };
        document.getElementById('view-deposit').innerHTML = `
            ${parseFloat(booking.deposit_amount).toFixed(2)} MAD
            <span class="badge ${depositStatusClass[booking.deposit_status] || 'bg-secondary'}">
                ${depositStatusLabels[booking.deposit_status] || capitalize(booking.deposit_status)}
            </span>
        `;
        
        // Set special requests
        document.getElementById('view-special-requests').textContent = booking.special_requests || 'Aucune demande spéciale';
        document.getElementById('view-notes').textContent = booking.notes || 'Aucune note';
        
        // Set status actions
        const viewStatusActions = document.getElementById('view-status-actions');
        viewStatusActions.innerHTML = '';
        
        if (booking.status === 'pending') {
            viewStatusActions.innerHTML += `
                <button type="button" class="btn btn-sm btn-success btn-status me-1" data-id="${booking.id}" data-status="confirmed">
                    <i class="fas fa-check"></i> Confirmer
                </button>`;
        }
        
        if (booking.status === 'confirmed') {
            viewStatusActions.innerHTML += `
                <button type="button" class="btn btn-sm btn-primary btn-start-rental me-1" data-id="${booking.id}">
                    <i class="fas fa-car"></i> Démarrer
                </button>`;
        }
        
        if (booking.status === 'in_progress') {
            viewStatusActions.innerHTML += `
                <button type="button" class="btn btn-sm btn-info btn-complete-rental me-1" data-id="${booking.id}">
                    <i class="fas fa-flag-checkered"></i> Terminer
                </button>`;
        }
        
        if (booking.status === 'pending' || booking.status === 'confirmed') {
            viewStatusActions.innerHTML += `
                <button type="button" class="btn btn-sm btn-dark btn-status me-1" data-id="${booking.id}" data-status="no_show">
                    <i class="fas fa-user-times"></i> Non Présenté
                </button>`;
        }
        
        if (booking.status !== 'cancelled' && booking.status !== 'completed' && booking.status !== 'no_show') {
            viewStatusActions.innerHTML += `
                <button type="button" class="btn btn-sm btn-danger btn-status me-1" data-id="${booking.id}" data-status="cancelled">
                    <i class="fas fa-ban"></i> Annuler
                </button>`;
        }
        
        if (booking.payment_status === 'unpaid' || booking.payment_status === 'pending') {
            viewStatusActions.innerHTML += `
                <button type="button" class="btn btn-sm btn-success btn-payment me-1" data-id="${booking.id}" data-status="paid">
                    <i class="fas fa-money-bill-wave"></i> Marquer Payé
                </button>`;
        }
        
        if (booking.payment_status === 'paid') {
            viewStatusActions.innerHTML += `
                <button type="button" class="btn btn-sm btn-warning btn-payment me-1" data-id="${booking.id}" data-status="refunded">
                    <i class="fas fa-undo"></i> Rembourser
                </button>`;
        }
        
        if (booking.deposit_status === 'pending') {
            viewStatusActions.innerHTML += `
                <button type="button" class="btn btn-sm btn-success btn-deposit" data-id="${booking.id}" data-status="paid">
                    <i class="fas fa-money-bill"></i> Caution Payée
                </button>`;
        }
        
        if (booking.deposit_status === 'paid') {
            viewStatusActions.innerHTML += `
                <button type="button" class="btn btn-sm btn-warning btn-deposit" data-id="${booking.id}" data-status="refunded">
                    <i class="fas fa-undo"></i> Rembourser Caution
                </button>`;
        }
        
        // Set edit button data attribute
        viewEditBtn.setAttribute('data-id', booking.id);
    }
    
    /**
     * Handle edit booking
     */
    function handleEditBooking(bookingId) {
        resetForm();
        
        // Set ID and title
        document.getElementById('booking_id').value = bookingId;
        document.getElementById('bookingModalLabel').textContent = 'Modifier la Réservation';
        
        // Show modal with loading overlay
        const bsModal = new bootstrap.Modal(bookingModal);
        bsModal.show();
        
        const modalBody = document.querySelector('#bookingModal .modal-body');
        if (modalBody) {
            const loadingDiv = document.createElement('div');
            loadingDiv.id = 'loading-overlay';
            loadingDiv.className = 'loading-overlay';
            loadingDiv.innerHTML = '<div class="spinner-border text-primary"></div>';
            
            // Add loading overlay
            modalBody.appendChild(loadingDiv);
        }
        
        // Fetch booking data
        fetch(routes.showUrl.replace(':id', bookingId), {
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
            
            if (data.success && data.booking) {
                const booking = data.booking;
                
                // Set form fields
                document.getElementById('car_id').value = booking.car_id || '';
                document.getElementById('user_id').value = booking.user_id || '';
                document.getElementById('customer_name').value = booking.customer_name || '';
                document.getElementById('customer_email').value = booking.customer_email || '';
                document.getElementById('customer_phone').value = booking.customer_phone || '';
                document.getElementById('customer_id_number').value = booking.customer_id_number || '';
                document.getElementById('pickup_location').value = booking.pickup_location || '';
                document.getElementById('dropoff_location').value = booking.dropoff_location || '';
                document.getElementById('pickup_date').value = formatDateForInput(booking.pickup_date);
                document.getElementById('pickup_time').value = booking.pickup_time || '';
                document.getElementById('dropoff_date').value = formatDateForInput(booking.dropoff_date);
                document.getElementById('dropoff_time').value = booking.dropoff_time || '';
                document.getElementById('special_requests').value = booking.special_requests || '';
                document.getElementById('status').value = booking.status || '';
                document.getElementById('cancellation_reason').value = booking.cancellation_reason || '';
                
                // Show/hide cancellation reason field based on status
                if (booking.status === 'cancelled') {
                    document.getElementById('cancellation_reason_container').style.display = 'block';
                } else {
                    document.getElementById('cancellation_reason_container').style.display = 'none';
                }
                
                document.getElementById('payment_method').value = booking.payment_method || '';
                document.getElementById('payment_status').value = booking.payment_status || '';
                document.getElementById('transaction_id').value = booking.transaction_id || '';
                document.getElementById('notes').value = booking.notes || '';
                document.getElementById('language_preference').value = booking.language_preference || 'fr';
                
                // Set insurance plan
                if (document.getElementById('insurance_plan')) {
                    document.getElementById('insurance_plan').value = booking.insurance_plan || 'basic';
                }
                
                // Set additional features
                if (document.getElementById('additional_driver')) {
                    document.getElementById('additional_driver').checked = booking.additional_driver;
                    
                    // Handle additional driver fields
                    const fieldsContainer = document.getElementById('additional_driver_fields');
                    const nameField = document.getElementById('additional_driver_name');
                    const licenseField = document.getElementById('additional_driver_license');
                    
                    if (booking.additional_driver) {
                        fieldsContainer.classList.remove('d-none');
                        nameField.removeAttribute('disabled');
                        licenseField.removeAttribute('disabled');
                        nameField.value = booking.additional_driver_name || '';
                        licenseField.value = booking.additional_driver_license || '';
                    } else {
                        fieldsContainer.classList.add('d-none');
                        nameField.setAttribute('disabled', 'disabled');
                        licenseField.setAttribute('disabled', 'disabled');
                    }
                }
                
                if (document.getElementById('delivery_option')) {
                    document.getElementById('delivery_option').value = booking.delivery_option || 'none';
                    
                    // Handle delivery address field
                    const deliveryAddressField = document.getElementById('delivery_address');
                    if (booking.delivery_option === 'home' || booking.delivery_option === 'airport') {
                        deliveryAddressField.classList.remove('d-none');
                        deliveryAddressField.value = booking.delivery_address || '';
                    } else {
                        deliveryAddressField.classList.add('d-none');
                    }
                }
                
                if (document.getElementById('fuel_policy')) {
                    document.getElementById('fuel_policy').value = booking.fuel_policy || 'full-to-full';
                }
                
                if (document.getElementById('gps_enabled')) {
                    document.getElementById('gps_enabled').checked = booking.gps_enabled;
                }
                
                if (document.getElementById('child_seat')) {
                    document.getElementById('child_seat').checked = booking.child_seat;
                }
                
                // Set deposit fields
                document.getElementById('deposit_amount').value = booking.deposit_amount || '';
                document.getElementById('deposit_status').value = booking.deposit_status || '';
                
                // Set pricing fields
                document.getElementById('total_days').value = booking.total_days || '';
                document.getElementById('base_price').value = booking.base_price || '';
                document.getElementById('discount_amount').value = booking.discount_amount || '';
                document.getElementById('tax_amount').value = booking.tax_amount || '';
                document.getElementById('total_amount').value = booking.total_amount || '';
                
                // Check availability
                calculatePrices();
                
                // Handle location fields after modal is fully shown
                setTimeout(() => {
                    // Handle pickup location
                    handleLocationField('pickup_location', booking.pickup_location);
                    
                    // Handle dropoff location
                    handleLocationField('dropoff_location', booking.dropoff_location);
                }, 500);
                
            } else {
                const bsModal = bootstrap.Modal.getInstance(bookingModal);
                if (bsModal) bsModal.hide();
                Swal.fire('Erreur', 'Impossible de charger les données de la réservation', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            const bsModal = bootstrap.Modal.getInstance(bookingModal);
            if (bsModal) bsModal.hide();
            Swal.fire('Erreur', 'Impossible de charger les données de la réservation', 'error');
        });
    }
    
    /**
     * Handle location field (check if it's in the predefined list)
     */
    function handleLocationField(fieldId, value) {
        if (!value) return;
        
        const selectElement = document.getElementById(fieldId);
        const customInputElement = document.getElementById(fieldId + '_custom');
        
        if (!selectElement || !customInputElement) return;
        
        // Check if value exists in options
        let valueExists = false;
        for (let i = 0; i < selectElement.options.length; i++) {
            if (selectElement.options[i].value === value) {
                valueExists = true;
                break;
            }
        }
        
        if (!valueExists && value) {
            // Value is not in the predefined list, set to custom
            selectElement.value = 'custom';
            customInputElement.classList.remove('d-none');
            customInputElement.value = value;
            
            // Set the correct name attributes
            selectElement.setAttribute('name', fieldId + '_select');
            customInputElement.setAttribute('name', fieldId);
        } else {
            // Value is in the list
            selectElement.value = value;
        }
    }
    
    /**
     * Handle delete booking
     */
    function handleDeleteBooking(bookingId, bookingNumber) {
        Swal.fire({
            title: 'Confirmer la Suppression',
            html: `Êtes-vous sûr de vouloir supprimer la réservation <strong>${bookingNumber}</strong>?<br><br>
                  <span class="text-danger font-weight-bold">Cette action ne peut pas être annulée!</span>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Supprimer',
            cancelButtonText: 'Annuler',
            focusCancel: true
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Suppression...',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });
                
                fetch(routes.destroyUrl.replace(':id', bookingId), {
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
                        Swal.fire('Supprimée!', data.message || 'Réservation supprimée avec succès', 'success');
                    } else {
                        throw new Error(data.message || 'Échec de la suppression de la réservation');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire('Erreur', error.message || 'Échec de la suppression de la réservation', 'error');
                });
            }
        });
    }
    /**
     * Handle status change
     */
    function handleStatusChange(bookingId, status) {
        const statusDisplay = getStatusLabel(status);
        
        // For cancelled status, ask for reason
        if (status === 'cancelled') {
            Swal.fire({
                title: 'Raison d\'Annulation',
                input: 'textarea',
                inputLabel: 'Veuillez indiquer la raison de l\'annulation',
                inputPlaceholder: 'Entrez la raison...',
                inputAttributes: {
                    'required': 'required'
                },
                showCancelButton: true,
                confirmButtonText: 'Confirmer l\'Annulation',
                cancelButtonText: 'Annuler',
                inputValidator: (value) => {
                    if (!value || !value.trim()) {
                        return 'La raison d\'annulation est requise';
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const cancellationReason = result.value;
                    updateBookingStatus(bookingId, status, cancellationReason);
                }
            });
        } else {
            Swal.fire({
                title: `Confirmer le Changement de Statut`,
                html: `Êtes-vous sûr de vouloir changer le statut à <strong>${statusDisplay}</strong>?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Oui, Changer',
                cancelButtonText: 'Annuler',
            }).then((result) => {
                if (result.isConfirmed) {
                    updateBookingStatus(bookingId, status);
                }
            });
        }
    }
    
    /**
     * Update booking status
     */
    function updateBookingStatus(bookingId, status, cancellationReason = null) {
        Swal.fire({
            title: 'Mise à jour...',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });
        
        const formData = new FormData();
        formData.append('_method', 'PATCH');
        formData.append('status', status);
        
        if (cancellationReason) {
            formData.append('cancellation_reason', cancellationReason);
        }
        
        fetch(routes.updateStatusUrl.replace(':id', bookingId), {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Reload table
                table.ajax.reload();
                
                // Close any open modals
                const viewModalInstance = bootstrap.Modal.getInstance(viewBookingModal);
                if (viewModalInstance) viewModalInstance.hide();
                
                Swal.fire('Mis à jour!', data.message || 'Statut de réservation mis à jour avec succès', 'success');
            } else {
                throw new Error(data.message || 'Échec de la mise à jour du statut de réservation');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire('Erreur', error.message || 'Échec de la mise à jour du statut de réservation', 'error');
        });
    }
    
    /**
     * Handle payment status change
     */
    function handlePaymentStatusChange(bookingId, status) {
        const statusDisplay = getPaymentStatusLabel(status);
        
        // For paid status, ask for transaction ID
        let transactionId = '';
        
        if (status === 'paid') {
            Swal.fire({
                title: 'Entrez l\'ID de Transaction',
                input: 'text',
                inputLabel: 'ID de Transaction (optionnel)',
                inputPlaceholder: 'Entrez l\'ID de transaction',
                showCancelButton: true,
                confirmButtonText: 'Mettre à jour',
                cancelButtonText: 'Annuler'
            }).then((result) => {
                if (result.isConfirmed) {
                    transactionId = result.value;
                    updatePaymentStatus(bookingId, status, transactionId);
                }
            });
        } else {
            Swal.fire({
                title: `Confirmer le Changement de Statut de Paiement`,
                html: `Êtes-vous sûr de vouloir changer le statut de paiement à <strong>${statusDisplay}</strong>?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Oui, Changer',
                cancelButtonText: 'Annuler',
            }).then((result) => {
                if (result.isConfirmed) {
                    updatePaymentStatus(bookingId, status, '');
                }
            });
        }
    }
    
    /**
     * Update payment status
     */
    function updatePaymentStatus(bookingId, status, transactionId) {
        Swal.fire({
            title: 'Mise à jour...',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });
        
        const formData = new FormData();
        formData.append('_method', 'PATCH');
        formData.append('payment_status', status);
        formData.append('transaction_id', transactionId);
        
        fetch(routes.updatePaymentStatusUrl.replace(':id', bookingId), {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Reload table
                table.ajax.reload();
                
                // Close any open modals
                const viewModalInstance = bootstrap.Modal.getInstance(viewBookingModal);
                if (viewModalInstance) viewModalInstance.hide();
                
                Swal.fire('Mis à jour!', data.message || 'Statut de paiement mis à jour avec succès', 'success');
            } else {
                throw new Error(data.message || 'Échec de la mise à jour du statut de paiement');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire('Erreur', error.message || 'Échec de la mise à jour du statut de paiement', 'error');
        });
    }
    
    /**
     * Handle deposit status change
     */
    function handleDepositStatusChange(bookingId, status) {
        const statusDisplay = getDepositStatusLabel(status);
        
        Swal.fire({
            title: `Confirmer le Changement de Statut de Caution`,
            html: `Êtes-vous sûr de vouloir changer le statut de la caution à <strong>${statusDisplay}</strong>?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Oui, Changer',
            cancelButtonText: 'Annuler',
        }).then((result) => {
            if (result.isConfirmed) {
                updateDepositStatus(bookingId, status);
            }
        });
    }
    
    /**
     * Update deposit status
     */
    function updateDepositStatus(bookingId, status) {
        Swal.fire({
            title: 'Mise à jour...',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });
        
        const formData = new FormData();
        formData.append('_method', 'PATCH');
        formData.append('deposit_status', status);
        
        fetch(routes.updateDepositStatusUrl.replace(':id', bookingId), {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Reload table
                table.ajax.reload();
                
                // Close any open modals
                const viewModalInstance = bootstrap.Modal.getInstance(viewBookingModal);
                if (viewModalInstance) viewModalInstance.hide();
                
                Swal.fire('Mis à jour!', data.message || 'Statut de caution mis à jour avec succès', 'success');
            } else {
                throw new Error(data.message || 'Échec de la mise à jour du statut de caution');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire('Erreur', error.message || 'Échec de la mise à jour du statut de caution', 'error');
        });
    }
    
    /**
     * Handle start rental process
     * This displays the start rental modal and populates it with booking data
     */
    function handleStartRental(bookingId) {
        // Fetch booking data to populate the modal
        fetch(routes.showUrl.replace(':id', bookingId), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.booking) {
                const booking = data.booking;
                
                // Set booking details in the start rental modal
                document.getElementById('start_booking_id').value = booking.id;
                document.getElementById('start_booking_number').textContent = `Réservation #${booking.booking_number}`;
                document.getElementById('start_customer_name').textContent = booking.customer_name;
                document.getElementById('start_car_name').textContent = booking.car ? booking.car.name : 'N/A';
                document.getElementById('start_car_info').textContent = booking.car ? 
                    `${booking.car.license_plate || ''} - ${booking.car.color || ''} ${booking.car.model || ''}` : '';
                
                // Set expected mileage
                const mileageLimit = booking.mileage_limit || 200;
                const totalDays = booking.total_days || 1;
                document.getElementById('expected_mileage').textContent = mileageLimit * totalDays;
                
                // Pre-fill start mileage if car has current mileage
                if (booking.car && booking.car.mileage) {
                    document.getElementById('start_mileage').value = booking.car.mileage;
                } else {
                    document.getElementById('start_mileage').value = '';
                }
                
                // Set default fuel level to full
                document.getElementById('fuel_level').value = 100;
                
                // Clear notes field
                document.getElementById('start_notes').value = '';
                
                // Show the modal
                const bsModal = new bootstrap.Modal(document.getElementById('startRentalModal'));
                bsModal.show();
                
                // Close any other open modals
                const viewModalInstance = bootstrap.Modal.getInstance(viewBookingModal);
                if (viewModalInstance) viewModalInstance.hide();
                
            } else {
                Swal.fire('Erreur', 'Impossible de charger les données de la réservation', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire('Erreur', 'Impossible de charger les données de la réservation', 'error');
        });
    }
    
    /**
     * Save start rental data
     */
    if (document.getElementById('saveStartRentalBtn')) {
        document.getElementById('saveStartRentalBtn').addEventListener('click', function() {
            const formElement = document.getElementById('startRentalForm');
            
            // Basic form validation
            if (!formElement.checkValidity()) {
                formElement.reportValidity();
                return;
            }
            
            const bookingId = document.getElementById('start_booking_id').value;
            const startMileage = document.getElementById('start_mileage').value;
            const fuelLevel = document.getElementById('fuel_level').value;
            const notes = document.getElementById('start_notes').value;
            
            // Disable the button to prevent double submissions
            this.disabled = true;
            this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Traitement...';
            
            // Create form data for submission
            const formData = new FormData();
            formData.append('start_mileage', startMileage);
            formData.append('fuel_level', fuelLevel);
            formData.append('notes', notes);
            
            // Define route for start rental endpoint
            const startRentalUrl = `/admin/bookings/${bookingId}/start-rental`;
            
            // Submit the data
            fetch(startRentalUrl, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                // Re-enable the button
                this.disabled = false;
                this.innerHTML = 'Démarrer la Location';
                
                if (data.success) {
                    // Close the modal
                    const bsModal = bootstrap.Modal.getInstance(document.getElementById('startRentalModal'));
                    if (bsModal) bsModal.hide();
                    
                    // Show success message
                    Swal.fire({
                        icon: 'success',
                        title: 'Succès',
                        text: data.message || 'Location démarrée avec succès',
                        confirmButtonText: 'OK'
                    });
                    
                    // Reload datatable
                    table.ajax.reload();
                } else {
                    throw new Error(data.message || 'Échec du démarrage de la location');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                
                // Re-enable the button
                this.disabled = false;
                this.innerHTML = 'Démarrer la Location';
                
                // Show error message
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    text: error.message || 'Une erreur est survenue lors du démarrage de la location',
                    confirmButtonText: 'OK'
                });
            });
        });
    }
    /**
     * Handle complete rental process
     * This displays the complete rental modal and populates it with booking data
     */
    function handleCompleteRental(bookingId) {
        // Fetch booking data to populate the modal
        fetch(routes.showUrl.replace(':id', bookingId), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.booking) {
                const booking = data.booking;
                
                // Make sure booking is in progress and has start mileage
                if (booking.status !== 'in_progress') {
                    Swal.fire('Erreur', 'Seules les réservations en cours peuvent être terminées', 'error');
                    return;
                }
                
                if (!booking.start_mileage) {
                    Swal.fire('Erreur', 'Le kilométrage de départ n\'a pas été enregistré', 'error');
                    return;
                }
                
                // Set booking details in the complete rental modal
                document.getElementById('complete_booking_id').value = booking.id;
                document.getElementById('complete_booking_number').textContent = `Réservation #${booking.booking_number}`;
                document.getElementById('complete_customer_name').textContent = booking.customer_name;
                document.getElementById('complete_car_name').textContent = booking.car ? booking.car.name : 'N/A';
                document.getElementById('complete_car_info').textContent = booking.car ? 
                    `${booking.car.license_plate || ''} - ${booking.car.color || ''} ${booking.car.model || ''}` : '';
                
                // Set mileage information
                document.getElementById('starting_mileage').textContent = booking.start_mileage;
                document.getElementById('mileage_limit').textContent = booking.mileage_limit || 200;
                document.getElementById('total_mileage_limit').textContent = (booking.mileage_limit || 200) * booking.total_days;
                document.getElementById('extra_mileage_cost').textContent = booking.extra_mileage_cost || 2;
                
                // Set minimum end mileage value
                document.getElementById('end_mileage').min = booking.start_mileage;
                document.getElementById('end_mileage').value = booking.start_mileage;
                
                // Hide extra mileage info by default
                document.getElementById('extra_mileage_info').classList.add('d-none');
                
                // Add event listener for end mileage changes
                document.getElementById('end_mileage').addEventListener('input', function() {
                    updateExtraMileageCalculation(booking);
                });
                
                // Clear fields
                document.getElementById('complete_fuel_level').value = 100; // Default to full tank
                document.getElementById('damage_report').value = '';
                document.getElementById('complete_notes').value = '';
                
                // Show the modal
                const bsModal = new bootstrap.Modal(document.getElementById('completeRentalModal'));
                bsModal.show();
                
                // Close any other open modals
                const viewModalInstance = bootstrap.Modal.getInstance(viewBookingModal);
                if (viewModalInstance) viewModalInstance.hide();
                
            } else {
                Swal.fire('Erreur', 'Impossible de charger les données de la réservation', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire('Erreur', 'Impossible de charger les données de la réservation', 'error');
        });
    }
    
    /**
     * Calculate and update extra mileage information
     */
    function updateExtraMileageCalculation(booking) {
        const endMileageField = document.getElementById('end_mileage');
        const extraMileageInfo = document.getElementById('extra_mileage_info');
        const totalMileageElement = document.getElementById('total_mileage');
        const extraMileageElement = document.getElementById('extra_mileage');
        const extraMileageChargesElement = document.getElementById('extra_mileage_charges');
        
        if (!endMileageField.value || endMileageField.value < booking.start_mileage) {
            extraMileageInfo.classList.add('d-none');
            return;
        }
        
        // Calculate mileage values
        const endMileage = parseInt(endMileageField.value);
        const startMileage = parseInt(booking.start_mileage);
        const totalMileage = endMileage - startMileage;
        const mileageLimit = (booking.mileage_limit || 200) * booking.total_days;
        const extraMileage = Math.max(0, totalMileage - mileageLimit);
        const extraMileageCost = booking.extra_mileage_cost || 2;
        const extraMileageCharges = extraMileage * extraMileageCost;
        
        // Update display elements
        totalMileageElement.textContent = totalMileage;
        extraMileageElement.textContent = extraMileage;
        extraMileageChargesElement.textContent = extraMileageCharges.toFixed(2);
        
        // Show or hide the extra mileage info box
        if (extraMileage > 0) {
            extraMileageInfo.classList.remove('d-none');
        } else {
            extraMileageInfo.classList.add('d-none');
        }
    }
    
    /**
     * Save complete rental data
     */
    if (document.getElementById('saveCompleteRentalBtn')) {
        document.getElementById('saveCompleteRentalBtn').addEventListener('click', function() {
            const formElement = document.getElementById('completeRentalForm');
            
            // Basic form validation
            if (!formElement.checkValidity()) {
                formElement.reportValidity();
                return;
            }
            
            const bookingId = document.getElementById('complete_booking_id').value;
            const endMileage = document.getElementById('end_mileage').value;
            const fuelLevel = document.getElementById('complete_fuel_level').value;
            const damageReport = document.getElementById('damage_report').value;
            const notes = document.getElementById('complete_notes').value;
            
            // Disable the button to prevent double submissions
            this.disabled = true;
            this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Traitement...';
            
            // Create form data for submission
            const formData = new FormData();
            formData.append('end_mileage', endMileage);
            formData.append('fuel_level', fuelLevel);
            formData.append('damage_report', damageReport);
            formData.append('notes', notes);
            
            // Define route for complete rental endpoint
            const completeRentalUrl = `/admin/bookings/${bookingId}/complete-rental`;
            
            // Submit the data
            fetch(completeRentalUrl, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                // Re-enable the button
                this.disabled = false;
                this.innerHTML = 'Terminer la Location';
                
                if (data.success) {
                    // Close the modal
                    const bsModal = bootstrap.Modal.getInstance(document.getElementById('completeRentalModal'));
                    if (bsModal) bsModal.hide();
                    
                    let successMessage = data.message || 'Location terminée avec succès';
                    
                    // If there were extra mileage charges, add to the message
                    if (data.extra_mileage_charges && data.extra_mileage_charges > 0) {
                        successMessage += ` Des frais supplémentaires de ${data.extra_mileage_charges.toFixed(2)} MAD ont été ajoutés pour kilométrage excédentaire.`;
                    }
                    
                    // Show success message
                    Swal.fire({
                        icon: 'success',
                        title: 'Succès',
                        text: successMessage,
                        confirmButtonText: 'OK'
                    });
                    
                    // Reload datatable
                    table.ajax.reload();
                } else {
                    throw new Error(data.message || 'Échec de la terminaison de la location');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                
                // Re-enable the button
                this.disabled = false;
                this.innerHTML = 'Terminer la Location';
                
                // Show error message
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    text: error.message || 'Une erreur est survenue lors de la terminaison de la location',
                    confirmButtonText: 'OK'
                });
            });
        });
    }
    
    /**
     * Get status label
     */
    function getStatusLabel(status) {
        const statusLabels = {
            'pending': 'En attente',
            'confirmed': 'Confirmée',
            'in_progress': 'En cours',
            'completed': 'Terminée',
            'cancelled': 'Annulée',
            'no_show': 'Non présenté'
        };
        return statusLabels[status] || capitalize(status);
    }
    
    /**
     * Get payment status label
     */
    function getPaymentStatusLabel(status) {
        const paymentLabels = {
            'paid': 'Payé',
            'unpaid': 'Non payé',
            'pending': 'En attente',
            'refunded': 'Remboursé'
        };
        return paymentLabels[status] || capitalize(status);
    }
    
    /**
     * Get payment method label
     */
    function getPaymentMethodLabel(method) {
        const methodLabels = {
            'cash': 'Espèces',
            'card': 'Carte bancaire',
            'bank_transfer': 'Virement bancaire',
            'mobile_payment': 'Paiement mobile',
            'cash_on_delivery': 'Paiement à la livraison',
            'credit_card': 'Carte de crédit',
            'paypal': 'PayPal'
        };
        return methodLabels[method] || method.replace(/_/g, ' ');
    }
    /**
     * Get deposit status label
     */
    function getDepositStatusLabel(status) {
        const depositLabels = {
            'pending': 'En attente',
            'paid': 'Payée',
            'refunded': 'Remboursée',
            'forfeited': 'Perdue'
        };
        return depositLabels[status] || capitalize(status);
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
        if (bookingForm) {
            bookingForm.reset();
            document.getElementById('booking_id').value = '';
        }
        
        // Reset custom fields visibility
        if (document.getElementById('additional_driver_fields')) {
            document.getElementById('additional_driver_fields').classList.add('d-none');
            document.getElementById('additional_driver_name').setAttribute('disabled', 'disabled');
            document.getElementById('additional_driver_license').setAttribute('disabled', 'disabled');
        }
        
        if (document.getElementById('delivery_address')) {
            document.getElementById('delivery_address').classList.add('d-none');
        }
        
        if (document.getElementById('pickup_location_custom')) {
            document.getElementById('pickup_location_custom').classList.add('d-none');
        }
        
        if (document.getElementById('dropoff_location_custom')) {
            document.getElementById('dropoff_location_custom').classList.add('d-none');
        }
        
        // Reset cancellation reason
        if (document.getElementById('cancellation_reason_container')) {
            document.getElementById('cancellation_reason_container').style.display = 'none';
        }
        
        clearValidationErrors();
        document.getElementById('availability_display').innerHTML = '<span class="badge bg-secondary">Aucun véhicule sélectionné</span>';
    }
    
    /**
     * Format date for display
     */
    function formatDate(dateStr) {
        if (!dateStr) return 'N/A';
        
        const date = new Date(dateStr);
        return date.toLocaleDateString('fr-FR', {
            day: '2-digit', 
            month: '2-digit', 
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }
    
    /**
     * Format date for input fields (YYYY-MM-DD)
     */
    function formatDateForInput(dateStr) {
        if (!dateStr) return '';
        
        const date = new Date(dateStr);
        return date.toISOString().split('T')[0];
    }
    
    /**
     * Capitalize first letter of each word
     */
    function capitalize(str) {
        if (!str) return '';
        
        return str.replace(/\b\w/g, l => l.toUpperCase()).replace(/_/g, ' ');
    }
    
    /**
     * Initialize routes object with rental management endpoints
     */
    if (routes) {
        // Add start and complete rental routes
        routes.startRentalUrl = "/admin/bookings/:id/start-rental";
        routes.completeRentalUrl = "/admin/bookings/:id/complete-rental";
        routes.calculateMileageChargesUrl = "/admin/bookings/:id/calculate-mileage-charges";
    }
});