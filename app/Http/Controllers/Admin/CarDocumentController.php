<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Car;
use App\Models\CarDocuments;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use Spatie\Activitylog\Models\Activity;

class CarDocumentController extends Controller
{
    /**
     * Display the documents for a specific car
     */
    public function show(Car $car)
{
    $documents = $car->documents()->firstOrNew([]);
    
    return view('admin.car-documents.show', compact('car', 'documents'));
}
    
    /** 
     * Get car documents via AJAX
     */
    public function getDocuments(Car $car)
    {
        $documents = CarDocuments::where('car_id', $car->id)->first();
        
        return response()->json([
            'success' => true,
            'documents' => $documents
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Car $car)
    {
        // Check permission
        if (!Auth::guard('admin')->user()->can('edit cars')) {
            return redirect()->back()
                ->with('error', 'You do not have permission to add car documents.');
        }
        
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
            $documents = $car->documents()->create($documentData);
            
            // Log activity
            $this->logActivity(
                $car,
                'car_documents_created',
                'Car Documents Created',
                'Created documents for car: ' . $car->name
            );
            
            // Check for expiring documents
            if ($documents->hasExpiringDocuments()) {
                $expiringDocs = $documents->getExpiringDocuments();
                $this->logActivity(
                    $car,
                    'document_expiry_warning',
                    'Document Expiration Warning',
                    'Car ' . $car->name . ' has documents that will expire soon',
                    ['expiring_documents' => $expiringDocs]
                );
            }
            
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
        // Check permission
        if (!Auth::guard('admin')->user()->can('edit cars')) {
            return redirect()->back()
                ->with('error', 'You do not have permission to update car documents.');
        }
        
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
            
            // Store original values for activity log
            $originalValues = $documents->toArray();
            
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
            
            // Log activity
            $this->logActivity(
                $car,
                'car_documents_updated',
                'Car Documents Updated',
                'Updated documents for car: ' . $car->name,
                ['changes' => $this->getChanges($originalValues, $documents->toArray())]
            );
            
            // Check for expiring documents
            if ($documents->hasExpiringDocuments()) {
                $expiringDocs = $documents->getExpiringDocuments();
                $this->logActivity(
                    $car,
                    'document_expiry_warning',
                    'Document Expiration Warning',
                    'Car ' . $car->name . ' has documents that will expire soon',
                    ['expiring_documents' => $expiringDocs]
                );
            }
            
            return redirect()->route('admin.cars.documents.show', $car->id)
                ->with('success', 'Documents updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'An error occurred while updating documents: ' . $e->getMessage())
                ->withInput();
        }
    }
    
    /**
     * AJAX update method for documents.
     */
    public function ajaxUpdate(Request $request, Car $car)
    {
        // Check permission
        if (!Auth::guard('admin')->user()->can('edit cars')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to update car documents.'
            ], 403);
        }
        
        $validator = Validator::make($request->all(), [
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
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        try {
            // Find or create documents record
            $documents = CarDocuments::firstOrNew(['car_id' => $car->id]);
            $isNew = !$documents->exists;
            
            // Store original values for activity log
            $originalValues = $documents->exists ? $documents->toArray() : [];
            
            // Set document data
            $documents->car_id = $car->id;
            $documents->carte_grise_number = $request->carte_grise_number;
            $documents->carte_grise_expiry_date = $request->carte_grise_expiry_date;
            $documents->assurance_number = $request->assurance_number;
            $documents->assurance_company = $request->assurance_company;
            $documents->assurance_expiry_date = $request->assurance_expiry_date;
            $documents->visite_technique_date = $request->visite_technique_date ?? now();
            $documents->visite_technique_expiry_date = $request->visite_technique_expiry_date;
            $documents->vignette_expiry_date = $request->vignette_expiry_date;
            
            // Handle file uploads
            if ($request->hasFile('file_carte_grise')) {
                if ($documents->file_carte_grise && Storage::exists('public/' . $documents->file_carte_grise)) {
                    Storage::delete('public/' . $documents->file_carte_grise);
                }
                $documents->file_carte_grise = $this->uploadFile($request->file('file_carte_grise'), $car->id, 'carte_grise');
            }
            
            if ($request->hasFile('file_assurance')) {
                if ($documents->file_assurance && Storage::exists('public/' . $documents->file_assurance)) {
                    Storage::delete('public/' . $documents->file_assurance);
                }
                $documents->file_assurance = $this->uploadFile($request->file('file_assurance'), $car->id, 'assurance');
            }
            
            if ($request->hasFile('file_visite_technique')) {
                if ($documents->file_visite_technique && Storage::exists('public/' . $documents->file_visite_technique)) {
                    Storage::delete('public/' . $documents->file_visite_technique);
                }
                $documents->file_visite_technique = $this->uploadFile($request->file('file_visite_technique'), $car->id, 'visite_technique');
            }
            
            if ($request->hasFile('file_vignette')) {
                if ($documents->file_vignette && Storage::exists('public/' . $documents->file_vignette)) {
                    Storage::delete('public/' . $documents->file_vignette);
                }
                $documents->file_vignette = $this->uploadFile($request->file('file_vignette'), $car->id, 'vignette');
            }
            
            // Save the documents
            $documents->save();
            
            // Log activity
            if ($isNew) {
                $this->logActivity(
                    $car,
                    'car_documents_created',
                    'Car Documents Created',
                    'Created documents for car: ' . $car->name
                );
            } else {
                $this->logActivity(
                    $car,
                    'car_documents_updated',
                    'Car Documents Updated',
                    'Updated documents for car: ' . $car->name,
                    ['changes' => $this->getChanges($originalValues, $documents->toArray())]
                );
            }
            
            // Check for expiring documents
            if ($documents->hasExpiringDocuments()) {
                $expiringDocs = $documents->getExpiringDocuments();
                $this->logActivity(
                    $car,
                    'document_expiry_warning',
                    'Document Expiration Warning',
                    'Car ' . $car->name . ' has documents that will expire soon',
                    ['expiring_documents' => $expiringDocs]
                );
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Documents ' . ($isNew ? 'created' : 'updated') . ' successfully.',
                'documents' => $documents
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while ' . ($isNew ? 'creating' : 'updating') . ' documents: ' . $e->getMessage()
            ], 500);
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
        // Check permission
        if (!Auth::guard('admin')->user()->can('edit cars')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to upload car documents.'
            ], 403);
        }
        
        $request->validate([
            'document_type' => 'required|in:carte_grise,assurance,visite_technique,vignette',
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);
        
        try {
            $documentType = $request->document_type;
            $documents = $car->documents;
            
            if (!$documents) {
                // Create a new document record if it doesn't exist
                $documents = new CarDocuments(['car_id' => $car->id]);
                $documents->save();
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
            
            // Log activity
            $this->logActivity(
                $car,
                'document_file_uploaded',
                'Document File Uploaded',
                'Uploaded ' . $this->getDocumentTypeName($documentType) . ' document for car: ' . $car->name
            );
            
            return response()->json([
                'success' => true,
                'message' => $this->getDocumentTypeName($documentType) . ' document uploaded successfully.',
                'file_path' => Storage::url($filePath)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while uploading the document: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Delete a document file.
     */
    public function deleteDocument(Request $request, Car $car)
    {
        // Check permission
        if (!Auth::guard('admin')->user()->can('edit cars')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to delete car documents.'
            ], 403);
        }
        
        $request->validate([
            'document_type' => 'required|in:carte_grise,assurance,visite_technique,vignette',
        ]);
        
        try {
            $documentType = $request->document_type;
            $documents = $car->documents;
            
            if (!$documents) {
                return response()->json([
                    'success' => false,
                    'message' => 'No documents found for this car.'
                ], 404);
            }
            
            // Delete file if exists
            $fileField = 'file_' . $documentType;
            if ($documents->$fileField && Storage::exists('public/' . $documents->$fileField)) {
                Storage::delete('public/' . $documents->$fileField);
                
                // Update the document record
                $documents->update([$fileField => null]);
                
                // Log activity
                $this->logActivity(
                    $car,
                    'document_file_deleted',
                    'Document File Deleted',
                    'Deleted ' . $this->getDocumentTypeName($documentType) . ' document for car: ' . $car->name
                );
                
                return response()->json([
                    'success' => true,
                    'message' => $this->getDocumentTypeName($documentType) . ' document deleted successfully.'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'No ' . $this->getDocumentTypeName($documentType) . ' document found for this car.'
                ], 404);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting the document: ' . $e->getMessage()
            ], 500);
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
                $car = $carDocument->car;
                return $car ? $car->brand_name . ' ' . $car->model . ' (' . $car->matricule . ')' : 'Unknown Car';
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
    
    /**
     * Log activity for car documents.
     */
    private function logActivity($car, $type, $title, $description, array $properties = [])
    {
        $activity = new Activity();
        $activity->log_name = 'car_documents';
        $activity->description = $description;
        $activity->subject_type = get_class($car);
        $activity->subject_id = $car->id;
        $activity->causer_type = get_class(Auth::guard('admin')->user());
        $activity->causer_id = Auth::guard('admin')->user()->id;
        $activity->properties = $properties;
        $activity->save();
        
        return $activity;
    }
    
    /**
     * Get changes between original and updated data.
     */
    private function getChanges($before, $after)
    {
        $changes = [];
        
        // Compare values and find changes
        foreach ($after as $key => $value) {
            // Skip the following fields
            if (in_array($key, ['id', 'created_at', 'updated_at'])) {
                continue;
            }
            
            // If old value existed
            if (isset($before[$key])) {
                // Check if value changed
                if ($before[$key] != $value) {
                    // For dates, we need to compare the format
                    if ($this->isDateField($key)) {
                        $beforeDate = $before[$key] ? Carbon::parse($before[$key])->format('Y-m-d') : null;
                        $afterDate = $value ? Carbon::parse($value)->format('Y-m-d') : null;
                        
                        if ($beforeDate != $afterDate) {
                            $changes[$key] = [
                                'before' => $beforeDate,
                                'after' => $afterDate
                            ];
                        }
                    } else {
                        $changes[$key] = [
                            'before' => $before[$key],
                            'after' => $value
                        ];
                    }
                }
            } else {
                // New value was added
                $changes[$key] = [
                    'before' => null,
                    'after' => $value
                ];
            }
        }
        
        return $changes;
    }
    
    /**
     * Check if field is a date field.
     */
    private function isDateField($fieldName)
    {
        return in_array($fieldName, [
            'carte_grise_expiry_date',
            'assurance_expiry_date',
            'visite_technique_date',
            'visite_technique_expiry_date',
            'vignette_expiry_date'
        ]);
    }
    
    /**
     * Get friendly name for document type.
     */
    private function getDocumentTypeName($documentType)
    {
        $names = [
            'carte_grise' => 'Carte Grise',
            'assurance' => 'Insurance',
            'visite_technique' => 'Technical Inspection',
            'vignette' => 'Vignette'
        ];
        
        return $names[$documentType] ?? ucfirst(str_replace('_', ' ', $documentType));
    }
}