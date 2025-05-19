@extends('admin.layouts.master')

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                        <h6>Product Categories</h6>
                        @can('create categories')
                            <button class="btn btn-primary btn-sm ms-auto" id="btn-add-category">
                                <i class="fas fa-plus me-1"></i> Add Category
                            </button>
                        @endcan
                    </div>
                    <div class="card-body px-0 pt-0 pb-2">
                        @if(!isset($tableExists) || $tableExists)
                            <div class="row mx-4 mt-3">
                                <div class="col-md-3 col-sm-6 mb-3">
                                    <div class="card text-white bg-gradient-primary">
                                        <div class="card-body p-3">
                                            <div class="row">
                                                <div class="col-8">
                                                    <div class="numbers">
                                                        <p class="text-sm mb-0 text-uppercase font-weight-bold">Total Categories
                                                        </p>
                                                        <h5 class="font-weight-bolder text-white mt-2">
                                                            {{ $totalCategories ?? 0 }}
                                                        </h5>
                                                    </div>
                                                </div>
                                                <div class="col-4 text-end">
                                                    <div class="icon icon-shape bg-white shadow text-center rounded-circle">
                                                        <i class="ni ni-collection text-primary text-lg opacity-10"
                                                            aria-hidden="true"></i>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-6 mb-3">
                                    <div class="card text-white bg-gradient-success">
                                        <div class="card-body p-3">
                                            <div class="row">
                                                <div class="col-8">
                                                    <div class="numbers">
                                                        <p class="text-sm mb-0 text-uppercase font-weight-bold">Active
                                                            Categories</p>
                                                        <h5 class="font-weight-bolder text-white mt-2">
                                                            {{ $activeCategories ?? 0 }}
                                                        </h5>
                                                    </div>
                                                </div>
                                                <div class="col-4 text-end">
                                                    <div class="icon icon-shape bg-white shadow text-center rounded-circle">
                                                        <i class="ni ni-check-bold text-success text-lg opacity-10"
                                                            aria-hidden="true"></i>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="table-responsive p-3">
                                <table class="table align-items-center justify-content-center mb-0" id="categories-table">
                                    <thead>
                                        <tr>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Logo
                                            </th>
                                            <th
                                                class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                                Name</th>
                                            <th
                                                class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                                Code</th>
                                            <th
                                                class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                                Parent</th>
                                            <th
                                                class="text-uppercase text-secondary text-xxs font-weight-bolder text-center opacity-7 ps-2">
                                                Status</th>
                                            <th
                                                class="text-uppercase text-secondary text-xxs font-weight-bolder text-center opacity-7 ps-2">
                                                Products</th>
                                            <th
                                                class="text-uppercase text-secondary text-xxs font-weight-bolder text-end opacity-7">
                                                Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- DataTables will fill this -->
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-warning mx-4 mt-4">
                                {{ $errorMessage ?? 'Database table not found. Please run migrations to create the necessary database structure.' }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Add/Edit Category Modal -->
        <div class="modal fade" id="categoryModal" tabindex="-1" role="dialog" aria-labelledby="categoryModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="categoryModalLabel">Add New Category</h5>
                        <button type="button" class="btn-close text-dark" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <form id="categoryForm" enctype="multipart/form-data">
                        <div class="modal-body">
                            @csrf
                            <input type="hidden" name="_method" id="method" value="POST">
                            <input type="hidden" name="id" id="category_id">

                            <div class="form-group">
                                <label for="name" class="form-control-label">Category Name <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" required>
                                <div class="invalid-feedback" id="name-error"></div>
                            </div>

                            <div class="form-group mt-3">
                                <label for="slug" class="form-control-label">Slug</label>
                                <input type="text" class="form-control" id="slug" name="slug" readonly>
                                <small class="form-text text-muted">Auto-generated from Category Name</small>
                                <div class="invalid-feedback" id="slug-error"></div>
                            </div>

                            <div class="form-group mt-3">
                                <label for="code" class="form-control-label">Code</label>
                                <input type="text" class="form-control" id="code" name="code">
                                <small class="form-text text-muted">Optional unique code for internal reference</small>
                                <div class="invalid-feedback" id="code-error"></div>
                            </div>

                            <div class="form-group mt-3">
                                <label for="parent_id" class="form-control-label">Parent Category</label>
                                <select class="form-control" id="parent_id" name="parent_id">
                                    <option value="">None</option>
                                    @foreach (\App\Models\ProductCategory::whereNull('parent_id')->with('children')->get() as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @foreach ($category->children as $child)
                                            <option value="{{ $child->id }}">-- {{ $child->name }}</option>
                                            @foreach ($child->children as $grandchild)
                                                <option value="{{ $grandchild->id }}">---- {{ $grandchild->name }}</option>
                                            @endforeach
                                        @endforeach
                                    @endforeach
                                </select>
                                <div class="invalid-feedback" id="parent_id-error"></div>
                            </div>

                            <div class="form-group mt-3">
                                <label for="website" class="form-control-label">Website URL</label>
                                <input type="url" class="form-control" id="website" name="website"
                                    placeholder="https://example.com">
                                <div class="invalid-feedback" id="website-error"></div>
                            </div>

                            <div class="form-group mt-3">
                                <label for="description" class="form-control-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                                <div class="invalid-feedback" id="description-error"></div>
                            </div>

                            <div class="form-group mt-3">
                                <label for="meta_title" class="form-control-label">Meta Title</label>
                                <input type="text" class="form-control" id="meta_title" name="meta_title" maxlength="100">
                                <small class="form-text text-muted">Optional SEO title (max 100 characters)</small>
                                <div class="invalid-feedback" id="meta_title-error"></div>
                            </div>

                            <div class="form-group mt-3">
                                <label for="meta_description" class="form-control-label">Meta Description</label>
                                <textarea class="form-control" id="meta_description" name="meta_description" rows="3"
                                    maxlength="255"></textarea>
                                <small class="form-text text-muted">Optional SEO description (max 255 characters)</small>
                                <div class="invalid-feedback" id="meta_description-error"></div>
                            </div>

                            <div class="form-group mt-3">
                                <label for="meta_keywords" class="form-control-label">Meta Keywords</label>
                                <input type="text" class="form-control" id="meta_keywords" name="meta_keywords"
                                    maxlength="255">
                                <small class="form-text text-muted">Optional SEO keywords, comma-separated (max 255
                                    characters)</small>
                                <div class="invalid-feedback" id="meta_keywords-error"></div>
                            </div>

                            <div class="form-group mt-3">
                                <label for="logo" class="form-control-label">Logo</label>
                                <input type="file" class="form-control" id="logo" name="logo" accept="image/*">
                                <small class="form-text text-muted">Maximum file size: 2MB. Recommended dimensions:
                                    200x200px</small>
                                <div class="invalid-feedback" id="logo-error"></div>

                                <div id="logo-preview-container" class="d-none mt-2">
                                    <img id="logo-preview" src="#" alt="Logo Preview" class="img-thumbnail"
                                        style="max-height: 150px;">
                                    <button type="button" class="btn btn-sm btn-danger mt-1" id="remove-logo">
                                        <i class="fas fa-trash"></i> Remove Logo
                                    </button>
                                </div>
                            </div>

                            <div class="form-check form-switch mt-3">
                                <input class="form-check-input" type="checkbox" id="active" name="active" checked>
                                <label class="form-check-label" for="active">Active</label>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary" id="save-category">Save Category</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- View Category Modal -->
        <div class="modal fade" id="viewCategoryModal" tabindex="-1" role="dialog" aria-labelledby="viewCategoryModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="viewCategoryModalLabel">Category Details</h5>
                        <button type="button" class="btn-close text-dark" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-4 text-center mb-4">
                                <div id="view-logo-container" class="mb-3">
                                    <img id="view-logo" src="#" alt="Category Logo" class="img-fluid rounded shadow-sm"
                                        style="max-width: 150px; max-height: 150px;">
                                </div>
                                <div id="no-logo-container" class="d-none mb-3">
                                    <div class="rounded bg-light d-flex align-items-center justify-content-center"
                                        style="width: 150px; height: 150px; margin: 0 auto;">
                                        <i class="ni ni-image text-muted" style="font-size: 3rem;"></i>
                                    </div>
                                </div>
                                <span class="badge badge-pill bg-gradient-success" id="view-status">Active</span>
                            </div>
                            <div class="col-md-8">
                                <h3 id="view-name" class="font-weight-bold mb-0"></h3>
                                <p id="view-code" class="text-sm text-muted"></p>
                                <p id="view-slug" class="text-sm text-muted"></p>
                                <p id="view-parent" class="text-sm text-muted"></p>

                                <p class="mt-3 mb-1 text-sm">Website:</p>
                                <p id="view-website" class="font-weight-bold"></p>

                                <p class="mt-3 mb-1 text-sm">Description:</p>
                                <p id="view-description" class="mb-0"></p>

                                <p class="mt-3 mb-1 text-sm">Meta Title:</p>
                                <p id="view-meta_title" class="font-weight-bold"></p>

                                <p class="mt-3 mb-1 text-sm">Meta Description:</p>
                                <p id="view-meta_description" class="mb-0"></p>

                                <p class="mt-3 mb-1 text-sm">Meta Keywords:</p>
                                <p id="view-meta_keywords" class="mb-0"></p>
                            </div>
                        </div>

                        <div class="row mt-4" id="products-section">
                            <div class="col-12">
                                <h6 class="font-weight-bold">Products in this category</h6>
                                <div id="products-list" class="row">
                                    <!-- Will be filled with products -->
                                </div>
                                <div id="no-products" class="d-none text-center py-4">
                                    <i class="ni ni-app text-muted mb-2" style="font-size: 2rem;"></i>
                                    <p class="text-muted">No products found for this category</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        @can('edit categories')
                            <button type="button" class="btn btn-primary" id="btn-edit-view">Edit Category</button>
                        @endcan
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div class="modal fade" id="deleteCategoryModal" tabindex="-1" role="dialog"
            aria-labelledby="deleteCategoryModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteCategoryModalLabel">Confirm Delete</h5>
                        <button type="button" class="btn-close text-dark" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        Are you sure you want to delete this category: <span id="delete-category-name"
                            class="font-weight-bold"></span>?
                        <input type="hidden" id="delete-category-id">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-danger" id="confirm-delete">Delete</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
    <style>
        .category-logo-thumbnail {
            object-fit: contain;
            height: 40px;
            width: 40px;
        }

        #view-description {
            max-height: 120px;
            overflow-y: auto;
        }

        .product-card {
            border-radius: 10px;
            transition: all 0.2s ease;
            height: 100%;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        input[readonly] {
            background-color: #f8f9fa;
            cursor: not-allowed;
        }
    </style>
@endpush

@push('js')
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function () {
            // Initialize DataTable for categories
            var table = $('#categories-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.categories.data') }}",
                columns: [
                    {
                        data: 'logo_image',
                        name: 'logo_image',
                        orderable: false,
                        searchable: false
                    },
                    { data: 'name', name: 'name' },
                    { data: 'code', name: 'code' },
                    { data: 'parent_name', name: 'parent_name' },
                    {
                        data: 'active',
                        name: 'active',
                        className: 'text-center',
                        render: function (data) {
                            if (data) {
                                return '<span class="badge badge-sm bg-gradient-success">Active</span>';
                            }
                            return '<span class="badge badge-sm bg-gradient-secondary">Inactive</span>';
                        }
                    },
                    {
                        data: 'products_count',
                        name: 'products_count',
                        className: 'text-center',
                        render: function (data) {
                            return '<span class="badge badge-sm bg-primary">' + data + '</span>';
                        }
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'text-end'
                    }
                ],
                order: [[1, 'asc']]
            });

            // Function to generate slug from category name
            function generateSlug(name) {
                const accents = {
                    'á': 'a', 'à': 'a', 'â': 'a', 'ä': 'a', 'ã': 'a', 'å': 'a',
                    'é': 'e', 'è': 'e', 'ê': 'e', 'ë': 'e',
                    'í': 'i', 'ì': 'i', 'î': 'i', 'ï': 'i',
                    'ó': 'o', 'ò': 'o', 'ô': 'o', 'ö': 'o', 'õ': 'o',
                    'ú': 'u', 'ù': 'u', 'û': 'u', 'ü': 'u',
                    'ç': 'c', 'ñ': 'n', 'ß': 'ss',
                    'æ': 'ae', 'œ': 'oe'
                };
                let slug = name.toLowerCase();
                slug = slug.replace(/[áàâäãåéèêëíìîïóòôöõúùûüçñßæœ]/g, char => accents[char] || char);
                slug = slug.replace(/[^a-z0-9\s-]/g, '')
                    .trim()
                    .replace(/\s+/g, '-')
                    .replace(/-+/g, '-');
                return slug;
            }

            // Auto-generate slug when category name is typed
            $('#name').on('input', function () {
                const name = $(this).val();
                $('#slug').val(generateSlug(name));
            });

            // Reset form when category modal is closed
            $('#categoryModal').on('hidden.bs.modal', function () {
                resetForm();
            });

            // Open modal to add new category
            $('#btn-add-category').click(function () {
                resetForm();
                $('#categoryModalLabel').text('Add New Category');
                $('#method').val('POST');
                $('#categoryModal').modal('show');
            });

            // Handle edit button click
            $(document).on('click', '.btn-edit', function () {
                resetForm();
                var categoryId = $(this).data('id');
                $('#categoryModalLabel').text('Edit Category');
                $('#method').val('PUT');
                $('#category_id').val(categoryId);

                $.ajax({
                    url: "{{ route('admin.categories.edit', ['id' => ':id']) }}".replace(':id', categoryId),
                    method: 'GET',
                    success: function (response) {
                        if (response.success) {
                            var category = response.category;
                            $('#name').val(category.name);
                            $('#slug').val(category.slug);
                            $('#code').val(category.code);
                            $('#parent_id').val(category.parent_id);
                            $('#website').val(category.website);
                            $('#description').val(category.description);
                            $('#meta_title').val(category.meta_title);
                            $('#meta_description').val(category.meta_description);
                            $('#meta_keywords').val(category.meta_keywords);
                            $('#active').prop('checked', category.active);

                            if (category.logo_url) {
                                $('#logo-preview').attr('src', category.logo_url);
                                $('#logo-preview-container').removeClass('d-none');
                            }

                            $('#categoryModal').modal('show');
                        } else {
                            showToast('error', response.error);
                        }
                    },
                    error: function (xhr) {
                        showErrorToast(xhr);
                    }
                });
            });

            // Switch from view modal to edit modal
            $('#btn-edit-view').click(function () {
                var categoryId = $('#view-category-modal-id').val();
                $('#viewCategoryModal').modal('hide');
                $('.btn-edit[data-id="' + categoryId + '"]').click();
            });

            // Handle view button click
            $(document).on('click', '.btn-view', function () {
                var categoryId = $(this).data('id');

                $.ajax({
                    url: "{{ route('admin.categories.show', ['id' => ':id']) }}".replace(':id', categoryId),
                    method: 'GET',
                    success: function (response) {
                        if (response.success) {
                            var category = response.category;
                            $('#view-category-modal-id').val(category.id);
                            $('#view-name').text(category.name);
                            $('#view-code').text(category.code ? 'Code: ' + category.code : '');
                            $('#view-slug').text(category.slug ? 'Slug: ' + category.slug : '');
                            $('#view-parent').text(category.parent ? 'Parent: ' + category.parent.name : '');

                            if (category.website) {
                                $('#view-website').html('<a href="' + category.website + '" target="_blank" class="text-info">' + category.website + '</a>');
                            } else {
                                $('#view-website').text('Not specified');
                            }

                            if (category.description) {
                                $('#view-description').text(category.description);
                            } else {
                                $('#view-description').html('<em class="text-muted">No description provided</em>');
                            }

                            if (category.meta_title) {
                                $('#view-meta_title').text(category.meta_title);
                            } else {
                                $('#view-meta_title').html('<em class="text-muted">Not specified</em>');
                            }

                            if (category.meta_description) {
                                $('#view-meta_description').text(category.meta_description);
                            } else {
                                $('#view-meta_description').html('<em class="text-muted">Not specified</em>');
                            }

                            if (category.meta_keywords) {
                                $('#view-meta_keywords').text(category.meta_keywords);
                            } else {
                                $('#view-meta_keywords').html('<em class="text-muted">Not specified</em>');
                            }

                            $('#view-status').removeClass('bg-gradient-success bg-gradient-secondary')
                                .addClass(category.active ? 'bg-gradient-success' : 'bg-gradient-secondary')
                                .text(category.active ? 'Active' : 'Inactive');

                            if (category.logo_url) {
                                $('#view-logo').attr('src', category.logo_url);
                                $('#view-logo-container').removeClass('d-none');
                                $('#no-logo-container').addClass('d-none');
                            } else {
                                $('#view-logo-container').addClass('d-none');
                                $('#no-logo-container').removeClass('d-none');
                            }

                            var productsHtml = '';
                            if (category.products && category.products.length > 0) {
                                category.products.forEach(function (product) {
                                    productsHtml += '<div class="col-md-4 col-sm-6 mb-3">';
                                    productsHtml += '<div class="card product-card shadow-sm">';
                                    productsHtml += '<div class="card-body p-3">';
                                    productsHtml += '<h6 class="card-title mb-1">' + product.name + '</h6>';
                                    productsHtml += '<p class="card-text text-xs text-muted mb-0">' + (product.code || 'No code') + '</p>';
                                    productsHtml += '</div>';
                                    productsHtml += '</div>';
                                    productsHtml += '</div>';
                                });

                                $('#products-list').html(productsHtml);
                                $('#no-products').addClass('d-none');
                                $('#products-list').removeClass('d-none');
                            } else {
                                $('#products-list').addClass('d-none');
                                $('#no-products').removeClass('d-none');
                            }

                            $('#viewCategoryModal').modal('show');
                        } else {
                            showToast('error', response.error);
                        }
                    },
                    error: function (xhr) {
                        showErrorToast(xhr);
                    }
                });
            });

            // Handle delete button click (open confirmation modal)
            $(document).on('click', '.btn-delete:not(.disabled)', function () {
                var categoryId = $(this).data('id');
                var categoryName = $(this).closest('tr').find('td:nth-child(2)').text();

                $('#delete-category-id').val(categoryId);
                $('#delete-category-name').text(categoryName);
                $('#deleteCategoryModal').modal('show');
            });

            // Confirm delete action
            $('#confirm-delete').click(function () {
                var categoryId = $('#delete-category-id').val();

                $.ajax({
                    url: "{{ route('admin.categories.destroy', ['id' => ':id']) }}".replace(':id', categoryId),
                    method: 'DELETE',
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function (response) {
                        if (response.success) {
                            $('#deleteCategoryModal').modal('hide');
                            table.ajax.reload();
                            showToast('success', response.message);
                        } else {
                            showToast('error', response.error);
                        }
                    },
                    error: function (xhr) {
                        showErrorToast(xhr);
                    }
                });
            });

            // Handle logo file selection
            $('#logo').change(function () {
                if (this.files && this.files[0]) {
                    var reader = new FileReader();

                    reader.onload = function (e) {
                        $('#logo-preview').attr('src', e.target.result);
                        $('#logo-preview-container').removeClass('d-none');
                    }

                    reader.readAsDataURL(this.files[0]);
                }
            });

            // Remove logo preview
            $('#remove-logo').click(function () {
                $('#logo').val('');
                $('#logo-preview-container').addClass('d-none');
                $('#logo-preview').attr('src', '#');
            });

            // Handle category form submission (create/update)
            $('#categoryForm').submit(function (e) {
                e.preventDefault();

                clearFormErrors();

                var formData = new FormData(this);
                formData.set('active', $('#active').is(':checked') ? '1' : '0');

                var method = $('#method').val();
                var url = "{{ route('admin.categories.store') }}";

                if (method === 'PUT') {
                    var categoryId = $('#category_id').val();
                    url = "{{ route('admin.categories.update', ['id' => ':id']) }}".replace(':id', categoryId);
                    formData.append('_method', 'PUT'); // Add _method for Laravel to recognize PUT
                }

                $.ajax({
                    url: url,
                    method: 'POST', // Always use POST; _method handles PUT
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        if (response.success) {
                            $('#categoryModal').modal('hide');
                            table.ajax.reload();
                            showToast('success', response.message);
                        } else {
                            showToast('error', response.error);
                        }
                    },
                    error: function (xhr) {
                        if (xhr.status === 422) {
                            var errors = xhr.responseJSON.errors;
                            displayFormErrors(errors);
                        } else {
                            showErrorToast(xhr);
                        }
                    }
                });
            });

            // Reset form fields
            function resetForm() {
                $('#categoryForm')[0].reset();
                $('#category_id').val('');
                $('#slug').val('');
                $('#logo-preview-container').addClass('d-none');
                $('#logo-preview').attr('src', '#');
                $('#active').prop('checked', true);
                clearFormErrors();
            }

            // Clear form validation errors
            function clearFormErrors() {
                $('.is-invalid').removeClass('is-invalid');
                $('.invalid-feedback').text('');
            }

            // Display form validation errors
            function displayFormErrors(errors) {
                $.each(errors, function (field, messages) {
                    var input = $('#' + field);
                    var feedback = $('#' + field + '-error');

                    input.addClass('is-invalid');
                    feedback.text(messages[0]);
                });
            }

            // Show toast notification
            function showToast(type, message) {
                var bgClass = 'bg-' + (type === 'success' ? 'success' : 'danger');
                var html = `
                <div class="position-fixed top-1 end-1 z-index-2">
                    <div class="toast fade p-2 mt-2 ${bgClass}" role="alert" aria-live="assertive" aria-atomic="true">
                        <div class="toast-body text-white">
                            ${message}
                        </div>
                    </div>
                </div>
            `;

                $('body').append(html);
                $('.toast').toast({
                    delay: 3000,
                    animation: true
                }).toast('show');

                setTimeout(function () {
                    $('.toast').remove();
                }, 3500);
            }

            // Show error toast for AJAX errors
            function showErrorToast(xhr) {
                var message = 'An error occurred. Please try again.';

                if (xhr.responseJSON && xhr.responseJSON.error) {
                    message = xhr.responseJSON.error;
                } else if (xhr.statusText) {
                    message = 'Error: ' + xhr.statusText;
                }

                showToast('error', message);
            }
        });
    </script>
@endpush