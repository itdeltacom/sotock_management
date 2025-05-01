<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Car;
use App\Models\CarDocuments;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class CarDocumentController extends Controller
{
    /**
     * Display the specified resource.
     */
    public function show(Car $car)
    {
        $documents = $car->documents;
        return view('admin.car-documents.show', compact('car', 'documents'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Car $car)
    {
        $request->validate([
            'carte_grise_number' => 'required|string|max:255',
            'carte_grise_expiry_date' => 'nullable|date',
            'assurance_number' => 'required|string|max:255',
            'assurance_company' => 'required|string|max:255',
            'assurance_expiry_date' => 'required|date',
            'visite_technique_date' => 'nullable|date',
            'visite_technique_expiry_date' => 'required|date',
            'vignette_expiry_date' => 'nullable|date',
            'file_carte_grise' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'file_assurance' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'file_visite_technique' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'file_vignette' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        try {
            // Check if documents already exist
            if ($car->documents()->exists()) {
                return redirect()->back()
                    ->with('error', 'Documents already exist for this car. Please update them instead.');
            }
            
            // Handle file uploads
            $documentData = $request->except(['file_carte_grise', 'file_assurance', 'file_visite_technique', 'file_vignette']);
            
            if ($request->hasFile('file_carte_grise')) {
                $documentData['file_carte_grise'] = $this->uploadFile($request->file('file_carte_grise'), $car->id, 'carte_grise');
            }
            
            if ($request->hasFile('file_assurance')) {
                $documentData['file_assurance'] = $this->uploadFile($request->file('file_assurance'), $car->id, 'assurance');
            }
            
            if ($request->hasFile('file_visite_technique')) {
                $documentData['file_visite_technique'] = $this->uploadFile($request->file('file_visite_technique'), $car->id, 'visite_technique');
            }
            
            if ($request->hasFile('file_vignette')) {
                $documentData['file_vignette'] = $this->uploadFile($request->file('file_vignette'), $car->id, 'vignette');
            }
            
            // Create the documents
            $car->documents()->create($documentData);
            
            return redirect()->route('admin.cars.documents.show', $car->id)
                ->with('success', 'Documents added successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'An error occurred while adding documents: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Car $car)
    {
        $request->validate([
            'carte_grise_number' => 'required|string|max:255',
            'carte_grise_expiry_date' => 'nullable|date',
            'assurance_number' => 'required|string|max:255',
            'assurance_company' => 'required|string|max:255',
            'assurance_expiry_date' => 'required|date',
            'visite_technique_date' => 'nullable|date',
            'visite_technique_expiry_date' => 'required|date',
            'vignette_expiry_date' => 'nullable|date',
            'file_carte_grise' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'file_assurance' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'file_visite_technique' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'file_vignette' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        try {
            // Get the documents
            $documents = $car->documents;
            if (!$documents) {
                return redirect()->back()
                    ->with('error', 'No documents found for this car. Please add them first.');
            }
            
            // Handle file uploads
            $documentData = $request->except(['file_carte_grise', 'file_assurance', 'file_visite_technique', 'file_vignette']);
            
            if ($request->hasFile('file_carte_grise')) {
                // Delete old file if exists
                if ($documents->file_carte_grise && Storage::exists('public/' . $documents->file_carte_grise)) {
                    Storage::delete('public/' . $documents->file_carte_grise);
                }
                $documentData['file_carte_grise'] = $this->uploadFile($request->file('file_carte_grise'), $car->id, 'carte_grise');
            }
            
            if ($request->hasFile('file_assurance')) {
                // Delete old file if exists
                if ($documents->file_assurance && Storage::exists('public/' . $documents->file_assurance)) {
                    Storage::delete('public/' . $documents->file_assurance);
                }
                $documentData['file_assurance'] = $this->uploadFile($request->file('file_assurance'), $car->id, 'assurance');
            }
            
            if ($request->hasFile('file_visite_technique')) {
                // Delete old file if exists
                if ($documents->file_visite_technique && Storage::exists('public/' . $documents->file_visite_technique)) {
                    Storage::delete('public/' . $documents->file_visite_technique);
                }
                $documentData['file_visite_technique'] = $this->uploadFile($request->file('file_visite_technique'), $car->id, 'visite_technique');
            }
            
            if ($request->hasFile('file_vignette')) {
                // Delete old file if exists
                if ($documents->file_vignette && Storage::exists('public/' . $documents->file_vignette)) {
                    Storage::delete('public/' . $documents->file_vignette);
                }
                $documentData['file_vignette'] = $this->uploadFile($request->file('file_vignette'), $car->id, 'vignette');
            }
            
            // Update the documents
            $documents->update($documentData);
            
            return redirect()->route('admin.cars.documents.show', $car->id)
                ->with('success', 'Documents updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'An error occurred while updating documents: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Upload a document file.
     */
    private function uploadFile($file, $carId, $documentType)
    {
        // Generate a unique filename
        $filename = 'doc_' . $carId . '_' . $documentType . '_' . time() . '.' . $file->getClientOriginalExtension();
        
        // Create directory if it doesn't exist
        $path = 'car_documents/' . $carId;
        if (!Storage::exists('public/' . $path)) {
            Storage::makeDirectory('public/' . $path);
        }
        
        // Store the file
        $file->storeAs('public/' . $path, $filename);
        
        return $path . '/' . $filename;
    }
    
    /**
     * Upload a document file via AJAX.
     */
    public function uploadDocument(Request $request, Car $car)
    {
        $request->validate([
            'document_type' => 'required|in:carte_grise,assurance,visite_technique,vignette',
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);
        
        try {
            $documentType = $request->document_type;
            $documents = $car->documents;
            
            if (!$documents) {
                return response()->json([
                    'success' => false,
                    'message' => 'No documents found for this car. Please add them first.'
                ]);
            }
            
            // Delete old file if exists
            $fileField = 'file_' . $documentType;
            if ($documents->$fileField && Storage::exists('public/' . $documents->$fileField)) {
                Storage::delete('public/' . $documents->$fileField);
            }
            
            // Upload new file
            $filePath = $this->uploadFile($request->file('file'), $car->id, $documentType);
            
            // Update the document
            $documents->update([$fileField => $filePath]);
            
            return response()->json([
                'success' => true,
                'message' => ucfirst(str_replace('_', ' ', $documentType)) . ' document uploaded successfully.',
                'file_path' => Storage::url($filePath)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while uploading the document: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Show cars with expiring documents.
     */
    public function expiringDocuments()
    {
        return view('admin.car-documents.expiring');
    }
    
    /**
     * Process DataTables AJAX request for cars with expiring documents.
     */
    public function expiringDocumentsDatatable(Request $request)
    {
        $thirtyDaysFromNow = now()->addDays(30);
        
        $carDocuments = CarDocuments::with('car')
            ->where(function ($query) use ($thirtyDaysFromNow) {
                $query->whereDate('carte_grise_expiry_date', '<=', $thirtyDaysFromNow)
                    ->orWhereDate('assurance_expiry_date', '<=', $thirtyDaysFromNow)
                    ->orWhereDate('visite_technique_expiry_date', '<=', $thirtyDaysFromNow)
                    ->orWhereDate('vignette_expiry_date', '<=', $thirtyDaysFromNow);
            });
        
        return DataTables::of($carDocuments)
            ->addColumn('car_details', function ($carDocument) {
                return $carDocument->car->brand_name . ' ' . $carDocument->car->model . ' (' . $carDocument->car->matricule . ')';
            })
            ->addColumn('expiring_documents', function ($carDocument) {
                $expiringDocs = $carDocument->getExpiringDocuments();
                $html = '<ul class="list-unstyled">';
                foreach ($expiringDocs as $doc) {
                    $badgeClass = $doc['days_left'] < 0 ? 'danger' : ($doc['days_left'] < 7 ? 'warning' : 'info');
                    $html .= '<li><span class="badge bg-' . $badgeClass . '">' . $doc['document'] . '</span> - ' . 
                             $doc['expiry_date']->format('d/m/Y') . ' (' . 
                             ($doc['days_left'] < 0 ? 'Expired ' . abs($doc['days_left']) . ' days ago' : $doc['days_left'] . ' days left') . 
                             ')</li>';
                }
                $html .= '</ul>';
                return $html;
            })
            ->addColumn('actions', function ($carDocument) {
                return '<a href="' . route('admin.cars.documents.show', $carDocument->car_id) . '" class="btn btn-sm btn-primary" title="Manage Documents"><i class="fas fa-edit"></i> Manage</a>';
            })
            ->rawColumns(['expiring_documents', 'actions'])
            ->make(true);
    }
}