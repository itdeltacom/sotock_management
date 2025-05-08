@extends('site.layouts.app')

@section('title', 'Comment Ça Marche - Cental')
@section('meta_description', 'Découvrez comment louer une voiture avec Cental à Casablanca. Suivez notre processus simple en 5 étapes pour une expérience fluide et rapide.')
@section('meta_keywords', 'comment louer voiture Casablanca, processus location voiture, guide location Cental, étapes réservation voiture')

@section('og_title', 'Comment Ça Marche - Location de Voitures avec Cental')
@section('og_description', 'Apprenez comment réserver une voiture avec Cental en suivant notre guide simple en 5 étapes.')
@section('og_image', asset('site/img/carousel-1.jpg'))

@section('content')
    <!-- Dynamic Breadcrumb -->
    <div class="container-fluid bg-breadcrumb bg-secondary">
        <div class="container text-center py-5" style="max-width: 900px;">
            <h4 class="text-white display-4 mb-4 wow fadeInDown" data-wow-delay="0.1s">Comment Ça Marche</h4>
            <ol class="breadcrumb d-flex justify-content-center mb-0 wow fadeInDown" data-wow-delay="0.3s">
                <li class="breadcrumb-item">
                    <a href="{{ route('home') }}">Accueil</a>
                </li>
                <li class="breadcrumb-item active text-primary">Comment Ça Marche</li>
            </ol>
        </div>
    </div>

    <!-- Steps Section -->
    <div class="container-fluid py-5" id="steps">
        <div class="container py-5">
            <div class="text-center mx-auto pb-5 wow fadeInUp" data-wow-delay="0.1s" style="max-width: 800px;">
                <h3 class="section-title text-capitalize text-primary mb-3 montserrat">Nos Étapes Simples</h3>
                <p class="mb-0 lato">Louer une voiture avec Cental est rapide et facile. Voici comment ça fonctionne.</p>
            </div>
            <div class="row g-4">
                <div class="col-lg-4 col-md-6 wow fadeInUp" data-wow-delay="0.1s">
                    <div class="step-card card border-0 shadow-sm p-4 text-center">
                        <div class="step-icon mb-3">
                            <i class="fas fa-car fa-2x text-primary"></i>
                        </div>
                        <h4 class="mb-3 montserrat">1. Choisissez Votre Voiture</h4>
                        <p class="lato">Parcourez notre large gamme de véhicules et sélectionnez celui qui correspond à vos
                            besoins.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 wow fadeInUp" data-wow-delay="0.2s">
                    <div class="step-card card border-0 shadow-sm p-4 text-center">
                        <div class="step-icon mb-3">
                            <i class="fas fa-calendar-check fa-2x text-primary"></i>
                        </div>
                        <h4 class="mb-3 montserrat">2. Réservez en Ligne</h4>
                        <p class="lato">Remplissez notre formulaire de réservation en ligne avec vos dates et préférences.
                        </p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 wow fadeInUp" data-wow-delay="0.3s">
                    <div class="step-card card border-0 shadow-sm p-4 text-center">
                        <div class="step-icon mb-3">
                            <i class="fas fa-user-check fa-2x text-primary"></i>
                        </div>
                        <h4 class="mb-3 montserrat">3. Vérifiez Vos Détails</h4>
                        <p class="lato">Fournissez vos informations personnelles et votre permis de conduire pour
                            confirmation.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 wow fadeInUp" data-wow-delay="0.4s">
                    <div class="step-card card border-0 shadow-sm p-4 text-center">
                        <div class="step-icon mb-3">
                            <i class="fas fa-key fa-2x text-primary"></i>
                        </div>
                        <h4 class="mb-3 montserrat">4. Récupérez Votre Voiture</h4>
                        <p class="lato">Rendez-vous à notre agence à Casablanca pour récupérer votre véhicule.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 wow fadeInUp" data-wow-delay="0.5s">
                    <div class="step-card card border-0 shadow-sm p-4 text-center">
                        <div class="step-icon mb-3">
                            <i class="fas fa-undo-alt fa-2x text-primary"></i>
                        </div>
                        <h4 class="mb-3 montserrat">5. Retournez le Véhicule</h4>
                        <p class="lato">Ramenez la voiture à l’agence à la fin de votre location, avec le plein de
                            carburant.</p>
                    </div>
                </div>
            </div>
            <div class="text-center mt-5 wow fadeInUp" data-wow-delay="0.6s">
                <a href="#" class="btn btn-primary btn-lg py-2 px-4">Réservez Maintenant</a>
            </div>
        </div>
    </div>

    <!-- Contact Info Section -->
    <div class="container-fluid bg-light py-5">
        <div class="container py-5">
            <div class="text-center mx-auto pb-5 wow fadeInUp" data-wow-delay="0.1s" style="max-width: 800px;">
                <h3 class="section-title text-capitalize text-primary mb-3 montserrat">Besoin d’Aide ?</h3>
                <p class="mb-0 lato">Notre équipe est disponible 24/7 pour répondre à vos questions. Contactez-nous pour une
                    assistance personnalisée.</p>
            </div>
            <div class="row g-4 justify-content-center">
                <div class="col-md-4 wow fadeInUp" data-wow-delay="0.1s">
                    <div class="contact-item card border-0 shadow-sm p-4 text-center">
                        <div class="contact-icon mb-3">
                            <i class="fas fa-phone-alt fa-2x text-primary"></i>
                        </div>
                        <h4 class="mb-3 montserrat">Téléphone</h4>
                        <p class="mb-0 lato">+212 5 22 XX XX XX</p>
                    </div>
                </div>
                <div class="col-md-4 wow fadeInUp" data-wow-delay="0.3s">
                    <div class="contact-item card border-0 shadow-sm p-4 text-center">
                        <div class="contact-icon mb-3">
                            <i class="fas fa-envelope fa-2x text-primary"></i>
                        </div>
                        <h4 class="mb-3 montserrat">Email</h4>
                        <p class="mb-0 lato">contact@cental.ma</p>
                    </div>
                </div>
                <div class="col-md-4 wow fadeInUp" data-wow-delay="0.5s">
                    <div class="contact-item card border-0 shadow-sm p-4 text-center">
                        <div class="contact-icon mb-3">
                            <i class="fab fa-whatsapp fa-2x text-primary"></i>
                        </div>
                        <h4 class="mb-3 montserrat">WhatsApp</h4>
                        <p class="mb-0 lato">+212 6 XX XX XX XX</p>
                    </div>
                </div>
            </div>
            <div class="text-center mt-5 wow fadeInUp" data-wow-delay="0.6s">
                <a href="{{ route('contact') }}" class="btn btn-outline-primary btn-lg py-2 px-4">Contactez-Nous</a>
            </div>
        </div>
    </div>

    <!-- Structured Data -->
    <script type="application/ld+json">
                    {
                        "@context": "https://schema.org",
                        "@type": "WebPage",
                        "name": "Comment Ça Marche",
                        "description": "Apprenez comment réserver une voiture avec Cental en suivant notre guide simple en 5 étapes.",
                        "publisher": {
                            "@type": "Organization",
                            "name": "Cental",
                            "logo": {
                                "@type": "ImageObject",
                                "url": "{{ asset('site/img/logo.png') }}"
                            }
                        },
                        "mainEntity": {
                            "@type": "HowTo",
                            "name": "Comment Louer une Voiture avec Cental",
                            "step": [
                                {
                                    "@type": "HowToStep",
                                    "name": "Choisissez Votre Voiture",
                                    "text": "Parcourez notre large gamme de véhicules et sélectionnez celui qui correspond à vos besoins."
                                },
                                {
                                    "@type": "HowToStep",
                                    "name": "Réservez en Ligne",
                                    "text": "Remplissez notre formulaire de réservation en ligne avec vos dates et préférences."
                                },
                                {
                                    "@type": "HowToStep",
                                    "name": "Vérifiez Vos Détails",
                                    "text": "Fournissez vos informations personnelles et votre permis de conduire pour confirmation."
                                },
                                {
                                    "@type": "HowToStep",
                                    "name": "Récupérez Votre Voiture",
                                    "text": "Rendez-vous à notre agence à Casablanca pour récupérer votre véhicule."
                                },
                                {
                                    "@type": "HowToStep",
                                    "name": "Retournez le Véhicule",
                                    "text": "Ramenez la voiture à l’agence à la fin de votre location, avec le plein de carburant."
                                }
                            ]
                        }
                    }
                </script>
@endsection

@push('styles')
    <style>
        /* Brand Fonts */
        .montserrat {
            font-family: 'Montserrat', sans-serif;
        }

        .lato {
            font-family: 'Lato', sans-serif;
        }

        /* Gradient Background */
        .bg-gradient-primary {
            background: linear-gradient(135deg, var(--bs-primary) 0%, var(--bs-secondary) 100%);
        }

        .hero-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.4);
            z-index: 1;
        }

        .how-it-works-hero h1,
        .how-it-works-hero p,
        .how-it-works-hero a {
            position: relative;
            z-index: 2;
        }

        /* Typography */
        .hero-title {
            font-size: 3rem;
            font-weight: 700;
        }

        .lead {
            font-size: 1rem;
        }

        .section-title {
            font-size: 1.25rem;
            font-weight: 700;
        }

        p,
        li {
            font-size: 1rem;
        }

        /* Glassmorphism Card */
        .step-card,
        .contact-item {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(8px);
            border-radius: 12px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .step-card:hover,
        .contact-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15) !important;
        }

        /* Step Icon */
        .step-icon,
        .contact-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: rgba(var(--bs-primary-rgb), 0.1);
        }

        /* Section Titles */
        .section-title::after {
            content: '';
            display: block;
            width: 60px;
            height: 4px;
            background: var(--bs-primary);
            border-radius: 2px;
            margin: 0.5rem auto;
        }

        /* Button Effects */
        .btn-primary,
        .btn-outline-primary {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .btn-primary:hover,
        .btn-outline-primary:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {

            .step-card,
            .contact-item {
                padding: 1.5rem !important;
            }

            .hero-title {
                font-size: 2rem;
            }

            .lead {
                font-size: 0.875rem;
            }

            .section-title {
                font-size: 1.125rem;
            }

            p,
            li {
                font-size: 0.875rem;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        // Initialize WOW.js
        new WOW().init();

        // Smooth scroll for hero button
        document.querySelector('a[href="#steps"]').addEventListener('click', function (e) {
            e.preventDefault();
            const targetElement = document.querySelector('#steps');
            window.scrollTo({
                top: targetElement.offsetTop - 80,
                behavior: 'smooth'
            });
        });
    </script>
@endpush