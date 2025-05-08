@extends('admin.layouts.master')

@section('title', 'Contract Details')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0 p-3">
                    <div class="row">
                        <div class="col-6 d-flex align-items-center">
                            <h6 class="mb-0">Contract #CT-{{ str_pad($contract->id, 5, '0', STR_PAD_LEFT) }}</h6>
                        </div>
                        <div class="col-6 text-end">
                            <div class="btn-group">
                                @if($contract->status === 'active')
                                    <a href="{{ route('admin.contracts.edit', $contract->id) }}" class="btn bg-gradient-primary me-2">
                                        <i class="fas fa-edit"></i> Edit Contract
                                    </a>
                                    <button type="button" class="btn bg-gradient-success me-2 complete-contract" data-id="{{ $contract->id }}">
                                        <i class="fas fa-check"></i> Complete
                                    </button>
                                    <button type="button" class="btn bg-gradient-warning me-2 extend-contract" data-id="{{ $contract->id }}">
                                        <i class="fas fa-calendar-plus"></i> Extend
                                    </button>
                                    <button type="button" class="btn bg-gradient-danger me-2 cancel-contract" data-id="{{ $contract->id }}">
                                        <i class="fas fa-times"></i> Cancel
                                    </button>
                                @endif
                                <button type="button" class="btn bg-gradient-dark me-2" onclick="window.print()">
                                    <i class="fas fa-print"></i> Print
                                </button>
                                <a href="{{ route('admin.contracts.index') }}" class="btn bg-gradient-secondary">
                                    <i class="fas fa-arrow-left"></i> Back
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        <!-- Contract Status Banner -->
                        <div class="col-12 mb-4">
                            @php
                                $statusClass = [
                                    'active' => 'success',
                                    'completed' => 'info',
                                    'cancelled' => 'danger'
                                ][$contract->status];
                                
                                $statusIcon = [
                                    'active' => 'fas fa-check-circle',
                                    'completed' => 'fas fa-flag-checkered',
                                    'cancelled' => 'fas fa-ban'
                                ][$contract->status];
                                
                                $statusMessage = [
                                    'active' => 'This contract is currently active. The vehicle is rented.',
                                    'completed' => 'This contract has been completed. The vehicle has been returned.',
                                    'cancelled' => 'This contract has been cancelled.'
                                ][$contract->status];
                            @endphp
                            
                            <div class="alert alert-{{ $statusClass }} d-flex align-items-center" role="alert">
                                <i class="{{ $statusIcon }} me-2"></i>
                                <div>
                                    <strong>{{ ucfirst($contract->status) }}</strong> - {{ $statusMessage }}
                                    
                                    @if($contract->status === 'active' && $contract->isOverdue())
                                        <span class="ms-3 badge bg-danger">
                                            Overdue by {{ $contract->getOverdueDaysAttribute() }} day(s)
                                        </span>
                                    @elseif($contract->status === 'active' && $contract->isEndingSoon())
                                        <span class="ms-3 badge bg-warning">
                                            Ending in {{ now()->diffInDays($contract->end_date) }} day(s)
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <!-- Contract Info -->
                        <div class="col-md-4">
                            <div class="card mb-4">
                                <div class="card-header p-3 bg-light">
                                    <div class="row">
                                        <div class="col-8">
                                            <h6 class="mb-0">Contract Details</h6>
                                        </div>
                                        <div class="col-4 text-end">
                                            <span class="badge bg-{{ $statusClass }}">{{ ucfirst($contract->status) }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body p-3">
                                    <div class="timeline timeline-one-side">
                                        <div class="timeline-block mb-3">
                                            <span class="timeline-step bg-primary">
                                                <i class="fas fa-calendar-alt"></i>
                                            </span>
                                            <div class="timeline-content">
                                                <h6 class="text-dark text-sm font-weight-bold mb-0">Start Date</h6>
                                                <p class="text-secondary font-weight-bold text-xs mt-1 mb-0">
                                                    {{ $contract->start_date->format('M d, Y') }}
                                                </p>
                                            </div>
                                        </div>
                                        <div class="timeline-block mb-3">
                                            <span class="timeline-step bg-primary">
                                                <i class="fas fa-calendar-check"></i>
                                            </span>
                                            <div class="timeline-content">
                                                <h6 class="text-dark text-sm font-weight-bold mb-0">End Date</h6>
                                                <p class="text-secondary font-weight-bold text-xs mt-1 mb-0">
                                                    {{ $contract->end_date->format('M d, Y') }}
                                                </p>
                                            </div>
                                        </div>
                                        <div class="timeline-block mb-3">
                                            <span class="timeline-step bg-primary">
                                                <i class="fas fa-hourglass-half"></i>
                                            </span>
                                            <div class="timeline-content">
                                                <h6 class="text-dark text-sm font-weight-bold mb-0">Duration</h6>
                                                <p class="text-secondary font-weight-bold text-xs mt-1 mb-0">
                                                    {{ $contract->duration_in_days }} day(s)
                                                    @if($contract->extension_days > 0)
                                                        <span class="text-success ms-2">(Extended by {{ $contract->extension_days }} day(s))</span>
                                                    @endif
                                                </p>
                                            </div>
                                        </div>
                                        <div class="timeline-block mb-3">
                                            <span class="timeline-step bg-info">
                                                <i class="fas fa-dollar-sign"></i>
                                            </span>
                                            <div class="timeline-content">
                                                <h6 class="text-dark text-sm font-weight-bold mb-0">Daily Rate</h6>
                                                <p class="text-secondary font-weight-bold text-xs mt-1 mb-0">
                                                    {{ number_format($contract->rental_fee, 2) }} MAD per day
                                                </p>
                                            </div>
                                        </div>
                                        <div class="timeline-block mb-3">
                                            <span class="timeline-step bg-success">
                                                <i class="fas fa-money-bill-wave"></i>
                                            </span>
                                            <div class="timeline-content">
                                                <h6 class="text-dark text-sm font-weight-bold mb-0">Total Amount</h6>
                                                <p class="text-secondary font-weight-bold text-xs mt-1 mb-0">
                                                    {{ number_format($contract->total_amount, 2) }} MAD
                                                    @if($contract->discount > 0)
                                                        <span class="text-success ms-2">(Discount: {{ number_format($contract->discount, 2) }} MAD)</span>
                                                    @endif
                                                </p>
                                            </div>
                                        </div>
                                        <div class="timeline-block mb-3">
                                            <span class="timeline-step bg-warning">
                                                <i class="fas fa-hand-holding-usd"></i>
                                            </span>
                                            <div class="timeline-content">
                                                <h6 class="text-dark text-sm font-weight-bold mb-0">Payment Status</h6>
                                                <p class="text-secondary font-weight-bold text-xs mt-1 mb-0">
                                                    @php
                                                        $paymentClass = [
                                                            'pending' => 'warning',
                                                            'partial' => 'info',
                                                            'paid' => 'success'
                                                        ][$contract->payment_status];
                                                    @endphp
                                                    <span class="badge bg-{{ $paymentClass }}">{{ ucfirst($contract->payment_status) }}</span>
                                                    
                                                    @if($contract->deposit_amount > 0)
                                                        <span class="ms-2">Deposit: {{ number_format($contract->deposit_amount, 2) }} MAD</span>
                                                    @endif
                                                </p>
                                            </div>
                                        </div>
                                        <div class="timeline-block">
                                            <span class="timeline-step bg-dark">
                                                <i class="fas fa-info"></i>
                                            </span>
                                            <div class="timeline-content">
                                                <h6 class="text-dark text-sm font-weight-bold mb-0">Contract ID</h6>
                                                <p class="text-secondary font-weight-bold text-xs mt-1 mb-0">
                                                    CT-{{ str_pad($contract->id, 5, '0', STR_PAD_LEFT) }}
                                                </p>
                                                <p class="text-secondary text-xs mt-1 mb-0">
                                                    Created {{ $contract->created_at->format('M d, Y') }}
                                                </p>
                                                @if($contract->created_at != $contract->updated_at)
                                                    <p class="text-secondary text-xs mt-1 mb-0">
                                                        Last Updated {{ $contract->updated_at->format('M d, Y') }}
                                                    </p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Client Info -->
                        <div class="col-md-4">
                            <div class="card mb-4">
                                <div class="card-header p-3 bg-light">
                                    <h6 class="mb-0">Client Information</h6>
                                </div>
                                <div class="card-body p-3">
                                    <div class="d-flex mb-3">
                                        <div class="avatar avatar-xl me-3">
                                            <img src="{{ $contract->client->photo_url ?? asset('/img/default-avatar.png') }}" alt="Client Avatar" class="border-radius-lg shadow">
                                        </div>
                                        <div class="d-flex flex-column">
                                            <h6 class="mb-1 text-dark">{{ $contract->client->full_name }}</h6>
                                            <span class="text-xs">{{ $contract->client->phone }}</span>
                                            <span class="text-xs">{{ $contract->client->email }}</span>
                                        </div>
                                    </div>
                                    
                                    <div class="row mt-3">
                                        <div class="col-6">
                                            <h6 class="text-xs text-muted mb-1">ID Number</h6>
                                            <p class="text-sm mb-2">{{ $contract->client->id_number }}</p>
                                            
                                            <h6 class="text-xs text-muted mb-1">License Number</h6>
                                            <p class="text-sm mb-2">{{ $contract->client->license_number }}</p>
                                            
                                            <h6 class="text-xs text-muted mb-1">License Expiry</h6>
                                            <p class="text-sm mb-0">
                                                {{ $contract->client->license_expiry ? $contract->client->license_expiry->format('M d, Y') : 'N/A' }}
                                            </p>
                                        </div>
                                        <div class="col-6">
                                            <h6 class="text-xs text-muted mb-1">Address</h6>
                                            <p class="text-sm mb-2">{{ $contract->client->address ?? 'N/A' }}</p>
                                            
                                            <h6 class="text-xs text-muted mb-1">Status</h6>
                                            <p class="text-sm mb-2">
                                                <span class="badge bg-{{ $contract->client->status === 'active' ? 'success' : 'danger' }}">
                                                    {{ ucfirst($contract->client->status) }}
                                                </span>
                                            </p>
                                            
                                            <a href="{{ route('admin.clients.show', $contract->client_id) }}" class="btn btn-sm bg-gradient-info mt-2">
                                                <i class="fas fa-user"></i> View Client Profile
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Vehicle Info -->
                        <div class="col-md-4">
                            <div class="card mb-4">
                                <div class="card-header p-3 bg-light">
                                    <h6 class="mb-0">Vehicle Information</h6>
                                </div>
                                <div class="card-body p-3">
                                    <div class="text-center mb-3">
                                        <img src="{{ Storage::url($contract->car->main_image) ?? asset('images/default-car.png') }}" alt="Vehicle Image" class="img-fluid border-radius-lg shadow" style="max-height: 150px;">
                                        {{-- {{ Storage::url($car->main_image) }}" alt="{{ $car->name }} --}}
                                    </div>
                                    
                                    <h6 class="text-center text-dark mb-3">
                                        {{ $contract->car->brand_name }} {{ $contract->car->model }} ({{ $contract->car->year }})
                                    </h6>
                                    
                                    <div class="row">
                                        <div class="col-6">
                                            <h6 class="text-xs text-muted mb-1">License Plate</h6>
                                            <p class="text-sm mb-2">{{ $contract->car->matricule }}</p>
                                            
                                            <h6 class="text-xs text-muted mb-1">Category</h6>
                                            <p class="text-sm mb-2">{{ $contract->car->category ? $contract->car->category->name : 'N/A' }}</p>
                                        </div>
                                        <div class="col-6">
                                            <h6 class="text-xs text-muted mb-1">Start Mileage</h6>
                                            <p class="text-sm mb-2">{{ number_format($contract->start_mileage) }} km</p>
                                            
                                            @if($contract->end_mileage)
                                                <h6 class="text-xs text-muted mb-1">End Mileage</h6>
                                                <p class="text-sm mb-2">{{ number_format($contract->end_mileage) }} km</p>
                                                
                                                <h6 class="text-xs text-muted mb-1">Distance Traveled</h6>
                                                <p class="text-sm mb-2">{{ number_format($contract->end_mileage - $contract->start_mileage) }} km</p>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <a href="{{ route('admin.cars.show', $contract->car_id) }}" class="btn btn-sm bg-gradient-info w-100 mt-2">
                                        <i class="fas fa-car"></i> View Vehicle Details
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Notes -->
                        @if($contract->notes)
                            <div class="col-12">
                                <div class="card mb-4">
                                    <div class="card-header p-3 bg-light">
                                        <h6 class="mb-0">Notes</h6>
                                    </div>
                                    <div class="card-body p-3">
                                        <p class="mb-0">{{ $contract->notes }}</p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Complete Contract Modal -->
<div class="modal fade" id="completeContractModal" tabindex="-1" aria-labelledby="completeContractModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="completeContractModalLabel">Complete Contract</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="completeContractForm">
                @csrf
                <input type="hidden" id="complete_contract_id" name="contract_id" value="{{ $contract->id }}">
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <i class="fas fa-check-circle text-success fa-4x mb-3"></i>
                        <p>You are about to complete this contract and return the vehicle.</p>
                    </div>
                    <div class="form-group mb-3">
                        <label for="end_mileage" class="form-control-label">End Mileage (km)<span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="end_mileage" name="end_mileage" required min="{{ $contract->start_mileage }}">
                        <div class="invalid-feedback" id="end_mileage-error"></div>
                        <small class="text-muted">Enter the final odometer reading of the vehicle</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn bg-gradient-success" id="completeContractBtn">Complete Contract</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Cancel Contract Modal -->
<div class="modal fade" id="cancelContractModal" tabindex="-1" aria-labelledby="cancelContractModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cancelContractModalLabel">Cancel Contract</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="cancelContractForm">
                @csrf
                <input type="hidden" id="cancel_contract_id" name="contract_id" value="{{ $contract->id }}">
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <i class="fas fa-times-circle text-danger fa-4x mb-3"></i>
                        <p>Are you sure you want to cancel this contract?</p>
                        <p class="text-danger">This action will return the vehicle to available status.</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">No, Keep Contract</button>
                    <button type="submit" class="btn bg-gradient-danger" id="cancelContractBtn">Yes, Cancel Contract</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Extend Contract Modal -->
<div class="modal fade" id="extendContractModal" tabindex="-1" aria-labelledby="extendContractModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="extendContractModalLabel">Extend Contract</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="extendContractForm">
                @csrf
                <input type="hidden" id="extend_contract_id" name="contract_id" value="{{ $contract->id }}">
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <i class="fas fa-calendar-plus text-warning fa-4x mb-3"></i>
                        <p>Extend the rental period for this contract.</p>
                    </div>
                    <div class="form-group mb-3">
                        <label for="extension_days" class="form-control-label">Extension (Days)<span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="extension_days" name="extension_days" required min="1" value="1">
                        <div class="invalid-feedback" id="extension_days-error"></div>
                    </div>
                    <div class="alert alert-info d-none" id="extension-details">
                        <p class="mb-1">Current End Date: <span>{{ $contract->end_date->format('M d, Y') }}</span></p>
                        <p class="mb-1">New End Date: <span id="new-end-date"></span></p>
                        <p class="mb-1">Additional Cost: <span id="additional-cost"></span> MAD</p>
                        <p class="mb-0">New Total Amount: <span id="new-total-amount"></span> MAD</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn bg-gradient-warning" id="extendContractBtn">Extend Contract</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('css')
    <style>
        @media print {
            body * {
                visibility: hidden;
            }
            .card, .card * {
                visibility: visible;
            }
            .card {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }
            .btn, .btn-group, button {
                display: none !important;
            }
        }
        
        .timeline {
            margin-top: 1rem;
        }
        
        .timeline .timeline-block {
            display: flex;
            margin-bottom: 1.5rem;
        }
        
        .timeline .timeline-block:last-child {
            margin-bottom: 0;
        }
        
        .timeline .timeline-step {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            margin-right: 1rem;
        }
        
        .timeline .timeline-step i {
            font-size: 1rem;
        }
        
        .timeline .timeline-content {
            width: calc(100% - 60px);
            position: relative;
        }
    </style>
@endpush

@push('js')
<script>
    $(document).ready(function() {
        // Complete Contract button
        $('.complete-contract').on('click', function() {
            const contractId = $(this).data('id');
            $('#complete_contract_id').val(contractId);
            const modal = new bootstrap.Modal(document.getElementById('completeContractModal'));
            modal.show();
        });

        // Complete Contract form submission
        $('#completeContractForm').on('submit', function(e) {
            e.preventDefault();
            const contractId = $('#complete_contract_id').val();
            const endMileage = $('#end_mileage').val();
            const completeBtn = $('#completeContractBtn');
            const originalBtnText = completeBtn.html();

            // Show loading state
            completeBtn.html('<i class="fas fa-spinner fa-spin"></i> Processing...');
            completeBtn.prop('disabled', true);

            // Send AJAX request
            $.ajax({
                url: `/admin/contracts/${contractId}/complete`,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    end_mileage: endMileage
                },
                success: function(response) {
                    if (response.success) {
                        // Hide modal
                        bootstrap.Modal.getInstance(document.getElementById('completeContractModal')).hide();
                        
                        // Show success message
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        }).then(function() {
                            // Reload page
                            window.location.reload();
                        });
                    } else {
                        // Show error message
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message
                        });
                    }
                },
                error: function(xhr) {
                    // Show validation errors if any
                    if (xhr.status === 422) {
                        const errors = xhr.responseJSON.errors;
                        if (errors.end_mileage) {
                            $('#end_mileage').addClass('is-invalid');
                            $('#end_mileage-error').text(errors.end_mileage[0]);
                        }
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'An error occurred while completing the contract.'
                        });
                    }
                },
                complete: function() {
                    // Restore button state
                    completeBtn.html(originalBtnText);
                    completeBtn.prop('disabled', false);
                }
            });
        });

        // Cancel Contract button
        $('.cancel-contract').on('click', function() {
            const contractId = $(this).data('id');
            $('#cancel_contract_id').val(contractId);
            const modal = new bootstrap.Modal(document.getElementById('cancelContractModal'));
            modal.show();
        });

        // Cancel Contract form submission
        $('#cancelContractForm').on('submit', function(e) {
            e.preventDefault();
            const contractId = $('#cancel_contract_id').val();
            const cancelBtn = $('#cancelContractBtn');
            const originalBtnText = cancelBtn.html();

            // Show loading state
            cancelBtn.html('<i class="fas fa-spinner fa-spin"></i> Processing...');
            cancelBtn.prop('disabled', true);

            // Send AJAX request
            $.ajax({
                url: `/admin/contracts/${contractId}/cancel`,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        // Hide modal
                        bootstrap.Modal.getInstance(document.getElementById('cancelContractModal')).hide();
                        
                        // Show success message
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        }).then(function() {
                            // Reload page
                            window.location.reload();
                        });
                    } else {
                        // Show error message
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'An error occurred while cancelling the contract.'
                    });
                },
                complete: function() {
                    // Restore button state
                    cancelBtn.html(originalBtnText);
                    cancelBtn.prop('disabled', false);
                }
            });
        });

        // Extend Contract button
        $('.extend-contract').on('click', function() {
            const contractId = $(this).data('id');
            $('#extend_contract_id').val(contractId);
            $('#extension-details').addClass('d-none');
            
            // Show the modal
            const modal = new bootstrap.Modal(document.getElementById('extendContractModal'));
            modal.show();
            
            // Trigger change event to calculate extension details
            $('#extension_days').trigger('change');
        });

        // Preview extension details
        $('#extension_days').on('change', function() {
            const days = $(this).val();
            
            if (days > 0) {
                // Calculate new end date
                const currentEndDate = new Date('{{ $contract->end_date->format('Y-m-d') }}');
                const newEndDate = new Date(currentEndDate);
                newEndDate.setDate(newEndDate.getDate() + parseInt(days));
                
                // Format date
                const formattedDate = newEndDate.toLocaleDateString('en-US', {
                    month: 'short',
                    day: 'numeric',
                    year: 'numeric'
                });
                
                // Calculate additional cost
                const additionalCost = {{ $contract->rental_fee }} * days;
                
                // Update preview
                $('#new-end-date').text(formattedDate);
                $('#additional-cost').text(additionalCost.toLocaleString());
                $('#new-total-amount').text(({{ $contract->total_amount }} + additionalCost).toLocaleString());
                
                // Show preview
                $('#extension-details').removeClass('d-none');
            } else {
                $('#extension-details').addClass('d-none');
            }
        });

        // Extend Contract form submission
        $('#extendContractForm').on('submit', function(e) {
            e.preventDefault();
            const contractId = $('#extend_contract_id').val();
            const extensionDays = $('#extension_days').val();
            const extendBtn = $('#extendContractBtn');
            const originalBtnText = extendBtn.html();

            // Show loading state
            extendBtn.html('<i class="fas fa-spinner fa-spin"></i> Processing...');
            extendBtn.prop('disabled', true);

            // Send AJAX request
            $.ajax({
                url: `/admin/contracts/${contractId}/extend`,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    extension_days: extensionDays
                },
                success: function(response) {
                    if (response.success) {
                        // Hide modal
                        bootstrap.Modal.getInstance(document.getElementById('extendContractModal')).hide();
                        
                        // Show success message
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        }).then(function() {
                            // Reload page
                            window.location.reload();
                        });
                    } else {
                        // Show error message
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message
                        });
                    }
                },
                error: function(xhr) {
                    // Show validation errors if any
                    if (xhr.status === 422) {
                        const errors = xhr.responseJSON.errors;
                        if (errors.extension_days) {
                            $('#extension_days').addClass('is-invalid');
                            $('#extension_days-error').text(errors.extension_days[0]);
                        }
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'An error occurred while extending the contract.'
                        });
                    }
                },
                complete: function() {
                    // Restore button state
                    extendBtn.html(originalBtnText);
                    extendBtn.prop('disabled', false);
                }
            });
        });
    });
</script>
@endpush