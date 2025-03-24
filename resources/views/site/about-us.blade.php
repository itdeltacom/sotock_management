@extends('site.layouts.app')

@section('title', 'Cental - Premium Car Rental Services | Book Your Dream Car Today')
@section('meta_description', 'Cental offers premium car rental services with a wide selection of luxury vehicles including Mercedes, BMW, Tesla, and more. Enjoy 24/7 support, free pick-up, and competitive rates.')
@section('meta_keywords', 'car rental, luxury car rental, premium cars, rent a car, cheap car rental, Mercedes Benz, Tesla, BMW, Toyota')

@section('og_title', 'Premium Car Rental Services | Cental')
@section('og_description', 'Choose from our fleet of luxury vehicles. Get 15% off your rental today with free pick-up and 24/7 road assistance.')
@section('og_image', asset('site/img/carousel-1.jpg'))
@section('content')

    <!-- Header Start -->
    @include('site.includes.head')
    <!-- Header End -->
    <div class="container-fluid overflow-hidden about py-5">
        <div class="container py-5">
            <div class="row g-5">
                <div class="col-xl-6 wow fadeInLeft" data-wow-delay="0.2s">
                    <div class="about-item">
                        <div class="pb-5">
                            <h1 class="display-5 text-capitalize">Cental <span class="text-primary">À Propos</span></h1>
                            <p class="mb-0">Née à Casablanca, Cental est votre partenaire de mobilité au cœur du Maroc. Nous
                                comprenons les besoins uniques des voyageurs et des professionnels de notre région.</p>
                        </div>
                        <div class="row g-4">
                            <div class="col-lg-6">
                                <div class="about-item-inner border p-4">
                                    <div class="about-icon mb-4">
                                        <img src="{{asset('site/img/about-icon-1.png')}}" class="img-fluid w-50 h-50"
                                            alt="Icône Vision">
                                    </div>
                                    <h5 class="mb-3">Notre Vision</h5>
                                    <p class="mb-0">Être le leader de la location de voitures au Maroc, en offrant une
                                        mobilité intelligente et accessible.</p>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="about-item-inner border p-4">
                                    <div class="about-icon mb-4">
                                        <img src="{{asset('site/img/about-icon-2.png')}}" class="img-fluid h-50 w-50"
                                            alt="Icône Mission">
                                    </div>
                                    <h5 class="mb-3">Notre Mission</h5>
                                    <p class="mb-0">Fournir des solutions de mobilité adaptées, avec une flotte de véhicules
                                        modernes et un service personnalisé.</p>
                                </div>
                            </div>
                        </div>
                        <p class="text-item my-4">Depuis notre création à Casablanca, nous avons à cœur de comprendre et de
                            servir les besoins de déplacement de nos clients, que ce soit pour le travail, les voyages ou
                            les loisirs.</p>
                        <div class="row g-4">
                            <div class="col-lg-6">
                                <div class="text-center rounded bg-secondary p-4">
                                    <h1 class="display-6 text-white">12</h1>
                                    <h5 class="text-light mb-0">Années de Présence à Casablanca</h5>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="rounded">
                                    <p class="mb-2"><i class="fa fa-check-circle text-primary me-1"></i> Service adapté au
                                        contexte local</p>
                                    <p class="mb-2"><i class="fa fa-check-circle text-primary me-1"></i> Véhicules adaptés
                                        aux routes marocaines</p>
                                    <p class="mb-2"><i class="fa fa-check-circle text-primary me-1"></i> Tarifs transparents
                                    </p>
                                    <p class="mb-0"><i class="fa fa-check-circle text-primary me-1"></i> Assistance
                                        multilingue</p>
                                </div>
                            </div>
                            <div class="col-lg-7">
                                <div class="d-flex align-items-center">
                                    <img src="{{asset('site/img/attachment-img.jpg')}}"
                                        class="img-fluid rounded-circle border border-4 border-secondary"
                                        style="width: 100px; height: 100px;" alt="Image du fondateur">
                                    <div class="ms-4">
                                        <h4>Mohammed El Fahmi</h4>
                                        <p class="mb-0">Fondateur de Cental Maroc</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-6 wow fadeInRight" data-wow-delay="0.2s">
                    <div class="about-img">
                        <div class="img-1">
                            <img src="{{asset('site/img/about-img.jpg')}}" class="img-fluid rounded h-100 w-100"
                                alt="Image de notre entreprise à Casablanca">
                        </div>
                        <div class="img-2">
                            <img src="{{asset('site/img/about-img-1.jpg')}}" class="img-fluid rounded w-100"
                                alt="Notre équipe à Casablanca">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid counter py-5">
        <div class="container py-5">
            <div class="row g-5">
                <div class="col-md-6 col-lg-6 col-xl-3 wow fadeInUp" data-wow-delay="0.1s">
                    <div class="counter-item text-center">
                        <div class="counter-item-icon mx-auto">
                            <i class="fas fa-thumbs-up fa-2x"></i>
                        </div>
                        <div class="counter-counting my-3">
                            <span class="text-white fs-2 fw-bold" data-toggle="counter-up">1200</span>
                            <span class="h1 fw-bold text-white">+</span>
                        </div>
                        <h4 class="text-white mb-0">Clients Satisfaits</h4>
                    </div>
                </div>
                <div class="col-md-6 col-lg-6 col-xl-3 wow fadeInUp" data-wow-delay="0.3s">
                    <div class="counter-item text-center">
                        <div class="counter-item-icon mx-auto">
                            <i class="fas fa-car-alt fa-2x"></i>
                        </div>
                        <div class="counter-counting my-3">
                            <span class="text-white fs-2 fw-bold" data-toggle="counter-up">85</span>
                            <span class="h1 fw-bold text-white">+</span>
                        </div>
                        <h4 class="text-white mb-0">Voitures en Flotte</h4>
                    </div>
                </div>
                <div class="col-md-6 col-lg-6 col-xl-3 wow fadeInUp" data-wow-delay="0.5s">
                    <div class="counter-item text-center">
                        <div class="counter-item-icon mx-auto">
                            <i class="fas fa-building fa-2x"></i>
                        </div>
                        <div class="counter-counting my-3">
                            <span class="text-white fs-2 fw-bold" data-toggle="counter-up">3</span>
                            <span class="h1 fw-bold text-white">+</span>
                        </div>
                        <h4 class="text-white mb-0">Agences au Maroc</h4>
                    </div>
                </div>
                <div class="col-md-6 col-lg-6 col-xl-3 wow fadeInUp" data-wow-delay="0.7s">
                    <div class="counter-item text-center">
                        <div class="counter-item-icon mx-auto">
                            <i class="fas fa-clock fa-2x"></i>
                        </div>
                        <div class="counter-counting my-3">
                            <span class="text-white fs-2 fw-bold" data-toggle="counter-up">890</span>
                            <span class="h1 fw-bold text-white">+</span>
                        </div>
                        <h4 class="text-white mb-0">Kilomètres Parcourus</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Team Start -->
    <div class="container-fluid team py-5">
        <div class="container py-5">
            <div class="text-center mx-auto pb-5 wow fadeInUp" data-wow-delay="0.1s" style="max-width: 800px;">
                <h1 class="display-5 text-capitalize mb-3">Customer<span class="text-primary"> Suport</span> Center</h1>
                <p class="mb-0">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Ut amet nemo expedita asperiores
                    commodi accusantium at cum harum, excepturi, quia tempora cupiditate! Adipisci facilis modi quisquam
                    quia distinctio,
                </p>
            </div>
            <div class="row g-4">
                <div class="col-md-6 col-lg-6 col-xl-3 wow fadeInUp" data-wow-delay="0.1s">
                    <div class="team-item p-4 pt-0">
                        <div class="team-img">
                            <img src="{{asset('site/img/team-1.jpg')}}" class="img-fluid rounded w-100" alt="Image">
                        </div>
                        <div class="team-content pt-4">
                            <h4>MARTIN DOE</h4>
                            <p>Profession</p>
                            <div class="team-icon d-flex justify-content-center">
                                <a class="btn btn-square btn-light rounded-circle mx-1" href=""><i
                                        class="fab fa-facebook-f"></i></a>
                                <a class="btn btn-square btn-light rounded-circle mx-1" href=""><i
                                        class="fab fa-twitter"></i></a>
                                <a class="btn btn-square btn-light rounded-circle mx-1" href=""><i
                                        class="fab fa-instagram"></i></a>
                                <a class="btn btn-square btn-light rounded-circle mx-1" href=""><i
                                        class="fab fa-linkedin-in"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-6 col-xl-3 wow fadeInUp" data-wow-delay="0.3s">
                    <div class="team-item p-4 pt-0">
                        <div class="team-img">
                            <img src="{{asset('site/img/team-2.jpg')}}" class="img-fluid rounded w-100" alt="Image">
                        </div>
                        <div class="team-content pt-4">
                            <h4>MARTIN DOE</h4>
                            <p>Profession</p>
                            <div class="team-icon d-flex justify-content-center">
                                <a class="btn btn-square btn-light rounded-circle mx-1" href=""><i
                                        class="fab fa-facebook-f"></i></a>
                                <a class="btn btn-square btn-light rounded-circle mx-1" href=""><i
                                        class="fab fa-twitter"></i></a>
                                <a class="btn btn-square btn-light rounded-circle mx-1" href=""><i
                                        class="fab fa-instagram"></i></a>
                                <a class="btn btn-square btn-light rounded-circle mx-1" href=""><i
                                        class="fab fa-linkedin-in"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-6 col-xl-3 wow fadeInUp" data-wow-delay="0.5s">
                    <div class="team-item p-4 pt-0">
                        <div class="team-img">
                            <img src="{{asset('site/img/team-3.jpg')}}" class="img-fluid rounded w-100" alt="Image">
                        </div>
                        <div class="team-content pt-4">
                            <h4>MARTIN DOE</h4>
                            <p>Profession</p>
                            <div class="team-icon d-flex justify-content-center">
                                <a class="btn btn-square btn-light rounded-circle mx-1" href=""><i
                                        class="fab fa-facebook-f"></i></a>
                                <a class="btn btn-square btn-light rounded-circle mx-1" href=""><i
                                        class="fab fa-twitter"></i></a>
                                <a class="btn btn-square btn-light rounded-circle mx-1" href=""><i
                                        class="fab fa-instagram"></i></a>
                                <a class="btn btn-square btn-light rounded-circle mx-1" href=""><i
                                        class="fab fa-linkedin-in"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-6 col-xl-3 wow fadeInUp" data-wow-delay="0.7s">
                    <div class="team-item p-4 pt-0">
                        <div class="team-img">
                            <img src="{{asset('site/img/team-4.jpg')}}" class="img-fluid rounded w-100" alt="Image">
                        </div>
                        <div class="team-content pt-4">
                            <h4>MARTIN DOE</h4>
                            <p>Profession</p>
                            <div class="team-icon d-flex justify-content-center">
                                <a class="btn btn-square btn-light rounded-circle mx-1" href=""><i
                                        class="fab fa-facebook-f"></i></a>
                                <a class="btn btn-square btn-light rounded-circle mx-1" href=""><i
                                        class="fab fa-twitter"></i></a>
                                <a class="btn btn-square btn-light rounded-circle mx-1" href=""><i
                                        class="fab fa-instagram"></i></a>
                                <a class="btn btn-square btn-light rounded-circle mx-1" href=""><i
                                        class="fab fa-linkedin-in"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Team End -->

    <!-- Banner Start -->
    @include('site.includes.banner')
    <!-- Banner End -->
    <!-- Structured Data for Car Rental Service -->
    <script type="application/ld+json">
        {
          "@context": "https://schema.org",
          "@type": "CarRentalBusiness",
          "name": "Cental Car Rental Maroc",
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
@endsection