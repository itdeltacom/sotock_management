@extends('site.layouts.app')

@section('title', 'Cental - Mot de passe oublié')
@section('meta_description', 'Réinitialisez votre mot de passe pour accéder à votre compte Cental et continuer à profiter de nos services de location de voitures.')

@section('content')
    <!-- Header Start -->
    @include('site.includes.head')
    <!-- Header End -->

    <!-- Password Reset Start -->
    <div class="container-fluid py-5">
        <div class="container py-5">
            <div class="text-center mx-auto pb-5 wow fadeInUp" data-wow-delay="0.1s" style="max-width: 800px;">
                <h1 class="display-5 text-capitalize text-primary mb-3">Mot de passe oublié</h1>
                <p class="mb-0">Entrez votre adresse e-mail et nous vous enverrons un lien pour réinitialiser votre mot de
                    passe.</p>
            </div>
            <div class="row g-5 justify-content-center">
                <div class="col-lg-6 wow fadeInUp" data-wow-delay="0.1s">
                    <div class="bg-light rounded p-5">
                        <form id="forgotPasswordForm">
                            @csrf
                            <div class="row g-4">
                                <div class="col-12">
                                    <div class="form-floating">
                                        <input type="email" class="form-control" id="email" name="email"
                                            placeholder="Votre Email">
                                        <label for="email">Votre Email</label>
                                        <div class="invalid-feedback" id="emailFeedback"></div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary w-100 py-3">Envoyer le lien de
                                        réinitialisation</button>
                                </div>
                                <div class="col-12 text-center">
                                    <a href="{{ route('login-register') }}" class="text-primary">Retour à la connexion</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Password Reset End -->
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function () {
            // Forgot password form submission
            $('#forgotPasswordForm').submit(function (e) {
                e.preventDefault();

                // Reset validation
                $(this).find('.is-invalid').removeClass('is-invalid');

                $.ajax({
                    url: "{{ route('password.email') }}",
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
            $('#email').on('input', function () {
                $(this).removeClass('is-invalid');

                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test($(this).val())) {
                    $(this).addClass('is-invalid');
                    $('#emailFeedback').text('Veuillez entrer une adresse email valide');
                }
            });
        });
    </script>
@endpush