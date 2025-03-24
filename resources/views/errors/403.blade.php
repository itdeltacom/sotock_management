@extends('site.layouts.app')

@section('title', 'Cental - Accès Interdit (403)')
@section('meta_description', 'L\'accès à cette page est interdit. Contactez Cental à Casablanca pour assistance avec votre demande de location de voiture.')
@section('meta_keywords', 'erreur 403, accès interdit, location voiture Casablanca, Cental Maroc')

@section('og_title', 'Accès Interdit - Cental Location de Voitures')
@section('og_description', 'L\'accès à cette page est interdit. Contactez notre équipe pour assistance.')
@section('og_image', asset('site/img/carousel-1.jpg'))

@section('content')
    <!-- Dynamic Breadcrumb -->
    <div class="container-fluid bg-breadcrumb bg-secondary">
        <div class="container text-center py-5" style="max-width: 900px;">
            <h4 class="text-white display-4 mb-4 wow fadeInDown" data-wow-delay="0.1s">
                Accès Interdit
            </h4>
            <ol class="breadcrumb d-flex justify-content-center mb-0 wow fadeInDown" data-wow-delay="0.3s">
                <li class="breadcrumb-item">
                    <a href="{{ route('home') }}">Accueil</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="#">Erreurs</a>
                </li>
                <li class="breadcrumb-item active text-primary">
                    Erreur 403
                </li>
            </ol>
        </div>
    </div>

    <!-- 403 Error Content -->
    <div class="container-fluid contact py-5">
        <div class="container py-5">
            <div class="text-center mx-auto pb-5 wow fadeInUp" data-wow-delay="0.1s" style="max-width: 800px;">
                <h1 class="display-5 text-capitalize text-primary mb-3">Accès Interdit</h1>
                <p class="mb-0">Vous n'avez pas l'autorisation d'accéder à cette ressource.</p>
            </div>
            <div class="row g-5 justify-content-center">
                <div class="col-lg-8 col-xl-6 wow fadeInUp" data-wow-delay="0.1s">
                    <div class="bg-light p-5 rounded text-center">
                        <i class="bi bi-x-octagon display-1 text-primary mb-4"></i>
                        <h1 class="display-1">403</h1>
                        <h4 class="mb-4">Accès Refusé</h4>
                        <p class="mb-4">Vous n'avez pas les droits nécessaires pour accéder à cette ressource. Si vous
                            pensez qu'il s'agit d'une erreur, veuillez contacter notre service client.</p>
                        <a class="btn btn-primary rounded-pill py-3 px-5" href="{{ route('home') }}">Retour à l'Accueil</a>
                    </div>
                </div>
                <div class="col-lg-8 col-xl-5 wow fadeInUp" data-wow-delay="0.3s">
                    <div class="bg-secondary p-5 rounded">
                        <h4 class="text-primary mb-4">Contactez-Nous</h4>
                        <div class="row g-4">
                            <div class="col-md-6 col-lg-6">
                                <div class="d-flex">
                                    <div class="flex-shrink-0 btn-square bg-light rounded-circle"
                                        style="width: 50px; height: 50px;">
                                        <i class="fa fa-map-marker-alt text-primary"></i>
                                    </div>
                                    <div class="ms-3">
                                        <h6>Adresse</h6>
                                        <p class="mb-0">123 Boulevard Mohammed V, Casablanca</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-6">
                                <div class="d-flex">
                                    <div class="flex-shrink-0 btn-square bg-light rounded-circle"
                                        style="width: 50px; height: 50px;">
                                        <i class="fa fa-phone-alt text-primary"></i>
                                    </div>
                                    <div class="ms-3">
                                        <h6>Téléphone</h6>
                                        <p class="mb-0">+212 5 22 XX XX XX</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-6">
                                <div class="d-flex">
                                    <div class="flex-shrink-0 btn-square bg-light rounded-circle"
                                        style="width: 50px; height: 50px;">
                                        <i class="fa fa-envelope text-primary"></i>
                                    </div>
                                    <div class="ms-3">
                                        <h6>Email</h6>
                                        <p class="mb-0">contact@cental.ma</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-6">
                                <div class="d-flex">
                                    <div class="flex-shrink-0 btn-square bg-light rounded-circle"
                                        style="width: 50px; height: 50px;">
                                        <i class="fab fa-whatsapp text-primary"></i>
                                    </div>
                                    <div class="ms-3">
                                        <h6>WhatsApp</h6>
                                        <p class="mb-0">+212 6 XX XX XX XX</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection