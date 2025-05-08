@extends('site.layouts.app')

@section('title', 'Terms and Conditions - Cental')
@section('meta_description', 'Read the terms and conditions for renting cars with Cental. Understand our policies for bookings, payments, and vehicle usage.')
@section('meta_keywords', 'car rental terms, Cental policies, rental conditions, terms and conditions')

@section('og_title', 'Terms and Conditions - Cental')
@section('og_description', 'Review the terms and conditions for car rentals with Cental, including booking, payment, and vehicle usage policies.')
@section('og_image', asset('site/img/logo.png'))

@section('content')
    <!-- Dynamic Breadcrumb -->
    <div class="container-fluid bg-breadcrumb bg-secondary">
        <div class="container text-center py-5" style="max-width: 900px;">
            <h4 class="text-white display-4 mb-4 wow fadeInDown" data-wow-delay="0.1s">Terms and Conditions</h4>
            <ol class="breadcrumb d-flex justify-content-center mb-0 wow fadeInDown" data-wow-delay="0.3s">
                <li class="breadcrumb-item">
                    <a href="{{ route('home') }}">Home</a>
                </li>
                <li class="breadcrumb-item active text-primary">Terms and Conditions</li>
            </ol>
        </div>
    </div>

    <!-- Policy Content Section -->
    <div class="container-fluid py-5" id="policy-content">
        <div class="container py-5">
            <div class="row g-5">
                <!-- Sticky Table of Contents -->
                <div class="col-lg-3 d-none d-lg-block">
                    <div class="toc-sticky card border-0 shadow-sm p-4 lato">
                        <h5 class="fw-bold mb-3 montserrat">Table of Contents</h5>
                        <ul class="list-unstyled">
                            <li><a href="#section-1" class="text-decoration-none text-muted">1. Booking and Reservations</a>
                            </li>
                            <li><a href="#section-2" class="text-decoration-none text-muted">2. Payment Terms</a></li>
                            <li><a href="#section-3" class="text-decoration-none text-muted">3. Vehicle Usage</a></li>
                            <li><a href="#section-4" class="text-decoration-none text-muted">4. Insurance and Liability</a>
                            </li>
                            <li><a href="#section-5" class="text-decoration-none text-muted">5. Cancellation and Refunds</a>
                            </li>
                            <li><a href="#section-6" class="text-decoration-none text-muted">6. Driver Requirements</a></li>
                            <li><a href="#section-7" class="text-decoration-none text-muted">7. Privacy Policy</a></li>
                            <li><a href="#section-8" class="text-decoration-none text-muted">8. Contact Us</a></li>
                        </ul>
                    </div>
                </div>

                <!-- Policy Content -->
                <div class="col-lg-9">
                    <div class="card border-0 shadow-sm policy-card p-5 lato">
                        <div class="policy-content">
                            <p class="text-muted mb-5 lead wow fadeInUp" data-wow-delay="0.2s">
                                Please read these terms and conditions carefully before making a booking with Cental. By
                                using our services, you agree to be bound by these terms.
                            </p>

                            <section id="section-1" class="mb-5 wow fadeInUp" data-wow-delay="0.3s">
                                <h3 class="section-title mb-4 montserrat">1. Booking and Reservations</h3>
                                <p>
                                    <i class="fas fa-check text-primary me-2"></i>
                                    All bookings are subject to availability and confirmation by Cental.
                                </p>
                                <p>
                                    <i class="fas fa-check text-primary me-2"></i>
                                    Customers must provide accurate personal information, including name, email, phone
                                    number, and valid identification (Moroccan CIN or passport).
                                </p>
                                <p>
                                    <i class="fas fa-check text-primary me-2"></i>
                                    Bookings can be made online through our website or at our physical locations.
                                </p>
                            </section>

                            <section id="section-2" class="mb-5 wow fadeInUp" data-wow-delay="0.4s">
                                <h3 class="section-title mb-4 montserrat">2. Payment Terms</h3>
                                <p>
                                    <i class="fas fa-check text-primary me-2"></i>
                                    Payments can be made via credit card, PayPal, or cash on delivery, as specified during
                                    booking.
                                </p>
                                <p>
                                    <i class="fas fa-check text-primary me-2"></i>
                                    A deposit of {{ config('booking.deposit_amount', 1000) }} MAD is required for all
                                    rentals, refundable upon return of the vehicle in good condition.
                                </p>
                                <p>
                                    <i class="fas fa-check text-primary me-2"></i>
                                    Late payments may incur penalties, and unpaid balances may restrict future rentals.
                                </p>
                            </section>

                            <section id="section-3" class="mb-5 wow fadeInUp" data-wow-delay="0.5s">
                                <h3 class="section-title mb-4 montserrat">3. Vehicle Usage</h3>
                                <p>
                                    <i class="fas fa-check text-primary me-2"></i>
                                    Vehicles must be driven only by the authorized driver(s) listed in the booking.
                                </p>
                                <p>
                                    <i class="fas fa-check text-primary me-2"></i>
                                    The mileage limit is {{ config('booking.mileage_limit', 200) }} km per day. Additional
                                    mileage is charged at {{ config('booking.extra_mileage_cost', 2.5) }} MAD per km.
                                </p>
                                <p>
                                    <i class="fas fa-check text-primary me-2"></i>
                                    Vehicles must be returned with a full fuel tank (full-to-full policy).
                                </p>
                            </section>

                            <section id="section-4" class="mb-5 wow fadeInUp" data-wow-delay="0.6s">
                                <h3 class="section-title mb-4 montserrat">4. Insurance and Liability</h3>
                                <p>
                                    <i class="fas fa-check text-primary me-2"></i>
                                    Basic insurance is included in all rentals. Optional standard and premium plans are
                                    available for extended coverage.
                                </p>
                                <p>
                                    <i class="fas fa-check text-primary me-2"></i>
                                    Customers are liable for damages not covered by the selected insurance plan.
                                </p>
                            </section>

                            <section id="section-5" class="mb-5 wow fadeInUp" data-wow-delay="0.7s">
                                <h3 class="section-title mb-4 montserrat">5. Cancellation and Refunds</h3>
                                <p>
                                    <i class="fas fa-check text-primary me-2"></i>
                                    Cancellations made at least 48 hours before the pickup date are eligible for a full
                                    refund, minus a processing fee.
                                </p>
                                <p>
                                    <i class="fas fa-check text-primary me-2"></i>
                                    No refunds are provided for cancellations within 48 hours of the pickup date.
                                </p>
                            </section>

                            <section id="section-6" class="mb-5 wow fadeInUp" data-wow-delay="0.8s">
                                <h3 class="section-title mb-4 montserrat">6. Driver Requirements</h3>
                                <p>
                                    <i class="fas fa-check text-primary me-2"></i>
                                    Drivers must be at least 21 years old and hold a valid driver’s license for at least one
                                    year.
                                </p>
                                <p>
                                    <i class="fas fa-check text-primary me-2"></i>
                                    Additional drivers must meet the same requirements and be registered at the time of
                                    booking.
                                </p>
                            </section>

                            <section id="section-7" class="mb-5 wow fadeInUp" data-wow-delay="0.9s">
                                <h3 class="section-title mb-4 montserrat">7. Privacy Policy</h3>
                                <p>
                                    <i class="fas fa-check text-primary me-2"></i>
                                    We collect and process personal information in accordance with our <a
                                        href="{{ route('privacy') }}" class="text-primary">Privacy Policy</a>.
                                </p>
                                <p>
                                    <i class="fas fa-check text-primary me-2"></i>
                                    Your data will not be shared with third parties except as required for booking and
                                    payment processing.
                                </p>
                            </section>

                            <section id="section-8" class="mb-5 wow fadeInUp" data-wow-delay="1.0s">
                                <h3 class="section-title mb-4 montserrat">8. Contact Us</h3>
                                <p>
                                    <i class="fas fa-check text-primary me-2"></i>
                                    For any questions or concerns, please contact us at <a href="{{ route('contact') }}"
                                        class="text-primary">our contact page</a>.
                                </p>
                            </section>

                            <div class="mt-5 text-center">
                                <a href="{{ route('home') }}" class="btn btn-primary btn-lg py-2 px-4 me-2">Back to Home</a>
                                <a href="{{ route('privacy') }}" class="btn btn-outline-primary btn-lg py-2 px-4">View
                                    Privacy Policy</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile TOC Modal -->
    <div class="d-lg-none">
        <button class="btn btn-primary btn-floating" data-bs-toggle="modal" data-bs-target="#tocModal">
            <i class="fas fa-list"></i>
        </button>
        <div class="modal fade" id="tocModal" tabindex="-1" aria-labelledby="tocModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title montserrat" id="tocModalLabel">Table of Contents</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body lato">
                        <ul class="list-unstyled">
                            <li><a href="#section-1" class="text-decoration-none text-primary" data-bs-dismiss="modal">1.
                                    Booking and Reservations</a></li>
                            <li><a href="#section-2" class="text-decoration-none text-primary" data-bs-dismiss="modal">2.
                                    Payment Terms</a></li>
                            <li><a href="#section-3" class="text-decoration-none text-primary" data-bs-dismiss="modal">3.
                                    Vehicle Usage</a></li>
                            <li><a href="#section-4" class="text-decoration-none text-primary" data-bs-dismiss="modal">4.
                                    Insurance and Liability</a></li>
                            <li><a href="#section-5" class="text-decoration-none text-primary" data-bs-dismiss="modal">5.
                                    Cancellation and Refunds</a></li>
                            <li><a href="#section-6" class="text-decoration-none text-primary" data-bs-dismiss="modal">6.
                                    Driver Requirements</a></li>
                            <li><a href="#section-7" class="text-decoration-none text-primary" data-bs-dismiss="modal">7.
                                    Privacy Policy</a></li>
                            <li><a href="#section-8" class="text-decoration-none text-primary" data-bs-dismiss="modal">8.
                                    Contact Us</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Structured Data for Policy Page -->
    <script type="application/ld+json">
                {
                    "@context": "https://schema.org",
                    "@type": "WebPage",
                    "name": "Terms and Conditions",
                    "description": "Review the terms and conditions for car rentals with Cental, including booking, payment, and vehicle usage policies.",
                    "publisher": {
                        "@type": "Organization",
                        "name": "Cental",
                        "logo": {
                            "@type": "ImageObject",
                            "url": "{{ asset('site/img/logo.png') }}"
                        }
                    }
                }
            </script>
@endsection

@push('styles')
    <style>
        /* Brand Fonts */
        .montserrat {
            font-family: 'Montserrat', sans-serif;
        }

        .lato {
            font-family: 'Lato', sans-serif;
        }

        /* Gradient Background */
        .bg-gradient-primary {
            background: linear-gradient(135deg, var(--bs-primary) 0%, var(--bs-secondary) 100%);
        }

        .hero-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.4);
            z-index: 1;
        }

        .policy-hero h1,
        .policy-hero p,
        .policy-hero a {
            position: relative;
            z-index: 2;
        }

        /* Typography */
        .hero-title {
            font-size: 3rem;
            font-weight: 700;
        }

        .lead {
            font-size: 1rem;
        }

        .section-title {
            font-size: 1.25rem;
            font-weight: 700;
        }

        p,
        li {
            font-size: 1rem;
        }

        /* Glassmorphism Card */
        .policy-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(8px);
            border-radius: 12px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .policy-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15) !important;
        }

        /* Section Titles */
        .section-title::after {
            content: '';
            position: absolute;
            bottom: -8px;
            left: 0;
            width: 60px;
            height: 4px;
            background: var(--bs-primary);
            border-radius: 2px;
        }

        /* TOC Styling */
        .toc-sticky {
            position: sticky;
            top: 100px;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(8px);
            border-radius: 10px;
        }

        .toc-sticky h5 {
            font-size: 1.25rem;
        }

        .toc-sticky a {
            font-size: 0.875rem;
            transition: color 0.2s ease;
        }

        .toc-sticky a:hover {
            color: var(--bs-primary) !important;
        }

        /* Active TOC Link */
        .toc-sticky a.active,
        #tocModal a.active {
            color: var(--bs-primary) !important;
            font-weight: 600;
            background: rgba(var(--bs-primary-rgb), 0.1);
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
        }

        /* Button Effects */
        .btn-primary,
        .btn-outline-primary {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .btn-primary:hover,
        .btn-outline-primary:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        /* Floating TOC Button */
        .btn-floating {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .policy-card {
                padding: 2rem !important;
            }

            .hero-title {
                font-size: 2rem;
            }

            .lead {
                font-size: 0.875rem;
            }

            .section-title {
                font-size: 1.125rem;
            }

            p,
            li {
                font-size: 0.875rem;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        // Initialize WOW.js
        new WOW().init();

        // Smooth scroll for TOC links
        document.querySelectorAll('.toc-sticky a, #tocModal a').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const targetId = this.getAttribute('href');
                const targetElement = document.querySelector(targetId);
                window.scrollTo({
                    top: targetElement.offsetTop - 80,
                    behavior: 'smooth'
                });
            });
        });

        // Highlight active TOC link on scroll
        window.addEventListener('scroll', () => {
            const sections = document.querySelectorAll('section');
            const tocLinks = document.querySelectorAll('.toc-sticky a, #tocModal a');
            let currentSection = '';

            sections.forEach(section => {
                const sectionTop = section.offsetTop - 100;
                if (window.pageYOffset >= sectionTop) {
                    currentSection = section.getAttribute('id');
                }
            });

            tocLinks.forEach(link => {
                link.classList.remove('active');
                if (link.getAttribute('href') === `#${currentSection}`) {
                    link.classList.add('active');
                }
            });
        });
    </script>
@endpush