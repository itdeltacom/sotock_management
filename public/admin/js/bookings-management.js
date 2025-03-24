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
    
    // Customer fields
    const userIdSelect = document.getElementById('user_id');
    const customerNameInput = document.getElementById('customer_name');
    const customerEmailInput = document.getElementById('customer_email');
    const customerPhoneInput = document.getElementById('customer_phone');
    
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
        responsive: true
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
            
            // Update modal title
            document.getElementById('bookingModalLabel').textContent = 'Add New Booking';
            
            // Show modal using Bootstrap 5 modal
            const bsModal = new bootstrap.Modal(bookingModal);
            bsModal.show();
        });
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
            const bookingNumber = button.getAttribute('data-number') || 'this booking';
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
        const carId = carIdSelect.value;
        const pickupDate = pickupDateInput.value;
        const dropoffDate = dropoffDateInput.value;
        const bookingId = document.getElementById('booking_id').value;
        
        if (!carId || !pickupDate || !dropoffDate) {
            resetPricing();
            return;
        }
        
        // Validate dates
        if (new Date(pickupDate) > new Date(dropoffDate)) {
            document.getElementById('availability_display').innerHTML = `
                <span class="badge bg-danger">Invalid Dates</span>
            `;
            resetPricing();
            return;
        }
        
        // Show loading state
        document.getElementById('availability_display').innerHTML = `
            <span class="badge bg-secondary">
                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                Checking availability...
            </span>
        `;
        
        const formData = new FormData();
        formData.append('car_id', carId);
        formData.append('pickup_date', pickupDate);
        formData.append('dropoff_date', dropoffDate);
        
        if (bookingId) {
            formData.append('booking_id', bookingId);
        }
        
        fetch(routes.calculateUrl, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update availability display
                const isAvailable = data.data.is_available;
                document.getElementById('availability_display').innerHTML = isAvailable
                    ? '<span class="badge bg-success">Car Available</span>'
                    : '<span class="badge bg-danger">Car Unavailable</span>';
                
                // Update pricing fields
                document.getElementById('total_days').value = data.data.total_days;
                document.getElementById('base_price').value = data.data.base_price.toFixed(2);
                document.getElementById('discount_amount').value = data.data.discount_amount.toFixed(2);
                document.getElementById('tax_amount').value = data.data.tax_amount.toFixed(2);
                document.getElementById('total_amount').value = data.data.total_amount.toFixed(2);
            } else {
                document.getElementById('availability_display').innerHTML = `
                    <span class="badge bg-warning">Error checking availability</span>
                `;
                resetPricing();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('availability_display').innerHTML = `
                <span class="badge bg-danger">Error</span>
            `;
            resetPricing();
        });
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
                const bsModal = bootstrap.Modal.getInstance(bookingModal);
                if (bsModal) bsModal.hide();
                
                // Reload table
                table.ajax.reload();
                
                // Show success message
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: data.message || 'Booking saved successfully',
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
                saveBtn.innerHTML = 'Save Booking';
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
                Swal.fire('Error', 'Failed to load booking data', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            const bsModal = bootstrap.Modal.getInstance(viewBookingModal);
            if (bsModal) bsModal.hide();
            Swal.fire('Error', 'Failed to load booking data', 'error');
        });
    }
    
    // Add this JavaScript to your existing script section or bookings-management.js file

document.addEventListener('DOMContentLoaded', function() {
    // Initialize Select2 for location dropdowns
    $('.location-select').select2({
        theme: 'bootstrap-5',
        width: '100%',
        minimumInputLength: 3,
        language: {
            inputTooShort: function() {
                return 'Veuillez saisir au moins 3 caractères';
            },
            noResults: function() {
                return 'Aucun résultat trouvé';
            },
            searching: function() {
                return 'Recherche en cours...';
            }
        }
    });

    // Handle custom location inputs
    $('#pickup_location').on('change', function() {
        if ($(this).val() === 'custom') {
            $('#pickup_location_custom').removeClass('d-none').prop('required', true);
            $('#pickup_location').attr('name', 'pickup_location_select');
            $('#pickup_location_custom').attr('name', 'pickup_location');
        } else {
            $('#pickup_location_custom').addClass('d-none').prop('required', false);
            $('#pickup_location').attr('name', 'pickup_location');
            $('#pickup_location_custom').attr('name', 'pickup_location_custom');
        }
    });

    $('#dropoff_location').on('change', function() {
        if ($(this).val() === 'custom') {
            $('#dropoff_location_custom').removeClass('d-none').prop('required', true);
            $('#dropoff_location').attr('name', 'dropoff_location_select');
            $('#dropoff_location_custom').attr('name', 'dropoff_location');
        } else {
            $('#dropoff_location_custom').addClass('d-none').prop('required', false);
            $('#dropoff_location').attr('name', 'dropoff_location');
            $('#dropoff_location_custom').attr('name', 'dropoff_location_custom');
        }
    });

    // Modify the original handleEditBooking function to handle location fields
    const originalHandleEditBooking = window.handleEditBooking;
    if (typeof originalHandleEditBooking === 'function') {
        window.handleEditBooking = function(bookingId) {
            originalHandleEditBooking(bookingId);

            // Add additional code to set location fields after modal is shown
            $('#bookingModal').on('shown.bs.modal', function() {
                const pickupLocation = $('#pickup_location').val();
                const dropoffLocation = $('#dropoff_location').val();
                
                // Check if locations are in the predefined list
                const pickupOption = $(`#pickup_location option[value="${pickupLocation}"]`);
                const dropoffOption = $(`#dropoff_location option[value="${dropoffLocation}"]`);
                
                if (pickupOption.length === 0 && pickupLocation) {
                    // Set to custom and populate the custom field
                    $('#pickup_location').val('custom').trigger('change');
                    $('#pickup_location_custom').val(pickupLocation);
                } else {
                    $('#pickup_location').val(pickupLocation).trigger('change');
                }
                
                if (dropoffOption.length === 0 && dropoffLocation) {
                    // Set to custom and populate the custom field
                    $('#dropoff_location').val('custom').trigger('change');
                    $('#dropoff_location_custom').val(dropoffLocation);
                } else {
                    $('#dropoff_location').val(dropoffLocation).trigger('change');
                }
            });
        };
    }

    // Override the form submission to handle custom locations
    const originalHandleFormSubmit = window.handleFormSubmit;
    if (typeof originalHandleFormSubmit === 'function') {
        window.handleFormSubmit = function(e) {
            e.preventDefault();
            
            // Handle custom locations before submitting
            if ($('#pickup_location').val() === 'custom') {
                const customPickup = $('#pickup_location_custom').val();
                $('#pickup_location').val(customPickup);
            }
            
            if ($('#dropoff_location').val() === 'custom') {
                const customDropoff = $('#dropoff_location_custom').val();
                $('#dropoff_location').val(customDropoff);
            }
            
            // Call the original function
            originalHandleFormSubmit.call(this, e);
        };
    }
});

    /**
     * Display booking details in the view modal
     */
    function displayBookingDetails(booking) {
        // Set booking number and status badges
        document.getElementById('view-booking-number').textContent = `Booking #${booking.booking_number}`;
        
        // Set status badge
        const statusClasses = {
            'pending': 'bg-warning',
            'confirmed': 'bg-success',
            'completed': 'bg-info',
            'cancelled': 'bg-danger'
        };
        const statusClass = statusClasses[booking.status] || 'bg-secondary';
        document.getElementById('view-status-badge').innerHTML = `
            <span class="badge ${statusClass}">${capitalize(booking.status)}</span>
        `;
        
        // Set payment badge
        const paymentClasses = {
            'paid': 'bg-success',
            'unpaid': 'bg-danger',
            'pending': 'bg-warning',
            'refunded': 'bg-info'
        };
        const paymentClass = paymentClasses[booking.payment_status] || 'bg-secondary';
        document.getElementById('view-payment-badge').innerHTML = `
            <span class="badge ${paymentClass}">${capitalize(booking.payment_status)}</span>
        `;
        
        // Set dates
        document.getElementById('view-created-at').textContent = formatDate(booking.created_at);
        document.getElementById('view-updated-at').textContent = formatDate(booking.updated_at);
        
        // Set car information
        document.getElementById('view-car-name').textContent = booking.car ? booking.car.name : 'N/A';
        document.getElementById('view-car-details').textContent = booking.car ? 
            `Price: $${booking.car.price_per_day}/day, Discount: ${booking.car.discount_percentage}%` : '';
        
        // Set customer information
        document.getElementById('view-customer-name').textContent = booking.customer_name;
        document.getElementById('view-customer-email').textContent = booking.customer_email;
        document.getElementById('view-customer-phone').textContent = booking.customer_phone || 'Not provided';
        document.getElementById('view-customer-account').innerHTML = booking.user ? 
            `<span class="badge bg-info">Registered User</span>` : 
            `<span class="badge bg-secondary">Guest</span>`;
        
        // Set rental details
        document.getElementById('view-pickup-details').innerHTML = `
            ${formatDate(booking.pickup_date)} at ${booking.pickup_time}<br>
            <small class="text-muted">${booking.pickup_location}</small>
        `;
        document.getElementById('view-dropoff-details').innerHTML = `
            ${formatDate(booking.dropoff_date)} at ${booking.dropoff_time}<br>
            <small class="text-muted">${booking.dropoff_location}</small>
        `;
        document.getElementById('view-duration').textContent = `${booking.total_days} days`;
        
        // Set payment information
        document.getElementById('view-base-price').textContent = `$${parseFloat(booking.base_price).toFixed(2)}`;
        document.getElementById('view-discount').textContent = `$${parseFloat(booking.discount_amount).toFixed(2)}`;
        document.getElementById('view-tax').textContent = `$${parseFloat(booking.tax_amount).toFixed(2)}`;
        document.getElementById('view-total').textContent = `$${parseFloat(booking.total_amount).toFixed(2)}`;
        document.getElementById('view-payment-method').textContent = capitalize(booking.payment_method.replace(/_/g, ' '));
        document.getElementById('view-transaction-id').textContent = booking.transaction_id || 'N/A';
        
        // Set special requests
        document.getElementById('view-special-requests').textContent = booking.special_requests || 'No special requests';
        
        // Set status actions
        const viewStatusActions = document.getElementById('view-status-actions');
        viewStatusActions.innerHTML = '';
        
        if (booking.status === 'pending') {
            viewStatusActions.innerHTML += `
                <button type="button" class="btn btn-sm btn-success btn-status" data-id="${booking.id}" data-status="confirmed">
                    <i class="fas fa-check"></i> Confirm
                </button> `;
        }
        
        if (booking.status === 'confirmed') {
            viewStatusActions.innerHTML += `
                <button type="button" class="btn btn-sm btn-info btn-status" data-id="${booking.id}" data-status="completed">
                    <i class="fas fa-flag-checkered"></i> Complete
                </button> `;
        }
        
        if (booking.status !== 'cancelled' && booking.status !== 'completed') {
            viewStatusActions.innerHTML += `
                <button type="button" class="btn btn-sm btn-danger btn-status" data-id="${booking.id}" data-status="cancelled">
                    <i class="fas fa-ban"></i> Cancel
                </button> `;
        }
        
        if (booking.payment_status === 'unpaid' || booking.payment_status === 'pending') {
            viewStatusActions.innerHTML += `
                <button type="button" class="btn btn-sm btn-success btn-payment" data-id="${booking.id}" data-status="paid">
                    <i class="fas fa-check"></i> Mark Paid
                </button> `;
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
        document.getElementById('bookingModalLabel').textContent = 'Edit Booking';
        
        // Show modal with loading overlay
        const bsModal = new bootstrap.Modal(bookingModal);
        bsModal.show();
        
        const modalBody = document.querySelector('#bookingModal .modal-body');
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
                document.getElementById('pickup_location').value = booking.pickup_location || '';
                document.getElementById('dropoff_location').value = booking.dropoff_location || '';
                document.getElementById('pickup_date').value = formatDateForInput(booking.pickup_date);
                document.getElementById('pickup_time').value = booking.pickup_time || '';
                document.getElementById('dropoff_date').value = formatDateForInput(booking.dropoff_date);
                document.getElementById('dropoff_time').value = booking.dropoff_time || '';
                document.getElementById('special_requests').value = booking.special_requests || '';
                document.getElementById('status').value = booking.status || '';
                document.getElementById('payment_method').value = booking.payment_method || '';
                document.getElementById('payment_status').value = booking.payment_status || '';
                document.getElementById('transaction_id').value = booking.transaction_id || '';
                
                // Set pricing fields
                document.getElementById('total_days').value = booking.total_days || '';
                document.getElementById('base_price').value = booking.base_price || '';
                document.getElementById('discount_amount').value = booking.discount_amount || '';
                document.getElementById('tax_amount').value = booking.tax_amount || '';
                document.getElementById('total_amount').value = booking.total_amount || '';
                
                // Check availability
                calculatePrices();
            } else {
                const bsModal = bootstrap.Modal.getInstance(bookingModal);
                if (bsModal) bsModal.hide();
                Swal.fire('Error', 'Failed to load booking data', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            const bsModal = bootstrap.Modal.getInstance(bookingModal);
            if (bsModal) bsModal.hide();
            Swal.fire('Error', 'Failed to load booking data', 'error');
        });
    }
    
    /**
     * Handle delete booking
     */
    function handleDeleteBooking(bookingId, bookingNumber) {
        Swal.fire({
            title: 'Confirm Delete',
            html: `Are you sure you want to delete booking <strong>${bookingNumber}</strong>?<br><br>
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
                        Swal.fire('Deleted!', data.message || 'Booking deleted successfully', 'success');
                    } else {
                        throw new Error(data.message || 'Failed to delete booking');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire('Error', error.message || 'Failed to delete booking', 'error');
                });
            }
        });
    }
    
    /**
     * Handle status change
     */
    function handleStatusChange(bookingId, status) {
        const statusDisplay = capitalize(status);
        
        Swal.fire({
            title: `Confirm Status Change`,
            html: `Are you sure you want to change the status to <strong>${statusDisplay}</strong>?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, Change',
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Updating...',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });
                
                const formData = new FormData();
                formData.append('_method', 'PATCH');
                formData.append('status', status);
                
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
                        
                        Swal.fire('Updated!', data.message || 'Booking status updated successfully', 'success');
                    } else {
                        throw new Error(data.message || 'Failed to update booking status');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire('Error', error.message || 'Failed to update booking status', 'error');
                });
            }
        });
    }
    
    /**
     * Handle payment status change
     */
    function handlePaymentStatusChange(bookingId, status) {
        const statusDisplay = capitalize(status);
        
        // For paid status, ask for transaction ID
        let transactionId = '';
        
        if (status === 'paid') {
            Swal.fire({
                title: 'Enter Transaction ID',
                input: 'text',
                inputLabel: 'Transaction ID (optional)',
                inputPlaceholder: 'Enter transaction ID',
                showCancelButton: true,
                confirmButtonText: 'Update Payment',
                cancelButtonText: 'Cancel',
                inputValidator: (value) => {
                    // Allow empty transaction ID
                    return null;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    transactionId = result.value;
                    updatePaymentStatus(bookingId, status, transactionId);
                }
            });
        } else {
            Swal.fire({
                title: `Confirm Payment Status Change`,
                html: `Are you sure you want to change the payment status to <strong>${statusDisplay}</strong>?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Change',
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
            title: 'Updating...',
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
                
                Swal.fire('Updated!', data.message || 'Payment status updated successfully', 'success');
            } else {
                throw new Error(data.message || 'Failed to update payment status');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire('Error', error.message || 'Failed to update payment status', 'error');
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
        if (bookingForm) {
            bookingForm.reset();
            document.getElementById('booking_id').value = '';
        }
        
        clearValidationErrors();
        document.getElementById('availability_display').innerHTML = '<span class="badge bg-secondary">No car selected</span>';
    }
    
    /**
     * Format date for display
     */
    function formatDate(dateStr) {
        if (!dateStr) return 'N/A';
        
        const date = new Date(dateStr);
        return date.toLocaleString();
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
        
        return str.replace(/\b\w/g, l => l.toUpperCase());
    }
});