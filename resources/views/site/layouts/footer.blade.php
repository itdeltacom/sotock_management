<!-- Footer Start -->
<div class="container-fluid footer py-5 wow fadeIn" data-wow-delay="0.2s">
    <div class="container py-5">
        <div class="row g-5">
            <div class="col-md-6 col-lg-6 col-xl-3">
                <div class="footer-item d-flex flex-column">
                    <div class="footer-item">
                        <h4 class="text-white mb-4">About Cental</h4>
                        <p class="mb-3">Your trusted car rental service providing quality vehicles and exceptional
                            customer experience.</p>
                    </div>
                    <div class="position-relative">
                        <form action="{{ route('contact.submit') }}" method="POST">
                            @csrf
                            <input class="form-control rounded-pill w-100 py-3 ps-4 pe-5" type="email" name="email"
                                placeholder="Enter your email" required>
                            <button type="submit"
                                class="btn btn-secondary rounded-pill position-absolute top-0 end-0 py-2 mt-2 me-2">Subscribe</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-6 col-xl-3">
                <div class="footer-item d-flex flex-column">
                    <h4 class="text-white mb-4">Quick Links</h4>
                    <a href="{{ route('about') }}"><i class="fas fa-angle-right me-2"></i> About</a>
                    <a href="{{ route('cars.index') }}"><i class="fas fa-angle-right me-2"></i> Our Cars</a>
                    <a href="{{ route('categories.index') }}"><i class="fas fa-angle-right me-2"></i> Car Categories</a>
                    <a href="/blogs"><i class="fas fa-angle-right me-2"></i> Blog</a>
                    <a href="{{ route('contact') }}"><i class="fas fa-angle-right me-2"></i> Contact Us</a>
                    <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">
                        <i class="fas fa-angle-right me-2"></i> Terms & Conditions
                    </a>
                </div>
            </div>
            <div class="col-md-6 col-lg-6 col-xl-3">
                <div class="footer-item d-flex flex-column">
                    <h4 class="text-white mb-4">Business Hours</h4>
                    <div class="mb-3">
                        <h6 class="text-muted mb-0">Mon - Friday:</h6>
                        <p class="text-white mb-0">09.00 am to 07.00 pm</p>
                    </div>
                    <div class="mb-3">
                        <h6 class="text-muted mb-0">Saturday:</h6>
                        <p class="text-white mb-0">10.00 am to 05.00 pm</p>
                    </div>
                    <div class="mb-3">
                        <h6 class="text-muted mb-0">Vacation:</h6>
                        <p class="text-white mb-0">All Sunday is our vacation</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-6 col-xl-3">
                <div class="footer-item d-flex flex-column">
                    <h4 class="text-white mb-4">Contact Info</h4>
                    <a href="{{ route('contact') }}"><i class="fa fa-map-marker-alt me-2"></i> 123 Street, New York,
                        USA</a>
                    <a href="mailto:info@cental.com"><i class="fas fa-envelope me-2"></i> info@cental.com</a>
                    <a href="tel:+012 345 67890"><i class="fas fa-phone me-2"></i> +012 345 67890</a>
                    <a href="tel:+012 345 67890" class="mb-3"><i class="fas fa-print me-2"></i> +012 345 67890</a>
                    <div class="d-flex">
                        <a class="btn btn-secondary btn-md-square rounded-circle me-3" href="#"><i
                                class="fab fa-facebook-f text-white"></i></a>
                        <a class="btn btn-secondary btn-md-square rounded-circle me-3" href="#"><i
                                class="fab fa-twitter text-white"></i></a>
                        <a class="btn btn-secondary btn-md-square rounded-circle me-3" href="#"><i
                                class="fab fa-instagram text-white"></i></a>
                        <a class="btn btn-secondary btn-md-square rounded-circle me-0" href="#"><i
                                class="fab fa-linkedin-in text-white"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Footer End -->

<!-- Copyright Start -->
<div class="container-fluid copyright py-4">
    <div class="container">
        <div class="row g-4 align-items-center">
            <div class="col-md-6 text-center text-md-start mb-md-0">
                <span class="text-body">
                    <a href="{{ route('home') }}" class="border-bottom text-white">
                        <i class="fas fa-copyright text-light me-2"></i>Cental Car Rental
                    </a>, All rights reserved.
                </span>
            </div>
            <div class="col-md-6 text-center text-md-end text-body">
                Designed By <a class="border-bottom text-white" href="https://htmlcodex.com">HTML Codex</a>
                Distributed By <a class="border-bottom text-white" href="https://themewagon.com">ThemeWagon</a>
            </div>
        </div>
    </div>
</div>
<!-- Copyright End -->

<!-- Terms and Conditions Modal -->
<div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="termsModalLabel">Terms & Conditions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>These are the terms and conditions for Cental Car Rental. By using our service, you agree to the
                    following:</p>
                <ul>
                    <li>All rentals are subject to availability</li>
                    <li>Valid driver's license required</li>
                    <li>Minimum age requirement applies</li>
                    <li>Insurance and additional fees may apply</li>
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Back to Top -->
<a href="#" class="btn btn-secondary btn-lg-square rounded-circle back-to-top"><i class="fa fa-arrow-up"></i></a>