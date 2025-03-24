@extends('admin.layouts.master')

@section('title', 'Permissions Management')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Permissions Management</h3>
                        <div class="card-tools">
                            @can('create permissions')
                                <button type="button" class="btn btn-primary btn-sm" id="createPermissionBtn">
                                    <i class="fas fa-plus"></i> Add New Permission
                                </button>
                            @endcan
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="permissions-table" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Guard</th>
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

    <!-- Create/Edit Permission Modal -->
    <div class="modal fade" id="permissionModal" tabindex="-1" aria-labelledby="permissionModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="permissionModalLabel">Add New Permission</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="permissionForm">
                    @csrf
                    <input type="hidden" name="permission_id" id="permission_id">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="name">Permission Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" required>
                            <div class="invalid-feedback" id="name-error"></div>
                            <small class="form-text text-muted">Enter a descriptive name for the permission (e.g., 'edit
                                posts').</small>
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
                    <p>Are you sure you want to delete this permission? This action cannot be undone.</p>
                    <p class="text-danger font-weight-bold">Warning: Deleting a permission will remove it from all roles
                        that have it!</p>
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
        .system-permission {
            background-color: rgba(0, 123, 255, 0.1);
        }
    </style>
@endpush

@push('js')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{asset('admin/js/permission-management.js')}}"></script>

    <script>
        // Define route URLs for AJAX calls
        const routes = {
            dataUrl: "{{ route('admin.permissions.data') }}",
            storeUrl: "{{ route('admin.permissions.store') }}",
            editUrl: "{{ route('admin.permissions.edit', ':id') }}",
            updateUrl: "{{ route('admin.permissions.update', ':id') }}",
            destroyUrl: "{{ route('admin.permissions.destroy', ':id') }}",
            validateFieldUrl: "{{ route('admin.permissions.validate-field') }}"
        };

        // System permissions that cannot be deleted
        const systemPermissions = [
            'manage admins', 'view admins', 'create admins', 'edit admins', 'delete admins',
            'manage roles', 'view roles', 'create roles', 'edit roles', 'delete roles',
            'manage permissions', 'view permissions', 'create permissions', 'edit permissions', 'delete permissions',
            'manage settings', 'access dashboard'
        ];

    </script>
@endpush