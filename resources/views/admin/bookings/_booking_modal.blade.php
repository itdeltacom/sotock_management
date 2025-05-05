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
                                            <textarea class="form-control" id="notes" name="notes"
                                                rows="3"></textarea>
                                            <div class="invalid-feedback" id="notes-error"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary"
                            data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn bg-gradient-primary" id="saveBtn">Enregistrer</button>
                    </div>
            </form>
        </div>
    </div>
</div>