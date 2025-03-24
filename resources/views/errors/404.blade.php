@extends('site.layouts.app')

@section('title', 'Cental - Page Non Trouvée (404)')
@section('meta_description', 'La page que vous recherchez n\'existe pas. Contactez Cental à Casablanca pour vos besoins de location de voitures au Maroc.')
@section('meta_keywords', 'erreur 404, page non trouvée, location voiture Casablanca, Cental Maroc')

@section('og_title', 'Page Non Trouvée - Cental Location de Voitures')
@section('og_description', 'La page que vous recherchez n\'existe pas. Découvrez nos services de location de voitures au Maroc.')
@section('og_image', asset('site/img/carousel-1.jpg'))

@section('content')
    <!-- Dynamic Breadcrumb -->
    <div class="container-fluid bg-breadcrumb bg-secondary">
        <div class="container text-center py-5" style="max-width: 900px;">
            <h4 class="text-white display-4 mb-4 wow fadeInDown" data-wow-delay="0.1s">
                Page Non Trouvée
            </h4>
            <ol class="breadcrumb d-flex justify-content-center mb-0 wow fadeInDown" data-wow-delay="0.3s">
                <li class="breadcrumb-item">
                    <a href="{{ route('home') }}">Accueil</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="#">Erreurs</a>
                </li>
                <li class="breadcrumb-item active text-primary">
                    Erreur 404
                </li>
            </ol>
        </div>
    </div>

    <!-- 404 Error Content -->
    <div class="container-fluid contact py-5">
        <div class="container py-5">
            <div class="text-center mx-auto pb-5 wow fadeInUp" data-wow-delay="0.1s" style="max-width: 800px;">
                <h1 class="display-5 text-capitalize text-primary mb-3">Page Non Trouvée</h1>
                <p class="mb-0">Nous sommes désolés, la page que vous avez recherchée n'existe pas sur notre site !</p>
            </div>
            <div class="row g-5 justify-content-center">
                <div class="col-lg-8 col-xl-6 wow fadeInUp" data-wow-delay="0.1s">
                    <div class="bg-light p-5 rounded text-center">
                        <i class="bi bi-exclamation-triangle display-1 text-primary mb-4"></i>
                        <h1 class="display-1">404</h1>
                        <h4 class="mb-4">Page Introuvable</h4>
                        <p class="mb-4">La page que vous cherchez semble avoir été déplacée, supprimée ou n'a jamais existé.
                            Veuillez vérifier l'URL ou explorer nos autres pages.</p>
                        <div class="d-flex justify-content-center gap-3">
                            <a class="btn btn-primary rounded-pill py-3 px-5" href="{{ route('home') }}">Retour à
                                l'Accueil</a>
                            <a class="btn btn-outline-primary rounded-pill py-3 px-5"
                                href="{{ route('contact') }}">Contactez-Nous</a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-8 col-xl-5 wow fadeInUp" data-wow-delay="0.3s">
                    <div class="bg-secondary p-5 rounded">
                        <h4 class="text-primary mb-4">Nos Services Populaires</h4>
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-shrink-0 btn-square bg-light rounded-circle"
                                style="width: 50px; height: 50px;">
                                <i class="fa fa-car-alt text-primary"></i>
                            </div>
                            <div class="ms-3">
                                <h6>Location de Voitures</h6>
                                <a href="/cars" class="text-primary">Découvrir nos véhicules <i
                                        class="fa fa-arrow-right ms-1"></i></a>
                            </div>
                        </div>
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-shrink-0 btn-square bg-light rounded-circle"
                                style="width: 50px; height: 50px;">
                                <i class="fa fa-calendar-alt text-primary"></i>
                            </div>
                            <div class="ms-3">
                                <h6>Réservation en Ligne</h6>
                                <a href="#" class="text-primary">Réserver maintenant <i
                                        class="fa fa-arrow-right ms-1"></i></a>
                            </div>
                        </div>
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-shrink-0 btn-square bg-light rounded-circle"
                                style="width: 50px; height: 50px;">
                                <i class="fa fa-map-marker-alt text-primary"></i>
                            </div>
                            <div class="ms-3">
                                <h6>Nos Agences</h6>
                                <a href="#" class="text-primary">Voir les emplacements <i
                                        class="fa fa-arrow-right ms-1"></i></a>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0 btn-square bg-light rounded-circle"
                                style="width: 50px; height: 50px;">
                                <i class="fa fa-headset text-primary"></i>
                            </div>
                            <div class="ms-3">
                                <h6>Service Client</h6>
                                <a href="{{ route('contact') }}" class="text-primary">Contactez-nous <i
                                        class="fa fa-arrow-right ms-1"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection