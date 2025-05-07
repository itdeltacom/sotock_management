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

        .password-reset-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: var(--surface-2);
            padding: 2rem 1rem;
        }

        .password-bg-decoration {
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

        .password-container {
            width: 100%;
            max-width: 440px;
            position: relative;
            z-index: 1;
        }

        .password-card {
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

        .password-header {
            padding: 3rem 2.5rem 2rem;
            text-align: center;
            position: relative;
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
        }

        .password-header h4 {
            color: var(--text-primary);
            font-weight: 700;
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }

        .password-body {
            padding: 2rem 2.5rem 3rem;
        }

        .text-muted {
            color: var(--text-secondary);
            margin-bottom: 1.75rem;
            font-size: 0.9375rem;
            text-align: center;
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

        .back-to-login {
            text-align: center;
        }

        .back-link {
            color: var(--text-secondary);
            font-size: 0.9375rem;
            text-decoration: none;
            transition: color var(--transition-normal);
        }

        .back-link:hover {
            color: var(--brand-primary);
        }

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
            color: var(--success);
        }

        .alert-content {
            font-size: 0.9375rem;
            color: #065F46;
        }

        .error-feedback {
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
        <div class="password-bg-decoration"></div>
        <div class="password-container">
            <div class="password-card form-processing">
                <div class="processing-overlay">
                    <div class="spinner"></div>
                </div>
                <div class="password-header">
                    <img src="{{ asset('img/logo.png') }}" alt="Logo">
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
                        Enter your email address or phone number to receive a password reset link.
                    </p>
                    <form id="forgotPasswordForm" action="{{ route('admin.password.email') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="login" class="form-label">Email or Phone Number</label>
                            <div class="form-control-wrapper">
                                <input type="text" class="form-control" id="login" name="login"
                                    placeholder="Enter email or phone number" required>
                                <i class="fas fa-user form-icon"></i>
                            </div>
                            <div class="error-feedback" id="loginError"></div>
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
            const form = document.getElementById('forgotPasswordForm');
            const loginInput = document.getElementById('login');
            const loginError = document.getElementById('loginError');
            const alertContainer = document.getElementById('alertContainer');
            const alertMessage = document.getElementById('alertMessage');
            const processingOverlay = document.querySelector('.processing-overlay');
            const submitButton = document.getElementById('submitButton');

            // Real-time validation
            loginInput.addEventListener('input', function () {
                loginError.textContent = '';
                if (!this.value.trim()) {
                    loginError.textContent = 'Email or phone number is required';
                } else if (!isValidEmail(this.value) && !isValidPhone(this.value)) {
                    loginError.textContent = 'Please enter a valid email or phone number';
                }
            });

            function isValidEmail(value) {
                return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
            }

            function isValidPhone(value) {
                return /^\+?[\d\s-]{7,}$/.test(value);
            }

            // Form submission
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                loginError.textContent = '';

                if (!loginInput.value.trim()) {
                    loginError.textContent = 'Email or phone number is required';
                    return;
                }

                if (!isValidEmail(loginInput.value) && !isValidPhone(loginInput.value)) {
                    loginError.textContent = 'Please enter a valid email or phone number';
                    return;
                }

                processingOverlay.parentElement.classList.add('processing');
                submitButton.disabled = true;

                const formData = new FormData(form);
                fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        processingOverlay.parentElement.classList.remove('processing');
                        submitButton.disabled = false;

                        if (data.success) {
                            alertContainer.style.display = 'block';
                            alertMessage.textContent = data.message;
                            form.style.display = 'none';
                            document.querySelector('.text-muted').style.display = 'none';
                            loginError.textContent = '';
                        } else if (data.errors) {
                            loginError.textContent = data.errors.login ? data.errors.login[0] : 'An error occurred';
                        }
                    })
                    .catch(error => {
                        processingOverlay.parentElement.classList.remove('processing');
                        submitButton.disabled = false;
                        loginError.textContent = 'An error occurred. Please try again.';
                        console.error('Error:', error);
                    });
            });
        });
    </script>
@endpush