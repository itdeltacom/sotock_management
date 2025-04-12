@extends('admin.layouts.master')

@section('title', 'Newsletter Subscribers')

@section('content')
    <div class="page-header">
        <h3 class="page-title">Newsletter Subscribers</h3>
        <div class="page-actions">
            <a href="{{ route('admin.newsletters.index') }}" class="btn btn-secondary me-2">
                <i class="fas fa-arrow-left"></i> Back to Newsletters
            </a>
            <button type="button" class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#importModal">
                <i class="fas fa-file-import"></i> Import
            </button>
            <a href="{{ route('admin.newsletters.subscribers.export') }}" class="btn btn-info">
                <i class="fas fa-file-export"></i> Export
            </a>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title">Filter Subscribers</h5>
        </div>
        <div class="card-body">
            <form id="filterForm" class="row g-3">
                <div class="col-md-4">
                    <label for="statusFilter" class="form-label">Status</label>
                    <select class="form-select" id="statusFilter" name="status">
                        <option value="">All Statuses</option>
                        <option value="active">Active</option>
                        <option value="unconfirmed">Unconfirmed</option>
                        <option value="unsubscribed">Unsubscribed</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-filter"></i> Apply
                    </button>
                    <button type="button" id="resetFilterBtn" class="btn btn-outline-secondary">
                        <i class="fas fa-undo"></i> Reset
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title">All Subscribers</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="subscribers-table" width="100%">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Subscribed Date</th>
                            <th>Confirmed Date</th>
                            <th>Unsubscribed Date</th>
                            <th>Last Email</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <!-- Import Modal -->
    <div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="importModalLabel">Import Subscribers</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="importForm" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group mb-3">
                            <label for="csv_file" class="form-label">CSV File <span class="text-danger">*</span></label>
                            <input type="file" class="form-control" id="csv_file" name="csv_file" accept=".csv" required>
                            <div class="form-text">CSV file should have email addresses in the first column. Header row is
                                optional.</div>
                            <div class="invalid-feedback" id="csv_file-error"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="importBtn">Import</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this subscriber?</p>
                    <p><strong>Email:</strong> <span id="delete-email"></span></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
@endpush

@push('js')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function () {
            // Initialize DataTable
            let dataTable = $('#subscribers-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('admin.newsletters.subscribers.data') }}",
                    data: function (d) {
                        d.status = $('#statusFilter').val();
                    }
                },
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'email', name: 'email' },
                    { data: 'status', name: 'status', orderable: false },
                    { data: 'subscribed_date', name: 'created_at' },
                    { data: 'confirmed_date', name: 'confirmed_at' },
                    { data: 'unsubscribed_date', name: 'unsubscribed_at' },
                    { data: 'last_email_date', name: 'last_email_sent_at' },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ],
                order: [[0, 'desc']]
            });

            // Filter form submission
            $('#filterForm').on('submit', function (e) {
                e.preventDefault();
                dataTable.draw();
            });

            // Reset filter
            $('#resetFilterBtn').on('click', function () {
                $('#filterForm')[0].reset();
                dataTable.draw();
            });

            // Import form submission
            $('#importForm').on('submit', function (e) {
                e.preventDefault();

                const formData = new FormData(this);

                $.ajax({
                    url: "{{ route('admin.newsletters.subscribers.import') }}",
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    beforeSend: function () {
                        // Clear previous errors
                        $('.is-invalid').removeClass('is-invalid');
                        $('.invalid-feedback').text('');

                        // Disable button and show loading
                        $('#importBtn').prop('disabled', true)
                            .html('<i class="fas fa-spinner fa-spin"></i> Importing...');
                    },
                    success: function (response) {
                        // Close modal
                        $('#importModal').modal('hide');

                        // Reset form
                        $('#importForm')[0].reset();

                        // Show success message
                        Swal.fire({
                            title: 'Success',
                            text: response.message,
                            icon: 'success',
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000
                        });

                        // Refresh table
                        dataTable.ajax.reload();
                    },
                    error: function (xhr) {
                        if (xhr.status === 422) {
                            // Validation errors
                            const errors = xhr.responseJSON.errors;

                            // Display errors
                            $.each(errors, function (field, messages) {
                                const errorField = $('#' + field);
                                errorField.addClass('is-invalid');
                                $('#' + field + '-error').text(messages[0]);
                            });
                        } else {
                            // Generic error
                            Swal.fire({
                                title: 'Error',
                                text: 'Failed to import subscribers',
                                icon: 'error',
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 3000
                            });
                        }
                    },
                    complete: function () {
                        // Re-enable button
                        $('#importBtn').prop('disabled', false)
                            .text('Import');
                    }
                });
            });

            // Toggle subscriber status
            $(document).on('click', '.btn-activate, .btn-deactivate', function () {
                const id = $(this).data('id');
                const email = $(this).data('email');
                const activate = $(this).hasClass('btn-activate');
                const action = activate ? 'activate' : 'deactivate';
                Swal.fire({
                    title: 'Confirm',
                    text: `Are you sure you want to ${action} this subscriber?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: activate ? 'Activate' : 'Deactivate',
                    cancelButtonText: 'Cancel',
                    confirmButtonColor: activate ? '#28a745' : '#ffc107',
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `{{ url('admin/newsletters/subscribers') }}/${id}/toggle-status`,
                            method: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function (response) {
                                // Show success message
                                Swal.fire({
                                    title: 'Success',
                                    text: response.message,
                                    icon: 'success',
                                    toast: true,
                                    position: 'top-end',
                                    showConfirmButton: false,
                                    timer: 3000
                                });

                                // Refresh table
                                dataTable.ajax.reload();
                            },
                            error: function () {
                                // Show error message
                                Swal.fire({
                                    title: 'Error',
                                    text: `Failed to ${action} subscriber`,
                                    icon: 'error',
                                    toast: true,
                                    position: 'top-end',
                                    showConfirmButton: false,
                                    timer: 3000
                                });
                            }
                        });
                    }
                });
            });

            // Resend confirmation email
            $(document).on('click', '.btn-resend', function () {
                const id = $(this).data('id');
                const email = $(this).data('email');

                Swal.fire({
                    title: 'Confirm',
                    text: `Resend confirmation email to ${email}?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Resend',
                    cancelButtonText: 'Cancel',
                    confirmButtonColor: '#0d6efd',
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `{{ url('admin/newsletters/subscribers') }}/${id}/resend-confirmation`,
                            method: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function (response) {
                                // Show success message
                                Swal.fire({
                                    title: 'Success',
                                    text: response.message,
                                    icon: 'success',
                                    toast: true,
                                    position: 'top-end',
                                    showConfirmButton: false,
                                    timer: 3000
                                });
                            },
                            error: function () {
                                // Show error message
                                Swal.fire({
                                    title: 'Error',
                                    text: 'Failed to resend confirmation email',
                                    icon: 'error',
                                    toast: true,
                                    position: 'top-end',
                                    showConfirmButton: false,
                                    timer: 3000
                                });
                            }
                        });
                    }
                });
            });

            // Prepare Delete Subscriber
            $(document).on('click', '.btn-delete-subscriber', function () {
                const id = $(this).data('id');
                const email = $(this).data('email');

                $('#delete-email').text(email);
                $('#deleteModal').data('id', id).modal('show');
            });

            // Delete Subscriber
            $('#confirmDeleteBtn').on('click', function () {
                const id = $('#deleteModal').data('id');

                $.ajax({
                    url: `{{ url('admin/newsletters/subscribers') }}/${id}`,
                    method: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    beforeSend: function () {
                        // Disable button and show loading
                        $('#confirmDeleteBtn').prop('disabled', true)
                            .html('<i class="fas fa-spinner fa-spin"></i> Deleting...');
                    },
                    success: function (response) {
                        // Close modal
                        $('#deleteModal').modal('hide');

                        // Show success message
                        Swal.fire({
                            title: 'Success',
                            text: response.message,
                            icon: 'success',
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000
                        });

                        // Refresh table
                        dataTable.ajax.reload();
                    },
                    error: function () {
                        // Show error message
                        Swal.fire({
                            title: 'Error',
                            text: 'Failed to delete subscriber',
                            icon: 'error',
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000
                        });
                    },
                    complete: function () {
                        // Reset button
                        $('#confirmDeleteBtn').prop('disabled', false)
                            .html('Delete');
                    }
                });
            });

            // Reset modals on close
            $('.modal').on('hidden.bs.modal', function () {
                $(this).removeData('id');
                $(this).find('form')[0]?.reset();
                $('.is-invalid').removeClass('is-invalid');
                $('.invalid-feedback').text('');
            });
        });
    </script>
@endpush