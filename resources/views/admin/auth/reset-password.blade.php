@extends('admin.layouts.guest')

@push('css')
    <style>
        :root {
            --brand-primary: #2D3FE0;
            --brand-secondary: #131B4D;
            --brand-accent: #FF7D3B;
            --surface-light: #ffffff;
            --surface-dark: #131B4D;
            --text-primary: #25265E;
            --text-secondary: #5F6188;
            --text-tertiary: #8A8BB3;
            --text-on-dark: #ffffff;
            --success: #34D399;
            --warning: #FBBF24;
            --error: #EF4444;
            --surface-1: #ffffff;
            --surface-2: #F7F8FB;
            --surface-3: #EBEDF5;
            --border: #E2E5F1;
            --shadow-xs: 0 1px 2px rgba(13, 16, 45, 0.06);
            --shadow-sm: 0 1px 3px rgba(13, 16, 45, 0.1), 0 1px 2px rgba(13, 16, 45, 0.06);
            --shadow-md: 0 4px 6px -1px rgba(13, 16, 45, 0.1), 0 2px 4px -1px rgba(13, 16, 45, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(13, 16, 45, 0.1), 0 4px 6px -2px rgba(13, 16, 45, 0.05);
            --shadow-xl: 0 20px 25px -5px rgba(13, 16, 45, 0.1), 0 10px 10px -5px rgba(13, 16, 45, 0.04);
            --font-sans: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            --transition-fast: 150ms cubic-bezier(0.4, 0, 0.2, 1);
            --transition-normal: 300ms cubic-bezier(0.4, 0, 0.2, 1);
            --transition-slow: 500ms cubic-bezier(0.4, 0, 0.2, 1);
            --container-max: 1440px;
            --container-padding: 1.5rem;
            --radius-sm: 0.375rem;
            --radius-md: 0.5rem;
            --radius-lg: 0.75rem;
            --radius-xl: 1rem;
            --radius-2xl: 1.5rem;
            --radius-full: 9999px;
        }

        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap');

        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html,
        body {
            height: 100%;
        }

        body {
            font-family: var(--font-sans);
            font-size: 16px;
            line-height: 1.5;
            color: var(--text-primary);
            background-color: var(--surface-2);
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        .reset-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: var(--surface-2);
            padding: 2rem 1rem;
        }

        .reset-bg-decoration {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            pointer-events: none;
            z-index: 0;
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%232D3FE0' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            opacity: 0.5;
        }

        .reset-container {
            width: 100%;
            max-width: 440px;
            position: relative;
            z-index: 1;
        }

        .reset-card {
            background-color: var(--surface-1);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-xl);
            overflow: hidden;
            position: relative;
            animation: cardEntrance var(--transition-slow) forwards;
        }

        @keyframes cardEntrance {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .reset-header {
            padding: 3rem 2.5rem 2rem;
            text-align: center;
            position: relative;
        }

        .reset-header::after {
            content: "";
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: linear-gradient(to right, var(--brand-primary), #5468FF);
            border-radius: var(--radius-full);
        }

        .reset-header img {
            height: 50px;
            width: auto;
            margin-bottom: 1.25rem;
        }

        .reset-header h4 {
            color: var(--text-primary);
            font-weight: 700;
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }

        .reset-body {
            padding: 2rem 2.5rem 3rem;
        }

        .form-group {
            margin-bottom: 1.75rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            font-size: 0.875rem;
            color: var(--text-primary);
        }

        .form-control-wrapper {
            position: relative;
            margin-bottom: 1rem;
        }

        .form-control {
            width: 100%;
            padding: 0.875rem 1rem 0.875rem 3rem;
            font-size: 1rem;
            border-radius: var(--radius-lg);
            border: 1px solid var(--border);
            background-color: var(--surface-2);
            color: var(--text-primary);
            box-shadow: var(--shadow-xs);
            transition: all var(--transition-normal);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--brand-primary);
            box-shadow: 0 0 0 4px rgba(45, 63, 224, 0.1);
            background-color: var(--surface-1);
        }

        .form-control:read-only {
            background-color: var(--surface-3);
            cursor: not-allowed;
        }

        .form-control.is-invalid {
            border-color: var(--error);
        }

        .form-control::placeholder {
            color: var(--text-tertiary);
        }

        .form-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-tertiary);
            pointer-events: none;
            transition: color var(--transition-normal);
        }

        .form-control:focus+.form-icon {
            color: var(--brand-primary);
        }

        .password-toggle {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--text-tertiary);
            cursor: pointer;
            transition: color var(--transition-normal);
            width: 2rem;
            height: 2rem;
            border-radius: var(--radius-full);
        }

        .password-toggle:hover {
            color: var(--text-primary);
            background-color: var(--surface-3);
        }

        .password-strength {
            height: 6px;
            margin-top: 0.5rem;
            border-radius: var(--radius-full);
            background-color: var(--surface-3);
            overflow: hidden;
        }

        .password-strength-meter {
            height: 100%;
            width: 0;
            border-radius: var(--radius-full);
            transition: width 0.3s ease, background-color 0.3s ease;
        }

        .strength-weak {
            background-color: var(--error);
        }

        .strength-medium {
            background-color: var(--warning);
        }

        .strength-strong {
            background-color: var(--success);
        }

        .password-feedback {
            font-size: 0.75rem;
            margin-top: 0.5rem;
            color: var(--text-tertiary);
            text-align: right;
        }

        .feedback-weak {
            color: var(--error);
        }

        .feedback-medium {
            color: var(--warning);
        }

        .feedback-strong {
            color: var(--success);
        }

        .btn-primary {
            display: block;
            width: 100%;
            padding: 0.9375rem 1.5rem;
            font-size: 1rem;
            font-weight: 600;
            text-align: center;
            border: none;
            border-radius: var(--radius-lg);
            background: linear-gradient(to right, var(--brand-primary), #5468FF);
            color: var(--text-on-dark);
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(45, 63, 224, 0.25);
            transition: all var(--transition-normal);
            position: relative;
            overflow: hidden;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(45, 63, 224, 0.3);
        }

        .btn-primary:active {
            transform: translateY(0);
            box-shadow: 0 2px 8px rgba(45, 63, 224, 0.2);
        }

        .btn-primary span {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn-primary i {
            margin-right: 0.625rem;
        }

        .text-danger {
            display: block;
            color: var(--error);
            font-size: 0.8125rem;
            margin-top: 0.5rem;
        }

        .form-processing .processing-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(255, 255, 255, 0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10;
            border-radius: var(--radius-xl);
            opacity: 0;
            visibility: hidden;
            transition: opacity var(--transition-normal), visibility var(--transition-normal);
        }

        .form-processing.processing .processing-overlay {
            opacity: 1;
            visibility: visible;
        }

        .spinner {
            width: 40px;
            height: 40px;
            border: 3px solid rgba(45, 63, 224, 0.2);
            border-radius: 50%;
            border-top-color: var(--brand-primary);
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        @media (max-width: 576px) {
            .reset-header {
                padding: 2rem 1.5rem 1.5rem;
            }

            .reset-body {
                padding: 1.5rem 1.5rem 2rem;
            }

            .form-control {
                padding: 0.75rem 1rem 0.75rem 2.75rem;
            }
        }
    </style>
@endpush

@section('content')
    <div class="reset-page">
        <div class="reset-bg-decoration"></div>
        <div class="reset-container">
            <div class="reset-card form-processing">
                <div class="processing-overlay">
                    <div class="spinner"></div>
                </div>
                <div class="reset-header">
                    <img src="{{ asset('img/logo.png') }}" alt="Logo">
                    <h4>Create New Password</h4>
                </div>
                <div class="reset-body">
                    <form id="resetPasswordForm" action="{{ route('admin.password.update') }}" method="POST">
                        @csrf
                        <input type="hidden" name="token" value="{{ $token }}">
                        <div class="form-group">
                            <label for="email" class="form-label">Email Address</label>
                            <div class="form-control-wrapper">
                                <input type="email" class="form-control @error('email') is-invalid @enderror" id="email"
                                    name="email" value="{{ $email ?? old('email') }}" readonly required>
                                <i class="fas fa-envelope form-icon"></i>
                            </div>
                            @error('email')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="password" class="form-label">New Password</label>
                            <div class="form-control-wrapper">
                                <input type="password" class="form-control @error('password') is-invalid @enderror"
                                    id="password" name="password" placeholder="Enter new password" required>
                                <i class="fas fa-lock form-icon"></i>
                                <button type="button" class="password-toggle"><i class="fas fa-eye"></i></button>
                            </div>
                            <div class="password-strength">
                                <div class="password-strength-meter"></div>
                            </div>
                            <p class="password-feedback"></p>
                            @error('password')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                            <div class="text-danger" id="passwordError"></div>
                        </div>
                        <div class="form-group">
                            <label for="password_confirmation" class="form-label">Confirm Password</label>
                            <div class="form-control-wrapper">
                                <input type="password" class="form-control" id="password_confirmation"
                                    name="password_confirmation" placeholder="Confirm new password" required>
                                <i class="fas fa-lock-open form-icon"></i>
                                <button type="button" class="password-toggle"><i class="fas fa-eye"></i></button>
                            </div>
                            <div class="text-danger" id="passwordConfirmationError"></div>
                        </div>
                        <button type="submit" class="btn btn-primary" id="submitButton">
                            <span>
                                <i class="fas fa-key"></i>
                                Reset Password
                            </span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('resetPasswordForm');
            const passwordInput = document.getElementById('password');
            const confirmInput = document.getElementById('password_confirmation');
            const meter = document.querySelector('.password-strength-meter');
            const feedback = document.querySelector('.password-feedback');
            const passwordError = document.getElementById('passwordError');
            const confirmError = document.getElementById('passwordConfirmationError');
            const processingOverlay = document.querySelector('.processing-overlay');
            const submitButton = document.getElementById('submitButton');

            // Password visibility toggle
            document.querySelectorAll('.password-toggle').forEach(button => {
                button.addEventListener('click', function () {
                    const input = this.previousElementSibling.previousElementSibling;
                    const type = input.type === 'password' ? 'text' : 'password';
                    input.type = type;
                    const icon = this.querySelector('i');
                    icon.classList.toggle('fa-eye');
                    icon.classList.toggle('fa-eye-slash');
                });
            });

            // Real-time password strength validation
            passwordInput.addEventListener('input', function () {
                const password = this.value;
                passwordError.textContent = '';
                meter.classList.remove('strength-weak', 'strength-medium', 'strength-strong');
                feedback.classList.remove('feedback-weak', 'feedback-medium', 'feedback-strong');

                if (!password) {
                    meter.style.width = '0';
                    feedback.textContent = '';
                    return;
                }

                fetch('/admin/validate-password', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ password })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            meter.style.width = data.strength + '%';
                            meter.classList.add(`strength-${data.feedback.level}`);
                            feedback.classList.add(`feedback-${data.feedback.level}`);
                            feedback.textContent = data.feedback.message;
                        } else if (data.errors && data.errors.password) {
                            meter.style.width = '30%';
                            meter.classList.add('strength-weak');
                            feedback.classList.add('feedback-weak');
                            passwordError.textContent = data.errors.password[0];
                        }
                    })
                    .catch(error => {
                        console.error('Error validating password:', error);
                        passwordError.textContent = 'Error validating password';
                    });

                // Check confirmation match
                if (confirmInput.value) {
                    validatePasswordMatch();
                }
            });

            // Password confirmation validation
            function validatePasswordMatch() {
                confirmError.textContent = '';
                if (confirmInput.value && passwordInput.value !== confirmInput.value) {
                    confirmInput.classList.add('is-invalid');
                    confirmError.textContent = 'Passwords do not match';
                } else {
                    confirmInput.classList.remove('is-invalid');
                    confirmError.textContent = '';
                }
            }

            confirmInput.addEventListener('input', validatePasswordMatch);

            // Form submission
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                passwordError.textContent = '';
                confirmError.textContent = '';

                if (passwordInput.value !== confirmInput.value) {
                    confirmInput.classList.add('is-invalid');
                    confirmError.textContent = 'Passwords do not match';
                    return;
                }

                processingOverlay.parentElement.classList.add('processing');
                submitButton.disabled = true;

                const formData = new FormData(form);
                fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        processingOverlay.parentElement.classList.remove('processing');
                        submitButton.disabled = false;

                        if (data.success) {
                            window.location.href = data.redirect;
                        } else if (data.errors) {
                            Object.entries(data.errors).forEach(([field, messages]) => {
                                const input = document.querySelector(`[name="${field}"]`);
                                const errorElement = document.getElementById(`${field}Error`);
                                if (input && errorElement) {
                                    input.classList.add('is-invalid');
                                    errorElement.textContent = messages[0];
                                }
                            });
                        }
                    })
                    .catch(error => {
                        processingOverlay.parentElement.classList.remove('processing');
                        submitButton.disabled = false;
                        passwordError.textContent = 'An error occurred. Please try again.';
                        console.error('Error:', error);
                    });
            });
        });
    </script>
@endpush