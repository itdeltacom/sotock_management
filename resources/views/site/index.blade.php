@extends('site.layouts.app')

@section('title', 'Cental - Premium Car Rental Services | Book Your Dream Car Today')
@section('meta_description', 'Cental offers premium car rental services with a wide selection of luxury vehicles including Mercedes, BMW, Tesla, and more. Enjoy 24/7 support, free pick-up, and competitive rates.')
@section('meta_keywords', 'car rental, luxury car rental, premium cars, rent a car, cheap car rental, Mercedes Benz, Tesla, BMW, Toyota')

@section('og_title', 'Premium Car Rental Services | Cental')
@section('og_description', 'Choose from our fleet of luxury vehicles. Get 15% off your rental today with free pick-up and 24/7 road assistance.')
@section('og_image', asset('site/img/carousel-1.jpg'))
@push('styles')
    <style>
        .testimonial-item {
            position: relative;
        }

        .featured-badge {
            position: absolute;
            top: 20px;
            right: 20px;
            z-index: 1;
        }

        .testimonial-inner img {
            width: 80px;
            height: 80px;
            object-fit: cover;
        }
    </style>
@endpush
@section('content')

    <!-- Carousel Start -->
    <div class="header-carousel">
        <div id="carouselId" class="carousel slide" data-bs-ride="carousel" data-bs-interval="false">
            <ol class="carousel-indicators">
                <li data-bs-target="#carouselId" data-bs-slide-to="0" class="active" aria-current="true"
                    aria-label="First slide"></li>
                <li data-bs-target="#carouselId" data-bs-slide-to="1" aria-label="Second slide"></li>
            </ol>
            <div class="carousel-inner" role="listbox">
                <div class="carousel-item active">
                    <img src="{{asset('site/img/carousel-2.jpg')}}" class="img-fluid w-100" alt="First slide" />
                    <div class="carousel-caption">
                        <div class="container py-4">
                            <div class="row g-5">
                                <div class="col-lg-6 fadeInLeft animated" data-animation="fadeInLeft" data-delay="1s"
                                    style="animation-delay: 1s;">
                                    <div class="bg-secondary rounded p-5">
                                        <h4 class="text-white mb-4">CONTINUE CAR RESERVATION</h4>
                                        <form action="{{ route('search.process') }}" method="POST">
                                            @csrf
                                            <div class="row g-3">
                                                <div class="col-12">
                                                    <select class="form-select" name="car_type"
                                                        aria-label="Select your car type">
                                                        <option selected>Select Your Car type</option>
                                                        @foreach($featuredCars as $car)
                                                            <option value="{{ $car->id }}">{{ $car->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-12">
                                                    <div class="input-group">
                                                        <div
                                                            class="d-flex align-items-center bg-light text-body rounded-start p-2">
                                                            <span class="fas fa-map-marker-alt"></span> <span
                                                                class="ms-1">Pick Up</span>
                                                        </div>
                                                        <input class="form-control" type="text" name="pickup_location"
                                                            placeholder="Enter a City or Airport"
                                                            aria-label="Enter a City or Airport">
                                                    </div>
                                                </div>
                                                <div class="col-12">
                                                    <a href="#" class="text-start text-white d-block mb-2">Need a
                                                        different drop-off location?</a>
                                                    <div class="input-group">
                                                        <div
                                                            class="d-flex align-items-center bg-light text-body rounded-start p-2">
                                                            <span class="fas fa-map-marker-alt"></span><span
                                                                class="ms-1">Drop off</span>
                                                        </div>
                                                        <input class="form-control" type="text" name="dropoff_location"
                                                            placeholder="Enter a City or Airport"
                                                            aria-label="Enter a City or Airport">
                                                    </div>
                                                </div>
                                                <div class="col-12">
                                                    <div class="input-group">
                                                        <div
                                                            class="d-flex align-items-center bg-light text-body rounded-start p-2">
                                                            <span class="fas fa-calendar-alt"></span><span class="ms-1">Pick
                                                                Up</span>
                                                        </div>
                                                        <input class="form-control" type="date" name="pickup_date">
                                                        <select class="form-select ms-3" name="pickup_time"
                                                            aria-label="Default select example">
                                                            <option selected>12:00AM</option>
                                                            <option value="1:00AM">1:00AM</option>
                                                            <option value="2:00AM">2:00AM</option>
                                                            <option value="3:00AM">3:00AM</option>
                                                            <option value="4:00AM">4:00AM</option>
                                                            <option value="5:00AM">5:00AM</option>
                                                            <option value="6:00AM">6:00AM</option>
                                                            <option value="7:00AM">7:00AM</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-12">
                                                    <div class="input-group">
                                                        <div
                                                            class="d-flex align-items-center bg-light text-body rounded-start p-2">
                                                            <span class="fas fa-calendar-alt"></span><span class="ms-1">Drop
                                                                off</span>
                                                        </div>
                                                        <input class="form-control" type="date" name="dropoff_date">
                                                        <select class="form-select ms-3" name="dropoff_time"
                                                            aria-label="Default select example">
                                                            <option selected>12:00AM</option>
                                                            <option value="1:00AM">1:00AM</option>
                                                            <option value="2:00AM">2:00AM</option>
                                                            <option value="3:00AM">3:00AM</option>
                                                            <option value="4:00AM">4:00AM</option>
                                                            <option value="5:00AM">5:00AM</option>
                                                            <option value="6:00AM">6:00AM</option>
                                                            <option value="7:00AM">7:00AM</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-12">
                                                    <button type="submit" class="btn btn-light w-100 py-2">Book Now</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <div class="col-lg-6 d-none d-lg-flex fadeInRight animated" data-animation="fadeInRight"
                                    data-delay="1s" style="animation-delay: 1s;">
                                    <div class="text-start">
                                        <h1 class="display-5 text-white">Get 15% off your rental Plan your trip now</h1>
                                        <p>Treat yourself in USA</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="carousel-item">
                    <img src="{{asset('site/img/carousel-1.jpg')}}" class="img-fluid w-100" alt="First slide" />
                    <div class="carousel-caption">
                        <div class="container py-4">
                            <div class="row g-5">
                                <div class="col-lg-6 fadeInLeft animated" data-animation="fadeInLeft" data-delay="1s"
                                    style="animation-delay: 1s;">
                                    <div class="bg-secondary rounded p-5">
                                        <h4 class="text-white mb-4">CONTINUE CAR RESERVATION</h4>
                                        <form action="{{ route('search.process') }}" method="POST">
                                            @csrf
                                            <div class="row g-3">
                                                <div class="col-12">
                                                    <select class="form-select" name="car_type"
                                                        aria-label="Select your car type">
                                                        <option selected>Select Your Car type</option>
                                                        @foreach($featuredCars as $car)
                                                            <option value="{{ $car->id }}">{{ $car->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-12">
                                                    <div class="input-group">
                                                        <div
                                                            class="d-flex align-items-center bg-light text-body rounded-start p-2">
                                                            <span class="fas fa-map-marker-alt"></span><span
                                                                class="ms-1">Pick Up</span>
                                                        </div>
                                                        <input class="form-control" type="text" name="pickup_location"
                                                            placeholder="Enter a City or Airport"
                                                            aria-label="Enter a City or Airport">
                                                    </div>
                                                </div>
                                                <div class="col-12">
                                                    <a href="#" class="text-start text-white d-block mb-2">Need a
                                                        different drop-off location?</a>
                                                    <div class="input-group">
                                                        <div
                                                            class="d-flex align-items-center bg-light text-body rounded-start p-2">
                                                            <span class="fas fa-map-marker-alt"></span><span
                                                                class="ms-1">Drop off</span>
                                                        </div>
                                                        <input class="form-control" type="text" name="dropoff_location"
                                                            placeholder="Enter a City or Airport"
                                                            aria-label="Enter a City or Airport">
                                                    </div>
                                                </div>
                                                <div class="col-12">
                                                    <div class="input-group">
                                                        <div
                                                            class="d-flex align-items-center bg-light text-body rounded-start p-2">
                                                            <span class="fas fa-calendar-alt"></span><span class="ms-1">Pick
                                                                Up</span>
                                                        </div>
                                                        <input class="form-control" type="date" name="pickup_date">
                                                        <select class="form-select ms-3" name="pickup_time"
                                                            aria-label="Default select example">
                                                            <option selected>12:00AM</option>
                                                            <option value="1:00AM">1:00AM</option>
                                                            <option value="2:00AM">2:00AM</option>
                                                            <option value="3:00AM">3:00AM</option>
                                                            <option value="4:00AM">4:00AM</option>
                                                            <option value="5:00AM">5:00AM</option>
                                                            <option value="6:00AM">6:00AM</option>
                                                            <option value="7:00AM">7:00AM</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-12">
                                                    <div class="input-group">
                                                        <div
                                                            class="d-flex align-items-center bg-light text-body rounded-start p-2">
                                                            <span class="fas fa-calendar-alt"></span><span class="ms-1">Drop
                                                                off</span>
                                                        </div>
                                                        <input class="form-control" type="date" name="dropoff_date">
                                                        <select class="form-select ms-3" name="dropoff_time"
                                                            aria-label="Default select example">
                                                            <option selected>12:00AM</option>
                                                            <option value="1:00AM">1:00AM</option>
                                                            <option value="2:00AM">2:00AM</option>
                                                            <option value="3:00AM">3:00AM</option>
                                                            <option value="4:00AM">4:00AM</option>
                                                            <option value="5:00AM">5:00AM</option>
                                                            <option value="6:00AM">6:00AM</option>
                                                            <option value="7:00AM">7:00AM</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-12">
                                                    <button type="submit" class="btn btn-light w-100 py-2">Book Now</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <div class="col-lg-6 d-none d-lg-flex fadeInRight animated" data-animation="fadeInRight"
                                    data-delay="1s" style="animation-delay: 1s;">
                                    <div class="text-start">
                                        <h1 class="display-5 text-white">Get 15% off your rental! Choose Your Model
                                        </h1>
                                        <p>Treat yourself in USA</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Carousel End -->

    <!-- Features Start -->
    <div class="container-fluid feature py-5">
        <div class="container py-5">
            <div class="text-center mx-auto pb-5 wow fadeInUp" data-wow-delay="0.1s" style="max-width: 800px;">
                <h1 class="display-5 text-capitalize mb-3">Cental <span class="text-primary">Features</span></h1>
                <p class="mb-0">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Ut amet nemo expedita
                    asperiores commodi accusantium at cum harum, excepturi, quia tempora cupiditate! Adipisci facilis
                    modi quisquam quia distinctio,
                </p>
            </div>
            <div class="row g-4 align-items-center">
                <div class="col-xl-4">
                    <div class="row gy-4 gx-0">
                        <div class="col-12 wow fadeInUp" data-wow-delay="0.1s">
                            <div class="feature-item">
                                <div class="feature-icon">
                                    <span class="fa fa-trophy fa-2x"></span>
                                </div>
                                <div class="ms-4">
                                    <h5 class="mb-3">First Class services</h5>
                                    <p class="mb-0">Lorem ipsum dolor sit amet consectetur adipisicing elit.
                                        Consectetur, in illum aperiam ullam magni eligendi?</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 wow fadeInUp" data-wow-delay="0.3s">
                            <div class="feature-item">
                                <div class="feature-icon">
                                    <span class="fa fa-road fa-2x"></span>
                                </div>
                                <div class="ms-4">
                                    <h5 class="mb-3">24/7 road assistance</h5>
                                    <p class="mb-0">Lorem ipsum dolor sit amet consectetur adipisicing elit.
                                        Consectetur, in illum aperiam ullam magni eligendi?</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-12 col-xl-4 wow fadeInUp" data-wow-delay="0.2s">
                    <img src="{{asset('site/img/features-img.png')}}" class="img-fluid w-100" style="object-fit: cover;"
                        alt="Img">
                </div>
                <div class="col-xl-4">
                    <div class="row gy-4 gx-0">
                        <div class="col-12 wow fadeInUp" data-wow-delay="0.1s">
                            <div class="feature-item justify-content-end">
                                <div class="text-end me-4">
                                    <h5 class="mb-3">Quality at Minimum</h5>
                                    <p class="mb-0">Lorem ipsum dolor sit amet consectetur adipisicing elit.
                                        Consectetur, in illum aperiam ullam magni eligendi?</p>
                                </div>
                                <div class="feature-icon">
                                    <span class="fa fa-tag fa-2x"></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 wow fadeInUp" data-wow-delay="0.3s">
                            <div class="feature-item justify-content-end">
                                <div class="text-end me-4">
                                    <h5 class="mb-3">Free Pick-Up & Drop-Off</h5>
                                    <p class="mb-0">Lorem ipsum dolor sit amet consectetur adipisicing elit.
                                        Consectetur, in illum aperiam ullam magni eligendi?</p>
                                </div>
                                <div class="feature-icon">
                                    <span class="fa fa-map-pin fa-2x"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Features End -->

    <!-- About Start -->
    <div class="container-fluid overflow-hidden about py-5">
        <div class="container py-5">
            <div class="row g-5">
                <div class="col-xl-6 wow fadeInLeft" data-wow-delay="0.2s">
                    <div class="about-item">
                        <div class="pb-5">
                            <h1 class="display-5 text-capitalize">Cental <span class="text-primary">About</span></h1>
                            <p class="mb-0">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Ut amet nemo
                                expedita asperiores commodi accusantium at cum harum, excepturi, quia tempora
                                cupiditate! Adipisci facilis modi quisquam quia distinctio,
                            </p>
                        </div>
                        <div class="row g-4">
                            <div class="col-lg-6">
                                <div class="about-item-inner border p-4">
                                    <div class="about-icon mb-4">
                                        <img src="{{asset('site/img/about-icon-1.png')}}" class="img-fluid w-50 h-50"
                                            alt="Icon">
                                    </div>
                                    <h5 class="mb-3">Our Vision</h5>
                                    <p class="mb-0">Lorem ipsum dolor sit amet consectetur adipisicing elit.</p>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="about-item-inner border p-4">
                                    <div class="about-icon mb-4">
                                        <img src="{{asset('site/img/about-icon-2.png')}}" class="img-fluid h-50 w-50"
                                            alt="Icon">
                                    </div>
                                    <h5 class="mb-3">Our Mision</h5>
                                    <p class="mb-0">Lorem ipsum dolor sit amet consectetur adipisicing elit.</p>
                                </div>
                            </div>
                        </div>
                        <p class="text-item my-4">Lorem, ipsum dolor sit amet consectetur adipisicing elit. Beatae,
                            aliquam ipsum. Sed suscipit dolorem libero sequi aut natus debitis reprehenderit facilis
                            quaerat similique, est at in eum. Quo, obcaecati in!
                        </p>
                        <div class="row g-4">
                            <div class="col-lg-6">
                                <div class="text-center rounded bg-secondary p-4">
                                    <h1 class="display-6 text-white">17</h1>
                                    <h5 class="text-light mb-0">Years Of Experience</h5>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="rounded">
                                    <p class="mb-2"><i class="fa fa-check-circle text-primary me-1"></i> Morbi tristique
                                        senectus</p>
                                    <p class="mb-2"><i class="fa fa-check-circle text-primary me-1"></i> A scelerisque
                                        purus</p>
                                    <p class="mb-2"><i class="fa fa-check-circle text-primary me-1"></i> Dictumst
                                        vestibulum</p>
                                    <p class="mb-0"><i class="fa fa-check-circle text-primary me-1"></i> dio aenean sed
                                        adipiscing</p>
                                </div>
                            </div>
                            <div class="col-lg-5 d-flex align-items-center">
                                <a href="{{ route('about') }}" class="btn btn-primary rounded py-3 px-5">More About Us</a>
                            </div>
                            <div class="col-lg-7">
                                <div class="d-flex align-items-center">
                                    <img src="{{asset('site/img/attachment-img.jpg')}}"
                                        class="img-fluid rounded-circle border border-4 border-secondary"
                                        style="width: 100px; height: 100px;" alt="Image">
                                    <div class="ms-4">
                                        <h4>William Burgess</h4>
                                        <p class="mb-0">Carveo Founder</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-6 wow fadeInRight" data-wow-delay="0.2s">
                    <div class="about-img">
                        <div class="img-1">
                            <img src="{{asset('site/img/about-img.jpg')}}" class="img-fluid rounded h-100 w-100" alt="">
                        </div>
                        <div class="img-2">
                            <img src="{{asset('site/img/about-img-1.jpg')}}" class="img-fluid rounded w-100" alt="">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- About End -->

    <!-- Fact Counter -->
    <div class="container-fluid counter bg-secondary py-5">
        <div class="container py-5">
            <div class="row g-5">
                <div class="col-md-6 col-lg-6 col-xl-3 wow fadeInUp" data-wow-delay="0.1s">
                    <div class="counter-item text-center">
                        <div class="counter-item-icon mx-auto">
                            <i class="fas fa-thumbs-up fa-2x"></i>
                        </div>
                        <div class="counter-counting my-3">
                            <span class="text-white fs-2 fw-bold"
                                data-toggle="counter-up">{{ $stats['happy_clients'] }}</span>
                            <span class="h1 fw-bold text-white">+</span>
                        </div>
                        <h4 class="text-white mb-0">Happy Clients</h4>
                    </div>
                </div>
                <div class="col-md-6 col-lg-6 col-xl-3 wow fadeInUp" data-wow-delay="0.3s">
                    <div class="counter-item text-center">
                        <div class="counter-item-icon mx-auto">
                            <i class="fas fa-car-alt fa-2x"></i>
                        </div>
                        <div class="counter-counting my-3">
                            <span class="text-white fs-2 fw-bold" data-toggle="counter-up">{{ $stats['cars_count'] }}</span>
                            <span class="h1 fw-bold text-white">+</span>
                        </div>
                        <h4 class="text-white mb-0">Number of Cars</h4>
                    </div>
                </div>
                <div class="col-md-6 col-lg-6 col-xl-3 wow fadeInUp" data-wow-delay="0.5s">
                    <div class="counter-item text-center">
                        <div class="counter-item-icon mx-auto">
                            <i class="fas fa-building fa-2x"></i>
                        </div>
                        <div class="counter-counting my-3">
                            <span class="text-white fs-2 fw-bold"
                                data-toggle="counter-up">{{ $stats['car_centers'] }}</span>
                            <span class="h1 fw-bold text-white">+</span>
                        </div>
                        <h4 class="text-white mb-0">Car Center</h4>
                    </div>
                </div>
                <div class="col-md-6 col-lg-6 col-xl-3 wow fadeInUp" data-wow-delay="0.7s">
                    <div class="counter-item text-center">
                        <div class="counter-item-icon mx-auto">
                            <i class="fas fa-clock fa-2x"></i>
                        </div>
                        <div class="counter-counting my-3">
                            <span class="text-white fs-2 fw-bold"
                                data-toggle="counter-up">{{ $stats['total_kilometers'] }}</span>
                            <span class="h1 fw-bold text-white">+</span>
                        </div>
                        <h4 class="text-white mb-0">Total kilometers</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Fact Counter -->

    <!-- Services Start -->
    <div class="container-fluid service py-5">
        <div class="container py-5">
            <div class="text-center mx-auto pb-5 wow fadeInUp" data-wow-delay="0.1s" style="max-width: 800px;">
                <h1 class="display-5 text-capitalize mb-3">Cental <span class="text-primary">Services</span></h1>
                <p class="mb-0">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Ut amet nemo expedita
                    asperiores commodi accusantium at cum harum, excepturi, quia tempora cupiditate! Adipisci facilis
                    modi quisquam quia distinctio,
                </p>
            </div>
            <div class="row g-4">
                <div class="col-md-6 col-lg-4 wow fadeInUp" data-wow-delay="0.1s">
                    <div class="service-item p-4">
                        <div class="service-icon mb-4">
                            <i class="fa fa-phone-alt fa-2x"></i>
                        </div>
                        <h5 class="mb-3">Phone Reservation</h5>
                        <p class="mb-0">Lorem ipsum dolor sit amet consectetur adipisicing elit. Reprehenderit ipsam
                            quasi quibusdam ipsa perferendis iusto?</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4 wow fadeInUp" data-wow-delay="0.3s">
                    <div class="service-item p-4">
                        <div class="service-icon mb-4">
                            <i class="fa fa-money-bill-alt fa-2x"></i>
                        </div>
                        <h5 class="mb-3">Special Rates</h5>
                        <p class="mb-0">Lorem ipsum dolor sit amet consectetur adipisicing elit. Reprehenderit ipsam
                            quasi quibusdam ipsa perferendis iusto?</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4 wow fadeInUp" data-wow-delay="0.5s">
                    <div class="service-item p-4">
                        <div class="service-icon mb-4">
                            <i class="fa fa-road fa-2x"></i>
                        </div>
                        <h5 class="mb-3">One Way Rental</h5>
                        <p class="mb-0">Lorem ipsum dolor sit amet consectetur adipisicing elit. Reprehenderit ipsam
                            quasi quibusdam ipsa perferendis iusto?</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4 wow fadeInUp" data-wow-delay="0.1s">
                    <div class="service-item p-4">
                        <div class="service-icon mb-4">
                            <i class="fa fa-umbrella fa-2x"></i>
                        </div>
                        <h5 class="mb-3">Life Insurance</h5>
                        <p class="mb-0">Lorem ipsum dolor sit amet consectetur adipisicing elit. Reprehenderit ipsam
                            quasi quibusdam ipsa perferendis iusto?</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4 wow fadeInUp" data-wow-delay="0.3s">
                    <div class="service-item p-4">
                        <div class="service-icon mb-4">
                            <i class="fa fa-building fa-2x"></i>
                        </div>
                        <h5 class="mb-3">City to City</h5>
                        <p class="mb-0">Lorem ipsum dolor sit amet consectetur adipisicing elit. Reprehenderit ipsam
                            quasi quibusdam ipsa perferendis iusto?</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4 wow fadeInUp" data-wow-delay="0.5s">
                    <div class="service-item p-4">
                        <div class="service-icon mb-4">
                            <i class="fa fa-car-alt fa-2x"></i>
                        </div>
                        <h5 class="mb-3">Free Rides</h5>
                        <p class="mb-0">Lorem ipsum dolor sit amet consectetur adipisicing elit. Reprehenderit ipsam
                            quasi quibusdam ipsa perferendis iusto?</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Services End -->

    <!-- Car categories Start -->
    <div class="container-fluid categories pb-5">
        <div class="container pb-5">
            <div class="text-center mx-auto pb-5 wow fadeInUp" data-wow-delay="0.1s" style="max-width: 800px;">
                <h1 class="display-5 text-capitalize mb-3">Vehicle <span class="text-primary">Categories</span></h1>
                <p class="mb-0">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Ut amet nemo expedita
                    asperiores commodi accusantium at cum harum, excepturi, quia tempora cupiditate! Adipisci facilis
                    modi quisquam quia distinctio,
                </p>
            </div>
            <div class="categories-carousel owl-carousel wow fadeInUp" data-wow-delay="0.1s">
                @foreach($featuredCars as $car)
                    <div class="categories-item p-4">
                        <div class="categories-item-inner">
                            <div class="categories-img rounded-top">
                                <img src="{{ Storage::url($car->main_image) }}" class="img-fluid w-100 rounded-top"
                                    alt="{{ $car->name }}">
                            </div>
                            <div class="categories-content rounded-bottom p-4">
                                <h4>{{ $car->name }}</h4>
                                <div class="categories-review mb-4">
                                    <div class="me-3">{{ number_format($car->rating, 1) }} Review</div>
                                    <div class="d-flex justify-content-center text-secondary">
                                        @for($i = 1; $i <= 5; $i++)
                                            @if($i <= floor($car->rating))
                                                <i class="fas fa-star"></i>
                                            @else
                                                <i class="fas fa-star text-body"></i>
                                            @endif
                                        @endfor
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <h4 class="bg-white text-primary rounded-pill py-2 px-4 mb-0">
                                        {{ number_format($car->price_per_day, 2) }} MAD/Day
                                    </h4>
                                </div>
                                <div class="row gy-2 gx-0 text-center mb-4">
                                    <div class="col-4 border-end border-white">
                                        <i class="fa fa-users text-dark"></i> <span class="text-body ms-1">{{ $car->seats }}
                                            Seat</span>
                                    </div>
                                    <div class="col-4 border-end border-white">
                                        <i class="fa fa-car text-dark"></i> <span
                                            class="text-body ms-1">{{ strtoupper($car->transmission) }}</span>
                                    </div>
                                    <div class="col-4">
                                        <i class="fa fa-gas-pump text-dark"></i> <span
                                            class="text-body ms-1">{{ ucfirst($car->fuel_type) }}</span>
                                    </div>
                                    <div class="col-4 border-end border-white">
                                        <i class="fa fa-car text-dark"></i> <span class="text-body ms-1">2023</span>
                                    </div>
                                    <div class="col-4 border-end border-white">
                                        <i class="fa fa-cogs text-dark"></i> <span class="text-body ms-1">AUTO</span>
                                    </div>
                                    <div class="col-4">
                                        <i class="fa fa-road text-dark"></i> <span
                                            class="text-body ms-1">{{ $car->mileage ?? '0' }}K</span>
                                    </div>
                                </div>
                                <a href="{{ route('cars.show', $car->slug) }}"
                                    class="btn btn-primary rounded-pill d-flex justify-content-center py-3">Book
                                    Now</a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    <!-- Car categories End -->

    <!-- Car Steps Start -->
    <div class="container-fluid steps py-5">
        <div class="container py-5">
            <div class="text-center mx-auto pb-5 wow fadeInUp" data-wow-delay="0.1s" style="max-width: 800px;">
                <h1 class="display-5 text-capitalize text-white mb-3">Cental<span class="text-primary"> Process</span>
                </h1>
                <p class="mb-0 text-white">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Ut amet nemo
                    expedita asperiores commodi accusantium at cum harum, excepturi, quia tempora cupiditate! Adipisci
                    facilis modi quisquam quia distinctio,
                </p>
            </div>
            <div class="row g-4">
                <div class="col-lg-4 wow fadeInUp" data-wow-delay="0.1s">
                    <div class="steps-item p-4 mb-4">
                        <h4>Come In Contact</h4>
                        <p class="mb-0">Lorem ipsum dolor sit amet consectetur adipisicing elit. Ad, dolorem!</p>
                        <div class="setps-number">01.</div>
                    </div>
                </div>
                <div class="col-lg-4 wow fadeInUp" data-wow-delay="0.3s">
                    <div class="steps-item p-4 mb-4">
                        <h4>Choose A Car</h4>
                        <p class="mb-0">Lorem ipsum dolor sit amet consectetur adipisicing elit. Ad, dolorem!</p>
                        <div class="setps-number">02.</div>
                    </div>
                </div>
                <div class="col-lg-4 wow fadeInUp" data-wow-delay="0.5s">
                    <div class="steps-item p-4 mb-4">
                        <h4>Enjoy Driving</h4>
                        <p class="mb-0">Lorem ipsum dolor sit amet consectetur adipisicing elit. Ad, dolorem!</p>
                        <div class="setps-number">03.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Car Steps End -->

    <!-- Blog Start -->
    <div class="container-fluid blog py-5">
        <div class="container py-5">
            <div class="text-center mx-auto pb-5 wow fadeInUp" data-wow-delay="0.1s" style="max-width: 800px;">
                <h1 class="display-5 text-capitalize mb-3">Cental<span class="text-primary"> Blog & News</span></h1>
                <p class="mb-0">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Ut amet nemo expedita
                    asperiores commodi accusantium at cum harum, excepturi, quia tempora cupiditate! Adipisci facilis
                    modi quisquam quia distinctio,
                </p>
            </div>
            <div class="row g-4">
                @foreach($latestPosts as $index => $post)
                    <div class="col-lg-4 wow fadeInUp" data-wow-delay="{{ 0.1 + $index * 0.2 }}s">
                        <div class="blog-item">
                            <div class="blog-img">
                                @if(isset($post->featured_image_url))
                                    <img src="{{ $post->featured_image_url }}" class="img-fluid rounded-top w-100" alt="Image">
                                @elseif(isset($post->featured_image))
                                    <img src="{{ Storage::url($post->featured_image) }}" class="img-fluid rounded-top w-100"
                                        alt="Image">
                                @else
                                    <img src="{{ asset('site/img/blog-' . ($index + 1) . '.jpg') }}"
                                        class="img-fluid rounded-top w-100" alt="Image">
                                @endif
                            </div>
                            <div class="blog-content rounded-bottom p-4">
                                <div class="blog-date">
                                    @if(isset($post->published_at) && $post->published_at instanceof \Carbon\Carbon)
                                        {{ $post->published_at->format('d M Y') }}
                                    @elseif(isset($post->published_at))
                                        {{ \Carbon\Carbon::parse($post->published_at)->format('d M Y') }}
                                    @else
                                        {{ now()->format('d M Y') }}
                                    @endif
                                </div>
                                <div class="blog-comment my-3">
                                    <div class="small"><span class="fa fa-user text-primary"></span><span
                                            class="ms-2">{{ $post->author->name ?? $post->author ?? 'Admin' }}</span></div>
                                    <div class="small"><span class="fa fa-comment-alt text-primary"></span><span class="ms-2">
                                            {{ isset($post->allComments) ? $post->allComments->count() : ($post->comments_count ?? 0) }}
                                            Comments</span></div>
                                </div>
                                <a href="{{ isset($post->url) ? $post->url : (isset($post->slug) ? url('blogs/' . $post->slug) : '#') }}"
                                    class="h4 d-block mb-3">
                                    {{ $post->title }}
                                </a>
                                <p class="mb-3">
                                    @if(isset($post->excerpt))
                                        {{ $post->excerpt }}
                                    @elseif(isset($post->content))
                                        {{ \Illuminate\Support\Str::limit(strip_tags($post->content), 100) }}
                                    @else
                                        Lorem ipsum dolor sit amet consectetur adipisicing elit. Eius libero soluta impedit
                                        eligendi?
                                    @endif
                                </p>
                                <a href="{{ isset($post->url) ? $post->url : (isset($post->slug) ? url('blogs/' . $post->slug) : '#') }}"
                                    class="">Read More <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    <!-- Blog End -->

    <!-- Banner Start -->
    @include('site.includes.banner')
    <!-- Banner End -->

    <!-- Team Start -->
    <div class="container-fluid team pb-5">
        <div class="container pb-5">
            <div class="text-center mx-auto pb-5 wow fadeInUp" data-wow-delay="0.1s" style="max-width: 800px;">
                <h1 class="display-5 text-capitalize mb-3">Customer<span class="text-primary"> Suport</span> Center</h1>
                <p class="mb-0">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Ut amet nemo expedita
                    asperiores commodi accusantium at cum harum, excepturi, quia tempora cupiditate! Adipisci facilis
                    modi quisquam quia distinctio,
                </p>
            </div>
            <div class="row g-4">
                @for($i = 1; $i <= 4; $i++)
                    <div class="col-md-6 col-lg-6 col-xl-3 wow fadeInUp" data-wow-delay="{{ 0.1 + ($i - 1) * 0.2 }}s">
                        <div class="team-item p-4 pt-0">
                            <div class="team-img">
                                <img src="{{asset('site/img/team-' . $i . '.jpg')}}" class="img-fluid rounded w-100"
                                    alt="Team Member">
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
                @endfor
            </div>
        </div>
    </div>
    <!-- Team End -->

    <!-- Testimonial Start -->
    <!-- Testimonial Start -->
    <div class="container-fluid testimonial pb-5">
        <div class="container pb-5">
            <div class="text-center mx-auto pb-5 wow fadeInUp" data-wow-delay="0.1s" style="max-width: 800px;">
                <h1 class="display-5 text-capitalize mb-3">Our Clients<span class="text-primary"> Reviews</span></h1>
                <p class="mb-0">Don't just take our word for it! See what our satisfied customers have to say about their
                    experience with Cental Car Rental. We're committed to providing exceptional service with every rental.
                </p>
            </div>
            <div class="owl-carousel testimonial-carousel wow fadeInUp" data-wow-delay="0.1s">
                @foreach($latestReviews as $review)
                    <div class="testimonial-item">
                        <div class="testimonial-quote"><i class="fa fa-quote-right fa-2x"></i>
                        </div>
                        <div class="testimonial-inner p-4">
                            @if($review->image)
                                <img src="{{ Storage::url($review->image) }}" class="img-fluid rounded-circle"
                                    alt="{{ $review->user_name }}">
                            @else
                                <img src="{{ asset('site/img/testimonial-' . (($loop->index % 4) + 1) . '.jpg') }}"
                                    class="img-fluid rounded-circle" alt="{{ $review->user_name }}">
                            @endif
                            <div class="ms-4">
                                <h4>{{ $review->user_name }}</h4>
                                <p>{{ $review->user_title ?: 'Customer' }}</p>
                                <div class="d-flex text-primary">
                                    @for($i = 1; $i <= 5; $i++)
                                        @if($i <= $review->rating)
                                            <i class="fas fa-star"></i>
                                        @else
                                            <i class="fas fa-star text-body"></i>
                                        @endif
                                    @endfor
                                </div>
                            </div>
                        </div>
                        <div class="border-top rounded-bottom p-4">
                            <p class="mb-0">{{ $review->content }}</p>
                        </div>
                        @if($review->is_featured)
                            <div class="featured-badge">
                                <span class="badge bg-primary rounded-pill py-1 px-3">
                                    <i class="fas fa-star me-1"></i> Featured
                                </span>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
            <div class="text-center mt-4 wow fadeInUp" data-wow-delay="0.3s">
                <a href="{{ route('testimonials') }}" class="btn btn-primary py-3 px-5 rounded-pill">View All Reviews</a>
                <button type="button" class="btn btn-outline-primary py-3 px-5 rounded-pill ms-3" data-bs-toggle="modal"
                    data-bs-target="#testimonialModal">
                    <i class="fas fa-comment-alt me-2"></i>Share Your Experience
                </button>
            </div>
        </div>
    </div>
    <!-- Testimonial End -->

    <!-- Testimonial Modal -->
    <div class="modal fade" id="testimonialModal" tabindex="-1" aria-labelledby="testimonialModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="testimonialModalLabel">Share Your Experience</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="testimonialForm">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="name" name="name" placeholder="Your Name"
                                        required>
                                    <label for="name">Your Name</label>
                                    <div class="invalid-feedback" id="name-error"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="email" class="form-control" id="email" name="email"
                                        placeholder="Your Email" required>
                                    <label for="email">Your Email</label>
                                    <div class="invalid-feedback" id="email-error"></div>
                                </div>
                                <small class="text-muted">Your email will not be published</small>
                            </div>
                            <div class="col-12">
                                <div class="mb-2">Your Rating</div>
                                <div class="rating-selector d-flex align-items-center">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="rating" id="rating1" value="1">
                                        <label class="form-check-label" for="rating1">1 <i
                                                class="fas fa-star text-warning"></i></label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="rating" id="rating2" value="2">
                                        <label class="form-check-label" for="rating2">2 <i
                                                class="fas fa-star text-warning"></i></label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="rating" id="rating3" value="3">
                                        <label class="form-check-label" for="rating3">3 <i
                                                class="fas fa-star text-warning"></i></label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="rating" id="rating4" value="4">
                                        <label class="form-check-label" for="rating4">4 <i
                                                class="fas fa-star text-warning"></i></label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="rating" id="rating5" value="5"
                                            checked>
                                        <label class="form-check-label" for="rating5">5 <i
                                                class="fas fa-star text-warning"></i></label>
                                    </div>
                                </div>
                                <div class="invalid-feedback d-block" id="rating-error"></div>
                            </div>
                            <div class="col-12">
                                <div class="form-floating">
                                    <textarea class="form-control" placeholder="Share your experience with us" id="content"
                                        name="content" style="height: 150px" required></textarea>
                                    <label for="content">Your Testimonial</label>
                                    <div class="invalid-feedback" id="content-error"></div>
                                </div>
                            </div>
                            <div class="col-12 mb-3">
                                <!-- Google reCAPTCHA -->
                                <div class="g-recaptcha" data-sitekey="{{ env('RECAPTCHA_SITE_KEY') }}"></div>
                                <div class="invalid-feedback d-block" id="g-recaptcha-response-error"></div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="submitTestimonial">
                        <i class="fas fa-paper-plane me-2"></i>Submit Testimonial
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Structured Data for Car Rental Service -->
    <script type="application/ld+json">
                                                                    {
                                                                      "@context": "https://schema.org",
                                                                      "@type": "CarRentalBusiness",
                                                                      "name": "Cental Car Rental",
                                                                      "image": "{{asset('site/img/carousel-1.jpg')}}",
                                                                      "address": {
                                                                        "@type": "PostalAddress",
                                                                        "streetAddress": "123 Main Street",
                                                                        "addressLocality": "New York",
                                                                        "addressRegion": "NY",
                                                                        "postalCode": "10001",
                                                                        "addressCountry": "US"
                                                                      },
                                                                      "geo": {
                                                                        "@type": "GeoCoordinates",
                                                                        "latitude": "40.7128",
                                                                        "longitude": "-74.0060"
                                                                      },
                                                                      "url": "{{url('/')}}",
                                                                      "telephone": "+1-234-567-8901",
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
                                                                      "priceRange": "$99 - $187",
                                                                      "aggregateRating": {
                                                                        "@type": "AggregateRating",
                                                                        "ratingValue": "4.5",
                                                                        "reviewCount": "{{ $stats['happy_clients'] }}"
                                                                      }
                                                                    }
                                                                    </script>
    <script type="application/ld+json">
                                                                        {
                                                                            "@context": "https://schema.org",
                                                                            "@type": "Service",
                                                                            "serviceType": "Car Rental",
                                                                            "provider": {
                                                                                "@type": "Organization",
                                                                                "name": "Cental Car Rental"
                                                                            },
                                                                            "areaServed": "USA",
                                                                            "offers": {
                                                                                "@type": "Offer",
                                                                                "priceCurrency": "USD",
                                                                                "priceRange": "$99 - $187"
                                                                            }
                                                                        }
                                                                        </script>
@endsection

@push('scripts')
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function () {
            // Submit testimonial form via AJAX
            $('#submitTestimonial').on('click', function () {
                // Clear previous errors
                $('.is-invalid').removeClass('is-invalid');
                $('.invalid-feedback').text('');

                // Get form data
                let formData = new FormData();
                formData.append('name', $('#name').val());
                formData.append('email', $('#email').val());
                formData.append('rating', $('input[name="rating"]:checked').val());
                formData.append('content', $('#content').val());
                formData.append('g-recaptcha-response', grecaptcha.getResponse());
                formData.append('_token', $('input[name="_token"]').val());

                // Submit form via AJAX
                $.ajax({
                    url: "{{ route('testimonials.submit.ajax') }}",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    beforeSend: function () {
                        // Disable submit button and show loading state
                        $('#submitTestimonial').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Submitting...');
                    },
                    success: function (response) {
                        // Close modal
                        $('#testimonialModal').modal('hide');

                        // Show success message
                        Swal.fire({
                            position: 'top-end',
                            icon: 'success',
                            title: 'Thank you for your testimonial!',
                            text: 'Your review has been submitted and will be published after approval.',
                            toast: true,
                            showConfirmButton: false,
                            timer: 5000,
                            timerProgressBar: true
                        });

                        // Reset form
                        $('#testimonialForm')[0].reset();
                        grecaptcha.reset();
                    },
                    error: function (xhr) {
                        if (xhr.status === 422) {
                            // Validation errors
                            let errors = xhr.responseJSON.errors;

                            // Display each error on the form
                            $.each(errors, function (field, messages) {
                                $('#' + field).addClass('is-invalid');
                                $('#' + field + '-error').text(messages[0]);
                            });
                        } else {
                            // General error
                            Swal.fire({
                                position: 'top-end',
                                icon: 'error',
                                title: 'Oops!',
                                text: 'Something went wrong. Please try again later.',
                                toast: true,
                                showConfirmButton: false,
                                timer: 5000
                            });
                        }
                    },
                    complete: function () {
                        // Re-enable submit button
                        $('#submitTestimonial').prop('disabled', false).html('<i class="fas fa-paper-plane me-2"></i>Submit Testimonial');
                    }
                });
            });

            // Reset form when modal is closed
            $('#testimonialModal').on('hidden.bs.modal', function () {
                $('#testimonialForm')[0].reset();
                $('.is-invalid').removeClass('is-invalid');
                $('.invalid-feedback').text('');
                grecaptcha.reset();
            });
        });
    </script>
@endpush