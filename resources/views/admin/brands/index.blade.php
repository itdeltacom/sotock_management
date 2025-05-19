@extends('admin.layouts.master')

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                        <h6>Product Brands</h6>
                        @can('create_brands')
                            <button class="btn btn-primary btn-sm ms-auto" id="btn-add-brand">
                                <i class="fas fa-plus me-1"></i> Add Brand
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
                                                        <p class="text-sm mb-0 text-uppercase font-weight-bold">Total Brands</p>
                                                        <h5 class="font-weight-bolder text-white mt-2">
                                                            {{ $totalBrands ?? 0 }}
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
                                                        <p class="text-sm mb-0 text-uppercase font-weight-bold">Active Brands
                                                        </p>
                                                        <h5 class="font-weight-bolder text-white mt-2">
                                                            {{ $activeBrands ?? 0 }}
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
                                <table class="table align-items-center justify-content-center mb-0" id="brands-table">
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
                                                Website</th>
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

        <!-- Add/Edit Brand Modal -->
        <div class="modal fade" id="brandModal" tabindex="-1" role="dialog" aria-labelledby="brandModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="brandModalLabel">Add New Brand</h5>
                        <button type="button" class="btn-close text-dark" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="brandForm" enctype="multipart/form-data">
                        <div class="modal-body">
                            @csrf
                            <input type="hidden" name="_method" id="method" value="POST">
                            <input type="hidden" name="id" id="brand_id">

                            <div class="form-group">
                                <label for="name" class="form-control-label">Brand Name <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" required>
                                <div class="invalid-feedback" id="name-error"></div>
                            </div>

                            <div class="form-group mt-3">
                                <label for="code" class="form-control-label">Code</label>
                                <input type="text" class="form-control" id="code" name="code">
                                <small class="form-text text-muted">Optional unique code for internal reference</small>
                                <div class="invalid-feedback" id="code-error"></div>
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
                            <button type="submit" class="btn btn-primary" id="save-brand">Save Brand</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- View Brand Modal -->
        <div class="modal fade" id="viewBrandModal" tabindex="-1" role="dialog" aria-labelledby="viewBrandModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="viewBrandModalLabel">Brand Details</h5>
                        <button type="button" class="btn-close text-dark" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-4 text-center mb-4">
                                <div id="view-logo-container" class="mb-3">
                                    <img id="view-logo" src="#" alt="Brand Logo" class="img-fluid rounded shadow-sm"
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

                                <p class="mt-3 mb-1 text-sm">Website:</p>
                                <p id="view-website" class="font-weight-bold"></p>

                                <p class="mt-3 mb-1 text-sm">Description:</p>
                                <p id="view-description" class="mb-0"></p>
                            </div>
                        </div>

                        <div class="row mt-4" id="products-section">
                            <div class="col-12">
                                <h6 class="font-weight-bold">Products in this brand</h6>
                                <div id="products-list" class="row">
                                    <!-- Will be filled with products -->
                                </div>
                                <div id="no-products" class="d-none text-center py-4">
                                    <i class="ni ni-app text-muted mb-2" style="font-size: 2rem;"></i>
                                    <p class="text-muted">No products found for this brand</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        @can('edit_brands')
                            <button type="button" class="btn btn-primary" id="btn-edit-view">Edit Brand</button>
                        @endcan
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div class="modal fade" id="deleteBrandModal" tabindex="-1" role="dialog" aria-labelledby="deleteBrandModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteBrandModalLabel">Confirm Delete</h5>
                        <button type="button" class="btn-close text-dark" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        Are you sure you want to delete this brand: <span id="delete-brand-name"
                            class="font-weight-bold"></span>?
                        <input type="hidden" id="delete-brand-id">
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
        .brand-logo-thumbnail {
            object-fit: contain;
            height: 40px;
            width: 40px;
        }

        /* Fixed height for description in view mode */
        #view-description {
            max-height: 120px;
            overflow-y: auto;
        }

        /* Product cards in view modal */
        .product-card {
            border-radius: 10px;
            transition: all 0.2s ease;
            height: 100%;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
    </style>
@endpush

@push('js')
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function () {
            // Initialize DataTable
            var table = $('#brands-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.brands.data') }}",
                columns: [
                    {
                        data: 'logo_image',
                        name: 'logo_image',
                        orderable: false,
                        searchable: false
                    },
                    { data: 'name', name: 'name' },
                    { data: 'code', name: 'code' },
                    {
                        data: 'website',
                        name: 'website',
                        render: function (data) {
                            if (data) {
                                return '<a href="' + data + '" target="_blank" class="text-info text-sm">' + data + '</a>';
                            }
                            return '<span class="text-muted">Not specified</span>';
                        }
                    },
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
                order: [[1, 'asc']] // Sort by name
            });

            // Reset form when modal is closed
            $('#brandModal').on('hidden.bs.modal', function () {
                resetForm();
            });

            // Show Add Brand modal
            $('#btn-add-brand').click(function () {
                resetForm();
                $('#brandModalLabel').text('Add New Brand');
                $('#method').val('POST');
                $('#brandModal').modal('show');
            });

            // Show Edit Brand modal
            $(document).on('click', '.btn-edit', function () {
                resetForm();
                var brandId = $(this).data('id');
                $('#brandModalLabel').text('Edit Brand');
                $('#method').val('PUT');
                $('#brand_id').val(brandId);

                // Get brand data
                $.ajax({
                    url: "{{ url('admin/brands') }}/" + brandId + "/edit",
                    method: 'GET',
                    success: function (response) {
                        if (response.success) {
                            var brand = response.brand;
                            $('#name').val(brand.name);
                            $('#code').val(brand.code);
                            $('#website').val(brand.website);
                            $('#description').val(brand.description);
                            $('#active').prop('checked', brand.active);

                            // Show logo preview if exists
                            if (brand.logo_url) {
                                $('#logo-preview').attr('src', brand.logo_url);
                                $('#logo-preview-container').removeClass('d-none');
                            }

                            $('#brandModal').modal('show');
                        } else {
                            showToast('error', response.error);
                        }
                    },
                    error: function (xhr) {
                        showErrorToast(xhr);
                    }
                });
            });

            // Edit brand from view modal
            $('#btn-edit-view').click(function () {
                var brandId = $('#view-brand-modal-id').val();
                $('#viewBrandModal').modal('hide');
                $('.btn-edit[data-id="' + brandId + '"]').click();
            });

            // View Brand details
            $(document).on('click', '.btn-view', function () {
                var brandId = $(this).data('id');

                $.ajax({
                    url: "{{ url('admin/brands') }}/" + brandId,
                    method: 'GET',
                    success: function (response) {
                        if (response.success) {
                            var brand = response.brand;
                            $('#view-brand-modal-id').val(brand.id);
                            $('#view-name').text(brand.name);
                            $('#view-code').text(brand.code ? 'Code: ' + brand.code : '');

                            // Website
                            if (brand.website) {
                                $('#view-website').html('<a href="' + brand.website + '" target="_blank" class="text-info">' + brand.website + '</a>');
                            } else {
                                $('#view-website').text('Not specified');
                            }

                            // Description
                            if (brand.description) {
                                $('#view-description').text(brand.description);
                            } else {
                                $('#view-description').html('<em class="text-muted">No description provided</em>');
                            }

                            // Status
                            $('#view-status').removeClass('bg-gradient-success bg-gradient-secondary')
                                .addClass(brand.active ? 'bg-gradient-success' : 'bg-gradient-secondary')
                                .text(brand.active ? 'Active' : 'Inactive');

                            // Logo
                            if (brand.logo_url) {
                                $('#view-logo').attr('src', brand.logo_url);
                                $('#view-logo-container').removeClass('d-none');
                                $('#no-logo-container').addClass('d-none');
                            } else {
                                $('#view-logo-container').addClass('d-none');
                                $('#no-logo-container').removeClass('d-none');
                            }

                            // Products
                            var productsHtml = '';

                            if (brand.products && brand.products.length > 0) {
                                brand.products.forEach(function (product) {
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

                            $('#viewBrandModal').modal('show');
                        } else {
                            showToast('error', response.error);
                        }
                    },
                    error: function (xhr) {
                        showErrorToast(xhr);
                    }
                });
            });

            // Show Delete confirmation
            $(document).on('click', '.btn-delete:not(.disabled)', function () {
                var brandId = $(this).data('id');
                var brandName = $(this).closest('tr').find('td:nth-child(2)').text();

                $('#delete-brand-id').val(brandId);
                $('#delete-brand-name').text(brandName);
                $('#deleteBrandModal').modal('show');
            });

            // Confirm Delete
            $('#confirm-delete').click(function () {
                var brandId = $('#delete-brand-id').val();

                $.ajax({
                    url: "{{ url('admin/brands') }}/" + brandId,
                    method: 'DELETE',
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function (response) {
                        if (response.success) {
                            $('#deleteBrandModal').modal('hide');
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

            // Handle logo file input change
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

            // Remove logo button
            $('#remove-logo').click(function () {
                $('#logo').val('');
                $('#logo-preview-container').addClass('d-none');
                $('#logo-preview').attr('src', '#');
            });

            // Submit form
            $('#brandForm').submit(function (e) {
                e.preventDefault();

                // Clear previous errors
                clearFormErrors();

                var formData = new FormData(this);
                var method = $('#method').val();
                var url = "{{ route('admin.brands.store') }}";

                if (method === 'PUT') {
                    var brandId = $('#brand_id').val();
                    url = "{{ url('admin/brands') }}/" + brandId;
                }

                $.ajax({
                    url: url,
                    method: method === 'PUT' ? 'POST' : 'POST', // Always use POST, but include _method field for PUT
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        if (response.success) {
                            $('#brandModal').modal('hide');
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

            // Helper functions
            function resetForm() {
                $('#brandForm')[0].reset();
                $('#brand_id').val('');
                $('#logo-preview-container').addClass('d-none');
                $('#logo-preview').attr('src', '#');
                clearFormErrors();
            }

            function clearFormErrors() {
                $('.is-invalid').removeClass('is-invalid');
                $('.invalid-feedback').text('');
            }

            function displayFormErrors(errors) {
                $.each(errors, function (field, messages) {
                    var input = $('#' + field);
                    var feedback = $('#' + field + '-error');

                    input.addClass('is-invalid');
                    feedback.text(messages[0]);
                });
            }

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