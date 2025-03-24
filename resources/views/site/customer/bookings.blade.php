@extends('site.layouts.app')

@section('content')
    <div class="bg-light rounded p-4 mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="display-6 text-primary mb-0">Mes Réservations</h1>
                <p class="mb-0">Historique et suivi de vos locations</p>
            </div>
            <a href="{{ route('cars.index') }}" class="btn btn-primary">Nouvelle Réservation</a>
        </div>
    </div>

    <!-- Booking Filters -->
    <div class="bg-light rounded p-4 mb-4">
        <form id="filter-form">
            <div class="row g-3">
                <div class="col-md-3">
                    <select class="form-select" id="status-filter" name="status">
                        <option value="">Tous les statuts</option>
                        <option value="pending">En attente</option>
                        <option value="confirmed">Confirmée</option>
                        <option value="active">Active</option>
                        <option value="completed">Terminée</option>
                        <option value="cancelled">Annulée</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                        <input type="date" class="form-control" id="date-from" name="date_from" placeholder="Du">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                        <input type="date" class="form-control" id="date-to" name="date_to" placeholder="Au">
                    </div>
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter"></i></button>
                </div>
            </div>
        </form>
    </div>

    <!-- Bookings List -->
    <div class="bg-light rounded p-4">
        @if($bookings->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">Réf.</th>
                            <th scope="col">Véhicule</th>
                            <th scope="col">Dates</th>
                            <th scope="col">Montant</th>
                            <th scope="col">Statut</th>
                            <th scope="col">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($bookings as $booking)
                            <tr>
                                <td>{{ $booking->booking_number }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="{{ asset('storage/' . $booking->car->image) }}" alt="{{ $booking->car->model }}"
                                            class="img-fluid rounded me-2" style="width: 50px; height: 30px; object-fit: cover;"
                                            onerror="this.src='{{ asset('site/img/car-placeholder.jpg') }}'">
                                        <span>{{ $booking->car->brand }} {{ $booking->car->model }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <small><i
                                                class="fas fa-calendar-alt text-primary me-1"></i>{{ \Carbon\Carbon::parse($booking->pickup_date)->format('d/m/Y') }}
                                            {{ $booking->pickup_time }}</small>
                                    </div>
                                    <div>
                                        <small><i
                                                class="fas fa-calendar-check text-primary me-1"></i>{{ \Carbon\Carbon::parse($booking->dropoff_date)->format('d/m/Y') }}
                                            {{ $booking->dropoff_time }}</small>
                                    </div>
                                </td>
                                <td>{{ number_format($booking->total_amount, 2) }} MAD</td>
                                <td>
                                    <span
                                        class="badge {{ getStatusBadgeClass($booking->status) }}">{{ getStatusLabel($booking->status) }}</span>
                                </td>
                                <td>
                                    <div class="d-flex">
                                        <a href="{{ route('client.booking.details', $booking->id) }}"
                                            class="btn btn-sm btn-primary me-1" data-bs-toggle="tooltip" title="Voir détails">
                                            <i class="fas fa-eye"></i>
                                        </a>

                                        @if(canBeCancelled($booking))
                                            <button type="button" class="btn btn-sm btn-danger cancel-booking"
                                                data-id="{{ $booking->id }}" data-bs-toggle="tooltip" title="Annuler">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-4">
                {{ $bookings->appends(request()->query())->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-calendar-times fa-4x text-secondary mb-3"></i>
                <h5 class="text-primary mb-3">Aucune réservation trouvée</h5>
                <p class="mb-4">Vous n'avez pas encore effectué de réservation.</p>
                <a href="{{ route('cars.index') }}" class="btn btn-primary">Réserver une voiture maintenant</a>
            </div>
        @endif
    </div>
@endsection

@section('dashboard-scripts')
    <script>
        $(document).ready(function () {
            // Initialize tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            });

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

            // Handle filter form submission
            $('#filter-form').submit(function (e) {
                e.preventDefault();

                const status = $('#status-filter').val();
                const dateFrom = $('#date-from').val();
                const dateTo = $('#date-to').val();

                // Construct URL with query parameters
                let url = "{{ route('client.bookings') }}";
                const params = [];

                if (status) params.push(`status=${status}`);
                if (dateFrom) params.push(`date_from=${dateFrom}`);
                if (dateTo) params.push(`date_to=${dateTo}`);

                if (params.length > 0) {
                    url += '?' + params.join('&');
                }

                window.location.href = url;
            });

            // Set filter values from URL on page load
            const urlParams = new URLSearchParams(window.location.search);
            $('#status-filter').val(urlParams.get('status') || '');
            $('#date-from').val(urlParams.get('date_from') || '');
            $('#date-to').val(urlParams.get('date_to') || '');
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

    function canBeCancelled($booking)
    {
        // Can be cancelled if status is pending or confirmed and pickup date is in the future
        return in_array($booking->status, ['pending', 'confirmed']) &&
            \Carbon\Carbon::parse($booking->pickup_date)->isFuture();
    }
@endphp