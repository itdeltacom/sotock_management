@extends('admin.layouts.master')

@section('title', 'Change Password')

@section('content')
    <div class="container-fluid py-4">
        <div class="page-header">
            <h3 class="page-title">{{ __('Change Password') }}</h3>
            <div class="page-actions">
                <button type="button" id="save-password" class="btn btn-primary">
                    <i class="fas fa-save"></i> {{ __('Save New Password') }}
                </button>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8 col-md-10 mx-auto">
                <div class="card mb-4">
                    <div class="card-header pb-0">
                        <div class="d-flex align-items-center">
                            <div>
                                <h6>{{ __('Update Your Password') }}</h6>
                                <p class="text-sm text-muted">
                                    {{ __('For security purposes, please enter your current password before setting a new one.') }}
                                </p>
                            </div>
                            <div class="ms-auto">
                                <div
                                    class="avatar avatar-md bg-gradient-primary rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="ni ni-key-25 text-lg text-white"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <form id="password-form" action="{{ route('admin.password.update') }}" method="POST">
                            @csrf

                            <div class="mb-4">
                                <label for="current_password" class="form-label">{{ __('Current Password') }}</label>
                                <div class="input-group">
                                    <input type="password" name="current_password" id="current_password"
                                        class="form-control input_pass @error('current_password') is-invalid @enderror"
                                        required>
                                    <button type="button" class="btn btn-outline-secondary password-toggle" tabindex="-1">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                @error('current_password')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label for="password" class="form-label">{{ __('New Password') }}</label>
                                <div class="input-group">
                                    <input type="password" name="password" id="password"
                                        class="form-control input_pass @error('password') is-invalid @enderror" required>
                                    <button type="button" class="btn btn-outline-secondary password-toggle" tabindex="-1">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                @error('password')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror

                                <div class="password-strength-container mt-2">
                                    <div class="password-strength-meter"></div>
                                    <div class="password-strength-text small"></div>
                                </div>
                                <div class="password-requirements mt-3">
                                    <p class="text-xs text-muted mb-1">{{ __('Your password should:') }}</p>
                                    <ul class="text-xs text-muted ps-3 mb-0">
                                        <li class="req-length">{{ __('Be at least 8 characters long') }}</li>
                                        <li class="req-uppercase">{{ __('Include at least one uppercase letter') }}</li>
                                        <li class="req-lowercase">{{ __('Include at least one lowercase letter') }}</li>
                                        <li class="req-number">{{ __('Include at least one number') }}</li>
                                        <li class="req-special">{{ __('Include at least one special character') }}</li>
                                    </ul>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="password_confirmation"
                                    class="form-label">{{ __('Confirm New Password') }}</label>
                                <div class="input-group">
                                    <input type="password" name="password_confirmation" id="password_confirmation"
                                        class="form-control input_pass" required>
                                    <button type="button" class="btn btn-outline-secondary password-toggle" tabindex="-1">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="match-status mt-1 text-xs"></div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="alert alert-info d-flex align-items-center" role="alert">
                                        <div class="alert-icon me-3">
                                            <i class="fas fa-info-circle fa-lg"></i>
                                        </div>
                                        <div>
                                            {{ __('After changing your password, you will be redirected to the login page to sign in with your new credentials.') }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex mt-4">
                                <a href="{{ route('admin.profile') }}" class="btn btn-outline-secondary me-2">
                                    <i class="fas fa-arrow-left me-1"></i> {{ __('Back to Profile') }}
                                </a>
                                <button type="submit" class="btn btn-primary ms-auto">
                                    <i class="fas fa-key me-1"></i> {{ __('Update Password') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header pb-0">
                        <h6>{{ __('Security Recommendations') }}</h6>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item border-0 ps-0">
                                <div class="d-flex">
                                    <div
                                        class="icon icon-shape icon-sm rounded-circle bg-gradient-success me-3 d-flex align-items-center justify-content-center">
                                        <i class="fas fa-check text-white text-xs"></i>
                                    </div>
                                    <div class="d-flex flex-column">
                                        <h6 class="mb-1 text-dark text-sm">{{ __('Use a unique password') }}</h6>
                                        <span
                                            class="text-xs">{{ __('Do not reuse passwords from other sites or applications.') }}</span>
                                    </div>
                                </div>
                            </li>
                            <li class="list-group-item border-0 ps-0">
                                <div class="d-flex">
                                    <div
                                        class="icon icon-shape icon-sm rounded-circle bg-gradient-success me-3 d-flex align-items-center justify-content-center">
                                        <i class="fas fa-check text-white text-xs"></i>
                                    </div>
                                    <div class="d-flex flex-column">
                                        <h6 class="mb-1 text-dark text-sm">{{ __('Periodic updates') }}</h6>
                                        <span
                                            class="text-xs">{{ __('Change your password regularly, at least every 3 months.') }}</span>
                                    </div>
                                </div>
                            </li>
                            <li class="list-group-item border-0 ps-0">
                                <div class="d-flex">
                                    <div
                                        class="icon icon-shape icon-sm rounded-circle bg-gradient-success me-3 d-flex align-items-center justify-content-center">
                                        <i class="fas fa-check text-white text-xs"></i>
                                    </div>
                                    <div class="d-flex flex-column">
                                        <h6 class="mb-1 text-dark text-sm">{{ __('Password manager') }}</h6>
                                        <span
                                            class="text-xs">{{ __('Consider using a password manager to generate and store complex passwords.') }}</span>
                                    </div>
                                </div>
                            </li>
                        </ul>
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
            margin-top: 8px;
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

        /* Password Requirements */
        .password-requirements ul {
            list-style-type: none;
            padding-left: 0;
        }

        .password-requirements li {
            position: relative;
            padding-left: 20px;
            margin-bottom: 4px;
            color: #8392AB;
        }

        .password-requirements li::before {
            content: "\f00d";
            font-family: "Font Awesome 5 Free";
            font-weight: 900;
            position: absolute;
            left: 0;
            color: #f5365c;
        }

        .password-requirements li.valid {
            color: #344767;
        }

        .password-requirements li.valid::before {
            content: "\f00c";
            color: #2dce89;
        }

        /* Match Status */
        .match-status {
            font-size: 0.75rem;
        }

        .match-status.matched {
            color: #2dce89;
        }

        .match-status.not-matched {
            color: #f5365c;
        }

        /* Buttons */
        .btn-primary {
            background: linear-gradient(310deg, #5e72e4 0%, #825ee4 100%);
            border: none;
            box-shadow: 0 3px 5px -1px rgba(94, 114, 228, 0.2), 0 2px 3px -1px rgba(94, 114, 228, 0.1);
        }

        .btn-outline-secondary {
            border-color: #8392AB;
            color: #8392AB;
        }

        .btn-outline-secondary:hover {
            background-color: #8392AB;
            color: #fff;
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

        /* List Group */
        .list-group-item {
            border-color: rgba(0, 0, 0, 0.05);
            padding: 1rem 0;
        }

        .icon-shape {
            width: 32px;
            height: 32px;
        }

        /* Alert */
        .alert-info {
            background-color: rgba(17, 113, 239, 0.1);
            color: #1171ef;
            border: none;
        }
    </style>
@endpush

@push('js')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Form submission from save button
            const saveButton = document.getElementById('save-password');
            const passwordForm = document.getElementById('password-form');

            if (saveButton && passwordForm) {
                saveButton.addEventListener('click', function () {
                    passwordForm.submit();
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
            const strengthMeter = document.querySelector('.password-strength-meter');
            const strengthText = document.querySelector('.password-strength-text');
            const confirmInput = document.getElementById('password_confirmation');
            const matchStatus = document.querySelector('.match-status');

            // Password requirements
            const reqLength = document.querySelector('.req-length');
            const reqUppercase = document.querySelector('.req-uppercase');
            const reqLowercase = document.querySelector('.req-lowercase');
            const reqNumber = document.querySelector('.req-number');
            const reqSpecial = document.querySelector('.req-special');

            if (passwordInput && strengthMeter && strengthText) {
                passwordInput.addEventListener('input', function () {
                    const password = this.value;

                    // Check requirements
                    const hasLength = password.length >= 8;
                    const hasUppercase = /[A-Z]/.test(password);
                    const hasLowercase = /[a-z]/.test(password);
                    const hasNumber = /[0-9]/.test(password);
                    const hasSpecial = /[^A-Za-z0-9]/.test(password);

                    // Update requirement list
                    toggleRequirement(reqLength, hasLength);
                    toggleRequirement(reqUppercase, hasUppercase);
                    toggleRequirement(reqLowercase, hasLowercase);
                    toggleRequirement(reqNumber, hasNumber);
                    toggleRequirement(reqSpecial, hasSpecial);

                    // Calculate strength
                    let strength = 0;
                    let feedback = '';

                    // Length checks
                    if (hasLength) strength += 25;
                    if (password.length >= 12) strength += 15;

                    // Character type checks
                    if (hasUppercase) strength += 10;
                    if (hasLowercase) strength += 10;
                    if (hasNumber) strength += 10;
                    if (hasSpecial) strength += 15;

                    // Variety of characters
                    if (password.length > 0) {
                        const uniqueChars = new Set(password.split('')).size;
                        const uniqueRatio = uniqueChars / password.length;
                        strength += Math.round(uniqueRatio * 15);
                    }

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
                        feedback = 'Medium strength. Consider adding more complexity.';
                    } else {
                        strengthMeter.classList.add('bg-success');
                        feedback = 'Strong password. Good job!';
                    }

                    strengthText.textContent = feedback;

                    // Check match if confirm field has content
                    if (confirmInput.value) {
                        checkPasswordMatch();
                    }
                });
            }

            // Password match checker
            if (confirmInput && matchStatus) {
                confirmInput.addEventListener('input', checkPasswordMatch);
            }

            function checkPasswordMatch() {
                const password = passwordInput.value;
                const confirmPassword = confirmInput.value;

                if (!confirmPassword) {
                    matchStatus.textContent = '';
                    matchStatus.classList.remove('matched', 'not-matched');
                    return;
                }

                if (password === confirmPassword) {
                    matchStatus.textContent = 'Passwords match';
                    matchStatus.classList.add('matched');
                    matchStatus.classList.remove('not-matched');
                } else {
                    matchStatus.textContent = 'Passwords do not match';
                    matchStatus.classList.add('not-matched');
                    matchStatus.classList.remove('matched');
                }
            }

            function toggleRequirement(element, isValid) {
                if (isValid) {
                    element.classList.add('valid');
                } else {
                    element.classList.remove('valid');
                }
            }
        });
    </script>
@endpush