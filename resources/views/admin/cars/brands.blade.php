@extends('admin.layouts.master')

@section('title', 'Brands Management')

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header pb-0 p-3">
                        <div class="row">
                            <div class="col-6 d-flex align-items-center">
                                <h6 class="mb-0">Car Brands Management</h6>
                            </div>
                            <div class="col-6 text-end">
                                @can('create brands')
                                    <button type="button" class="btn bg-gradient-primary" id="createBrandBtn">
                                        <i class="fas fa-plus"></i> Add New Brand
                                    </button>
                                @endcan
                            </div>
                        </div>
                    </div>
                    <div class="card-body px-0 pt-0 pb-2">
                        <div class="table-responsive p-0">
                            <table class="table align-items-center mb-0" id="brands-table" width="100%">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">ID</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Logo</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Name</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Slug</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Cars</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                                        @if(auth()->guard('admin')->user()->can('edit brands') || auth()->guard('admin')->user()->can('delete brands'))
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

    <!-- Create/Edit Brand Modal -->
    <div class="modal fade" id="brandModal" tabindex="-1" aria-labelledby="brandModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="brandModalLabel">Add New Brand</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="brandForm" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="brand_id" id="brand_id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="name" class="form-control-label">Brand Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                            <div class="invalid-feedback" id="name-error"></div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-control-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                            <div class="invalid-feedback" id="description-error"></div>
                        </div>

                        <div class="mb-3">
                            <label for="logo" class="form-control-label">Logo</label>
                            <input type="file" class="form-control" id="logo" name="logo" accept="image/*">
                            <div class="invalid-feedback" id="logo-error"></div>
                            <div id="logo-preview" class="mt-2 d-none">
                                <img src="" alt="Brand Logo" class="img-fluid shadow-sm rounded" style="max-height: 100px;">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="is_active" class="form-control-label">Status</label>
                            <select class="form-control" id="is_active" name="is_active">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                            <div class="invalid-feedback" id="is_active-error"></div>
                        </div>

                        <hr>
                        <h6 class="text-uppercase text-sm">SEO Settings (Optional)</h6>

                        <div class="mb-3">
                            <label for="meta_title" class="form-control-label">Meta Title</label>
                            <input type="text" class="form-control" id="meta_title" name="meta_title">
                            <div class="invalid-feedback" id="meta_title-error"></div>
                        </div>

                        <div class="mb-3">
                            <label for="meta_description" class="form-control-label">Meta Description</label>
                            <textarea class="form-control" id="meta_description" name="meta_description"
                                rows="2"></textarea>
                            <div class="invalid-feedback" id="meta_description-error"></div>
                        </div>

                        <div class="mb-3">
                            <label for="meta_keywords" class="form-control-label">Meta Keywords</label>
                            <input type="text" class="form-control" id="meta_keywords" name="meta_keywords">
                            <div class="invalid-feedback" id="meta_keywords-error"></div>
                            <small class="text-muted text-xs">Separate keywords with commas</small>
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
                        <p>Are you sure you want to delete this brand? This action cannot be undone.</p>
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
        /* Argon-style card and shadow effects */
        .card {
            box-shadow: 0 20px 27px 0 rgba(0, 0, 0, 0.05);
            border-radius: 0.75rem;
        }
        .modal-dialog-scrollable .modal-content{
            overflow: scroll !important;
        }

        .card .card-header {
            padding: 1.5rem;
        }

        /* Make inputs look like Argon's */
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

        .bg-gradient-danger {
            background: linear-gradient(310deg, #f5365c 0%, #f56036 100%);
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

        /* Modal styling */
        .modal-content {
            border: 0;
            border-radius: 0.75rem;
            box-shadow: 0 10px 35px -5px rgba(0, 0, 0, 0.15);
        }

        .modal-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .modal-footer {
            padding: 1.25rem 1.5rem;
            border-top: 1px solid rgba(0, 0, 0, 0.05);
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
            dataUrl: "{{ route('admin.brands.data') }}",
            storeUrl: "{{ route('admin.brands.store') }}",
            editUrl: "{{ route('admin.brands.edit', ':id') }}",
            updateUrl: "{{ route('admin.brands.update', ':id') }}",
            deleteUrl: "{{ route('admin.brands.destroy', ':id') }}"
        };

        // Pass permissions data to JavaScript
        const canEditBrands = @json(auth()->guard('admin')->user()->can('edit brands'));
        const canDeleteBrands = @json(auth()->guard('admin')->user()->can('delete brands'));
    </script>

    <!-- Include the JS script for brands management -->
    {{--
    <script src="{{ asset('admin/js/brands-management.js') }}"></script> --}}

    <script>
        'use strict';

        document.addEventListener('DOMContentLoaded', function () {
            // Cache DOM elements
            const brandsTable = document.getElementById('brands-table');
            const brandForm = document.getElementById('brandForm');
            const brandModal = document.getElementById('brandModal');
            const createBrandBtn = document.getElementById('createBrandBtn');
            const saveBtn = document.getElementById('saveBtn');
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            // Initialize DataTable
            let table = new DataTable('#brands-table', {
                processing: true,
                serverSide: true,
                ajax: {
                    url: routes.dataUrl,
                    type: 'GET'
                },
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'logo', name: 'logo', orderable: false, searchable: false },
                    { data: 'name', name: 'name' },
                    { data: 'slug', name: 'slug' },
                    { data: 'cars_count', name: 'cars_count', searchable: false },
                    { data: 'status', name: 'is_active' },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ],
                order: [[0, 'desc']],
                pageLength: 10,
                responsive: true,
        language: {
            paginate: {
                next: '<i class="fas fa-angle-right"></i>',
                previous: '<i class="fas fa-angle-left"></i>'
            }
        }
            });

            // Set up event listeners
            if (createBrandBtn) {
                createBrandBtn.addEventListener('click', function () {
                    resetForm();
                    document.getElementById('brandModalLabel').textContent = 'Add New Brand';

                    // Hide logo preview
                    const logoPreview = document.getElementById('logo-preview');
                    if (logoPreview) logoPreview.classList.add('d-none');

                    $(brandModal).modal('show');
                });
            }

            if (brandForm) {
                brandForm.addEventListener('submit', handleFormSubmit);
            }

            // File input preview for logo
            const logoInput = document.getElementById('logo');
            if (logoInput) {
                logoInput.addEventListener('change', function () {
                    const logoPreview = document.getElementById('logo-preview');
                    const previewImg = logoPreview.querySelector('img');

                    if (this.files && this.files[0]) {
                        const reader = new FileReader();

                        reader.onload = function (e) {
                            previewImg.src = e.target.result;
                            logoPreview.classList.remove('d-none');
                        }

                        reader.readAsDataURL(this.files[0]);
                    } else {
                        logoPreview.classList.add('d-none');
                    }
                });
            }

            // Handle action buttons with event delegation
            document.addEventListener('click', function (e) {
                // Edit button
                if (e.target.closest('.btn-edit')) {
                    // Check permission
                    if (typeof canEditBrands !== 'undefined' && !canEditBrands) {
                        Swal.fire('Permission Denied', 'You do not have permission to edit brands.', 'warning');
                        return;
                    }

                    const button = e.target.closest('.btn-edit');
                    const brandId = button.getAttribute('data-id');
                    if (brandId) handleEditBrand(brandId);
                }

                // Delete button
                if (e.target.closest('.btn-delete')) {
                    // Check permission
                    if (typeof canDeleteBrands !== 'undefined' && !canDeleteBrands) {
                        Swal.fire('Permission Denied', 'You do not have permission to delete brands.', 'warning');
                        return;
                    }

                    const button = e.target.closest('.btn-delete');
                    const brandId = button.getAttribute('data-id');
                    const brandName = button.getAttribute('data-name') || 'this brand';
                    if (brandId) handleDeleteBrand(brandId, brandName);
                }
            });

            /**
             * Handle form submission
             */
            function handleFormSubmit(e) {
                e.preventDefault();

                // Reset validation UI
                clearValidationErrors();

                // Get form data
                const formData = new FormData(e.target);

                // Get brand ID and determine if this is an edit operation
                const brandId = document.getElementById('brand_id').value;
                const isEdit = brandId && brandId !== '';

                // For PUT requests, Laravel doesn't process FormData the same way as POST
                if (isEdit) {
                    formData.append('_method', 'PUT');
                }

                // Set up request URL
                const url = isEdit ? routes.updateUrl.replace(':id', brandId) : routes.storeUrl;

                // Show loading state
                if (saveBtn) {
                    saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...';
                    saveBtn.disabled = true;
                }

                // Send request
                fetch(url, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                    .then(response => {
                        if (!response.ok) {
                            return response.json().then(data => {
                                throw { status: response.status, data: data };
                            });
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            // Hide modal
                            $(brandModal).modal('hide');

                            // Reload table
                            table.ajax.reload();

                            // Show success message using Toast
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
                            
                            Toast.fire({
                                icon: 'success',
                                title: data.message || 'Brand saved successfully'
                            });
                        } else {
                            throw new Error(data.message || 'An error occurred');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);

                        // Handle validation errors
                        if (error.status === 422 && error.data && error.data.errors) {
                            displayValidationErrors(error.data.errors);
                        } else {
                            // Show error notification
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: error.data?.message || 'An error occurred',
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 5000
                            });
                        }
                    })
                    .finally(() => {
                        // Reset button state
                        if (saveBtn) {
                            saveBtn.innerHTML = 'Save';
                            saveBtn.disabled = false;
                        }
                    });
            }

            /**
             * Handle edit brand
             */
            function handleEditBrand(brandId) {
                resetForm();

                // Set ID and title
                document.getElementById('brand_id').value = brandId;
                document.getElementById('brandModalLabel').textContent = 'Edit Brand';

                // Show modal with loading overlay
                $(brandModal).modal('show');

                const modalBody = document.querySelector('#brandModal .modal-body');
                if (modalBody) {
                    const loadingDiv = document.createElement('div');
                    loadingDiv.id = 'loading-overlay';
                    loadingDiv.className = 'position-absolute bg-white d-flex justify-content-center align-items-center';
                    loadingDiv.style.cssText = 'left: 0; top: 0; right: 0; bottom: 0; z-index: 10;';
                    loadingDiv.innerHTML = '<div class="spinner-border text-primary"></div>';

                    // Add loading overlay
                    modalBody.style.position = 'relative';
                    modalBody.appendChild(loadingDiv);
                }

                // Fetch brand data
                fetch(routes.editUrl.replace(':id', brandId), {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                    .then(response => response.json())
                    .then(data => {
                        // Remove loading overlay
                        const overlay = document.getElementById('loading-overlay');
                        if (overlay) overlay.remove();

                        if (data.success && data.brand) {
                            const brand = data.brand;

                            // Set form values
                            document.getElementById('name').value = brand.name || '';
                            document.getElementById('description').value = brand.description || '';
                            document.getElementById('meta_title').value = brand.meta_title || '';
                            document.getElementById('meta_description').value = brand.meta_description || '';
                            document.getElementById('meta_keywords').value = brand.meta_keywords || '';
                            document.getElementById('is_active').value = brand.is_active ? '1' : '0';

                            // Show logo preview if exists
                            const logoPreview = document.getElementById('logo-preview');
                            const previewImg = logoPreview?.querySelector('img');

                            if (logoPreview && previewImg && brand.logo) {
                                previewImg.src = '/storage/' + brand.logo;
                                logoPreview.classList.remove('d-none');
                            } else if (logoPreview) {
                                logoPreview.classList.add('d-none');
                            }
                        } else {
                            $(brandModal).modal('hide');
                            Swal.fire('Error', 'Failed to load brand data', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        $(brandModal).modal('hide');
                        Swal.fire('Error', 'Failed to load brand data', 'error');
                    });
            }

            /**
             * Handle delete brand
             */
            function handleDeleteBrand(brandId, brandName) {
                Swal.fire({
                    title: 'Confirm Delete',
                    html: `Are you sure you want to delete <strong>${brandName}</strong>?<br><br>
                           <span class="text-danger font-weight-bold">This action cannot be undone!</span>`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Delete',
                    focusCancel: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Deleting...',
                            allowOutsideClick: false,
                            didOpen: () => { Swal.showLoading(); }
                        });

                        fetch(routes.deleteUrl.replace(':id', brandId), {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            }
                        })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    table.ajax.reload();
                                    Swal.fire('Deleted!', data.message || 'Brand deleted successfully', 'success');
                                } else {
                                    throw new Error(data.message || 'Failed to delete brand');
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                Swal.fire('Error', error.message || 'Failed to delete brand', 'error');
                            });
                    }
                });
            }

            /**
             * Clear validation errors
             */
            function clearValidationErrors() {
                document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                document.querySelectorAll('.invalid-feedback').forEach(el => {
                    el.textContent = '';
                    el.style.display = 'none';
                });
            }

            /**
             * Display validation errors
             */
            function displayValidationErrors(errors) {
                clearValidationErrors();

                Object.keys(errors).forEach(field => {
                    const input = document.getElementById(field);
                    const errorEl = document.getElementById(`${field}-error`);

                    if (input) input.classList.add('is-invalid');

                    if (errorEl && errors[field][0]) {
                        errorEl.textContent = errors[field][0];
                        errorEl.style.display = 'block';
                    }
                });
            }

            /**
             * Reset form
             */
            function resetForm() {
                if (brandForm) brandForm.reset();
                document.getElementById('brand_id').value = '';
                clearValidationErrors();
            }
        });
    </script>
@endpush