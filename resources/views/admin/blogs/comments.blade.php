@extends('admin.layouts.master')

@section('title', 'Blog Comments Management')

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header pb-0 p-3">
                        <div class="row">
                            <div class="col-6 d-flex align-items-center">
                                <h6 class="mb-0">Blog Comments Management</h6>
                            </div>
                            <div class="col-6 text-end">
                                @can('create blog comments')
                                    <button type="button" class="btn bg-gradient-primary" id="createCommentBtn">
                                        <i class="fas fa-plus"></i> Add New Comment
                                    </button>
                                @endcan
                            </div>
                        </div>
                    </div>

                    <!-- Filters -->
                    <div class="card-header pb-0 p-3">
                        <form id="filterForm" class="row g-3">
                            <div class="col-md-3">
                                <select class="form-select form-select-sm" id="filter_post">
                                    <option value="">All Posts</option>
                                    @foreach(\App\Models\BlogPost::orderBy('title')->get() as $post)
                                        <option value="{{ $post->id }}">{{ $post->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select form-select-sm" id="filter_status">
                                    <option value="">All Statuses</option>
                                    <option value="approved">Approved</option>
                                    <option value="pending">Pending</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select form-select-sm" id="filter_type">
                                    <option value="">All Types</option>
                                    <option value="comments">Comments</option>
                                    <option value="replies">Replies</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <div class="d-flex">
                                    <button type="submit" class="btn btn-sm bg-gradient-info me-2">
                                        <i class="fas fa-filter"></i> Apply
                                    </button>
                                    <button type="button" id="resetFiltersBtn" class="btn btn-sm bg-gradient-secondary">
                                        <i class="fas fa-undo"></i> Reset
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="card-body px-0 pt-0 pb-2">
                        <div class="table-responsive p-0">
                            <table class="table align-items-center mb-0" id="comments-table" width="100%">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">ID
                                        </th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Type
                                        </th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Author</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Content</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Post
                                        </th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Status</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Date
                                        </th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Approval</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Actions</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create/Edit Comment Modal -->
    <div class="modal fade" id="commentModal" tabindex="-1" aria-labelledby="commentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="commentModalLabel">Add New Comment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="commentForm">
                    @csrf
                    <input type="hidden" name="comment_id" id="comment_id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="post_id" class="form-control-label">Post <span class="text-danger">*</span></label>
                            <select class="form-control" id="post_id" name="post_id" required>
                                <option value="">Select Post</option>
                                @foreach(\App\Models\BlogPost::orderBy('title')->get() as $post)
                                    <option value="{{ $post->id }}">{{ $post->title }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback" id="post_id-error"></div>
                        </div>

                        <div class="mb-3">
                            <label for="parent_id" class="form-control-label">Parent Comment (Optional)</label>
                            <select class="form-control" id="parent_id" name="parent_id">
                                <option value="">None (New Comment)</option>
                            </select>
                            <div class="invalid-feedback" id="parent_id-error"></div>
                        </div>

                        <div class="mb-3">
                            <label for="name" class="form-control-label">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" required>
                            <div class="invalid-feedback" id="name-error"></div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-control-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" required>
                            <div class="invalid-feedback" id="email-error"></div>
                        </div>

                        <div class="mb-3">
                            <label for="website" class="form-control-label">Website</label>
                            <input type="url" class="form-control" id="website" name="website">
                            <div class="invalid-feedback" id="website-error"></div>
                        </div>

                        <div class="mb-3">
                            <label for="content" class="form-control-label">Comment <span
                                    class="text-danger">*</span></label>
                            <textarea class="form-control" id="content" name="content" rows="5" required></textarea>
                            <div class="invalid-feedback" id="content-error"></div>
                        </div>

                        <div class="mb-3">
                            <label for="is_approved" class="form-control-label">Status</label>
                            <select class="form-control" id="is_approved" name="is_approved">
                                <option value="1">Approved</option>
                                <option value="0">Pending</option>
                            </select>
                            <div class="invalid-feedback" id="is_approved-error"></div>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="comment_as_admin" name="comment_as_admin">
                            <label class="form-check-label" for="comment_as_admin">
                                Comment as admin (use your admin details)
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn bg-gradient-primary" id="saveBtn">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Comment Modal -->
    <div class="modal fade" id="viewCommentModal" tabindex="-1" aria-labelledby="viewCommentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewCommentModalLabel">Comment Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex align-items-start mb-3">
                        <img src="" id="view-avatar" class="rounded-circle me-3 shadow-sm" width="50" height="50"
                            alt="Avatar">
                        <div>
                            <h5 id="view-author" class="mb-0 fw-bold"></h5>
                            <div id="view-email" class="text-muted"></div>
                            <div id="view-website" class="small"></div>
                        </div>
                    </div>

                    <div class="card mb-3 shadow-sm">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                            <div>
                                <span id="view-type" class="badge badge-sm me-2"></span>
                                <span id="view-status" class="badge badge-sm me-2"></span>
                                <span id="view-date" class="text-muted small"></span>
                            </div>
                            <div id="view-post" class="small"></div>
                        </div>
                        <div class="card-body">
                            <div id="view-content"></div>
                        </div>
                    </div>

                    <div id="view-parent-container" class="card mb-3 shadow-sm d-none">
                        <div class="card-header bg-light">
                            <i class="fas fa-reply text-muted me-2"></i> Reply to
                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-start mb-2">
                                <img src="" id="view-parent-avatar" class="rounded-circle me-3 shadow-sm" width="40"
                                    height="40" alt="Parent Avatar">
                                <div>
                                    <h6 id="view-parent-author" class="mb-0 fw-bold"></h6>
                                    <div id="view-parent-date" class="text-muted small"></div>
                                </div>
                            </div>
                            <div id="view-parent-content" class="ps-5"></div>
                        </div>
                    </div>

                    <div id="view-meta" class="card mb-3 shadow-sm">
                        <div class="card-header bg-light">
                            <i class="fas fa-info-circle text-muted me-2"></i> Additional Information
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-2">
                                    <strong>IP Address:</strong> <span id="view-ip"></span>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <strong>User Agent:</strong> <span id="view-user-agent" class="small"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div id="view-approval-buttons" class="me-auto"></div>
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" id="viewEditBtn" class="btn bg-gradient-primary">Edit</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="py-3 text-center">
                        <i class="fas fa-exclamation-triangle text-warning fa-3x mb-3"></i>
                        <p>Are you sure you want to delete this comment? This action cannot be undone.</p>
                        <div id="delete-warning" class="text-danger mt-3"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn bg-gradient-danger" id="confirmDeleteBtn">Delete</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <style>
        .card {
            box-shadow: 0 20px 27px 0 rgba(0, 0, 0, 0.05);
            border-radius: 0.75rem;
        }

        .card .card-header {
            padding: 1.5rem;
        }

        .form-control,
        .form-select {
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
            line-height: 1.4;
            border-radius: 0.5rem;
            transition: box-shadow 0.15s ease, border-color 0.15s ease;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #5e72e4;
            box-shadow: 0 3px 9px rgba(50, 50, 9, 0), 3px 4px 8px rgba(94, 114, 228, 0.1);
        }

        .form-control-label {
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
            font-weight: 600;
            color: #8392AB;
        }

        /* Buttons and gradients */
        .bg-gradient-primary {
            background: linear-gradient(310deg, #5e72e4 0%, #825ee4 100%);
        }

        .bg-gradient-success {
            background: linear-gradient(310deg, #2dce89 0%, #2dcecc 100%);
        }

        .bg-gradient-danger {
            background: linear-gradient(310deg, #f5365c 0%, #f56036 100%);
        }

        .bg-gradient-warning {
            background: linear-gradient(310deg, #fb6340 0%, #fbb140 100%);
        }

        .bg-gradient-info {
            background: linear-gradient(310deg, #11cdef 0%, #1171ef 100%);
        }

        .bg-gradient-secondary {
            background: linear-gradient(310deg, #627594, #8097bf);
        }

        /* Modal styling */
        .modal-content {
            border: 0;
            border-radius: 0.75rem;
            box-shadow: 0 10px 35px -5px rgba(0, 0, 0, 0.15);
        }

        .modal-dialog-scrollable .modal-content {
            max-height: 85vh;
        }

        .modal-dialog-scrollable .modal-body {
            overflow-y: auto;
            max-height: calc(85vh - 130px);
        }

        /* DataTable styling */
        table.dataTable {
            margin-top: 0 !important;
        }

        .table thead th {
            padding: 0.75rem 1.5rem;
            text-transform: uppercase;
            letter-spacing: 0.025em;
            font-size: 0.65rem;
            font-weight: 700;
            border-bottom-width: 1px;
        }

        .table td {
            white-space: nowrap;
            padding: 0.5rem 1.5rem;
            font-size: 0.875rem;
            vertical-align: middle;
            border-bottom: 1px solid #E9ECEF;
        }

        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter,
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_processing,
        .dataTables_wrapper .dataTables_paginate {
            font-size: 0.875rem;
            color: #8392AB;
            padding: 1rem 1.5rem;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: linear-gradient(310deg, #5e72e4 0%, #825ee4 100%);
            color: white !important;
            border: none;
            border-radius: 0.5rem;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: #f6f9fc;
            color: #5e72e4 !important;
            border: 1px solid #f6f9fc;
        }

        /* Comments specific styling */
        #comments-table td.content-cell {
            max-width: 300px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .comment-highlight {
            background-color: rgba(94, 114, 228, 0.1);
            padding: 0.5rem;
            border-radius: 0.5rem;
            border-left: 3px solid #5e72e4;
        }

        /* Avatar styling */
        .rounded-circle {
            object-fit: cover;
            border: 2px solid #fff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        /* Badge styling */
        .badge {
            padding: 0.35em 0.65em;
            font-weight: 600;
            border-radius: 0.5rem;
        }

        /* Form switch styling */
        .form-check-input:checked {
            background-color: #5e72e4;
            border-color: #5e72e4;
        }

        /* Loading overlay for AJAX */
        .loading-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.7);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            border-radius: 0.75rem;
        }
    </style>
@endpush

@push('js')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // Routes for AJAX calls
        const routes = {
            dataUrl: "{{ route('admin.blog-comments.data') }}",
            storeUrl: "{{ route('admin.blog-comments.store') }}",
            showUrl: "{{ route('admin.blog-comments.show', ':id') }}",
            updateUrl: "{{ route('admin.blog-comments.update', ':id') }}",
            destroyUrl: "{{ route('admin.blog-comments.destroy', ':id') }}",
            approveUrl: "{{ route('admin.blog-comments.approve', ':id') }}",
            rejectUrl: "{{ route('admin.blog-comments.reject', ':id') }}",
            getCommentsUrl: "{{ route('admin.blog-comments.get-by-post', ':id') }}"
        };

        // Pass permissions data to JavaScript
        const canEditComments = @json(auth()->guard('admin')->user()->can('edit blog comments'));
        const canDeleteComments = @json(auth()->guard('admin')->user()->can('delete blog comments'));
        const adminUser = {
            name: @json(auth()->guard('admin')->user()->name),
            email: @json(auth()->guard('admin')->user()->email)
        };
    </script>

    <!-- Include the JS for comments management -->
    <script src="{{ asset('admin/js/blog-comments-management.js') }}"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Configure SweetAlert to use Argon style
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer)
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                }
            });

            // Override showAlert function if needed
            if (typeof window.showAlert !== 'function') {
                window.showAlert = function (title, text, icon) {
                    Toast.fire({
                        icon: icon,
                        title: title,
                        text: text
                    });
                };
            }

            // Post ID change handler
            $('#post_id').on('change', function () {
                const postId = $(this).val();
                if (postId) {
                    // Clear parent dropdown
                    $('#parent_id').empty().append('<option value="">None (New Comment)</option>');

                    // Get comments for selected post
                    fetch(routes.getCommentsUrl.replace(':id', postId))
                        .then(response => response.json())
                        .then(data => {
                            if (data.success && data.comments.length > 0) {
                                data.comments.forEach(comment => {
                                    $('#parent_id').append(`<option value="${comment.id}">${comment.name}: ${comment.content.substring(0, 50)}...</option>`);
                                });
                            }
                        })
                        .catch(error => {
                            console.error('Error fetching comments:', error);
                            showAlert('Error', 'Failed to fetch comments for selected post', 'error');
                        });
                }
            });

            // Comment as admin toggle
            $('#comment_as_admin').on('change', function () {
                if (this.checked) {
                    $('#name').val(adminUser.name);
                    $('#email').val(adminUser.email);
                    $('#name').prop('readonly', true);
                    $('#email').prop('readonly', true);
                } else {
                    $('#name').prop('readonly', false);
                    $('#email').prop('readonly', false);
                }
            });

            // Reset filters button
            $('#resetFiltersBtn').on('click', function () {
                $('#filterForm')[0].reset();
                $('#filterForm').trigger('submit');
            });

            // View edit button
            $('#viewEditBtn').on('click', function () {
                const commentId = $(this).data('comment-id');
                if (commentId) {
                    $('#viewCommentModal').modal('hide');
                    // Load the comment for editing
                    fetch(routes.showUrl.replace(':id', commentId))
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                populateEditForm(data.comment);
                                $('#commentModal').modal('show');
                            } else {
                                throw new Error(data.message || 'Failed to load comment data');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            showAlert('Error', error.message || 'Failed to load comment data', 'error');
                        });
                }
            });

            // Populate edit form
            function populateEditForm(comment) {
                $('#commentModalLabel').text('Edit Comment');
                $('#comment_id').val(comment.id);
                $('#post_id').val(comment.post_id).trigger('change');
                $('#parent_id').val(comment.parent_id || '');
                $('#name').val(comment.name);
                $('#email').val(comment.email);
                $('#website').val(comment.website || '');
                $('#content').val(comment.content);
                $('#is_approved').val(comment.is_approved ? '1' : '0');

                // Check if comment is by admin
                if (comment.name === adminUser.name && comment.email === adminUser.email) {
                    $('#comment_as_admin').prop('checked', true).trigger('change');
                } else {
                    $('#comment_as_admin').prop('checked', false).trigger('change');
                }
            }

            // Add approval buttons to view modal
            $('#viewCommentModal').on('show.bs.modal', function (e) {
                const commentId = $(e.relatedTarget).data('comment-id') || $('#viewEditBtn').data('comment-id');
                const isApproved = $(e.relatedTarget).data('is-approved');

                $('#viewEditBtn').data('comment-id', commentId);

                const approvalButtons = $('#view-approval-buttons');
                approvalButtons.empty();

                if (canEditComments) {
                    if (isApproved) {
                        approvalButtons.append(`
                                <button type="button" class="btn btn-sm bg-gradient-danger" onclick="rejectComment(${commentId})">
                                    <i class="fas fa-times"></i> Reject
                                </button>
                            `);
                    } else {
                        approvalButtons.append(`
                                <button type="button" class="btn btn-sm bg-gradient-success" onclick="approveComment(${commentId})">
                                    <i class="fas fa-check"></i> Approve
                                </button>
                            `);
                    }
                }
            });

            // Global functions for approval/rejection
            window.approveComment = function (commentId) {
                fetch(routes.approveUrl.replace(':id', commentId), {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            $('#viewCommentModal').modal('hide');
                            showAlert('Success', 'Comment approved successfully', 'success');
                            // Reload the table
                            if (window.commentTable) {
                                window.commentTable.ajax.reload();
                            }
                        } else {
                            throw new Error(data.message || 'Failed to approve comment');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showAlert('Error', error.message || 'Failed to approve comment', 'error');
                    });
            };

            window.rejectComment = function (commentId) {
                fetch(routes.rejectUrl.replace(':id', commentId), {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            $('#viewCommentModal').modal('hide');
                            showAlert('Success', 'Comment rejected successfully', 'success');
                            // Reload the table
                            if (window.commentTable) {
                                window.commentTable.ajax.reload();
                            }
                        } else {
                            throw new Error(data.message || 'Failed to reject comment');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showAlert('Error', error.message || 'Failed to reject comment', 'error');
                    });
            };
        });
    </script>
@endpush