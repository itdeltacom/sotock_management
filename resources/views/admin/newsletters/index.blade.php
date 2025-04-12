@extends('admin.layouts.master')

@section('title', 'Newsletter Management')

@section('content')
    <div class="page-header">
        <h3 class="page-title">Newsletter Management</h3>
        <div class="page-actions">
            @can('create newsletters')
                <a href="{{ route('admin.newsletters.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Create Newsletter
                </a>
            @endcan
            <a href="{{ route('admin.newsletters.subscribers') }}" class="btn btn-info ml-2">
                <i class="fas fa-users"></i> Manage Subscribers
            </a>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title">Filter Newsletters</h5>
        </div>
        <div class="card-body">
            <form id="filterForm" class="row g-3">
                <div class="col-md-4">
                    <label for="statusFilter" class="form-label">Status</label>
                    <select class="form-select" id="statusFilter" name="status">
                        <option value="">All Statuses</option>
                        <option value="draft">Draft</option>
                        <option value="scheduled">Scheduled</option>
                        <option value="sending">Sending</option>
                        <option value="sent">Sent</option>
                        <option value="failed">Failed</option>
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
            <h5 class="card-title">All Newsletters</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="newsletters-table" width="100%">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Subject</th>
                            <th>Status</th>
                            <th>Recipients</th>
                            <th>Created By</th>
                            <th>Scheduled For</th>
                            <th>Sent At</th>
                            <th>Attachment</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <!-- View Newsletter Modal -->
    <div class="modal fade" id="viewNewsletterModal" tabindex="-1" aria-labelledby="viewNewsletterModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewNewsletterModalLabel">View Newsletter</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <h5>Subject:</h5>
                        <p id="view-subject"></p>
                    </div>

                    <div class="mb-3">
                        <h5>Content:</h5>
                        <div id="view-content" class="border p-3 bg-light"></div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <h5>Status:</h5>
                            <p id="view-status"></p>
                        </div>
                        <div class="col-md-4">
                            <h5>Created By:</h5>
                            <p id="view-created-by"></p>
                        </div>
                        <div class="col-md-4">
                            <h5>Attachment:</h5>
                            <p id="view-attachment"></p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <h5>Recipients:</h5>
                            <p id="view-recipients"></p>
                        </div>
                        <div class="col-md-4">
                            <h5>Scheduled For:</h5>
                            <p id="view-scheduled"></p>
                        </div>
                        <div class="col-md-4">
                            <h5>Sent At:</h5>
                            <p id="view-sent"></p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Send Confirmation Modal -->
    <div class="modal fade" id="sendConfirmModal" tabindex="-1" aria-labelledby="sendConfirmModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="sendConfirmModalLabel">Confirm Send</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to send this newsletter to all active subscribers?</p>
                    <p><strong>Subject:</strong> <span id="send-subject"></span></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" id="confirmSendBtn">Send Now</button>
                </div>
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
                    <p>Are you sure you want to delete this newsletter?</p>
                    <p><strong>Subject:</strong> <span id="delete-subject"></span></p>
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
            let dataTable = $('#newsletters-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('admin.newsletters.data') }}",
                    data: function (d) {
                        d.status = $('#statusFilter').val();
                    }
                },
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'subject', name: 'subject' },
                    { data: 'status_label', name: 'status', orderable: true },
                    { data: 'recipients_count', name: 'recipients_count' },
                    { data: 'created_by', name: 'created_by' },
                    { data: 'scheduled_for_formatted', name: 'scheduled_for' },
                    { data: 'sent_at_formatted', name: 'sent_at' },
                    { data: 'attachment_link', name: 'attachment', orderable: false, searchable: false },
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

            // View Newsletter
            $(document).on('click', '.btn-view', function () {
                const id = $(this).data('id');

                $.ajax({
                    url: `{{ url('admin/newsletters') }}/${id}`,
                    method: 'GET',
                    success: function (response) {
                        const newsletter = response.newsletter;

                        // Populate modal
                        $('#view-subject').text(newsletter.subject);
                        $('#view-content').html(newsletter.content);
                        $('#view-status').text(newsletter.status.charAt(0).toUpperCase() + newsletter.status.slice(1));
                        $('#view-created-by').text(newsletter.created_by ? newsletter.created_by.name : 'System');

                        if (newsletter.attachment_url) {
                            $('#view-attachment').html(`<a href="${newsletter.attachment_url}" target="_blank" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-paperclip"></i> View Attachment
                                </a>`);
                        } else {
                            $('#view-attachment').text('No attachment');
                        }

                        $('#view-recipients').text(newsletter.recipients_count || '0');
                        $('#view-scheduled').text(newsletter.scheduled_for || 'Not scheduled');
                        $('#view-sent').text(newsletter.sent_at || 'Not sent');

                        // Show modal
                        $('#viewNewsletterModal').modal('show');
                    },
                    error: function () {
                        Swal.fire({
                            title: 'Error',
                            text: 'Failed to load newsletter data',
                            icon: 'error',
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000
                        });
                    }
                });
            });

            // Prepare Send Newsletter
            $(document).on('click', '.btn-send', function () {
                const id = $(this).data('id');
                const row = dataTable.row($(this).closest('tr')).data();

                $('#sendConfirmModal').data('id', id).modal('show');
            });

            // Send Newsletter
            $('#confirmSendBtn').on('click', function () {
                const id = $('#sendConfirmModal').data('id');

                $.ajax({
                    url: `{{ url('admin/newsletters') }}/${id}/send`,
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    beforeSend: function () {
                        // Disable button and show loading
                        $('#confirmSendBtn').prop('disabled', true)
                            .html('<i class="fas fa-spinner fa-spin"></i> Sending...');
                    },
                    success: function (response) {
                        // Close modal
                        $('#sendConfirmModal').modal('hide');

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
                        // Show error message
                        Swal.fire({
                            title: 'Error',
                            text: xhr.responseJSON?.message || 'Failed to send newsletter',
                            icon: 'error',
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000
                        });
                    },
                    complete: function () {
                        // Reset button
                        $('#confirmSendBtn').prop('disabled', false)
                            .html('Send Now');
                    }
                });
            });

            // Prepare Delete Newsletter
            $(document).on('click', '.btn-delete', function () {
                const id = $(this).data('id');
                const subject = $(this).data('subject');

                $('#delete-subject').text(subject);
                $('#deleteModal').data('id', id).modal('show');
            });

            // Delete Newsletter
            $('#confirmDeleteBtn').on('click', function () {
                const id = $('#deleteModal').data('id');

                $.ajax({
                    url: `{{ url('admin/newsletters') }}/${id}`,
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
                            text: 'Failed to delete newsletter',
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
            });
        });
    </script>
@endpush