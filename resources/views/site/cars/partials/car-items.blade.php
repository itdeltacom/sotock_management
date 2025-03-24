@forelse($cars as $car)
    <div class="col-md-6 col-xl-4 mb-4">
        <div class="categories-item p-4 h-100">
            <div class="categories-item-inner h-100 d-flex flex-column">
                <div class="categories-img rounded-top">
                    @if($car->main_image)
                        <img src="{{ Storage::url($car->main_image) }}" class="img-fluid w-100 rounded-top"
                            alt="{{ $car->name }}">
                    @else
                        <img src="{{ asset('site/img/car-' . rand(1, 4) . '.png') }}" class="img-fluid w-100 rounded-top"
                            alt="{{ $car->name }}">
                    @endif
                </div>
                <div class="categories-content rounded-bottom p-4 flex-grow-1 d-flex flex-column">
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
                            {{ number_format($car->price_per_day, 2) }}<small>MAD/Day</small>
                        </h4>
                        @if($car->discount_percentage > 0)
                            <div class="mt-2 text-center">
                                <span class="badge bg-danger">Save {{ $car->discount_percentage }}%</span>
                            </div>
                        @endif
                    </div>
                    <div class="row gy-2 gx-0 text-center mb-4">
                        <div class="col-4 border-end border-white">
                            <i class="fa fa-users text-dark"></i> <span class="text-body ms-1">{{ $car->seats }} Seat</span>
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
                            <i class="fa fa-car text-dark"></i> <span
                                class="text-body ms-1">{{ $car->year ?? '2023' }}</span>
                        </div>
                        <div class="col-4 border-end border-white">
                            <i class="fa fa-cogs text-dark"></i> <span
                                class="text-body ms-1">{{ strtoupper($car->transmission) }}</span>
                        </div>
                        <div class="col-4">
                            <i class="fa fa-road text-dark"></i> <span
                                class="text-body ms-1">{{ $car->mileage ?? '0' }}K</span>
                        </div>
                    </div>
                    <a href="{{ route('cars.show', $car->slug) }}"
                        class="btn btn-primary rounded-pill d-flex justify-content-center py-3 mt-auto">Book Now</a>
                </div>
            </div>
        </div>
    </div>
@empty
    <div class="col-12 text-center py-5">
        <div class="wow fadeInUp">
            <i class="fa fa-car-side fa-5x text-secondary mb-4"></i>
            <h4 class="mb-3">No cars found matching your criteria</h4>
            <p>Try adjusting your filters to see more options.</p>
            <button type="button" id="reset-filters" class="btn btn-primary mt-2">Reset Filters</button>
        </div>
    </div>
@endforelse