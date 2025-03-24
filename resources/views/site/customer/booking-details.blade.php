@extends('site.layouts.app')

@section('content')
    <div class="bg-light rounded p-4 mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="display-6 text-primary mb-0">Détails de la Réservation</h1>
                <p class="mb-0">Référence: <strong>{{ $booking->booking_number }}</strong></p>
            </div>
            <div>
                <a href="{{ route('client.bookings') }}" class="btn btn-secondary me-2">
                    <i class="fas fa-arrow-left me-1"></i> Retour
                </a>
                @if(canBeCancelled($booking))
                    <button type="button" class="btn btn-danger cancel-booking" data-id="{{ $booking->id }}">
                        <i class="fas fa-times me-1"></i> Annuler
                    </button>
                @endif
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Booking Details -->
        <div class="col-lg-8">
            <div class="bg-light rounded p-4 mb-4">
                <h4 class="text-primary mb-4">Information de Réservation</h4>
                <div class="row g-4">
                    <div class="col-md-6">
                        <p class="mb-2"><i class="fas fa-calendar-alt text-primary me-2"></i> <strong>Date de prise en
                                charge:</strong></p>
                        <p class="ms-4">{{ \Carbon\Carbon::parse($booking->pickup_date)->format('d/m/Y') }} à
                            {{ $booking->pickup_time }}
                        </p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-2"><i class="fas fa-calendar-check text-primary me-2"></i> <strong>Date de
                                retour:</strong></p>
                        <p class="ms-4">{{ \Carbon\Carbon::parse($booking->dropoff_date)->format('d/m/Y') }} à
                            {{ $booking->dropoff_time }}
                        </p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-2"><i class="fas fa-clock text-primary me-2"></i> <strong>Durée:</strong></p>
                        <p class="ms-4">{{ $booking->total_days }} jours</p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-2"><i class="fas fa-tag text-primary me-2"></i> <strong>Statut:</strong></p>
                        <p class="ms-4"><span
                                class="badge {{ getStatusBadgeClass($booking->status) }}">{{ getStatusLabel($booking->status) }}</span>
                        </p>
                    </div>
                </div>
                <hr>
                <div class="row g-4">
                    <div class="col-md-6">
                        <p class="mb-2"><i class="fas fa-map-marker-alt text-primary me-2"></i> <strong>Lieu de prise en
                                charge:</strong></p>
                        <p class="ms-4">{{ $booking->pickup_location ?: 'Notre agence principale' }}</p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-2"><i class="fas fa-map-marker text-primary me-2"></i> <strong>Lieu de retour:</strong>
                        </p>
                        <p class="ms-4">{{ $booking->dropoff_location ?: 'Notre agence principale' }}</p>
                    </div>
                </div>
                <hr>
                <div class="row g-4">
                    <div class="col-md-12">
                        <p class="mb-2"><i class="fas fa-sticky-note text-primary me-2"></i> <strong>Demandes
                                spéciales:</strong></p>
                        <p class="ms-4">{{ $booking->special_requests ?: 'Aucune demande spécifiée' }}</p>
                    </div>
                </div>
            </div>

            <!-- Vehicle Details -->
            <div class="bg-light rounded p-4">
                <h4 class="text-primary mb-4">Détails du Véhicule</h4>
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <img src="{{ Storage::url($booking->car->main_image) }}"
                            alt="{{ $booking->car->brand }} {{ $booking->car->model }}" class="img-fluid rounded mb-3"
                            onerror="this.src='{{ asset('site/img/car-placeholder.jpg') }}'">
                    </div>
                    <div class="col-md-6">
                        <h5 class="mb-3">{{ $booking->car->brand }} {{ $booking->car->model }}</h5>
                        <div class="d-flex mb-2">
                            <span class="me-3"><i class="fas fa-car text-primary me-1"></i> {{ $booking->car->year }}</span>
                            <span><i class="fas fa-cog text-primary me-1"></i> {{ $booking->car->transmission }}</span>
                        </div>
                        <div class="d-flex mb-2">
                            <span class="me-3"><i class="fas fa-gas-pump text-primary me-1"></i>
                                {{ $booking->car->fuel_type }}</span>
                            <span><i class="fas fa-users text-primary me-1"></i> {{ $booking->car->passengers }}
                                places</span>
                        </div>
                        <div class="mb-3">
                            <span><i class="fas fa-tag text-primary me-1"></i>
                                {{ $booking->car->category->name ?? 'Non catégorisé' }}</span>
                        </div>
                        <a href="{{ route('cars.show', $booking->car->slug) }}" class="btn btn-sm btn-secondary">
                            <i class="fas fa-info-circle me-1"></i> Plus de détails
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pricing Details -->
        <div class="col-lg-4">
            <div class="bg-light rounded p-4 mb-4">
                <h4 class="text-primary mb-4">Récapitulatif</h4>
                <div class="border-bottom pb-2 mb-2">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Tarif journalier</span>
                        <span>{{ number_format($booking->base_price / $booking->total_days, 2) }} MAD</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Durée de location</span>
                        <span>{{ $booking->total_days }} jours</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Sous-total</span>
                        <span>{{ number_format($booking->base_price, 2) }} MAD</span>
                    </div>
                </div>

                @if($booking->discount_amount > 0)
                    <div class="border-bottom pb-2 mb-2">
                        <div class="d-flex justify-content-between text-success">
                            <span>Réduction</span>
                            <span>-{{ number_format($booking->discount_amount, 2) }} MAD</span>
                        </div>
                    </div>
                @endif

                <div class="border-bottom pb-2 mb-2">
                    <div class="d-flex justify-content-between">
                        <span>Taxes</span>
                        <span>{{ number_format($booking->tax_amount, 2) }} MAD</span>
                    </div>
                </div>

                <div class="d-flex justify-content-between mt-3">
                    <span class="fw-bold fs-5">Total</span>
                    <span class="fw-bold fs-5 text-primary">{{ number_format($booking->total_amount, 2) }} MAD</span>
                </div>
            </div>

            <!-- Payment Status -->
            <div class="bg-light rounded p-4 mb-4">
                <h4 class="text-primary mb-4">Paiement</h4>
                <div class="d-flex justify-content-between mb-3">
                    <span>Statut</span>
                    <span class="badge {{ getPaymentStatusBadgeClass($booking->payment_status) }}">
                        {{ getPaymentStatusLabel($booking->payment_status) }}
                    </span>
                </div>

                @if($booking->payment_method)
                    <div class="d-flex justify-content-between mb-3">
                        <span>Méthode</span>
                        <span>{{ getPaymentMethodLabel($booking->payment_method) }}</span>
                    </div>
                @endif

                @if($booking->transaction_id)
                    <div class="d-flex justify-content-between">
                        <span>Numéro de transaction</span>
                        <span>{{ $booking->transaction_id }}</span>
                    </div>
                @endif

                @if($booking->payment_status !== 'paid')
                    <div class="mt-4">
                        <a href="{{ route('payment.checkout', $booking->id) }}" class="btn btn-primary w-100">
                            <i class="fas fa-credit-card me-1"></i> Payer maintenant
                        </a>
                    </div>
                @endif
            </div>

            <!-- Customer Information -->
            <div class="bg-light rounded p-4 mb-4">
                <h4 class="text-primary mb-4">Information Client</h4>
                <div class="d-flex justify-content-between mb-2">
                    <span>Nom</span>
                    <span>{{ $booking->customer_name ?: $user->name }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Email</span>
                    <span>{{ $booking->customer_email ?: $user->email }}</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span>Téléphone</span>
                    <span>{{ $booking->customer_phone ?: $user->phone }}</span>
                </div>
            </div>

            <!-- Help Box -->
            <div class="bg-primary rounded p-4 text-white">
                <h5 class="text-white mb-3">Besoin d'aide ?</h5>
                <p>Si vous avez des questions concernant votre réservation, n'hésitez pas à nous contacter.</p>
                <div class="mb-3">
                    <i class="fas fa-phone-alt me-2"></i> +212 5 22 XX XX XX
                </div>
                <div class="mb-3">
                    <i class="fas fa-envelope me-2"></i> contact@cental.ma
                </div>
                <a href="{{ route('contact') }}" class="btn btn-light">Nous contacter</a>
            </div>
        </div>
    </div>
@endsection

@section('dashboard-scripts')
    <script>
        $(document).ready(function () {
            // Handle booking cancellation
            $('.cancel-booking').on('click', function () {
                const bookingId = $(this).data('id');

                Swal.fire({
                    title: 'Êtes-vous sûr?',
                    text: "Cette action ne peut pas être annulée!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Oui, annuler la réservation!',
                    cancelButtonText: 'Non, retour'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `/client/bookings/${bookingId}/cancel`,
                            type: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function (response) {
                                if (response.status === 'success') {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Réservation annulée!',
                                        text: response.message,
                                        showConfirmButton: false,
                                        timer: 2000
                                    }).then(() => {
                                        window.location.href = "{{ route('client.bookings') }}";
                                    });
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Erreur',
                                        text: response.message
                                    });
                                }
                            },
                            error: function (xhr) {
                                const response = xhr.responseJSON;
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Erreur',
                                    text: response.message || 'Une erreur est survenue. Veuillez réessayer.'
                                });
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection

@php
    function getStatusLabel($status)
    {
        $labels = [
            'pending' => 'En attente',
            'confirmed' => 'Confirmée',
            'active' => 'Active',
            'completed' => 'Terminée',
            'cancelled' => 'Annulée',
        ];

        return $labels[$status] ?? ucfirst($status);
    }

    function getStatusBadgeClass($status)
    {
        $classes = [
            'pending' => 'bg-warning text-dark',
            'confirmed' => 'bg-info',
            'active' => 'bg-primary',
            'completed' => 'bg-success',
            'cancelled' => 'bg-danger',
        ];

        return $classes[$status] ?? 'bg-secondary';
    }

    function getPaymentStatusLabel($status)
    {
        $labels = [
            'pending' => 'Paiement en attente',
            'paid' => 'Payé',
            'failed' => 'Échec',
            'refunded' => 'Remboursé',
        ];

        return $labels[$status] ?? ucfirst($status);
    }

    function getPaymentStatusBadgeClass($status)
    {
        $classes = [
            'pending' => 'bg-warning text-dark',
            'paid' => 'bg-success',
            'failed' => 'bg-danger',
            'refunded' => 'bg-info',
        ];

        return $classes[$status] ?? 'bg-secondary';
    }

    function getPaymentMethodLabel($method)
    {
        $labels = [
            'credit_card' => 'Carte de crédit',
            'bank_transfer' => 'Virement bancaire',
            'paypal' => 'PayPal',
            'cash' => 'Espèces',
        ];

        return $labels[$method] ?? ucfirst($method);
    }

    function canBeCancelled($booking)
    {
        // Can be cancelled if status is pending or confirmed and pickup date is in the future
        return in_array($booking->status, ['pending', 'confirmed']) &&
            \Carbon\Carbon::parse($booking->pickup_date)->isFuture();
    }
@endphp