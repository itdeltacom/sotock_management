@extends('site.layouts.app')

@section('title', 'Cental - Session Expirée (419)')
@section('meta_description', 'Votre session a expiré. Veuillez rafraîchir la page et réessayer. Cental à Casablanca pour vos locations de voitures au Maroc.')
@section('meta_keywords', 'erreur 419, session expirée, location voiture Casablanca, Cental Maroc')

@section('og_title', 'Session Expirée - Cental Location de Voitures')
@section('og_description', 'Votre session a expiré. Veuillez rafraîchir la page et réessayer.')
@section('og_image', asset('site/img/carousel-1.jpg'))

@section('content')
    <!-- Dynamic Breadcrumb -->
    <div class="container-fluid bg-breadcrumb bg-secondary">
        <div class="container text-center py-5" style="max-width: 900px;">
            <h4 class="text-white display-4 mb-4 wow fadeInDown" data-wow-delay="0.1s">
                Session Expirée
            </h4>
            <ol class="breadcrumb d-flex justify-content-center mb-0 wow fadeInDown" data-wow-delay="0.3s">
                <li class="breadcrumb-item">
                    <a href="{{ route('home') }}">Accueil</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="#">Erreurs</a>
                </li>
                <li class="breadcrumb-item active text-primary">
                    Erreur 419
                </li>
            </ol>
        </div>
    </div>

    <!-- 419 Error Content -->
    <div class="container-fluid contact py-5">
        <div class="container py-5">
            <div class="text-center mx-auto pb-5 wow fadeInUp" data-wow-delay="0.1s" style="max-width: 800px;">
                <h1 class="display-5 text-capitalize text-primary mb-3">Session Expirée</h1>
                <p class="mb-0">Votre session a expiré pour des raisons de sécurité. Veuillez rafraîchir la page et
                    réessayer.</p>
            </div>
            <div class="row g-5 justify-content-center">
                <div class="col-lg-8 col-xl-6 wow fadeInUp" data-wow-delay="0.1s">
                    <div class="bg-light p-5 rounded text-center">
                        <i class="bi bi-hourglass-split display-1 text-primary mb-4"></i>
                        <h1 class="display-1">419</h1>
                        <h4 class="mb-4">Page Expirée</h4>
                        <p class="mb-4">La page a expiré pour des raisons de sécurité. Cela peut se produire si vous êtes
                            resté trop longtemps sur un formulaire ou si votre session est terminée.</p>
                        <div class="d-flex justify-content-center gap-3">
                            <a class="btn btn-primary rounded-pill py-3 px-5" href="{{ url()->current() }}">Rafraîchir la
                                Page</a>
                            <a class="btn btn-outline-primary rounded-pill py-3 px-5" href="{{ route('home') }}">Retour à
                                l'Accueil</a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-8 col-xl-5 wow fadeInUp" data-wow-delay="0.3s">
                    <div class="bg-secondary p-5 rounded">
                        <h4 class="text-primary mb-4">Que Faire?</h4>
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-shrink-0 btn-square bg-light rounded-circle"
                                style="width: 50px; height: 50px;">
                                <i class="fa fa-redo-alt text-primary"></i>
                            </div>
                            <div class="ms-3">
                                <h6>Rafraîchir la Page</h6>
                                <p class="mb-0">Actualisez votre navigateur et réessayez</p>
                            </div>
                        </div>
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-shrink-0 btn-square bg-light rounded-circle"
                                style="width: 50px; height: 50px;">
                                <i class="fa fa-cookie-bite text-primary"></i>
                            </div>
                            <div class="ms-3">
                                <h6>Vérifier vos Cookies</h6>
                                <p class="mb-0">Assurez-vous que les cookies sont activés</p>
                            </div>
                        </div>
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-shrink-0 btn-square bg-light rounded-circle"
                                style="width: 50px; height: 50px;">
                                <i class="fa fa-sign-in-alt text-primary"></i>
                            </div>
                            <div class="ms-3">
                                <h6>Se Reconnecter</h6>
                                <p class="mb-0">Connectez-vous à nouveau si nécessaire</p>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0 btn-square bg-light rounded-circle"
                                style="width: 50px; height: 50px;">
                                <i class="fa fa-headset text-primary"></i>
                            </div>
                            <div class="ms-3">
                                <h6>Besoin d'Aide?</h6>
                                <p class="mb-0">+212 5 22 XX XX XX</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection