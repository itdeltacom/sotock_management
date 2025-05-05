@extends('admin.layouts.guest')
@section('content')
    <main class="main-content mt-0">
        <section>
            <div class="page-header min-vh-100">
                <div class="container">
                    <div class="row">
                        <div class="col-xl-4 col-lg-5 col-md-7 d-flex flex-column mx-lg-0 mx-auto">
                            <div class="card card-plain">
                                <div class="card-header pb-0 text-start">
                                    <h4 class="font-weight-bolder">Sign In</h4>
                                    <p class="mb-0">Enter your email or phone number and password to sign in</p>
                                </div>
                                <div class="card-body">
                                    <form method="POST" action="{{ route('admin.login.submit') }}" id="login-form">
                                        @csrf
                                        <div class="mb-3">
                                            <input id="login" type="text"
                                                class="form-control form-control-lg @error('login') is-invalid @enderror"
                                                name="login" value="{{ old('login') }}" required autocomplete="email"
                                                autofocus placeholder="Email or Phone Number">

                                            @error('login')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                            <div class="invalid-feedback" id="login-error"></div>
                                        </div>
                                        <div class="mb-3">
                                            <input id="password" type="password"
                                                class="form-control form-control-lg @error('password') is-invalid @enderror"
                                                name="password" required autocomplete="current-password"
                                                placeholder="Password">

                                            @error('password')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                            <div class="invalid-feedback" id="password-error"></div>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="rememberMe" name="remember"
                                                {{ old('remember') ? 'checked' : '' }}>
                                            <label class="form-check-label" for="rememberMe">Remember me</label>
                                        </div>
                                        <div class="text-center">
                                            <button type="submit" class="btn btn-lg btn-primary btn-lg w-100 mt-4 mb-0"
                                                id="login-btn">
                                                Sign in
                                            </button>
                                        </div>
                                    </form>
                                </div>
                                <div class="card-footer text-center pt-0 px-lg-2 px-1">
                                    <p class="mb-1 text-sm mx-auto">
                                        Forgot password?
                                        <a href="{{ route('admin.password.request') }}"
                                            class="text-primary text-gradient font-weight-bold">
                                            Reset Password
                                        </a>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div
                            class="col-6 d-lg-flex d-none h-100 my-auto pe-0 position-absolute top-0 end-0 text-center justify-content-center flex-column">
                            <div class="position-relative bg-gradient-primary h-100 m-3 px-7 border-radius-lg d-flex flex-column justify-content-center overflow-hidden"
                                style="background-image: url('{{ asset('admin/assets/img/signin-bg.png') }}');
                                          background-size: cover;">
                                <span class="mask bg-gradient-primary opacity-6"></span>
                                <h4 class="mt-5 text-white font-weight-bolder position-relative">Welcome to IT Delta Com
                                    Admin Panel</h4>
                                <p class="text-white position-relative">Sign in to manage your resources and settings.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
@endsection

@push('js')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <script>
        $(document).ready(function () {
            // Configure Toastr
            toastr.options = {
                "closeButton": true,
                "progressBar": true,
                "positionClass": "toast-top-right",
                "showDuration": "300",
                "hideDuration": "1000",
                "timeOut": "5000",
                "extendedTimeOut": "1000",
                "showEasing": "swing",
                "hideEasing": "linear",
                "showMethod": "fadeIn",
                "hideMethod": "fadeOut"
            };

            // Show toastr messages from session
            @if(session('toastr'))
                toastr.{{ session('toastr.type') }}("{{ session('toastr.message') }}");
            @endif

            @if(session('status'))
                toastr.success("{{ session('status') }}");
            @endif

            @if ($errors->any())
                @foreach ($errors->all() as $error)
                    toastr.error("{{ $error }}");
                @endforeach
            @endif

            // AJAX form submission with validation
            $('#login-form').on('submit', function (e) {
                e.preventDefault();

                // Reset validation errors
                $('.is-invalid').removeClass('is-invalid');
                $('.invalid-feedback').hide();

                // Disable submit button and show loading state
                $('#login-btn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Signing in...');

                // For debugging
                console.log('Form action URL:', $(this).attr('action'));

                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: $(this).serialize(),
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (response) {
                        // For debugging
                        console.log('Success response:', response);

                        if (response.success) {
                            toastr.success(response.message || 'Login successful!');

                            // Add defensive coding to prevent undefined redirects
                            const redirectUrl = response.redirect || '/admin/dashboard';

                            // For debugging
                            console.log('Redirecting to:', redirectUrl);

                            // Redirect after showing toast
                            setTimeout(function () {
                                window.location.href = redirectUrl;
                            }, 1000);
                        } else {
                            // Handle authentication failure with HTTP success
                            $('#login-btn').prop('disabled', false).text('Sign in');

                            if (response.message) {
                                toastr.error(response.message);
                            } else if (response.errors) {
                                // Display validation errors
                                $.each(response.errors, function (field, messages) {
                                    $('#' + field).addClass('is-invalid');
                                    $('#' + field + '-error').text(messages[0]).show();
                                    toastr.error(messages[0]);
                                });
                            } else {
                                toastr.error('The provided credentials do not match our records.');
                            }
                        }
                    },
                    error: function (xhr) {
                        // For debugging
                        console.log('Error response:', xhr);

                        $('#login-btn').prop('disabled', false).text('Sign in');

                        if (xhr.status === 422) {
                            // Validation errors
                            var errors = xhr.responseJSON.errors;
                            if (errors) {
                                // Display validation errors
                                $.each(errors, function (field, messages) {
                                    $('#' + field).addClass('is-invalid');
                                    $('#' + field + '-error').text(messages[0]).show();
                                    toastr.error(messages[0]);
                                });
                            } else if (xhr.responseJSON.message) {
                                toastr.error(xhr.responseJSON.message);
                            }
                        } else if (xhr.status === 403) {
                            // Forbidden (e.g., account deactivated)
                            toastr.error(xhr.responseJSON.message);
                        } else {
                            // Other errors
                            toastr.error('An error occurred. Please try again.');
                        }
                    }
                });
            });

            // Clear validation state when starting to type
            $('input').on('input', function () {
                $(this).removeClass('is-invalid');
                $(this).siblings('.invalid-feedback').hide();
            });

            // Real-time validation for login field
            $('#login').on('blur', function () {
                if ($(this).val() === '') {
                    $(this).addClass('is-invalid');
                    $('#login-error').text('Please enter your email or phone number').show();
                }
            });

            // Real-time validation for password field
            $('#password').on('blur', function () {
                if ($(this).val() === '') {
                    $(this).addClass('is-invalid');
                    $('#password-error').text('Please enter your password').show();
                }
            });
        });
    </script>
@endpush