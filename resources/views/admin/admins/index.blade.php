@extends('admin.layouts.master')

@section('title', 'Admins Management')

@section('content')
    <div class="page-header">
        <h3 class="page-title">Admin Users Management</h3>
        <div class="page-actions">
            @can('create admins')
                <button type="button" class="btn btn-primary" id="createAdminBtn">
                    <i class="fas fa-plus"></i> Add New Admin
                </button>
            @endcan
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title">All Admin Users</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="admins-table" width="100%">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Roles</th>
                            <th>Status</th>
                            @if(auth()->guard('admin')->user()->can('edit admins') || auth()->guard('admin')->user()->can('delete admins'))
                                <th>Actions</th>
                            @endif
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <!-- Create/Edit Admin Modal -->
    <div class="modal fade" id="adminModal" tabindex="-1" aria-labelledby="adminModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
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
                            <label for="name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                            <div class="invalid-feedback" id="name-error"></div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                            <div class="invalid-feedback" id="email-error"></div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Phone</label>
                                <input type="text" class="form-control" id="phone" name="phone">
                                <div class="invalid-feedback" id="phone-error"></div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="position" class="form-label">Position</label>
                                <input type="text" class="form-control" id="position" name="position">
                                <div class="invalid-feedback" id="position-error"></div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="department" class="form-label">Department</label>
                            <input type="text" class="form-control" id="department" name="department">
                            <div class="invalid-feedback" id="department-error"></div>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="password" name="password">
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
                            <label for="password_confirmation" class="form-label">Confirm Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="password_confirmation"
                                    name="password_confirmation">
                                <button class="btn btn-outline-secondary toggle-password" type="button">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="invalid-feedback" id="password_confirmation-error"></div>
                        </div>

                        <div class="mb-3">
                            <label for="is_active" class="form-label">Status</label>
                            <select class="form-select" id="is_active" name="is_active">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                            <div class="invalid-feedback" id="is_active-error"></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Assign Roles</label>
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
                    <p>Are you sure you want to delete this admin user? This action cannot be undone.</p>
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
    <style>
        .badge-role {
            margin-right: 4px;
            margin-bottom: 4px;
            display: inline-block;
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
@endpush