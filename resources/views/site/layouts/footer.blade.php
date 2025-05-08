<!-- Footer Start -->
<div class="container-fluid footer py-5 wow fadeIn" data-wow-delay="0.2s">
    <div class="container py-5">
        <div class="row g-5">
            <div class="col-md-6 col-lg-6 col-xl-3">
                <div class="footer-item d-flex flex-column">
                    <div class="footer-item">
                        <h4 class="text-white mb-4 montserrat">À Propos de Cental</h4>
                        <p class="mb-3 lato">Votre service de location de voitures de confiance à Casablanca, offrant
                            des véhicules de qualité et une expérience client exceptionnelle.</p>
                    </div>
                    <div class="position-relative">
                        <form id="newsletterForm">
                            @csrf
                            <div class="input-group">
                                <input class="form-control rounded-pill w-100 py-3 ps-4 pe-5" type="email" name="email"
                                    id="newsletter-email" placeholder="Entrez votre email" required>
                                <button type="submit"
                                    class="btn btn-secondary rounded-pill position-absolute top-0 end-0 py-2 mt-2 me-2"
                                    id="subscribeBtn">S'abonner</button>
                            </div>
                            <div class="invalid-feedback" id="newsletter-email-error"></div>
                            <div class="form-text text-white mt-1 lato">Abonnez-vous à notre newsletter pour des offres
                                exclusives et des mises à jour.</div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-6 col-xl-3">
                <div class="footer-item d-flex flex-column">
                    <h4 class="text-white mb-4 montserrat">Liens Rapides</h4>
                    <a href="{{ route('about') }}" class="lato"><i class="fas fa-angle-right me-2"></i> À Propos</a>
                    <a href="{{ route('cars.index') }}" class="lato"><i class="fas fa-angle-right me-2"></i> Nos
                        Voitures</a>
                    <a href="{{ route('categories.index') }}" class="lato"><i class="fas fa-angle-right me-2"></i>
                        Catégories de Voitures</a>
                    <a href="{{ route('blog.index') }}" class="lato"><i class="fas fa-angle-right me-2"></i> Blog</a>
                    <a href="{{ route('testimonials') }}" class="lato"><i class="fas fa-angle-right me-2"></i>
                        Témoignages</a>
                    <a href="{{ route('faq') }}" class="lato"><i class="fas fa-angle-right me-2"></i> FAQ</a>
                    <a href="{{ route('how-it-works') }}" class="lato"><i class="fas fa-angle-right me-2"></i> Comment
                        Ça Marche</a>
                    <a href="{{ route('policy') }}" class="lato"><i class="fas fa-angle-right me-2"></i> Conditions
                        Générales</a>
                    <a href="{{ route('privacy') }}" class="lato"><i class="fas fa-angle-right me-2"></i> Politique de
                        Confidentialité</a>
                    <a href="{{ route('contact') }}" class="lato"><i class="fas fa-angle-right me-2"></i>
                        Contactez-Nous</a>
                </div>
            </div>
            <div class="col-md-6 col-lg-6 col-xl-3">
                <div class="footer-item d-flex flex-column">
                    <h4 class="text-white mb-4 montserrat">Horaires d'Ouverture</h4>
                    <div class="mb-3">
                        <h6 class="text-muted mb-0 lato">Lundi - Vendredi :</h6>
                        <p class="text-white mb-0 lato">09h00 à 19h00</p>
                    </div>
                    <div class="mb-3">
                        <h6 class="text-muted mb-0 lato">Samedi :</h6>
                        <p class="text-white mb-0 lato">10h00 à 17h00</p>
                    </div>
                    <div class="mb-3">
                        <h6 class="text-muted mb-0 lato">Vacances :</h6>
                        <p class="text-white mb-0 lato">Fermé tous les dimanches</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-6 col-xl-3">
                <div class="footer-item d-flex flex-column">
                    <h4 class="text-white mb-4 montserrat">Informations de Contact</h4>
                    <a href="{{ route('contact') }}" class="lato"><i class="fa fa-map-marker-alt me-2"></i> Casablanca,
                        Maroc</a>
                    <a href="mailto:contact@cental.ma" class="lato"><i class="fas fa-envelope me-2"></i>
                        contact@cental.ma</a>
                    <a href="tel:+212522XXXXXX" class="lato"><i class="fas fa-phone me-2"></i> +212 5 22 XX XX XX</a>
                    <a href="https://wa.me/+2126XXXXXXXX" class="lato mb-3"><i class="fab fa-whatsapp me-2"></i> +212 6
                        XX XX XX XX</a>
                    <div class="d-flex">
                        <a class="btn btn-secondary btn-md-square rounded-circle me-3" href="#"><i
                                class="fab fa-facebook-f text-white"></i></a>
                        <a class="btn btn-secondary btn-md-square rounded-circle me-3" href="#"><i
                                class="fab fa-twitter text-white"></i></a>
                        <a class="btn btn-secondary btn-md-square rounded-circle me-3" href="#"><i
                                class="fab fa-instagram text-white"></i></a>
                        <a class="btn btn-secondary btn-md-square rounded-circle me-0" href="#"><i
                                class="fab fa-linkedin-in text-white"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Footer End -->

<!-- Copyright Start -->
<div class="container-fluid copyright py-4">
    <div class="container">
        <div class="row g-4 align-items-center">
            <div class="col-md-6 text-center text-md-start mb-md-0">
                <span class="text-body">
                    <a href="{{ route('home') }}" class="border-bottom text-white">
                        <i class="fas fa-copyright text-light me-2"></i>Cental Location de Voitures
                    </a>, Tous droits réservés.
                </span>
            </div>
            <div class="col-md-6 text-center text-md-end text-body">
                Conçu par <a class="border-bottom text-white" href="http://itdeltacom.com">It Delta Com</a>
            </div>
        </div>
    </div>
</div>
<!-- Copyright End -->

<!-- Back to Top -->
<a href="#" class="btn btn-secondary btn-lg-square rounded-circle back-to-top"><i class="fa fa-arrow-up"></i></a>

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function () {
            // Initialize email validation
            const emailRegex = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;

            // Real-time email validation
            $('#newsletter-email').on('input', function () {
                const email = $(this).val();
                const isValid = emailRegex.test(email);

                if (email && !isValid) {
                    $(this).addClass('is-invalid');
                    $('#newsletter-email-error').text('Veuillez entrer une adresse email valide').show();
                } else {
                    $(this).removeClass('is-invalid');
                    $('#newsletter-email-error').hide();
                }
            });

            // Form submission
            $('#newsletterForm').on('submit', function (e) {
                e.preventDefault();

                const email = $('#newsletter-email').val();

                // Validate email again
                if (!email || !emailRegex.test(email)) {
                    $('#newsletter-email').addClass('is-invalid');
                    $('#newsletter-email-error').text('Veuillez entrer une adresse email valide').show();
                    return false;
                }

                // Submit the subscription request
                $.ajax({
                    url: "{{ route('newsletter.subscribe') }}",
                    method: 'POST',
                    data: {
                        email: email,
                        _token: '{{ csrf_token() }}'
                    },
                    beforeSend: function () {
                        // Disable button and show loading
                        $('#subscribeBtn').prop('disabled', true)
                            .html('<i class="fas fa-spinner fa-spin"></i>');
                    },
                    success: function (response) {
                        // Reset form
                        $('#newsletterForm')[0].reset();

                        // Show success message
                        Swal.fire({
                            title: 'Succès !',
                            text: response.message,
                            icon: 'success',
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 5000,
                            timerProgressBar: true
                        });
                    },
                    error: function (xhr) {
                        if (xhr.status === 422) {
                            // Validation errors
                            const errors = xhr.responseJSON.errors;

                            if (errors.email) {
                                $('#newsletter-email').addClass('is-invalid');
                                $('#newsletter-email-error').text(errors.email[0]).show();
                            }
                        } else {
                            // Generic error
                            Swal.fire({
                                title: 'Erreur',
                                text: 'Échec de l\'abonnement. Veuillez réessayer plus tard.',
                                icon: 'error',
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 3000
                            });
                        }
                    },
                    complete: function () {
                        // Re-enable button
                        $('#subscribeBtn').prop('disabled', false)
                            .text('S\'abonner');
                    }
                });
            });
        });
    </script>
@endpush