<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>@yield('title', 'Cental - Premium Car Rental Services')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- SEO Meta Tags -->
    <meta name="description"
        content="@yield('meta_description', 'Cental offers premium car rental services with a wide selection of vehicles at competitive prices. Easy booking, 24/7 support, and free pick-up services.')">
    <meta name="keywords"
        content="@yield('meta_keywords', 'car rental, vehicle hire, premium cars, rent a car, cheap car rental, luxury cars, car booking')">
    <meta name="author" content="Cental Car Rentals">
    <meta name="robots" content="index, follow">
    <meta name="language" content="English">

    <!-- Canonical URL -->
    <link rel="canonical" href="{{ url()->current() }}">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="@yield('og_title', 'Cental - Premium Car Rental Services')">
    <meta property="og:description"
        content="@yield('og_description', 'Cental offers premium car rental services with a wide selection of vehicles at competitive prices. Easy booking, 24/7 support, and free pick-up services.')">
    <meta property="og:image" content="@yield('og_image', asset('site/img/carousel-1.jpg'))">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ url()->current() }}">
    <meta property="twitter:title" content="@yield('twitter_title', 'Cental - Premium Car Rental Services')">
    <meta property="twitter:description"
        content="@yield('twitter_description', 'Cental offers premium car rental services with a wide selection of vehicles at competitive prices.')">
    <meta property="twitter:image" content="@yield('twitter_image', asset('site/img/carousel-1.jpg'))">

    <meta property="og:site_name" content="Cental Car Rentals">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="en_US">
    <meta name="twitter:creator" content="@centralcarrental">
    <meta name="twitter:site" content="@centralcarrental">

    <link rel="icon" type="image/png" href="{{ asset('site/img/favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('site/img/apple-touch-icon.png') }}">
    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Lato:ital,wght@0,400;0,700;0,900;1,400;1,700;1,900&family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">

    <link
        href="https://fonts.googleapis.com/css2?family=Lato:ital,wght@0,400;0,700;0,900;1,400;1,700;1,900&family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">

    <!-- Icon Font Stylesheet -->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="{{asset('site/lib/animate/animate.min.css')}}" rel="stylesheet">
    <link href="{{asset('site/lib/owlcarousel/assets/owl.carousel.min.css')}}" rel="stylesheet">


    <!-- Customized Bootstrap Stylesheet -->
    <link href="{{asset('site/css/bootstrap.min.css')}}" rel="stylesheet">

    <!-- Template Stylesheet -->
    <link href="{{asset('site/css/style.css')}}" rel="stylesheet">
</head>

<body>

    <!-- Spinner Start -->
    <div id="spinner"
        class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
            <span class="sr-only">Loading...</span>
        </div>
    </div>
    <!-- Spinner End -->

    <!-- Topbar Start -->
    @include('site.includes.header')
    <!-- Navbar & Hero End -->
    @yield('content')
    <!-- Footer Start -->
    @include('site.layouts.footer')

    <!-- JavaScript Libraries -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{asset('site/lib/wow/wow.min.js')}}"></script>
    <script src="{{asset('site/lib/easing/easing.min.js')}}"></script>
    <script src="{{asset('site/lib/waypoints/waypoints.min.js')}}"></script>
    <script src="{{asset('site/lib/counterup/counterup.min.js')}}"></script>
    <script src="{{asset('site/lib/owlcarousel/owl.carousel.min.js')}}"></script>


    <!-- Template Javascript -->
    <script src="{{asset('site/js/main.js')}}"></script>
</body>

</html>