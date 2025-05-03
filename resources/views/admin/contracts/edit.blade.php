@extends('admin.layouts.master')

@section('title', 'Edit Contract')

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header pb-0 p-3">
                        <div class="row">
                            <div class="col-6 d-flex align-items-center">
                                <h6 class="mb-0">Edit Contract #CT-{{ str_pad($contract->id, 5, '0', STR_PAD_LEFT) }}</h6>
                            </div>
                            <div class="col-6 text-end">
                                <a href="{{ route('admin.contracts.show', $contract->id) }}"
                                    class="btn bg-gradient-info me-2">
                                    <i class="fas fa-eye"></i> View Contract
                                </a>
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

                        <form action="{{ route('admin.contracts.update', $contract->id) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="row">
                                <!-- Client & Vehicle Info (Read-only) -->
                                <div class="col-md-6">
                                    <div class="card mb-4">
                                        <div class="card-header pb-0 p-3 bg-light">
                                            <h6 class="mb-0">Client Information</h6>
                                        </div>
                                        <div class="card-body p-3">
                                            <div class="d-flex mb-3">
                                                <div class="avatar avatar-xl me-3">
                                                    <img src="{{ $contract->client->profile_photo ?? '/img/default-avatar.png' }}"
                                                        alt="Client Avatar" class="border-radius-lg shadow">
                                                </div>
                                                <div class="d-flex flex-column">
                                                    <h6 class="mb-1 text-dark">{{ $contract->client->full_name }}</h6>
                                                    <span class="text-xs">{{ $contract->client->phone }} •
                                                        {{ $contract->client->email }}</span>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <p class="text-xs mb-1">ID Number: <span
                                                            class="font-weight-bold">{{ $contract->client->id_number }}</span>
                                                    </p>
                                                    <p class="text-xs mb-1">License: <span
                                                            class="font-weight-bold">{{ $contract->client->license_number }}</span>
                                                    </p>
                                                </div>
                                                <div class="col-md-6">
                                                    <p class="text-xs mb-1">Address: <span
                                                            class="font-weight-bold">{{ $contract->client->address ?? 'N/A' }}</span>
                                                    </p>
                                                    <p class="text-xs mb-0">Status:
                                                        <span
                                                            class="badge bg-{{ $contract->client->status === 'active' ? 'success' : 'danger' }}">
                                                            {{ ucfirst($contract->client->status) }}
                                                        </span>
                                                    </p>
                                                </div>
                                            </div>

                                            @if($contract->client->hasOverdueContracts() && $contract->id !== $contract->client->getOverdueContracts()->first()->id)
                                                <div class="alert alert-warning mt-3 mb-0">
                                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                                    <span>Client has {{ $contract->client->getOverdueContracts()->count() }}
                                                        overdue contract(s).</span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="card mb-4">
                                        <div class="card-header pb-0 p-3 bg-light">
                                            <h6 class="mb-0">Vehicle Information</h6>
                                        </div>
                                        <div class="card-body p-3">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <img src="{{ $contract->car->featured_image ?? '/img/default-car.png' }}"
                                                        alt="Vehicle Image" class="w-100 border-radius-lg shadow">
                                                </div>
                                                <div class="col-md-6">
                                                    <h6 class="mb-1 text-dark">{{ $contract->car->brand_name }}
                                                        {{ $contract->car->model }} ({{ $contract->car->year }})</h6>
                                                    <p class="text-xs mb-1">License Plate: <span
                                                            class="font-weight-bold">{{ $contract->car->matricule }}</span>
                                                    </p>
                                                    <p class="text-xs mb-1">Category: <span
                                                            class="font-weight-bold">{{ $contract->car->category ? $contract->car->category->name : 'N/A' }}</span>
                                                    </p>
                                                    <p class="text-xs mb-0">Mileage: <span
                                                            class="font-weight-bold">{{ number_format($contract->car->mileage) }}
                                                            km</span></p>
                                                </div>
                                            </div>

                                            <div class="alert alert-info mt-3 mb-0">
                                                <div class="row">
                                                    <div class="col-6">
                                                        <p class="text-xs mb-1">Current Status</p>
                                                        <span class="badge bg-primary">Rented</span>
                                                    </div>
                                                    <div class="col-6 text-end">
                                                        <p class="text-xs mb-1">Starting Mileage</p>
                                                        <p class="font-weight-bold mb-0">
                                                            {{ number_format($contract->start_mileage) }} km</p>
                                                    </div>
                                                </div>
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
                                                        <label for="start_date" class="form-control-label">Start
                                                            Date</label>
                                                        <input class="form-control" type="date" id="start_date"
                                                            value="{{ $contract->start_date->format('Y-m-d') }}" disabled>
                                                        <small class="text-muted">Start date cannot be changed</small>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group mb-3">
                                                        <label for="end_date" class="form-control-label">End Date <span
                                                                class="text-danger">*</span></label>
                                                        <input class="form-control" type="date" id="end_date"
                                                            name="end_date"
                                                            value="{{ old('end_date', $contract->end_date->format('Y-m-d')) }}"
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
                                                            value="{{ old('rental_fee', $contract->rental_fee) }}" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group mb-3">
                                                        <label for="deposit_amount" class="form-control-label">Security
                                                            Deposit (MAD)</label>
                                                        <input class="form-control" type="number" min="0" step="0.01"
                                                            id="deposit_amount" name="deposit_amount"
                                                            value="{{ old('deposit_amount', $contract->deposit_amount) }}">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group mb-3">
                                                        <label for="discount" class="form-control-label">Discount Amount
                                                            (MAD)</label>
                                                        <input class="form-control" type="number" min="0" step="0.01"
                                                            id="discount" name="discount"
                                                            value="{{ old('discount', $contract->discount) }}">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group mb-3">
                                                        <label for="payment_status" class="form-control-label">Payment
                                                            Status <span class="text-danger">*</span></label>
                                                        <select class="form-select" id="payment_status"
                                                            name="payment_status" required>
                                                            <option value="pending" {{ old('payment_status', $contract->payment_status) == 'pending' ? 'selected' : '' }}>
                                                                Pending</option>
                                                            <option value="partial" {{ old('payment_status', $contract->payment_status) == 'partial' ? 'selected' : '' }}>
                                                                Partial Payment</option>
                                                            <option value="paid" {{ old('payment_status', $contract->payment_status) == 'paid' ? 'selected' : '' }}>
                                                                Fully Paid</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-group mb-3">
                                                <label for="notes" class="form-control-label">Notes</label>
                                                <textarea class="form-control" id="notes" name="notes"
                                                    rows="3">{{ old('notes', $contract->notes) }}</textarea>
                                            </div>

                                            <div class="card bg-light p-3 mb-3">
                                                <h6 class="mb-3">Rental Summary</h6>
                                                <div class="row">
                                                    <div class="col-md-3 mb-2">
                                                        <p class="text-xs text-muted mb-1">Duration</p>
                                                        <p class="font-weight-bold mb-0" id="duration-days">
                                                            {{ $contract->duration_in_days }} day(s)</p>
                                                    </div>
                                                    <div class="col-md-3 mb-2">
                                                        <p class="text-xs text-muted mb-1">Daily Rate</p>
                                                        <p class="font-weight-bold mb-0" id="summary-daily-rate">
                                                            {{ number_format($contract->rental_fee, 2) }} MAD</p>
                                                    </div>
                                                    <div class="col-md-3 mb-2">
                                                        <p class="text-xs text-muted mb-1">Security Deposit</p>
                                                        <p class="font-weight-bold mb-0" id="summary-deposit">
                                                            {{ number_format($contract->deposit_amount, 2) }} MAD</p>
                                                    </div>
                                                    <div class="col-md-3 mb-2">
                                                        <p class="text-xs text-muted mb-1">Discount</p>
                                                        <p class="font-weight-bold mb-0" id="summary-discount">
                                                            {{ number_format($contract->discount, 2) }} MAD</p>
                                                    </div>
                                                </div>
                                                <hr>
                                                <div class="row">
                                                    <div class="col-6">
                                                        <p class="text-xs text-muted mb-1">Subtotal</p>
                                                        <p class="font-weight-bold mb-0" id="summary-subtotal">
                                                            {{ number_format($contract->total_amount + $contract->discount, 2) }}
                                                            MAD</p>
                                                    </div>
                                                    <div class="col-6 text-end">
                                                        <p class="text-xs text-muted mb-1">Total Amount</p>
                                                        <p class="text-lg font-weight-bold text-success mb-0"
                                                            id="summary-total">
                                                            {{ number_format($contract->total_amount, 2) }} MAD</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="text-center mt-4">
                                <button type="submit" class="btn bg-gradient-primary btn-lg w-50">Update Contract</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        $(document).ready(function () {
            // Update rental summary on field changes
            $('#end_date, #rental_fee, #deposit_amount, #discount').on('change input', function () {
                updateRentalSummary();
            });

            // Function to update rental summary
            function updateRentalSummary() {
                // Get values
                const startDate = new Date('{{ $contract->start_date->format('Y-m-d') }}');
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

            // Form validation before submit
            $('form').on('submit', function (e) {
                const startDate = new Date('{{ $contract->start_date->format('Y-m-d') }}');
                const endDate = new Date($('#end_date').val());

                // Validate end date
                if (endDate < startDate) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid Date Range',
                        text: 'End date must be on or after the start date',
                    });
                    return false;
                }

                // Show loading state
                const submitBtn = $(this).find('button[type="submit"]');
                const originalText = submitBtn.html();
                submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Updating Contract...');
                submitBtn.prop('disabled', true);

                // Add event to restore button on browser back
                window.onpageshow = function (event) {
                    if (event.persisted) {
                        submitBtn.html(originalText);
                        submitBtn.prop('disabled', false);
                    }
                };
            });
        });
    </script>
@endpush