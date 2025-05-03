<?php

namespace App\Http\Controllers\Admin;

use App\Models\Car;
use Illuminate\Http\Request;
use App\Models\CarMaintenance;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Response;
use Yajra\DataTables\Facades\DataTables;

class CarMaintenanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Car $car)
    {
        return view('admin.car-maintenance.index', compact('car'));
    }
    
    /**
     * Process DataTables AJAX request.
     */
    public function datatable(Request $request, Car $car)
    {
        $maintenance = $car->maintenance()->latest();
        
        return DataTables::of($maintenance)
            ->addColumn('maintenance_title', function ($maintenance) {
                return ucfirst(str_replace('_', ' ', $maintenance->maintenance_type));
            })
            ->addColumn('next_due', function ($maintenance) {
                $html = '';
                
                if ($maintenance->next_due_date) {
                    $badgeClass = $maintenance->next_due_date < now() ? 'danger' : 'info';
                    $html .= '<span class="badge bg-' . $badgeClass . '">' . $maintenance->next_due_date->format('d/m/Y') . '</span>';
                }
                
                if ($maintenance->next_due_mileage) {
                    $car = $maintenance->car;
                    $kmLeft = $maintenance->next_due_mileage - $car->mileage;
                    $badgeClass = $kmLeft <= 0 ? 'danger' : ($kmLeft <= 500 ? 'warning' : 'primary');
                    $html .= ' <span class="badge bg-' . $badgeClass . '">' . number_format($maintenance->next_due_mileage) . ' km</span>';
                    $html .= ' <small class="text-muted">(' . ($kmLeft <= 0 ? 'Overdue' : number_format($kmLeft) . ' km left') . ')</small>';
                }
                
                return $html ?: '<span class="text-muted">Not specified</span>';
            })
            ->addColumn('status', function ($maintenance) {
                if ($maintenance->isDueSoon()) {
                    return '<span class="badge bg-warning">Due Soon</span>';
                } elseif ($maintenance->next_due_date && $maintenance->next_due_date < now()) {
                    return '<span class="badge bg-danger">Overdue</span>';
                } elseif ($maintenance->next_due_mileage && $maintenance->car && $maintenance->next_due_mileage <= $maintenance->car->mileage) {
                    return '<span class="badge bg-danger">Overdue</span>';
                } else {
                    return '<span class="badge bg-success">OK</span>';
                }
            })
            ->addColumn('actions', function ($maintenance) {
                $buttons = '<div class="btn-group" role="group">';
                
                // Edit button
                $buttons .= '<button class="btn btn-sm btn-primary edit-maintenance" data-id="' . $maintenance->id . '" title="Edit"><i class="fas fa-edit"></i></button>';
                
                // Delete button
                $buttons .= '<button class="btn btn-sm btn-danger delete-maintenance" data-id="' . $maintenance->id . '" title="Delete"><i class="fas fa-trash"></i></button>';
                
                $buttons .= '</div>';
                return $buttons;
            })
            ->rawColumns(['next_due', 'status', 'actions'])
            ->make(true);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Car $car)
    {
        $request->validate([
            'maintenance_type' => 'required|string|max:255',
            'date_performed' => 'required|date',
            'next_due_date' => 'nullable|date|after:date_performed',
            'next_due_mileage' => 'nullable|integer|min:' . $car->mileage,
            'cost' => 'nullable|numeric|min:0',
            'performed_by' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'mileage_at_service' => 'required|integer|min:0',
            'oil_type' => 'nullable|string|max:255',
            'oil_quantity' => 'nullable|string|max:50',
            'parts_replaced' => 'nullable|string',
        ]);

        try {
            // Create the maintenance record
            $car->maintenance()->create($request->all());
            
            // Update car mileage if maintenance mileage is higher
            if ($request->mileage_at_service > $car->mileage) {
                $car->update(['mileage' => $request->mileage_at_service]);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Maintenance record added successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while adding the maintenance record: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CarMaintenance $maintenance)
    {
        $car = $maintenance->car;
        
        $request->validate([
            'maintenance_type' => 'required|string|max:255',
            'date_performed' => 'required|date',
            'next_due_date' => 'nullable|date|after:date_performed',
            'next_due_mileage' => 'nullable|integer|min:' . $car->mileage,
            'cost' => 'nullable|numeric|min:0',
            'performed_by' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'mileage_at_service' => 'required|integer|min:0',
            'oil_type' => 'nullable|string|max:255',
            'oil_quantity' => 'nullable|string|max:50',
            'parts_replaced' => 'nullable|string',
        ]);

        try {
            // Update the maintenance record
            $maintenance->update($request->all());
            
            // Update car mileage if maintenance mileage is higher
            if ($request->mileage_at_service > $car->mileage) {
                $car->update(['mileage' => $request->mileage_at_service]);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Maintenance record updated successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the maintenance record: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CarMaintenance $maintenance)
    {
        try {
            $maintenance->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Maintenance record deleted successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting the maintenance record: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Show cars with maintenance due soon.
     */
    public function maintenanceDueSoon()
    {
        return view('admin.car-maintenance.due-soon');
    }
    
    /**
     * Process DataTables AJAX request for cars with maintenance due soon.
     */
    public function maintenanceDueSoonDatatable(Request $request)
    {
        $fifteenDaysFromNow = now()->addDays(15);
        
        $maintenances = CarMaintenance::with('car')
            ->where(function($query) use ($fifteenDaysFromNow) {
                $query->whereDate('next_due_date', '<=', $fifteenDaysFromNow)
                    ->orWhereRaw('next_due_mileage - (SELECT mileage FROM cars WHERE cars.id = car_maintenances.car_id) <= 500');
            })
            ->get();
        
        return DataTables::of($maintenances)
            ->addColumn('car_details', function ($maintenance) {
                return $maintenance->car->brand_name . ' ' . $maintenance->car->model . ' (' . $maintenance->car->matricule . ')';
            })
            ->addColumn('maintenance_type', function ($maintenance) {
                return ucfirst(str_replace('_', ' ', $maintenance->maintenance_type));
            })
            ->addColumn('due_details', function ($maintenance) {
                $html = '';
                $car = $maintenance->car;
                
                if ($maintenance->next_due_date) {
                    $daysLeft = now()->diffInDays($maintenance->next_due_date, false);
                    $badgeClass = $daysLeft < 0 ? 'danger' : ($daysLeft < 7 ? 'warning' : 'info');
                    $html .= '<div><span class="badge bg-' . $badgeClass . '">Date: ' . $maintenance->next_due_date->format('d/m/Y') . '</span> ';
                    $html .= $daysLeft < 0 ? '(Overdue ' . abs($daysLeft) . ' days)' : '(' . $daysLeft . ' days left)</div>';
                }
                
                if ($maintenance->next_due_mileage) {
                    $kmLeft = $maintenance->next_due_mileage - $car->mileage;
                    $badgeClass = $kmLeft <= 0 ? 'danger' : ($kmLeft <= 200 ? 'warning' : 'primary');
                    $html .= '<div><span class="badge bg-' . $badgeClass . '">Mileage: ' . number_format($maintenance->next_due_mileage) . ' km</span> ';
                    $html .= $kmLeft <= 0 ? '(Overdue ' . abs($kmLeft) . ' km)' : '(' . number_format($kmLeft) . ' km left)</div>';
                }
                
                return $html;
            })
            ->addColumn('last_performed', function ($maintenance) {
                return $maintenance->date_performed->format('d/m/Y') . ' at ' . number_format($maintenance->mileage_at_service) . ' km';
            })
            ->addColumn('actions', function ($maintenance) {
                return '<a href="' . route('admin.cars.maintenance.index', $maintenance->car_id) . '" class="btn btn-sm btn-primary" title="Manage Maintenance"><i class="fas fa-tools"></i> Manage</a>';
            })
            ->rawColumns(['due_details', 'actions'])
            ->make(true);
    }

/**
 * Export maintenance records for a car as CSV
 *
 * @param Request $request
 * @param Car $car
 * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
 */
public function exportCsv(Request $request, Car $car)
{
    // Load car maintenance records
    $car->load('maintenance');
    
    // Prepare CSV data
    $headers = [
        'ID', 'Type', 'Date Performed', 'Mileage', 'Next Due Date', 
        'Next Due Mileage', 'Cost', 'Performed By', 'Notes'
    ];
    
    // Create CSV content
    $callback = function() use ($car, $headers) {
        $file = fopen('php://output', 'w');
        
        // Add headers
        fputcsv($file, $headers);
        
        // Add data rows
        foreach ($car->maintenance as $record) {
            $row = [
                $record->id,
                ucfirst(str_replace('_', ' ', $record->maintenance_type)),
                $record->date_performed->format('d/m/Y'),
                number_format($record->mileage_at_service) . ' km',
                $record->next_due_date ? $record->next_due_date->format('d/m/Y') : 'N/A',
                $record->next_due_mileage ? number_format($record->next_due_mileage) . ' km' : 'N/A',
                $record->cost ? number_format($record->cost, 2) . ' MAD' : 'N/A',
                $record->performed_by ?: 'N/A',
                $record->notes ?: ''
            ];
            
            fputcsv($file, $row);
        }
        
        fclose($file);
    };
    
    // Set filename
    $filename = 'maintenance_history_' . $car->matricule . '_' . date('Y-m-d') . '.csv';
    
    // Return streaming response
    return Response::stream($callback, 200, [
        'Content-Type' => 'text/csv',
        'Content-Disposition' => 'attachment; filename="' . $filename . '"',
    ]);
}

/**
 * Show printable view of maintenance records
 *
 * @param Car $car
 * @return \Illuminate\View\View
 */
public function printMaintenanceHistory(Car $car)
{
    // Load maintenance records
    $car->load('maintenance');
    
    // Prepare data for view
    $maintenance = $car->maintenance->sortByDesc('date_performed')->map(function ($record) {
        return [
            'ID' => $record->id,
            'Type' => ucfirst(str_replace('_', ' ', $record->maintenance_type)),
            'Date Performed' => $record->date_performed->format('d/m/Y'),
            'Mileage' => number_format($record->mileage_at_service) . ' km',
            'Next Due Date' => $record->next_due_date ? $record->next_due_date->format('d/m/Y') : 'N/A',
            'Next Due Mileage' => $record->next_due_mileage ? number_format($record->next_due_mileage) . ' km' : 'N/A',
            'Cost' => $record->cost ? number_format($record->cost, 2) . ' MAD' : 'N/A',
            'Performed By' => $record->performed_by ?: 'N/A',
            'Notes' => $record->notes ?: ''
        ];
    });
    
    // Return view
    return view('admin.car-maintenance.print', compact('car', 'maintenance'));
}

/**
 * Export due maintenance records as CSV
 *
 * @param Request $request
 * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
 */
public function exportDueMaintenanceCsv(Request $request)
{
    // Get all maintenance records due soon
    $maintenances = CarMaintenance::with('car')
        ->where(function($query) {
            $query->whereDate('next_due_date', '<=', now()->addDays(15))
                ->orWhereRaw('next_due_mileage - (SELECT mileage FROM cars WHERE cars.id = car_maintenances.car_id) <= 500');
        })
        ->get();
    
    // Prepare CSV headers
    $headers = [
        'Car', 'Maintenance Type', 'Last Performed', 'Current Mileage',
        'Next Due Date', 'Days Left', 'Next Due Mileage', 'KM Left', 'Status'
    ];
    
    // Create CSV content
    $callback = function() use ($maintenances, $headers) {
        $file = fopen('php://output', 'w');
        
        // Add headers
        fputcsv($file, $headers);
        
        // Add data rows
        foreach ($maintenances as $maintenance) {
            // Calculate days and kilometers left
            $daysLeft = $maintenance->next_due_date ? now()->diffInDays($maintenance->next_due_date, false) : null;
            $kmLeft = $maintenance->next_due_mileage && $maintenance->car ? $maintenance->next_due_mileage - $maintenance->car->mileage : null;
            
            $row = [
                $maintenance->car ? $maintenance->car->brand_name . ' ' . $maintenance->car->model . ' (' . $maintenance->car->matricule . ')' : 'N/A',
                ucfirst(str_replace('_', ' ', $maintenance->maintenance_type)),
                $maintenance->date_performed->format('d/m/Y'),
                $maintenance->car ? number_format($maintenance->car->mileage) . ' km' : 'N/A',
                $maintenance->next_due_date ? $maintenance->next_due_date->format('d/m/Y') : 'N/A',
                $daysLeft !== null ? ($daysLeft < 0 ? 'Overdue by ' . abs($daysLeft) . ' days' : $daysLeft . ' days left') : 'N/A',
                $maintenance->next_due_mileage ? number_format($maintenance->next_due_mileage) . ' km' : 'N/A',
                $kmLeft !== null ? ($kmLeft < 0 ? 'Overdue by ' . abs($kmLeft) . ' km' : $kmLeft . ' km left') : 'N/A',
                $maintenance->isOverdue() ? 'Overdue' : ($maintenance->isDueSoon() ? 'Due Soon' : 'OK')
            ];
            
            fputcsv($file, $row);
        }
        
        fclose($file);
    };
    
    // Set filename
    $filename = 'maintenance_due_soon_' . date('Y-m-d') . '.csv';
    
    // Return streaming response
    return Response::stream($callback, 200, [
        'Content-Type' => 'text/csv',
        'Content-Disposition' => 'attachment; filename="' . $filename . '"',
    ]);
}

/**
 * Show printable view of due maintenance records
 *
 * @return \Illuminate\View\View
 */
public function printDueMaintenance()
{
    // Get all maintenance records due soon
    $maintenances = CarMaintenance::with('car')
        ->where(function($query) {
            $query->whereDate('next_due_date', '<=', now()->addDays(15))
                ->orWhereRaw('next_due_mileage - (SELECT mileage FROM cars WHERE cars.id = car_maintenances.car_id) <= 500');
        })
        ->get();
    
    // Prepare data for the view
    $records = collect();
    
    foreach ($maintenances as $maintenance) {
        // Calculate days and kilometers left
        $daysLeft = $maintenance->next_due_date ? now()->diffInDays($maintenance->next_due_date, false) : null;
        $kmLeft = $maintenance->next_due_mileage && $maintenance->car ? $maintenance->next_due_mileage - $maintenance->car->mileage : null;
        
        $records->push([
            'car' => $maintenance->car,
            'maintenance' => $maintenance,
            'days_left' => $daysLeft,
            'km_left' => $kmLeft
        ]);
    }
    
    return view('admin.car-maintenance.due-soon-print', compact('records'));
}
}