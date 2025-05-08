@extends('admin.layouts.master')

@section('title', 'Roles Management')

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header pb-0 p-3">
                        <div class="row">
                            <div class="col-6 d-flex align-items-center">
                                <h6 class="mb-0">Roles Management</h6>
                            </div>
                            <div class="col-6 text-end">
                                @can('create roles')
                                    <button type="button" class="btn bg-gradient-primary" id="createRoleBtn">
                                        <i class="fas fa-plus"></i> Add New Role
                                    </button>
                                @endcan
                            </div>
                        </div>
                    </div>

                    <!-- Filters -->
                    <div class="card-header pb-0 p-3">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <select class="form-select form-select-sm" id="guardFilter">
                                    <option value="">All Guards</option>
                                    <option value="admin">Admin</option>
                                    <option value="web">Web</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <select class="form-select form-select-sm" id="permissionCountFilter">
                                    <option value="">All Roles</option>
                                    <option value="high">High Permission Count (10+)</option>
                                    <option value="medium">Medium Permission Count (5-9)</option>
                                    <option value="low">Low Permission Count (1-4)</option>
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
                            <table class="table align-items-center mb-0" id="roles-table" width="100%">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">ID
                                        </th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Name
                                        </th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Guard</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Permissions</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Permission Count</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Created At</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
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
    </div>

    <!-- Create/Edit Role Modal -->
    <div class="modal fade" id="roleModal" tabindex="-1" aria-labelledby="roleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="roleModalLabel">Add New Role</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="roleForm">
                    @csrf
                    <input type="hidden" name="role_id" id="role_id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="name" class="form-control-label">Role Name <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" required>
                            <div class="invalid-feedback" id="name-error"></div>
                            <small class="form-text text-muted">Enter a descriptive name for the role (e.g., 'Editor',
                                'Content Manager').</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-control-label">Permissions <span class="text-danger">*</span></label>
                            <div class="permissions-container border rounded p-3"
                                style="max-height: 350px; overflow-y: auto;">
                                <div class="row mb-2">
                                    <div class="col-12">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="select-all-permissions">
                                            <label class="form-check-label fw-bold"
                                                for="select-all-permissions">Select/Deselect All</label>
                                        </div>
                                    </div>
                                </div>

                                <hr>

                                <div class="row">
                                    <!-- Group permissions by module -->
                                    @php
                                        $groupedPermissions = $permissions->groupBy(function ($permission) {
                                            $name = $permission->name;
                                            if (strpos($name, 'admin') !== false)
                                                return 'Admin Management';
                                            if (strpos($name, 'role') !== false)
                                                return 'Role Management';
                                            if (strpos($name, 'permission') !== false)
                                                return 'Permission Management';
                                            if (strpos($name, 'dashboard') !== false)
                                                return 'Dashboard';
                                            if (strpos($name, 'setting') !== false)
                                                return 'Settings';
                                            return 'Other';
                                        });
                                    @endphp

                                    @foreach($groupedPermissions as $group => $perms)
                                        <div class="col-md-6 mb-3">
                                            <h6 class="fw-bold">{{ $group }}</h6>
                                            @foreach($perms as $permission)
                                                <div class="form-check mb-2">
                                                    <input type="checkbox" class="form-check-input permission-checkbox"
                                                        id="permission_{{ $permission->id }}" name="permissions[]"
                                                        value="{{ $permission->id }}">
                                                    <label class="form-check-label" for="permission_{{ $permission->id }}">
                                                        {{ ucfirst($permission->name) }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <div class="invalid-feedback" id="permissions-error"></div>
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

    <!-- View Role Modal -->
    <div class="modal fade" id="viewRoleModal" tabindex="-1" aria-labelledby="viewRoleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewRoleModalLabel">Role Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="role-info">
                        <h5 id="view-role-name"></h5>
                        <p><strong>Guard:</strong> <span id="view-role-guard"></span></p>
                        <p><strong>Created At:</strong> <span id="view-role-created"></span></p>

                        <h6 class="mt-4 mb-2">Permissions:</h6>
                        <div id="view-role-permissions" class="list-group">
                            <!-- Permissions will be inserted here -->
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn bg-gradient-primary" id="editRoleFromView">Edit</button>
                </div>
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
                        <p>Are you sure you want to delete this role? This action cannot be undone.</p>
                        <p class="text-danger fw-bold">Warning: Deleting a role will remove it from all users that have it!
                        </p>
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

        /* Super admin role styling */
        .super-admin-role {
            background-color: rgba(94, 114, 228, 0.1);
        }

        /* Permission badges */
        .badge-permission {
            display: inline-block;
            margin-right: 3px;
            margin-bottom: 3px;
            font-size: 0.75em;
            font-weight: 700;
            padding: 0.35em 0.65em;
            border-radius: 0.5rem;
            color: #fff;
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

        /* List group styling for permissions */
        .list-group-item {
            border-radius: 0.5rem;
            margin-bottom: 0.25rem;
            font-size: 0.875rem;
        }
    </style>
@endpush

@push('js')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{asset('admin/js/role-management.js')}}"></script>

    <script>
        // Define route URLs for AJAX calls
        const routes = {
            dataUrl: "{{ route('admin.roles.data') }}",
            storeUrl: "{{ route('admin.roles.store') }}",
            showUrl: "{{ route('admin.roles.show', ':id') }}",
            editUrl: "{{ route('admin.roles.edit', ':id') }}",
            updateUrl: "{{ route('admin.roles.update', ':id') }}",
            destroyUrl: "{{ route('admin.roles.destroy', ':id') }}",
            validateFieldUrl: "{{ route('admin.roles.validate-field') }}"
        };

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
            if (document.getElementById('guardFilter') && document.getElementById('permissionCountFilter')) {
                $('#guardFilter, #permissionCountFilter').on('change', function () {
                    if (window.rolesTable) {
                        window.rolesTable.ajax.reload();
                    }
                });

                $('#resetFilters').on('click', function () {
                    $('#guardFilter, #permissionCountFilter').val('');
                    if (window.rolesTable) {
                        window.rolesTable.ajax.reload();
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

            // Update Bootstrap 5 modal dismiss
            document.querySelectorAll('[data-dismiss="modal"]').forEach(button => {
                button.setAttribute('data-bs-dismiss', 'modal');
                button.removeAttribute('data-dismiss');
            });

            // Add edit from view functionality
            if (document.getElementById('editRoleFromView')) {
                document.getElementById('editRoleFromView').addEventListener('click', function () {
                    const roleId = this.getAttribute('data-id');
                    if (roleId) {
                        $('#viewRoleModal').modal('hide');
                        if (typeof editRole === 'function') {
                            editRole(roleId);
                        } else {
                            // Fallback if external function not available
                            window.location.href = routes.editUrl.replace(':id', roleId);
                        }
                    }
                });
            }

            // Select all permissions functionality
            document.getElementById('select-all-permissions').addEventListener('change', function () {
                const isChecked = this.checked;
                document.querySelectorAll('.permission-checkbox').forEach(checkbox => {
                    checkbox.checked = isChecked;
                });
            });
        });
    </script>
@endpush