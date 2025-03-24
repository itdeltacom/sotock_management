@extends('site.layouts.app')

@section('title', 'Cental - Connexion et Inscription')
@section('meta_description', 'Connectez-vous à votre compte Cental ou inscrivez-vous pour profiter de nos services de location de voitures à Casablanca et au Maroc.')

@section('content')
    <!-- Header Start -->
    @include('site.includes.head')
    <!-- Header End -->

    <!-- Auth Start -->
    <div class="container-fluid py-5">
        <div class="container py-5">
            <div class="text-center mx-auto pb-5 wow fadeInUp" data-wow-delay="0.1s" style="max-width: 800px;">
                <h1 class="display-5 text-capitalize text-primary mb-3">Mon Compte</h1>
                <p class="mb-0">Connectez-vous ou créez un compte pour gérer vos réservations de voiture et bénéficier
                    d'offres exclusives.</p>
            </div>
            <div class="row g-5 justify-content-center">
                <div class="col-lg-8 wow fadeInUp" data-wow-delay="0.1s">
                    <div class="bg-light rounded p-5">
                        <!-- Tabs -->
                        <ul class="nav nav-tabs mb-4" id="authTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="login-tab" data-bs-toggle="tab" data-bs-target="#login"
                                    type="button" role="tab" aria-controls="login" aria-selected="true">Connexion</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="register-tab" data-bs-toggle="tab" data-bs-target="#register"
                                    type="button" role="tab" aria-controls="register"
                                    aria-selected="false">Inscription</button>
                            </li>
                        </ul>

                        <!-- Tab content -->
                        <div class="tab-content" id="authTabContent">
                            <!-- Login Tab -->
                            <div class="tab-pane fade show active" id="login" role="tabpanel" aria-labelledby="login-tab">
                                <h4 class="text-primary mb-4">Connexion</h4>
                                <form id="loginForm">
                                    @csrf
                                    <div class="row g-4">
                                        <div class="col-12">
                                            <div class="form-floating">
                                                <input type="email" class="form-control" id="loginEmail" name="email"
                                                    placeholder="Votre Email">
                                                <label for="loginEmail">Votre Email</label>
                                                <div class="invalid-feedback" id="loginEmailFeedback"></div>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="form-floating">
                                                <input type="password" class="form-control" id="loginPassword"
                                                    name="password" placeholder="Mot de passe">
                                                <label for="loginPassword">Mot de passe</label>
                                                <div class="invalid-feedback" id="loginPasswordFeedback"></div>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="remember"
                                                    id="remember">
                                                <label class="form-check-label" for="remember">
                                                    Se souvenir de moi
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="d-flex justify-content-between">
                                                <button type="submit" class="btn btn-primary px-4 py-3">Connexion</button>
                                                <a href="{{ route('password.request') }}" class="text-primary pt-2">Mot de
                                                    passe oublié?</a>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                                <div class="d-flex flex-column align-items-center mt-4">
                                    <p class="mb-3">Ou connectez-vous avec</p>
                                    <div class="d-flex">
                                        <a href="{{ route('auth.google') }}" class="btn btn-light me-2">
                                            <i class="fab fa-google text-danger"></i> Google
                                        </a>
                                        <a href="{{ route('auth.facebook') }}" class="btn btn-light">
                                            <i class="fab fa-facebook text-primary"></i> Facebook
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Register Tab -->
                            <div class="tab-pane fade" id="register" role="tabpanel" aria-labelledby="register-tab">
                                <h4 class="text-primary mb-4">Inscription</h4>
                                <form id="registerForm" enctype="multipart/form-data">
                                    @csrf
                                    <div class="row g-4">
                                        <div class="col-12">
                                            <div class="form-floating">
                                                <input type="text" class="form-control" id="registerName" name="name"
                                                    placeholder="Votre Nom">
                                                <label for="registerName">Votre Nom</label>
                                                <div class="invalid-feedback" id="registerNameFeedback"></div>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="form-floating">
                                                <input type="email" class="form-control" id="registerEmail" name="email"
                                                    placeholder="Votre Email">
                                                <label for="registerEmail">Votre Email</label>
                                                <div class="invalid-feedback" id="registerEmailFeedback"></div>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="form-floating">
                                                <input type="tel" class="form-control" id="registerPhone" name="phone"
                                                    placeholder="Votre Téléphone">
                                                <label for="registerPhone">Votre Téléphone</label>
                                                <div class="invalid-feedback" id="registerPhoneFeedback"></div>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="form-floating">
                                                <input type="password" class="form-control" id="registerPassword"
                                                    name="password" placeholder="Mot de passe">
                                                <label for="registerPassword">Mot de passe</label>
                                                <div class="invalid-feedback" id="registerPasswordFeedback"></div>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="form-floating">
                                                <input type="password" class="form-control"
                                                    id="registerPasswordConfirmation" name="password_confirmation"
                                                    placeholder="Confirmer le mot de passe">
                                                <label for="registerPasswordConfirmation">Confirmer le mot de passe</label>
                                                <div class="invalid-feedback" id="registerPasswordConfirmationFeedback">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="mb-3">
                                                <label for="photo" class="form-label">Photo de profil (optionnel)</label>
                                                <input class="form-control" type="file" id="photo" name="photo">
                                                <div class="invalid-feedback" id="photoFeedback"></div>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <button type="submit" class="btn btn-primary w-100 py-3">S'inscrire</button>
                                        </div>
                                    </div>
                                </form>
                                <div class="d-flex flex-column align-items-center mt-4">
                                    <p class="mb-3">Ou inscrivez-vous avec</p>
                                    <div class="d-flex">
                                        <a href="{{ route('auth.google') }}" class="btn btn-light me-2">
                                            <i class="fab fa-google text-danger"></i> Google
                                        </a>
                                        <a href="{{ route('auth.facebook') }}" class="btn btn-light">
                                            <i class="fab fa-facebook text-primary"></i> Facebook
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Auth End -->
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function () {
            // Login form submission
            $('#loginForm').submit(function (e) {
                e.preventDefault();

                // Reset validation
                $(this).find('.is-invalid').removeClass('is-invalid');

                $.ajax({
                    url: "{{ route('login.submit') }}",
                    type: "POST",
                    data: $(this).serialize(),
                    success: function (response) {
                        if (response.status === 'success') {
                            Swal.fire({
                                position: 'top-end',
                                icon: 'success',
                                title: response.message,
                                showConfirmButton: false,
                                timer: 1500
                            });

                            setTimeout(function () {
                                window.location.href = response.redirect;
                            }, 1500);
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
                                $('#login' + key.charAt(0).toUpperCase() + key.slice(1)).addClass('is-invalid');
                                $('#login' + key.charAt(0).toUpperCase() + key.slice(1) + 'Feedback').text(value[0]);
                            });
                        }
                    }
                });
            });

            // Register form submission
            $('#registerForm').submit(function (e) {
                e.preventDefault();

                // Reset validation
                $(this).find('.is-invalid').removeClass('is-invalid');

                const formData = new FormData(this);

                $.ajax({
                    url: "{{ route('register.submit') }}",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        if (response.status === 'success') {
                            Swal.fire({
                                position: 'top-end',
                                icon: 'success',
                                title: response.message,
                                showConfirmButton: false,
                                timer: 1500
                            });

                            setTimeout(function () {
                                window.location.href = response.redirect;
                            }, 1500);
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
                                if (key === 'password_confirmation') {
                                    $('#registerPasswordConfirmation').addClass('is-invalid');
                                    $('#registerPasswordConfirmationFeedback').text(value[0]);
                                } else if (key === 'photo') {
                                    $('#photo').addClass('is-invalid');
                                    $('#photoFeedback').text(value[0]);
                                } else {
                                    $('#register' + key.charAt(0).toUpperCase() + key.slice(1)).addClass('is-invalid');
                                    $('#register' + key.charAt(0).toUpperCase() + key.slice(1) + 'Feedback').text(value[0]);
                                }
                            });
                        }
                    }
                });
            });

            // Real-time validation
            $('#registerName, #registerEmail, #registerPhone, #registerPassword, #registerPasswordConfirmation, #photo').on('input', function () {
                const id = $(this).attr('id');
                const value = $(this).val();

                // Remove invalid state when user starts typing again
                $(this).removeClass('is-invalid');

                // Basic validations
                if (id === 'registerName' && value.length < 3) {
                    $(this).addClass('is-invalid');
                    $('#registerNameFeedback').text('Le nom doit contenir au moins 3 caractères');
                }

                if (id === 'registerEmail') {
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test(value)) {
                        $(this).addClass('is-invalid');
                        $('#registerEmailFeedback').text('Veuillez entrer une adresse email valide');
                    }
                }

                if (id === 'registerPhone') {
                    const phoneRegex = /^[0-9+\s-]{8,15}$/;
                    if (value !== '' && !phoneRegex.test(value)) {
                        $(this).addClass('is-invalid');
                        $('#registerPhoneFeedback').text('Veuillez entrer un numéro de téléphone valide');
                    }
                }

                if (id === 'registerPassword' && value.length < 8) {
                    $(this).addClass('is-invalid');
                    $('#registerPasswordFeedback').text('Le mot de passe doit contenir au moins 8 caractères');
                }

                if (id === 'registerPasswordConfirmation' && value !== $('#registerPassword').val()) {
                    $(this).addClass('is-invalid');
                    $('#registerPasswordConfirmationFeedback').text('Les mots de passe ne correspondent pas');
                }

                if (id === 'photo') {
                    const file = this.files[0];
                    if (file) {
                        const fileType = file.type;
                        const validImageTypes = ['image/jpeg', 'image/png', 'image/jpg'];
                        if (!validImageTypes.includes(fileType)) {
                            $(this).addClass('is-invalid');
                            $('#photoFeedback').text('Seuls les formats JPEG, PNG et JPG sont acceptés');
                        } else if (file.size > 2 * 1024 * 1024) {
                            $(this).addClass('is-invalid');
                            $('#photoFeedback').text('La taille du fichier ne doit pas dépasser 2 Mo');
                        }
                    }
                }
            });

            // Also validate login fields
            $('#loginEmail, #loginPassword').on('input', function () {
                $(this).removeClass('is-invalid');
            });
        });
    </script>
@endpush