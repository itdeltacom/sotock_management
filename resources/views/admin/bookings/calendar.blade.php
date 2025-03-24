@extends('admin.layouts.master')

@section('title', 'Booking Calendar')

@section('content')
    <div class="page-header">
        <h3 class="page-title">Booking Calendar</h3>
        <div class="page-actions">
            <button type="button" id="createBookingFromCalendar" class="btn btn-primary me-2">
                    <i class="fas fa-plus"></i> Add New Booking
                </button>
                <a href="{{ route('admin.bookings.index') }}" class="btn btn-secondary">
                    <i class="fas fa-list"></i> List View
                </a>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title">Calendar View</h5>
                <div class="header-actions">
                    <button type="button" id="prevBtn" class="btn btn-sm btn-light me-2">
                        <i class="fas fa-chevron-left"></i> Previous
                    </button>
                    <button type="button" id="todayBtn" class="btn btn-sm btn-primary me-2">
                        Today
                    </button>
                    <button type="button" id="nextBtn" class="btn btn-sm btn-light me-3">
                        Next <i class="fas fa-chevron-right"></i>
                    </button>
                    <div class="btn-group">
                        <button type="button" id="monthViewBtn" class="btn btn-sm btn-outline-secondary active">Month</button>
                        <button type="button" id="weekViewBtn" class="btn btn-sm btn-outline-secondary">Week</button>
                        <button type="button" id="dayViewBtn" class="btn btn-sm btn-outline-secondary">Day</button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="filterCar" class="form-label">Filter by Car:</label>
                            <select id="filterCar" class="form-select">
                                <option value="">All Cars</option>
                                @foreach(\App\Models\Car::orderBy('name')->get() as $car)
                                    <option value="{{ $car->id }}">{{ $car->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="filterStatus" class="form-label">Filter by Status:</label>
                            <select id="filterStatus" class="form-select">
                                <option value="">All Statuses</option>
                                <option value="pending">Pending</option>
                                <option value="confirmed">Confirmed</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="filterPayment" class="form-label">Filter by Payment:</label>
                            <select id="filterPayment" class="form-select">
                                <option value="">All Payment Statuses</option>
                                <option value="paid">Paid</option>
                                <option value="unpaid">Unpaid</option>
                                <option value="pending">Pending</option>
                                <option value="refunded">Refunded</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div id="calendarTitle" class="h4 text-center my-4">March 2025</div>

                <div id="calendar-container">
                    <!-- Calendar will be rendered here -->
                    <div id="calendar"></div>
                </div>
            </div>
        </div>

        <!-- Booking Details Modal -->
        <div class="modal fade" id="bookingDetailsModal" tabindex="-1" aria-labelledby="bookingDetailsModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="bookingDetailsModalLabel">Booking Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="text-center" id="bookingDetailLoader">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                        <div id="bookingDetailContent" style="display: none;">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <h5 id="booking-number" class="mb-2"></h5>
                                    <div id="booking-status" class="mb-2"></div>
                                    <p id="booking-dates" class="mb-1"></p>
                                    <p id="booking-customer" class="mb-0"></p>
                                </div>
                                <div class="col-md-6">
                                    <h5 id="car-name" class="mb-2"></h5>
                                    <p id="booking-locations" class="mb-1"></p>
                                    <p id="booking-amount" class="mb-0"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" id="viewEditBtn" class="btn btn-primary">Edit Booking</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Import the booking modal from the index page -->
        @include('admin.bookings._booking_modal', ['cars' => \App\Models\Car::where('is_available', true)->orderBy('name')->get()])
@endsection

@push('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css">
    <style>
        #calendar {
            height: 800px;
        }

        .fc-event {
            cursor: pointer;
        }

        .fc-day-today {
            background-color: rgba(var(--bs-primary-rgb), 0.1) !important;
        }

        .booking-pending {
            background-color: var(--bs-warning);
            border-color: var(--bs-warning);
        }

        .booking-confirmed {
            background-color: var(--bs-success);
            border-color: var(--bs-success);
        }

        .booking-completed {
            background-color: var(--bs-info);
            border-color: var(--bs-info);
        }

        .booking-cancelled {
            background-color: var(--bs-danger);
            border-color: var(--bs-danger);
            text-decoration: line-through;
        }

        #availability_display .badge {
            font-size: 1rem;
            padding: 0.5rem 1rem;
        }
    </style>
@endpush

@push('js')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <script>
        // Routes for AJAX calls
        const routes = {
            dataUrl: "{{ route('admin.bookings.data') }}",
            storeUrl: "{{ route('admin.bookings.store') }}",
            showUrl: "{{ route('admin.bookings.show', ':id') }}",
            updateUrl: "{{ route('admin.bookings.update', ':id') }}",
            destroyUrl: "{{ route('admin.bookings.destroy', ':id') }}",
            calculateUrl: "{{ route('admin.bookings.calculate-prices') }}",
            updateStatusUrl: "{{ route('admin.bookings.update-status', ':id') }}",
            updatePaymentStatusUrl: "{{ route('admin.bookings.update-payment-status', ':id') }}",
            exportUrl: "{{ route('admin.bookings.export') }}"
        };

        // CSRF Token
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    </script>

    <!-- Include the bookings management JS -->
    <script src="{{ asset('admin/js/bookings-management.js') }}"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Create Booking Button on Calendar Page
            const createBookingFromCalendarBtn = document.getElementById('createBookingFromCalendar');
            if (createBookingFromCalendarBtn) {
                createBookingFromCalendarBtn.addEventListener('click', function() {
                    // Call the existing resetForm function from bookings-management.js
                    resetForm();

                    // Set default dates
                    const today = new Date().toISOString().split('T')[0];
                    const tomorrow = new Date();
                    tomorrow.setDate(tomorrow.getDate() + 1);
                    const tomorrowStr = tomorrow.toISOString().split('T')[0];

                    document.getElementById('pickup_date').value = today;
                    document.getElementById('dropoff_date').value = tomorrowStr;

                    // Set default times
                    document.getElementById('pickup_time').value = '10:00';
                    document.getElementById('dropoff_time').value = '10:00';

                    // Update modal title
                    document.getElementById('bookingModalLabel').textContent = 'Add New Booking';

                    // Show modal using Bootstrap 5 modal
                    const bsModal = new bootstrap.Modal(document.getElementById('bookingModal'));
                    bsModal.show();
                });
            }

            // Handle edit from the details modal
            const viewEditBtn = document.getElementById('viewEditBtn');
            if (viewEditBtn) {
                viewEditBtn.addEventListener('click', function() {
                    const bookingId = this.getAttribute('data-id');
                    if (bookingId) {
                        // Close details modal
                        const detailsModal = bootstrap.Modal.getInstance(document.getElementById('bookingDetailsModal'));
                        detailsModal.hide();

                        // Wait for the modal to close then open edit modal
                        setTimeout(() => {
                            // Use the existing handleEditBooking function from bookings-management.js
                            handleEditBooking(bookingId);
                        }, 500);
                    }
                    return false; // Prevent default behavior
                });
            }

            // Initialize FullCalendar
            let calendarEl = document.getElementById('calendar');
            let currentView = 'dayGridMonth';
            let calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: currentView,
                headerToolbar: false, // We're using our own header
                height: 'auto',
                events: function (info, successCallback, failureCallback) {
                    // Get filter values
                    const carId = document.getElementById('filterCar').value;
                    const status = document.getElementById('filterStatus').value;
                    const paymentStatus = document.getElementById('filterPayment').value;

                    // Fetch events from the server
                    fetch(`{{ route('admin.bookings.data') }}?start=${info.startStr}&end=${info.endStr}&car_id=${carId}&status=${status}&payment_status=${paymentStatus}`)
                        .then(response => response.json())
                        .then(data => {
                            // Transform bookings to calendar events
                            const events = data.data.map(booking => {
                                return {
                                    id: booking.id,
                                    title: `${booking.car_name} - ${booking.customer_name}`,
                                    start: `${booking.pickup_date}T${booking.pickup_time}`,
                                    end: `${booking.dropoff_date}T${booking.dropoff_time}`,
                                    className: `booking-${booking.status}`,
                                    extendedProps: {
                                        bookingNumber: booking.booking_number,
                                        status: booking.status,
                                        paymentStatus: booking.payment_status,
                                        customerName: booking.customer_name,
                                        customerEmail: booking.customer_email,
                                        carName: booking.car_name,
                                        pickupLocation: booking.pickup_location,
                                        dropoffLocation: booking.dropoff_location,
                                        totalAmount: booking.total_amount
                                    }
                                };
                            });
                            successCallback(events);
                        })
                        .catch(error => {
                            console.error('Error fetching booking data:', error);
                            failureCallback(error);
                        });
                },
                eventClick: function (info) {
                    // Show booking details modal
                    showBookingDetails(info.event);
                },
                dateClick: function(info) {
                    // When a date is clicked - open create booking modal with that date
                    resetForm();

                    const clickedDate = info.dateStr;
                    const nextDay = new Date(clickedDate);
                    nextDay.setDate(nextDay.getDate() + 1);
                    const nextDayStr = nextDay.toISOString().split('T')[0];

                    document.getElementById('pickup_date').value = clickedDate;
                    document.getElementById('dropoff_date').value = nextDayStr;

                    // Set default times
                    document.getElementById('pickup_time').value = '10:00';
                    document.getElementById('dropoff_time').value = '10:00';

                    // Update modal title
                    document.getElementById('bookingModalLabel').textContent = 'Add New Booking';

                    // Show modal using Bootstrap 5 modal
                    const bsModal = new bootstrap.Modal(document.getElementById('bookingModal'));
                    bsModal.show();
                },
                eventTimeFormat: {
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: true
                },
                datesSet: function (dateInfo) {
                    // Update the calendar title
                    updateCalendarTitle(dateInfo.view);
                }
            });

            calendar.render();

            // Make calendar instance available globally for updates after booking changes
            window.calendar = calendar;

            // Update initial calendar title
            updateCalendarTitle(calendar.view);

            // Handle navigation buttons
            document.getElementById('prevBtn').addEventListener('click', function () {
                calendar.prev();
            });

            document.getElementById('todayBtn').addEventListener('click', function () {
                calendar.today();
            });

            document.getElementById('nextBtn').addEventListener('click', function () {
                calendar.next();
            });

            // Handle view change buttons
            document.getElementById('monthViewBtn').addEventListener('click', function () {
                setActiveViewButton('monthViewBtn');
                calendar.changeView('dayGridMonth');
                currentView = 'dayGridMonth';
                updateCalendarTitle(calendar.view);
            });

            document.getElementById('weekViewBtn').addEventListener('click', function () {
                setActiveViewButton('weekViewBtn');
                calendar.changeView('timeGridWeek');
                currentView = 'timeGridWeek';
                updateCalendarTitle(calendar.view);
            });

            document.getElementById('dayViewBtn').addEventListener('click', function () {
                setActiveViewButton('dayViewBtn');
                calendar.changeView('timeGridDay');
                currentView = 'timeGridDay';
                updateCalendarTitle(calendar.view);
            });

            // Handle filter changes
            document.getElementById('filterCar').addEventListener('change', function () {
                calendar.refetchEvents();
            });

            document.getElementById('filterStatus').addEventListener('change', function () {
                calendar.refetchEvents();
            });

            document.getElementById('filterPayment').addEventListener('change', function () {
                calendar.refetchEvents();
            });

            // Helper functions
            function setActiveViewButton(activeButtonId) {
                document.querySelectorAll('.btn-group .btn').forEach(button => {
                    button.classList.remove('active');
                });
                document.getElementById(activeButtonId).classList.add('active');
            }

            function updateCalendarTitle(view) {
                const titleEl = document.getElementById('calendarTitle');
                let title = '';

                const date = view.currentStart;
                const month = date.toLocaleString('default', { month: 'long' });
                const year = date.getFullYear();

                if (currentView === 'dayGridMonth') {
                    title = `${month} ${year}`;
                } else if (currentView === 'timeGridWeek') {
                    const endDate = new Date(view.currentEnd);
                    endDate.setDate(endDate.getDate() - 1); // FullCalendar's end date is exclusive
                    title = `${formatDate(date)} - ${formatDate(endDate)}`;
                } else if (currentView === 'timeGridDay') {
                    title = formatDate(date);
                }

                titleEl.textContent = title;
            }

            function formatDate(date) {
                return date.toLocaleDateString('en-US', {
                    month: 'short',
                    day: 'numeric',
                    year: 'numeric'
                });
            }

            function showBookingDetails(event) {
                // Get the modal elements
                const modal = new bootstrap.Modal(document.getElementById('bookingDetailsModal'));
                const loader = document.getElementById('bookingDetailLoader');
                const content = document.getElementById('bookingDetailContent');
                const editBtn = document.getElementById('viewEditBtn');

                // Show loader, hide content
                loader.style.display = 'block';
                content.style.display = 'none';

                // Show the modal
                modal.show();

                // Get booking data from event
                const props = event.extendedProps;

                // Set modal content
                document.getElementById('booking-number').textContent = `Booking #${props.bookingNumber}`;

                // Set status badge
                const statusClasses = {
                    'pending': 'bg-warning',
                    'confirmed': 'bg-success',
                    'completed': 'bg-info',
                    'cancelled': 'bg-danger'
                };
                const statusClass = statusClasses[props.status] || 'bg-secondary';
                document.getElementById('booking-status').innerHTML = `
                        <span class="badge ${statusClass}">${props.status.charAt(0).toUpperCase() + props.status.slice(1)}</span>
                        <span class="badge ${props.paymentStatus === 'paid' ? 'bg-success' : 'bg-danger'} ms-2">
                            ${props.paymentStatus.charAt(0).toUpperCase() + props.paymentStatus.slice(1)}
                        </span>
                    `;

                // Set dates
                const startDate = event.start ? event.start.toLocaleString() : 'N/A';
                const endDate = event.end ? event.end.toLocaleString() : 'N/A';
                document.getElementById('booking-dates').innerHTML = `
                        <strong>Pickup:</strong> ${startDate}<br>
                        <strong>Dropoff:</strong> ${endDate}
                    `;

                // Set customer info
                document.getElementById('booking-customer').innerHTML = `
                        <strong>Customer:</strong> ${props.customerName}<br>
                        <strong>Email:</strong> ${props.customerEmail}
                    `;

                // Set car info
                document.getElementById('car-name').textContent = props.carName;

                // Set locations
                document.getElementById('booking-locations').innerHTML = `
                        <strong>Pickup:</strong> ${props.pickupLocation}<br>
                        <strong>Dropoff:</strong> ${props.dropoffLocation}
                    `;

                // Set amount
                document.getElementById('booking-amount').innerHTML = `
                        <strong>Total Amount:</strong> $${parseFloat(props.totalAmount).toFixed(2)}
                    `;

                // Set button data-id for editing
                editBtn.setAttribute('data-id', event.id);

                // Hide loader, show content
                setTimeout(() => {
                    loader.style.display = 'none';
                    content.style.display = 'block';
                }, 500);
            }

            // Override the resetForm function to refresh calendar
            const originalResetForm = window.resetForm;
            window.resetForm = function() {
                if (originalResetForm) {
                    originalResetForm();
                } else {
                    // Fallback if the original function isn't available
                    if (bookingForm) {
                        bookingForm.reset();
                        document.getElementById('booking_id').value = '';
                    }
                    clearValidationErrors();
                    document.getElementById('availability_display').innerHTML = '<span class="badge bg-secondary">No car selected</span>';
                }
            };

            // Override handleFormSubmit to refresh calendar after successful booking
            const originalHandleFormSubmit = window.handleFormSubmit;
            window.handleFormSubmit = function(e) {
                e.preventDefault();

                // Use the original function implementation but add calendar refresh
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
                const saveBtn = document.getElementById('saveBtn');
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
                        const bsModal = bootstrap.Modal.getInstance(document.getElementById('bookingModal'));
                        if (bsModal) bsModal.hide();

                        // Reload calendar instead of DataTable
                        if (window.calendar) window.calendar.refetchEvents();

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
            };
        });
    </script>
@endpush