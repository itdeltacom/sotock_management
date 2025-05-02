@extends('admin.layouts.master')

@section('title', 'Blog Categories Management')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Blog Categories Management</h3>
                        <div class="card-tools">
                            @can('create blog categories')
                                <button type="button" class="btn btn-primary btn-sm" id="createCategoryBtn">
                                    <i class="fas fa-plus"></i> Add New Category
                                </button>
                            @endcan
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="categories-table" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Slug</th>
                                        <th>Parent</th>
                                        <th>Description</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- DataTables will fill this -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create/Edit Category Modal -->
    <div class="modal fade" id="categoryModal" tabindex="-1" aria-labelledby="categoryModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="categoryModalLabel">Add New Category</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="categoryForm">
                    @csrf
                    <input type="hidden" name="category_id" id="category_id">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="name">Category Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" required>
                            <div class="invalid-feedback" id="name-error"></div>
                            <small class="form-text text-muted">The name is how the category appears on your site.</small>
                        </div>

                        <div class="form-group">
                            <label for="parent_id">Parent Category</label>
                            <select class="form-control" id="parent_id" name="parent_id">
                                <option value="">None (Top Level)</option>
                                @foreach(\App\Models\BlogCategory::where('is_active', true)->orderBy('name')->get() as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback" id="parent_id-error"></div>
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                            <div class="invalid-feedback" id="description-error"></div>
                        </div>

                        <div class="form-group">
                            <label for="is_active">Status</label>
                            <select class="form-control" id="is_active" name="is_active">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                            <div class="invalid-feedback" id="is_active-error"></div>
                        </div>

                        <div class="form-group">
                            <label for="meta_title">Meta Title</label>
                            <input type="text" class="form-control" id="meta_title" name="meta_title">
                            <div class="invalid-feedback" id="meta_title-error"></div>
                        </div>

                        <div class="form-group">
                            <label for="meta_description">Meta Description</label>
                            <textarea class="form-control" id="meta_description" name="meta_description"
                                rows="2"></textarea>
                            <div class="invalid-feedback" id="meta_description-error"></div>
                        </div>

                        <div class="form-group">
                            <label for="meta_keywords">Meta Keywords</label>
                            <input type="text" class="form-control" id="meta_keywords" name="meta_keywords">
                            <div class="invalid-feedback" id="meta_keywords-error"></div>
                            <small class="form-text text-muted">Separate keywords with commas</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
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
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this category? This action cannot be undone.</p>
                    <p class="text-danger font-weight-bold category-name"></p>
                    <p class="text-danger font-weight-bold">
                        Warning: You cannot delete categories that contain posts or subcategories!
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
    <style>
        /* Blog Categories Management Styles - Argon-inspired */

        /* Card Styling */
        .card {
            box-shadow: 0 20px 27px 0 rgba(0, 0, 0, 0.05);
            border-radius: 0.75rem;
        }

        .card .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.5rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .card-header h3 {
            margin-bottom: 0;
            font-size: 1.25rem;
            color: #344767;
            font-weight: 600;
        }

        .card-tools .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* Table Styling */
        .table {
            margin-bottom: 0;
        }

        .table thead th {
            padding: 0.75rem 1.5rem;
            text-transform: uppercase;
            letter-spacing: 0.025em;
            font-size: 0.65rem;
            font-weight: 700;
            color: #8392AB;
            border-bottom: 1px solid #E9ECEF;
            vertical-align: middle;
        }

        .table td {
            padding: 0.75rem 1.5rem;
            font-size: 0.875rem;
            vertical-align: middle;
            border-bottom: 1px solid #E9ECEF;
        }

        /* Table Column Specific Styles */
        .table td.truncate-text {
            max-width: 250px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .child-category {
            padding-left: 20px;
            color: #8392AB;
        }

        .child-category::before {
            content: "— ";
            color: #8392AB;
        }

        /* Status Badges */
        .badge-status-active {
            background: linear-gradient(310deg, #2dce89 0%, #2dcecc 100%);
            color: white;
            font-weight: 600;
            padding: 2px 10px;
            border-radius: 0.5rem;
            display: inline-block;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .badge-status-inactive {
            background: linear-gradient(310deg, #67748e 0%, #344767 100%);
            color: white;
            font-weight: 600;
            padding: 2px 10px;
            border-radius: 0.5rem;
            display: inline-block;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        /* Form Inputs */
        .form-control {
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
            line-height: 1.4;
            border-radius: 0.5rem;
            transition: box-shadow 0.15s ease, border-color 0.15s ease;
        }

        .form-control:focus {
            border-color: #5e72e4;
            box-shadow: 0 3px 9px rgba(0, 0, 0, 0), 3px 4px 8px rgba(94, 114, 228, 0.1);
        }

        .form-label,
        .form-group label {
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
            font-weight: 600;
            color: #8392AB;
        }

        .form-text.text-muted {
            font-size: 0.75rem;
            color: #8392AB !important;
        }

        /* Buttons */
        .btn-primary {
            background: linear-gradient(310deg, #5e72e4 0%, #825ee4 100%);
            border: none;
            box-shadow: 0 3px 5px -1px rgba(94, 114, 228, 0.2), 0 2px 3px -1px rgba(94, 114, 228, 0.1);
        }

        .btn-secondary {
            background: linear-gradient(310deg, #67748e 0%, #344767 100%);
            color: white;
            border: none;
        }

        .btn-danger {
            background: linear-gradient(310deg, #f5365c 0%, #f56036 100%);
            border: none;
        }

        /* Modal Styling */
        .modal-content {
            border: 0;
            border-radius: 0.75rem;
            box-shadow: 0 10px 35px -5px rgba(0, 0, 0, 0.15);
        }

        .modal-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .modal-header .close {
            margin: -1.25rem -1.5rem -1.25rem auto;
            padding: 1.25rem;
            color: #344767;
            opacity: 0.5;
            transition: all 0.15s ease;
        }

        .modal-header .close:hover {
            opacity: 1;
            color: #f5365c;
        }

        .modal-footer {
            padding: 1.25rem 1.5rem;
            border-top: 1px solid rgba(0, 0, 0, 0.05);
        }

        /* DataTables Styling */
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

        /* Warning and Danger Text */
        .text-danger {
            color: #f5365c !important;
        }

        .font-weight-bold {
            font-weight: 600;
        }
    </style>
@endpush

@push('js')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // Define route URLs for AJAX calls
        const routes = {
            dataUrl: "{{ route('admin.blog-categories.data') }}",
            storeUrl: "{{ route('admin.blog-categories.store') }}",
            editUrl: "{{ route('admin.blog-categories.edit', ':id') }}",
            updateUrl: "{{ route('admin.blog-categories.update', ':id') }}",
            destroyUrl: "{{ route('admin.blog-categories.destroy', ':id') }}",
            validateFieldUrl: "{{ route('admin.blog-categories.validate-field') }}"
        };

        // Pass permissions data to JavaScript
        const canEditCategories = @json(auth()->guard('admin')->user()->can('edit blog categories'));
        const canDeleteCategories = @json(auth()->guard('admin')->user()->can('delete blog categories'));
    </script>

    <!-- Include the JS for categories management -->
    <script src="{{ asset('admin/js/blog-categories-management.js') }}"></script>
@endpush