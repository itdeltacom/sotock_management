@extends('site.layouts.app')

@section('title', 'Cental - Accès Non Autorisé (401)')
@section('meta_description', 'Vous n\'avez pas les autorisations nécessaires pour accéder à cette page. Contactez Cental à Casablanca pour assistance.')
@section('meta_keywords', 'erreur 401, accès refusé, location voiture Casablanca, Cental Maroc')

@section('og_title', 'Accès Non Autorisé - Cental Location de Voitures')
@section('og_description', 'Vous n\'avez pas les autorisations nécessaires pour accéder à cette page. Contactez notre équipe pour assistance.')
@section('og_image', asset('site/img/carousel-1.jpg'))

@section('content')
    <!-- Dynamic Breadcrumb -->
    <div class="container-fluid bg-breadcrumb bg-secondary">
        <div class="container text-center py-5" style="max-width: 900px;">
            <h4 class="text-white display-4 mb-4 wow fadeInDown" data-wow-delay="0.1s">
                Accès Non Autorisé
            </h4>
            <ol class="breadcrumb d-flex justify-content-center mb-0 wow fadeInDown" data-wow-delay="0.3s">
                <li class="breadcrumb-item">
                    <a href="{{ route('home') }}">Accueil</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="#">Erreurs</a>
                </li>
                <li class="breadcrumb-item active text-primary">
                    Erreur 401
                </li>
            </ol>
        </div>
    </div>

    <!-- 401 Error Content -->
    <div class="container-fluid contact py-5">
        <div class="container py-5">
            <div class="text-center mx-auto pb-5 wow fadeInUp" data-wow-delay="0.1s" style="max-width: 800px;">
                <h1 class="display-5 text-capitalize text-primary mb-3">Accès Non Autorisé</h1>
                <p class="mb-0">Vous n'avez pas les autorisations nécessaires pour accéder à cette page.</p>
            </div>
            <div class="row g-5 justify-content-center">
                <div class="col-lg-8 col-xl-6 wow fadeInUp" data-wow-delay="0.1s">
                    <div class="bg-light p-5 rounded text-center">
                        <i class="bi bi-shield-lock display-1 text-primary mb-4"></i>
                        <h1 class="display-1">401</h1>
                        <h4 class="mb-4">Authentification Requise</h4>
                        <p class="mb-4">Vous devez vous authentifier pour accéder à cette ressource. Si vous pensez qu'il
                            s'agit d'une erreur, veuillez contacter notre service client pour assistance.</p>
                        <a class="btn btn-primary rounded-pill py-3 px-5" href="{{ route('home') }}">Retour à l'Accueil</a>
                    </div>
                </div>
                <div class="col-lg-8 col-xl-5 wow fadeInUp" data-wow-delay="0.3s">
                    <div class="bg-secondary p-5 rounded">
                        <h4 class="text-primary mb-4">Besoin d'Aide?</h4>
                        <div class="row g-5">
                            <div class="col-md-6">
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
                            <div class="col-md-6">
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
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection