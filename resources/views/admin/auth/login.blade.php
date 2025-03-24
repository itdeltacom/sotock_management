@extends('admin.layouts.guest')
@push('css')
    <style>
        /* Premium Login Design */
        :root {
            /* Color system */
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

        /* Login page specific styles */
        .login-page {
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
        .login-bg-decoration {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            pointer-events: none;
            z-index: 0;
        }

        .login-bg-decoration::before {
            content: "";
            position: absolute;
            width: 100%;
            height: 100%;
            background-image:
                radial-gradient(circle at 15% 50%, rgba(45, 63, 224, 0.03) 0%, transparent 25%),
                radial-gradient(circle at 85% 30%, rgba(45, 63, 224, 0.02) 0%, transparent 30%);
        }

        .login-bg-decoration::after {
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

        .login-pattern {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%232D3FE0' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            opacity: 0.5;
        }

        /* Login container */
        .login-container {
            width: 100%;
            max-width: 440px;
            position: relative;
            z-index: 1;
        }

        /* Card styles */
        .login-card {
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
        .login-header {
            padding: 3rem 2.5rem 2rem;
            text-align: center;
            position: relative;
            z-index: 1;
        }

        .login-header::after {
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

        .login-header img {
            height: 50px;
            width: auto;
            margin-bottom: 1.25rem;
            filter: drop-shadow(0 4px 6px rgba(45, 63, 224, 0.1));
        }

        .login-header h4 {
            color: var(--text-primary);
            font-weight: 700;
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }

        .login-header p {
            color: var(--text-secondary);
            font-size: 0.95rem;
            max-width: 24rem;
            margin: 0 auto;
        }

        /* Card body */
        .login-body {
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

        /* Remember me checkbox */
        .form-check {
            display: flex;
            align-items: center;
            margin-bottom: 1.75rem;
        }

        .form-check-input {
            appearance: none;
            width: 1.25rem;
            height: 1.25rem;
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            margin-right: 0.75rem;
            background-color: var(--surface-2);
            cursor: pointer;
            position: relative;
            transition: all var(--transition-normal);
            flex-shrink: 0;
        }

        .form-check-input:checked {
            background-color: var(--brand-primary);
            border-color: var(--brand-primary);
        }

        .form-check-input:checked::after {
            content: "";
            position: absolute;
            top: 45%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(45deg);
            width: 0.3125rem;
            height: 0.625rem;
            border: solid white;
            border-width: 0 0.125rem 0.125rem 0;
        }

        .form-check-input:focus {
            box-shadow: 0 0 0 4px rgba(45, 63, 224, 0.1);
        }

        .form-check-label {
            font-size: 0.9375rem;
            color: var(--text-secondary);
            cursor: pointer;
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

        /* Forgot password */
        .forgot-password {
            text-align: center;
            margin-top: 1.75rem;
        }

        .forgot-link {
            color: var(--text-secondary);
            font-size: 0.9375rem;
            text-decoration: none;
            transition: color var(--transition-normal);
            position: relative;
            display: inline-block;
        }

        .forgot-link::after {
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

        .forgot-link:hover {
            color: var(--brand-primary);
        }

        .forgot-link:hover::after {
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

        .alert-danger {
            background-color: rgba(239, 68, 68, 0.08);
            border-left: 3px solid var(--error);
        }

        .alert i {
            margin-right: 0.75rem;
            font-size: 1.125rem;
            color: var(--error);
        }

        .alert-content {
            color: #991B1B;
            font-size: 0.9375rem;
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

        .login-card {
            animation: cardEntrance var(--transition-slow) forwards;
        }

        /* Responsive adjustments */
        @media (max-width: 576px) {
            .login-header {
                padding: 2rem 1.5rem 1.5rem;
            }

            .login-body {
                padding: 1.5rem 1.5rem 2rem;
            }

            .form-control {
                padding: 0.75rem 1rem 0.75rem 2.75rem;
            }
        }
    </style>
@endpush

@section('content')
    <div class="login-page">
        <div class="login-bg-decoration">
            <div class="login-pattern"></div>
        </div>

        <div class="login-container">
            <div class="login-card">
                <div class="login-header">
                    <img src="{{ asset('site/img/logo.png') }}" alt="BATI Car Rental">
                    <h4>Welcome Back</h4>
                    <p>Sign in to access your admin dashboard</p>
                </div>
                <div class="login-body">
                    @if(session('error'))
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle"></i>
                            <div class="alert-content">{{ session('error') }}</div>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.login') }}" id="loginForm">
                        @csrf
                        <div class="form-group">
                            <label for="email" class="form-label">Email Address</label>
                            <div class="form-control-wrapper">
                                <input type="email" class="form-control @error('email') is-invalid @enderror" id="email"
                                    name="email" value="{{ old('email') }}" placeholder="Enter your email" required
                                    autofocus>
                                <i class="fas fa-envelope form-icon"></i>
                            </div>
                            @error('email')
                                <span class="error-feedback">{{ $message }}</span>
                            @enderror
                            <div id="email-error" class="error-feedback" style="display: none;"></div>
                        </div>

                        <div class="form-group">
                            <label for="password" class="form-label">Password</label>
                            <div class="form-control-wrapper">
                                <input type="password" class="form-control @error('password') is-invalid @enderror"
                                    id="password" name="password" placeholder="Enter your password" required>
                                <i class="fas fa-lock form-icon"></i>
                                <button type="button" class="password-toggle" tabindex="-1">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            @error('password')
                                <span class="error-feedback">{{ $message }}</span>
                            @enderror
                            <div id="password-error" class="error-feedback" style="display: none;"></div>
                        </div>

                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }}>
                            <label class="form-check-label" for="remember">Remember me for 30 days</label>
                        </div>

                        <button type="submit" class="btn btn-primary" id="loginButton">
                            <span>
                                <i class="fas fa-sign-in-alt"></i>
                                Sign in to Dashboard
                            </span>
                        </button>
                    </form>

                    <div class="forgot-password">
                        <a href="{{ route('admin.password.request') }}" class="forgot-link">Forgot your password?</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Password visibility toggle
            const togglePassword = document.querySelector('.password-toggle');
            const passwordInput = document.querySelector('#password');

            togglePassword.addEventListener('click', function () {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);

                // Toggle eye icon
                this.querySelector('i').classList.toggle('fa-eye');
                this.querySelector('i').classList.toggle('fa-eye-slash');
            });

            // Real-time validation with AJAX
            const loginForm = document.getElementById('loginForm');
            const loginButton = document.getElementById('loginButton');
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('password');
            const emailError = document.getElementById('email-error');
            const passwordError = document.getElementById('password-error');

            loginForm.addEventListener('submit', function (e) {
                e.preventDefault();

                // Disable login button and show loading state
                loginButton.disabled = true;
                loginButton.innerHTML = '<span><i class="fas fa-spinner fa-spin"></i> Signing in...</span>';

                // Clear previous errors
                emailError.style.display = 'none';
                passwordError.style.display = 'none';

                // Get form data
                const formData = new FormData(loginForm);

                // AJAX validation before submitting
                fetch('{{ route('admin.login') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Form is valid, submit it normally
                            loginForm.submit();
                        } else if (data.errors) {
                            // Show validation errors
                            if (data.errors.email) {
                                emailError.textContent = data.errors.email[0];
                                emailError.style.display = 'block';
                            }

                            if (data.errors.password) {
                                passwordError.textContent = data.errors.password[0];
                                passwordError.style.display = 'block';
                            }

                            // Re-enable login button
                            loginButton.disabled = false;
                            loginButton.innerHTML = '<span><i class="fas fa-sign-in-alt"></i> Sign in to Dashboard</span>';
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);

                        // Re-enable login button
                        loginButton.disabled = false;
                        loginButton.innerHTML = '<span><i class="fas fa-sign-in-alt"></i> Sign in to Dashboard</span>';

                        // Show error toast with SweetAlert2
                        Swal.fire({
                            icon: 'error',
                            title: 'Connection Error',
                            text: 'Could not connect to the server. Please try again.',
                            position: 'top-end',
                            timer: 3000,
                            timerProgressBar: true,
                            toast: true,
                            showConfirmButton: false,
                            iconColor: '#EF4444',
                            customClass: {
                                popup: 'swal2-toast colored-toast',
                                title: 'swal2-title-error'
                            }
                        });
                    });
            });
        });
    </script>
@endpush