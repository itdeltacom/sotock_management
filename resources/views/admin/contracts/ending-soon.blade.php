@extends('admin.layouts.master')

@section('title', 'Contracts Ending Soon')

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header pb-0 p-3">
                        <div class="row">
                            <div class="col-6 d-flex align-items-center">
                                <h6 class="mb-0">Contracts Ending Soon</h6>
                            </div>
                            <div class="col-6 text-end">
                                <a href="{{ route('admin.contracts.index') }}" class="btn bg-gradient-secondary">
                                    <i class="fas fa-arrow-left"></i> Back to All Contracts
                                </a>
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
                                        <li><a class="dropdown-item active"
                                                href="{{ route('admin.contracts.ending-soon') }}">Ending Soon</a></li>
                                        <li><a class="dropdown-item"
                                                href="{{ route('admin.contracts.overdue') }}">Overdue</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body px-0 pt-0 pb-2">
                        <div class="alert alert-warning mx-4 mt-3">
                            <div class="d-flex">
                                <div>
                                    <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
                                </div>
                                <div>
                                    <h5 class="text-warning">Attention Required</h5>
                                    <p class="mb-0">
                                        The following contracts are ending within the next 2 days. Consider contacting the
                                        clients
                                        to discuss extension or return procedures.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive p-0">
                            <table class="table align-items-center mb-0" id="ending-soon-table" width="100%">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Contract #</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Client</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Vehicle</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Period</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Days Left</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Balance</th>
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
                        <div class="form-group mb-3">
                            <label for="final_notes" class="form-control-label">Final Notes</label>
                            <textarea class="form-control" id="final_notes" name="final_notes" rows="3"></textarea>
                            <div class="invalid-feedback" id="final_notes-error"></div>
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
                        <div class="form-group mb-3">
                            <label for="extension_notes" class="form-control-label">Notes</label>
                            <textarea class="form-control" id="extension_notes" name="extension_notes" rows="3"></textarea>
                            <div class="invalid-feedback" id="extension_notes-error"></div>
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

    <!-- Notify Client Modal -->
    <div class="modal fade" id="notifyClientModal" tabindex="-1" aria-labelledby="notifyClientModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="notifyClientModalLabel">Send Notification</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="notifyClientForm">
                    @csrf
                    <input type="hidden" id="notify_contract_id" name="contract_id">
                    <div class="modal-body">
                        <div class="text-center mb-4">
                            <i class="fas fa-bell text-primary fa-4x mb-3"></i>
                            <p>Send a notification to the client about their contract ending soon.</p>
                        </div>
                        <div class="form-group mb-3">
                            <label for="notification_type" class="form-control-label">Notification Type<span
                                    class="text-danger">*</span></label>
                            <select class="form-select" id="notification_type" name="type" required>
                                <option value="ending_soon">Contract Ending Soon</option>
                                <option value="payment_reminder">Payment Reminder</option>
                            </select>
                            <div class="invalid-feedback" id="notification_type-error"></div>
                        </div>
                        <div class="form-group mb-3">
                            <label for="notification_message" class="form-control-label">Additional Message</label>
                            <textarea class="form-control" id="notification_message" name="message" rows="3"></textarea>
                            <div class="invalid-feedback" id="notification_message-error"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn bg-gradient-primary" id="sendNotificationBtn">Send
                            Notification</button>
                    </div>
                </form>
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
            // Initialize DataTable for Ending Soon contracts
            const contractsTable = $('#ending-soon-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.contracts.ending-soon.datatable') }}",
                columns: [
                    {
                        data: 'contract_number',
                        name: 'id',
                        render: function (data) {
                            return `<span class="text-xs font-weight-bold">${data}</span>`;
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
                    { data: 'days_left', name: 'days_left' },
                    { data: 'outstanding_balance', name: 'outstanding_balance' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false }
                ],
                order: [[4, 'asc']], // Sort by days left by default (ascending order)
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
                const finalNotes = $('#final_notes').val();
                const completeBtn = $('#completeContractBtn');
                const originalBtnText = completeBtn.html();

                // Clear previous validation errors
                $(this).find('.is-invalid').removeClass('is-invalid');
                $(this).find('.invalid-feedback').text('');

                // Show loading state
                completeBtn.html('<i class="fas fa-spinner fa-spin"></i> Processing...');
                completeBtn.prop('disabled', true);

                // Send AJAX request
                $.ajax({
                    url: `/admin/contracts/${contractId}/complete`,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        end_mileage: endMileage,
                        final_notes: finalNotes
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
                            if (errors.final_notes) {
                                $('#final_notes').addClass('is-invalid');
                                $('#final_notes-error').text(errors.final_notes[0]);
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
                const extensionNotes = $('#extension_notes').val();
                const extendBtn = $('#extendContractBtn');
                const originalBtnText = extendBtn.html();

                // Clear previous validation errors
                $(this).find('.is-invalid').removeClass('is-invalid');
                $(this).find('.invalid-feedback').text('');

                // Show loading state
                extendBtn.html('<i class="fas fa-spinner fa-spin"></i> Processing...');
                extendBtn.prop('disabled', true);

                // Send AJAX request
                $.ajax({
                    url: `/admin/contracts/${contractId}/extend`,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        extension_days: extensionDays,
                        extension_notes: extensionNotes
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
                            if (errors.extension_notes) {
                                $('#extension_notes').addClass('is-invalid');
                                $('#extension_notes-error').text(errors.extension_notes[0]);
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

            // Notify Client button
            $(document).on('click', '.notify-client', function () {
                const contractId = $(this).data('id');
                $('#notify_contract_id').val(contractId);
                const modal = new bootstrap.Modal(document.getElementById('notifyClientModal'));
                modal.show();
            });

            // Notify Client form submission
            $('#notifyClientForm').on('submit', function (e) {
                e.preventDefault();
                const contractId = $('#notify_contract_id').val();
                const notificationType = $('#notification_type').val();
                const message = $('#notification_message').val();
                const notifyBtn = $('#sendNotificationBtn');
                const originalBtnText = notifyBtn.html();

                // Clear previous validation errors
                $(this).find('.is-invalid').removeClass('is-invalid');
                $(this).find('.invalid-feedback').text('');

                // Show loading state
                notifyBtn.html('<i class="fas fa-spinner fa-spin"></i> Sending...');
                notifyBtn.prop('disabled', true);

                // Send AJAX request
                $.ajax({
                    url: `/admin/contracts/${contractId}/notify`,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        type: notificationType,
                        message: message
                    },
                    success: function (response) {
                        if (response.success) {
                            // Hide modal
                            bootstrap.Modal.getInstance(document.getElementById('notifyClientModal')).hide();

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
                            if (errors.type) {
                                $('#notification_type').addClass('is-invalid');
                                $('#notification_type-error').text(errors.type[0]);
                            }
                            if (errors.message) {
                                $('#notification_message').addClass('is-invalid');
                                $('#notification_message-error').text(errors.message[0]);
                            }
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'An error occurred while sending the notification.',
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 3000
                            });
                        }
                    },
                    complete: function () {
                        // Restore button state
                        notifyBtn.html(originalBtnText);
                        notifyBtn.prop('disabled', false);
                    }
                });
            });

            // Reset form fields and errors on modal show
            $('.modal').on('show.bs.modal', function () {
                $(this).find('form')[0].reset();
                $(this).find('.is-invalid').removeClass('is-invalid');
                $(this).find('.invalid-feedback').text('');
            });

            // Auto-refresh data every 5 minutes
            setInterval(function () {
                contractsTable.ajax.reload(null, false);
            }, 300000); // 5 minutes in milliseconds
        });
    </script>
@endpush