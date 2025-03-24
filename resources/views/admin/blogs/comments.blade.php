@extends('admin.layouts.master')

@section('title', 'Blog Comments Management')

@section('content')
    <div class="page-header">
        <h3 class="page-title">Blog Comments Management</h3>
        <div class="page-actions">
            @can('create blog comments')
                <button type="button" class="btn btn-primary" id="createCommentBtn">
                    <i class="fas fa-plus"></i> Add New Comment
                </button>
            @endcan
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title">Filter Comments</h5>
        </div>
        <div class="card-body">
            <form id="filterForm" class="row">
                <div class="col-md-3 mb-3">
                    <label for="filter_post" class="form-label">Post</label>
                    <select class="form-select" id="filter_post">
                        <option value="">All Posts</option>
                        @foreach(\App\Models\BlogPost::orderBy('title')->get() as $post)
                            <option value="{{ $post->id }}">{{ $post->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="filter_status" class="form-label">Status</label>
                    <select class="form-select" id="filter_status">
                        <option value="">All</option>
                        <option value="approved">Approved</option>
                        <option value="pending">Pending</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="filter_type" class="form-label">Type</label>
                    <select class="form-select" id="filter_type">
                        <option value="">All</option>
                        <option value="comments">Comments</option>
                        <option value="replies">Replies</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">Filter</button>
                    <button type="button" id="resetFiltersBtn" class="btn btn-secondary">Reset</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title">All Comments</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="comments-table" width="100%">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Type</th>
                            <th>Author</th>
                            <th>Content</th>
                            <th>Post</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Approval</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                </table>
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
                            <label for="post_id" class="form-label">Post <span class="text-danger">*</span></label>
                            <select class="form-select" id="post_id" name="post_id" required>
                                <option value="">Select Post</option>
                                @foreach(\App\Models\BlogPost::orderBy('title')->get() as $post)
                                    <option value="{{ $post->id }}">{{ $post->title }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback" id="post_id-error"></div>
                        </div>

                        <div class="mb-3">
                            <label for="parent_id" class="form-label">Parent Comment (Optional)</label>
                            <select class="form-select" id="parent_id" name="parent_id">
                                <option value="">None (New Comment)</option>
                            </select>
                            <div class="invalid-feedback" id="parent_id-error"></div>
                        </div>

                        <div class="mb-3">
                            <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" required>
                            <div class="invalid-feedback" id="name-error"></div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" required>
                            <div class="invalid-feedback" id="email-error"></div>
                        </div>

                        <div class="mb-3">
                            <label for="website" class="form-label">Website</label>
                            <input type="url" class="form-control" id="website" name="website">
                            <div class="invalid-feedback" id="website-error"></div>
                        </div>

                        <div class="mb-3">
                            <label for="content" class="form-label">Comment <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="content" name="content" rows="5" required></textarea>
                            <div class="invalid-feedback" id="content-error"></div>
                        </div>

                        <div class="mb-3">
                            <label for="is_approved" class="form-label">Status</label>
                            <select class="form-select" id="is_approved" name="is_approved">
                                <option value="1">Approved</option>
                                <option value="0">Pending</option>
                            </select>
                            <div class="invalid-feedback" id="is_approved-error"></div>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="comment_as_admin" name="comment_as_admin">
                            <label class="form-check-label" for="comment_as_admin">
                                Comment as admin (use your admin details)
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="saveBtn">Save</button>
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
                        <img src="" id="view-avatar" class="rounded-circle me-3" width="50" height="50" alt="Avatar">
                        <div>
                            <h5 id="view-author" class="mb-0"></h5>
                            <div id="view-email" class="text-muted"></div>
                            <div id="view-website" class="small"></div>
                        </div>
                    </div>

                    <div class="card mb-3">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div>
                                <span id="view-type" class="badge me-2"></span>
                                <span id="view-status" class="badge me-2"></span>
                                <span id="view-date" class="text-muted small"></span>
                            </div>
                            <div id="view-post" class="small"></div>
                        </div>
                        <div class="card-body">
                            <div id="view-content"></div>
                        </div>
                    </div>

                    <div id="view-parent-container" class="card mb-3 d-none">
                        <div class="card-header">
                            Reply to
                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-start mb-2">
                                <img src="" id="view-parent-avatar" class="rounded-circle me-3" width="40" height="40"
                                    alt="Parent Avatar">
                                <div>
                                    <h6 id="view-parent-author" class="mb-0"></h6>
                                    <div id="view-parent-date" class="text-muted small"></div>
                                </div>
                            </div>
                            <div id="view-parent-content" class="ps-5"></div>
                        </div>
                    </div>

                    <div id="view-meta" class="card mb-3">
                        <div class="card-header">
                            Additional Information
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
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" id="viewEditBtn" class="btn btn-primary">Edit</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <style>
        #comments-table td.content-cell {
            max-width: 300px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .comment-highlight {
            background-color: rgba(255, 251, 214, 0.5);
            padding: 0.5rem;
            border-radius: 0.25rem;
            border-left: 3px solid #ffc107;
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
@endpush