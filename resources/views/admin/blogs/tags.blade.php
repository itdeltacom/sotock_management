@extends('admin.layouts.master')

@section('title', 'Blog Tags Management')

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header pb-0 p-3">
                        <div class="row">
                            <div class="col-6 d-flex align-items-center">
                                <h6 class="mb-0">Blog Tags Management</h6>
                            </div>
                            <div class="col-6 text-end">
                                @can('create blog tags')
                                    <button type="button" class="btn bg-gradient-primary" id="createTagBtn">
                                        <i class="fas fa-plus"></i> Add New Tag
                                    </button>
                                @endcan
                            </div>
                        </div>
                    </div>
                    
                    <!-- Filters -->
                    <div class="card-header pb-0 p-3">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <select class="form-select form-select-sm" id="statusFilter">
                                    <option value="">All Status</option>
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <input type="text" class="form-control form-control-sm" id="searchFilter" placeholder="Search tags...">
                            </div>
                            <div class="col-md-4">
                                <button type="button" class="btn btn-sm bg-gradient-info w-100" id="resetFilters">
                                    <i class="fas fa-undo"></i> Reset Filters
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-body px-0 pt-0 pb-2">
                        <div class="table-responsive p-0">
                            <table class="table align-items-center mb-0" id="tags-table" width="100%">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">ID</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Name</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Slug</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Description</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Posts</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                                        @if(auth()->guard('admin')->user()->can('edit blog tags') || auth()->guard('admin')->user()->can('delete blog tags'))
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

    <!-- Create/Edit Tag Modal -->
    <div class="modal fade" id="tagModal" tabindex="-1" aria-labelledby="tagModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="tagModalLabel">Add New Tag</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="tagForm">
                    @csrf
                    <input type="hidden" name="tag_id" id="tag_id">
                    <div class="modal-body">
                        <ul class="nav nav-pills mb-3" id="tagTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="basic-tab" data-bs-toggle="tab" data-bs-target="#basic"
                                    type="button" role="tab" aria-controls="basic" aria-selected="true">Basic Info</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="seo-tab" data-bs-toggle="tab" data-bs-target="#seo"
                                    type="button" role="tab" aria-controls="seo" aria-selected="false">SEO</button>
                            </li>
                        </ul>

                        <div class="tab-content p-3 border border-top-0 rounded-bottom" id="tagTabsContent">
                            <!-- Basic Info Tab -->
                            <div class="tab-pane fade show active" id="basic" role="tabpanel" aria-labelledby="basic-tab">
                                <div class="mb-3">
                                    <label for="name" class="form-control-label">Tag Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                    <div class="invalid-feedback" id="name-error"></div>
                                    <small class="form-text text-muted">The name is how the tag appears on your
                                        site.</small>
                                </div>

                                <div class="mb-3">
                                    <label for="description" class="form-control-label">Description</label>
                                    <textarea class="form-control ckeditor" id="description" name="description"
                                        rows="4"></textarea>
                                    <div class="invalid-feedback" id="description-error"></div>
                                    <small class="form-text text-muted">The description is not prominent by default;
                                        however, some themes may show it.</small>
                                </div>

                                <div class="mb-3">
                                    <label for="is_active" class="form-control-label">Status</label>
                                    <select class="form-control" id="is_active" name="is_active">
                                        <option value="1">Active</option>
                                        <option value="0">Inactive</option>
                                    </select>
                                    <div class="invalid-feedback" id="is_active-error"></div>
                                </div>
                            </div>

                            <!-- SEO Tab -->
                            <div class="tab-pane fade" id="seo" role="tabpanel" aria-labelledby="seo-tab">
                                <div class="mb-3">
                                    <label for="meta_title" class="form-control-label">Meta Title</label>
                                    <input type="text" class="form-control" id="meta_title" name="meta_title">
                                    <div class="invalid-feedback" id="meta_title-error"></div>
                                </div>

                                <div class="mb-3">
                                    <label for="meta_description" class="form-control-label">Meta Description</label>
                                    <textarea class="form-control" id="meta_description" name="meta_description"
                                        rows="3"></textarea>
                                    <div class="invalid-feedback" id="meta_description-error"></div>
                                </div>

                                <div class="mb-3">
                                    <label for="meta_keywords" class="form-control-label">Meta Keywords</label>
                                    <input type="text" class="form-control" id="meta_keywords" name="meta_keywords">
                                    <div class="invalid-feedback" id="meta_keywords-error"></div>
                                    <small class="form-text text-muted">Separate keywords with commas</small>
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
                        <p>Are you sure you want to delete this tag?</p>
                        <p class="text-danger">This action cannot be undone and may affect posts using this tag.</p>
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

        /* Nav pills styling for tabs */
        .nav-pills .nav-link {
            color: #344767;
            font-size: 0.875rem;
            font-weight: 500;
            border-radius: 0.5rem;
            padding: 0.75rem 1rem;
        }

        .nav-pills .nav-link.active {
            color: #fff;
            background: linear-gradient(310deg, #5e72e4 0%, #825ee4 100%);
            box-shadow: 0 3px 5px -1px rgba(94, 114, 228, 0.2), 0 2px 3px -1px rgba(94, 114, 228, 0.1);
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

        /* Modal styling */
        .modal-content {
            border: 0;
            border-radius: 0.75rem;
            box-shadow: 0 10px 35px -5px rgba(0, 0, 0, 0.15);
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
        
        /* CKEditor styling */
        .ck-editor__editable_inline {
            min-height: 200px;
            border-radius: 0 0 0.5rem 0.5rem !important;
            border-color: #d2d6da !important;
        }
        
        .ck-toolbar {
            border-radius: 0.5rem 0.5rem 0 0 !important;
            border-color: #d2d6da !important;
        }
        
        .ck.ck-editor__main > .ck-editor__editable:focus {
            border-color: #5e72e4 !important;
            box-shadow: 0 3px 9px rgba(50, 50, 9, 0), 3px 4px 8px rgba(94, 114, 228, 0.1) !important;
        }
    </style>
@endpush

@push('js')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.ckeditor.com/ckeditor5/36.0.1/classic/ckeditor.js"></script>

    <script>
        // Routes for AJAX calls
        const routes = {
            dataUrl: "{{ route('admin.blog-tags.data') }}",
            storeUrl: "{{ route('admin.blog-tags.store') }}",
            editUrl: "{{ route('admin.blog-tags.edit', ':id') }}",
            updateUrl: "{{ route('admin.blog-tags.update', ':id') }}",
            destroyUrl: "{{ route('admin.blog-tags.destroy', ':id') }}"
        };

        // Pass permissions data to JavaScript
        const canEditTags = @json(auth()->guard('admin')->user()->can('edit blog tags'));
        const canDeleteTags = @json(auth()->guard('admin')->user()->can('delete blog tags'));
    </script>

    <!-- Include the JS for tags management -->
    <script src="{{ asset('admin/js/blog-tags-management.js') }}"></script>

    <script>
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
            
            // Initialize CKEditor
            let editor;

            ClassicEditor
                .create(document.querySelector('#description'), {
                    toolbar: ['heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', '|', 'undo', 'redo']
                })
                .then(newEditor => {
                    editor = newEditor;
                    window.editor = editor; // Make it globally available

                    // Save CKEditor content to form when submitting
                    document.getElementById('tagForm').addEventListener('submit', function () {
                        document.getElementById('description').value = editor.getData();
                    });
                })
                .catch(error => {
                    console.error(error);
                });

            // Set up filters if they exist
            if (document.getElementById('statusFilter') && document.getElementById('searchFilter')) {
                $('#statusFilter, #searchFilter').on('change keyup', function () {
                    if (window.tagsTable) {
                        window.tagsTable.search($('#searchFilter').val()).draw();
                        window.tagsTable.column(5).search($('#statusFilter').val()).draw();
                    }
                });
                
                $('#resetFilters').on('click', function () {
                    $('#statusFilter').val('');
                    $('#searchFilter').val('');
                    if (window.tagsTable) {
                        window.tagsTable.search('').columns().search('').draw();
                    }
                });
            }
            
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

            // Modify the original handleEditTag function to set CKEditor content
            const originalHandleEditTag = window.handleEditTag;
            if (typeof originalHandleEditTag === 'function') {
                window.handleEditTag = function (tagId) {
                    originalHandleEditTag(tagId);

                    // Add additional code to set CKEditor content after modal is shown
                    $('#tagModal').on('shown.bs.modal', function () {
                        if (editor && document.getElementById('description')) {
                            const descriptionValue = document.getElementById('description').value;
                            editor.setData(descriptionValue);
                        }
                    });
                };
            }
            
            // Add delete confirmation modal functionality
            if (!document.getElementById('deleteModal')) {
                const deleteModal = document.createElement('div');
                deleteModal.className = 'modal fade';
                deleteModal.id = 'deleteModal';
                deleteModal.setAttribute('tabindex', '-1');
                deleteModal.setAttribute('aria-labelledby', 'deleteModalLabel');
                deleteModal.setAttribute('aria-hidden', 'true');
                
                deleteModal.innerHTML = `
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="py-3 text-center">
                                <i class="fas fa-exclamation-triangle text-warning fa-3x mb-3"></i>
                                <p>Are you sure you want to delete this tag?</p>
                                <p class="text-danger">This action cannot be undone and may affect posts using this tag.</p>
                                <div id="delete-warning" class="text-danger mt-3"></div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn bg-gradient-danger" id="confirmDeleteBtn">Delete</button>
                        </div>
                    </div>
                </div>
                `;
                
                document.body.appendChild(deleteModal);
            }
        });
    </script>
@endpush