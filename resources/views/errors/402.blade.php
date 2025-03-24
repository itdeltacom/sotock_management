@extends('site.layouts.app')

@section('title', 'Cental - Paiement Requis (402)')
@section('meta_description', 'Un paiement est requis pour accéder à cette page ou service. Contactez Cental à Casablanca pour assistance.')
@section('meta_keywords', 'erreur 402, paiement requis, location voiture Casablanca, Cental Maroc')

@section('og_title', 'Paiement Requis - Cental Location de Voitures')
@section('og_description', 'Un paiement est requis pour accéder à cette page ou service. Contactez notre équipe pour assistance.')
@section('og_image', asset('site/img/carousel-1.jpg'))

@section('content')
    <!-- Dynamic Breadcrumb -->
    <div class="container-fluid bg-breadcrumb bg-secondary">
        <div class="container text-center py-5" style="max-width: 900px;">
            <h4 class="text-white display-4 mb-4 wow fadeInDown" data-wow-delay="0.1s">
                Paiement Requis
            </h4>
            <ol class="breadcrumb d-flex justify-content-center mb-0 wow fadeInDown" data-wow-delay="0.3s">
                <li class="breadcrumb-item">
                    <a href="{{ route('home') }}">Accueil</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="#">Erreurs</a>
                </li>
                <li class="breadcrumb-item active text-primary">
                    Erreur 402
                </li>
            </ol>
        </div>
    </div>

    <!-- 402 Error Content -->
    <div class="container-fluid contact py-5">
        <div class="container py-5">
            <div class="text-center mx-auto pb-5 wow fadeInUp" data-wow-delay="0.1s" style="max-width: 800px;">
                <h1 class="display-5 text-capitalize text-primary mb-3">Paiement Requis</h1>
                <p class="mb-0">Un paiement est nécessaire pour accéder à ce contenu ou service.</p>
            </div>
            <div class="row g-5 justify-content-center">
                <div class="col-lg-8 col-xl-6 wow fadeInUp" data-wow-delay="0.1s">
                    <div class="bg-light p-5 rounded text-center">
                        <i class="bi bi-credit-card display-1 text-primary mb-4"></i>
                        <h1 class="display-1">402</h1>
                        <h4 class="mb-4">Transaction Requise</h4>
                        <p class="mb-4">Un paiement est nécessaire pour accéder à ce contenu ou service. Veuillez effectuer
                            le paiement ou contacter notre service client pour assistance.</p>
                        <a class="btn btn-primary rounded-pill py-3 px-5" href="{{ route('home') }}">Retour à l'Accueil</a>
                    </div>
                </div>
                <div class="col-lg-8 col-xl-5 wow fadeInUp" data-wow-delay="0.3s">
                    <div class="bg-secondary p-5 rounded">
                        <h4 class="text-primary mb-4">Nos Tarifs</h4>
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-shrink-0 btn-square bg-light rounded-circle"
                                style="width: 50px; height: 50px;">
                                <i class="fa fa-car-alt text-primary"></i>
                            </div>
                            <div class="ms-3">
                                <h6>Location Économique</h6>
                                <p class="mb-0">À partir de 500 MAD / jour</p>
                            </div>
                        </div>
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-shrink-0 btn-square bg-light rounded-circle"
                                style="width: 50px; height: 50px;">
                                <i class="fa fa-car text-primary"></i>
                            </div>
                            <div class="ms-3">
                                <h6>Location Intermédiaire</h6>
                                <p class="mb-0">À partir de 800 MAD / jour</p>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0 btn-square bg-light rounded-circle"
                                style="width: 50px; height: 50px;">
                                <i class="fa fa-car text-primary"></i>
                            </div>
                            <div class="ms-3">
                                <h6>Location Premium</h6>
                                <p class="mb-0">À partir de 1200 MAD / jour</p>
                            </div>
                        </div>
                        <div class="mt-4">
                            <h6>Pour toute information:</h6>
                            <p><i class="fa fa-phone-alt me-2 text-primary"></i>+212 5 22 XX XX XX</p>
                            <p><i class="fa fa-envelope me-2 text-primary"></i>contact@cental.ma</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection