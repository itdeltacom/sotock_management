@extends('site.layouts.app')

@section('title', 'Cental - Erreur Serveur (500)')
@section('meta_description', 'Une erreur serveur s\'est produite. Nos équipes travaillent à résoudre le problème. Cental à Casablanca pour vos locations de voitures au Maroc.')
@section('meta_keywords', 'erreur 500, erreur serveur, location voiture Casablanca, Cental Maroc')

@section('og_title', 'Erreur Serveur - Cental Location de Voitures')
@section('og_description', 'Une erreur serveur s\'est produite. Nos équipes travaillent à résoudre le problème.')
@section('og_image', asset('site/img/carousel-1.jpg'))

@section('content')
    <!-- Dynamic Breadcrumb -->
    <div class="container-fluid bg-breadcrumb bg-secondary">
        <div class="container text-center py-5" style="max-width: 900px;">
            <h4 class="text-white display-4 mb-4 wow fadeInDown" data-wow-delay="0.1s">
                Erreur Serveur
            </h4>
            <ol class="breadcrumb d-flex justify-content-center mb-0 wow fadeInDown" data-wow-delay="0.3s">
                <li class="breadcrumb-item">
                    <a href="{{ route('home') }}">Accueil</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="#">Erreurs</a>
                </li>
                <li class="breadcrumb-item active text-primary">
                    Erreur 500
                </li>
            </ol>
        </div>
    </div>

    <!-- 500 Error Content -->
    <div class="container-fluid contact py-5">
        <div class="container py-5">
            <div class="text-center mx-auto pb-5 wow fadeInUp" data-wow-delay="0.1s" style="max-width: 800px;">
                <h1 class="display-5 text-capitalize text-primary mb-3">Erreur Serveur</h1>
                <p class="mb-0">Une erreur interne s'est produite sur notre serveur. Nos équipes techniques ont été
                    informées et travaillent à résoudre le problème.</p>
            </div>
            <div class="row g-5 justify-content-center">
                <div class="col-lg-8 col-xl-6 wow fadeInUp" data-wow-delay="0.1s">
                    <div class="bg-light p-5 rounded text-center">
                        <i class="bi bi-gear display-1 text-primary mb-4"></i>
                        <h1 class="display-1">500</h1>
                        <h4 class="mb-4">Erreur Interne du Serveur</h4>
                        <p class="mb-4">Désolé pour ce désagrément. Notre équipe technique a été automatiquement notifiée et
                            travaille déjà à résoudre ce problème. Veuillez réessayer ultérieurement.</p>
                        <div class="d-flex justify-content-center gap-3">
                            <a class="btn btn-primary rounded-pill py-3 px-5" href="{{ route('home') }}">Retour à
                                l'Accueil</a>
                            <a class="btn btn-outline-primary rounded-pill py-3 px-5"
                                href="{{ url()->current() }}">Réessayer</a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-8 col-xl-5 wow fadeInUp" data-wow-delay="0.3s">
                    <div class="bg-secondary p-5 rounded">
                        <h4 class="text-primary mb-4">Nos Services Restent Disponibles</h4>
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-shrink-0 btn-square bg-light rounded-circle"
                                style="width: 50px; height: 50px;">
                                <i class="fa fa-phone-alt text-primary"></i>
                            </div>
                            <div class="ms-3">
                                <h6>Service Client</h6>
                                <p class="mb-0">+212 5 22 XX XX XX</p>
                            </div>
                        </div>
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-shrink-0 btn-square bg-light rounded-circle"
                                style="width: 50px; height: 50px;">
                                <i class="fab fa-whatsapp text-primary"></i>
                            </div>
                            <div class="ms-3">
                                <h6>WhatsApp</h6>
                                <p class="mb-0">+212 6 XX XX XX XX</p>
                            </div>
                        </div>
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-shrink-0 btn-square bg-light rounded-circle"
                                style="width: 50px; height: 50px;">
                                <i class="fa fa-envelope text-primary"></i>
                            </div>
                            <div class="ms-3">
                                <h6>Email</h6>
                                <p class="mb-0">contact@cental.ma</p>
                            </div>
                        </div>
                        <div class="bg-light p-3 rounded mt-4">
                            <h6 class="text-primary mb-2">Rapport d'Erreur</h6>
                            <p class="small mb-0">Code d'Erreur: <span class="text-dark">500</span></p>
                            <p class="small mb-0">Référence: <span class="text-dark">{{ Str::random(8) }}</span></p>
                            <p class="small mb-0">Horodatage: <span
                                    class="text-dark">{{ now()->format('d/m/Y H:i:s') }}</span></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection