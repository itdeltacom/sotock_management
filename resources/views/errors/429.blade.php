@extends('site.layouts.app')

@section('title', 'Cental - Trop de Requêtes (429)')
@section('meta_description', 'Trop de requêtes ont été effectuées. Veuillez patienter et réessayer. Cental à Casablanca pour vos locations de voitures au Maroc.')
@section('meta_keywords', 'erreur 429, trop de requêtes, location voiture Casablanca, Cental Maroc')

@section('og_title', 'Trop de Requêtes - Cental Location de Voitures')
@section('og_description', 'Trop de requêtes ont été effectuées. Veuillez patienter et réessayer.')
@section('og_image', asset('site/img/carousel-1.jpg'))

@section('content')
    <!-- Dynamic Breadcrumb -->
    <div class="container-fluid bg-breadcrumb bg-secondary">
        <div class="container text-center py-5" style="max-width: 900px;">
            <h4 class="text-white display-4 mb-4 wow fadeInDown" data-wow-delay="0.1s">
                Trop de Requêtes
            </h4>
            <ol class="breadcrumb d-flex justify-content-center mb-0 wow fadeInDown" data-wow-delay="0.3s">
                <li class="breadcrumb-item">
                    <a href="{{ route('home') }}">Accueil</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="#">Erreurs</a>
                </li>
                <li class="breadcrumb-item active text-primary">
                    Erreur 429
                </li>
            </ol>
        </div>
    </div>

    <!-- 429 Error Content -->
    <div class="container-fluid contact py-5">
        <div class="container py-5">
            <div class="text-center mx-auto pb-5 wow fadeInUp" data-wow-delay="0.1s" style="max-width: 800px;">
                <h1 class="display-5 text-capitalize text-primary mb-3">Trop de Requêtes</h1>
                <p class="mb-0">Vous avez effectué trop de requêtes en peu de temps. Veuillez patienter quelques instants
                    avant de réessayer.</p>
            </div>
            <div class="row g-5 justify-content-center">
                <div class="col-lg-8 col-xl-6 wow fadeInUp" data-wow-delay="0.1s">
                    <div class="bg-light p-5 rounded text-center">
                        <i class="bi bi-speedometer2 display-1 text-primary mb-4"></i>
                        <h1 class="display-1">429</h1>
                        <h4 class="mb-4">Limite de Taux Dépassée</h4>
                        <p class="mb-4">Nos systèmes ont détecté un nombre inhabituel de requêtes depuis votre adresse. Pour
                            protéger nos services, nous avons temporairement limité vos demandes.</p>
                        <div class="countdown-container mb-4">
                            <div class="text-primary mb-2">Veuillez réessayer dans</div>
                            <div class="countdown-timer d-flex justify-content-center">
                                <div class="bg-secondary rounded px-3 py-2 mx-1">
                                    <span id="countdown-minutes">02</span>
                                    <small>minutes</small>
                                </div>
                                <div class="bg-secondary rounded px-3 py-2 mx-1">
                                    <span id="countdown-seconds">00</span>
                                    <small>secondes</small>
                                </div>
                            </div>
                        </div>
                        <a class="btn btn-primary rounded-pill py-3 px-5" href="{{ route('home') }}">Retour à l'Accueil</a>
                    </div>
                </div>
                <div class="col-lg-8 col-xl-5 wow fadeInUp" data-wow-delay="0.3s">
                    <div class="bg-secondary p-5 rounded">
                        <h4 class="text-primary mb-4">Pourquoi Cela Arrive-t-il?</h4>
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-shrink-0 btn-square bg-light rounded-circle"
                                style="width: 50px; height: 50px;">
                                <i class="fa fa-mouse text-primary"></i>
                            </div>
                            <div class="ms-3">
                                <h6>Trop de Clics</h6>
                                <p class="mb-0">Vous avez peut-être cliqué trop rapidement sur plusieurs liens</p>
                            </div>
                        </div>
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-shrink-0 btn-square bg-light rounded-circle"
                                style="width: 50px; height: 50px;">
                                <i class="fa fa-sync-alt text-primary"></i>
                            </div>
                            <div class="ms-3">
                                <h6>Rafraîchissements Fréquents</h6>
                                <p class="mb-0">Trop de rafraîchissements de page en peu de temps</p>
                            </div>
                        </div>
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-shrink-0 btn-square bg-light rounded-circle"
                                style="width: 50px; height: 50px;">
                                <i class="fa fa-robot text-primary"></i>
                            </div>
                            <div class="ms-3">
                                <h6>Comportement Automatisé</h6>
                                <p class="mb-0">Nos systèmes ont détecté un comportement similaire à un bot</p>
                            </div>
                        </div>
                        <div class="mt-4">
                            <h6>Besoin d'Assistance Immédiate?</h6>
                            <p><i class="fa fa-phone-alt me-2 text-primary"></i>+212 5 22 XX XX XX</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Simple countdown timer
        document.addEventListener('DOMContentLoaded', function () {
            let minutes = 2;
            let seconds = 0;

            const minutesDisplay = document.getElementById('countdown-minutes');
            const secondsDisplay = document.getElementById('countdown-seconds');

            const countdown = setInterval(function () {
                if (seconds > 0) {
                    seconds--;
                } else {
                    if (minutes > 0) {
                        minutes--;
                        seconds = 59;
                    } else {
                        clearInterval(countdown);
                        location.reload();
                        return;
                    }
                }

                minutesDisplay.textContent = minutes.toString().padStart(2, '0');
                secondsDisplay.textContent = seconds.toString().padStart(2, '0');
            }, 1000);
        });
    </script>
@endsection