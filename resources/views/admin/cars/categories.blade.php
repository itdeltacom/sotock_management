@extends('admin.layouts.master')

@section('title', 'Categories Management')

@section('content')
    <div class="page-header">
        <h3 class="page-title">Car Categories Management</h3>
        <div class="page-actions">
            @can('create categories')
                <button type="button" class="btn btn-primary" id="createCategoryBtn">
                    <i class="fas fa-plus"></i> Add New Category
                </button>
            @endcan
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title">All Categories</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="categories-table" width="100%">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Slug</th>
                            <th>Cars</th>
                            <th>Status</th>
                            @if(auth()->guard('admin')->user()->can('edit categories') || auth()->guard('admin')->user()->can('delete categories'))
                                <th>Actions</th>
                            @endif
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <!-- Create/Edit Category Modal -->
    <div class="modal fade" id="categoryModal" tabindex="-1" aria-labelledby="categoryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="categoryModalLabel">Add New Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="categoryForm" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="category_id" id="category_id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">Category Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                            <div class="invalid-feedback" id="name-error"></div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                            <div class="invalid-feedback" id="description-error"></div>
                        </div>

                        <div class="mb-3">
                            <label for="image" class="form-label">Image</label>
                            <input type="file" class="form-control" id="image" name="image">
                            <div class="invalid-feedback" id="image-error"></div>
                            <div id="image-preview" class="mt-2 d-none">
                                <img src="" alt="Category Image" class="img-thumbnail" style="max-height: 150px;">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="is_active" class="form-label">Status</label>
                            <select class="form-select" id="is_active" name="is_active">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                            <div class="invalid-feedback" id="is_active-error"></div>
                        </div>

                        <div class="border-top pt-3 mt-4 mb-3">
                            <h5>SEO Settings (Optional)</h5>
                        </div>

                        <div class="mb-3">
                            <label for="meta_title" class="form-label">Meta Title</label>
                            <input type="text" class="form-control" id="meta_title" name="meta_title">
                            <div class="invalid-feedback" id="meta_title-error"></div>
                        </div>

                        <div class="mb-3">
                            <label for="meta_description" class="form-label">Meta Description</label>
                            <textarea class="form-control" id="meta_description" name="meta_description"
                                rows="2"></textarea>
                            <div class="invalid-feedback" id="meta_description-error"></div>
                        </div>

                        <div class="mb-3">
                            <label for="meta_keywords" class="form-label">Meta Keywords</label>
                            <input type="text" class="form-control" id="meta_keywords" name="meta_keywords">
                            <div class="invalid-feedback" id="meta_keywords-error"></div>
                            <small class="form-text text-muted">Separate keywords with commas</small>
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
                    <p>Are you sure you want to delete this category? This action cannot be undone.</p>
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
        // Routes for AJAX calls
        const routes = {
            dataUrl: "{{ route('admin.categories.data') }}",
            storeUrl: "{{ route('admin.categories.store') }}",
            editUrl: "{{ route('admin.categories.edit', ':id') }}",
            updateUrl: "{{ route('admin.categories.update', ':id') }}",
            deleteUrl: "{{ route('admin.categories.destroy', ':id') }}"
        };

        // Pass permissions data to JavaScript
        const canEditCategories = @json(auth()->guard('admin')->user()->can('edit categories'));
        const canDeleteCategories = @json(auth()->guard('admin')->user()->can('delete categories'));
    </script>

    <!-- Include the JS for categories management -->
    <script src="{{ asset('admin/js/categories-management.js') }}"></script>
@endpush