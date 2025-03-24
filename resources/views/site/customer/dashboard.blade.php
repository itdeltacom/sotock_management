@extends('site.layouts.app')

@section('content')
    <div class="bg-light rounded p-4 mb-4">
        <h1 class="display-6 text-primary mb-4">Bienvenue, {{ $user->name }}</h1>
        <p class="lead">Gérez vos réservations et informations personnelles depuis votre espace client.</p>
    </div>

    <!-- Stats Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="bg-primary rounded p-4 text-center text-white">
                <div class="mb-2">
                    <i class="fas fa-calendar-check fa-3x"></i>
                </div>
                <h2 class="mb-1">{{ $activeBookings }}</h2>
                <p class="mb-0">Réservation(s) Active(s)</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="bg-secondary rounded p-4 text-center text-white">
                <div class="mb-2">
                    <i class="fas fa-clock fa-3x"></i>
                </div>
                <h2 class="mb-1">{{ $upcomingBookings }}</h2>
                <p class="mb-0">Réservation(s) à Venir</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="bg-light rounded p-4 text-center">
                <div class="mb-2 text-primary">
                    <i class="fas fa-history fa-3x"></i>
                </div>
                <h2 class="mb-1">{{ $completedBookings }}</h2>
                <p class="mb-0">Réservation(s) Terminée(s)</p>
            </div>
        </div>
    </div>

    <!-- Latest Booking Section -->
    <div class="bg-light rounded p-4 mb-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="text-primary mb-0">Dernière Réservation</h4>
            <a href="{{ route('client.bookings') }}" class="btn btn-sm btn-primary">Voir Toutes</a>
        </div>

        @if($latestBooking)
            <div class="border rounded p-3">
                <div class="row">
                    <div class="col-md-4">
                        <img src="{{ asset('storage/' . $latestBooking->car->image) }}" alt="{{ $latestBooking->car->model }}"
                            class="img-fluid rounded" onerror="this.src='{{ asset('site/img/car-placeholder.jpg') }}'">
                    </div>
                    <div class="col-md-8">
                        <h5 class="text-primary">{{ $latestBooking->car->brand }} {{ $latestBooking->car->model }}</h5>
                        <div class="d-flex mb-3">
                            <small class="me-3"><i class="fas fa-calendar-alt text-primary me-2"></i>Du
                                {{ \Carbon\Carbon::parse($latestBooking->pickup_date)->format('d/m/Y') }} à
                                {{ $latestBooking->pickup_time }}</small>
                        </div>
                        <div class="d-flex mb-3">
                            <small><i class="fas fa-calendar-check text-primary me-2"></i>Au
                                {{ \Carbon\Carbon::parse($latestBooking->dropoff_date)->format('d/m/Y') }} à
                                {{ $latestBooking->dropoff_time }}</small>
                        </div>
                        <div class="mb-3">
                            <span
                                class="badge {{ getStatusBadgeClass($latestBooking->status) }}">{{ getStatusLabel($latestBooking->status) }}</span>
                            <span
                                class="badge {{ getPaymentStatusBadgeClass($latestBooking->payment_status) }} ms-2">{{ getPaymentStatusLabel($latestBooking->payment_status) }}</span>
                        </div>
                        <p class="mb-3">Montant Total: <strong>{{ number_format($latestBooking->total_amount, 2) }} MAD</strong>
                        </p>
                        <a href="{{ route('client.booking.details', $latestBooking->id) }}"
                            class="btn btn-sm btn-primary">Détails</a>

                        @if(canBeCancelled($latestBooking))
                            <button type="button" class="btn btn-sm btn-danger ms-2 cancel-booking"
                                data-id="{{ $latestBooking->id }}">Annuler</button>
                        @endif
                    </div>
                </div>
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-car-side fa-4x text-secondary mb-3"></i>
                <p class="mb-3">Vous n'avez pas encore de réservations.</p>
                <a href="{{ route('cars.index') }}" class="btn btn-primary">Réserver une voiture</a>
            </div>
        @endif
    </div>

    <!-- Quick Actions -->
    <div class="bg-light rounded p-4">
        <h4 class="text-primary mb-4">Actions Rapides</h4>
        <div class="row g-4">
            <div class="col-md-6">
                <a href="{{ route('cars.index') }}"
                    class="d-flex align-items-center p-3 bg-white rounded text-decoration-none">
                    <div class="rounded bg-primary p-3 me-3">
                        <i class="fas fa-car text-white"></i>
                    </div>
                    <div>
                        <h5 class="mb-0">Louer une Voiture</h5>
                        <small class="text-muted">Parcourir notre catalogue</small>
                    </div>
                </a>
            </div>
            <div class="col-md-6">
                <a href="{{ route('client.profile') }}"
                    class="d-flex align-items-center p-3 bg-white rounded text-decoration-none">
                    <div class="rounded bg-primary p-3 me-3">
                        <i class="fas fa-user-edit text-white"></i>
                    </div>
                    <div>
                        <h5 class="mb-0">Modifier Profil</h5>
                        <small class="text-muted">Mettre à jour vos informations</small>
                    </div>
                </a>
            </div>
            <div class="col-md-6">
                <a href="{{ route('contact') }}"
                    class="d-flex align-items-center p-3 bg-white rounded text-decoration-none">
                    <div class="rounded bg-primary p-3 me-3">
                        <i class="fas fa-headset text-white"></i>
                    </div>
                    <div>
                        <h5 class="mb-0">Assistance</h5>
                        <small class="text-muted">Contactez notre équipe</small>
                    </div>
                </a>
            </div>
            <div class="col-md-6">
                <a href="{{ route('faq') }}" class="d-flex align-items-center p-3 bg-white rounded text-decoration-none">
                    <div class="rounded bg-primary p-3 me-3">
                        <i class="fas fa-question-circle text-white"></i>
                    </div>
                    <div>
                        <h5 class="mb-0">FAQ</h5>
                        <small class="text-muted">Questions fréquemment posées</small>
                    </div>
                </a>
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
                                        location.reload();
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

    function canBeCancelled($booking)
    {
        // Can be cancelled if status is pending or confirmed and pickup date is in the future
        return in_array($booking->status, ['pending', 'confirmed']) &&
            \Carbon\Carbon::parse($booking->pickup_date)->isFuture();
    }
@endphp