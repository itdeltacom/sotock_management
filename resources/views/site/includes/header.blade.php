<!-- Topbar Start -->
<div class="container-fluid topbar bg-secondary d-none d-xl-block w-100">
    <div class="container">
        <div class="row gx-0 align-items-center" style="height: 45px;">
            <div class="col-lg-6 text-center text-lg-start mb-lg-0">
                <div class="d-flex flex-wrap">
                    <a href="{{ route('home') }}" class="text-muted me-4">
                        <i class="fas fa-map-marker-alt text-primary me-2"></i>Find A Location
                    </a>
                    <a href="tel:+01234567890" class="text-muted me-4">
                        <i class="fas fa-phone-alt text-primary me-2"></i>+01234567890
                    </a>
                    <a href="mailto:example@gmail.com" class="text-muted me-0">
                        <i class="fas fa-envelope text-primary me-2"></i>Example@gmail.com
                    </a>
                </div>
            </div>
            <div class="col-lg-6 text-center text-lg-end">
                <div class="d-flex align-items-center justify-content-end">
                    <!-- User Authentication Links -->
                    @auth
                        <div class="dropdown me-4">
                            <a class="text-white dropdown-toggle d-flex align-items-center" href="#" role="button" id="userDropdown"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                @if(Auth::user()->photo)
                                    <img src="{{ asset('storage/' . Auth::user()->photo) }}" alt="{{ Auth::user()->name }}" 
                                         class="rounded-circle me-2" width="28" height="28">
                                @else
                                    <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-2" 
                                         style="width: 28px; height: 28px;">
                                        <i class="fas fa-user text-white" style="font-size: 14px;"></i>
                                    </div>
                                @endif
                                <span class="d-none d-sm-inline">{{ Auth::user()->name }}</span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" 
                                style="min-width: 240px; border-radius: 10px; margin-top: 10px; z-index: 2000;" aria-labelledby="userDropdown">
                                <li class="px-3 py-2 bg-primary rounded-top" style="border-radius: 10px 10px 0 0;">
                                    <div class="d-flex align-items-center">
                                        @if(Auth::user()->photo)
                                            <img src="{{ asset('storage/' . Auth::user()->photo) }}" alt="{{ Auth::user()->name }}" 
                                                 class="rounded-circle me-2" width="40" height="40">
                                        @else
                                            <div class="bg-light rounded-circle d-flex align-items-center justify-content-center me-2" 
                                                 style="width: 40px; height: 40px;">
                                                <i class="fas fa-user text-primary"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <h6 class="mb-0 text-white">{{ Auth::user()->name }}</h6>
                                            <small class="text-light">{{ Auth::user()->email }}</small>
                                        </div>
                                    </div>
                                </li>
                                
                                <li><a class="dropdown-item py-2" href="{{ route('client.dashboard') }}">
                                    <i class="fas fa-tachometer-alt me-2 text-primary"></i>Tableau de Bord
                                </a></li>
                                
                                <li><a class="dropdown-item py-2" href="{{ route('client.profile') }}">
                                    <i class="fas fa-user-edit me-2 text-primary"></i>Mon Profil
                                </a></li>
                                
                                <li><a class="dropdown-item py-2" href="{{ route('client.bookings') }}">
                                    <i class="fas fa-calendar-check me-2 text-primary"></i>Mes Réservations
                                </a></li>
                                
                                <li><a class="dropdown-item py-2" href="{{ route('cars.index') }}">
                                    <i class="fas fa-car me-2 text-primary"></i>Réserver une Voiture
                                </a></li>
                                
                                <li>
                                    <hr class="dropdown-divider my-1">
                                </li>
                                
                                <li><a class="dropdown-item py-2" href="{{ route('faq') }}">
                                    <i class="fas fa-question-circle me-2 text-primary"></i>Aide & FAQ
                                </a></li>
                                
                                <li><a class="dropdown-item py-2" href="{{ route('contact') }}">
                                    <i class="fas fa-headset me-2 text-primary"></i>Contacter Support
                                </a></li>
                                
                                <li>
                                    <hr class="dropdown-divider my-1">
                                </li>
                                
                                <li>
                                    <form id="logout-form" action="{{ route('logout') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="dropdown-item py-2 text-danger">
                                            <i class="fas fa-sign-out-alt me-2"></i>Déconnexion
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    @else
                        <a href="{{ route('login-register') }}" class="btn btn-primary btn-sm rounded-pill px-3 py-1 me-4">
                            <i class="fas fa-user me-2"></i>Connexion / Inscription
                        </a>
                    @endauth

                    <!-- Social Media Links -->
                    <a href="#" class="btn btn-light btn-sm-square rounded-circle me-3">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="#" class="btn btn-light btn-sm-square rounded-circle me-3">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="#" class="btn btn-light btn-sm-square rounded-circle me-3">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="#" class="btn btn-light btn-sm-square rounded-circle me-0">
                        <i class="fab fa-linkedin-in"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Topbar End -->

<!-- Navbar & Hero Start -->
<div class="container-fluid nav-bar sticky-top px-0 px-lg-4 py-2 py-lg-0">
    <div class="container">
        <nav class="navbar navbar-expand-lg navbar-light">
            <a href="{{ route('home') }}" class="navbar-brand p-0">
                <img src="{{asset('site/img/logo.png')}}" alt="Logo " class="logo-header">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
                <span class="fa fa-bars"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarCollapse">
                <div class="navbar-nav mx-auto py-0">
                    <a href="{{ route('home') }}"
                        class="nav-item nav-link {{ request()->routeIs('home') ? 'active' : '' }}">Home</a>
                    <a href="{{ route('about') }}"
                        class="nav-item nav-link {{ request()->routeIs('about') ? 'active' : '' }}">About</a>

                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle {{ 
                            request()->routeIs('cars.*') ||
    request()->routeIs('categories.*') ? 'active' : '' 
                        }}" data-bs-toggle="dropdown">Cars</a>
                        <div class="dropdown-menu m-0">
                            <a href="{{ route('cars.index') }}"
                                class="dropdown-item {{ request()->routeIs('cars.index') ? 'active' : '' }}">Our
                                Cars</a>
                            <a href="{{ route('categories.index') }}"
                                class="dropdown-item {{ request()->routeIs('categories.index') ? 'active' : '' }}">Car
                                Categories</a>
                        </div>
                    </div>

                    <a href="/blogs" class="nav-item nav-link {{ request()->is('blogs*') ? 'active' : '' }}">Blog</a>

                    <a href="{{ route('contact') }}"
                        class="nav-item nav-link {{ request()->routeIs('contact') ? 'active' : '' }}">Contact</a>

                    <!-- Mobile view authentication link (visible only on small screens) -->
                    @auth
                        <div class="nav-item dropdown d-lg-none">
                            <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="fas fa-user-circle me-1"></i> {{ Auth::user()->name }}
                            </a>
                            <div class="dropdown-menu dropdown-menu-end m-0 border-0 shadow-sm" style="border-radius: 10px;">
                                <div class="px-3 py-2 bg-primary" style="border-radius: 10px 10px 0 0;">
                                    <h6 class="mb-0 text-white">{{ Auth::user()->name }}</h6>
                                    <small class="text-light">{{ Auth::user()->email }}</small>
                                </div>
                                <a href="{{ route('client.dashboard') }}" class="dropdown-item py-2">
                                    <i class="fas fa-tachometer-alt me-2 text-primary"></i>Tableau de Bord
                                </a>
                                <a href="{{ route('client.profile') }}" class="dropdown-item py-2">
                                    <i class="fas fa-user-edit me-2 text-primary"></i>Mon Profil
                                </a>
                                <a href="{{ route('client.bookings') }}" class="dropdown-item py-2">
                                    <i class="fas fa-calendar-check me-2 text-primary"></i>Mes Réservations
                                </a>
                                <a href="{{ route('cars.index') }}" class="dropdown-item py-2">
                                    <i class="fas fa-car me-2 text-primary"></i>Réserver une Voiture
                                </a>
                                <div class="dropdown-divider"></div>
                                <a href="{{ route('faq') }}" class="dropdown-item py-2">
                                    <i class="fas fa-question-circle me-2 text-primary"></i>Aide & FAQ
                                </a>
                                <a href="{{ route('contact') }}" class="dropdown-item py-2">
                                    <i class="fas fa-headset me-2 text-primary"></i>Contacter Support
                                </a>
                                <div class="dropdown-divider"></div>
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="dropdown-item py-2 text-danger">
                                        <i class="fas fa-sign-out-alt me-2"></i>Déconnexion
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('login-register') }}" class="nav-item nav-link d-lg-none">
                            <i class="fas fa-user me-1"></i> Connexion
                        </a>
                    @endauth
                </div>
                <a href="{{ route('admin.login') }}" class="btn btn-primary rounded-pill py-2 px-4">Get Started</a>
            </div>
        </nav>
    </div>
</div>
<!-- Navbar End -->