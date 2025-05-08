@extends('admin.layouts.master')

@section('title', 'Testimonial Management')

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header pb-0 p-3">
                        <div class="row">
                            <div class="col-6 d-flex align-items-center">
                                <h6 class="mb-0">Testimonial Management</h6>
                            </div>
                            <div class="col-6 text-end">
                                @can('create testimonials')
                                    <button type="button" class="btn bg-gradient-primary" id="createTestimonialBtn">
                                        <i class="fas fa-plus"></i> Add New Testimonial
                                    </button>
                                @endcan
                            </div>
                        </div>
                    </div>
                    
                    <!-- Filters -->
                    <div class="card-header pb-0 p-3">
                        <form id="filterForm" class="row g-3">
                            <div class="col-md-3">
                                <select class="form-select form-select-sm" id="statusFilter" name="status">
                                    <option value="">All Statuses</option>
                                    <option value="approved">Approved</option>
                                    <option value="pending">Pending</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select form-select-sm" id="ratingFilter" name="rating">
                                    <option value="">All Ratings</option>
                                    <option value="5">5 Stars</option>
                                    <option value="4">4 Stars</option>
                                    <option value="3">3 Stars</option>
                                    <option value="2">2 Stars</option>
                                    <option value="1">1 Star</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select form-select-sm" id="featuredFilter" name="is_featured">
                                    <option value="">All Testimonials</option>
                                    <option value="1">Featured Only</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <div class="d-flex">
                                    <button type="submit" class="btn btn-sm bg-gradient-info me-2">
                                        <i class="fas fa-filter"></i> Apply
                                    </button>
                                    <button type="button" id="resetFilterBtn" class="btn btn-sm bg-gradient-secondary">
                                        <i class="fas fa-undo"></i> Reset
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                    
                    <div class="card-body px-0 pt-0 pb-2">
                        <div class="table-responsive p-0">
                            <table class="table align-items-center mb-0" id="testimonials-table" width="100%">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">ID</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Image</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Name</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Position</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Rating</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Featured</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Date</th>
                                        @if(auth()->guard('admin')->user()->can('edit testimonials') || auth()->guard('admin')->user()->can('delete testimonials'))
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Actions</th>
                                        @endif
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create/Edit Testimonial Modal -->
    <div class="modal fade" id="testimonialModal" tabindex="-1" aria-labelledby="testimonialModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
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
                                    <label for="user_name" class="form-control-label">Name <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="user_name" name="user_name" required>
                                    <div class="invalid-feedback" id="user_name-error"></div>
                                </div>
                                <div class="mb-3">
                                    <label for="user_title" class="form-control-label">Position/Title</label>
                                    <input type="text" class="form-control" id="user_title" name="user_title">
                                    <div class="invalid-feedback" id="user_title-error"></div>
                                </div>
                                <div class="mb-3">
                                    <label for="user_email" class="form-control-label">Email</label>
                                    <input type="email" class="form-control" id="user_email" name="user_email">
                                    <div class="invalid-feedback" id="user_email-error"></div>
                                </div>
                                <div class="mb-3">
                                    <label for="rating" class="form-control-label">Rating <span class="text-danger">*</span></label>
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
                                    <label for="image" class="form-control-label">Profile Image</label>
                                    <input type="file" class="form-control" id="image" name="image" accept="image/*">
                                    <div class="invalid-feedback" id="image-error"></div>
                                    <div class="form-text">Recommended size: 150 x 150 pixels</div>
                                </div>
                                <div id="image-preview" class="mt-2 d-none">
                                    <img src="" alt="Profile Image" class="img-thumbnail rounded-circle"
                                        style="width: 100px; height: 100px; object-fit: cover;">
                                </div>
                                <div class="mb-3 mt-3">
                                    <label for="order" class="form-control-label">Display Order</label>
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
                                    <label for="content" class="form-control-label">Testimonial <span
                                            class="text-danger">*</span></label>
                                    <textarea class="form-control" id="content" name="content" rows="4" required></textarea>
                                    <div class="invalid-feedback" id="content-error"></div>
                                </div>
                            </div>
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
                        <p>Are you sure you want to delete the testimonial from "<strong
                                id="delete-testimonial-name"></strong>"?</p>
                        <p class="text-danger">This action cannot be undone.</p>
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
            opacity: 1 !important;
            background-color: #fff !important;
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

        /* Image preview */
        #image-preview img {
            border: 1px solid #dee2e6;
            border-radius: 50%;
            object-fit: cover;
            box-shadow: 0 3px 6px rgba(0,0,0,0.1);
        }

        /* Rating stars in the table */
        .fas.fa-star.text-warning {
            color: #ffc107 !important;
        }

        .far.fa-star.text-muted {
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

        /* Form switch styling */
        .form-check-input:checked {
            background-color: #5e72e4;
            border-color: #5e72e4;
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

            // Image preview functionality
            document.getElementById('image').addEventListener('change', function() {
                const preview = document.getElementById('image-preview');
                const image = preview.querySelector('img');
                
                if (this.files && this.files[0]) {
                    const reader = new FileReader();
                    
                    reader.onload = function(e) {
                        image.src = e.target.result;
                        preview.classList.remove('d-none');
                    }
                    
                    reader.readAsDataURL(this.files[0]);
                } else {
                    preview.classList.add('d-none');
                }
            });
            
            // Reset filter button
            document.getElementById('resetFilterBtn').addEventListener('click', function() {
                document.getElementById('filterForm').reset();
                document.getElementById('filterForm').dispatchEvent(new Event('submit'));
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
        });
    </script>

    <!-- Include your testimonial management JS file -->
    <script src="{{ asset('admin/js/testimonial-management.js') }}"></script>
@endpush