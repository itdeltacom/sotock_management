@extends('admin.layouts.master')

@section('title', 'Review Management')

@section('content')
    <div class="page-header">
        <h3 class="page-title">Review Management</h3>
        <div class="page-actions">
            @can('create reviews')
                <button type="button" class="btn btn-primary" id="createReviewBtn">
                    <i class="fas fa-plus"></i> Add New Review
                </button>
            @endcan
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title">Filter Reviews</h5>
        </div>
        <div class="card-body">
            <form id="filterForm" class="row g-3">
                <div class="col-md-3">
                    <label for="carFilter" class="form-label">Car</label>
                    <select class="form-select" id="carFilter" name="car_id">
                        <option value="">All Cars</option>
                        @foreach($cars as $car)
                            <option value="{{ $car->id }}">{{ $car->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="approvalFilter" class="form-label">Approval Status</label>
                    <select class="form-select" id="approvalFilter" name="approval_status">
                        <option value="">All Statuses</option>
                        <option value="1">Approved</option>
                        <option value="0">Pending</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="ratingFilter" class="form-label">Rating</label>
                    <select class="form-select" id="ratingFilter" name="rating">
                        <option value="">All Ratings</option>
                        <option value="5">5 Stars</option>
                        <option value="4">4 Stars</option>
                        <option value="3">3 Stars</option>
                        <option value="2">2 Stars</option>
                        <option value="1">1 Star</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
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
            <h5 class="card-title">All Reviews</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="reviews-table" width="100%">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Car</th>
                            <th>Reviewer</th>
                            <th>Rating</th>
                            <th>Comment</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <!-- Create/Edit Review Modal -->
    <div class="modal fade" id="reviewModal" tabindex="-1" aria-labelledby="reviewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="reviewModalLabel">Add New Review</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="reviewForm">
                    @csrf
                    <input type="hidden" name="review_id" id="review_id">
                    <div class="modal-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="car_id" class="form-label">Car <span class="text-danger">*</span></label>
                                <select class="form-select" id="car_id" name="car_id" required>
                                    <option value="">Select Car</option>
                                    @foreach($cars as $car)
                                        <option value="{{ $car->id }}">{{ $car->name }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback" id="car_id-error"></div>
                            </div>
                            <div class="col-md-6">
                                <label for="rating" class="form-label">Rating <span class="text-danger">*</span></label>
                                <div class="star-rating">
                                    <div class="star-input">
                                        <input type="radio" name="rating" id="rating-5" value="5">
                                        <label for="rating-5" class="fas fa-star"></label>
                                        <input type="radio" name="rating" id="rating-4" value="4">
                                        <label for="rating-4" class="fas fa-star"></label>
                                        <input type="radio" name="rating" id="rating-3" value="3">
                                        <label for="rating-3" class="fas fa-star"></label>
                                        <input type="radio" name="rating" id="rating-2" value="2">
                                        <label for="rating-2" class="fas fa-star"></label>
                                        <input type="radio" name="rating" id="rating-1" value="1">
                                        <label for="rating-1" class="fas fa-star"></label>
                                    </div>
                                    <div class="invalid-feedback" id="rating-error"></div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="comment" class="form-label">Comment <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="comment" name="comment" rows="4" required></textarea>
                            <div class="invalid-feedback" id="comment-error"></div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_approved" name="is_approved" checked>
                                <label class="form-check-label" for="is_approved">Approved</label>
                            </div>
                            <div class="invalid-feedback" id="is_approved-error"></div>
                        </div>

                        <hr>
                        <h6>Reviewer Information</h6>

                        <div class="mb-3">
                            <label for="user_id" class="form-label">Select Existing User</label>
                            <select class="form-select" id="user_id" name="user_id">
                                <option value="">Guest Review (No User)</option>
                            </select>
                            <div class="form-text">Leave empty to create a guest review</div>
                        </div>

                        <div id="guest-info">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="reviewer_name" class="form-label">Name <span
                                                class="text-danger reviewer-required">*</span></label>
                                        <input type="text" class="form-control" id="reviewer_name" name="reviewer_name">
                                        <div class="invalid-feedback" id="reviewer_name-error"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="reviewer_email" class="form-label">Email <span
                                                class="text-danger reviewer-required">*</span></label>
                                        <input type="email" class="form-control" id="reviewer_email" name="reviewer_email">
                                        <div class="invalid-feedback" id="reviewer_email-error"></div>
                                    </div>
                                </div>
                            </div>
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

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this review?</p>
                    <p class="text-danger">This action cannot be undone.</p>
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
    <style>
        /* Star Rating CSS */
        .star-rating {
            direction: rtl;
            display: inline-block;
            padding: 0.5rem 0;
        }

        .star-rating input[type=radio] {
            display: none;
        }

        .star-rating label {
            color: #bbb;
            cursor: pointer;
            font-size: 1.5rem;
            padding: 0 0.1rem;
        }

        .star-rating label:hover,
        .star-rating label:hover~label,
        .star-rating input[type=radio]:checked~label {
            color: #f90;
        }

        /* Comment cell max width */
        #reviews-table .comment-cell {
            max-width: 300px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
    </style>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css"
        rel="stylesheet" />
@endpush

@push('js')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        // Routes for AJAX calls
        const routes = {
            dataUrl: "{{ route('admin.reviews.data') }}",
            storeUrl: "{{ route('admin.reviews.store') }}",
            editUrl: "{{ route('admin.reviews.edit', ':id') }}",
            updateUrl: "{{ route('admin.reviews.update', ':id') }}",
            deleteUrl: "{{ route('admin.reviews.destroy', ':id') }}",
            toggleApprovalUrl: "{{ route('admin.reviews.toggle-approval', ':id') }}",
            getUsersUrl: "{{ route('admin.reviews.get-users') }}"
        };

        // Pass permissions data to JavaScript
        const canEditReviews = @json(auth()->guard('admin')->user()->can('edit reviews'));
        const canDeleteReviews = @json(auth()->guard('admin')->user()->can('delete reviews'));
    </script>

    <script src="{{ asset('admin/js/reviews-management.js') }}"></script>
@endpush