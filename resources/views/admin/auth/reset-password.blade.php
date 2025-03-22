@extends('admin.layouts.guest')
@push('css')
    <style>
        /* Premium Reset Password Design */
        :root {
            /* Color system */
            --brand-primary: #2D3FE0;
            /* Royal blue */
            --brand-secondary: #131B4D;
            /* Dark blue */
            --brand-accent: #FF7D3B;
            /* Coral orange - used sparingly */
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

            /* Elevation system */
            --shadow-xs: 0 1px 2px rgba(13, 16, 45, 0.06);
            --shadow-sm: 0 1px 3px rgba(13, 16, 45, 0.1), 0 1px 2px rgba(13, 16, 45, 0.06);
            --shadow-md: 0 4px 6px -1px rgba(13, 16, 45, 0.1), 0 2px 4px -1px rgba(13, 16, 45, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(13, 16, 45, 0.1), 0 4px 6px -2px rgba(13, 16, 45, 0.05);
            --shadow-xl: 0 20px 25px -5px rgba(13, 16, 45, 0.1), 0 10px 10px -5px rgba(13, 16, 45, 0.04);
            --shadow-2xl: 0 25px 50px -12px rgba(13, 16, 45, 0.25);
            --shadow-inner: inset 0 2px 4px 0 rgba(13, 16, 45, 0.06);

            /* Typography */
            --font-sans: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;

            /* Animation */
            --transition-fast: 150ms cubic-bezier(0.4, 0, 0.2, 1);
            --transition-normal: 300ms cubic-bezier(0.4, 0, 0.2, 1);
            --transition-slow: 500ms cubic-bezier(0.4, 0, 0.2, 1);

            /* Layout */
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

        /* Global reset */
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

        /* Reset password page specific styles */
        .reset-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: var(--surface-2);
            position: relative;
            overflow: hidden;
            padding: 2rem 1rem;
        }

        /* Background decoration */
        .reset-bg-decoration {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            pointer-events: none;
            z-index: 0;
        }

        .reset-bg-decoration::before {
            content: "";
            position: absolute;
            width: 100%;
            height: 100%;
            background-image:
                radial-gradient(circle at 15% 50%, rgba(45, 63, 224, 0.03) 0%, transparent 25%),
                radial-gradient(circle at 85% 30%, rgba(45, 63, 224, 0.02) 0%, transparent 30%);
        }

        .reset-bg-decoration::after {
            content: "";
            position: absolute;
            top: -100px;
            right: -100px;
            width: 400px;
            height: 400px;
            border-radius: var(--radius-full);
            background: linear-gradient(135deg, rgba(45, 63, 224, 0.03), rgba(255, 125, 59, 0.03));
            filter: blur(60px);
            opacity: 0.7;
        }

        .reset-pattern {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%232D3FE0' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            opacity: 0.5;
        }

        /* Reset container */
        .reset-container {
            width: 100%;
            max-width: 440px;
            position: relative;
            z-index: 1;
        }

        /* Card styles */
        .reset-card {
            background-color: var(--surface-1);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-xl);
            overflow: hidden;
            border: none;
            position: relative;
            transform-style: preserve-3d;
            perspective: 1000px;
        }

        /* Card header */
        .reset-header {
            padding: 3rem 2.5rem 2rem;
            text-align: center;
            position: relative;
            z-index: 1;
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
            filter: drop-shadow(0 4px 6px rgba(45, 63, 224, 0.1));
        }

        .reset-header h4 {
            color: var(--text-primary);
            font-weight: 700;
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }

        /* Card body */
        .reset-body {
            padding: 2rem 2.5rem 3rem;
        }

        /* Form styles */
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
            line-height: 1.5;
            border-radius: var(--radius-lg);
            border: 1px solid var(--border);
            background-color: var(--surface-2);
            color: var(--text-primary);
            box-shadow: var(--shadow-xs);
            transition: all var(--transition-normal);
            margin-bottom: 0;
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
            display: flex;
            align-items: center;
            justify-content: center;
            width: 2rem;
            height: 2rem;
            border-radius: var(--radius-full);
        }

        .password-toggle:hover {
            color: var(--text-primary);
            background-color: var(--surface-3);
        }

        /* Password strength meter */
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
            margin-bottom: 0;
            color: var(--text-tertiary);
            text-align: right;
            transition: color 0.3s ease;
            height: 1rem;
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

        /* Submit button */
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

        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(to right, #5468FF, var(--brand-primary));
            opacity: 0;
            transition: opacity var(--transition-normal);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(45, 63, 224, 0.3);
        }

        .btn-primary:hover::before {
            opacity: 1;
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

        /* Error message */
        .text-danger {
            display: block;
            color: var(--error);
            font-size: 0.8125rem;
            margin-top: 0.5rem;
        }

        /* Card animation */
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

        .reset-card {
            animation: cardEntrance var(--transition-slow) forwards;
        }

        /* Responsive adjustments */
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
        <div class="reset-bg-decoration">
            <div class="reset-pattern"></div>
        </div>

        <div class="reset-container">
            <div class="reset-card">
                <div class="reset-header">
                    <img src="{{ asset('img/logo.png') }}" alt="BATI Car Rental">
                    <h4>Create New Password</h4>
                </div>
                <div class="reset-body">
                    <form method="POST" action="{{ route('admin.password.update') }}">
                        @csrf
                        <input type="hidden" name="token" value="{{ $token }}">

                        <div class="form-group">
                            <label for="email" class="form-label">Email Address</label>
                            <div class="form-control-wrapper">
                                <input type="email" class="form-control @error('email') is-invalid @enderror" id="email"
                                    name="email" value="{{ $email ?? old('email') }}" required readonly>
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
                                    id="password" name="password" required autofocus placeholder="Enter your new password">
                                <i class="fas fa-lock form-icon"></i>
                                <button type="button" class="password-toggle" tabindex="-1">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="password-strength">
                                <div class="password-strength-meter"></div>
                            </div>
                            <p class="password-feedback"></p>
                            @error('password')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="password_confirmation" class="form-label">Confirm Password</label>
                            <div class="form-control-wrapper">
                                <input type="password" class="form-control" id="password_confirmation"
                                    name="password_confirmation" required placeholder="Confirm your new password">
                                <i class="fas fa-lock-open form-icon"></i>
                                <button type="button" class="password-toggle" tabindex="-1">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">
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
        // Toggle password visibility
        document.querySelectorAll('.password-toggle').forEach(button => {
            button.addEventListener('click', function () {
                const passwordInput = this.previousElementSibling.previousElementSibling;
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);

                // Toggle eye icon
                this.querySelector('i').classList.toggle('fa-eye');
                this.querySelector('i').classList.toggle('fa-eye-slash');
            });
        });

        // Password strength meter
        document.getElementById('password').addEventListener('input', function () {
            const password = this.value;
            const meter = document.querySelector('.password-strength-meter');
            const feedback = document.querySelector('.password-feedback');

            // Remove existing classes
            meter.classList.remove('strength-weak', 'strength-medium', 'strength-strong');
            feedback.classList.remove('feedback-weak', 'feedback-medium', 'feedback-strong');

            if (password.length === 0) {
                meter.style.width = '0';
                feedback.textContent = '';
            } else if (password.length < 6) {
                meter.style.width = '30%';
                meter.classList.add('strength-weak');
                feedback.classList.add('feedback-weak');
                feedback.textContent = 'Weak password';
            } else if (password.length < 10 || !/[A-Z]/.test(password) || !/[0-9]/.test(password)) {
                meter.style.width = '60%';
                meter.classList.add('strength-medium');
                feedback.classList.add('feedback-medium');
                feedback.textContent = 'Medium password';
            } else {
                meter.style.width = '100%';
                meter.classList.add('strength-strong');
                feedback.classList.add('feedback-strong');
                feedback.textContent = 'Strong password';
            }
        });

        // Simple password confirmation check
        document.getElementById('password_confirmation').addEventListener('input', function () {
            const password = document.getElementById('password').value;
            const confirmation = this.value;

            if (confirmation && confirmation !== password) {
                this.classList.add('is-invalid');
            } else {
                this.classList.remove('is-invalid');
            }
        });
        document.addEventListener('DOMContentLoaded', function () {
            // Password visibility toggle handlers
            document.querySelectorAll('.password-toggle').forEach(button => {
                button.addEventListener('click', function () {
                    const passwordInput = this.previousElementSibling.previousElementSibling;
                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordInput.setAttribute('type', type);

                    // Toggle eye icon
                    this.querySelector('i').classList.toggle('fa-eye');
                    this.querySelector('i').classList.toggle('fa-eye-slash');
                });
            });

            // Password strength validation
            const passwordInput = document.getElementById('password');
            const confirmInput = document.getElementById('password_confirmation');
            const meter = document.querySelector('.password-strength-meter');
            const feedback = document.querySelector('.password-feedback');
            const form = document.querySelector('form');

            // Real-time password strength check
            passwordInput.addEventListener('input', function () {
                const password = this.value;

                if (password.length === 0) {
                    // Reset the meter and feedback
                    meter.style.width = '0';
                    meter.classList.remove('strength-weak', 'strength-medium', 'strength-strong');
                    feedback.classList.remove('feedback-weak', 'feedback-medium', 'feedback-strong');
                    feedback.textContent = '';
                    return;
                }

                // Make AJAX call to validate password
                fetch('/admin/validate-password', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        password: password
                    })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Update strength meter
                            meter.style.width = data.strength + '%';

                            // Clear existing classes
                            meter.classList.remove('strength-weak', 'strength-medium', 'strength-strong');
                            feedback.classList.remove('feedback-weak', 'feedback-medium', 'feedback-strong');

                            // Add appropriate class based on feedback level
                            const strengthClass = 'strength-' + data.feedback.level;
                            const feedbackClass = 'feedback-' + data.feedback.level;

                            meter.classList.add(strengthClass);
                            feedback.classList.add(feedbackClass);
                            feedback.textContent = data.feedback.message;
                        } else if (data.errors && data.errors.password) {
                            // Display error
                            meter.style.width = '30%';
                            meter.classList.add('strength-weak');
                            feedback.classList.add('feedback-weak');
                            feedback.textContent = data.errors.password[0];
                        }
                    })
                    .catch(error => {
                        console.error('Error validating password:', error);
                    });

                // Check if confirmation matches
                if (confirmInput.value) {
                    validatePasswordMatch();
                }
            });

            // Password confirmation match checking
            function validatePasswordMatch() {
                if (confirmInput.value && passwordInput.value !== confirmInput.value) {
                    confirmInput.classList.add('is-invalid');
                    const errorElement = confirmInput.parentElement.parentElement.querySelector('.text-danger');
                    if (!errorElement) {
                        const div = document.createElement('span');
                        div.className = 'text-danger';
                        div.textContent = 'Passwords do not match.';
                        confirmInput.parentElement.parentElement.appendChild(div);
                    }
                } else {
                    confirmInput.classList.remove('is-invalid');
                    const errorElement = confirmInput.parentElement.parentElement.querySelector('.text-danger');
                    if (errorElement) {
                        errorElement.remove();
                    }
                }
            }

            confirmInput.addEventListener('input', validatePasswordMatch);

            // Form submission with validation
            form.addEventListener('submit', function (e) {
                // Don't submit if passwords don't match
                if (passwordInput.value !== confirmInput.value) {
                    e.preventDefault();
                    validatePasswordMatch();
                    return false;
                }

                // Validate with AJAX before submitting
                e.preventDefault();

                const formData = new FormData(this);

                fetch(this.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // If validation passed, submit the form
                            form.removeEventListener('submit', arguments.callee);
                            form.submit();
                        } else if (data.errors) {
                            // Display errors
                            Object.keys(data.errors).forEach(field => {
                                const input = document.querySelector(`[name="${field}"]`);
                                if (input) {
                                    input.classList.add('is-invalid');

                                    // Remove any existing error message
                                    const existingError = input.parentElement.parentElement.querySelector('.text-danger');
                                    if (existingError) {
                                        existingError.remove();
                                    }

                                    // Add error message
                                    const errorElement = document.createElement('span');
                                    errorElement.className = 'text-danger';
                                    errorElement.textContent = data.errors[field][0];
                                    input.parentElement.parentElement.appendChild(errorElement);
                                }
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error submitting form:', error);
                    });
            });
        });
    </script>
@endpush