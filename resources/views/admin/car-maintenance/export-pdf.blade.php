<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance History - {{ $car->brand_name }} {{ $car->model }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            color: #344767;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 10px;
            border-bottom: 1px solid #ddd;
        }

        .header h1 {
            margin-bottom: 5px;
            color: #344767;
        }

        .header p {
            margin-top: 0;
            color: #8392AB;
        }

        .car-details {
            margin-bottom: 30px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 10px;
        }

        .car-details h2 {
            margin-top: 0;
            color: #344767;
        }

        .detail-row {
            display: flex;
            flex-wrap: wrap;
            margin-bottom: 10px;
        }

        .detail-item {
            flex: 1;
            min-width: 200px;
            margin-bottom: 10px;
        }

        .detail-label {
            font-weight: bold;
            color: #8392AB;
            font-size: 0.9em;
        }

        .maintenance-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        .maintenance-table th {
            background-color: #f1f1f1;
            padding: 12px 15px;
            text-align: left;
            font-weight: bold;
            color: #344767;
            border-bottom: 2px solid #ddd;
        }

        .maintenance-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #ddd;
        }

        .maintenance-table tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .maintenance-table tr:hover {
            background-color: #f1f4ff;
        }

        .badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 0.85em;
            font-weight: bold;
            text-align: center;
        }

        .badge-success {
            background-color: #2dce89;
            color: white;
        }

        .badge-warning {
            background-color: #fb6340;
            color: white;
        }

        .badge-danger {
            background-color: #f5365c;
            color: white;
        }

        .badge-info {
            background-color: #11cdef;
            color: white;
        }

        .badge-primary {
            background-color: #5e72e4;
            color: white;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 0.9em;
            color: #8392AB;
            padding-top: 20px;
            border-top: 1px solid #ddd;
        }

        .summary {
            margin-bottom: 30px;
        }

        .summary h2 {
            color: #344767;
        }

        .summary-item {
            display: flex;
            margin-bottom: 15px;
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
        }

        .summary-item-label {
            flex: 1;
            font-weight: bold;
            color: #344767;
        }

        .summary-item-value {
            flex: 2;
            text-align: right;
        }

        @media print {
            body {
                padding: 0;
                font-size: 12px;
            }

            .no-print {
                display: none;
            }

            .header {
                margin-bottom: 20px;
            }

            .maintenance-table th,
            .maintenance-table td {
                padding: 8px 10px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>Maintenance History Report</h1>
            <p>{{ $car->brand_name }} {{ $car->model }} ({{ $car->year }}) - {{ $car->matricule }}</p>
            <p>Generated on {{ now()->format('F d, Y') }}</p>
        </div>

        <div class="car-details">
            <h2>Vehicle Information</h2>
            <div class="detail-row">
                <div class="detail-item">
                    <div class="detail-label">Make & Model</div>
                    <div>{{ $car->brand_name }} {{ $car->model }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Year</div>
                    <div>{{ $car->year }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">License Plate</div>
                    <div>{{ $car->matricule }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">VIN/Chassis Number</div>
                    <div>{{ $car->chassis_number }}</div>
                </div>
            </div>

            <div class="detail-row">
                <div class="detail-item">
                    <div class="detail-label">Current Mileage</div>
                    <div>{{ number_format($car->mileage) }} km</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Fuel Type</div>
                    <div>{{ ucfirst($car->fuel_type) }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Transmission</div>
                    <div>{{ ucfirst($car->transmission) }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Current Status</div>
                    <div>{{ ucfirst($car->status) }}</div>
                </div>
            </div>
        </div>

        <div class="summary">
            <h2>Maintenance Summary</h2>
            <div class="summary-item">
                <div class="summary-item-label">Total Maintenance Records</div>
                <div class="summary-item-value">{{ $car->maintenance->count() }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-item-label">Total Maintenance Cost</div>
                <div class="summary-item-value">{{ number_format($car->maintenance->sum('cost'), 2) }} MAD</div>
            </div>
            <div class="summary-item">
                <div class="summary-item-label">Last Service Date</div>
                <div class="summary-item-value">
                    @if($car->maintenance->count() > 0)
                        {{ $car->maintenance->sortByDesc('date_performed')->first()->date_performed->format('F d, Y') }}
                    @else
                        N/A
                    @endif
                </div>
            </div>
            <div class="summary-item">
                <div class="summary-item-label">Next Scheduled Maintenance</div>
                <div class="summary-item-value">
                    @if($car->nextMaintenanceDate)
                        {{ $car->nextMaintenanceDate->format('F d, Y') }}
                    @elseif($car->nextMaintenanceMileage)
                        At {{ number_format($car->nextMaintenanceMileage) }} km
                    @else
                        Not scheduled
                    @endif
                </div>
            </div>
        </div>

        <h2>Maintenance History</h2>

        @if($car->maintenance->count() > 0)
            <table class="maintenance-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Service Type</th>
                        <th>Mileage</th>
                        <th>Next Due</th>
                        <th>Performed By</th>
                        <th>Cost</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($car->maintenance->sortByDesc('date_performed') as $record)
                        <tr>
                            <td>{{ $record->date_performed->format('d/m/Y') }}</td>
                            <td>{{ ucfirst(str_replace('_', ' ', $record->maintenance_type)) }}</td>
                            <td>{{ number_format($record->mileage_at_service) }} km</td>
                            <td>
                                @if($record->next_due_date)
                                    <div>{{ $record->next_due_date->format('d/m/Y') }}</div>
                                @endif

                                @if($record->next_due_mileage)
                                    <div>{{ number_format($record->next_due_mileage) }} km</div>
                                @endif

                                @if(!$record->next_due_date && !$record->next_due_mileage)
                                    N/A
                                @endif
                            </td>
                            <td>{{ $record->performed_by ?: 'N/A' }}</td>
                            <td>{{ $record->cost ? number_format($record->cost, 2) . ' MAD' : 'N/A' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p>No maintenance records found for this vehicle.</p>
        @endif

        <div class="footer">
            <p>This report was generated from the car rental management system.</p>
            <div class="no-print">
                <button onclick="window.print()">Print Report</button>
                <button onclick="window.close()">Close</button>
            </div>
        </div>
    </div>
</body>

</html>