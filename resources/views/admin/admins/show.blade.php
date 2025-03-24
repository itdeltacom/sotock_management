@extends('admin.layouts.master')

@section('title', 'Admin Details')

@section('content')
    <!-- Page Header -->
    <div class="page-header">
        <h3 class="page-title">Admin User Details</h3>
        <div class="page-actions">
            <a href="{{ route('admin.admins.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>

            @can('edit admins')
                <button type="button" class="btn btn-primary edit-admin" data-id="{{ $admin->id }}">
                    <i class="fas fa-edit"></i> Edit Admin
                </button>
            @endcan

            @if(auth()->guard('admin')->user()->can('delete admins') && auth()->guard('admin')->id() !== $admin->id)
                <button type="button" class="btn btn-danger delete-admin" data-id="{{ $admin->id }}">
                    <i class="fas fa-trash"></i> Delete
                </button>
            @endif
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <!-- Admin Profile Card -->
            <div class="card">
                <div class="card-body text-center">
                    <div class="profile-image-container">
                        <div class="profile-image mb-3" style="width: 150px; height: 150px;">
                            @if($admin->profile_image)
                                <img src="{{ asset('storage/' . $admin->profile_image) }}" alt="{{ $admin->name }}">
                            @else
                                <img src="{{ asset('img/default-user.png') }}" alt="{{ $admin->name }}">
                            @endif
                        </div>
                    </div>

                    <h4 class="mt-3">{{ $admin->name }}</h4>

                    <div class="text-muted">
                        @if($admin->position)
                            <div>{{ $admin->position }}</div>
                        @endif

                        @if($admin->department)
                            <div>{{ $admin->department }}</div>
                        @endif
                    </div>

                    <div class="mt-3">
                        @if($admin->is_active)
                            <span class="badge badge-success">Active</span>
                        @else
                            <span class="badge badge-danger">Inactive</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Admin Roles Card -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title">Assigned Roles</h5>
                </div>
                <div class="card-body">
                    @if($admin->roles->count() > 0)
                        <div class="admin-roles">
                            @foreach($admin->roles as $role)
                                <div class="admin-role-item mb-2">
                                    <span class="badge badge-primary py-2 px-3">{{ $role->name }}</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted">No roles assigned</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <!-- Admin Details Card -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Contact Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Email:</label>
                                <div>{{ $admin->email }}</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Phone:</label>
                                <div>{{ $admin->phone ?? 'Not provided' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Admin Permissions Card -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title">Permissions</h5>
                </div>
                <div class="card-body">
                    @if($admin->permissions->count() > 0 || $admin->getPermissionsViaRoles()->count() > 0)
                                    <div class="row">
                                        @php
                                            $allPermissions = $admin->getAllPermissions();
                                            $permissionGroups = $allPermissions->groupBy(function ($permission) {
                                                return explode(' ', $permission->name)[0];
                                            });
                                        @endphp

                                        @foreach($permissionGroups as $group => $permissions)
                                            <div class="col-md-6 mb-4">
                                                <h6 class="text-primary font-weight-bold">{{ ucfirst($group) }}</h6>
                                                <ul class="list-unstyled">
                                                    @foreach($permissions as $permission)
                                                        <li>
                                                            <i class="fas fa-check-circle text-success mr-2"></i>
                                                            {{ ucfirst(explode(' ', $permission->name)[1]) }} {{ $group }}
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endforeach
                                    </div>
                    @else
                        <p class="text-muted">No specific permissions</p>
                    @endif
                </div>
            </div>

            <!-- Account Information Card -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title">Account Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Created:</label>
                                <div>{{ $admin->created_at->format('F j, Y \a\t g:i a') }}</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Last Updated:</label>
                                <div>{{ $admin->updated_at->format('F j, Y \a\t g:i a') }}</div>
                            </div>
                        </div>
                    </div>

                    @if($admin->last_login_at)
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold">Last Login:</label>
                                    <div>{{ \Carbon\Carbon::parse($admin->last_login_at)->format('F j, Y \a\t g:i a') }}</div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold">Last Login IP:</label>
                                    <div>{{ $admin->last_login_ip ?? 'N/A' }}</div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @can('delete admins')
        <!-- Delete Confirmation Modal -->
        <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to delete this admin user? This action cannot be undone.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
                    </div>
                </div>
            </div>
        </div>
    @endcan

    @if(auth()->guard('admin')->user()->can('edit admins'))
        <!-- Admin Modal (for editing) -->
        <div class="modal fade" id="adminModal" tabindex="-1" role="dialog" aria-labelledby="adminModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <!-- Modal content will be loaded via AJAX -->
                </div>
            </div>
        </div>
    @endif
@endsection

@push('js')
    <script>
        $(document).ready(function () {
            @can('edit admins')
                // Edit admin user
                $('.edit-admin').click(function () {
                    const adminId = $(this).data('id');

                    // Show loading in the modal
                    $('#adminModal').modal('show');
                    $('.modal-content').html('<div class="text-center py-5"><i class="fas fa-spinner fa-spin fa-2x"></i><p class="mt-2">Loading...</p></div>');

                    // Load edit form via AJAX
                    $.ajax({
                        url: "{{ route('admin.admins.edit', ':id') }}".replace(':id', adminId),
                        method: 'GET',
                        success: function (response) {
                            // Populate modal with form
                            $.ajax({
                                url: "{{ route('admin.admins.edit.form', ':id') }}".replace(':id', adminId),
                                method: 'GET',
                                success: function (form) {
                                    $('.modal-content').html(form);

                                    // Fill form with admin data
                                    $('#admin_id').val(response.admin.id);
                                    $('#name').val(response.admin.name);
                                    $('#email').val(response.admin.email);
                                    $('#phone').val(response.admin.phone);
                                    $('#position').val(response.admin.position);
                                    $('#department').val(response.admin.department);
                                    $('#is_active').val(response.admin.is_active);

                                    // Set profile image if exists
                                    if (response.admin.profile_image) {
                                        $('#profile_image_preview').attr('src', "{{ asset('storage') }}/" + response.admin.profile_image);
                                    }

                                    // Check roles
                                    response.admin.roles.forEach(function (role) {
                                        $('#role_' + role).prop('checked', true);
                                    });

                                    // Setup event handlers
                                    initFormEvents();
                                },
                                error: function () {
                                    $('#adminModal').modal('hide');
                                    showNotification('error', 'Failed to load edit form.');
                                }
                            });
                        },
                        error: function () {
                            $('#adminModal').modal('hide');
                            showNotification('error', 'Failed to load admin data.');
                        }
                    });
                });
            @endcan

                @can('delete admins')
                    // Delete admin user
                    let deleteAdminId = null;

                    $('.delete-admin').click(function () {
                        deleteAdminId = $(this).data('id');
                        $('#deleteModal').modal('show');
                    });

                    $('#confirmDeleteBtn').click(function () {
                        if (deleteAdminId) {
                            // Show loading
                            $(this).html('<i class="fas fa-spinner fa-spin"></i> Deleting...').attr('disabled', true);

                            // Send delete request
                            $.ajax({
                                url: "{{ route('admin.admins.destroy', ':id') }}".replace(':id', deleteAdminId),
                                method: 'DELETE',
                                data: {
                                    _token: "{{ csrf_token() }}"
                                },
                                success: function (response) {
                                    $('#deleteModal').modal('hide');
                                    showNotification('success', response.message || 'Admin user deleted successfully!');

                                    // Redirect back to index page after successful deletion
                                    setTimeout(function () {
                                        window.location.href = "{{ route('admin.admins.index') }}";
                                    }, 1500);
                                },
                                error: function (xhr) {
                                    $('#deleteModal').modal('hide');
                                    const errorMessage = xhr.responseJSON?.message || 'Failed to delete admin user.';
                                    showNotification('error', errorMessage);
                                },
                                complete: function () {
                                    $('#confirmDeleteBtn').html('Delete').attr('disabled', false);
                                    deleteAdminId = null;
                                }
                            });
                        }
                    });
                @endcan

                // Helper function to initialize form events
                function initFormEvents() {
                    // Toggle password visibility
                    $('.toggle-password').click(function () {
                        const passwordInput = $(this).closest('.input-group').find('input');
                        const icon = $(this).find('i');

                        if (passwordInput.attr('type') === 'password') {
                            passwordInput.attr('type', 'text');
                            icon.removeClass('fa-eye').addClass('fa-eye-slash');
                        } else {
                            passwordInput.attr('type', 'password');
                            icon.removeClass('fa-eye-slash').addClass('fa-eye');
                        }
                    });

                    // Profile image preview
                    $('#profile_image').change(function () {
                        const file = this.files[0];
                        if (file) {
                            const reader = new FileReader();
                            reader.onload = function (e) {
                                $('#profile_image_preview').attr('src', e.target.result);
                            }
                            reader.readAsDataURL(file);
                        }
                    });

                    // Handle form submission
                    $('#adminForm').submit(function (e) {
                        e.preventDefault();

                        // Clear previous error messages
                        $('.is-invalid').removeClass('is-invalid');
                        $('.invalid-feedback').text('');

                        // Get form data
                        const formData = new FormData(this);
                        const adminId = $('#admin_id').val();

                        // We're updating
                        const url = "{{ route('admin.admins.update', ':id') }}".replace(':id', adminId);
                        formData.append('_method', 'PUT');

                        // Show loading indicator
                        $('#saveAdminBtn').html('<i class="fas fa-spinner fa-spin"></i> Saving...').attr('disabled', true);

                        // Submit the form
                        $.ajax({
                            url: url,
                            method: 'POST',
                            data: formData,
                            processData: false,
                            contentType: false,
                            success: function (response) {
                                $('#adminModal').modal('hide');

                                // Show success notification
                                showNotification('success', response.message || 'Admin user updated successfully!');

                                // Reload the page to reflect changes
                                setTimeout(function () {
                                    location.reload();
                                }, 1500);
                            },
                            error: function (xhr) {
                                if (xhr.status === 422) {
                                    const errors = xhr.responseJSON.errors;
                                    // Display validation errors
                                    for (const field in errors) {
                                        const errorField = field.replace(/\./g, '_');
                                        $('#' + errorField).addClass('is-invalid');
                                        $('#' + errorField + '-error').text(errors[field][0]);
                                    }
                                } else {
                                    // Show error notification
                                    showNotification('error', 'An error occurred. Please try again.');
                                }

                                $('#saveAdminBtn').html('Save Admin').attr('disabled', false);
                            }
                        });
                    });
                }

            // Helper function to show notification
            function showNotification(type, message) {
                const icon = type === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-circle';
                const className = type === 'success' ? 'alert-success' : 'alert-danger';

                const notification = `
                    <div class="alert ${className} alert-dismissible fade show" role="alert">
                        <i class="${icon} mr-2"></i>
                        ${message}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                `;

                $('.admin-main').prepend(notification);

                // Auto-dismiss after 5 seconds
                setTimeout(function () {
                    $('.alert').alert('close');
                }, 5000);
            }
        });
    </script>
@endpush

@push('css')
    <style>
        .profile-image {
            border-radius: 50%;
            overflow: hidden;
            border: 3px solid #e9ecef;
            background-color: #f8f9fa;
        }

        .profile-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .admin-role-item .badge {
            font-size: 0.85rem;
        }

        .admin-roles {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
    </style>
@endpush