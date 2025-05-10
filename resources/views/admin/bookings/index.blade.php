@extends('admin.layouts.master')

@section('title', 'Gestion des Réservations')

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                <div class="card cards-stats">
                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-8">
                                <div class="numbers">
                                    <p class="text-sm mb-0 text-uppercase font-weight-bold">Total Réservations</p>
                                    <h5 class="font-weight-bolder">
                                        {{ $statistics['total_bookings'] ?? 0 }}
                                    </h5>
                                    <p class="mb-0">
                                        <span
                                            class="{{ $statistics['total_bookings_change'] >= 0 ? 'text-success' : 'text-danger' }} text-sm font-weight-bolder">
                                            {{ $statistics['total_bookings_change'] >= 0 ? '+' : '' }}{{ $statistics['total_bookings_change'] ?? 0 }}%
                                        </span>
                                        depuis le mois dernier
                                    </p>
                                </div>
                            </div>
                            <div class="col-4 text-end">
                                <div class="icon icon-shape bg-gradient-primary shadow-primary text-center rounded-circle">
                                    <i class="fas fa-calendar-check text-lg opacity-10" aria-hidden="true"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                <div class="card cards-stats">
                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-8">
                                <div class="numbers">
                                    <p class="text-sm mb-0 text-uppercase font-weight-bold">Réservations Actives</p>
                                    <h5 class="font-weight-bolder">
                                        {{ $statistics['active_bookings'] ?? 0 }}
                                    </h5>
                                    <p class="mb-0">
                                        <span
                                            class="{{ $statistics['active_bookings_change'] >= 0 ? 'text-success' : 'text-danger' }} text-sm font-weight-bolder">
                                            {{ $statistics['active_bookings_change'] >= 0 ? '+' : '' }}{{ $statistics['active_bookings_change'] ?? 0 }}%
                                        </span>
                                        depuis la semaine dernière
                                    </p>
                                </div>
                            </div>
                            <div class="col-4 text-end">
                                <div class="icon icon-shape bg-gradient-success shadow-success text-center rounded-circle">
                                    <i class="fas fa-check-circle text-lg opacity-10" aria-hidden="true"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                <div class="card cards-stats">
                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-8">
                                <div class="numbers">
                                    <p class="text-sm mb-0 text-uppercase font-weight-bold">Paiements en Attente</p>
                                    <h5 class="font-weight-bolder">
                                        {{ $statistics['pending_payments'] ?? 0 }}
                                    </h5>
                                    <p class="mb-0">
                                        <span
                                            class="{{ $statistics['pending_payments_change'] >= 0 ? 'text-success' : 'text-danger' }} text-sm font-weight-bolder">
                                            {{ $statistics['pending_payments_change'] >= 0 ? '+' : '' }}{{ $statistics['pending_payments_change'] ?? 0 }}%
                                        </span>
                                        depuis hier
                                    </p>
                                </div>
                            </div>
                            <div class="col-4 text-end">
                                <div class="icon icon-shape bg-gradient-warning shadow-warning text-center rounded-circle">
                                    <i class="fas fa-money-bill-wave text-lg opacity-10" aria-hidden="true"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6">
                <div class="card cards-stats">
                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-8">
                                <div class="numbers">
                                    <p class="text-sm mb-0 text-uppercase font-weight-bold">Réservations Annulées</p>
                                    <h5 class="font-weight-bolder">
                                        {{ $statistics['cancelled_bookings'] ?? 0 }}
                                    </h5>
                                    <p class="mb-0">
                                        <span
                                            class="{{ $statistics['cancelled_bookings_change'] >= 0 ? 'text-warning' : 'text-success' }} text-sm font-weight-bolder">
                                            {{ $statistics['cancelled_bookings_change'] >= 0 ? '+' : '' }}{{ $statistics['cancelled_bookings_change'] ?? 0 }}%
                                        </span>
                                        ce mois-ci
                                    </p>
                                </div>
                            </div>
                            <div class="col-4 text-end">
                                <div class="icon icon-shape bg-gradient-danger shadow-danger text-center rounded-circle">
                                    <i class="fas fa-times-circle text-lg opacity-10" aria-hidden="true"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header pb-0 p-3">
                        <div class="row">
                            <div class="col-6 d-flex align-items-center">
                                <h6 class="mb-0">Gestion des Réservations</h6>
                            </div>
                            <div class="col-6 text-end">
                                <button type="button" class="btn bg-gradient-primary" id="createBookingBtn">
                                    <i class="fas fa-plus"></i> Nouvelle Réservation
                                </button>
                                <button type="button" class="btn bg-gradient-success" id="exportBtn">
                                    <i class="fas fa-file-export"></i> Exporter
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body px-0 pt-0 pb-2">
                        <div class="p-3">
                            <form id="filterForm" class="row">
                                <div class="col-md-2 mb-3">
                                    <label for="filter_car" class="form-control-label">Véhicule</label>
                                    <select class="form-select" id="filter_car">
                                        <option value="">Tous les véhicules</option>
                                        @foreach($cars as $car)
                                            <option value="{{ $car->id }}">{{ $car->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2 mb-3">
                                    <label for="filter_status" class="form-control-label">Statut</label>
                                    <select class="form-select" id="filter_status">
                                        <option value="">Tous les statuts</option>
                                        <option value="pending">En attente</option>
                                        <option value="confirmed">Confirmée</option>
                                        <option value="in_progress">En cours</option>
                                        <option value="completed">Terminée</option>
                                        <option value="cancelled">Annulée</option>
                                        <option value="no_show">Non présenté</option>
                                    </select>
                                </div>
                                <div class="col-md-2 mb-3">
                                    <label for="filter_payment_status" class="form-control-label">Statut de Paiement</label>
                                    <select class="form-select" id="filter_payment_status">
                                        <option value="">Tous</option>
                                        <option value="paid">Payé</option>
                                        <option value="unpaid">Non payé</option>
                                        <option value="pending">En attente</option>
                                        <option value="refunded">Remboursé</option>
                                    </select>
                                </div>
                                <div class="col-md-2 mb-3">
                                    <label for="filter_date_from" class="form-control-label">Date de début</label>
                                    <input type="date" class="form-control" id="filter_date_from">
                                </div>
                                <div class="col-md-2 mb-3">
                                    <label for="filter_date_to" class="form-control-label">Date de fin</label>
                                    <input type="date" class="form-control" id="filter_date_to">
                                </div>
                                <div class="col-md-2 mb-3 d-flex align-items-end">
                                    <button type="submit" class="btn bg-gradient-primary me-2">Filtrer</button>
                                    <button type="button" id="resetFiltersBtn"
                                        class="btn btn-outline-secondary">Réinitialiser</button>
                                </div>
                            </form>
                        </div>
                        <div class="table-responsive p-0">
                            <table class="table align-items-center mb-0" id="bookings-table" width="100%">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">ID
                                        </th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Réservation</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Client</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Véhicule</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Période</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Montant</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Statut</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Paiement</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Actions Statut</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Actions Paiement</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Actions</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create/Edit Booking Modal -->
        <div class="modal fade" id="bookingModal" tabindex="-1" aria-labelledby="bookingModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable modal-70">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="bookingModalLabel">Nouvelle Réservation</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="bookingForm">
                        @csrf
                        <input type="hidden" name="booking_id" id="booking_id">
                        <div class="modal-body">
                            <div class="row">
                                <!-- Left column: Car and rental details -->
                                <div class="col-md-6">
                                    <h5 class="mb-3">Détails du Véhicule & Location</h5>
                                    <div class="card bg-light mb-3">
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label for="car_id" class="form-control-label">Véhicule <span
                                                        class="text-danger">*</span></label>
                                                <select class="form-select" id="car_id" name="car_id" required>
                                                    <option value="">Sélectionner un véhicule</option>
                                                    @foreach($cars as $car)
                                                        <option value="{{ $car->id }}" data-price="{{ $car->price_per_day }}"
                                                            data-discount="{{ $car->discount_percentage }}">
                                                            {{ $car->name }} - {{ number_format($car->price_per_day, 2) }}
                                                            MAD/jour
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <div class="invalid-feedback" id="car_id-error"></div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label for="pickup_date" class="form-control-label">Date de Retrait
                                                        <span class="text-danger">*</span></label>
                                                    <input type="date" class="form-control" id="pickup_date"
                                                        name="pickup_date" required>
                                                    <div class="invalid-feedback" id="pickup_date-error"></div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="pickup_time" class="form-control-label">Heure de Retrait
                                                        <span class="text-danger">*</span></label>
                                                    <input type="time" class="form-control" id="pickup_time"
                                                        name="pickup_time" required>
                                                    <div class="invalid-feedback" id="pickup_time-error"></div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label for="dropoff_date" class="form-control-label">Date de Retour
                                                        <span class="text-danger">*</span></label>
                                                    <input type="date" class="form-control" id="dropoff_date"
                                                        name="dropoff_date" required>
                                                    <div class="invalid-feedback" id="dropoff_date-error"></div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="dropoff_time" class="form-control-label">Heure de Retour
                                                        <span class="text-danger">*</span></label>
                                                    <input type="time" class="form-control" id="dropoff_time"
                                                        name="dropoff_time" required>
                                                    <div class="invalid-feedback" id="dropoff_time-error"></div>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label for="pickup_location" class="form-control-label">Lieu de Retrait
                                                    <span class="text-danger">*</span></label>
                                                <select class="form-select location-select" id="pickup_location"
                                                    name="pickup_location" required>
                                                    <option value="">Sélectionner un lieu</option>
                                                    <optgroup label="Grandes Villes">
                                                        <option value="Casablanca">Casablanca</option>
                                                        <option value="Rabat">Rabat</option>
                                                        <option value="Marrakech">Marrakech</option>
                                                        <option value="Fès">Fès</option>
                                                        <option value="Tanger">Tanger</option>
                                                        <option value="Agadir">Agadir</option>
                                                    </optgroup>
                                                    <optgroup label="Aéroports">
                                                        <option value="Aéroport Mohammed V (Casablanca)">Aéroport Mohammed V
                                                            (Casablanca)</option>
                                                        <option value="Aéroport Marrakech Menara">Aéroport Marrakech Menara
                                                        </option>
                                                        <option value="Aéroport Agadir Al Massira">Aéroport Agadir Al
                                                            Massira</option>
                                                    </optgroup>
                                                    <optgroup label="Autre">
                                                        <option value="custom">Autre (préciser)</option>
                                                    </optgroup>
                                                </select>
                                                <input type="text" class="form-control mt-2 d-none"
                                                    id="pickup_location_custom" placeholder="Précisez le lieu de retrait">
                                                <div class="invalid-feedback" id="pickup_location-error"></div>
                                            </div>

                                            <div class="mb-3">
                                                <label for="dropoff_location" class="form-control-label">Lieu de Retour
                                                    <span class="text-danger">*</span></label>
                                                <select class="form-select location-select" id="dropoff_location"
                                                    name="dropoff_location" required>
                                                    <option value="">Sélectionner un lieu</option>
                                                    <optgroup label="Grandes Villes">
                                                        <option value="Casablanca">Casablanca</option>
                                                        <option value="Rabat">Rabat</option>
                                                        <option value="Marrakech">Marrakech</option>
                                                        <option value="Fès">Fès</option>
                                                        <option value="Tanger">Tanger</option>
                                                        <option value="Agadir">Agadir</option>
                                                    </optgroup>
                                                    <optgroup label="Aéroports">
                                                        <option value="Aéroport Mohammed V (Casablanca)">Aéroport Mohammed V
                                                            (Casablanca)</option>
                                                        <option value="Aéroport Marrakech Menara">Aéroport Marrakech Menara
                                                        </option>
                                                        <option value="Aéroport Agadir Al Massira">Aéroport Agadir Al
                                                            Massira</option>
                                                    </optgroup>
                                                    <optgroup label="Autre">
                                                        <option value="custom">Autre (préciser)</option>
                                                    </optgroup>
                                                </select>
                                                <input type="text" class="form-control mt-2 d-none"
                                                    id="dropoff_location_custom" placeholder="Précisez le lieu de retour">
                                                <div class="invalid-feedback" id="dropoff_location-error"></div>
                                            </div>

                                            <!-- Additional Rental Options -->
                                            <div class="mb-3">
                                                <label class="form-control-label">Options Additionnelles</label>
                                                <div class="form-check form-switch mt-2">
                                                    <input class="form-check-input" type="checkbox" id="additional_driver"
                                                        name="additional_driver">
                                                    <label class="form-check-label" for="additional_driver">Conducteur
                                                        supplémentaire (+{{ config('booking.additional_driver_fee', 30) }}
                                                        MAD)</label>
                                                </div>
                                                <div id="additional_driver_fields" class="ps-4 mt-2 d-none">
                                                    <div class="mb-2">
                                                        <input type="text" class="form-control" id="additional_driver_name"
                                                            name="additional_driver_name" placeholder="Nom du conducteur"
                                                            disabled>
                                                    </div>
                                                    <div>
                                                        <input type="text" class="form-control"
                                                            id="additional_driver_license" name="additional_driver_license"
                                                            placeholder="Numéro de permis" disabled>
                                                    </div>
                                                </div>

                                                <div class="form-check form-switch mt-2">
                                                    <input class="form-check-input" type="checkbox" id="gps_enabled"
                                                        name="gps_enabled">
                                                    <label class="form-check-label" for="gps_enabled">GPS
                                                        (+{{ config('booking.gps_fee', 20) }} MAD)</label>
                                                </div>

                                                <div class="form-check form-switch mt-2">
                                                    <input class="form-check-input" type="checkbox" id="child_seat"
                                                        name="child_seat">
                                                    <label class="form-check-label" for="child_seat">Siège enfant
                                                        (+{{ config('booking.child_seat_fee', 15) }} MAD)</label>
                                                </div>
                                            </div>

                                            <!-- Insurance Plan -->
                                            <div class="mb-3">
                                                <label for="insurance_plan" class="form-control-label">Formule d'Assurance
                                                    <span class="text-danger">*</span></label>
                                                <select class="form-select" id="insurance_plan" name="insurance_plan"
                                                    required>
                                                    <option value="basic">Basique (inclus)</option>
                                                    <option value="standard">Standard
                                                        (+{{ config('booking.insurance.standard', 50) }} MAD)</option>
                                                    <option value="premium">Premium
                                                        (+{{ config('booking.insurance.premium', 100) }} MAD)</option>
                                                </select>
                                                <div class="invalid-feedback" id="insurance_plan-error"></div>
                                            </div>

                                            <!-- Delivery Options -->
                                            <div class="mb-3">
                                                <label for="delivery_option" class="form-control-label">Option de Livraison
                                                    <span class="text-danger">*</span></label>
                                                <select class="form-select" id="delivery_option" name="delivery_option"
                                                    required>
                                                    <option value="none">Aucune</option>
                                                    <option value="home">Livraison à domicile
                                                        (+{{ config('booking.delivery_fee', 50) }} MAD)</option>
                                                    <option value="airport">Livraison à l'aéroport
                                                        (+{{ config('booking.delivery_fee', 50) }} MAD)</option>
                                                </select>
                                                <div class="invalid-feedback" id="delivery_option-error"></div>
                                                <input type="text" class="form-control mt-2 d-none" id="delivery_address"
                                                    name="delivery_address" placeholder="Adresse de livraison">
                                            </div>

                                            <!-- Fuel Policy -->
                                            <div class="mb-3">
                                                <label for="fuel_policy" class="form-control-label">Politique de Carburant
                                                    <span class="text-danger">*</span></label>
                                                <select class="form-select" id="fuel_policy" name="fuel_policy" required>
                                                    <option value="full-to-full">Plein à plein</option>
                                                    <option value="full-to-empty">Plein à vide</option>
                                                </select>
                                                <div class="invalid-feedback" id="fuel_policy-error"></div>
                                            </div>

                                            <div class="mb-3">
                                                <label for="special_requests" class="form-control-label">Demandes
                                                    Spéciales</label>
                                                <textarea class="form-control" id="special_requests" name="special_requests"
                                                    rows="3"></textarea>
                                                <div class="invalid-feedback" id="special_requests-error"></div>
                                            </div>
                                        </div>
                                    </div>

                                    <h5 class="mb-3">Détails du Prix</h5>
                                    <div class="card bg-light mb-3">
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label for="total_days" class="form-control-label">Nombre de
                                                        Jours</label>
                                                    <input type="number" class="form-control" id="total_days"
                                                        name="total_days" readonly>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <div id="availability_display" class="my-2 py-2 text-center">
                                                        <span class="badge bg-secondary">Aucun véhicule sélectionné</span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-4 mb-3">
                                                    <label for="base_price" class="form-control-label">Prix de Base
                                                        (MAD)</label>
                                                    <input type="number" step="0.01" class="form-control" id="base_price"
                                                        name="base_price" readonly>
                                                    <div class="invalid-feedback" id="base_price-error"></div>
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <label for="discount_amount" class="form-control-label">Remise
                                                        (MAD)</label>
                                                    <input type="number" step="0.01" class="form-control"
                                                        id="discount_amount" name="discount_amount" readonly>
                                                    <div class="invalid-feedback" id="discount_amount-error"></div>
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <label for="tax_amount" class="form-control-label">TVA (MAD)</label>
                                                    <input type="number" step="0.01" class="form-control" id="tax_amount"
                                                        name="tax_amount" readonly>
                                                    <div class="invalid-feedback" id="tax_amount-error"></div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label for="total_amount" class="form-control-label">Montant Total
                                                        (MAD)</label>
                                                    <input type="number" step="0.01" class="form-control" id="total_amount"
                                                        name="total_amount" readonly>
                                                    <div class="invalid-feedback" id="total_amount-error"></div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="deposit_amount" class="form-control-label">Caution
                                                        (MAD)</label>
                                                    <input type="number" step="0.01" class="form-control"
                                                        id="deposit_amount" name="deposit_amount"
                                                        value="{{ config('booking.default_deposit', 1000) }}" required>
                                                    <div class="invalid-feedback" id="deposit_amount-error"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Right column: Customer and payment details -->
                                <div class="col-md-6">
                                    <h5 class="mb-3">Informations Client</h5>
                                    <div class="card bg-light mb-3">
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label for="user_id" class="form-control-label">Compte Client
                                                    (Optionnel)</label>
                                                <select class="form-select" id="user_id" name="user_id">
                                                    <option value="">Client Invité</option>
                                                    @php
                                                        $users = \App\Models\User::orderBy('name')->get();
                                                    @endphp
                                                    @foreach($users as $user)
                                                        <option value="{{ $user->id }}" data-name="{{ $user->name }}"
                                                            data-email="{{ $user->email }}"
                                                            data-phone="{{ $user->phone ?? '' }}">
                                                            {{ $user->name }} ({{ $user->email }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <div class="invalid-feedback" id="user_id-error"></div>
                                                <div class="form-text">Si sélectionné, les détails du client seront
                                                    pré-remplis.
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label for="customer_name" class="form-control-label">Nom du Client <span
                                                        class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="customer_name"
                                                    name="customer_name" required>
                                                <div class="invalid-feedback" id="customer_name-error"></div>
                                            </div>

                                            <div class="mb-3">
                                                <label for="customer_email" class="form-control-label">Email du Client
                                                    <span class="text-danger">*</span></label>
                                                <input type="email" class="form-control" id="customer_email"
                                                    name="customer_email" required>
                                                <div class="invalid-feedback" id="customer_email-error"></div>
                                            </div>

                                            <div class="mb-3">
                                                <label for="customer_phone" class="form-control-label">Téléphone du
                                                    Client <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="customer_phone"
                                                    name="customer_phone" required>
                                                <div class="invalid-feedback" id="customer_phone-error"></div>
                                            </div>

                                            <div class="mb-3">
                                                <label for="customer_id_number" class="form-control-label">Numéro CIN
                                                    <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="customer_id_number"
                                                    name="customer_id_number" required>
                                                <div class="invalid-feedback" id="customer_id_number-error"></div>
                                            </div>

                                            <div class="mb-3">
                                                <label for="language_preference" class="form-control-label">Langue
                                                    Préférée <span class="text-danger">*</span></label>
                                                <select class="form-select" id="language_preference"
                                                    name="language_preference" required>
                                                    <option value="fr">Français</option>
                                                    <option value="ar">Arabe</option>
                                                    <option value="en">Anglais</option>
                                                </select>
                                                <div class="invalid-feedback" id="language_preference-error"></div>
                                            </div>
                                        </div>
                                    </div>

                                    <h5 class="mb-3">Paiement & Statut</h5>
                                    <div class="card bg-light mb-3">
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label for="status" class="form-control-label">Statut de Réservation
                                                    <span class="text-danger">*</span></label>
                                                <select class="form-select" id="status" name="status" required>
                                                    <option value="pending">En attente</option>
                                                    <option value="confirmed">Confirmée</option>
                                                    <option value="in_progress">En cours</option>
                                                    <option value="completed">Terminée</option>
                                                    <option value="cancelled">Annulée</option>
                                                    <option value="no_show">Non présenté</option>
                                                </select>
                                                <div class="invalid-feedback" id="status-error"></div>
                                            </div>

                                            <div class="mb-3" id="cancellation_reason_container" style="display: none;">
                                                <label for="cancellation_reason" class="form-control-label">Raison
                                                    d'Annulation <span class="text-danger">*</span></label>
                                                <textarea class="form-control" id="cancellation_reason"
                                                    name="cancellation_reason" rows="2"></textarea>
                                                <div class="invalid-feedback" id="cancellation_reason-error"></div>
                                            </div>

                                            <div class="mb-3">
                                                <label for="payment_method" class="form-control-label">Méthode de
                                                    Paiement <span class="text-danger">*</span></label>
                                                <select class="form-select" id="payment_method" name="payment_method"
                                                    required>
                                                    <option value="cash">Espèces</option>
                                                    <option value="card">Carte bancaire</option>
                                                    <option value="bank_transfer">Virement bancaire</option>
                                                    <option value="mobile_payment">Paiement mobile</option>
                                                </select>
                                                <div class="invalid-feedback" id="payment_method-error"></div>
                                            </div>

                                            <div class="mb-3">
                                                <label for="payment_status" class="form-control-label">Statut de
                                                    Paiement <span class="text-danger">*</span></label>
                                                <select class="form-select" id="payment_status" name="payment_status"
                                                    required>
                                                    <option value="unpaid">Non payé</option>
                                                    <option value="pending">En attente</option>
                                                    <option value="paid">Payé</option>
                                                    <option value="refunded">Remboursé</option>
                                                </select>
                                                <div class="invalid-feedback" id="payment_status-error"></div>
                                            </div>

                                            <div class="mb-3">
                                                <label for="transaction_id" class="form-control-label">ID de
                                                    Transaction</label>
                                                <input type="text" class="form-control" id="transaction_id"
                                                    name="transaction_id">
                                                <div class="invalid-feedback" id="transaction_id-error"></div>
                                            </div>

                                            <div class="mb-3">
                                                <label for="deposit_status" class="form-control-label">Statut de la
                                                    Caution <span class="text-danger">*</span></label>
                                                <select class="form-select" id="deposit_status" name="deposit_status"
                                                    required>
                                                    <option value="pending">En attente</option>
                                                    <option value="paid">Payée</option>
                                                    <option value="refunded">Remboursée</option>
                                                    <option value="forfeited">Perdue</option>
                                                </select>
                                                <div class="invalid-feedback" id="deposit_status-error"></div>
                                            </div>

                                            <div class="mb-3">
                                                <label for="notes" class="form-control-label">Notes Internes</label>
                                                <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                                                <div class="invalid-feedback" id="notes-error"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn bg-gradient-primary" id="saveBtn">Enregistrer</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- View Booking Modal -->
        <div class="modal fade" id="viewBookingModal" tabindex="-1" aria-labelledby="viewBookingModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable modal-70">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="viewBookingModalLabel">Détails de la Réservation</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card bg-light mb-3 car-modal-view">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0" id="view-booking-number"></h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between mb-2">
                                            <span id="view-status-badge"></span>
                                            <span id="view-payment-badge"></span>
                                        </div>
                                        <p class="mb-1"><strong>Créée le:</strong> <span id="view-created-at"></span></p>
                                        <p class="mb-0"><strong>Mise à jour le:</strong> <span id="view-updated-at"></span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-light mb-3 car-modal-view">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">Information du Véhicule</h5>
                                    </div>
                                    <div class="card-body">
                                        <h5 id="view-car-name" class="mb-2"></h5>
                                        <p class="mb-0" id="view-car-details"></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card bg-light mb-3 car-modal-view">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">Information du Client</h5>
                                    </div>
                                    <div class="card-body">
                                        <h5 id="view-customer-name" class="mb-2"></h5>
                                        <p class="mb-1" id="view-customer-email"></p>
                                        <p class="mb-1" id="view-customer-phone"></p>
                                        <p class="mb-1" id="view-customer-id-number"></p>
                                        <p class="mb-0" id="view-customer-account"></p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-light mb-3 car-modal-view">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">Détails de la Location</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row mb-2">
                                            <div class="col-md-4"><strong>Retrait:</strong></div>
                                            <div class="col-md-8" id="view-pickup-details"></div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-md-4"><strong>Retour:</strong></div>
                                            <div class="col-md-8" id="view-dropoff-details"></div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-md-4"><strong>Durée:</strong></div>
                                            <div class="col-md-8" id="view-duration"></div>
                                        </div>
                                        <div class="row mb-0">
                                            <div class="col-md-4"><strong>Options:</strong></div>
                                            <div class="col-md-8" id="view-options"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card bg-light mb-3 car-modal-view">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">Information de Paiement</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row mb-2">
                                            <div class="col-md-4"><strong>Prix de Base:</strong></div>
                                            <div class="col-md-8" id="view-base-price"></div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-md-4"><strong>Remise:</strong></div>
                                            <div class="col-md-8" id="view-discount"></div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-md-4"><strong>TVA:</strong></div>
                                            <div class="col-md-8" id="view-tax"></div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-md-4"><strong>Total:</strong></div>
                                            <div class="col-md-8 fw-bold" id="view-total"></div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-md-4"><strong>Méthode:</strong></div>
                                            <div class="col-md-8" id="view-payment-method"></div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-md-4"><strong>Transaction:</strong></div>
                                            <div class="col-md-8" id="view-transaction-id"></div>
                                        </div>
                                        <div class="row mb-0">
                                            <div class="col-md-4"><strong>Caution:</strong></div>
                                            <div class="col-md-8" id="view-deposit"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-light mb-3 car-modal-view">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">Demandes Spéciales</h5>
                                    </div>
                                    <div class="card-body">
                                        <p id="view-special-requests" class="mb-2"></p>
                                        <hr>
                                        <p class="mb-0"><strong>Notes:</strong> <span id="view-notes"></span></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="row w-100">
                            <div class="col-md-6 text-start" id="view-status-actions"></div>
                            <div class="col-md-6 text-end">
                                <button type="button" class="btn btn-outline-secondary"
                                    data-bs-dismiss="modal">Fermer</button>
                                <button type="button" id="viewEditBtn" class="btn bg-gradient-primary">Modifier</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteModalLabel">Confirmer la Suppression</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="py-3 text-center">
                            <i class="fas fa-exclamation-triangle text-warning fa-3x mb-3"></i>
                            <p>Êtes-vous sûr de vouloir supprimer cette réservation? Cette action ne peut pas être annulée.
                            </p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="button" class="btn bg-gradient-danger" id="confirmDeleteBtn">Supprimer</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- Start Rental Modal -->
        <div class="modal fade" id="startRentalModal" tabindex="-1" aria-labelledby="startRentalModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="startRentalModalLabel">Démarrer la Location</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="startRentalForm">
                            <input type="hidden" id="start_booking_id" name="booking_id">

                            <div class="mb-3">
                                <div class="card bg-light mb-3">
                                    <div class="card-body">
                                        <h6 class="mb-1" id="start_booking_number"></h6>
                                        <p class="mb-0" id="start_customer_name"></p>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="card bg-light mb-3">
                                    <div class="card-body">
                                        <h6 class="mb-1" id="start_car_name"></h6>
                                        <p class="mb-0" id="start_car_info"></p>
                                    </div>
                                </div>
                            </div>

                            <div class="alert alert-info">
                                <strong>Kilométrage prévu:</strong> <span id="expected_mileage"></span> km
                            </div>

                            <div class="mb-3">
                                <label for="start_mileage" class="form-control-label">Kilométrage de départ (km) <span
                                        class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="start_mileage" name="start_mileage" required
                                    min="0">
                                <div class="form-text">Entrez la valeur actuelle du compteur kilométrique</div>
                            </div>

                            <div class="mb-3">
                                <label for="fuel_level" class="form-control-label">Niveau de carburant <span
                                        class="text-danger">*</span></label>
                                <select class="form-select" id="fuel_level" name="fuel_level" required>
                                    <option value="100">Plein (100%)</option>
                                    <option value="75">Trois-quarts (75%)</option>
                                    <option value="50">Moitié (50%)</option>
                                    <option value="25">Quart (25%)</option>
                                    <option value="0">Vide (0%)</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="start_notes" class="form-control-label">Notes</label>
                                <textarea class="form-control" id="start_notes" name="notes" rows="3"
                                    placeholder="État du véhicule, instructions spéciales, etc."></textarea>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="button" class="btn bg-gradient-primary" id="saveStartRentalBtn">Démarrer la
                            Location</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Complete Rental Modal -->
        <div class="modal fade" id="completeRentalModal" tabindex="-1" aria-labelledby="completeRentalModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="completeRentalModalLabel">Terminer la Location</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="completeRentalForm">
                            <input type="hidden" id="complete_booking_id" name="booking_id">

                            <div class="mb-3">
                                <div class="card bg-light mb-3">
                                    <div class="card-body">
                                        <h6 class="mb-1" id="complete_booking_number"></h6>
                                        <p class="mb-0" id="complete_customer_name"></p>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="card bg-light mb-3">
                                    <div class="card-body">
                                        <h6 class="mb-1" id="complete_car_name"></h6>
                                        <p class="mb-0" id="complete_car_info"></p>
                                    </div>
                                </div>
                            </div>

                            <div class="alert alert-info">
                                <strong>Kilométrage de départ:</strong> <span id="starting_mileage"></span> km<br>
                                <strong>Limite kilométrique:</strong> <span id="mileage_limit"></span> km/jour<br>
                                <strong>Limite totale:</strong> <span id="total_mileage_limit"></span> km<br>
                                <strong>Coût kilomètre supplémentaire:</strong> <span id="extra_mileage_cost"></span> MAD/km
                            </div>

                            <div class="mb-3">
                                <label for="end_mileage" class="form-control-label">Kilométrage de retour (km) <span
                                        class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="end_mileage" name="end_mileage" required
                                    min="0">
                                <div class="form-text">Entrez la valeur finale du compteur kilométrique</div>
                            </div>

                            <div class="mb-3">
                                <label for="complete_fuel_level" class="form-control-label">Niveau de carburant <span
                                        class="text-danger">*</span></label>
                                <select class="form-select" id="complete_fuel_level" name="fuel_level" required>
                                    <option value="100">Plein (100%)</option>
                                    <option value="75">Trois-quarts (75%)</option>
                                    <option value="50">Moitié (50%)</option>
                                    <option value="25">Quart (25%)</option>
                                    <option value="0">Vide (0%)</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="damage_report" class="form-control-label">Rapport de dommages</label>
                                <textarea class="form-control" id="damage_report" name="damage_report" rows="2"
                                    placeholder="Si des dommages sont constatés, veuillez les décrire ici"></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="complete_notes" class="form-control-label">Notes</label>
                                <textarea class="form-control" id="complete_notes" name="notes" rows="3"
                                    placeholder="Commentaires supplémentaires sur le retour du véhicule"></textarea>
                            </div>

                            <div id="extra_mileage_info" class="d-none alert alert-warning">
                                <h6 class="alert-heading">Kilométrage supplémentaire détecté!</h6>
                                <p class="mb-0">Kilométrage total: <span id="total_mileage"></span> km</p>
                                <p class="mb-0">Kilométrage supplémentaire: <span id="extra_mileage"></span> km</p>
                                <p class="mb-2">Frais supplémentaires: <span id="extra_mileage_charges"></span> MAD</p>
                                <p class="mb-0"><small>Ces frais seront automatiquement ajoutés au montant total de la
                                        réservation.</small></p>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="button" class="btn bg-gradient-primary" id="saveCompleteRentalBtn">Terminer la
                            Location</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <style>
        /* Argon-style card and shadow effects */
        .card {
            box-shadow: 0 20px 27px 0 rgba(0, 0, 0, 0.05);
            border-radius: 0.75rem;
        }

        .cards-stats {
            height: 100%;
        }

        .card .card-header {
            padding: 1.5rem;
        }

        /* Make inputs look like Argon's */
        .form-control,
        .form-select {
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
            line-height: 1.4;
            border-radius: 0.5rem;
            transition: box-shadow 0.15s ease, border-color 0.15s ease;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #5e72e4;
            box-shadow: 0 3px 9px rgba(50, 50, 9, 0), 3px 4px 8px rgba(94, 114, 228, 0.1);
        }

        .form-control-label {
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
            font-weight: 600;
            color: #8392AB;
        }

        /* Buttons and gradients */
        .bg-gradient-primary {
            background: linear-gradient(310deg, #5e72e4 0%, #825ee4 100%);
        }

        .bg-gradient-success {
            background: linear-gradient(310deg, #2dce89 0%, #2dcecc 100%);
        }

        .bg-gradient-danger {
            background: linear-gradient(310deg, #f5365c 0%, #f56036 100%);
        }

        .bg-gradient-warning {
            background: linear-gradient(310deg, #fb6340 0%, #fbb140 100%);
        }

        .car-modal-view {
            height: 100%;
        }

        /* Modal styling */
        .modal-content {
            border: 0;
            border-radius: 0.75rem;
            box-shadow: 0 10px 35px -5px rgba(0, 0, 0, 0.15);
        }

        .modal-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .modal-footer {
            padding: 1.25rem 1.5rem;
            border-top: 1px solid rgba(0, 0, 0, 0.05);
        }

        .modal-70 {
            max-width: 70%;
            margin: 1.75rem auto;
        }

        .modal-70 .modal-content {
            max-height: 93vh;
        }

        .modal-70 .modal-body {
            overflow-y: auto;
            max-height: calc(93vh - 130px);
            padding: 1.5rem;
        }

        /* DataTable styling */
        table.dataTable {
            margin-top: 0 !important;
        }

        .table thead th {
            padding: 0.75rem 1.5rem;
            text-transform: uppercase;
            letter-spacing: 0.025em;
            font-size: 0.65rem;
            font-weight: 700;
            border-bottom-width: 1px;
        }

        .table td {
            white-space: nowrap;
            padding: 0.5rem 1.5rem;
            font-size: 0.875rem;
            vertical-align: middle;
            border-bottom: 1px solid #E9ECEF;
        }

        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter,
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_processing,
        .dataTables_wrapper .dataTables_paginate {
            font-size: 0.875rem;
            color: #8392AB;
            padding: 1rem 1.5rem;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: linear-gradient(310deg, #5e72e4 0%, #825ee4 100%);
            color: white !important;
            border: none;
            border-radius: 0.5rem;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: #f6f9fc;
            color: #5e72e4 !important;
            border: 1px solid #f6f9fc;
        }

        /* Badge styling */
        .badge {
            font-size: 0.85rem;
            padding: 0.5rem 1rem;
        }

        #availability_display .badge {
            font-size: 1rem;
            padding: 0.5rem 1rem;
        }

        /* Loading overlay for AJAX */
        .loading-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.7);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            border-radius: 0.75rem;
        }
    </style>
@endpush

@push('js')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // Routes for AJAX calls - Corrected route definitions
        const routes = {
            dataUrl: "{{ route('admin.bookings.data') }}",
            storeUrl: "{{ route('admin.bookings.store') }}",
            showUrl: "{{ route('admin.bookings.show', ':id') }}",
            updateUrl: "{{ route('admin.bookings.update', ':id') }}",
            destroyUrl: "{{ route('admin.bookings.destroy', ':id') }}",
            calculateUrl: "{{ route('admin.bookings.calculate-prices') }}",
            updateStatusUrl: "{{ route('admin.bookings.update-status', ':id') }}",
            updatePaymentStatusUrl: "{{ route('admin.bookings.update-payment-status', ':id') }}",
            updateDepositStatusUrl: "{{ route('admin.bookings.update-deposit-status', ':id') }}",
            exportUrl: "{{ route('admin.bookings.export') }}",
            // Rental-specific routes
            startRentalUrl: "{{ route('admin.bookings.start-rental', ':id') }}",
            completeRentalUrl: "{{ route('admin.bookings.complete-rental', ':id') }}",
            calculateMileageChargesUrl: "{{ route('admin.bookings.calculate-mileage-charges') }}"
        };

        // CSRF Token
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        // Debug routes on page load
        console.log('Routes configuration loaded:', routes);
    </script>
    <!-- Include the main booking management script -->
    <script src="{{ asset('admin/js/bookings-management.js') }}"></script>
@endpush