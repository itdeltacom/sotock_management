@extends('admin.layouts.master')

@section('title', 'Roles Management')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Roles Management</h3>
                        <div class="card-tools">
                            @can('create roles')
                                <button type="button" class="btn btn-primary btn-sm" id="createRoleBtn">
                                    <i class="fas fa-plus"></i> Add New Role
                                </button>
                            @endcan
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="roles-table" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Guard</th>
                                        <th>Permissions</th>
                                        <th>Permission Count</th>
                                        <th>Created At</th>
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

    <!-- Create/Edit Role Modal -->
    <div class="modal fade" id="roleModal" tabindex="-1" aria-labelledby="roleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="roleModalLabel">Add New Role</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="roleForm">
                    @csrf
                    <input type="hidden" name="role_id" id="role_id">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="name">Role Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" required>
                            <div class="invalid-feedback" id="name-error"></div>
                            <small class="form-text text-muted">Enter a descriptive name for the role (e.g., 'Editor',
                                'Content Manager').</small>
                        </div>

                        <div class="form-group">
                            <label>Permissions <span class="text-danger">*</span></label>
                            <div class="permissions-container border rounded p-3"
                                style="max-height: 350px; overflow-y: auto;">
                                <div class="row mb-2">
                                    <div class="col-12">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="select-all-permissions">
                                            <label class="custom-control-label font-weight-bold"
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
                                            <h6 class="font-weight-bold">{{ $group }}</h6>
                                            @foreach($perms as $permission)
                                                <div class="custom-control custom-checkbox mb-2">
                                                    <input type="checkbox" class="custom-control-input permission-checkbox"
                                                        id="permission_{{ $permission->id }}" name="permissions[]"
                                                        value="{{ $permission->id }}">
                                                    <label class="custom-control-label" for="permission_{{ $permission->id }}">
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
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="saveBtn">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Role Modal -->
    <div class="modal fade" id="viewRoleModal" tabindex="-1" aria-labelledby="viewRoleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewRoleModalLabel">Role Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
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
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
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
                    <p>Are you sure you want to delete this role? This action cannot be undone.</p>
                    <p class="text-danger font-weight-bold">Warning: Deleting a role will remove it from all users that have
                        it!</p>
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
        .super-admin-role {
            background-color: rgba(0, 123, 255, 0.1);
        }

        /* Permission badges */
        .badge-permission {
            display: inline-block;
            margin-right: 3px;
            margin-bottom: 3px;
        }
    </style>
@endpush

@push('js')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
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
    </script>
@endpush