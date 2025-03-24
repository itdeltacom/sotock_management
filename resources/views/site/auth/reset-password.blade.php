@extends('site.layouts.app')

@section('title', 'Cental - Réinitialisation du mot de passe')
@section('meta_description', 'Créez un nouveau mot de passe pour votre compte Cental et accédez à nos services de location de voitures.')

@section('content')
    <!-- Header Start -->
    @include('site.includes.head')
    <!-- Header End -->

    <!-- Reset Password Start -->
    <div class="container-fluid py-5">
        <div class="container py-5">
            <div class="text-center mx-auto pb-5 wow fadeInUp" data-wow-delay="0.1s" style="max-width: 800px;">
                <h1 class="display-5 text-capitalize text-primary mb-3">Réinitialisation du mot de passe</h1>
                <p class="mb-0">Créez un nouveau mot de passe pour votre compte.</p>
            </div>
            <div class="row g-5 justify-content-center">
                <div class="col-lg-6 wow fadeInUp" data-wow-delay="0.1s">
                    <div class="bg-light rounded p-5">
                        <form id="resetPasswordForm">
                            @csrf
                            <input type="hidden" name="token" value="{{ $token }}">
                            <div class="row g-4">
                                <div class="col-12">
                                    <div class="form-floating">
                                        <input type="email" class="form-control" id="email" name="email"
                                            value="{{ $email }}" readonly>
                                        <label for="email">Votre Email</label>
                                        <div class="invalid-feedback" id="emailFeedback"></div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-floating">
                                        <input type="password" class="form-control" id="password" name="password"
                                            placeholder="Nouveau mot de passe">
                                        <label for="password">Nouveau mot de passe</label>
                                        <div class="invalid-feedback" id="passwordFeedback"></div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-floating">
                                        <input type="password" class="form-control" id="password_confirmation"
                                            name="password_confirmation" placeholder="Confirmer le mot de passe">
                                        <label for="password_confirmation">Confirmer le mot de passe</label>
                                        <div class="invalid-feedback" id="passwordConfirmationFeedback"></div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary w-100 py-3">Réinitialiser le mot de
                                        passe</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Reset Password End -->
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function () {
            // Reset password form submission
            $('#resetPasswordForm').submit(function (e) {
                e.preventDefault();

                // Reset validation
                $(this).find('.is-invalid').removeClass('is-invalid');

                $.ajax({
                    url: "{{ route('password.update') }}",
                    type: "POST",
                    data: $(this).serialize(),
                    success: function (response) {
                        if (response.status === 'success') {
                            Swal.fire({
                                position: 'top-end',
                                icon: 'success',
                                title: response.message,
                                showConfirmButton: false,
                                timer: 3000
                            });

                            setTimeout(function () {
                                window.location.href = response.redirect;
                            }, 3000);
                        } else {
                            Swal.fire({
                                position: 'top-end',
                                icon: 'error',
                                title: response.message,
                                showConfirmButton: false,
                                timer: 3000
                            });
                        }
                    },
                    error: function (xhr) {
                        const response = xhr.responseJSON;
                        if (response && response.errors) {
                            $.each(response.errors, function (key, value) {
                                $('#' + key).addClass('is-invalid');
                                $('#' + key + 'Feedback').text(value[0]);
                            });
                        }
                    }
                });
            });

            // Real-time validation
            $('#password, #password_confirmation').on('input', function () {
                $(this).removeClass('is-invalid');

                if ($(this).attr('id') === 'password' && $(this).val().length < 8) {
                    $(this).addClass('is-invalid');
                    $('#passwordFeedback').text('Le mot de passe doit contenir au moins 8 caractères');
                }

                if ($(this).attr('id') === 'password_confirmation' && $(this).val() !== $('#password').val()) {
                    $(this).addClass('is-invalid');
                    $('#passwordConfirmationFeedback').text('Les mots de passe ne correspondent pas');
                }
            });
        });
    </script>
@endpush