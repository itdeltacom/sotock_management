@extends('admin.layouts.master')

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                        <h6>Products</h6>
                        @can('create products')
                            <button class="btn btn-primary btn-sm ms-auto" id="btn-add-product">
                                <i class="fas fa-plus me-1"></i> Add Product
                            </button>
                        @endcan
                    </div>
                    <div class="card-body px-0 pt-0 pb-2">
                        <div class="row mx-4 mt-3">
                            <div class="col-md-3 col-sm-6 mb-3">
                                <div class="card text-white bg-gradient-primary">
                                    <div class="card-body p-3">
                                        <div class="row">
                                            <div class="col-8">
                                                <div class="numbers">
                                                    <p class="text-sm mb-0 text-uppercase font-weight-bold">Total Products
                                                    </p>
                                                    <h5 class="font-weight-bolder text-white mt-2">
                                                        {{ $totalProducts ?? 0 }}
                                                    </h5>
                                                </div>
                                            </div>
                                            <div class="col-4 text-end">
                                                <div class="icon icon-shape bg-white shadow text-center rounded-circle">
                                                    <i class="ni ni-box-2 text-primary text-lg opacity-10"
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
                                                    <p class="text-sm mb-0 text-uppercase font-weight-bold">Active Products
                                                    </p>
                                                    <h5 class="font-weight-bolder text-white mt-2">
                                                        {{ $activeProducts ?? 0 }}
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

                        <!-- Filters -->
                        <div class="row mx-4 mt-2">
                            <div class="col-md-12 mb-3">
                                <div class="card">
                                    <div class="card-header p-3">
                                        <h6 class="mb-0">Filters</h6>
                                    </div>
                                    <div class="card-body p-3">
                                        <div class="row">
                                            <div class="col-md-4 mb-2">
                                                <label for="filter-category" class="form-label">Category</label>
                                                <select class="form-control" id="filter-category">
                                                    <option value="">All Categories</option>
                                                    @foreach ($categories as $category)
                                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-4 mb-2">
                                                <label for="filter-brand" class="form-label">Brand</label>
                                                <select class="form-control" id="filter-brand">
                                                    <option value="">All Brands</option>
                                                    @foreach ($brands as $brand)
                                                        <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-4 mb-2">
                                                <label for="filter-status" class="form-label">Status</label>
                                                <select class="form-control" id="filter-status">
                                                    <option value="">All Status</option>
                                                    <option value="1">Active</option>
                                                    <option value="0">Inactive</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row mt-2">
                                            <div class="col-md-12 text-end">
                                                <button class="btn btn-primary btn-sm" id="btn-apply-filters">
                                                    <i class="fas fa-filter me-1"></i> Apply Filters
                                                </button>
                                                <button class="btn btn-secondary btn-sm" id="btn-reset-filters">
                                                    <i class="fas fa-sync-alt me-1"></i> Reset
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive p-3">
                            <table class="table align-items-center justify-content-center mb-0" id="products-table">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Image</th>
                                        <th
                                            class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                            Name</th>
                                        <th
                                            class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                            Code</th>
                                        <th
                                            class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                            Brand</th>
                                        <th
                                            class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                            Categories</th>
                                        <th
                                            class="text-uppercase text-secondary text-xxs font-weight-bolder text-center opacity-7 ps-2">
                                            Status</th>
                                        <th
                                            class="text-uppercase text-secondary text-xxs font-weight-bolder text-center opacity-7 ps-2">
                                            Stock</th>
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
                    </div>
                </div>
            </div>
        </div>

        <!-- Add/Edit Product Modal -->
        <div class="modal fade" id="productModal" tabindex="-1" role="dialog" aria-labelledby="productModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="productModalLabel">Add New Product</h5>
                        <button type="button" class="btn-close text-dark" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <form id="productForm" enctype="multipart/form-data">
                        <div class="modal-body">
                            @csrf
                            <input type="hidden" name="_method" id="method" value="POST">
                            <input type="hidden" name="id" id="product_id">

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="name" class="form-control-label">Product Name <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="name" name="name" required>
                                        <div class="invalid-feedback" id="name-error"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="code" class="form-control-label">Product Code <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="code" name="code" required>
                                        <div class="invalid-feedback" id="code-error"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="barcode" class="form-control-label">Barcode</label>
                                        <input type="text" class="form-control" id="barcode" name="barcode">
                                        <div class="invalid-feedback" id="barcode-error"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="sku" class="form-control-label">SKU</label>
                                        <input type="text" class="form-control" id="sku" name="sku">
                                        <div class="invalid-feedback" id="sku-error"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="unit" class="form-control-label">Unit <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="unit" name="unit" required>
                                        <small class="form-text text-muted">E.g., pcs, kg, liter, etc.</small>
                                        <div class="invalid-feedback" id="unit-error"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="brand_id" class="form-control-label">Brand</label>
                                        <select class="form-control" id="brand_id" name="brand_id">
                                            <option value="">Select Brand</option>
                                            @foreach ($brands as $brand)
                                                <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback" id="brand_id-error"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mt-3">
                                <label for="categories" class="form-control-label">Categories</label>
                                <select class="form-control" id="categories" name="categories[]" multiple>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback" id="categories-error"></div>
                            </div>

                            <div class="form-group mt-3">
                                <label for="description" class="form-control-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                                <div class="invalid-feedback" id="description-error"></div>
                            </div>

                            <div class="form-group mt-3">
                                <label for="image" class="form-control-label">Product Image</label>
                                <input type="file" class="form-control" id="image" name="image" accept="image/*">
                                <small class="form-text text-muted">Maximum file size: 2MB. Recommended dimensions:
                                    600x600px</small>
                                <div class="invalid-feedback" id="image-error"></div>

                                <div id="image-preview-container" class="d-none mt-2">
                                    <img id="image-preview" src="#" alt="Product Preview" class="img-thumbnail"
                                        style="max-height: 200px;">
                                    <button type="button" class="btn btn-sm btn-danger mt-1" id="remove-image">
                                        <i class="fas fa-trash"></i> Remove Image
                                    </button>
                                </div>
                            </div>

                            <div class="form-group mt-3">
                                <label for="attributes" class="form-control-label">Attributes</label>
                                <div id="attributes-container">
                                    <!-- Attributes will be added here dynamically -->
                                    <div class="row mb-2 attribute-row">
                                        <div class="col-md-5">
                                            <input type="text" class="form-control" name="attributes[0][key]"
                                                placeholder="Attribute name">
                                        </div>
                                        <div class="col-md-5">
                                            <input type="text" class="form-control" name="attributes[0][value]"
                                                placeholder="Attribute value">
                                        </div>
                                        <div class="col-md-2">
                                            <button type="button" class="btn btn-sm btn-danger remove-attribute">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-sm btn-info mt-2" id="add-attribute">
                                    <i class="fas fa-plus"></i> Add Attribute
                                </button>
                            </div>

                            <div class="form-check form-switch mt-3">
                                <input class="form-check-input" type="checkbox" id="active" name="active" checked>
                                <label class="form-check-label" for="active">Active</label>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary" id="save-product">Save Product</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- View Product Modal -->
        <div class="modal fade" id="viewProductModal" tabindex="-1" role="dialog" aria-labelledby="viewProductModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="viewProductModalLabel">Product Details</h5>
                        <button type="button" class="btn-close text-dark" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-4 text-center mb-4">
                                <div id="view-image-container" class="mb-3">
                                    <img id="view-image" src="#" alt="Product Image" class="img-fluid rounded shadow-sm"
                                        style="max-width: 200px; max-height: 200px;">
                                </div>
                                <div id="no-image-container" class="d-none mb-3">
                                    <div class="rounded bg-light d-flex align-items-center justify-content-center"
                                        style="width: 200px; height: 200px; margin: 0 auto;">
                                        <i class="ni ni-image text-muted" style="font-size: 3rem;"></i>
                                    </div>
                                </div>
                                <span class="badge badge-pill bg-gradient-success" id="view-status">Active</span>
                            </div>
                            <div class="col-md-8">
                                <h3 id="view-name" class="font-weight-bold mb-0"></h3>
                                <p id="view-code" class="text-sm text-muted"></p>
                                <div class="d-flex">
                                    <p class="text-sm mb-0 me-3"><strong>SKU:</strong> <span id="view-sku"></span></p>
                                    <p class="text-sm mb-0"><strong>Barcode:</strong> <span id="view-barcode"></span></p>
                                </div>
                                <p class="text-sm mb-3"><strong>Unit:</strong> <span id="view-unit"></span></p>

                                <p class="text-sm mb-1"><strong>Brand:</strong> <span id="view-brand"></span></p>
                                <p class="text-sm mb-3"><strong>Categories:</strong> <span id="view-categories"></span></p>

                                <h6 class="font-weight-bold mb-2">Description</h6>
                                <p id="view-description" class="mb-3"></p>

                                <h6 class="font-weight-bold mb-2">Attributes</h6>
                                <div id="view-attributes" class="mb-3">
                                    <!-- Attributes will be filled dynamically -->
                                </div>

                                <h6 class="font-weight-bold mb-2">Stock Information</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <p class="text-sm mb-1"><strong>Total Stock:</strong> <span
                                                id="view-total-stock"></span></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="text-sm mb-1"><strong>Average Cost (CMUP):</strong> <span
                                                id="view-average-cost"></span></p>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-sm btn-info mt-2" id="btn-view-stock-details">
                                    <i class="fas fa-box-open me-1"></i> View Stock Details
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        @can('edit products')
                            <button type="button" class="btn btn-primary" id="btn-edit-view">Edit Product</button>
                        @endcan
                    </div>
                </div>
            </div>
        </div>

        <!-- Stock Details Modal -->
        <div class="modal fade" id="stockDetailsModal" tabindex="-1" role="dialog" aria-labelledby="stockDetailsModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="stockDetailsModalLabel">Stock Details</h5>
                        <button type="button" class="btn-close text-dark" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <h6 id="stock-product-name" class="font-weight-bold mb-3"></h6>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Warehouse</th>
                                    <th>Available</th>
                                    <th>Reserved</th>
                                    <th>Total</th>
                                    <th>CMUP</th>
                                </tr>
                            </thead>
                            <tbody id="stock-details-table-body">
                                <!-- Stock details will be filled dynamically -->
                            </tbody>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div class="modal fade" id="deleteProductModal" tabindex="-1" role="dialog"
            aria-labelledby="deleteProductModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteProductModalLabel">Confirm Delete</h5>
                        <button type="button" class="btn-close text-dark" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        Are you sure you want to delete this product: <span id="delete-product-name"
                            class="font-weight-bold"></span>?
                        <input type="hidden" id="delete-product-id">
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">
    <style>
        .product-thumbnail {
            object-fit: contain;
            height: 40px;
            width: 40px;
        }

        #view-description {
            max-height: 120px;
            overflow-y: auto;
        }

        .attribute-badge {
            display: inline-block;
            padding: 0.35em 0.65em;
            margin-right: 0.5rem;
            margin-bottom: 0.5rem;
            font-size: 0.75em;
            font-weight: 700;
            line-height: 1;
            color: #fff;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            border-radius: 0.25rem;
            background-color: #6c757d;
        }
    </style>
@endpush

@push('js')
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function () {
            console.log("DOM ready");

            // Global variable to track if filters have been applied
            window.filtersApplied = false;

            // Initialize Select2 for categories
            $('#categories').select2({
                theme: 'bootstrap-5',
                placeholder: 'Select Categories',
                allowClear: true
            });

            // Add this: missing hidden field for view product modal ID
            if (!$('#view-product-modal-id').length) {
                $('body').append('<input type="hidden" id="view-product-modal-id">');
            }

            // Initialize DataTable for products with debugging
            var table = $('#products-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('admin.products.data') }}",
                    data: function (d) {
                        // Only apply filters if they've been explicitly set
                        if (window.filtersApplied) {
                            d.category_id = $('#filter-category').val();
                            d.brand_id = $('#filter-brand').val();
                            d.active = $('#filter-status').val();
                        }
                    },
                    dataSrc: function (json) {
                        console.log("DataTable received data:", json);
                        if (json.data && json.data.length > 0) {
                            return json.data;
                        }
                        return [];
                    },
                    error: function (xhr, error, thrown) {
                        console.log("DataTable error:", error);
                        console.log("Error details:", thrown);
                        console.log("Response:", xhr.responseText);
                    }
                },
                columns: [
                    {
                        data: 'image_preview',
                        name: 'image_preview',
                        orderable: false,
                        searchable: false
                    },
                    { data: 'name', name: 'name' },
                    { data: 'code', name: 'code' },
                    { data: 'brand_name', name: 'brand_name' },
                    { data: 'categories_list', name: 'categories_list' },
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
                        data: 'stock_status',
                        name: 'stock_status',
                        className: 'text-center',
                        orderable: false
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'text-end'
                    }
                ],
                order: [[1, 'asc']],
                drawCallback: function () {
                    console.log("Table draw complete");
                    // Check if table is empty
                    if ($('#products-table tbody tr').length === 0 ||
                        $('#products-table tbody tr td').first().hasClass('dataTables_empty')) {
                        console.log("Table appears empty after drawing");
                    }
                }
            });

            // Force refresh table after setup to ensure data loads
            setTimeout(function () {
                console.log("Forcing table reload");
                table.ajax.reload();
            }, 500);

            // Apply filters - set flag and reload table
            $('#btn-apply-filters').click(function () {
                console.log("Applying filters");
                window.filtersApplied = true;
                table.ajax.reload();
            });

            // Reset filters - clear inputs, reset flag, reload table
            $('#btn-reset-filters').click(function () {
                console.log("Resetting filters");
                window.filtersApplied = false;
                $('#filter-category').val('');
                $('#filter-brand').val('');
                $('#filter-status').val('');
                table.ajax.reload();
            });

            // Reset form when product modal is closed
            $('#productModal').on('hidden.bs.modal', function () {
                resetForm();
            });

            // Open modal to add new product
            $('#btn-add-product').click(function () {
                resetForm();
                $('#productModalLabel').text('Add New Product');
                $('#method').val('POST');
                $('#productModal').modal('show');
            });

            // Handle attributes
            let attributeCount = 1;

            $('#add-attribute').click(function () {
                const html = `
                        <div class="row mb-2 attribute-row">
                            <div class="col-md-5">
                                <input type="text" class="form-control" name="attributes[${attributeCount}][key]" placeholder="Attribute name">
                            </div>
                            <div class="col-md-5">
                                <input type="text" class="form-control" name="attributes[${attributeCount}][value]" placeholder="Attribute value">
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-sm btn-danger remove-attribute">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    `;
                $('#attributes-container').append(html);
                attributeCount++;
            });

            $(document).on('click', '.remove-attribute', function () {
                $(this).closest('.attribute-row').remove();
            });

            // Handle edit button click
            $(document).on('click', '.btn-edit', function () {
                resetForm();
                var productId = $(this).data('id');
                $('#productModalLabel').text('Edit Product');
                $('#method').val('PUT');
                $('#product_id').val(productId);

                $.ajax({
                    url: "{{ route('admin.products.edit', ['id' => ':id']) }}".replace(':id', productId),
                    method: 'GET',
                    success: function (response) {
                        if (response.success) {
                            var product = response.product;
                            $('#name').val(product.name);
                            $('#code').val(product.code);
                            $('#barcode').val(product.barcode);
                            $('#sku').val(product.sku);
                            $('#unit').val(product.unit);
                            $('#brand_id').val(product.brand_id);
                            $('#description').val(product.description);
                            $('#active').prop('checked', product.active);

                            // Set categories
                            if (product.category_ids && product.category_ids.length > 0) {
                                $('#categories').val(product.category_ids).trigger('change');
                            }

                            // Set attributes
                            if (product.attributes) {
                                $('#attributes-container').empty();
                                attributeCount = 0;

                                // Fix: Handle both JSON string and object
                                let attributesObj = product.attributes;
                                if (typeof attributesObj === 'string') {
                                    try {
                                        attributesObj = JSON.parse(attributesObj);
                                    } catch (e) {
                                        console.error("Error parsing attributes JSON:", e);
                                        attributesObj = {};
                                    }
                                }

                                if (typeof attributesObj === 'object' && attributesObj !== null) {
                                    $.each(attributesObj, function (key, value) {
                                        const html = `
                                                <div class="row mb-2 attribute-row">
                                                    <div class="col-md-5">
                                                        <input type="text" class="form-control" name="attributes[${attributeCount}][key]" placeholder="Attribute name" value="${key}">
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="text" class="form-control" name="attributes[${attributeCount}][value]" placeholder="Attribute value" value="${value}">
                                                    </div>
                                                    <div class="col-md-2">
                                                        <button type="button" class="btn btn-sm btn-danger remove-attribute">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            `;
                                        $('#attributes-container').append(html);
                                        attributeCount++;
                                    });
                                }

                                // Add empty row if no attributes
                                if (attributeCount === 0) {
                                    $('#add-attribute').click();
                                }
                            }

                            if (product.image_url) {
                                $('#image-preview').attr('src', product.image_url);
                                $('#image-preview-container').removeClass('d-none');
                            }

                            $('#productModal').modal('show');
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
                var productId = $('#view-product-modal-id').val();
                $('#viewProductModal').modal('hide');
                setTimeout(function () {
                    $('.btn-edit[data-id="' + productId + '"]').click();
                }, 500);
            });

            // Handle view button click
            $(document).on('click', '.btn-view', function () {
                var productId = $(this).data('id');

                $.ajax({
                    url: "{{ route('admin.products.show', ['id' => ':id']) }}".replace(':id', productId),
                    method: 'GET',
                    success: function (response) {
                        if (response.success) {
                            var product = response.product;
                            $('#view-product-modal-id').val(product.id);
                            $('#view-name').text(product.name);
                            $('#view-code').text('Code: ' + product.code);
                            $('#view-sku').text(product.sku || 'N/A');
                            $('#view-barcode').text(product.barcode || 'N/A');
                            $('#view-unit').text(product.unit);
                            $('#view-brand').text(product.brand ? product.brand.name : 'N/A');
                            $('#view-categories').text(product.categories_list || 'None');
                            $('#view-total-stock').text(product.total_stock || 0);
                            $('#view-average-cost').text((product.average_cost || 0).toFixed(2));

                            if (product.description) {
                                $('#view-description').text(product.description);
                            } else {
                                $('#view-description').html('<em class="text-muted">No description provided</em>');
                            }

                            // Display attributes
                            let attributesHtml = '';
                            // Fix: Handle both JSON string and object for attributes
                            let attributesObj = product.attributes;
                            if (typeof attributesObj === 'string') {
                                try {
                                    attributesObj = JSON.parse(attributesObj);
                                } catch (e) {
                                    console.error("Error parsing attributes JSON:", e);
                                    attributesObj = {};
                                }
                            }

                            if (typeof attributesObj === 'object' && attributesObj !== null && Object.keys(attributesObj).length > 0) {
                                $.each(attributesObj, function (key, value) {
                                    attributesHtml += `<span class="attribute-badge">${key}: ${value}</span>`;
                                });
                            } else {
                                attributesHtml = '<em class="text-muted">No attributes defined</em>';
                            }
                            $('#view-attributes').html(attributesHtml);

                            $('#view-status').removeClass('bg-gradient-success bg-gradient-secondary')
                                .addClass(product.active ? 'bg-gradient-success' : 'bg-gradient-secondary')
                                .text(product.active ? 'Active' : 'Inactive');

                            if (product.image_url) {
                                $('#view-image').attr('src', product.image_url);
                                $('#view-image-container').removeClass('d-none');
                                $('#no-image-container').addClass('d-none');
                            } else {
                                $('#view-image-container').addClass('d-none');
                                $('#no-image-container').removeClass('d-none');
                            }

                            $('#viewProductModal').modal('show');
                        } else {
                            showToast('error', response.error);
                        }
                    },
                    error: function (xhr) {
                        showErrorToast(xhr);
                    }
                });
            });

            // View stock details
            $('#btn-view-stock-details').click(function () {
                var productId = $('#view-product-modal-id').val();
                var productName = $('#view-name').text();

                $.ajax({
                    url: "{{ route('admin.products.stock-info', ['id' => ':id']) }}".replace(':id', productId),
                    method: 'GET',
                    success: function (response) {
                        if (response.success) {
                            var stockInfo = response.stock_info;
                            $('#stock-product-name').text(productName);

                            // Generate table rows
                            let tableHtml = '';
                            if (stockInfo.warehouses && stockInfo.warehouses.length > 0) {
                                $.each(stockInfo.warehouses, function (index, warehouse) {
                                    tableHtml += `
                                            <tr>
                                                <td>${warehouse.warehouse_name}</td>
                                                <td>${warehouse.available}</td>
                                                <td>${warehouse.reserved}</td>
                                                <td>${warehouse.total}</td>
                                                <td>${warehouse.cmup.toFixed(2)}</td>
                                            </tr>
                                        `;
                                });
                            } else {
                                tableHtml = `
                                        <tr>
                                            <td colspan="5" class="text-center">No stock information available</td>
                                        </tr>
                                    `;
                            }

                            $('#stock-details-table-body').html(tableHtml);
                            $('#stockDetailsModal').modal('show');
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
            $(document).on('click', '.btn-delete', function () {
                var productId = $(this).data('id');
                var productName = $(this).closest('tr').find('td:nth-child(2)').text();

                $('#delete-product-id').val(productId);
                $('#delete-product-name').text(productName);
                $('#deleteProductModal').modal('show');
            });

            // Confirm delete action
            $('#confirm-delete').click(function () {
                var productId = $('#delete-product-id').val();

                $.ajax({
                    url: "{{ route('admin.products.destroy', ['id' => ':id']) }}".replace(':id', productId),
                    method: 'DELETE',
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function (response) {
                        if (response.success) {
                            $('#deleteProductModal').modal('hide');
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

            // Handle image file selection
            $('#image').change(function () {
                if (this.files && this.files[0]) {
                    var reader = new FileReader();

                    reader.onload = function (e) {
                        $('#image-preview').attr('src', e.target.result);
                        $('#image-preview-container').removeClass('d-none');
                    }

                    reader.readAsDataURL(this.files[0]);
                }
            });

            // Remove image preview
            $('#remove-image').click(function () {
                $('#image').val('');
                $('#image-preview-container').addClass('d-none');
                $('#image-preview').attr('src', '#');
            });

            // Handle product form submission (create/update)
            $('#productForm').submit(function (e) {
                e.preventDefault();

                clearFormErrors();

                var formData = new FormData(this);
                formData.set('active', $('#active').is(':checked') ? '1' : '0');

                var method = $('#method').val();
                var url = "{{ route('admin.products.store') }}";

                if (method === 'PUT') {
                    var productId = $('#product_id').val();
                    url = "{{ route('admin.products.update', ['id' => ':id']) }}".replace(':id', productId);
                    formData.append('_method', 'PUT');
                }

                $.ajax({
                    url: url,
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        if (response.success) {
                            $('#productModal').modal('hide');
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
                $('#productForm')[0].reset();
                $('#product_id').val('');
                $('#categories').val(null).trigger('change');
                $('#image-preview-container').addClass('d-none');
                $('#image-preview').attr('src', '#');
                $('#active').prop('checked', true);

                // Reset attributes
                $('#attributes-container').html(`
                        <div class="row mb-2 attribute-row">
                            <div class="col-md-5">
                                <input type="text" class="form-control" name="attributes[0][key]" placeholder="Attribute name">
                            </div>
                            <div class="col-md-5">
                                <input type="text" class="form-control" name="attributes[0][value]" placeholder="Attribute value">
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-sm btn-danger remove-attribute">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    `);
                attributeCount = 1;

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