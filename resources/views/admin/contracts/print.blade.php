@extends('admin.layouts.master')

@section('title', 'Print Contract #' . str_pad($contract->id, 5, '0', STR_PAD_LEFT))

@section('content')
    <div class="container-fluid print-container">
        <div class="print-header">
            <!-- Replace with your brand logo -->
            <img src="{{ asset('images/brand-logo.png') }}" alt="Brand Logo" class="brand-logo">
            <div class="company-info">
                <h1>Your Company Name</h1>
                <p>Your Company Address</p>
                <p>Phone: +123-456-7890 | Email: info@yourcompany.com</p>
            </div>
        </div>

        <div class="contract-title">
            <h2>Rental Contract</h2>
            <p>Contract #CT-{{ str_pad($contract->id, 5, '0', STR_PAD_LEFT) }}</p>
        </div>

        <div class="contract-details">
            <div class="section">
                <h3>Contract Information</h3>
                <table class="info-table">
                    <tr>
                        <th>Status</th>
                        <td><span
                                class="badge bg-{{ ['active' => 'success', 'completed' => 'info', 'cancelled' => 'danger'][$contract->status] }}">{{ ucfirst($contract->status) }}</span>
                        </td>
                    </tr>
                    <tr>
                        <th>Start Date</th>
                        <td>{{ $contract->start_date->format('F d, Y') }}</td>
                    </tr>
                    <tr>
                        <th>End Date</th>
                        <td>{{ $contract->end_date->format('F d, Y') }}</td>
                    </tr>
                    <tr>
                        <th>Duration</th>
                        <td>{{ $contract->duration_in_days }} day(s) @if($contract->extension_days > 0) (Extended by
                        {{ $contract->extension_days }} day(s)) @endif</td>
                    </tr>
                    <tr>
                        <th>Daily Rate</th>
                        <td>{{ number_format($contract->rental_fee, 2) }} MAD</td>
                    </tr>
                    <tr>
                        <th>Total Amount</th>
                        <td>{{ number_format($contract->total_amount, 2) }} MAD @if($contract->discount > 0) (Discount:
                        {{ number_format($contract->discount, 2) }} MAD) @endif</td>
                    </tr>
                    <tr>
                        <th>Deposit</th>
                        <td>{{ number_format($contract->deposit_amount, 2) }} MAD</td>
                    </tr>
                    <tr>
                        <th>Payment Status</th>
                        <td><span
                                class="badge bg-{{ ['pending' => 'warning', 'partial' => 'info', 'paid' => 'success'][$contract->payment_status] }}">{{ ucfirst($contract->payment_status) }}</span>
                        </td>
                    </tr>
                    <tr>
                        <th>Created</th>
                        <td>{{ $contract->created_at->format('F d, Y') }}</td>
                    </tr>
                </table>
            </div>

            <div class="section">
                <h3>Client Information</h3>
                <table class="info-table">
                    <tr>
                        <th>Name</th>
                        <td>{{ $contract->client->full_name }}</td>
                    </tr>
                    <tr>
                        <th>Email</th>
                        <td>{{ $contract->client->email }}</td>
                    </tr>
                    <tr>
                        <th>Phone</th>
                        <td>{{ $contract->client->phone }}</td>
                    </tr>
                    <tr>
                        <th>ID Number</th>
                        <td>{{ $contract->client->id_number }}</td>
                    </tr>
                    <tr>
                        <th>License Number</th>
                        <td>{{ $contract->client->license_number }}</td>
                    </tr>
                    <tr>
                        <th>License Expiry</th>
                        <td>{{ $contract->client->license_expiry ? $contract->client->license_expiry->format('F d, Y') : 'N/A' }}
                        </td>
                    </tr>
                    <tr>
                        <th>Address</th>
                        <td>{{ $contract->client->address ?? 'N/A' }}</td>
                    </tr>
                </table>
            </div>

            <div class="section">
                <h3>Vehicle Information</h3>
                <table class="info-table">
                    <tr>
                        <th>Vehicle</th>
                        <td>{{ $contract->car->brand_name }} {{ $contract->car->model }} ({{ $contract->car->year }})</td>
                    </tr>
                    <tr>
                        <th>License Plate</th>
                        <td>{{ $contract->car->matricule }}</td>
                    </tr>
                    <tr>
                        <th>Category</th>
                        <td>{{ $contract->car->category ? $contract->car->category->name : 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Start Mileage</th>
                        <td>{{ number_format($contract->start_mileage) }} km</td>
                    </tr>
                    @if($contract->end_mileage)
                        <tr>
                            <th>End Mileage</th>
                            <td>{{ number_format($contract->end_mileage) }} km</td>
                        </tr>
                        <tr>
                            <th>Distance Traveled</th>
                            <td>{{ number_format($contract->end_mileage - $contract->start_mileage) }} km</td>
                        </tr>
                    @endif
                </table>
            </div>

            <div class="section">
                <h3>Payment History</h3>
                @if($contract->payments->isEmpty())
                    <p>No payments recorded.</p>
                @else
                    <table class="payment-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Reference</th>
                                <th>Notes</th>
                                <th>Processed By</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($contract->payments as $payment)
                                <tr>
                                    <td>{{ $payment->payment_date->format('F d, Y') }}</td>
                                    <td>{{ number_format($payment->amount, 2) }} MAD</td>
                                    <td>{{ ucfirst($payment->payment_method) }}</td>
                                    <td>{{ $payment->reference ?? 'N/A' }}</td>
                                    <td>{{ $payment->notes ?? 'N/A' }}</td>
                                    <td>{{ $payment->processedBy ? $payment->processedBy->name : 'N/A' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
                <div class="payment-summary">
                    <p><strong>Total Paid:</strong> {{ number_format($contract->total_paid, 2) }} MAD</p>
                    <p><strong>Outstanding Balance:</strong> {{ number_format($contract->outstanding_balance, 2) }} MAD</p>
                </div>
            </div>

            @if($contract->notes)
                <div class="section">
                    <h3>Notes</h3>
                    <p>{{ $contract->notes }}</p>
                </div>
            @endif
        </div>

        <div class="print-footer">
            <p>Thank you for choosing Your Company Name. For inquiries, contact us at info@yourcompany.com.</p>
            <p>Generated on {{ now()->format('F d, Y') }}</p>
        </div>
    </div>
@endsection

@push('css')
    <style>
        /* General print styles */
        body {
            font-family: 'Arial', sans-serif;
            /* Replace with your brand font */
            color: #333;
            /* Replace with your brand primary color */
            line-height: 1.5;
        }

        .print-container {
            max-width: 800px;
            margin: 20mm auto;
            padding: 0 15mm;
            background: #fff;
        }

        /* Header */
        .print-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20mm;
            border-bottom: 2px solid #007bff;
            /* Replace with your brand primary color */
            padding-bottom: 10mm;
        }

        .brand-logo {
            max-height: 50mm;
            width: auto;
        }

        .company-info h1 {
            font-size: 1.5rem;
            color: #007bff;
            /* Replace with your brand primary color */
            margin: 0;
        }

        .company-info p {
            font-size: 0.9rem;
            margin: 0;
        }

        /* Contract title */
        .contract-title {
            text-align: center;
            margin-bottom: 15mm;
        }

        .contract-title h2 {
            font-size: 1.8rem;
            color: #333;
            /* Replace with your brand primary color */
            margin: 0;
        }

        .contract-title p {
            font-size: 1rem;
            color: #666;
            margin: 5px 0 0;
        }

        /* Sections */
        .section {
            margin-bottom: 15mm;
        }

        .section h3 {
            font-size: 1.2rem;
            color: #007bff;
            /* Replace with your brand primary color */
            border-bottom: 1px solid #007bff;
            /* Replace with your brand primary color */
            padding-bottom: 5px;
            margin-bottom: 10px;
        }

        /* Info tables */
        .info-table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-table th,
        .info-table td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .info-table th {
            width: 30%;
            font-weight: bold;
            background: #f8f9fa;
            /* Replace with your brand secondary color */
        }

        .info-table td .badge {
            font-size: 0.8rem;
            padding: 5px 10px;
        }

        /* Payment table */
        .payment-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10mm;
        }

        .payment-table th,
        .payment-table td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .payment-table th {
            background: #007bff;
            /* Replace with your brand primary color */
            color: #fff;
            font-weight: bold;
        }

        .payment-table tbody tr:nth-child(even) {
            background: #f8f9fa;
            /* Replace with your brand secondary color */
        }

        .payment-summary p {
            margin: 5px 0;
            font-size: 1rem;
        }

        .payment-summary p strong {
            display: inline-block;
            width: 150px;
        }

        /* Footer */
        .print-footer {
            text-align: center;
            border-top: 2px solid #007bff;
            /* Replace with your brand primary color */
            padding-top: 10mm;
            margin-top: 20mm;
            font-size: 0.9rem;
            color: #666;
        }

        /* Print-specific styles */
        @media print {
            body {
                margin: 0;
                padding: 0;
            }

            .print-container {
                margin: 0;
                padding: 0;
                width: 100%;
            }

            .no-print,
            .btn,
            .btn-group,
            button,
            nav,
            aside,
            header,
            footer:not(.print-footer) {
                display: none !important;
            }

            .print-header,
            .print-footer {
                position: relative;
                page-break-inside: avoid;
            }

            .section {
                page-break-inside: auto;
            }

            .info-table,
            .payment-table {
                page-break-inside: auto;
            }

            .info-table tr,
            .payment-table tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }

            @page {
                size: A4;
                margin: 15mm;
            }
        }
    </style>
@endpush