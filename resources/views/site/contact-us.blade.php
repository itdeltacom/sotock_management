@extends('site.layouts.app')

@section('title', 'Cental - Services de Location de Voitures à Casablanca | Contactez-Nous')
@section('meta_description', 'Contactez Cental à Casablanca pour vos besoins de location de voitures. Nos conseillers sont à votre écoute pour un service personnalisé, des tarifs compétitifs et une assistance 24/7.')
@section('meta_keywords', 'contact location voiture Casablanca, réservation voiture Maroc, service client Cental, location véhicule Casablanca')

@section('og_title', 'Contactez Cental - Votre Partenaire Location de Voitures')
@section('og_description', 'Nous sommes à votre écoute. Contactez Cental Casablanca pour toute demande de location de voiture ou information.')
@section('og_image', asset('site/img/carousel-1.jpg'))

@section('content')
    <!-- Header Start -->
    @include('site.includes.head')
    <!-- Header End -->

    <!-- Contact Start -->
    <div class="container-fluid contact py-5">
        <div class="container py-5">
            <div class="text-center mx-auto pb-5 wow fadeInUp" data-wow-delay="0.1s" style="max-width: 800px;">
                <h1 class="display-5 text-capitalize text-primary mb-3">Contactez-Nous</h1>
                <p class="mb-0">Nous sommes à votre écoute. Notre équipe est disponible pour répondre à toutes vos questions
                    et vous accompagner dans votre location de véhicule.</p>
            </div>
            <div class="row g-5">
                <div class="col-12 wow fadeInUp" data-wow-delay="0.1s">
                    <div class="row g-5">
                        <div class="col-md-6 col-lg-6 col-xl-3">
                            <div class="contact-add-item p-4">
                                <div class="contact-icon mb-4">
                                    <i class="fas fa-map-marker-alt fa-2x"></i>
                                </div>
                                <div>
                                    <h4>Adresse</h4>
                                    <p class="mb-0">123 Boulevard Mohammed V, Casablanca, Maroc</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-6 col-xl-3 wow fadeInUp" data-wow-delay="0.3s">
                            <div class="contact-add-item p-4">
                                <div class="contact-icon mb-4">
                                    <i class="fas fa-envelope fa-2x"></i>
                                </div>
                                <div>
                                    <h4>Email</h4>
                                    <p class="mb-0">contact@cental.ma</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-6 col-xl-3 wow fadeInUp" data-wow-delay="0.5s">
                            <div class="contact-add-item p-4">
                                <div class="contact-icon mb-4">
                                    <i class="fa fa-phone-alt fa-2x"></i>
                                </div>
                                <div>
                                    <h4>Téléphone</h4>
                                    <p class="mb-0">+212 5 22 XX XX XX</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-6 col-xl-3 wow fadeInUp" data-wow-delay="0.7s">
                            <div class="contact-add-item p-4">
                                <div class="contact-icon mb-4">
                                    <i class="fab fa-whatsapp fa-2x"></i>
                                </div>
                                <div>
                                    <h4>WhatsApp</h4>
                                    <p class="mb-0">+212 6 XX XX XX XX</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-6 wow fadeInUp" data-wow-delay="0.1s">
                    <div class="bg-secondary p-5 rounded">
                        <h4 class="text-primary mb-4">Envoyez-nous un Message</h4>
                        <form method="POST" action="{{ route('contact.submit') }}">
                            @csrf
                            <div class="row g-4">
                                <div class="col-lg-12 col-xl-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="name" name="name"
                                            placeholder="Votre Nom" required>
                                        <label for="name">Votre Nom</label>
                                    </div>
                                </div>
                                <div class="col-lg-12 col-xl-6">
                                    <div class="form-floating">
                                        <input type="email" class="form-control" id="email" name="email"
                                            placeholder="Votre Email" required>
                                        <label for="email">Votre Email</label>
                                    </div>
                                </div>
                                <div class="col-lg-12 col-xl-6">
                                    <div class="form-floating">
                                        <input type="tel" class="form-control" id="phone" name="phone"
                                            placeholder="Téléphone" required>
                                        <label for="phone">Votre Téléphone</label>
                                    </div>
                                </div>
                                <div class="col-lg-12 col-xl-6">
                                    <div class="form-floating">
                                        <select class="form-control" id="service" name="service" required>
                                            <option value="">Choisissez un Service</option>
                                            <option value="location">Location de Voiture</option>
                                            <option value="reservation">Réservation</option>
                                            <option value="assistance">Assistance</option>
                                            <option value="autre">Autre</option>
                                        </select>
                                        <label for="service">Type de Service</label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="subject" name="subject"
                                            placeholder="Sujet" required>
                                        <label for="subject">Sujet</label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-floating">
                                        <textarea class="form-control" placeholder="Votre Message" id="message"
                                            name="message" style="height: 160px" required></textarea>
                                        <label for="message">Votre Message</label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-light w-100 py-3">Envoyer le Message</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="col-12 col-xl-1 wow fadeInUp" data-wow-delay="0.3s">
                    <div class="d-flex flex-xl-column align-items-center justify-content-center">
                        <a class="btn btn-xl-square btn-light rounded-circle mb-0 mb-xl-4 me-4 me-xl-0"
                            href="https://www.facebook.com/cental.ma"><i class="fab fa-facebook-f"></i></a>
                        <a class="btn btn-xl-square btn-light rounded-circle mb-0 mb-xl-4 me-4 me-xl-0"
                            href="https://www.twitter.com/cental_maroc"><i class="fab fa-twitter"></i></a>
                        <a class="btn btn-xl-square btn-light rounded-circle mb-0 mb-xl-4 me-4 me-xl-0"
                            href="https://www.instagram.com/cental.maroc"><i class="fab fa-instagram"></i></a>
                        <a class="btn btn-xl-square btn-light rounded-circle mb-0 mb-xl-0 me-0 me-xl-0"
                            href="https://www.linkedin.com/company/cental-maroc"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
                <div class="col-12 col-xl-5 wow fadeInUp" data-wow-delay="0.1s">
                    <div class="p-5 bg-light rounded">
                        <div class="bg-white rounded p-4 mb-4">
                            <h4 class="mb-3">Agence Casablanca</h4>
                            <div class="d-flex align-items-center flex-shrink-0 mb-3">
                                <p class="mb-0 text-dark me-2">Adresse:</p><i
                                    class="fas fa-map-marker-alt text-primary me-2"></i>
                                <p class="mb-0">123 Boulevard Mohammed V, Casablanca</p>
                            </div>
                            <div class="d-flex align-items-center">
                                <p class="mb-0 text-dark me-2">Téléphone:</p><i
                                    class="fa fa-phone-alt text-primary me-2"></i>
                                <p class="mb-0">+212 5 22 XX XX XX</p>
                            </div>
                        </div>
                        <div class="bg-white rounded p-4 mb-4">
                            <h4 class="mb-3">Agence Rabat</h4>
                            <div class="d-flex align-items-center mb-3">
                                <p class="mb-0 text-dark me-2">Adresse:</p><i
                                    class="fas fa-map-marker-alt text-primary me-2"></i>
                                <p class="mb-0">45 Avenue Mohammed VI, Rabat</p>
                            </div>
                            <div class="d-flex align-items-center">
                                <p class="mb-0 text-dark me-2">Téléphone:</p><i
                                    class="fa fa-phone-alt text-primary me-2"></i>
                                <p class="mb-0">+212 5 37 XX XX XX</p>
                            </div>
                        </div>
                        <div class="bg-white rounded p-4 mb-0">
                            <h4 class="mb-3">Agence Marrakech</h4>
                            <div class="d-flex align-items-center mb-3">
                                <p class="mb-0 text-dark me-2">Adresse:</p><i
                                    class="fas fa-map-marker-alt text-primary me-2"></i>
                                <p class="mb-0">78 Avenue Mohammed V, Marrakech</p>
                            </div>
                            <div class="d-flex align-items-center">
                                <p class="mb-0 text-dark me-2">Téléphone:</p><i
                                    class="fa fa-phone-alt text-primary me-2"></i>
                                <p class="mb-0">+212 5 24 XX XX XX</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="rounded">
                        <iframe class="rounded w-100" style="height: 400px;"
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3323.6964267109!2d-7.618821084649528!3d33.58821178072574!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0xda7cd4dd7c541ad%3A0x82a50a42043d8a!2sCasablanca!5e0!3m2!1sfr!2sma!4v1694259649153!5m2!1sfr!2sma"
                            loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Contact End -->

    <!-- Structured Data for Car Rental Service -->
    <script type="application/ld+json">
        {
          "@context": "https://schema.org",
          "@type": "CarRentalBusiness",
          "name": "Cental Location de Voitures",
          "image": "{{asset('site/img/carousel-1.jpg')}}",
          "address": {
            "@type": "PostalAddress",
            "streetAddress": "123 Boulevard Mohammed V",
            "addressLocality": "Casablanca",
            "addressRegion": "Grand Casablanca",
            "postalCode": "20000",
            "addressCountry": "MA"
          },
          "geo": {
            "@type": "GeoCoordinates",
            "latitude": "33.5731",
            "longitude": "-7.5898"
          },
          "url": "{{url('/')}}",
          "telephone": "+212 5 22 XX XX XX",
          "openingHoursSpecification": [
            {
              "@type": "OpeningHoursSpecification",
              "dayOfWeek": ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday"],
              "opens": "08:00",
              "closes": "18:00"
            },
            {
              "@type": "OpeningHoursSpecification",
              "dayOfWeek": ["Saturday"],
              "opens": "09:00",
              "closes": "16:00"
            }
          ],
          "priceRange": "500 - 2000 MAD",
          "aggregateRating": {
            "@type": "AggregateRating",
            "ratingValue": "4.7",
            "reviewCount": "1200"
          }
        }
        </script>
    <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "Service",
            "serviceType": "Location de Voitures",
            "provider": {
                "@type": "Organization",
                "name": "Cental Location de Voitures"
            },
            "areaServed": "Maroc",
            "offers": {
                "@type": "Offer",
                "priceCurrency": "MAD",
                "priceRange": "500 - 2000"
            }
        }
        </script>
    <script type="application/ld+json">
        {
          "@context": "https://schema.org",
          "@type": "PostalAddress",
            "streetAddress": "123 Boulevard Mohammed V",
            "addressLocality": "Casablanca",
            "addressRegion": "Grand Casablanca",
            "postalCode": "20000",
            "addressCountry": "MA"
          },
          "telephone": "+212 5 22 XX XX XX",
          "openingHoursSpecification": [
            {
              "@type": "OpeningHoursSpecification",
              "dayOfWeek": ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday"],
              "opens": "08:00",
              "closes": "18:00"
            },
            {
              "@type": "OpeningHoursSpecification",
              "dayOfWeek": ["Saturday"],
              "opens": "09:00",
              "closes": "16:00"
            }
          ]
        }
        </script>
@endsection