@extends('admin.layouts.master')

@section('title', 'Admins Management')

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header pb-0 p-3">
                        <div class="row">
                            <div class="col-6 d-flex align-items-center">
                                <h6 class="mb-0">Admin Users Management</h6>
                            </div>
                            <div class="col-6 text-end">
                                @can('create admins')
                                    <button type="button" class="btn bg-gradient-primary" id="createAdminBtn">
                                        <i class="fas fa-plus"></i> Add New Admin
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
                                <select class="form-select form-select-sm" id="roleFilter">
                                    <option value="">All Roles</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id }}">{{ $role->name }}</option>
                                    @endforeach
                                </select>
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
                            <table class="table align-items-center mb-0" id="admins-table" width="100%">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">ID</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Name</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Email</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Roles</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                                        @if(auth()->guard('admin')->user()->can('edit admins') || auth()->guard('admin')->user()->can('delete admins'))
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

    <!-- Create/Edit Admin Modal -->
    <div class="modal fade" id="adminModal" tabindex="-1" aria-labelledby="adminModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="adminModalLabel">Add New Admin</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="adminForm" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="admin_id" id="admin_id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="name" class="form-control-label">Full Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                            <div class="invalid-feedback" id="name-error"></div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-control-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                            <div class="invalid-feedback" id="email-error"></div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-control-label">Phone</label>
                                <input type="text" class="form-control" id="phone" name="phone">
                                <div class="invalid-feedback" id="phone-error"></div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="position" class="form-control-label">Position</label>
                                <input type="text" class="form-control" id="position" name="position">
                                <div class="invalid-feedback" id="position-error"></div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="department" class="form-control-label">Department</label>
                            <input type="text" class="form-control" id="department" name="department">
                            <div class="invalid-feedback" id="department-error"></div>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-control-label">Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control input-pass" id="password" name="password">
                                <button class="btn btn-outline-secondary toggle-password" type="button">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="invalid-feedback" id="password-error"></div>
                            <small class="form-text text-muted password-help">
                                Leave blank to keep current password when editing.
                            </small>
                        </div>

                        <div class="mb-3">
                            <label for="password_confirmation" class="form-control-label">Confirm Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control input-pass" id="password_confirmation"
                                    name="password_confirmation">
                                <button class="btn btn-outline-secondary toggle-password" type="button">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="invalid-feedback" id="password_confirmation-error"></div>
                        </div>

                        <div class="mb-3">
                            <label for="is_active" class="form-control-label">Status</label>
                            <select class="form-control" id="is_active" name="is_active">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                            <div class="invalid-feedback" id="is_active-error"></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-control-label">Assign Roles</label>
                            <div class="roles-container border rounded p-3" style="max-height: 200px; overflow-y: auto;">
                                @foreach($roles as $role)
                                    <div class="form-check mb-2">
                                        <input type="checkbox" class="form-check-input" id="role_{{ $role->id }}" name="roles[]"
                                            value="{{ $role->id }}">
                                        <label class="form-check-label" for="role_{{ $role->id }}">{{ $role->name }}</label>
                                    </div>
                                @endforeach
                            </div>
                            <div class="invalid-feedback" id="roles-error"></div>
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
                        <p>Are you sure you want to delete this admin user? This action cannot be undone.</p>
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
.input-pass{
    height: 100%;
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

        .modal-70,
        .modal-xl {
            max-width: 70%;
            margin: 1.75rem auto;
        }

        .modal-70 .modal-content,
        .modal-xl .modal-content {
            max-height: 85vh;
        }

        .modal-70 .modal-body,
        .modal-xl .modal-body {
            overflow-y: auto;
            max-height: calc(85vh - 130px);
            padding: 1.5rem;
        }

        .progress {
            border-radius: 0.5rem;
            overflow: hidden;
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

        /* Badge styling */
        .badge-role {
            margin-right: 4px;
            margin-bottom: 4px;
            display: inline-block;
            padding: 0.35em 0.65em;
            font-size: 0.75em;
            font-weight: 700;
            line-height: 1;
            color: #fff;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            border-radius: 0.5rem;
            background: linear-gradient(310deg, #5e72e4 0%, #825ee4 100%);
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
            dataUrl: "{{ route('admin.admins.data') }}",
            storeUrl: "{{ route('admin.admins.store') }}",
            editUrl: "{{ route('admin.admins.edit', ':id') }}",
            updateUrl: "{{ route('admin.admins.update', ':id') }}",
            deleteUrl: "{{ route('admin.admins.destroy', ':id') }}"
        };

        // Pass permissions data to JavaScript
        const canEditAdmins = @json(auth()->guard('admin')->user()->can('edit admins'));
        const canDeleteAdmins = @json(auth()->guard('admin')->user()->can('delete admins'));
        const currentAdminId = @json(auth()->guard('admin')->id());
    </script>

    <!-- Include the optimized JS -->
    <script src="{{ asset('admin/js/admins-management.js') }}"></script>
    
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

            // Set up filters if they exist
            if (document.getElementById('statusFilter') && document.getElementById('roleFilter')) {
                $('#statusFilter, #roleFilter').on('change', function () {
                    if (window.adminsTable) {
                        window.adminsTable.ajax.reload();
                    }
                });
                
                $('#resetFilters').on('click', function () {
                    $('#statusFilter, #roleFilter').val('');
                    if (window.adminsTable) {
                        window.adminsTable.ajax.reload();
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
            
            // Add eye/eye-slash toggle for password fields
            document.querySelectorAll('.toggle-password').forEach(button => {
                button.addEventListener('click', function() {
                    const passwordField = this.closest('.input-group').querySelector('input');
                    const icon = this.querySelector('i');
                    
                    if (passwordField.type === 'password') {
                        passwordField.type = 'text';
                        icon.classList.remove('fa-eye');
                        icon.classList.add('fa-eye-slash');
                    } else {
                        passwordField.type = 'password';
                        icon.classList.remove('fa-eye-slash');
                        icon.classList.add('fa-eye');
                    }
                });
            });
        });
    </script>
@endpush