@extends('admin.layouts.guest')

@section('title', 'Verify Two-Factor Authentication')

@push('css')
    <style>
        .verification-form {
            max-width: 400px;
            margin: 100px auto;
        }

        .verification-card {
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            overflow: hidden;
            background-color: white;
        }

        .verification-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            padding: var(--spacing-xl) var(--spacing-lg);
            text-align: center;
        }

        .verification-header img {
            max-width: 200px;
            margin-bottom: var(--spacing-md);
        }

        .verification-header h4 {
            color: white;
            font-weight: 600;
            margin: 0;
            font-size: 1.5rem;
        }

        .verification-body {
            padding: var(--spacing-xl);
            background-color: white;
        }

        .verification-icon {
            font-size: 3rem;
            margin-bottom: var(--spacing-md);
            color: var(--secondary-color);
            text-align: center;
        }

        .code-inputs {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin: var(--spacing-lg) 0;
        }

        .code-input {
            width: 50px;
            height: 60px;
            text-align: center;
            font-size: 1.5rem;
            font-weight: 600;
            border: 2px solid var(--border-color);
            border-radius: var(--border-radius);
            background-color: var(--surface-2);
            transition: all 0.2s;
        }

        .code-input:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 2px rgba(0, 160, 227, 0.2);
            outline: none;
        }

        .verification-footer {
            text-align: center;
            padding: var(--spacing-md);
            background-color: rgba(0, 0, 0, 0.02);
            border-top: 1px solid var(--border-color);
        }
    </style>
@endpush

@section('content')
    <div class="verification-form">
        <div class="verification-card">
            <div class="verification-header">
                <img src="{{ asset('img/logo.png') }}" alt="BATI Car Rental">
                <h4>Two-Factor Verification</h4>
            </div>

            <div class="verification-body">
                <div class="verification-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>

                <p class="text-center">Please enter the verification code from your authenticator app to continue.</p>

                <form method="POST" action="{{ route('admin.two-factor.verify') }}" id="verificationForm">
                    @csrf

                    <div class="form-group">
                        <label for="code" class="form-label">Verification Code</label>
                        <div class="form-control-wrapper">
                            <input id="code" type="text" class="form-control @error('code') is-invalid @enderror"
                                name="code" required autocomplete="off" autofocus placeholder="Enter 6-digit code"
                                maxlength="6">
                            <i class="fas fa-key form-icon"></i>
                        </div>
                        @error('code')
                            <span class="error-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Alternative code input UI (visual digits) -->
                    <div class="code-inputs d-none">
                        <input type="text" class="code-input" maxlength="1" data-index="0">
                        <input type="text" class="code-input" maxlength="1" data-index="1">
                        <input type="text" class="code-input" maxlength="1" data-index="2">
                        <input type="text" class="code-input" maxlength="1" data-index="3">
                        <input type="text" class="code-input" maxlength="1" data-index="4">
                        <input type="text" class="code-input" maxlength="1" data-index="5">
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-arrow-right"></i> Verify and Continue
                    </button>
                </form>
            </div>

            <div class="verification-footer">
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="btn-link text-muted">
                        <i class="fas fa-sign-out-alt"></i> Log out
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        // Focus the code input field when the page loads
        window.onload = function () {
            document.getElementById('code').focus();
        };

        // For the alternative code input UI (currently hidden with d-none class)
        document.querySelectorAll('.code-input').forEach(function (input) {
            input.addEventListener('keyup', function (e) {
                const index = parseInt(this.getAttribute('data-index'));

                // Move to the next input field when a digit is entered
                if (this.value.length === 1 && index < 5) {
                    document.querySelector(`.code-input[data-index="${index + 1}"]`).focus();
                }

                // Handle backspace
                if (e.key === 'Backspace' && index > 0 && this.value.length === 0) {
                    document.querySelector(`.code-input[data-index="${index - 1}"]`).focus();
                }

                // Update the hidden input with all digits
                const code = Array.from(document.querySelectorAll('.code-input'))
                    .map(input => input.value)
                    .join('');

                document.getElementById('code').value = code;

                // Submit the form when all digits are entered
                if (code.length === 6) {
                    document.getElementById('verificationForm').submit();
                }
            });
        });
    </script>
@endpush