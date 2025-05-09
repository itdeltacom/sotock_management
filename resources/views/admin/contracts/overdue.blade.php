@extends('admin.layouts.master')

@section('title', 'Overdue Contracts')

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header pb-0 p-3">
                        <div class="row">
                            <div class="col-6 d-flex align-items-center">
                                <h6 class="mb-0">Overdue Contracts</h6>
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
                                        <li><a class="dropdown-item"
                                                href="{{ route('admin.contracts.ending-soon') }}">Ending Soon</a></li>
                                        <li><a class="dropdown-item active"
                                                href="{{ route('admin.contracts.overdue') }}">Overdue</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body px-0 pt-0 pb-2">
                        <div class="alert alert-danger mx-4 mt-3">
                            <div class="d-flex">
                                <div>
                                    <i class="fas fa-exclamation-circle fa-2x me-3"></i>
                                </div>
                                <div>
                                    <h5 class="text-danger">Urgent Attention Required</h5>
                                    <p class="mb-0">
                                        These contracts have passed their return date. Immediate action is required to contact
                                        clients, collect outstanding payments, or recover vehicles.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive p-0">
                            <table class="table align-items-center mb-0" id="overdue-contracts-table" width="100%">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Contract #</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Client</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Vehicle</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            End Date</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Overdue</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Penalty</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Total Due</th>
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
                            <p>You are about to complete this overdue contract and return the vehicle.</p>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                This contract is overdue. Make sure to collect any applicable late fees.
                            </div>
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

    <!-- Contact Client Modal -->
    <div class="modal fade" id="contactClientModal" tabindex="-1" aria-labelledby="contactClientModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="contactClientModalLabel">Contact Client</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <i class="fas fa-phone text-warning fa-4x mb-3"></i>
                        <h5>Contact Information</h5>
                    </div>
                    <div id="client-contact-info">
                        <div class="card">
                            <div class="card-body">
                                <div class="mb-3">
                                    <h6 class="text-sm">Client Name</h6>
                                    <p id="contact-client-name" class="font-weight-bold mb-0">Loading...</p>
                                </div>
                                <div class="mb-3">
                                    <h6 class="text-sm">Phone Number</h6>
                                    <p id="contact-client-phone" class="font-weight-bold mb-0">Loading...</p>
                                </div>
                                <div class="mb-3">
                                    <h6 class="text-sm">Email</h6>
                                    <p id="contact-client-email" class="font-weight-bold mb-0">Loading...</p>
                                </div>
                                <div>
                                    <h6 class="text-sm">Address</h6>
                                    <p id="contact-client-address" class="font-weight-bold mb-0">Loading...</p>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3">
                            <h6>Contract Details</h6>
                            <p class="text-sm mb-1">Vehicle: <span id="contact-car-details">Loading...</span></p>
                            <p class="text-sm mb-1">End Date: <span id="contact-end-date">Loading...</span></p>
                            <p class="text-sm mb-1">Overdue: <span id="contact-overdue-days" class="text-danger">Loading...</span></p>
                            <p class="text-sm mb-0">Outstanding Balance: <span id="contact-balance" class="text-danger">Loading...</span></p>
                        </div>
                        <div class="row mt-4">
                            <div class="col-6">
                                <a id="client-call-link" href="#" class="btn bg-gradient-info w-100">
                                    <i class="fas fa-phone me-1"></i> Call
                                </a>
                            </div>
                            <div class="col-6">
                                <a id="client-email-link" href="#" class="btn bg-gradient-primary w-100">
                                    <i class="fas fa-envelope me-1"></i> Email
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn bg-gradient-danger" id="sendReminderBtn">
                        <i class="fas fa-exclamation-triangle me-1"></i> Send Reminder
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Send Reminder Modal -->
    <div class="modal fade" id="sendReminderModal" tabindex="-1" aria-labelledby="sendReminderModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="sendReminderModalLabel">Send Reminder</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="sendReminderForm">
                    @csrf
                    <input type="hidden" id="reminder_contract_id" name="contract_id">
                    <div class="modal-body">
                        <div class="text-center mb-4">
                            <i class="fas fa-exclamation-triangle text-danger fa-4x mb-3"></i>
                            <p>Send an urgent reminder to the client regarding their overdue contract.</p>
                        </div>
                        <div class="form-group mb-3">
                            <label for="reminder_type" class="form-control-label">Reminder Type<span
                                    class="text-danger">*</span></label>
                            <select class="form-select" id="reminder_type" name="type" required>
                                <option value="overdue">Overdue Contract</option>
                                <option value="payment_reminder">Payment Reminder</option>
                            </select>
                            <div class="invalid-feedback" id="reminder_type-error"></div>
                        </div>
                        <div class="form-group mb-3">
                            <label for="reminder_message" class="form-control-label">Additional Message</label>
                            <textarea class="form-control" id="reminder_message" name="message" rows="3"></textarea>
                            <div class="invalid-feedback" id="reminder_message-error"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn bg-gradient-danger" id="sendReminderSubmitBtn">Send Reminder</button>
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
            // Initialize DataTable for Overdue contracts
            const overdueTable = $('#overdue-contracts-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.contracts.overdue.datatable') }}",
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
                        data: 'end_date',
                        name: 'end_date',
                        render: function (data) {
                            const endDate = new Date(data).toLocaleDateString();
                            return `<span class="text-xs">${endDate}</span>`;
                        }
                    },
                    { data: 'overdue_days', name: 'overdue_days' },
                    { data: 'estimated_penalty', name: 'estimated_penalty' },
                    { data: 'outstanding_balance', name: 'outstanding_balance' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false }
                ],
                order: [[4, 'desc']], // Sort by overdue days by default (descending)
                pageLength: 25,
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search overdue contracts",
                    paginate: {
                        previous: '<i class="fas fa-chevron-left"></i>',
                        next: '<i class="fas fa-chevron-right"></i>'
                    }
                }
            });

            // Complete Contract button click
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
                            overdueTable.ajax.reload();
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

            // Contact Client button click
            $(document).on('click', '.contact-client', function () {
                const contractId = $(this).data('id');
                
                // Fetch client details
                $.ajax({
                    url: `/admin/contracts/${contractId}`,
                    type: 'GET',
                    success: function (response) {
                        if (response.success) {
                            const contract = response.contract;
                            const client = contract.client;
                            const car = contract.car;
                            
                            // Update modal content
                            $('#contact-client-name').text(client.name);
                            $('#contact-client-phone').text(client.phone || 'N/A');
                            $('#contact-client-email').text(client.email || 'N/A');
                            $('#contact-client-address').text(client.address || 'N/A');
                            
                            $('#contact-car-details').text(`${car.brand_name} ${car.model} (${car.matricule})`);
                            $('#contact-end-date').text(new Date(contract.end_date).toLocaleDateString());
                            
                            const overdueDays = Math.floor((new Date() - new Date(contract.end_date)) / (1000 * 60 * 60 * 24));
                            $('#contact-overdue-days').text(`${overdueDays} day(s)`);
                            
                            const balance = parseFloat(contract.outstanding_balance) + parseFloat(contract.estimated_penalty || 0);
                            $('#contact-balance').text(`${balance.toLocaleString()} MAD`);
                            
                            // Set up call and email links
                            $('#client-call-link').attr('href', `tel:${client.phone}`);
                            $('#client-email-link').attr('href', `mailto:${client.email}`);
                            
                            // Set reminder button data
                            $('#sendReminderBtn').data('id', contractId);
                            
                            // Show the modal
                            const modal = new bootstrap.Modal(document.getElementById('contactClientModal'));
                            modal.show();
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Could not retrieve client information',
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
                            text: 'Failed to load client information',
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000
                        });
                    }
                });
            });

            // Send Reminder button from Contact Modal
            $('#sendReminderBtn').on('click', function () {
                const contractId = $(this).data('id');
                $('#reminder_contract_id').val(contractId);
                
                // Hide contact modal
                bootstrap.Modal.getInstance(document.getElementById('contactClientModal')).hide();
                
                // Show reminder modal
                const reminderModal = new bootstrap.Modal(document.getElementById('sendReminderModal'));
                reminderModal.show();
            });

            // Send Reminder button direct click
            $(document).on('click', '.send-reminder', function () {
                const contractId = $(this).data('id');
                $('#reminder_contract_id').val(contractId);
                const modal = new bootstrap.Modal(document.getElementById('sendReminderModal'));
                modal.show();
            });

            // Send Reminder form submission
            $('#sendReminderForm').on('submit', function (e) {
                e.preventDefault();
                const contractId = $('#reminder_contract_id').val();
                const reminderType = $('#reminder_type').val();
                const message = $('#reminder_message').val();
                const reminderBtn = $('#sendReminderSubmitBtn');
                const originalBtnText = reminderBtn.html();

                // Clear previous validation errors
                $(this).find('.is-invalid').removeClass('is-invalid');
                $(this).find('.invalid-feedback').text('');

                // Show loading state
                reminderBtn.html('<i class="fas fa-spinner fa-spin"></i> Sending...');
                reminderBtn.prop('disabled', true);

                // Send AJAX request
                $.ajax({
                    url: `/admin/contracts/${contractId}/notify`,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        type: reminderType,
                        message: message
                    },
                    success: function (response) {
                        if (response.success) {
                            // Hide modal
                            bootstrap.Modal.getInstance(document.getElementById('sendReminderModal')).hide();

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
                                $('#reminder_type').addClass('is-invalid');
                                $('#reminder_type-error').text(errors.type[0]);
                            }
                            if (errors.message) {
                                $('#reminder_message').addClass('is-invalid');
                                $('#reminder_message-error').text(errors.message[0]);
                            }
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'An error occurred while sending the reminder.',
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 3000
                            });
                        }
                    },
                    complete: function () {
                        // Restore button state
                        reminderBtn.html(originalBtnText);
                        reminderBtn.prop('disabled', false);
                    }
                });
            });

            // Reset form fields and errors on modal show
            $('.modal').on('show.bs.modal', function () {
                $(this).find('form')[0]?.reset();
                $(this).find('.is-invalid').removeClass('is-invalid');
                $(this).find('.invalid-feedback').text('');
            });

            // Auto-refresh data every 5 minutes
            setInterval(function () {
                overdueTable.ajax.reload(null, false);
            }, 300000); // 5 minutes in milliseconds
        });
    </script>
@endpush