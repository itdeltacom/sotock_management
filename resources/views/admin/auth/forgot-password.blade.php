@extends('admin.layouts.guest')
@push('css')
    <style>
        /* Premium Forgot Password Design */
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

        /* Password reset page specific styles */
        .password-reset-page {
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
        .password-bg-decoration {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            pointer-events: none;
            z-index: 0;
        }

        .password-bg-decoration::before {
            content: "";
            position: absolute;
            width: 100%;
            height: 100%;
            background-image:
                radial-gradient(circle at 15% 50%, rgba(45, 63, 224, 0.03) 0%, transparent 25%),
                radial-gradient(circle at 85% 30%, rgba(45, 63, 224, 0.02) 0%, transparent 30%);
        }

        .password-bg-decoration::after {
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

        .password-pattern {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%232D3FE0' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            opacity: 0.5;
        }

        /* Password reset container */
        .password-container {
            width: 100%;
            max-width: 440px;
            position: relative;
            z-index: 1;
        }

        /* Card styles */
        .password-card {
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
        .password-header {
            padding: 3rem 2.5rem 2rem;
            text-align: center;
            position: relative;
            z-index: 1;
        }

        .password-header::after {
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

        .password-header img {
            height: 50px;
            width: auto;
            margin-bottom: 1.25rem;
            filter: drop-shadow(0 4px 6px rgba(45, 63, 224, 0.1));
        }

        .password-header h4 {
            color: var(--text-primary);
            font-weight: 700;
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }

        /* Card body */
        .password-body {
            padding: 2rem 2.5rem 3rem;
        }

        /* Instructions text */
        .text-muted {
            color: var(--text-secondary);
            margin-bottom: 1.75rem;
            font-size: 0.9375rem;
            text-align: center;
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
            margin-bottom: 1.75rem;
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

        /* Back to login */
        .back-to-login {
            text-align: center;
        }

        .back-link {
            color: var(--text-secondary);
            font-size: 0.9375rem;
            text-decoration: none;
            transition: color var(--transition-normal);
            position: relative;
            display: inline-flex;
            align-items: center;
        }

        .back-link i {
            margin-right: 0.5rem;
            font-size: 0.875rem;
        }

        .back-link::after {
            content: '';
            position: absolute;
            width: 100%;
            height: 1px;
            bottom: -2px;
            left: 0;
            background-color: var(--brand-primary);
            transform: scaleX(0);
            transform-origin: bottom right;
            transition: transform var(--transition-normal);
        }

        .back-link:hover {
            color: var(--brand-primary);
        }

        .back-link:hover::after {
            transform: scaleX(1);
            transform-origin: bottom left;
        }

        /* Alert message */
        .alert {
            padding: 1rem 1.25rem;
            border-radius: var(--radius-lg);
            margin-bottom: 1.75rem;
            display: flex;
            align-items: flex-start;
        }

        .alert-success {
            background-color: rgba(52, 211, 153, 0.08);
            border-left: 3px solid var(--success);
        }

        .alert i {
            margin-right: 0.75rem;
            font-size: 1.125rem;
        }

        .alert-success i {
            color: var(--success);
        }

        .alert-content {
            font-size: 0.9375rem;
        }

        .alert-success .alert-content {
            color: #065F46;
        }

        /* Error message */
        .error-feedback {
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

        .password-card {
            animation: cardEntrance var(--transition-slow) forwards;
        }

        /* Form processing overlay */
        .form-processing {
            position: relative;
        }

        .processing-overlay {
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
            backdrop-filter: blur(2px);
            opacity: 0;
            visibility: hidden;
            transition: opacity var(--transition-normal), visibility var(--transition-normal);
        }

        .processing-overlay.active {
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

        /* Responsive adjustments */
        @media (max-width: 576px) {
            .password-header {
                padding: 2rem 1.5rem 1.5rem;
            }

            .password-body {
                padding: 1.5rem 1.5rem 2rem;
            }

            .form-control {
                padding: 0.75rem 1rem 0.75rem 2.75rem;
            }
        }
    </style>
@endpush

@section('content')
    <div class="password-reset-page">
        <div class="password-bg-decoration">
            <div class="password-pattern"></div>
        </div>

        <div class="password-container">
            <div class="password-card form-processing">
                <div class="processing-overlay">
                    <div class="spinner"></div>
                </div>

                <div class="password-header">
                    <img src="{{ asset('img/logo.png') }}" alt="BATI Car Rental">
                    <h4>Reset Password</h4>
                </div>
                <div class="password-body">
                    <div id="alertContainer" style="display: none;">
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i>
                            <div class="alert-content" id="alertMessage"></div>
                        </div>
                    </div>

                    <p class="text-muted">
                        Enter your email address and we'll send you a link to reset your password.
                    </p>

                    <form id="forgotPasswordForm">
                        @csrf
                        <div class="form-group">
                            <label for="email" class="form-label">Email Address</label>
                            <div class="form-control-wrapper">
                                <input type="email" class="form-control" id="email" name="email"
                                    placeholder="Enter your email">
                                <i class="fas fa-envelope form-icon"></i>
                            </div>
                            <div class="error-feedback" id="emailError"></div>
                        </div>

                        <button type="submit" class="btn btn-primary" id="submitButton">
                            <span>
                                <i class="fas fa-paper-plane"></i>
                                Send Reset Link
                            </span>
                        </button>
                    </form>

                    <div class="back-to-login">
                        <a href="{{ route('admin.login') }}" class="back-link">
                            <i class="fas fa-arrow-left"></i> Back to Login
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const forgotPasswordForm = document.getElementById('forgotPasswordForm');
            const emailInput = document.getElementById('email');
            const emailError = document.getElementById('emailError');
            const processingOverlay = document.querySelector('.processing-overlay');
            const alertContainer = document.getElementById('alertContainer');
            const alertMessage = document.getElementById('alertMessage');

            // Initialize CSRF token for AJAX requests
            const csrfToken = document.querySelector('input[name="_token"]').value;

            // Real-time email validation
            emailInput.addEventListener('input', validateEmail);
            emailInput.addEventListener('blur', validateEmail);

            function validateEmail() {
                const email = emailInput.value.trim();
                emailError.textContent = '';

                if (email === '') {
                    emailError.textContent = 'Email is required';
                    return false;
                }

                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(email)) {
                    emailError.textContent = 'Please enter a valid email address';
                    return false;
                }

                // Check if email exists in the system (optional)
                return true;
            }

            // Form submission
            forgotPasswordForm.addEventListener('submit', function (e) {
                e.preventDefault();

                // Validate email
                if (!validateEmail()) {
                    return;
                }

                // Show processing overlay
                processingOverlay.classList.add('active');

                // Get form data
                const formData = new FormData(forgotPasswordForm);

                // Send AJAX request
                fetch('{{ route("admin.password.email") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        // Hide processing overlay
                        processingOverlay.classList.remove('active');

                        if (data.errors) {
                            // Display validation errors
                            if (data.errors.email) {
                                emailError.textContent = data.errors.email[0];
                            }
                        } else if (data.success) {
                            // Show success message
                            alertContainer.style.display = 'block';
                            alertMessage.textContent = data.message || 'We have emailed your password reset link!';

                            // Clear form
                            forgotPasswordForm.reset();

                            // Hide form and show only success message after short delay
                            setTimeout(function () {
                                document.querySelector('.text-muted').style.display = 'none';
                                forgotPasswordForm.style.display = 'none';
                            }, 1000);
                        }
                    })
                    .catch(error => {
                        console.error('Error sending reset link:', error);
                        processingOverlay.classList.remove('active');
                        emailError.textContent = 'An error occurred. Please try again.';
                    });
            });
        });
    </script>
@endpush