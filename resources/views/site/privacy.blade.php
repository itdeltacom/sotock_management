@extends('site.layouts.app')

@section('title', 'Privacy Policy - Cental')
@section('meta_description', 'Learn how Cental collects, uses, and protects your personal information in our Privacy Policy.')
@section('meta_keywords', 'privacy policy, Cental data protection, personal information, car rental privacy')

@section('og_title', 'Privacy Policy - Cental')
@section('og_description', 'Understand how Cental handles your personal data, including collection, usage, and protection practices.')
@section('og_image', asset('site/img/logo.png'))

@section('content')
    <!-- Dynamic Breadcrumb -->
    <div class="container-fluid bg-breadcrumb bg-secondary">
        <div class="container text-center py-5" style="max-width: 900px;">
            <h4 class="text-white display-4 mb-4 wow fadeInDown" data-wow-delay="0.1s">Privacy Policy</h4>
            <ol class="breadcrumb d-flex justify-content-center mb-0 wow fadeInDown" data-wow-delay="0.3s">
                <li class="breadcrumb-item">
                    <a href="{{ route('home') }}">Home</a>
                </li>
                <li class="breadcrumb-item active text-primary">Privacy Policy</li>
            </ol>
        </div>
    </div>

    <!-- Privacy Content Section -->
    <div class="container-fluid py-5" id="privacy-content">
        <div class="container py-5">
            <div class="row g-5">
                <!-- Sticky Table of Contents -->
                <div class="col-lg-3 d-none d-lg-block">
                    <div class="toc-sticky card border-0 shadow-sm p-4 lato">
                        <h5 class="fw-bold mb-3 montserrat">Table of Contents</h5>
                        <ul class="list-unstyled">
                            <li><a href="#section-1" class="text-decoration-none text-muted">1. Information We Collect</a>
                            </li>
                            <li><a href="#section-2" class="text-decoration-none text-muted">2. How We Use Your
                                    Information</a></li>
                            <li><a href="#section-3" class="text-decoration-none text-muted">3. How We Protect Your
                                    Information</a></li>
                            <li><a href="#section-4" class="text-decoration-none text-muted">4. Sharing Your Information</a>
                            </li>
                            <li><a href="#section-5" class="text-decoration-none text-muted">5. Your Rights</a></li>
                            <li><a href="#section-6" class="text-decoration-none text-muted">6. Cookies</a></li>
                            <li><a href="#section-7" class="text-decoration-none text-muted">7. Changes to This Policy</a>
                            </li>
                            <li><a href="#section-8" class="text-decoration-none text-muted">8. Contact Us</a></li>
                        </ul>
                    </div>
                </div>

                <!-- Privacy Content -->
                <div class="col-lg-9">
                    <div class="card border-0 shadow-sm privacy-card p-5 lato">
                        <div class="privacy-content">
                            <p class="text-muted mb-5 lead wow fadeInUp" data-wow-delay="0.2s">
                                At Cental, we are committed to protecting your privacy. This Privacy Policy explains how we
                                collect, use, and safeguard your personal information when you use our services.
                            </p>

                            <section id="section-1" class="mb-5 wow fadeInUp" data-wow-delay="0.3s">
                                <h3 class="section-title mb-4 montserrat">1. Information We Collect</h3>
                                <p>
                                    <i class="fas fa-check text-primary me-2"></i>
                                    <strong>Personal Information:</strong> When you make a booking, we collect details such
                                    as your name, email address, phone number, and identification (Moroccan CIN or
                                    passport).
                                </p>
                                <p>
                                    <i class="fas fa-check text-primary me-2"></i>
                                    <strong>Payment Information:</strong> We collect payment details (e.g., credit card or
                                    PayPal information) to process your booking. These are securely handled by our payment
                                    gateway.
                                </p>
                                <p>
                                    <i class="fas fa-check text-primary me-2"></i>
                                    <strong>Usage Data:</strong> We may collect information about how you interact with our
                                    website, such as IP address, browser type, and pages visited.
                                </p>
                            </section>

                            <section id="section-2" class="mb-5 wow fadeInUp" data-wow-delay="0.4s">
                                <h3 class="section-title mb-4 montserrat">2. How We Use Your Information</h3>
                                <p>
                                    <i class="fas fa-check text-primary me-2"></i>
                                    <strong>Booking and Services:</strong> To process and manage your car rental bookings,
                                    including sending confirmation emails and updates.
                                </p>
                                <p>
                                    <i class="fas fa-check text-primary me-2"></i>
                                    <strong>Customer Support:</strong> To respond to your inquiries and provide assistance.
                                </p>
                                <p>
                                    <i class="fas fa-check text-primary me-2"></i>
                                    <strong>Marketing:</strong> With your consent, we may send you promotional offers or
                                    newsletters. You can opt out at any time via the <a
                                        href="{{ route('newsletter.unsubscribe', ['email' => 'example@email.com', 'token' => 'example-token']) }}"
                                        class="text-primary">unsubscribe link</a>.
                                </p>
                            </section>

                            <section id="section-3" class="mb-5 wow fadeInUp" data-wow-delay="0.5s">
                                <h3 class="section-title mb-4 montserrat">3. How We Protect Your Information</h3>
                                <p>
                                    <i class="fas fa-check text-primary me-2"></i>
                                    We use industry-standard encryption (e.g., SSL) to protect your data during
                                    transmission.
                                </p>
                                <p>
                                    <i class="fas fa-check text-primary me-2"></i>
                                    Access to your personal information is restricted to authorized personnel only.
                                </p>
                                <p>
                                    <i class="fas fa-check text-primary me-2"></i>
                                    Regular security audits are conducted to ensure the safety of our systems.
                                </p>
                            </section>

                            <section id="section-4" class="mb-5 wow fadeInUp" data-wow-delay="0.6s">
                                <h3 class="section-title mb-4 montserrat">4. Sharing Your Information</h3>
                                <p>
                                    <i class="fas fa-check text-primary me-2"></i>
                                    We do not sell or rent your personal information to third parties.
                                </p>
                                <p>
                                    <i class="fas fa-check text-primary me-2"></i>
                                    We may share your data with trusted partners (e.g., payment processors, insurance
                                    providers) solely to fulfill your booking.
                                </p>
                                <p>
                                    <i class="fas fa-check text-primary me-2"></i>
                                    We may disclose information if required by law or to protect our legal rights.
                                </p>
                            </section>

                            <section id="section-5" class="mb-5 wow fadeInUp" data-wow-delay="0.7s">
                                <h3 class="section-title mb-4 montserrat">5. Your Rights</h3>
                                <p>
                                    <i class="fas fa-check text-primary me-2"></i>
                                    You have the right to access, correct, or delete your personal information.
                                </p>
                                <p>
                                    <i class="fas fa-check text-primary me-2"></i>
                                    You can opt out of marketing communications at any time.
                                </p>
                                <p>
                                    <i class="fas fa-check text-primary me-2"></i>
                                    To exercise these rights, please contact us via our <a href="{{ route('contact') }}"
                                        class="text-primary">contact page</a>.
                                </p>
                            </section>

                            <section id="section-6" class="mb-5 wow fadeInUp" data-wow-delay="0.8s">
                                <h3 class="section-title mb-4 montserrat">6. Cookies</h3>
                                <p>
                                    <i class="fas fa-check text-primary me-2"></i>
                                    Our website uses cookies to enhance your browsing experience and analyze site usage.
                                </p>
                                <p>
                                    <i class="fas fa-check text-primary me-2"></i>
                                    You can manage cookie preferences through your browser settings.
                                </p>
                            </section>

                            <section id="section-7" class="mb-5 wow fadeInUp" data-wow-delay="0.9s">
                                <h3 class="section-title mb-4 montserrat">7. Changes to This Policy</h3>
                                <p>
                                    <i class="fas fa-check text-primary me-2"></i>
                                    We may update this Privacy Policy from time to time. Changes will be posted on this page
                                    with an updated effective date.
                                </p>
                            </section>

                            <section id="section-8" class="mb-5 wow fadeInUp" data-wow-delay="1.0s">
                                <h3 class="section-title mb-4 montserrat">8. Contact Us</h3>
                                <p>
                                    <i class="fas fa-check text-primary me-2"></i>
                                    For questions about this Privacy Policy, please contact us at <a
                                        href="{{ route('contact') }}" class="text-primary">our contact page</a>.
                                </p>
                            </section>

                            <div class="mt-5 text-center">
                                <a href="{{ route('home') }}" class="btn btn-primary btn-lg py-2 px-4 me-2">Back to Home</a>
                                <a href="{{ route('policy') }}" class="btn btn-outline-primary btn-lg py-2 px-4">View Terms
                                    & Conditions</a>
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
                                    Information We Collect</a></li>
                            <li><a href="#section-2" class="text-decoration-none text-primary" data-bs-dismiss="modal">2.
                                    How We Use Your Information</a></li>
                            <li><a href="#section-3" class="text-decoration-none text-primary" data-bs-dismiss="modal">3.
                                    How We Protect Your Information</a></li>
                            <li><a href="#section-4" class="text-decoration-none text-primary" data-bs-dismiss="modal">4.
                                    Sharing Your Information</a></li>
                            <li><a href="#section-5" class="text-decoration-none text-primary" data-bs-dismiss="modal">5.
                                    Your Rights</a></li>
                            <li><a href="#section-6" class="text-decoration-none text-primary" data-bs-dismiss="modal">6.
                                    Cookies</a></li>
                            <li><a href="#section-7" class="text-decoration-none text-primary" data-bs-dismiss="modal">7.
                                    Changes to This Policy</a></li>
                            <li><a href="#section-8" class="text-decoration-none text-primary" data-bs-dismiss="modal">8.
                                    Contact Us</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Structured Data for Privacy Page -->
    <script type="application/ld+json">
                {
                    "@context": "https://schema.org",
                    "@type": "WebPage",
                    "name": "Privacy Policy",
                    "description": "Understand how Cental handles your personal data, including collection, usage, and protection practices.",
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

        .privacy-hero h1,
        .privacy-hero p,
        .privacy-hero a {
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
        .privacy-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(8px);
            border-radius: 12px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .privacy-card:hover {
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
            .privacy-card {
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