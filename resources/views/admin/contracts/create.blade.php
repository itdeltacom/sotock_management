@extends('admin.layouts.master')

@section('title', 'Create Contract')

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header pb-0 p-3">
                        <div class="row">
                            <div class="col-6 d-flex align-items-center">
                                <h6 class="mb-0">Create New Contract</h6>
                            </div>
                            <div class="col-6 text-end">
                                <a href="{{ route('admin.contracts.index') }}" class="btn bg-gradient-secondary">
                                    <i class="fas fa-arrow-left"></i> Back to Contracts
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-3">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ route('admin.contracts.store') }}" method="POST" id="createContractForm"
                              data-config='{
                                  "additional_driver_fee": {{ config('rental_config.additional_driver_fee') }},
                                  "gps_fee": {{ config('rental_config.gps_fee') }},
                                  "child_seat_fee": {{ config('rental_config.child_seat_fee') }},
                                  "tax_rate": {{ config('rental_config.tax_rate') / 100 }},
                                  "default_deposit": {{ config('rental_config.default_deposit') }}
                              }'>
                            @csrf
                            <div class="row">
                                <!-- Creation Type -->
                                <div class="col-12 mb-3">
                                    <div class="card mb-4">
                                        <div class="card-header pb-0 p-3 bg-light">
                                            <h6 class="mb-0">Creation Method</h6>
                                        </div>
                                        <div class="card-body p-3">
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="creation_type"
                                                       id="fromBooking" value="booking" checked>
                                                <label class="form-check-label" for="fromBooking">From Pending Booking</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="creation_type"
                                                       id="manualInput" value="manual">
                                                <label class="form-check-label" for="manualInput">Manual Input</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Client Section -->
                                <div class="col-md-6">
                                    <div class="card mb-4 card-contract">
                                        <div class="card-header pb-0 p-3 bg-light">
                                            <h6 class="mb-0">Client Information</h6>
                                        </div>
                                        <div class="card-body p-3">
                                            <div class="mb-3" id="clientSelection">
                                                <label for="client_id" class="form-control-label">Select Client <span
                                                        class="text-danger">*</span></label>
                                                <select class="form-select" id="client_id" name="client_id" required>
                                                    <option value="">-- Select Client --</option>
                                                    @foreach($clients as $client)
                                                        <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>
                                                            {{ $client->name }} ({{ $client->id_number }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="mb-3" id="bookingSelection" style="display: none;">
                                                <label for="booking_id" class="form-control-label">Select Booking <span
                                                        class="text-danger">*</span></label>
                                                <select class="form-select" id="booking_id" name="booking_id" disabled>
                                                    <option value="">Select a client first</option>
                                                </select>
                                            </div>
                                            <div id="client-details" class="d-none">
                                                <div class="card card-body bg-gray-100 mb-3">
                                                    <div class="d-flex mb-2">
                                                        <div class="avatar avatar-xl me-3">
                                                            <img src="" id="client-avatar" alt="Client Avatar"
                                                                 class="border-radius-lg shadow">
                                                        </div>
                                                        <div class="d-flex flex-column">
                                                            <h6 class="mb-1 text-dark text-sm" id="client-name"></h6>
                                                            <span class="text-xs" id="client-contact"></span>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <p class="text-xs mb-1">ID Number: <span
                                                                    id="client-id-number"></span></p>
                                                            <p class="text-xs mb-1">License: <span
                                                                    id="client-license"></span></p>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <p class="text-xs mb-1">Address: <span
                                                                    id="client-address"></span></p>
                                                            <p class="text-xs mb-0">Status: <span id="client-status"></span>
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="alert alert-warning d-none" id="client-alert">
                                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                                    <span id="client-alert-text"></span>
                                                </div>
                                            </div>
                                            <div class="text-end">
                                                <a href="{{ route('admin.clients.create') }}"
                                                   class="btn btn-sm bg-gradient-dark" target="_blank">
                                                    <i class="fas fa-plus"></i> New Client
                                                </a>
                                                <button type="button" class="btn btn-sm bg-gradient-info"
                                                        id="refresh-clients">
                                                    <i class="fas fa-sync"></i> Refresh List
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Vehicle Section -->
                                <div class="col-md-6">
                                    <div class="card mb-4 card-contract">
                                        <div class="card-header pb-0 p-3 bg-light">
                                            <h6 class="mb-0">Vehicle Information</h6>
                                        </div>
                                        <div class="card-body p-3">
                                            <div class="mb-3">
                                                <label for="car_id" class="form-control-label">Select Vehicle <span
                                                        class="text-danger">*</span></label>
                                                <select class="form-select" id="car_id" name="car_id" required>
                                                    <option value="">-- Select Vehicle --</option>
                                                    @foreach($availableCars as $car)
                                                        <option value="{{ $car->id }}" {{ old('car_id') == $car->id ? 'selected' : '' }}
                                                                data-daily-rate="{{ $car->daily_rate }}"
                                                                data-mileage="{{ $car->mileage }}"
                                                                data-brand="{{ $car->brand_name }}"
                                                                data-model="{{ $car->model }}"
                                                                data-plate="{{ $car->matricule }}">
                                                            {{ $car->brand_name }} {{ $car->model }} ({{ $car->matricule }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div id="car-details" class="d-none">
                                                <div class="card card-body bg-gray-100 mb-3">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <img src="" id="car-image" alt="Vehicle Image"
                                                                 class="w-100 border-radius-lg shadow">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <h6 class="mb-1 text-dark text-sm" id="car-name"></h6>
                                                            <p class="text-xs mb-1">License Plate: <span
                                                                    id="car-plate"></span></p>
                                                            <p class="text-xs mb-1">Year: <span id="car-year"></span></p>
                                                            <p class="text-xs mb-1">Category: <span
                                                                    id="car-category"></span></p>
                                                            <p class="text-xs mb-0">Mileage: <span id="car-mileage"></span>
                                                                km</p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div id="car-rate-info" class="alert alert-info">
                                                    <div class="row">
                                                        <div class="col-6">
                                                            <h6 class="text-sm mb-1">Daily Rate</h6>
                                                            <p class="text-lg font-weight-bold mb-0" id="car-daily-rate">0
                                                                MAD</p>
                                                        </div>
                                                        <div class="col-6 text-end">
                                                            <h6 class="text-sm mb-1">Availability</h6>
                                                            <span class="badge bg-success"
                                                                  id="car-availability">Available</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="text-end">
                                                <button type="button" class="btn btn-sm bg-gradient-info" id="refresh-cars">
                                                    <i class="fas fa-sync"></i> Refresh List
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Rental Details Section -->
                                <div class="col-12 pt-3">
                                    <div class="card mb-4">
                                        <div class="card-header pb-0 p-3 bg-light">
                                            <h6 class="mb-0">Rental Details</h6>
                                        </div>
                                        <div class="card-body p-3">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group mb-3">
                                                        <label for="start_date" class="form-control-label">Start Date <span
                                                                class="text-danger">*</span></label>
                                                        <input class="form-control" type="date" id="start_date"
                                                               name="start_date" value="{{ old('start_date', date('Y-m-d')) }}"
                                                               required>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group mb-3">
                                                        <label for="end_date" class="form-control-label">End Date <span
                                                                class="text-danger">*</span></label>
                                                        <input class="form-control" type="date" id="end_date"
                                                               name="end_date"
                                                               value="{{ old('end_date', date('Y-m-d', strtotime('+1 day'))) }}"
                                                               required>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group mb-3">
                                                        <label for="rental_fee" class="form-control-label">Daily Rental Rate
                                                            (MAD) <span class="text-danger">*</span></label>
                                                        <input class="form-control" type="number" min="0" step="0.01"
                                                               id="rental_fee" name="rental_fee"
                                                               value="{{ old('rental_fee') }}" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group mb-3">
                                                        <label for="start_mileage" class="form-control-label">Starting
                                                            Mileage (km) <span class="text-danger">*</span></label>
                                                        <input class="form-control" type="number" min="0" id="start_mileage"
                                                               name="start_mileage" value="{{ old('start_mileage') }}"
                                                               required>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group mb-3">
                                                        <label for="deposit_amount" class="form-control-label">Security
                                                            Deposit (MAD)</label>
                                                        <input class="form-control" type="number" min="0" step="0.01"
                                                               id="deposit_amount" name="deposit_amount"
                                                               value="{{ old('deposit_amount', config('rental_config.default_deposit')) }}">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group mb-3">
                                                        <label for="discount" class="form-control-label">Discount Amount
                                                            (MAD)</label>
                                                        <input class="form-control" type="number" min="0" step="0.01"
                                                               id="discount" name="discount" value="{{ old('discount', 0) }}">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group mb-3">
                                                        <label for="insurance_plan" class="form-control-label">Insurance
                                                            Plan</label>
                                                        <select class="form-select" id="insurance_plan"
                                                                name="insurance_plan">
                                                            <option value="" {{ old('insurance_plan') == '' ? 'selected' : '' }}>None</option>
                                                            <option value="standard" {{ old('insurance_plan') == 'standard' ? 'selected' : '' }}>Standard</option>
                                                            <option value="premium" {{ old('insurance_plan') == 'premium' ? 'selected' : '' }}>Premium</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group mb-3">
                                                        <label for="payment_status" class="form-control-label">Payment
                                                            Status <span class="text-danger">*</span></label>
                                                        <select class="form-select" id="payment_status"
                                                                name="payment_status" required>
                                                            <option value="pending" {{ old('payment_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                                            <option value="partial" {{ old('payment_status') == 'partial' ? 'selected' : '' }}>Partial Payment</option>
                                                            <option value="paid" {{ old('payment_status') == 'paid' ? 'selected' : '' }}>Fully Paid</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="form-check mb-3">
                                                        <input class="form-check-input" type="checkbox"
                                                               id="additional_driver" name="additional_driver" {{ old('additional_driver') ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="additional_driver">
                                                            Additional Driver
                                                            ({{ config('rental_config.additional_driver_fee') }} MAD/day)
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-check mb-3">
                                                        <input class="form-check-input" type="checkbox" id="gps_enabled"
                                                               name="gps_enabled" {{ old('gps_enabled') ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="gps_enabled">
                                                            GPS Navigation ({{ config('rental_config.gps_fee') }} MAD/day)
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-check mb-3">
                                                        <input class="form-check-input" type="checkbox" id="child_seat"
                                                               name="child_seat" {{ old('child_seat') ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="child_seat">
                                                            Child Seat ({{ config('rental_config.child_seat_fee') }} MAD/day)
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group mb-3">
                                                <label for="notes" class="form-control-label">Notes</label>
                                                <textarea class="form-control" id="notes" name="notes"
                                                          rows="3">{{ old('notes') }}</textarea>
                                            </div>
                                            <div class="card bg-light p-3 mb-3">
                                                <h6 class="mb-3">Rental Summary</h6>
                                                <div class="row">
                                                    <div class="col-md-3 mb-2">
                                                        <p class="text-xs text-muted mb-1">Duration</p>
                                                        <p class="font-weight-bold mb-0" id="duration-days">0 day(s)</p>
                                                    </div>
                                                    <div class="col-md-3 mb-2">
                                                        <p class="text-xs text-muted mb-1">Daily Rate</p>
                                                        <p class="font-weight-bold mb-0" id="summary-daily-rate">0 MAD</p>
                                                    </div>
                                                    <div class="col-md-3 mb-2">
                                                        <p class="text-xs text-muted mb-1">Security Deposit</p>
                                                        <p class="font-weight-bold mb-0" id="summary-deposit">0 MAD</p>
                                                    </div>
                                                    <div class="col-md-3 mb-2">
                                                        <p class="text-xs text-muted mb-1">Discount</p>
                                                        <p class="font-weight-bold mb-0" id="summary-discount">0 MAD</p>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-3 mb-2">
                                                        <p class="text-xs text-muted mb-1">Extras</p>
                                                        <p class="font-weight-bold mb-0" id="summary-extras">0 MAD</p>
                                                    </div>
                                                    <div class="col-md-3 mb-2">
                                                        <p class="text-xs text-muted mb-1">Tax</p>
                                                        <p class="font-weight-bold mb-0" id="summary-tax">0 MAD</p>
                                                    </div>
                                                </div>
                                                <hr>
                                                <div class="row">
                                                    <div class="col-6">
                                                        <p class="text-xs text-muted mb-1">Subtotal</p>
                                                        <p class="font-weight-bold mb-0" id="summary-subtotal">0 MAD</p>
                                                    </div>
                                                    <div class="col-6 text-end">
                                                        <p class="text-xs text-muted mb-1">Total Amount</p>
                                                        <p class="text-lg font-weight-bold text-success mb-0"
                                                           id="summary-total">0 MAD</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Contract Terms -->
                                <div class="col-12">
                                    <div class="card mb-4">
                                        <div class="card-header pb-0 p-3 bg-light">
                                            <h6 class="mb-0">Contract Terms</h6>
                                        </div>
                                        <div class="card-body p-3">
                                            <div class="form-check mb-3">
                                                <input class="form-check-input" type="checkbox" id="terms_agreed"
                                                       name="terms_agreed" required {{ old('terms_agreed') ? 'checked' : '' }}>
                                                <label class="form-check-label" for="terms_agreed">
                                                    Client has agreed to the rental terms and conditions
                                                </label>
                                            </div>
                                            <div class="form-check mb-3">
                                                <input class="form-check-input" type="checkbox" id="id_verified"
                                                       name="id_verified" required {{ old('id_verified') ? 'checked' : '' }}>
                                                <label class="form-check-label" for="id_verified">
                                                    Client's ID and driver's license have been verified
                                                </label>
                                            </div>
                                            <div class="form-check mb-3">
                                                <input class="form-check-input" type="checkbox" id="vehicle_inspected"
                                                       name="vehicle_inspected" required {{ old('vehicle_inspected') ? 'checked' : '' }}>
                                                <label class="form-check-label" for="vehicle_inspected">
                                                    Vehicle has been inspected with the client and existing damage documented
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="text-center mt-4">
                                <button type="submit" class="btn bg-gradient-primary btn-lg w-50" id="submitBtn">Create
                                    Contract</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <style>
        .select2-container .select2-selection--single {
            height: 38px;
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 38px;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 38px;
        }
        .card-contract{
            height: 100%;
        }
    </style>
@endpush

@push('js')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
   <script>
    // Debounce function to limit frequent function calls
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

$(document).ready(function () {
    // Initialize Select2
    $('#client_id, #booking_id, #car_id').select2({
        width: '100%',
        placeholder: '-- Select --',
        allowClear: true
    });

    // Toggle creation type
    $('input[name="creation_type"]').on('change', function () {
        const creationType = $(this).val();
        if (creationType === 'booking') {
            $('#bookingSelection').show();
            $('#clientSelection').show();
            $('#booking_id').prop('required', true);
            $('#car_id, #start_date, #end_date, #rental_fee, #start_mileage, #insurance_plan, #additional_driver, #gps_enabled, #child_seat').prop('disabled', true);
        } else {
            $('#bookingSelection').hide();
            $('#clientSelection').show();
            $('#booking_id').prop('required', false);
            $('#car_id, #start_date, #end_date, #rental_fee, #start_mileage, #insurance_plan, #additional_driver, #gps_enabled, #child_seat').prop('disabled', false);
            
            // When switching to manual mode, reset form and trigger car selection if a car is already selected
            // This ensures the car's daily rate gets properly loaded
            resetForm();
            
            const selectedCarId = $('#car_id').val();
            if (selectedCarId) {
                $('#car_id').trigger('change');
            }
        }
        updateRentalSummary();
    });

    // Trigger default creation type
    $('#fromBooking').trigger('change');

    // Client selection change
    $('#client_id').on('change', function() {
        const clientId = $(this).val();
        if (clientId) {
            if ($('#fromBooking').is(':checked')) {
                const url = '/admin/contracts/pending-bookings';
                console.log('Fetching pending bookings from URL:', url, 'with client_id:', clientId);
                $.ajax({
                    url: url,
                    type: 'GET',
                    data: { client_id: clientId },
                    success: function(response) {
                        if (response.success && response.bookings.length > 0) {
                            let options = '<option value="">Select a booking</option>';
                            response.bookings.forEach(booking => {
                                options += `<option value="${booking.id}" 
                                            data-car-id="${booking.car_id}"
                                            data-start-date="${booking.pickup_date}"
                                            data-end-date="${booking.dropoff_date}"
                                            data-rental-fee="${booking.base_price / booking.total_days}"
                                            data-mileage="${booking.start_mileage || booking.car.mileage}"
                                            data-insurance="${booking.insurance_plan || ''}"
                                            data-additional-driver="${booking.additional_driver ? 1 : 0}"
                                            data-gps="${booking.gps_enabled ? 1 : 0}"
                                            data-child-seat="${booking.child_seat ? 1 : 0}"
                                            data-deposit="${booking.deposit_amount}"
                                            data-discount="${booking.discount_amount}"
                                            data-total-amount="${booking.total_amount}"
                                            data-car-details="${booking.car.brand_name} ${booking.car.model} (${booking.car.matricule})">
                                            Booking #${booking.id} - ${booking.car.brand_name} ${booking.car.model} (${booking.pickup_date} to ${booking.dropoff_date})
                                        </option>`;
                            });
                            $('#booking_id').html(options).prop('disabled', false);
                        } else {
                            $('#booking_id').html('<option value="">No pending bookings</option>').prop('disabled', false);
                        }
                    },
                    error: function(xhr) {
                        console.error('Error fetching pending bookings:', xhr.responseText);
                        $('#booking_id').html('<option value="">Error loading bookings</option>');
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Failed to load pending bookings',
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000
                        });
                    }
                });
            }
        } else {
            $('#booking_id').html('<option value="">Select a client first</option>').prop('disabled', true);
        }
    });

    // Booking selection change
    $('#booking_id').on('change', function () {
        const selectedOption = $(this).find('option:selected');
        if (selectedOption.val()) {
            const carId = selectedOption.data('car-id');
            const startDate = selectedOption.data('start-date');
            const endDate = selectedOption.data('end-date');
            const rentalFee = selectedOption.data('rental-fee');
            const mileage = selectedOption.data('mileage');
            const insurance = selectedOption.data('insurance');
            const additionalDriver = selectedOption.data('additional-driver');
            const gps = selectedOption.data('gps');
            const childSeat = selectedOption.data('child-seat');
            const deposit = selectedOption.data('deposit');
            const discount = selectedOption.data('discount');
            const totalAmount = selectedOption.data('total-amount');
            const carDetails = selectedOption.data('car-details');

            // Set form fields and trigger change events
            $('#car_id').val(carId).trigger('change');
            $('#start_date').val(startDate);
            $('#end_date').val(endDate);
            $('#rental_fee').val(rentalFee.toFixed(2));
            $('#start_mileage').val(mileage);
            $('#insurance_plan').val(insurance);
            $('#additional_driver').prop('checked', !!additionalDriver);
            $('#gps_enabled').prop('checked', !!gps);
            $('#child_seat').prop('checked', !!childSeat);
            $('#deposit_amount').val(deposit.toFixed(2));
            $('#discount').val(discount.toFixed(2));

            // Store booking data for calculations
            $(this).data('booking-data', {
                startDate: startDate,
                endDate: endDate,
                totalAmount: totalAmount,
                rentalFee: rentalFee,
                deposit: deposit,
                discount: discount
            });

            // Explicitly call updateRentalSummary to ensure summary reflects all changes
            updateRentalSummary();
        } else {
            resetForm();
        }
    });

    // Car selection change
    $('#car_id').on('change', function () {
        const carId = $(this).val();
        if (carId) {
            const selectedOption = $(this).find('option:selected');
            const dailyRate = selectedOption.data('daily-rate');
            const mileage = selectedOption.data('mileage');
            const brand = selectedOption.data('brand');
            const model = selectedOption.data('model');
            const plate = selectedOption.data('plate');
            
            // Only auto-set the rental fee if we're in manual mode
            const isManualMode = $('#manualInput').is(':checked');

            $.ajax({
                url: `/admin/cars/${carId}`,
                type: 'GET',
                success: function (response) {
                    if (response.success) {
                        const car = response.car;
                        $('#car-name').text(`${car.brand_name} ${car.model} (${car.year})`);
                        $('#car-plate').text(car.matricule);
                        $('#car-year').text(car.year);
                        $('#car-category').text(car.category ? car.category.name : 'N/A');
                        $('#car-mileage').text(car.mileage.toLocaleString());
                        $('#car-image').attr('src', car.featured_image || '/img/default-car.png');
                        $('#car-daily-rate').text(`${car.daily_rate.toLocaleString()} MAD`);
                        
                        // Only auto-populate fields if in manual mode
                        if (isManualMode) {
                            $('#rental_fee').val(car.daily_rate).trigger('input');
                            $('#start_mileage').val(car.mileage).trigger('input');
                        }
                        
                        $('#car-details').removeClass('d-none');
                        updateRentalSummary();
                    }
                },
                error: function () {
                    $('#car-details').addClass('d-none');
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to load vehicle details',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000
                    });
                }
            });
        } else {
            $('#car-details').addClass('d-none');
            
            // Only clear these fields if in manual mode
            if ($('#manualInput').is(':checked')) {
                $('#rental_fee').val('');
                $('#start_mileage').val('');
            }
            
            updateRentalSummary();
        }
    });

    // Refresh clients list
    $('#refresh-clients').on('click', function () {
        const button = $(this);
        const originalText = button.html();
        button.html('<i class="fas fa-spinner fa-spin"></i> Refreshing...').prop('disabled', true);
        $.ajax({
            url: '/admin/clients/list',
            type: 'GET',
            success: function (response) {
                if (response.success) {
                    const clientSelect = $('#client_id');
                    const selectedValue = clientSelect.val();
                    clientSelect.empty().append('<option value="">-- Select Client --</option>');
                    response.clients.forEach(function (client) {
                        const option = new Option(
                            `${client.full_name} (${client.id_number})`,
                            client.id,
                            client.id == selectedValue,
                            client.id == selectedValue
                        );
                        clientSelect.append(option);
                    });
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: 'Clients list refreshed',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 2000
                    });
                }
            },
            error: function () {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to refresh clients list',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                });
            },
            complete: function () {
                button.html(originalText).prop('disabled', false);
            }
        });
    });

    // Refresh cars list
    $('#refresh-cars').on('click', function () {
        const button = $(this);
        const originalText = button.html();
        button.html('<i class="fas fa-spinner fa-spin"></i> Refreshing...').prop('disabled', true);
        $.ajax({
            url: '/admin/api/cars/available',
            type: 'GET',
            success: function (response) {
                if (response.success) {
                    const carSelect = $('#car_id');
                    const selectedValue = carSelect.val();
                    carSelect.empty().append('<option value="">-- Select Vehicle --</option>');
                    response.cars.forEach(function (car) {
                        const option = new Option(
                            `${car.brand_name} ${car.model} (${car.matricule})`,
                            car.id,
                            car.id == selectedValue,
                            car.id == selectedValue
                        );
                        $(option).data({
                            'daily-rate': car.daily_rate,
                            'mileage': car.mileage,
                            'brand': car.brand_name,
                            'model': car.model,
                            'plate': car.matricule
                        });
                        carSelect.append(option);
                    });
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: 'Available vehicles list refreshed',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 2000
                    });
                }
            },
            error: function () {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to refresh vehicles list',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                });
            },
            complete: function () {
                button.html(originalText).prop('disabled', false);
            }
        });
    });

    // Update rental summary (debounced)
    const debouncedUpdateRentalSummary = debounce(updateRentalSummary, 300);
    $('#start_date, #end_date, #rental_fee, #deposit_amount, #discount, #insurance_plan, #additional_driver, #gps_enabled, #child_seat').on('change input', debouncedUpdateRentalSummary);

    // Main function to update rental summary
    function updateRentalSummary() {
        console.log("Updating rental summary...");
        
        const config = $('#createContractForm').data('config');
        const isBookingMode = $('#fromBooking').is(':checked');
        
        // If this is a booking, use the booking data
        if (isBookingMode && $('#booking_id').val()) {
            const bookingData = $('#booking_id').data('booking-data');
            
            if (bookingData) {
                console.log("Using booking data for calculation:", bookingData);
                
                // Calculate duration from booking dates
                let duration = 0;
                try {
                    const startDate = new Date(bookingData.startDate + 'T00:00:00');
                    const endDate = new Date(bookingData.endDate + 'T00:00:00');
                    
                    if (!isNaN(startDate.getTime()) && !isNaN(endDate.getTime())) {
                        const startUTC = Date.UTC(startDate.getFullYear(), startDate.getMonth(), startDate.getDate());
                        const endUTC = Date.UTC(endDate.getFullYear(), endDate.getMonth(), endDate.getDate());
                        const daysDiff = Math.floor((endUTC - startUTC) / (1000 * 60 * 60 * 24));
                        
                        duration = daysDiff + 1;
                    }
                } catch (error) {
                    console.error("Error calculating duration from booking dates:", error);
                }
                
                // Format currency values for display
                const formatCurrency = (value) => {
                    return parseFloat(value).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                };
                
                // Get values from booking data
                const rentalFee = parseFloat(bookingData.rentalFee) || 0;
                const deposit = parseFloat(bookingData.deposit) || 0;
                const discount = parseFloat(bookingData.discount) || 0;
                const totalAmount = parseFloat(bookingData.totalAmount) || 0;
                
                // Calculate subtotal (total + discount)
                const subtotal = totalAmount + discount;
                
                // Update UI elements
                $('#duration-days').text(duration > 0 ? `${duration} day(s)` : '0 day(s)');
                $('#summary-daily-rate').text(`${formatCurrency(rentalFee)} MAD`);
                $('#summary-deposit').text(`${formatCurrency(deposit)} MAD`);
                $('#summary-discount').text(`${formatCurrency(discount)} MAD`);
                
                // We don't have extras and tax breakdown from the booking, so set reasonable values
                const extrasCost = subtotal - (rentalFee * duration);
                $('#summary-extras').text(`${formatCurrency(Math.max(0, extrasCost))} MAD`);
                $('#summary-tax').text(`0.00 MAD`); // Assuming tax is 0% as per the config
                
                $('#summary-subtotal').text(`${formatCurrency(subtotal)} MAD`);
                $('#summary-total').text(`${formatCurrency(totalAmount)} MAD`);
                
                return; // Exit the function after using booking data
            }
        }
        
        // If we're not using booking data, do the normal calculation for manual mode
        const startDateStr = $('#start_date').val();
        const endDateStr = $('#end_date').val();
        const rentalFee = parseFloat($('#rental_fee').val()) || 0;
        const deposit = parseFloat($('#deposit_amount').val()) || 0;
        const discount = parseFloat($('#discount').val()) || 0;
        const insurancePlan = $('#insurance_plan').val();
        const additionalDriver = $('#additional_driver').is(':checked');
        const gps = $('#gps_enabled').is(':checked');
        const childSeat = $('#child_seat').is(':checked');
        
        console.log("Start Date:", startDateStr);
        console.log("End Date:", endDateStr);
        console.log("Rental Fee:", rentalFee);
        
        // Initialize variables with default values
        let duration = 0;
        let extrasCost = 0;
        let subtotal = 0;
        let tax = 0;
        let total = 0;
        
        // Only calculate if both dates are provided
        if (startDateStr && endDateStr) {
            try {
                // Parse dates correctly
                const startDate = new Date(startDateStr + 'T00:00:00');
                const endDate = new Date(endDateStr + 'T00:00:00');
                
                console.log("Parsed Start Date:", startDate);
                console.log("Parsed End Date:", endDate);
                
                // Check if dates are valid and end date is not before start date
                if (!isNaN(startDate.getTime()) && !isNaN(endDate.getTime())) {
                    // Calculate days difference
                    const startUTC = Date.UTC(startDate.getFullYear(), startDate.getMonth(), startDate.getDate());
                    const endUTC = Date.UTC(endDate.getFullYear(), endDate.getMonth(), endDate.getDate());
                    const daysDiff = Math.floor((endUTC - startUTC) / (1000 * 60 * 60 * 24));
                    
                    if (daysDiff >= 0) {
                        // Add 1 to include both the start and end day
                        duration = daysDiff + 1;
                        
                        console.log("Duration calculated:", duration);
                        
                        // Calculate extras cost
                        let dailyExtrasCost = 0;
                        
                        if (additionalDriver && config.additional_driver_fee) {
                            dailyExtrasCost += config.additional_driver_fee;
                        }
                        
                        if (gps && config.gps_fee) {
                            dailyExtrasCost += config.gps_fee;
                        }
                        
                        if (childSeat && config.child_seat_fee) {
                            dailyExtrasCost += config.child_seat_fee;
                        }
                        
                        if (insurancePlan === 'standard') {
                            dailyExtrasCost += 50; // Standard insurance rate
                        } else if (insurancePlan === 'premium') {
                            dailyExtrasCost += 100; // Premium insurance rate
                        }
                        
                        extrasCost = dailyExtrasCost * duration;
                        
                        // Calculate subtotal
                        subtotal = (rentalFee * duration) + extrasCost;
                        
                        // No tax
                        tax = 0;
                        
                        // Calculate total
                        total = subtotal - discount;
                        
                        console.log("Calculation complete:", {
                            duration,
                            extrasCost,
                            subtotal,
                            tax,
                            total
                        });
                    } else {
                        console.log("End date is before start date");
                    }
                } else {
                    console.log("One or both dates are invalid");
                }
            } catch (error) {
                console.error("Error in date calculation:", error);
            }
        }

        // Format currency values for display
        const formatCurrency = (value) => {
            return value.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        };

        // Update UI elements
        $('#duration-days').text(duration > 0 ? `${duration} day(s)` : 'Invalid dates');
        $('#summary-daily-rate').text(`${formatCurrency(rentalFee)} MAD`);
        $('#summary-deposit').text(`${formatCurrency(deposit)} MAD`);
        $('#summary-discount').text(`${formatCurrency(discount)} MAD`);
        $('#summary-extras').text(`${formatCurrency(extrasCost)} MAD`);
        $('#summary-tax').text(`${formatCurrency(tax)} MAD`);
        
        if (duration > 0) {
            $('#summary-subtotal').text(`${formatCurrency(subtotal)} MAD`);
            $('#summary-total').text(`${formatCurrency(total)} MAD`);
        } else {
            $('#summary-subtotal').text('Invalid dates');
            $('#summary-total').text('Invalid dates');
        }
    }

    // Reset form fields
    function resetForm() {
        // Preserve car selection if in manual mode and a car is already selected
        const isManualMode = $('#manualInput').is(':checked');
        const selectedCarId = $('#car_id').val();
        
        if (!isManualMode || !selectedCarId) {
            $('#car_id').val('').trigger('change');
        }
        
        $('#start_date').val(getCurrentDate());
        $('#end_date').val(getNextDate());
        
        // Only reset these fields if we're in manual mode
        if (isManualMode) {
            $('#rental_fee').val('');
            $('#start_mileage').val('');
        }
        
        // Always reset these optional fields
        $('#deposit_amount').val('1000');
        $('#discount').val('0');
        $('#insurance_plan').val('');
        $('#additional_driver').prop('checked', false);
        $('#gps_enabled').prop('checked', false);
        $('#child_seat').prop('checked', false);
        
        // If in manual mode and a car is selected, trigger car selection to load car rates
        if (isManualMode && selectedCarId) {
            $('#car_id').trigger('change');
        } else {
            updateRentalSummary();
        }
    }

    // Helper function to get current date in YYYY-MM-DD format
    function getCurrentDate() {
        const now = new Date();
        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    // Helper function to get next day in YYYY-MM-DD format
    function getNextDate() {
        const now = new Date();
        now.setDate(now.getDate() + 1);
        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    // Form submission
    $('#createContractForm').on('submit', function (e) {
        e.preventDefault();
        const startDate = new Date($('#start_date').val());
        const endDate = new Date($('#end_date').val());
        const submitBtn = $('#submitBtn');
        const originalText = submitBtn.html();

        if (endDate < startDate) {
            Swal.fire({
                icon: 'error',
                title: 'Invalid Date Range',
                text: 'End date must be on or after the start date',
            });
            return;
        }

        if (!$('#client_id').val()) {
            Swal.fire({
                icon: 'error',
                title: 'Client Required',
                text: 'Please select a client for this contract',
            });
            return;
        }

        if (!$('#car_id').val()) {
            Swal.fire({
                icon: 'error',
                title: 'Vehicle Required',
                text: 'Please select a vehicle for this contract',
            });
            return;
        }

        if (!$('#terms_agreed').is(':checked') || !$('#id_verified').is(':checked') || !$('#vehicle_inspected').is(':checked')) {
            Swal.fire({
                icon: 'error',
                title: 'Terms Required',
                text: 'Please confirm all contract terms before proceeding',
            });
            return;
        }

        submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Creating Contract...').prop('disabled', true);

        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: $(this).serialize(),
            success: function (response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message,
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000
                    }).then(() => {
                        window.location.href = '/admin/contracts';
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message,
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000
                    });
                }
            },
            error: function (xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    let errorMessages = '<ul>';
                    $.each(errors, function (key, value) {
                        errorMessages += `<li>${value[0]}</li>`;
                    });
                    errorMessages += '</ul>';
                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        html: errorMessages,
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'An error occurred while creating the contract',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000
                    });
                }
            },
            complete: function () {
                submitBtn.html(originalText).prop('disabled', false);
            }
        });
    });

    // Initial triggers
    if ($('#client_id').val()) {
        $('#client_id').trigger('change');
    }
    if ($('#car_id').val()) {
        $('#car_id').trigger('change');
    }
    
    // Call these functions to initialize the form
    resetForm();
    updateRentalSummary();
});
   </script>
@endpush