@extends('admin.layouts.master')

@section('title', 'Contrat de Location #' . str_pad($contract->id, 5, '0', STR_PAD_LEFT))

@section('content')
    <div class="contract-wrapper">
        <!-- Header with geometric pattern -->
        <div class="contract-header">
            <div class="pattern-overlay"></div>
            <div class="header-content">
                <div class="logo-section">
                    <img src="{{ asset(config('company.logo')) }}" alt="{{ config('company.name') }}">
                </div>
                <div class="company-details">
                    <h1>{{ config('company.name') }}</h1>
                    <div class="contact-details">
                        <div class="contact-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <span>{{ config('company.address') }}</span>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-phone-alt"></i>
                            <span>{{ config('company.phone') }}</span>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-envelope"></i>
                            <span>{{ config('company.email') }}</span>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-globe"></i>
                            <span>{{ config('company.website') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contract title with badge -->
        <div class="contract-title-section">
            <div class="contract-badge">
                <span
                    class="badge-status bg-{{ ['active' => 'success', 'completed' => 'info', 'cancelled' => 'danger'][$contract->status] }}">{{ ucfirst($contract->status) }}</span>
            </div>
            <div class="title-content">
                <h2>Contrat de Location</h2>
                <div class="contract-id">
                    <span class="id-label">N° de Contrat:</span>
                    <span class="id-value">CT-{{ str_pad($contract->id, 5, '0', STR_PAD_LEFT) }}</span>
                </div>
            </div>
            <div class="contract-dates">
                <div class="date-block">
                    <div class="date-icon"><i class="far fa-calendar-alt"></i></div>
                    <div class="date-details">
                        <div class="date-label">Date de début</div>
                        <div class="date-value">{{ $contract->start_date->format('d M Y') }}</div>
                    </div>
                </div>
                <div class="date-separator">
                    <i class="fas fa-arrow-right"></i>
                </div>
                <div class="date-block">
                    <div class="date-icon"><i class="far fa-calendar-check"></i></div>
                    <div class="date-details">
                        <div class="date-label">Date de fin</div>
                        <div class="date-value">{{ $contract->end_date->format('d M Y') }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main content in accordion style -->
        <div class="contract-main">
            <!-- Vehicle information with illustration -->
            <div class="info-card vehicle-card">
                <div class="card-header"
                    onclick="document.getElementById('vehicle-details').style.display = document.getElementById('vehicle-details').style.display === 'none' ? 'block' : 'none';">
                    <div class="header-icon car-icon">
                        <i class="fas fa-car"></i>
                    </div>
                    <h3>Informations du Véhicule</h3>
                    <div class="toggle-icon">
                        <i class="fas fa-chevron-down"></i>
                    </div>
                </div>
                <div class="card-body" id="vehicle-details">
                    <div class="car-details-wrapper">
                        <div class="car-image">
                            <div class="car-silhouette">
                                <i class="fas fa-car-side"></i>
                                <div class="car-name">{{ $contract->car->brand_name }} {{ $contract->car->model }}</div>
                            </div>
                        </div>
                        <div class="car-specs">
                            <div class="spec-item">
                                <div class="spec-label"><i class="fas fa-tag"></i> Marque & Modèle</div>
                                <div class="spec-value">{{ $contract->car->brand_name }} {{ $contract->car->model }}
                                    ({{ $contract->car->year }})</div>
                            </div>
                            <div class="spec-item">
                                <div class="spec-label"><i class="fas fa-id-card"></i> Immatriculation</div>
                                <div class="spec-value">{{ $contract->car->matricule }}</div>
                            </div>
                            <div class="spec-item">
                                <div class="spec-label"><i class="fas fa-layer-group"></i> Catégorie</div>
                                <div class="spec-value">
                                    {{ $contract->car->category ? $contract->car->category->name : 'N/A' }}
                                </div>
                            </div>
                            <div class="spec-item">
                                <div class="spec-label"><i class="fas fa-tachometer-alt"></i> Kilométrage Initial</div>
                                <div class="spec-value">{{ number_format($contract->start_mileage) }} km</div>
                            </div>
                            @if($contract->end_mileage)
                                <div class="spec-item">
                                    <div class="spec-label"><i class="fas fa-tachometer-alt"></i> Kilométrage Final</div>
                                    <div class="spec-value">{{ number_format($contract->end_mileage) }} km</div>
                                </div>
                                <div class="spec-item highlight-item">
                                    <div class="spec-label"><i class="fas fa-road"></i> Distance Parcourue</div>
                                    <div class="spec-value">
                                        {{ number_format($contract->end_mileage - $contract->start_mileage) }} km
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Client information -->
            <div class="info-card client-card">
                <div class="card-header"
                    onclick="document.getElementById('client-details').style.display = document.getElementById('client-details').style.display === 'none' ? 'block' : 'none';">
                    <div class="header-icon client-icon">
                        <i class="fas fa-user"></i>
                    </div>
                    <h3>Informations du Client</h3>
                    <div class="toggle-icon">
                        <i class="fas fa-chevron-down"></i>
                    </div>
                </div>
                <div class="card-body" id="client-details">
                    <div class="client-details-grid">
                        <div class="detail-item">
                            <div class="detail-icon"><i class="fas fa-user-circle"></i></div>
                            <div class="detail-content">
                                <div class="detail-label">Nom Complet</div>
                                <div class="detail-value">{{ $contract->client->full_name }}</div>
                            </div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-icon"><i class="fas fa-envelope"></i></div>
                            <div class="detail-content">
                                <div class="detail-label">Email</div>
                                <div class="detail-value">{{ $contract->client->email }}</div>
                            </div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-icon"><i class="fas fa-phone"></i></div>
                            <div class="detail-content">
                                <div class="detail-label">Téléphone</div>
                                <div class="detail-value">{{ $contract->client->phone }}</div>
                            </div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-icon"><i class="fas fa-id-card"></i></div>
                            <div class="detail-content">
                                <div class="detail-label">Numéro de Carte d'Identité</div>
                                <div class="detail-value">{{ $contract->client->id_number }}</div>
                            </div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-icon"><i class="fas fa-id-badge"></i></div>
                            <div class="detail-content">
                                <div class="detail-label">Numéro de Permis</div>
                                <div class="detail-value">{{ $contract->client->license_number }}</div>
                            </div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-icon"><i class="fas fa-calendar-times"></i></div>
                            <div class="detail-content">
                                <div class="detail-label">Expiration du Permis</div>
                                <div class="detail-value">
                                    {{ $contract->client->license_expiry ? $contract->client->license_expiry->format('d/m/Y') : 'N/A' }}
                                </div>
                            </div>
                        </div>
                        <div class="detail-item full-width">
                            <div class="detail-icon"><i class="fas fa-map-marked-alt"></i></div>
                            <div class="detail-content">
                                <div class="detail-label">Adresse</div>
                                <div class="detail-value">{{ $contract->client->address ?? 'N/A' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment information -->
            <div class="info-card payment-card">
                <div class="card-header"
                    onclick="document.getElementById('payment-details').style.display = document.getElementById('payment-details').style.display === 'none' ? 'block' : 'none';">
                    <div class="header-icon payment-icon">
                        <i class="fas fa-hand-holding-usd"></i>
                    </div>
                    <h3>Informations de Paiement</h3>
                    <div class="toggle-icon">
                        <i class="fas fa-chevron-down"></i>
                    </div>
                </div>
                <div class="card-body" id="payment-details">
                    <div class="payment-details-wrapper">
                        <div class="payment-summary-boxes">
                            <div class="summary-box">
                                <div class="box-value">{{ $contract->duration_in_days }}
                                    @if($contract->extension_days > 0)
                                        <span class="highlight">(+{{ $contract->extension_days }})</span>
                                    @endif
                                </div>
                                <div class="box-label">Jours</div>
                                <div class="box-icon"><i class="fas fa-calendar-day"></i></div>
                            </div>
                            <div class="summary-box">
                                <div class="box-value">{{ number_format($contract->rental_fee, 0) }}</div>
                                <div class="box-label">{{ config('company.currency') }}/jour</div>
                                <div class="box-icon"><i class="fas fa-tag"></i></div>
                            </div>
                            <div class="summary-box">
                                <div class="box-value">{{ number_format($contract->total_amount, 0) }}</div>
                                <div class="box-label">{{ config('company.currency') }} Total</div>
                                <div class="box-icon"><i class="fas fa-coins"></i></div>
                            </div>
                            @if($contract->discount > 0)
                                <div class="summary-box discount-box">
                                    <div class="box-value">{{ number_format($contract->discount, 0) }}</div>
                                    <div class="box-label">{{ config('company.currency') }} Remise</div>
                                    <div class="box-icon"><i class="fas fa-percentage"></i></div>
                                </div>
                            @endif
                            <div class="summary-box deposit-box">
                                <div class="box-value">{{ number_format($contract->deposit_amount, 0) }}</div>
                                <div class="box-label">{{ config('company.currency') }} Caution</div>
                                <div class="box-icon"><i class="fas fa-shield-alt"></i></div>
                            </div>
                        </div>

                        <div class="payment-status-wrapper">
                            <div class="status-label">Statut de Paiement:</div>
                            <div class="status-badge status-{{ $contract->payment_status }}">
                                <i
                                    class="fas fa-{{ ['pending' => 'clock', 'partial' => 'dot-circle', 'paid' => 'check-circle'][$contract->payment_status] }}"></i>
                                {{ ['pending' => 'En attente', 'partial' => 'Partiel', 'paid' => 'Payé'][$contract->payment_status] }}
                            </div>
                        </div>

                        <div class="payment-history">
                            <h4><i class="fas fa-history"></i> Historique des Paiements</h4>

                            @if($contract->payments->isEmpty())
                                <div class="no-payments">
                                    <i class="far fa-file-alt"></i>
                                    <p>Aucun paiement enregistré.</p>
                                </div>
                            @else
                                <div class="payment-table-wrapper">
                                    <table class="payment-table">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Montant</th>
                                                <th>Méthode</th>
                                                <th>Référence</th>
                                                <th>Notes</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($contract->payments as $payment)
                                                <tr>
                                                    <td>{{ $payment->payment_date->format('d/m/Y') }}</td>
                                                    <td>{{ number_format($payment->amount, 2) }} {{ config('company.currency') }}
                                                    </td>
                                                    <td>
                                                        <span class="payment-method">
                                                            <i
                                                                class="fas fa-{{ ['cash' => 'money-bill-wave', 'card' => 'credit-card', 'transfer' => 'exchange-alt', 'check' => 'money-check'][$payment->payment_method] ?? 'money-bill' }}"></i>
                                                            {{ ucfirst($payment->payment_method) }}
                                                        </span>
                                                    </td>
                                                    <td>{{ $payment->reference ?? 'N/A' }}</td>
                                                    <td>{{ $payment->notes ?? 'N/A' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif

                            <div class="balance-summary">
                                <div class="balance-item">
                                    <span class="balance-label">Total Payé:</span>
                                    <span class="balance-value">{{ number_format($contract->total_paid, 2) }}
                                        {{ config('company.currency') }}</span>
                                </div>
                                <div class="balance-item">
                                    <span class="balance-label">Solde Restant:</span>
                                    <span
                                        class="balance-value {{ $contract->outstanding_balance > 0 ? 'text-danger' : 'text-success' }}">
                                        {{ number_format($contract->outstanding_balance, 2) }}
                                        {{ config('company.currency') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notes section if available -->
            @if($contract->notes)
                <div class="info-card notes-card">
                    <div class="card-header"
                        onclick="document.getElementById('notes-details').style.display = document.getElementById('notes-details').style.display === 'none' ? 'block' : 'none';">
                        <div class="header-icon notes-icon">
                            <i class="fas fa-sticky-note"></i>
                        </div>
                        <h3>Notes</h3>
                        <div class="toggle-icon">
                            <i class="fas fa-chevron-down"></i>
                        </div>
                    </div>
                    <div class="card-body" id="notes-details">
                        <div class="notes-content">
                            <i class="fas fa-quote-left quote-icon"></i>
                            <p>{{ $contract->notes }}</p>
                            <i class="fas fa-quote-right quote-icon"></i>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Signatures section -->
        <div class="signatures-section">
            <div class="signature-divider">
                <span>Signatures</span>
            </div>

            <div class="signatures-container">
                <div class="signature-block">
                    <div class="signature-title">Représentant de la Société</div>
                    <div class="signature-line"></div>
                    <p class="signature-caption">Nom et signature</p>
                    <div class="company-stamp">
                        <div class="stamp-placeholder">
                            <i class="fas fa-stamp"></i>
                            <span>Cachet</span>
                        </div>
                    </div>
                </div>

                <div class="signature-block">
                    <div class="signature-title">Client</div>
                    <div class="signature-line"></div>
                    <p class="signature-caption">Signature précédée de "Lu et approuvé"</p>
                </div>
            </div>
        </div>

        <!-- Footer with moroccan pattern -->
        <div class="contract-footer">
            <div class="pattern-overlay footer-pattern"></div>
            <div class="footer-content">
                <p>{{ config('company.footer_text') }}</p>
                <p>Généré le {{ now()->format('d/m/Y') }} à {{ now()->format('H:i') }}</p>
            </div>
        </div>
    </div>
@endsection

@push('css')
    <style>
        /* Modern Professional Styles with Moroccan Accents */
    :root {
        --primary-color: #2c3e50; /* Deep navy blue - professional */
        --secondary-color: #f8f9fa; /* Light gray background */
        --accent-color: #b88e48; /* Moroccan gold accent */
        --text-color: #333333;
        --light-text: #6c757d;
        --border-color: #e0e0e0;
        --success-color: #28a745;
        --info-color: #17a2b8;
        --warning-color: #ffc107;
        --danger-color: #dc3545;
        --highlight-color: #f8f1e5; /* Light gold for highlights */
        --card-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
        --border-radius: 6px;
        --pattern-opacity: 0.08;
    }

    body {
        font-family: 'Roboto', 'Helvetica Neue', Arial, sans-serif;
        color: var(--text-color);
        line-height: 1.6;
        background-color: #f5f5f5;
        margin: 0;
        padding: 0;
    }

    .contract-wrapper {
        max-width: 210mm;
        margin: 20px auto;
        background: white;
        box-shadow: 0 5px 30px rgba(0, 0, 0, 0.1);
        position: relative;
        overflow: hidden;
    }

    /* Modern Header with Subtle Pattern */
    .contract-header {
        background: linear-gradient(135deg, var(--primary-color) 0%, #1a2a3a 100%);
        color: white;
        padding: 40px 30px 30px;
        position: relative;
        overflow: hidden;
    }

    .contract-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-image: url("data:image/svg+xml,%3Csvg width='80' height='80' viewBox='0 0 80 80' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M0 0h80v80H0V0zm10 10h60v60H10V10z' fill='%23b88e48' fill-opacity='0.05'/%3E%3C/svg%3E");
        opacity: 0.15;
    }

    .header-content {
        display: flex;
        align-items: center;
        position: relative;
        z-index: 2;
    }

    .logo-section {
        flex: 0 0 100px;
        margin-right: 30px;
    }

    .logo-section img {
        width: 100%;
        height: auto;
        border-radius: 4px;
        border: 1px solid rgba(255,255,255,0.2);
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .company-details h1 {
        font-family: 'Montserrat', sans-serif;
        font-weight: 600;
        font-size: 24px;
        margin: 0 0 10px 0;
        letter-spacing: 0.5px;
    }

    .contact-details {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        margin-top: 15px;
    }

    .contact-item {
        display: flex;
        align-items: center;
        font-size: 13px;
        opacity: 0.9;
    }

    .contact-item i {
        margin-right: 8px;
        font-size: 14px;
        color: var(--accent-color);
    }

    /* Modern Title Section */
    .contract-title-section {
        background: white;
        padding: 25px 30px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        position: relative;
        border-bottom: 1px solid var(--border-color);
    }

    .contract-badge {
        position: absolute;
        top: -15px;
        left: 30px;
        z-index: 3;
    }

    .badge-status {
        padding: 6px 18px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        letter-spacing: 0.5px;
        color: white;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }

    .title-content h2 {
        font-family: 'Montserrat', sans-serif;
        font-weight: 500;
        font-size: 24px;
        color: var(--primary-color);
        margin: 0 0 5px 0;
        letter-spacing: 0.3px;
    }

    .contract-id {
        font-size: 13px;
        color: var(--light-text);
    }

    .contract-dates {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .date-block {
        display: flex;
        align-items: center;
        background: var(--secondary-color);
        padding: 10px 15px;
        border-radius: var(--border-radius);
        min-width: 140px;
    }

    .date-icon {
        width: 28px;
        height: 28px;
        background: var(--primary-color);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 10px;
        font-size: 12px;
    }

    .date-details {
        line-height: 1.3;
    }

    .date-label {
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: var(--light-text);
    }

    .date-value {
        font-size: 14px;
        font-weight: 500;
        color: var(--text-color);
    }

    .date-separator {
        color: var(--accent-color);
        font-size: 14px;
    }

    /* Modern Card Design */
    .info-card {
        margin: 0 30px 25px;
        border-radius: var(--border-radius);
        overflow: hidden;
        box-shadow: var(--card-shadow);
        border: 1px solid var(--border-color);
        background: white;
        transition: all 0.3s ease;
    }

    .info-card:hover {
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        transform: translateY(-2px);
    }

    .card-header {
        padding: 18px 25px;
        display: flex;
        align-items: center;
        cursor: pointer;
        background: white;
        border-bottom: 1px solid var(--border-color);
        position: relative;
    }

    .card-header::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 2px;
        background: linear-gradient(90deg, var(--accent-color) 0%, var(--primary-color) 100%);
        opacity: 0.7;
    }

    .header-icon {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 15px;
        color: white;
        font-size: 16px;
        background: var(--primary-color);
    }

    .card-header h3 {
        font-family: 'Montserrat', sans-serif;
        font-size: 16px;
        font-weight: 500;
        margin: 0;
        color: var(--text-color);
    }

    .toggle-icon {
        margin-left: auto;
        color: var(--accent-color);
        transition: all 0.3s ease;
    }

    .card-body {
        padding: 25px;
        background: white;
    }

    /* Vehicle Section - Modern Layout */
    .car-details-wrapper {
        display: flex;
        gap: 25px;
    }

    .car-image {
        flex: 0 0 180px;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .car-silhouette {
        width: 100%;
        height: 120px;
        background: var(--secondary-color);
        border-radius: var(--border-radius);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        margin-bottom: 15px;
        position: relative;
        overflow: hidden;
    }

    .car-silhouette::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: var(--accent-color);
    }

    .car-silhouette i {
        font-size: 42px;
        color: var(--primary-color);
        margin-bottom: 8px;
        opacity: 0.8;
    }

    .car-name {
        font-size: 14px;
        font-weight: 500;
        text-align: center;
    }

    .car-specs {
        flex: 1;
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
    }

    .spec-item {
        display: flex;
        flex-direction: column;
        padding-bottom: 10px;
        border-bottom: 1px dashed var(--border-color);
    }

    .spec-label {
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: var(--light-text);
        margin-bottom: 5px;
        display: flex;
        align-items: center;
    }

    .spec-label i {
        margin-right: 8px;
        color: var(--accent-color);
        font-size: 12px;
    }

    .spec-value {
        font-size: 15px;
        font-weight: 500;
    }

    .highlight-item {
        background: var(--highlight-color);
        padding: 12px;
        border-radius: var(--border-radius);
        grid-column: span 2;
        border-left: 3px solid var(--accent-color);
    }

    /* Client Section - Modern Grid */
    .client-details-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
    }

    .detail-item {
        display: flex;
        align-items: flex-start;
        padding-bottom: 12px;
        border-bottom: 1px dashed var(--border-color);
    }

    .detail-item.full-width {
        grid-column: span 2;
    }

    .detail-icon {
        width: 28px;
        height: 28px;
        background: var(--secondary-color);
        color: var(--accent-color);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 12px;
        font-size: 12px;
    }

    .detail-label {
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: var(--light-text);
        margin-bottom: 3px;
    }

    .detail-value {
        font-size: 15px;
        font-weight: 500;
    }

    /* Payment Section - Modern Layout */
    .payment-summary-boxes {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
        gap: 15px;
        margin-bottom: 20px;
    }

    .summary-box {
        background: white;
        border-radius: var(--border-radius);
        padding: 18px 15px;
        text-align: center;
        border: 1px solid var(--border-color);
        position: relative;
        transition: all 0.3s ease;
    }

    .summary-box:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    }

    .box-value {
        font-family: 'Montserrat', sans-serif;
        font-size: 24px;
        font-weight: 600;
        margin-bottom: 5px;
        color: var(--primary-color);
    }

    .box-value .highlight {
        color: var(--accent-color);
        font-size: 16px;
    }

    .box-label {
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: var(--light-text);
    }

    .box-icon {
        position: absolute;
        top: 12px;
        right: 12px;
        color: var(--border-color);
        font-size: 16px;
    }

    .discount-box {
        border-color: var(--accent-color);
    }

    .discount-box .box-value,
    .discount-box .box-icon {
        color: var(--accent-color);
    }

    .deposit-box {
        border-color: var(--primary-color);
    }

    .deposit-box .box-value,
    .deposit-box .box-icon {
        color: var(--primary-color);
    }

    .payment-status-wrapper {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 15px;
        background: var(--secondary-color);
        border-radius: var(--border-radius);
        margin-bottom: 20px;
    }

    .status-label {
        font-weight: 500;
        font-size: 14px;
    }

    .status-badge {
        padding: 6px 15px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .status-badge i {
        font-size: 12px;
    }

    .status-pending {
        background: #fff3cd;
        color: #856404;
    }

    .status-partial {
        background: #d1ecf1;
        color: #0c5460;
    }

    .status-paid {
        background: #d4edda;
        color: #155724;
    }

    .payment-history h4 {
        font-size: 15px;
        font-weight: 500;
        margin: 0 0 15px 0;
        display: flex;
        align-items: center;
        gap: 8px;
        color: var(--primary-color);
    }

    .payment-history h4 i {
        color: var(--accent-color);
    }

    .payment-table-wrapper {
        border-radius: var(--border-radius);
        overflow: hidden;
        border: 1px solid var(--border-color);
    }

    .payment-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 14px;
    }

    .payment-table th {
        background: var(--primary-color);
        color: white;
        font-weight: 500;
        padding: 12px 15px;
        text-align: left;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .payment-table td {
        padding: 12px 15px;
        border-bottom: 1px solid var(--border-color);
    }

    .payment-table tr:nth-child(even) {
        background: var(--secondary-color);
    }

    .payment-table tr:last-child td {
        border-bottom: none;
    }

    .payment-method {
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .balance-summary {
        display: flex;
        justify-content: flex-end;
        gap: 25px;
        margin-top: 20px;
        padding-top: 15px;
        border-top: 1px dashed var(--border-color);
    }

    .balance-item {
        font-size: 15px;
        display: flex;
        align-items: center;
    }

    .balance-label {
        font-weight: 500;
        margin-right: 8px;
    }

    .balance-value {
        font-weight: 600;
        font-family: 'Montserrat', sans-serif;
    }

    .text-danger {
        color: var(--danger-color);
    }

    .text-success {
        color: var(--success-color);
    }

    /* Notes Section - Modern Design */
    .notes-content {
        position: relative;
        padding: 20px;
        background: var(--highlight-color);
        border-radius: var(--border-radius);
        border-left: 3px solid var(--accent-color);
    }

    .notes-content p {
        margin: 0;
        font-style: italic;
        color: var(--text-color);
    }

    .quote-icon {
        position: absolute;
        color: rgba(0,0,0,0.1);
        font-size: 24px;
    }

    .fa-quote-left {
        top: 10px;
        left: 10px;
    }

    .fa-quote-right {
        bottom: 10px;
        right: 10px;
    }

    /* Modern Signature Section */
    .signatures-section {
        padding: 30px;
        margin: 0 30px 30px;
        background: white;
        border-radius: var(--border-radius);
        box-shadow: var(--card-shadow);
        border: 1px solid var(--border-color);
        position: relative;
    }

    .signature-divider {
        text-align: center;
        position: relative;
        margin-bottom: 30px;
    }

    .signature-divider:before {
        content: '';
        position: absolute;
        top: 50%;
        left: 0;
        right: 0;
        height: 1px;
        background: var(--border-color);
        z-index: 1;
    }

    .signature-divider span {
        position: relative;
        z-index: 2;
        background: white;
        padding: 0 15px;
        font-family: 'Montserrat', sans-serif;
        font-size: 14px;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: var(--primary-color);
    }

    .signatures-container {
        display: flex;
        gap: 40px;
    }

    .signature-block {
        flex: 1;
    }

    .signature-title {
        font-weight: 500;
        margin-bottom: 15px;
        font-size: 14px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: var(--light-text);
    }

    .signature-line {
        height: 1px;
        background: var(--border-color);
        margin-bottom: 5px;
        position: relative;
        padding: 30px 0;
    }

    .signature-line:after {
        content: '';
        position: absolute;
        top: 50%;
        left: 0;
        right: 0;
        height: 1px;
        background: var(--border-color);
    }

    .signature-caption {
        font-size: 11px;
        color: var(--light-text);
        text-align: center;
        margin: 5px 0 0;
    }

    .company-stamp {
        margin-top: 20px;
        display: flex;
        justify-content: center;
    }

    .stamp-placeholder {
        width: 100px;
        height: 100px;
        border: 1px dashed var(--border-color);
        border-radius: 50%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: var(--light-text);
    }

    .stamp-placeholder i {
        font-size: 24px;
        margin-bottom: 5px;
        opacity: 0.5;
    }

    .stamp-placeholder span {
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* Modern Footer with Moroccan Pattern */
    .contract-footer {
        background: var(--primary-color);
        color: white;
        padding: 20px 30px;
        text-align: center;
        position: relative;
    }

    .contract-footer::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 5px;
        background: linear-gradient(90deg, transparent 0%, var(--accent-color) 50%, transparent 100%);
        opacity: 0.3;
    }

    .footer-content {
        position: relative;
        z-index: 2;
    }

    .footer-content p {
        margin: 5px 0;
        font-size: 12px;
        opacity: 0.8;
    }

    /* Responsive Adjustments */
    @media (max-width: 768px) {
        .header-content {
            flex-direction: column;
            text-align: center;
        }

        .logo-section {
            margin-right: 0;
            margin-bottom: 20px;
        }

        .contact-details {
            justify-content: center;
        }

        .contract-title-section {
            flex-direction: column;
            gap: 20px;
        }

        .contract-dates {
            width: 100%;
            justify-content: center;
        }

        .car-details-wrapper {
            flex-direction: column;
        }

        .car-image {
            margin-bottom: 20px;
        }

        .client-details-grid {
            grid-template-columns: 1fr;
        }

        .payment-summary-boxes {
            grid-template-columns: 1fr 1fr;
        }

        .signatures-container {
            flex-direction: column;
            gap: 30px;
        }
    }

    @media (max-width: 480px) {
        .contract-header,
        .contract-title-section,
        .contract-main,
        .signatures-section {
            padding-left: 20px;
            padding-right: 20px;
        }

        .info-card {
            margin-left: 15px;
            margin-right: 15px;
        }

        .payment-summary-boxes {
            grid-template-columns: 1fr;
        }

        .car-specs {
            grid-template-columns: 1fr;
        }

        .highlight-item {
            grid-column: span 1;
        }

        .balance-summary {
            flex-direction: column;
            gap: 10px;
            align-items: flex-end;
        }
    }

    /* Print Styles */
    @media print {
        body {
            background: white;
            font-size: 12pt;
        }

        .contract-wrapper {
            width: 100%;
            margin: 0;
            box-shadow: none;
            border-radius: 0;
        }

        .info-card {
            page-break-inside: avoid;
            box-shadow: none;
            border: 1px solid #ddd;
        }

        .card-header {
            cursor: default;
        }

        .toggle-icon {
            display: none;
        }

        .card-body {
            display: block !important;
        }

        @page {
            size: A4;
            margin: 15mm;
        }
    }
       </style>
@endpush

@push('js')
    <script>
        // Make sure all sections are initially visible when page loads
        document.addEventListener('DOMContentLoaded', function () {
            // Set initial states for all sections
            const sections = ['vehicle-details', 'client-details', 'payment-details'];

            if (document.getElementById('notes-details')) {
                sections.push('notes-details');
            }

            sections.forEach(id => {
                const section = document.getElementById(id);
                if (section) {
                    section.style.display = 'block';
                }
            });

            // Apply toggle effect to the icons
            const toggleIcons = document.querySelectorAll('.toggle-icon i');
            toggleIcons.forEach(icon => {
                icon.classList.remove('fa-chevron-down');
                icon.classList.add('fa-chevron-up');
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
        // Add corner decorations
        var wrapper = document.querySelector('.contract-wrapper');
        if (wrapper) {
            // Top left corner
            var cornerTL = document.createElement('div');
            cornerTL.className = 'corner-decoration-tl';
            wrapper.appendChild(cornerTL);

            // Bottom right corner
            var cornerBR = document.createElement('div');
            cornerBR.className = 'corner-decoration-br';
            wrapper.appendChild(cornerBR);
        }

        // Add section dividers before each card header
        var cardHeaders = document.querySelectorAll('.card-header');
        cardHeaders.forEach(function(header, index) {
            if (index > 0) {
                var divider = document.createElement('div');
                divider.className = 'section-divider';
                header.parentNode.insertBefore(divider, header);
            }
        });
    });
    </script>
@endpush