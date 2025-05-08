@extends('admin.layouts.master')

@section('title', 'My Profile')

@section('content')
    <div class="container-fluid py-4">
        <div class="page-header">
            <h3 class="page-title">My Profile</h3>
            <div class="page-actions">
                <button type="button" id="save-profile" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Changes
                </button>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header pb-0">
                        <h6>Personal Information</h6>
                        <p class="text-sm text-muted">
                            Update your profile details and manage your account settings.
                        </p>
                    </div>
                    <div class="card-body">
                        <form id="profile-form" action="{{ route('admin.profile.update') }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <div class="row">
                                <!-- Profile Image -->
                                <div class="col-md-3 text-center mb-4">
                                    <div class="profile-image-container">
                                        <div class="profile-image mb-3">
                                            @if($admin->profile_image)
                                                <img src="{{ Storage::url($admin->profile_image) }}" alt="{{ $admin->name }}"
                                                    id="profile-preview" class="img-fluid rounded-circle">
                                            @else
                                                <img src="{{ asset('img/default-avatar.png') }}" alt="{{ $admin->name }}"
                                                    id="profile-preview" class="img-fluid rounded-circle">
                                            @endif
                                        </div>
                                        <div class="profile-image-actions">
                                            <label for="profile_image" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-upload"></i> Change Image
                                            </label>
                                            <input type="file" name="profile_image" id="profile_image" class="d-none"
                                                accept="image/*">
                                        </div>
                                        <div class="text-muted small mt-2">
                                            Recommended size: 200x200px
                                        </div>
                                    </div>
                                </div>

                                <!-- Profile Details -->
                                <div class="col-md-9">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="name" class="form-label">Full Name</label>
                                            <input type="text" name="name" id="name"
                                                class="form-control @error('name') is-invalid @enderror"
                                                value="{{ old('name', $admin->name) }}" required>
                                            @error('name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label for="email" class="form-label">Email Address</label>
                                            <input type="email" name="email" id="email"
                                                class="form-control @error('email') is-invalid @enderror"
                                                value="{{ old('email', $admin->email) }}" required>
                                            @error('email')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label for="phone" class="form-label">Phone Number</label>
                                            <input type="text" name="phone" id="phone"
                                                class="form-control @error('phone') is-invalid @enderror"
                                                value="{{ old('phone', $admin->phone) }}">
                                            @error('phone')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label for="position" class="form-label">Position</label>
                                            <input type="text" name="position" id="position"
                                                class="form-control @error('position') is-invalid @enderror"
                                                value="{{ old('position', $admin->position) }}">
                                            @error('position')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label for="department" class="form-label">Department</label>
                                            <input type="text" name="department" id="department"
                                                class="form-control @error('department') is-invalid @enderror"
                                                value="{{ old('department', $admin->department) }}">
                                            @error('department')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <hr class="horizontal dark">

                            <!-- Password Section -->
                            <h6 class="mt-4 mb-3">Change Password</h6>
                            <p class="text-sm text-muted mb-4">Leave password fields empty if you don't want to change it
                            </p>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="current_password" class="form-label">Current Password</label>
                                    <div class="input-group">
                                        <input type="password" name="current_password" id="current_password"
                                            class="form-control input_pass @error('current_password') is-invalid @enderror">
                                        <button type="button" class="btn btn-outline-secondary password-toggle"
                                            tabindex="-1">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    @error('current_password')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="password" class="form-label">New Password</label>
                                    <div class="input-group">
                                        <input type="password" name="password" id="password"
                                            class="form-control input_pass @error('password') is-invalid @enderror">
                                        <button type="button" class="btn btn-outline-secondary password-toggle"
                                            tabindex="-1">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    @error('password')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror

                                    <div class="password-strength-container mt-2 d-none">
                                        <div class="password-strength-meter"></div>
                                        <div class="password-strength-text small"></div>
                                    </div>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="password_confirmation" class="form-label">Confirm New Password</label>
                                    <div class="input-group">
                                        <input type="password" name="password_confirmation" id="password_confirmation"
                                            class="form-control input_pass">
                                        <button type="button" class="btn btn-outline-secondary password-toggle"
                                            tabindex="-1">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('css')
    <style>
        /* Card Styling */
        .card {
            box-shadow: 0 20px 27px 0 rgba(0, 0, 0, 0.05);
            border-radius: 0.75rem;
        }

        .card .card-header {
            padding: 1.5rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .card-header h6 {
            margin-bottom: 0.25rem;
            font-size: 0.875rem;
            font-weight: 600;
            color: #344767;
        }

        .card-header p.text-sm {
            color: #8392AB;
            font-size: 0.75rem;
        }

        /* Profile Image Styling */
        .profile-image-container {
            padding: 1rem;
            text-align: center;
        }

        .profile-image img {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border: 4px solid #f5f5f5;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .profile-image-actions .btn {
            margin-top: 0.5rem;
        }

        /* Form Inputs */
        .form-control {
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
            line-height: 1.4;
            border-radius: 0.5rem;
            transition: box-shadow 0.15s ease, border-color 0.15s ease;
        }

        .form-control:focus {
            border-color: #5e72e4;
            box-shadow: 0 3px 9px rgba(0, 0, 0, 0), 3px 4px 8px rgba(94, 114, 228, 0.1);
        }

        .form-label {
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
            font-weight: 600;
            color: #8392AB;
        }

        /* Password Strength Meter */
        .password-strength-container {
            height: 5px;
            background-color: #e9ecef;
            border-radius: 5px;
            overflow: hidden;
        }

        .password-strength-meter {
            height: 100%;
            width: 0;
            transition: width 0.3s ease;
            border-radius: 5px;
        }

        .input_pass {
            height: 100%;
        }

        .password-strength-meter.bg-danger {
            background: linear-gradient(310deg, #f5365c 0%, #f56036 100%);
        }

        .password-strength-meter.bg-warning {
            background: linear-gradient(310deg, #fb6340 0%, #fbb140 100%);
        }

        .password-strength-meter.bg-success {
            background: linear-gradient(310deg, #2dce89 0%, #2dcecc 100%);
        }

        .password-strength-text {
            margin-top: 5px;
            color: #8392AB;
        }

        /* Buttons */
        .btn-primary {
            background: linear-gradient(310deg, #5e72e4 0%, #825ee4 100%);
            border: none;
            box-shadow: 0 3px 5px -1px rgba(94, 114, 228, 0.2), 0 2px 3px -1px rgba(94, 114, 228, 0.1);
        }

        .btn-outline-primary {
            border-color: #5e72e4;
            color: #5e72e4;
        }

        /* Page Header */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .page-header h3 {
            margin-bottom: 0;
            font-size: 1.25rem;
            color: #344767;
        }

        .page-actions .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* Input Group */
        .input-group .btn {
            background-color: #f6f9fc;
            border-color: #e9ecef;
            color: #8392AB;
        }

        .input-group .btn:hover {
            background-color: #e9ecef;
        }

        /* Horizontal Rule */
        .horizontal.dark {
            background-color: rgba(0, 0, 0, 0.1);
            height: 1px;
            margin: 1.5rem 0;
        }
    </style>
@endpush

@push('js')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Image preview
            const profileImage = document.getElementById('profile_image');
            const profilePreview = document.getElementById('profile-preview');

            if (profileImage && profilePreview) {
                profileImage.addEventListener('change', function (e) {
                    if (e.target.files.length > 0) {
                        const reader = new FileReader();
                        reader.onload = function (e) {
                            profilePreview.src = e.target.result;
                        };
                        reader.readAsDataURL(e.target.files[0]);
                    }
                });
            }

            // Form submission from save button
            const saveButton = document.getElementById('save-profile');
            const profileForm = document.getElementById('profile-form');

            if (saveButton && profileForm) {
                saveButton.addEventListener('click', function () {
                    profileForm.submit();
                });
            }

            // Password toggle visibility
            document.querySelectorAll('.password-toggle').forEach(button => {
                button.addEventListener('click', function () {
                    const passwordInput = this.parentElement.querySelector('input');
                    if (passwordInput) {
                        if (passwordInput.type === 'password') {
                            passwordInput.type = 'text';
                            this.querySelector('i').classList.remove('fa-eye');
                            this.querySelector('i').classList.add('fa-eye-slash');
                        } else {
                            passwordInput.type = 'password';
                            this.querySelector('i').classList.remove('fa-eye-slash');
                            this.querySelector('i').classList.add('fa-eye');
                        }
                    }
                });
            });

            // Password strength meter
            const passwordInput = document.getElementById('password');
            const strengthContainer = document.querySelector('.password-strength-container');
            const strengthMeter = document.querySelector('.password-strength-meter');
            const strengthText = document.querySelector('.password-strength-text');

            if (passwordInput && strengthContainer && strengthMeter && strengthText) {
                passwordInput.addEventListener('input', function () {
                    const password = this.value;

                    if (password.length > 0) {
                        strengthContainer.classList.remove('d-none');

                        // Calculate strength
                        let strength = 0;
                        let feedback = '';

                        // Length checks
                        if (password.length >= 8) strength += 25;
                        if (password.length >= 12) strength += 15;

                        // Character type checks
                        if (/[A-Z]/.test(password)) strength += 10;
                        if (/[a-z]/.test(password)) strength += 10;
                        if (/[0-9]/.test(password)) strength += 10;
                        if (/[^A-Za-z0-9]/.test(password)) strength += 15;

                        // Variety of characters
                        const uniqueChars = new Set(password.split('')).size;
                        const uniqueRatio = uniqueChars / password.length;
                        strength += Math.round(uniqueRatio * 15);

                        // Cap at 100
                        strength = Math.min(100, strength);

                        // Update UI
                        strengthMeter.style.width = strength + '%';

                        // Remove existing classes
                        strengthMeter.classList.remove('bg-danger', 'bg-warning', 'bg-success');

                        // Set color and feedback based on strength
                        if (strength < 40) {
                            strengthMeter.classList.add('bg-danger');
                            feedback = 'Weak password. Add more variety and length.';
                        } else if (strength < 70) {
                            strengthMeter.classList.add('bg-warning');
                            feedback = 'Medium strength. Consider adding special characters.';
                        } else {
                            strengthMeter.classList.add('bg-success');
                            feedback = 'Strong password. Good job!';
                        }

                        strengthText.textContent = feedback;
                    } else {
                        strengthContainer.classList.add('d-none');
                    }
                });
            }
        });
    </script>
@endpush