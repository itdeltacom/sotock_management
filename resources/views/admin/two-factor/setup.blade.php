@extends('admin.layouts.master')

@section('title', 'Setup Two-Factor Authentication')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary">
                        <h5 class="card-title text-white">Setup Two-Factor Authentication</h5>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-4">
                            <div class="alert alert-info">
                                <div class="alert-icon">
                                    <i class="fas fa-info-circle"></i>
                                </div>
                                <div class="alert-content">
                                    <p>Two-factor authentication adds an extra layer of security to your account. Once
                                        enabled, you'll need to provide both your password and a verification code from your
                                        mobile phone to log in.</p>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="text-center mb-4">
                                    <h6 class="font-weight-bold">1. Scan this QR code with your authenticator app</h6>
                                    <p class="text-muted small">(Google Authenticator, Microsoft Authenticator, Authy, etc.)
                                    </p>
                                    <div class="qr-code-container p-3 bg-light rounded d-inline-block mt-2">
                                        {!! $qrCode !!}
                                    </div>
                                </div>

                                <div class="text-center mb-4">
                                    <h6>App not scanning correctly?</h6>
                                    <p class="text-muted small">Try manually entering the key below.</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-4">
                                    <h6 class="font-weight-bold">2. Enter the verification code from your app</h6>
                                    <form method="POST" action="{{ route('admin.two-factor.enable') }}">
                                        @csrf

                                        <div class="form-group">
                                            <label for="code" class="form-label">Verification Code</label>
                                            <div class="form-control-wrapper">
                                                <input id="code" type="text"
                                                    class="form-control @error('code') is-invalid @enderror" name="code"
                                                    required autocomplete="off" autofocus placeholder="Enter 6-digit code"
                                                    maxlength="6">
                                                <i class="fas fa-key form-icon"></i>
                                            </div>
                                            @error('code')
                                                <span class="error-feedback">{{ $message }}</span>
                                            @enderror
                                            <div class="form-text text-muted">
                                                Enter the 6-digit code that appears in your authenticator app.
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-check"></i> Verify & Enable
                                            </button>
                                            <a href="{{ route('admin.profile') }}" class="btn btn-outline-secondary">
                                                <i class="fas fa-times"></i> Cancel
                                            </a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="mt-5">
                            <h6 class="font-weight-bold">Manual Setup</h6>
                            <p>If you cannot scan the QR code, enter these details manually in your authenticator app:</p>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Account Name</label>
                                        <div class="form-control bg-light">
                                            {{ config('app.name') }}:{{ Auth::guard('admin')->user()->email }}</div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Service/Issuer</label>
                                        <div class="form-control bg-light">{{ config('app.name') }}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Secret Key (Base32)</label>
                                <div class="input-group">
                                    <input type="text" class="form-control bg-light" value="{{ $secret }}" readonly
                                        id="secretKey">
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary" type="button" onclick="copySecretKey()">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="form-text text-muted">
                                    Spaces don't matter. Most apps will automatically remove them.
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Type/Algorithm</label>
                                <div class="form-control bg-light">Time-based (TOTP) / SHA1</div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <div class="alert alert-warning">
                                <div class="alert-icon">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </div>
                                <div class="alert-content">
                                    <h6 class="font-weight-bold">Important</h6>
                                    <p>If you lose access to your authentication app, you may be locked out of your account.
                                        Make sure to save your recovery codes or keep backup access to your authenticator
                                        app.</p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <h6 class="font-weight-bold">Supported Apps</h6>
                            <div class="d-flex flex-wrap">
                                <div class="mr-4 mb-2 text-center">
                                    <i class="fab fa-google fa-2x mb-2"></i>
                                    <div>Google Authenticator</div>
                                </div>
                                <div class="mr-4 mb-2 text-center">
                                    <i class="fab fa-microsoft fa-2x mb-2"></i>
                                    <div>Microsoft Authenticator</div>
                                </div>
                                <div class="mr-4 mb-2 text-center">
                                    <i class="fas fa-mobile-alt fa-2x mb-2"></i>
                                    <div>Authy</div>
                                </div>
                                <div class="mr-4 mb-2 text-center">
                                    <i class="fas fa-shield-alt fa-2x mb-2"></i>
                                    <div>LastPass Authenticator</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        function copySecretKey() {
            const secretInput = document.getElementById('secretKey');
            secretInput.select();
            document.execCommand('copy');

            // Show notification
            showNotification('success', 'Secret key copied to clipboard!');
        }
    </script>
@endpush