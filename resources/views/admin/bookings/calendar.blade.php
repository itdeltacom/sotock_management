@extends('admin.layouts.master')

@section('title', 'Booking Calendar')

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header pb-0 p-3">
                        <div class="row">
                            <div class="col-6 d-flex align-items-center">
                                <h6 class="mb-0">Booking Calendar</h6>
                            </div>
                            <div class="col-6 text-end">
                                <button type="button" id="createBookingFromCalendar" class="btn bg-gradient-primary me-2">
                                    <i class="fas fa-plus"></i> Add New Booking
                                </button>
                                <a href="{{ route('admin.bookings.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-list"></i> List View
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body px-0 pt-0 pb-2">
                        <div class="p-3">
                            <div class="row mb-4">
                                <div class="col-md-4 mb-3">
                                    <label for="filterCar" class="form-label">Filter by Car</label>
                                    <select id="filterCar" class="form-select">
                                        <option value="">All Cars</option>
                                        @foreach(\App\Models\Car::orderBy('name')->get() as $car)
                                            <option value="{{ $car->id }}">{{ $car->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="filterStatus" class="form-label">Filter by Status</label>
                                    <select id="filterStatus" class="form-select">
                                        <option value="">All Statuses</option>
                                        <option value="pending">Pending</option>
                                        <option value="confirmed">Confirmed</option>
                                        <option value="completed">Completed</option>
                                        <option value="cancelled">Cancelled</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="filterPayment" class="form-label">Filter by Payment</label>
                                    <select id="filterPayment" class="form-select">
                                        <option value="">All Payment Statuses</option>
                                        <option value="paid">Paid</option>
                                        <option value="unpaid">Unpaid</option>
                                        <option value="pending">Pending</option>
                                        <option value="refunded">Refunded</option>
                                    </select>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div>
                                    <button type="button" id="prevBtn" class="btn btn-sm btn-outline-secondary me-2">
                                        <i class="fas fa-chevron-left"></i> Previous
                                    </button>
                                    <button type="button" id="todayBtn" class="btn btn-sm bg-gradient-primary me-2">
                                        Today
                                    </button>
                                    <button type="button" id="nextBtn" class="btn btn-sm btn-outline-secondary">
                                        Next <i class="fas fa-chevron-right"></i>
                                    </button>
                                </div>
                                <div class="btn-group">
                                    <button type="button" id="monthViewBtn"
                                        class="btn btn-sm btn-outline-secondary active">Month</button>
                                    <button type="button" id="weekViewBtn"
                                        class="btn btn-sm btn-outline-secondary">Week</button>
                                    <button type="button" id="dayViewBtn"
                                        class="btn btn-sm btn-outline-secondary">Day</button>
                                </div>
                            </div>

                            <div id="calendarTitle" class="h5 text-center mb-4"></div>
                            <div id="calendar"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Booking Details Modal -->
        <div class="modal fade" id="viewBookingModal" tabindex="-1" aria-labelledby="viewBookingModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="viewBookingModalLabel">Booking Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" style="position: relative;">
                        <div id="view-loading-overlay"
                            class="position-absolute bg-white d-flex justify-content-center align-items-center"
                            style="left: 0; top: 0; right: 0; bottom: 0; z-index: 10; display: none;">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <h6 id="view-booking-number" class="mb-2"></h6>
                                <div id="view-status-badge" class="mb-2"></div>
                                <div id="view-payment-badge" class="mb-2"></div>
                                <p><strong>Created:</strong> <span id="view-created-at"></span></p>
                                <p><strong>Updated:</strong> <span id="view-updated-at"></span></p>
                            </div>
                            <div class="col-md-6">
                                <h6>Car Information</h6>
                                <p><strong>Name:</strong> <span id="view-car-name"></span></p>
                                <p><span id="view-car-details"></span></p>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Customer Information</h6>
                                <p><strong>Name:</strong> <span id="view-customer-name"></span></p>
                                <p><strong>Email:</strong> <span id="view-customer-email"></span></p>
                                <p><strong>Phone:</strong> <span id="view-customer-phone"></span></p>
                                <p><strong>Account:</strong> <span id="view-customer-account"></span></p>
                            </div>
                            <div class="col-md-6">
                                <h6>Rental Details</h6>
                                <p><strong>Pickup:</strong> <span id="view-pickup-details"></span></p>
                                <p><strong>Dropoff:</strong> <span id="view-dropoff-details"></span></p>
                                <p><strong>Duration:</strong> <span id="view-duration"></span></p>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Payment Information</h6>
                                <p><strong>Base Price:</strong> <span id="view-base-price"></span></p>
                                <p><strong>Discount:</strong> <span id="view-discount"></span></p>
                                <p><strong>Tax:</strong> <span id="view-tax"></span></p>
                                <p><strong>Total:</strong> <span id="view-total"></span></p>
                                <p><strong>Method:</strong> <span id="view-payment-method"></span></p>
                                <p><strong>Transaction ID:</strong> <span id="view-transaction-id"></span></p>
                            </div>
                            <div class="col-md-6">
                                <h6>Special Requests</h6>
                                <p id="view-special-requests"></p>
                            </div>
                        </div>
                        <hr>
                        <div id="view-status-actions" class="mt-3"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" id="viewEditBtn" class="btn bg-gradient-primary">Edit Booking</button>
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Include Booking Modal -->
        @include('admin.bookings._booking_modal', ['cars' => \App\Models\Car::where('is_available', true)->orderBy('name')->get()])
    </div>
@endsection

@push('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css">
    <style>
        #calendar {
            max-width: 100%;
            margin: 0 auto;
        }

        .fc-event {
            cursor: pointer;
            border-radius: 0.25rem;
            padding: 2px 5px;
            font-size: 0.85rem;
        }

        .fc-daygrid-day.fc-day-today {
            background-color: rgba(94, 114, 228, 0.1) !important;
        }

        .booking-pending {
            background-color: #fb6340 !important;
            border-color: #fb6340 !important;
            color: white !important;
        }

        .booking-confirmed {
            background-color: #2dce89 !important;
            border-color: #2dce89 !important;
            color: white !important;
        }

        .booking-completed {
            background-color: #11cdef !important;
            border-color: #11cdef !important;
            color: white !important;
        }

        .booking-cancelled {
            background-color: #f5365c !important;
            border-color: #f5365c !important;
            color: white !important;
            text-decoration: line-through;
        }

        .modal-content {
            border-radius: 0.75rem;
            box-shadow: 0 10px 35px -5px rgba(0, 0, 0, 0.15);
        }

        .modal-fullscreen .modal-content {
            border-radius: 0;
        }

        .modal-fullscreen .modal-body {
            max-height: calc(100vh - 130px);
            overflow-y: auto;
        }

        .form-control,
        .form-select {
            border-radius: 0.5rem;
            font-size: 0.875rem;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #5e72e4;
            box-shadow: 0 3px 9px rgba(50, 50, 9, 0), 3px 4px 8px rgba(94, 114, 228, 0.1);
        }

        .form-label {
            font-size: 0.875rem;
            font-weight: 600;
            color: #8392AB;
        }

        .bg-gradient-primary {
            background: linear-gradient(310deg, #5e72e4 0%, #825ee4 100%);
        }

        .badge {
            font-size: 0.85rem;
            padding: 0.5rem 1rem;
        }

        #availability_display .badge {
            font-size: 1rem;
            padding: 0.5rem 1rem;
        }
    </style>
@endpush

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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

        document.addEventListener('DOMContentLoaded', function () {
            // Initialize FullCalendar
            const calendarEl = document.getElementById('calendar');
            let currentView = 'dayGridMonth';
            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: currentView,
                headerToolbar: false,
                height: '800px',
                events: function (info, successCallback, failureCallback) {
                    const carId = document.getElementById('filterCar').value;
                    const status = document.getElementById('filterStatus').value;
                    const paymentStatus = document.getElementById('filterPayment').value;

                    fetch(`${routes.dataUrl}?start=${info.startStr}&end=${info.endStr}&car_id=${carId}&status=${status}&payment_status=${paymentStatus}`)
                        .then(response => response.json())
                        .then(data => {
                            const events = data.data.map(booking => ({
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
                            }));
                            successCallback(events);
                        })
                        .catch(error => {
                            console.error('Error fetching booking data:', error);
                            failureCallback(error);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Failed to load calendar events',
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 3000
                            });
                        });
                },
                eventClick: function (info) {
                    handleViewBooking(info.event.id);
                },
                dateClick: function (info) {
                    resetForm();
                    const clickedDate = info.dateStr;
                    const nextDay = new Date(clickedDate);
                    nextDay.setDate(nextDay.getDate() + 1);
                    const nextDayStr = nextDay.toISOString().split('T')[0];

                    document.getElementById('pickup_date').value = clickedDate;
                    document.getElementById('dropoff_date').value = nextDayStr;
                    document.getElementById('pickup_time').value = '10:00';
                    document.getElementById('dropoff_time').value = '10:00';

                    document.getElementById('bookingModalLabel').textContent = 'Add New Booking';
                    const bsModal = new bootstrap.Modal(document.getElementById('bookingModal'));
                    bsModal.show();
                },
                eventTimeFormat: {
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: true
                },
                datesSet: function (dateInfo) {
                    updateCalendarTitle(dateInfo.view);
                }
            });

            calendar.render();
            window.calendar = calendar;

            // Update initial calendar title
            updateCalendarTitle(calendar.view);

            // Navigation buttons
            document.getElementById('prevBtn').addEventListener('click', () => calendar.prev());
            document.getElementById('todayBtn').addEventListener('click', () => calendar.today());
            document.getElementById('nextBtn').addEventListener('click', () => calendar.next());

            // View change buttons
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

            // Filter changes
            document.getElementById('filterCar').addEventListener('change', () => calendar.refetchEvents());
            document.getElementById('filterStatus').addEventListener('change', () => calendar.refetchEvents());
            document.getElementById('filterPayment').addEventListener('change', () => calendar.refetchEvents());

            // Create Booking Button
            document.getElementById('createBookingFromCalendar').addEventListener('click', function () {
                resetForm();
                const today = new Date().toISOString().split('T')[0];
                const tomorrow = new Date();
                tomorrow.setDate(tomorrow.getDate() + 1);
                const tomorrowStr = tomorrow.toISOString().split('T')[0];

                document.getElementById('pickup_date').value = today;
                document.getElementById('dropoff_date').value = tomorrowStr;
                document.getElementById('pickup_time').value = '10:00';
                document.getElementById('dropoff_time').value = '10:00';

                document.getElementById('bookingModalLabel').textContent = 'Add New Booking';
                const bsModal = new bootstrap.Modal(document.getElementById('bookingModal'));
                bsModal.show();
            });

            // Auto-calculate prices on field changes
            ['car_id', 'pickup_date', 'dropoff_date'].forEach(id => {
                const element = document.getElementById(id);
                if (element) {
                    element.addEventListener('change', calculatePrices);
                }
            });

            // Helper Functions
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
                    endDate.setDate(endDate.getDate() - 1);
                    title = `${formatDate(endDate)}`;
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

            // Override resetForm to handle modal fields
            const originalResetForm = window.resetForm || function () {
                const bookingForm = document.getElementById('bookingForm');
                if (bookingForm) bookingForm.reset();
                clearValidationErrors();
                document.getElementById('availability_display').innerHTML = '<span class="badge bg-secondary">No car selected</span>';
            };
            window.resetForm = function () {
                originalResetForm();
                document.getElementById('booking_id').value = '';
                document.getElementById('total_days').value = '';
                document.getElementById('base_price').value = '';
                document.getElementById('discount_amount').value = '';
                document.getElementById('tax_amount').value = '';
                document.getElementById('total_amount').value = '';
            };

            // Override handleFormSubmit to refresh calendar
            const originalHandleFormSubmit = window.handleFormSubmit;
            window.handleFormSubmit = function (e) {
                e.preventDefault();
                clearValidationErrors();

                const formData = new FormData(e.target);
                const bookingId = document.getElementById('booking_id').value;
                const isEdit = bookingId && bookingId !== '';

                if (isEdit) {
                    formData.append('_method', 'PUT');
                }

                const url = isEdit ? routes.updateUrl.replace(':id', bookingId) : routes.storeUrl;
                const saveBtn = document.getElementById('saveBtn');
                if (saveBtn) {
                    saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...';
                    saveBtn.disabled = true;
                }

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
                            const bsModal = bootstrap.Modal.getInstance(document.getElementById('bookingModal'));
                            if (bsModal) bsModal.hide();
                            window.calendar.refetchEvents();
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
                        if (error.status === 422 && error.data && error.data.errors) {
                            displayValidationErrors(error.data.errors);
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
                        if (saveBtn) {
                            saveBtn.innerHTML = 'Save Booking';
                            saveBtn.disabled = false;
                        }
                    });
            };

            // Override calculatePrices to handle readonly fields
            const originalCalculatePrices = window.calculatePrices;
            window.calculatePrices = function () {
                const carId = document.getElementById('car_id').value;
                const pickupDate = document.getElementById('pickup_date').value;
                const dropoffDate = document.getElementById('dropoff_date').value;
                const bookingId = document.getElementById('booking_id').value;

                if (!carId || !pickupDate || !dropoffDate) {
                    resetPricing();
                    return;
                }

                if (new Date(pickupDate) > new Date(dropoffDate)) {
                    document.getElementById('availability_display').innerHTML = `
                            <span class="badge bg-danger">Invalid Dates</span>
                        `;
                    resetPricing();
                    return;
                }

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
                            const isAvailable = data.data.is_available;
                            document.getElementById('availability_display').innerHTML = isAvailable
                                ? '<span class="badge bg-success">Car Available</span>'
                                : '<span class="badge bg-danger">Car Unavailable</span>';

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
            };
        });
    </script>
    <script src="{{ asset('admin/js/bookings-management.js') }}"></script>
@endpush