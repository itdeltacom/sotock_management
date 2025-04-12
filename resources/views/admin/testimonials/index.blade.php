@extends('admin.layouts.master')

@section('title', 'Testimonial Management')

@section('content')
    <div class="page-header">
        <h3 class="page-title">Testimonial Management</h3>
        <div class="page-actions">
            @can('create testimonials')
                <button type="button" class="btn btn-primary" id="createTestimonialBtn">
                    <i class="fas fa-plus"></i> Add New Testimonial
                </button>
            @endcan
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title">Filter Testimonials</h5>
        </div>
        <div class="card-body">
            <form id="filterForm" class="row g-3">
                <div class="col-md-3">
                    <label for="statusFilter" class="form-label">Status</label>
                    <select class="form-select" id="statusFilter" name="status">
                        <option value="">All Statuses</option>
                        <option value="approved">Approved</option>
                        <option value="pending">Pending</option>
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
                <div class="col-md-3">
                    <label for="featuredFilter" class="form-label">Featured</label>
                    <select class="form-select" id="featuredFilter" name="is_featured">
                        <option value="">All Testimonials</option>
                        <option value="1">Featured Only</option>
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
            <h5 class="card-title">All Testimonials</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="testimonials-table" width="100%">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Position</th>
                            <th>Rating</th>
                            <th>Status</th>
                            <th>Featured</th>
                            <th>Date</th>
                            @if(auth()->guard('admin')->user()->can('edit testimonials') || auth()->guard('admin')->user()->can('delete testimonials'))
                                <th>Actions</th>
                            @endif
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <!-- Create/Edit Testimonial Modal -->
    <div class="modal fade" id="testimonialModal" tabindex="-1" aria-labelledby="testimonialModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="testimonialModalLabel">Add New Testimonial</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="testimonialForm" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="testimonial_id" id="testimonial_id">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="user_name" class="form-label">Name <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="user_name" name="user_name" required>
                                    <div class="invalid-feedback" id="user_name-error"></div>
                                </div>
                                <div class="mb-3">
                                    <label for="user_title" class="form-label">Position/Title</label>
                                    <input type="text" class="form-control" id="user_title" name="user_title">
                                    <div class="invalid-feedback" id="user_title-error"></div>
                                </div>
                                <div class="mb-3">
                                    <label for="user_email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="user_email" name="user_email">
                                    <div class="invalid-feedback" id="user_email-error"></div>
                                </div>
                                <div class="mb-3">
                                    <label for="rating" class="form-label">Rating <span class="text-danger">*</span></label>
                                    <div class="rating-selector">
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="rating" id="rating1"
                                                value="1">
                                            <label class="form-check-label" for="rating1">1 <i
                                                    class="fas fa-star text-warning"></i></label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="rating" id="rating2"
                                                value="2">
                                            <label class="form-check-label" for="rating2">2 <i
                                                    class="fas fa-star text-warning"></i></label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="rating" id="rating3"
                                                value="3">
                                            <label class="form-check-label" for="rating3">3 <i
                                                    class="fas fa-star text-warning"></i></label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="rating" id="rating4"
                                                value="4">
                                            <label class="form-check-label" for="rating4">4 <i
                                                    class="fas fa-star text-warning"></i></label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="rating" id="rating5"
                                                value="5" checked>
                                            <label class="form-check-label" for="rating5">5 <i
                                                    class="fas fa-star text-warning"></i></label>
                                        </div>
                                    </div>
                                    <div class="invalid-feedback" id="rating-error"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="image" class="form-label">Profile Image</label>
                                    <input type="file" class="form-control" id="image" name="image" accept="image/*">
                                    <div class="invalid-feedback" id="image-error"></div>
                                    <div class="form-text">Recommended size: 150 x 150 pixels</div>
                                </div>
                                <div id="image-preview" class="mt-2 d-none">
                                    <img src="" alt="Profile Image" class="img-thumbnail rounded-circle"
                                        style="width: 100px; height: 100px; object-fit: cover;">
                                </div>
                                <div class="mb-3 mt-3">
                                    <label for="order" class="form-label">Display Order</label>
                                    <input type="number" class="form-control" id="order" name="order" min="0" value="0">
                                    <div class="invalid-feedback" id="order-error"></div>
                                    <div class="form-text">Lower numbers will display first</div>
                                </div>
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="is_approved" name="is_approved"
                                            checked>
                                        <label class="form-check-label" for="is_approved">Approved</label>
                                    </div>
                                    <div class="invalid-feedback" id="is_approved-error"></div>
                                </div>
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured">
                                        <label class="form-check-label" for="is_featured">Featured Testimonial</label>
                                    </div>
                                    <div class="invalid-feedback" id="is_featured-error"></div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="content" class="form-label">Testimonial <span
                                            class="text-danger">*</span></label>
                                    <textarea class="form-control" id="content" name="content" rows="4" required></textarea>
                                    <div class="invalid-feedback" id="content-error"></div>
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
                    <p>Are you sure you want to delete the testimonial from "<strong
                            id="delete-testimonial-name"></strong>"?</p>
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
        /* Modal styling */
        .modal-content {
            opacity: 1 !important;
            background-color: #fff !important;
        }

        /* Image preview */
        #image-preview img {
            border: 1px solid #dee2e6;
            border-radius: 50%;
            object-fit: cover;
        }

        /* Rating stars in the table */
        .table .fas.fa-star.text-warning {
            color: #ffc107 !important;
        }

        .table .far.fa-star.text-muted {
            color: #6c757d !important;
        }

        /* Sort handle for reordering */
        .sort-handle {
            cursor: grab;
            color: #6c757d;
        }

        .sort-handle:active {
            cursor: grabbing;
        }
    </style>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
@endpush

@push('js')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <script>
        // Routes for AJAX calls
        const routes = {
            dataUrl: "{{ route('admin.testimonials.data') }}",
            storeUrl: "{{ route('admin.testimonials.store') }}",
            editUrl: "{{ route('admin.testimonials.edit', ':id') }}",
            updateUrl: "{{ route('admin.testimonials.update', ':id') }}",
            deleteUrl: "{{ route('admin.testimonials.destroy', ':id') }}",
            toggleFeaturedUrl: "{{ route('admin.testimonials.toggle-featured', ':id') }}",
            toggleApprovalUrl: "{{ route('admin.testimonials.toggle-approval', ':id') }}",
            updateOrderUrl: "{{ route('admin.testimonials.update-order') }}"
        };

        // Pass permissions data to JavaScript
        const canEditTestimonials = @json(auth()->guard('admin')->user()->can('edit testimonials'));
        const canDeleteTestimonials = @json(auth()->guard('admin')->user()->can('delete testimonials'));
    </script>

    <!-- Include your testimonial management JS file -->
    <script src="{{ asset('admin/js/testimonial-management.js') }}"></script>
@endpush