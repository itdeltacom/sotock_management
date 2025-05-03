@extends('admin.layouts.master')

@section('title', 'Contract Management')

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                <div class="card">
                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-8">
                                <div class="numbers">
                                    <p class="text-sm mb-0 text-uppercase font-weight-bold">Active Contracts</p>
                                    <h5 class="font-weight-bolder" id="active-counter">
                                        <div class="spinner-border spinner-border-sm text-primary" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                    </h5>
                                    <p class="mb-0">
                                        <span class="text-success text-sm font-weight-bolder">
                                            <i class="fas fa-car"></i> Currently rented
                                        </span>
                                    </p>
                                </div>
                            </div>
                            <div class="col-4 text-end">
                                <div class="icon icon-shape bg-gradient-primary shadow-primary text-center rounded-circle">
                                    <i class="fas fa-file-signature text-lg opacity-10" aria-hidden="true"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                <div class="card">
                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-8">
                                <div class="numbers">
                                    <p class="text-sm mb-0 text-uppercase font-weight-bold">Ending Soon</p>
                                    <h5 class="font-weight-bolder" id="ending-soon-counter">
                                        <div class="spinner-border spinner-border-sm text-warning" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                    </h5>
                                    <p class="mb-0">
                                        <span class="text-warning text-sm font-weight-bolder">
                                            <i class="fas fa-clock"></i> Within 2 days
                                        </span>
                                    </p>
                                </div>
                            </div>
                            <div class="col-4 text-end">
                                <div class="icon icon-shape bg-gradient-warning shadow-warning text-center rounded-circle">
                                    <i class="fas fa-hourglass-end text-lg opacity-10" aria-hidden="true"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                <div class="card">
                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-8">
                                <div class="numbers">
                                    <p class="text-sm mb-0 text-uppercase font-weight-bold">Overdue</p>
                                    <h5 class="font-weight-bolder" id="overdue-counter">
                                        <div class="spinner-border spinner-border-sm text-danger" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                    </h5>
                                    <p class="mb-0">
                                        <span class="text-danger text-sm font-weight-bolder">
                                            <i class="fas fa-exclamation-circle"></i> Need attention
                                        </span>
                                    </p>
                                </div>
                            </div>
                            <div class="col-4 text-end">
                                <div class="icon icon-shape bg-gradient-danger shadow-danger text-center rounded-circle">
                                    <i class="fas fa-exclamation-triangle text-lg opacity-10" aria-hidden="true"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                <div class="card">
                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-8">
                                <div class="numbers">
                                    <p class="text-sm mb-0 text-uppercase font-weight-bold">Revenue This Month</p>
                                    <h5 class="font-weight-bolder" id="revenue-counter">
                                        <div class="spinner-border spinner-border-sm text-success" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                    </h5>
                                    <p class="mb-0">
                                        <span class="text-success text-sm font-weight-bolder">
                                            <i class="fas fa-money-bill"></i> MAD
                                        </span>
                                    </p>
                                </div>
                            </div>
                            <div class="col-4 text-end">
                                <div class="icon icon-shape bg-gradient-success shadow-success text-center rounded-circle">
                                    <i class="fas fa-money-bill-wave text-lg opacity-10" aria-hidden="true"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header pb-0 p-3">
                        <div class="row">
                            <div class="col-6 d-flex align-items-center">
                                <h6 class="mb-0">Contracts</h6>
                            </div>
                            <div class="col-6 text-end">
                                <a href="{{ route('admin.contracts.create') }}" class="btn bg-gradient-primary">
                                    <i class="fas fa-plus"></i> New Contract
                                </a>
                                <div class="btn-group ms-2">
                                    <button type="button" class="btn bg-gradient-info dropdown-toggle"
                                        data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-filter"></i> View
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><a class="dropdown-item" href="{{ route('admin.contracts.index') }}">All
                                                Contracts</a></li>
                                        <li><a class="dropdown-item"
                                                href="{{ route('admin.contracts.ending-soon') }}">Ending Soon</a></li>
                                        <li><a class="dropdown-item"
                                                href="{{ route('admin.contracts.overdue') }}">Overdue</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body px-0 pt-0 pb-2">
                        <div class="table-responsive p-0">
                            <table class="table align-items-center mb-0" id="contracts-table" width="100%">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Contract ID</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Client</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Vehicle</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Period</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Duration</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Status</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Payment</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Total</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- DataTables will populate this -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Complete Contract Modal -->
    <div class="modal fade" id="completeContractModal" tabindex="-1" aria-labelledby="completeContractModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="completeContractModalLabel">Complete Contract</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="completeContractForm">
                    @csrf
                    <input type="hidden" id="complete_contract_id" name="contract_id">
                    <div class="modal-body">
                        <div class="text-center mb-4">
                            <i class="fas fa-check-circle text-success fa-4x mb-3"></i>
                            <p>You are about to complete this contract and return the vehicle.</p>
                        </div>
                        <div class="form-group mb-3">
                            <label for="end_mileage" class="form-control-label">End Mileage (km)<span
                                    class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="end_mileage" name="end_mileage" required min="0">
                            <div class="invalid-feedback" id="end_mileage-error"></div>
                            <small class="text-muted">Enter the final odometer reading of the vehicle</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn bg-gradient-success" id="completeContractBtn">Complete
                            Contract</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Cancel Contract Modal -->
    <div class="modal fade" id="cancelContractModal" tabindex="-1" aria-labelledby="cancelContractModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cancelContractModalLabel">Cancel Contract</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="cancelContractForm">
                    @csrf
                    <input type="hidden" id="cancel_contract_id" name="contract_id">
                    <div class="modal-body">
                        <div class="text-center mb-4">
                            <i class="fas fa-times-circle text-danger fa-4x mb-3"></i>
                            <p>Are you sure you want to cancel this contract?</p>
                            <p class="text-danger">This action will return the vehicle to available status.</p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">No, Keep
                            Contract</button>
                        <button type="submit" class="btn bg-gradient-danger" id="cancelContractBtn">Yes, Cancel
                            Contract</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Extend Contract Modal -->
    <div class="modal fade" id="extendContractModal" tabindex="-1" aria-labelledby="extendContractModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="extendContractModalLabel">Extend Contract</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="extendContractForm">
                    @csrf
                    <input type="hidden" id="extend_contract_id" name="contract_id">
                    <div class="modal-body">
                        <div class="text-center mb-4">
                            <i class="fas fa-calendar-plus text-warning fa-4x mb-3"></i>
                            <p>Extend the rental period for this contract.</p>
                        </div>
                        <div class="form-group mb-3">
                            <label for="extension_days" class="form-control-label">Extension (Days)<span
                                    class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="extension_days" name="extension_days" required
                                min="1" value="1">
                            <div class="invalid-feedback" id="extension_days-error"></div>
                        </div>
                        <div class="alert alert-info d-none" id="extension-details">
                            <p class="mb-1">New End Date: <span id="new-end-date"></span></p>
                            <p class="mb-1">Additional Cost: <span id="additional-cost"></span> MAD</p>
                            <p class="mb-0">New Total Amount: <span id="new-total-amount"></span> MAD</p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn bg-gradient-warning" id="extendContractBtn">Extend
                            Contract</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteContractModal" tabindex="-1" aria-labelledby="deleteContractModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteContractModalLabel">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="py-3 text-center">
                        <i class="fas fa-exclamation-triangle text-warning fa-3x mb-3"></i>
                        <p>Are you sure you want to delete this contract? This action cannot be undone.</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn bg-gradient-danger" id="confirmDeleteContractBtn">Delete</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <style>
        .badge {
            font-size: 0.75rem;
            font-weight: 700;
            padding: 0.35em 0.65em;
        }
    </style>
@endpush

@push('js')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function () {
            // Initialize DataTable
            const contractsTable = $('#contracts-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.contracts.datatable') }}",
                columns: [
                    {
                        data: 'id',
                        name: 'id',
                        render: function (data) {
                            return `<span class="text-xs font-weight-bold">CT-${String(data).padStart(5, '0')}</span>`;
                        }
                    },
                    { data: 'client_name', name: 'client_name' },
                    { data: 'car_details', name: 'car_details' },
                    {
                        data: null,
                        name: 'period',
                        render: function (data) {
                            const startDate = new Date(data.start_date).toLocaleDateString();
                            const endDate = new Date(data.end_date).toLocaleDateString();
                            return `<span class="text-xs">${startDate} to ${endDate}</span>`;
                        }
                    },
                    { data: 'duration', name: 'duration' },
                    { data: 'status_badge', name: 'status' },
                    { data: 'payment_badge', name: 'payment_status' },
                    {
                        data: 'total_amount',
                        name: 'total_amount',
                        render: function (data) {
                            return `<span class="text-xs font-weight-bold">${parseFloat(data).toLocaleString()} MAD</span>`;
                        }
                    },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false }
                ],
                order: [[0, 'desc']], // Sort by ID by default
                pageLength: 25,
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search contracts",
                    paginate: {
                        previous: '<i class="fas fa-chevron-left"></i>',
                        next: '<i class="fas fa-chevron-right"></i>'
                    }
                }
            });

            // Load contract statistics
            loadContractStats();

            // Complete Contract button
            $(document).on('click', '.complete-contract', function () {
                const contractId = $(this).data('id');
                $('#complete_contract_id').val(contractId);
                const modal = new bootstrap.Modal(document.getElementById('completeContractModal'));
                modal.show();
            });

            // Complete Contract form submission
            $('#completeContractForm').on('submit', function (e) {
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
                    success: function (response) {
                        if (response.success) {
                            // Hide modal
                            bootstrap.Modal.getInstance(document.getElementById('completeContractModal')).hide();

                            // Show success message
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: response.message,
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 3000
                            });

                            // Reload DataTable
                            contractsTable.ajax.reload();

                            // Reload contract stats
                            loadContractStats();
                        } else {
                            // Show error message
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
                                text: 'An error occurred while completing the contract.',
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 3000
                            });
                        }
                    },
                    complete: function () {
                        // Restore button state
                        completeBtn.html(originalBtnText);
                        completeBtn.prop('disabled', false);
                    }
                });
            });

            // Cancel Contract button
            $(document).on('click', '.cancel-contract', function () {
                const contractId = $(this).data('id');
                $('#cancel_contract_id').val(contractId);
                const modal = new bootstrap.Modal(document.getElementById('cancelContractModal'));
                modal.show();
            });

            // Cancel Contract form submission
            $('#cancelContractForm').on('submit', function (e) {
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
                    success: function (response) {
                        if (response.success) {
                            // Hide modal
                            bootstrap.Modal.getInstance(document.getElementById('cancelContractModal')).hide();

                            // Show success message
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: response.message,
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 3000
                            });

                            // Reload DataTable
                            contractsTable.ajax.reload();

                            // Reload contract stats
                            loadContractStats();
                        } else {
                            // Show error message
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
                    error: function () {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'An error occurred while cancelling the contract.',
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000
                        });
                    },
                    complete: function () {
                        // Restore button state
                        cancelBtn.html(originalBtnText);
                        cancelBtn.prop('disabled', false);
                    }
                });
            });

            // Extend Contract button
            $(document).on('click', '.extend-contract', function () {
                const contractId = $(this).data('id');
                $('#extend_contract_id').val(contractId);
                $('#extension-details').addClass('d-none');
                const modal = new bootstrap.Modal(document.getElementById('extendContractModal'));
                modal.show();
            });

            // Preview extension details
            $('#extension_days').on('change', function () {
                const contractId = $('#extend_contract_id').val();
                const days = $(this).val();

                if (contractId && days > 0) {
                    $.ajax({
                        url: `/admin/contracts/${contractId}`,
                        type: 'GET',
                        dataType: 'json',
                        success: function (response) {
                            if (response.success) {
                                const contract = response.contract;

                                // Calculate new end date
                                const endDate = new Date(contract.end_date);
                                endDate.setDate(endDate.getDate() + parseInt(days));

                                // Calculate additional cost
                                const additionalCost = contract.rental_fee * days;

                                // Update preview
                                $('#new-end-date').text(endDate.toLocaleDateString());
                                $('#additional-cost').text(additionalCost.toLocaleString());
                                $('#new-total-amount').text((parseFloat(contract.total_amount) + additionalCost).toLocaleString());

                                // Show preview
                                $('#extension-details').removeClass('d-none');
                            }
                        }
                    });
                }
            });

            // Extend Contract form submission
            $('#extendContractForm').on('submit', function (e) {
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
                    success: function (response) {
                        if (response.success) {
                            // Hide modal
                            bootstrap.Modal.getInstance(document.getElementById('extendContractModal')).hide();

                            // Show success message
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: response.message,
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 3000
                            });

                            // Reload DataTable
                            contractsTable.ajax.reload();
                        } else {
                            // Show error message
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
                                text: 'An error occurred while extending the contract.',
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 3000
                            });
                        }
                    },
                    complete: function () {
                        // Restore button state
                        extendBtn.html(originalBtnText);
                        extendBtn.prop('disabled', false);
                    }
                });
            });

            // Delete Contract button
            $(document).on('click', '.delete-record', function () {
                const contractId = $(this).data('id');
                $('#confirmDeleteContractBtn').data('id', contractId);
                const modal = new bootstrap.Modal(document.getElementById('deleteContractModal'));
                modal.show();
            });

            // Delete Contract confirmation
            $('#confirmDeleteContractBtn').on('click', function () {
                const contractId = $(this).data('id');
                const deleteBtn = $(this);
                const originalBtnText = deleteBtn.html();

                // Show loading state
                deleteBtn.html('<i class="fas fa-spinner fa-spin"></i> Deleting...');
                deleteBtn.prop('disabled', true);

                // Send AJAX request
                $.ajax({
                    url: `/admin/contracts/${contractId}`,
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function (response) {
                        if (response.success) {
                            // Hide modal
                            bootstrap.Modal.getInstance(document.getElementById('deleteContractModal')).hide();

                            // Show success message
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: response.message,
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 3000
                            });

                            // Reload DataTable
                            contractsTable.ajax.reload();

                            // Reload contract stats
                            loadContractStats();
                        } else {
                            // Show error message
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message || 'Failed to delete contract',
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 3000
                            });
                        }
                    },
                    error: function () {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'An error occurred while deleting the contract.',
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000
                        });
                    },
                    complete: function () {
                        // Restore button state
                        deleteBtn.html(originalBtnText);
                        deleteBtn.prop('disabled', false);
                    }
                });
            });

            /**
             * Load contract statistics
             */
            function loadContractStats() {
                $.ajax({
                    url: '{{ route("admin.contracts.stats") }}',
                    type: 'GET',
                    success: function (response) {
                        if (response.success) {
                            // Update counters
                            $('#active-counter').html(response.stats.active);
                            $('#ending-soon-counter').html(response.stats.ending_soon);
                            $('#overdue-counter').html(response.stats.overdue);
                            $('#revenue-counter').html(parseFloat(response.stats.monthly_revenue).toLocaleString() + ' MAD');
                        } else {
                            // Set default values if there's an error
                            $('#active-counter').html('0');
                            $('#ending-soon-counter').html('0');
                            $('#overdue-counter').html('0');
                            $('#revenue-counter').html('0 MAD');
                        }
                    },
                    error: function () {
                        // Set default values if there's an error
                        $('#active-counter').html('0');
                        $('#ending-soon-counter').html('0');
                        $('#overdue-counter').html('0');
                        $('#revenue-counter').html('0 MAD');
                    }
                });
            }

            // Reset form fields and errors on modal show
            $('.modal').on('show.bs.modal', function () {
                $(this).find('form')[0].reset();
                $(this).find('.is-invalid').removeClass('is-invalid');
                $(this).find('.invalid-feedback').text('');
            });

            // Auto-refresh data every 5 minutes
            setInterval(function () {
                contractsTable.ajax.reload(null, false);
                loadContractStats();
            }, 300000); // 5 minutes in milliseconds
        });
    </script>
@endpush