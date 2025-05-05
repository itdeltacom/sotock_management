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

                        <form action="{{ route('admin.contracts.store') }}" method="POST">
                            @csrf

                            <div class="row">
                                <!-- Client Section -->
                                <div class="col-md-6">
                                    <div class="card mb-4">
                                        <div class="card-header pb-0 p-3 bg-light">
                                            <h6 class="mb-0">Client Information</h6>
                                        </div>
                                        <div class="card-body p-3">
                                            <div class="mb-3">
                                                <label for="client_id" class="form-control-label">Select Client <span class="text-danger">*</span></label>
                                                <select class="form-select" id="client_id" name="client_id" required>
                                                    <option value="">-- Select Client --</option>
                                                    @foreach($clients as $client)
                                                        <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>
                                                            {{ $client->name }} ({{ $client->id }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            
                                            <div id="client-details" class="d-none">
                                                <div class="card card-body bg-gray-100 mb-3">
                                                    <div class="d-flex mb-2">
                                                        <div class="avatar avatar-xl me-3">
                                                            <img src="" id="client-avatar" alt="Client Avatar" class="border-radius-lg shadow">
                                                        </div>
                                                        <div class="d-flex flex-column">
                                                            <h6 class="mb-1 text-dark text-sm" id="client-name"></h6>
                                                            <span class="text-xs" id="client-contact"></span>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <p class="text-xs mb-1">ID Number: <span id="client-id-number"></span></p>
                                                            <p class="text-xs mb-1">License: <span id="client-license"></span></p>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <p class="text-xs mb-1">Address: <span id="client-address"></span></p>
                                                            <p class="text-xs mb-0">Status: <span id="client-status"></span></p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="alert alert-warning d-none" id="client-alert">
                                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                                    <span id="client-alert-text"></span>
                                                </div>
                                            </div>
                                            
                                            <div class="text-end">
                                                <a href="{{ route('admin.clients.create') }}" class="btn btn-sm bg-gradient-dark" target="_blank">
                                                    <i class="fas fa-plus"></i> New Client
                                                </a>
                                                <button type="button" class="btn btn-sm bg-gradient-info" id="refresh-clients">
                                                    <i class="fas fa-sync"></i> Refresh List
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Vehicle Section -->
                                <div class="col-md-6">
                                    <div class="card mb-4">
                                        <div class="card-header pb-0 p-3 bg-light">
                                            <h6 class="mb-0">Vehicle Information</h6>
                                        </div>
                                        <div class="card-body p-3">
                                            <div class="mb-3">
                                                <label for="car_id" class="form-control-label">Select Vehicle <span class="text-danger">*</span></label>
                                                <select class="form-select" id="car_id" name="car_id" required>
                                                    <option value="">-- Select Vehicle --</option>
                                                    @foreach($availableCars as $car)
                                                        <option value="{{ $car->id }}" {{ old('car_id') == $car->id ? 'selected' : '' }}>
                                                            {{ $car->brand_name }} {{ $car->model }} ({{ $car->matricule }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            
                                            <div id="car-details" class="d-none">
                                                <div class="card card-body bg-gray-100 mb-3">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <img src="" id="car-image" alt="Vehicle Image" class="w-100 border-radius-lg shadow">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <h6 class="mb-1 text-dark text-sm" id="car-name"></h6>
                                                            <p class="text-xs mb-1">License Plate: <span id="car-plate"></span></p>
                                                            <p class="text-xs mb-1">Year: <span id="car-year"></span></p>
                                                            <p class="text-xs mb-1">Category: <span id="car-category"></span></p>
                                                            <p class="text-xs mb-0">Mileage: <span id="car-mileage"></span> km</p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div id="car-rate-info" class="alert alert-info">
                                                    <div class="row">
                                                        <div class="col-6">
                                                            <h6 class="text-sm mb-1">Daily Rate</h6>
                                                            <p class="text-lg font-weight-bold mb-0" id="car-daily-rate">0 MAD</p>
                                                        </div>
                                                        <div class="col-6 text-end">
                                                            <h6 class="text-sm mb-1">Availability</h6>
                                                            <span class="badge bg-success" id="car-availability">Available</span>
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
                                <div class="col-12">
                                    <div class="card mb-4">
                                        <div class="card-header pb-0 p-3 bg-light">
                                            <h6 class="mb-0">Rental Details</h6>
                                        </div>
                                        <div class="card-body p-3">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group mb-3">
                                                        <label for="start_date" class="form-control-label">Start Date <span class="text-danger">*</span></label>
                                                        <input class="form-control" type="date" id="start_date" name="start_date" value="{{ old('start_date', date('Y-m-d')) }}" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group mb-3">
                                                        <label for="end_date" class="form-control-label">End Date <span class="text-danger">*</span></label>
                                                        <input class="form-control" type="date" id="end_date" name="end_date" value="{{ old('end_date', date('Y-m-d', strtotime('+1 day'))) }}" required>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group mb-3">
                                                        <label for="rental_fee" class="form-control-label">Daily Rental Rate (MAD) <span class="text-danger">*</span></label>
                                                        <input class="form-control" type="number" min="0" step="0.01" id="rental_fee" name="rental_fee" value="{{ old('rental_fee') }}" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group mb-3">
                                                        <label for="start_mileage" class="form-control-label">Starting Mileage (km) <span class="text-danger">*</span></label>
                                                        <input class="form-control" type="number" min="0" id="start_mileage" name="start_mileage" value="{{ old('start_mileage') }}" required>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group mb-3">
                                                        <label for="deposit_amount" class="form-control-label">Security Deposit (MAD)</label>
                                                        <input class="form-control" type="number" min="0" step="0.01" id="deposit_amount" name="deposit_amount" value="{{ old('deposit_amount', 0) }}">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group mb-3">
                                                        <label for="discount" class="form-control-label">Discount Amount (MAD)</label>
                                                        <input class="form-control" type="number" min="0" step="0.01" id="discount" name="discount" value="{{ old('discount', 0) }}">
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="form-group mb-3">
                                                <label for="payment_status" class="form-control-label">Payment Status <span class="text-danger">*</span></label>
                                                <select class="form-select" id="payment_status" name="payment_status" required>
                                                    <option value="pending" {{ old('payment_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                                    <option value="partial" {{ old('payment_status') == 'partial' ? 'selected' : '' }}>Partial Payment</option>
                                                    <option value="paid" {{ old('payment_status') == 'paid' ? 'selected' : '' }}>Fully Paid</option>
                                                </select>
                                            </div>
                                            
                                            <div class="form-group mb-3">
                                                <label for="notes" class="form-control-label">Notes</label>
                                                <textarea class="form-control" id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
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
                                                <hr>
                                                <div class="row">
                                                    <div class="col-6">
                                                        <p class="text-xs text-muted mb-1">Subtotal</p>
                                                        <p class="font-weight-bold mb-0" id="summary-subtotal">0 MAD</p>
                                                    </div>
                                                    <div class="col-6 text-end">
                                                        <p class="text-xs text-muted mb-1">Total Amount</p>
                                                        <p class="text-lg font-weight-bold text-success mb-0" id="summary-total">0 MAD</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Contracts Terms -->
                                <div class="col-12">
                                    <div class="card mb-4">
                                        <div class="card-header pb-0 p-3 bg-light">
                                            <h6 class="mb-0">Contract Terms</h6>
                                        </div>
                                        <div class="card-body p-3">
                                            <div class="form-check mb-3">
                                                <input class="form-check-input" type="checkbox" id="terms_agreed" name="terms_agreed" required {{ old('terms_agreed') ? 'checked' : '' }}>
                                                <label class="form-check-label" for="terms_agreed">
                                                    Client has agreed to the rental terms and conditions
                                                </label>
                                            </div>
                                            <div class="form-check mb-3">
                                                <input class="form-check-input" type="checkbox" id="id_verified" name="id_verified" required {{ old('id_verified') ? 'checked' : '' }}>
                                                <label class="form-check-label" for="id_verified">
                                                    Client's ID and driver's license have been verified
                                                </label>
                                            </div>
                                            <div class="form-check mb-3">
                                                <input class="form-check-input" type="checkbox" id="vehicle_inspected" name="vehicle_inspected" required {{ old('vehicle_inspected') ? 'checked' : '' }}>
                                                <label class="form-check-label" for="vehicle_inspected">
                                                    Vehicle has been inspected with the client and existing damage documented
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="text-center mt-4">
                                <button type="submit" class="btn bg-gradient-primary btn-lg w-50">Create Contract</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('css')
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
@endpush
@push('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        // Client selection change
        $('#client_id').on('change', function() {
    const clientId = $(this).val();
    console.log('Selected client ID:', clientId); // Debug log
    
    if (clientId) {
        const url = '{{ url("admin/clients/api") }}/' + clientId;
        console.log('Fetching from URL:', url); // Debug log
        
        $.ajax({
            url: url,
            type: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            success: function(response) {
                console.log('Response received:', response); // Debug log
                
                if (response.success) {
                    const client = response.client;
                    
                    // Fill client details
                    $('#client-name').text(client.full_name);
                    $('#client-contact').text(`${client.phone} • ${client.email}`);
                    $('#client-id-number').text(client.id_number);
                    $('#client-license').text(client.license_number);
                    $('#client-address').text(client.address || 'N/A');
                    
                    // Set avatar
                    if (client.profile_photo) {
                        $('#client-avatar').attr('src', client.profile_photo);
                    } else {
                        $('#client-avatar').attr('src', '/img/default-avatar.png');
                    }
                    
                    // Set status badge
                    const statusText = client.status === 'active' ? 
                        '<span class="badge bg-success">Active</span>' : 
                        '<span class="badge bg-danger">Inactive</span>';
                    $('#client-status').html(statusText);
                    
                    // Check for warnings
                    if (client.overdue_contracts > 0 || client.active_contracts > 0) {
                        let alertText = '';
                        if (client.overdue_contracts > 0) {
                            alertText += `Client has ${client.overdue_contracts} overdue contract(s). `;
                        }
                        if (client.active_contracts > 0) {
                            alertText += `Client already has ${client.active_contracts} active contract(s).`;
                        }
                        
                        $('#client-alert-text').text(alertText);
                        $('#client-alert').removeClass('d-none');
                    } else {
                        $('#client-alert').addClass('d-none');
                    }
                    
                    // Show client details
                    $('#client-details').removeClass('d-none');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', {
                    status: xhr.status,
                    statusText: xhr.statusText,
                    responseText: xhr.responseText,
                    error: error
                });
                
                $('#client-details').addClass('d-none');
                
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to load client details',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000
                    });
                } else {
                    alert('Failed to load client details');
                }
            }
        });
    } else {
        $('#client-details').addClass('d-none');
    }
});
        // Car selection change
        $('#car_id').on('change', function() {
            const carId = $(this).val();
            if (carId) {
                // Fetch car details
                $.ajax({
                    url: `/admin/cars/${carId}`,
                    type: 'GET',
                    success: function(response) {
                        if (response.success) {
                            const car = response.car;
                            
                            // Fill car details
                            $('#car-name').text(`${car.brand_name} ${car.model} (${car.year})`);
                            $('#car-plate').text(car.matricule);
                            $('#car-year').text(car.year);
                            $('#car-category').text(car.category ? car.category.name : 'N/A');
                            $('#car-mileage').text(car.mileage.toLocaleString());
                            $('#start_mileage').val(car.mileage);
                            
                            // Set image
                            if (car.featured_image) {
                                $('#car-image').attr('src', car.featured_image);
                            } else {
                                $('#car-image').attr('src', '/img/default-car.png');
                            }
                            
                            // Set daily rate
                            $('#car-daily-rate').text(`${car.daily_rate.toLocaleString()} MAD`);
                            $('#rental_fee').val(car.daily_rate);
                            
                            // Update rental summary
                            updateRentalSummary();
                            
                            // Show car details
                            $('#car-details').removeClass('d-none');
                        }
                    },
                    error: function() {
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
            }
        });
        
        // Refresh clients list
        $('#refresh-clients').on('click', function() {
            const button = $(this);
            const originalText = button.html();
            
            // Show loading
            button.html('<i class="fas fa-spinner fa-spin"></i> Refreshing...');
            button.prop('disabled', true);
            
            // Fetch updated clients list
            $.ajax({
                url: '{{ route("admin.clients.list") }}',
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        // Clear current options
                        const clientSelect = $('#client_id');
                        const selectedValue = clientSelect.val();
                        
                        clientSelect.empty();
                        clientSelect.append('<option value="">-- Select Client --</option>');
                        
                        // Add new options
                        response.clients.forEach(function(client) {
                            const option = new Option(
                                `${client.full_name} (${client.id_number})`, 
                                client.id, 
                                client.id == selectedValue,
                                client.id == selectedValue
                            );
                            clientSelect.append(option);
                        });
                        
                        // Show success message
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
                error: function() {
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
                complete: function() {
                    // Restore button
                    button.html(originalText);
                    button.prop('disabled', false);
                }
            });
        });
        
        // Refresh cars list
        $('#refresh-cars').on('click', function() {
            const button = $(this);
            const originalText = button.html();
            
            // Show loading
            button.html('<i class="fas fa-spinner fa-spin"></i> Refreshing...');
            button.prop('disabled', true);
            
            // Fetch updated cars list
            $.ajax({
                url: '{{ route("admin.api.cars.available") }}',
                                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        // Clear current options
                        const carSelect = $('#car_id');
                        const selectedValue = carSelect.val();
                        
                        carSelect.empty();
                        carSelect.append('<option value="">-- Select Vehicle --</option>');
                        
                        // Add new options
                        response.cars.forEach(function(car) {
                            const option = new Option(
                                `${car.brand_name} ${car.model} (${car.matricule})`, 
                                car.id, 
                                car.id == selectedValue,
                                car.id == selectedValue
                            );
                            carSelect.append(option);
                        });
                        
                        // Show success message
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
                error: function() {
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
                complete: function() {
                    // Restore button
                    button.html(originalText);
                    button.prop('disabled', false);
                }
            });
        });
        
        // Update rental summary on date change
        $('#start_date, #end_date, #rental_fee, #deposit_amount, #discount').on('change input', function() {
            updateRentalSummary();
        });
        
        // Function to update rental summary
        function updateRentalSummary() {
            // Get values
            const startDate = new Date($('#start_date').val());
            const endDate = new Date($('#end_date').val());
            const rentalFee = parseFloat($('#rental_fee').val()) || 0;
            const deposit = parseFloat($('#deposit_amount').val()) || 0;
            const discount = parseFloat($('#discount').val()) || 0;
            
           // Calculate duration in days
           if (!isNaN(startDate.getTime()) && !isNaN(endDate.getTime())) {
                // Add 1 because we count both start and end days
                const diffTime = Math.abs(endDate - startDate);
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
                
                // Update duration in summary
                $('#duration-days').text(`${diffDays} day(s)`);
                
                // Calculate subtotal
                const subtotal = diffDays * rentalFee;
                
                // Update summary values
                $('#summary-daily-rate').text(`${rentalFee.toLocaleString()} MAD`);
                $('#summary-deposit').text(`${deposit.toLocaleString()} MAD`);
                $('#summary-discount').text(`${discount.toLocaleString()} MAD`);
                $('#summary-subtotal').text(`${subtotal.toLocaleString()} MAD`);
                
                // Calculate total
                const total = subtotal - discount;
                $('#summary-total').text(`${total.toLocaleString()} MAD`);
            } else {
                // Reset summary if dates are invalid
                $('#duration-days').text('0 day(s)');
                $('#summary-daily-rate').text(`${rentalFee.toLocaleString()} MAD`);
                $('#summary-deposit').text(`${deposit.toLocaleString()} MAD`);
                $('#summary-discount').text(`${discount.toLocaleString()} MAD`);
                $('#summary-subtotal').text('0 MAD');
                $('#summary-total').text('0 MAD');
            }
        }
        
        // Initialize if we have previously selected values
        if ($('#client_id').val()) {
            $('#client_id').trigger('change');
        }
        
        if ($('#car_id').val()) {
            $('#car_id').trigger('change');
        }
        
        // Initial calculation of rental summary
        updateRentalSummary();
        
        // Form validation before submit
        $('form').on('submit', function(e) {
            const startDate = new Date($('#start_date').val());
            const endDate = new Date($('#end_date').val());
            
            // Validate dates
            if (endDate < startDate) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Date Range',
                    text: 'End date must be on or after the start date',
                });
                return false;
            }
            
            // Check if car is selected
            if (!$('#car_id').val()) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Vehicle Required',
                    text: 'Please select a vehicle for this contract',
                });
                return false;
            }
            
            // Check if client is selected
            if (!$('#client_id').val()) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Client Required',
                    text: 'Please select a client for this contract',
                });
                return false;
            }
            
            // Validate terms checkboxes
            if (!$('#terms_agreed').is(':checked') || !$('#id_verified').is(':checked') || !$('#vehicle_inspected').is(':checked')) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Terms Required',
                    text: 'Please confirm all contract terms before proceeding',
                });
                return false;
            }
            
            // Show loading state
            const submitBtn = $(this).find('button[type="submit"]');
            const originalText = submitBtn.html();
            submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Creating Contract...');
            submitBtn.prop('disabled', true);
            
            // Add event to restore button on browser back
            window.onpageshow = function(event) {
                if (event.persisted) {
                    submitBtn.html(originalText);
                    submitBtn.prop('disabled', false);
                }
            };
        });
    });
</script>
@endpush