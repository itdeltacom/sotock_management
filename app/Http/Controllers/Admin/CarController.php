<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Car;
use App\Models\Brand;
use App\Models\CarImage;
use App\Models\Category;
use Illuminate\Support\Str;
use App\Models\CarDocuments;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;
use Spatie\Activitylog\Models\Activity;

class CarController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
{
    $categories = Category::where('is_active', true)->orderBy('name')->get();
    $brands = Brand::where('is_active', true)->orderBy('name')->get();
    
    // Calculate statistics
    $totalCars = Car::count();
    $availableCars = Car::where('status', 'available')->count();
    $rentedCars = Car::where('status', 'rented')->count();
    $maintenanceCars = Car::where('status', 'maintenance')->count();
    
    // Calculate changes from last month/week
    $lastMonth = now()->subMonth();
    $lastWeek = now()->subWeek();
    
    $totalCarsLastMonth = Car::where('created_at', '<', $lastMonth)->count();
    $availableCarsLastWeek = Car::where('status', 'available')
        ->where('updated_at', '<', $lastWeek)
        ->count();
    $rentedCarsYesterday = Car::where('status', 'rented')
        ->where('updated_at', '<', now()->subDay())
        ->count();
    
    // Calculate percentage changes
    $totalCarsChange = $totalCarsLastMonth > 0 
        ? round((($totalCars - $totalCarsLastMonth) / $totalCarsLastMonth) * 100, 1)
        : 0;
    
    $availableCarsChange = $availableCarsLastWeek > 0
        ? round((($availableCars - $availableCarsLastWeek) / $availableCarsLastWeek) * 100, 1)
        : 0;
    
    $rentedCarsChange = $rentedCarsYesterday > 0
        ? round((($rentedCars - $rentedCarsYesterday) / $rentedCarsYesterday) * 100, 1)
        : 0;
    
    // Get documents needing attention (expiring in 30 days or expired)
    $documentsNeedingAttention = $this->getExpiringDocuments()->count();
    
    $statistics = [
        'total_cars' => $totalCars,
        'total_cars_change' => $totalCarsChange,
        'available_cars' => $availableCars,
        'available_cars_change' => $availableCarsChange,
        'rented_cars' => $rentedCars,
        'rented_cars_change' => $rentedCarsChange,
        'maintenance_cars' => $maintenanceCars,
        'maintenance_cars_needing_attention' => $documentsNeedingAttention,
    ];
    
    return view('admin.cars.cars', compact('categories', 'brands', 'statistics'));
}
    

    /**
     * Process DataTables AJAX request.
     */
    public function datatable(Request $request)
    {
        $cars = Car::with(['category', 'brand', 'images'])
            ->select('cars.*') 
            ->latest();
        
            return DataTables::of($cars)
            ->addColumn('image', function (Car $car) {
                if ($car->main_image) {
                    return '<img src="' . Storage::url($car->main_image) . '" alt="' . $car->name . '" class="img-thumbnail" width="80">';
                }
                return '<div class="bg-light text-center p-2" style="width:80px;height:60px;"><i class="fas fa-car fa-2x text-muted"></i></div>';
            })
            ->addColumn('brand_model', function (Car $car) {
                $brandName = optional($car->brand)->name ?? $car->brand_name ?? 'Unknown Brand';
                return "<strong>{$brandName}</strong> {$car->name} ({$car->year})";
            })
            ->addColumn('status', function (Car $car) {
                $statusClass = [
                    'available' => 'success',
                    'rented' => 'primary',
                    'maintenance' => 'warning',
                    'unavailable' => 'danger'
                ][$car->status] ?? 'secondary';
        
                return '<span class="badge bg-' . $statusClass . '">' . ucfirst($car->status) . '</span>';
            })
            ->addColumn('price', function (Car $car) {
                $output = number_format($car->price_per_day, 2) . ' MAD/day';
        
                if ($car->discount_percentage > 0) {
                    $discountedPrice = $car->price_per_day * (1 - ($car->discount_percentage / 100));
                    $output .= '<br><small class="text-success">' . number_format($discountedPrice, 2) . ' MAD after ' . $car->discount_percentage . '% off</small>';
                }
        
                if ($car->weekly_price) {
                    $output .= '<br><small class="text-muted">Weekly: ' . number_format($car->weekly_price, 2) . ' MAD</small>';
                }
        
                if ($car->monthly_price) {
                    $output .= '<br><small class="text-muted">Monthly: ' . number_format($car->monthly_price, 2) . ' MAD</small>';
                }
        
                return $output;
            })
            ->addColumn('actions', function (Car $car) {
                $buttons = '';
                $buttons .= '<a href="'.route('admin.cars.show', $car->id).'" class="btn btn-sm btn-info me-1" title="View">
                                <i class="fas fa-eye"></i>
                            </a>';
        
                if (auth()->guard('admin')->user()->can('edit cars')) {
                    $buttons .= '<button class="btn btn-sm btn-primary me-1 btn-edit" data-id="'.$car->id.'" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>';
                }
        
                if (auth()->guard('admin')->user()->can('delete cars')) {
                    $buttons .= '<button class="btn btn-sm btn-danger btn-delete" data-id="'.$car->id.'" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>';
                }
        
                return '<div class="btn-group">'.$buttons.'</div>';
            })
            ->rawColumns(['image', 'status', 'price', 'actions', 'brand_model']) // <-- include brand_model here
            ->make(true);
    }

    
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.cars.create');
    }
    private function logActivity(Car $car, $type, $title, $description, array $properties = [])
    {
        return \App\Models\Activity::log(
            $type,
            $title,
            $description,
            Auth::guard('admin')->user(),
            $car,
            $properties
        );
    }
   /**
 * Store a newly created resource in storage.
 */
public function store(Request $request)
{
    // Check permission
    if (!Auth::guard('admin')->user()->can('create cars')) {
        return response()->json([
            'success' => false,
            'message' => 'You do not have permission to create cars.'
        ], 403);
    }
    
    // Validate input 
    $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:255',
        'category_id' => 'required|exists:categories,id',
        'brand_id' => 'required|exists:brands,id',
        'model' => 'required|string|max:255',
        'year' => 'required|digits:4|integer|min:1900|max:'.(date('Y')+1),
        'chassis_number' => 'required|string|unique:cars,chassis_number',
        'matricule' => [
            'required',
            'string',
            'unique:cars,matricule',
            function ($attribute, $value, $fail) {
                // Validation for Moroccan license plate format
                $normalized = preg_replace('/[-|]/', '', $value);
                if (!preg_match('/^\d{1,5}([A-Za-z]|[\x{0600}-\x{06FF}])\d{1,2}$/u', $normalized)) {
                    $fail('The license plate format is invalid. Format: numbers-letter-region code (e.g. 12345-A-6 or 12345-أ-6)');
                }
            },
        ],
        'color' => 'nullable|string|max:100',
        'mise_en_service_date' => 'required|date',
        'status' => 'required|in:available,rented,maintenance,unavailable',
        'price_per_day' => 'required|numeric|min:0',
        'weekly_price' => 'nullable|numeric|min:0',
        'monthly_price' => 'nullable|numeric|min:0',
        'discount_percentage' => 'nullable|numeric|min:0|max:100',
        'fuel_type' => 'required|in:diesel,gasoline,electric,hybrid,petrol',
        'transmission' => 'required|in:manual,automatic,semi-automatic',
        'mileage' => 'required|integer|min:0',
        'engine_capacity' => 'nullable|string|max:50',
        'seats' => 'required|integer|min:1|max:20',
        'features' => 'nullable|array',
        'description' => 'nullable|string',
        'meta_title' => 'nullable|string|max:255',
        'meta_description' => 'nullable|string|max:500',
        'meta_keywords' => 'nullable|string|max:255',
        
        // Document fields
        'assurance_number' => 'required|string|max:100',
        'assurance_company' => 'required|string|max:100',
        'assurance_expiry_date' => 'required|date|after_or_equal:today',
        'carte_grise_number' => 'required|string|max:100',
        'carte_grise_expiry_date' => 'nullable|date',
        'vignette_expiry_date' => 'required|date|after_or_equal:today',
        'visite_technique_date' => 'nullable|date',
        'visite_technique_expiry_date' => 'required|date|after_or_equal:today',
        
        'main_image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        
        // Document files
        'file_carte_grise' => 'nullable|file|mimes:pdf,jpeg,png,jpg,gif,webp|max:2048',
        'file_assurance' => 'nullable|file|mimes:pdf,jpeg,png,jpg,gif,webp|max:2048',
        'file_visite_technique' => 'nullable|file|mimes:pdf,jpeg,png,jpg,gif,webp|max:2048',
        'file_vignette' => 'nullable|file|mimes:pdf,jpeg,png,jpg,gif,webp|max:2048',
    ]);
    
    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'errors' => $validator->errors()
        ], 422);
    }

    try {
        // Prepare car data
        $data = $request->except([
            'main_image', 'images', '_token', 
            'carte_grise_number', 'carte_grise_expiry_date', 
            'assurance_number', 'assurance_company', 'assurance_expiry_date', 
            'vignette_expiry_date',
            'visite_technique_date', 'visite_technique_expiry_date',
            'file_carte_grise', 'file_assurance', 'file_visite_technique', 'file_vignette'
        ]);
        
        $data['slug'] = $this->generateUniqueSlug($request->name, $request->color);

        $data['features'] = $request->features ?? [];
        $data['brand_name'] = Brand::find($request->brand_id)->name ?? '';
        
        // Set daily_price equal to price_per_day for consistency
        $data['daily_price'] = $request->price_per_day;
        
        // Set is_available based on status
        $data['is_available'] = ($request->status === 'available');
        
        // Handle automatic pricing if weekly/monthly not provided
        $data['weekly_price'] = $request->weekly_price ?? $request->price_per_day * 5;
        $data['monthly_price'] = $request->monthly_price ?? $request->price_per_day * 22;
        
        // Handle main image upload
        if ($request->hasFile('main_image')) {
            $data['main_image'] = $this->processImage($request->file('main_image'), 'cars', $data['slug']);
        }
        
        // Create the car
        $car = Car::create($data);
        
        // Create or update CarDocuments
        $this->syncCarDocuments($car, $request);
        
        // Handle additional images upload
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $image) {
                $imagePath = $this->processImage($image, 'cars/gallery', $data['slug'] . '-' . ($index + 1));
                
                CarImage::create([
                    'car_id' => $car->id,
                    'image_path' => $imagePath,
                    'alt_text' => $car->name . ' - Image ' . ($index + 1),
                    'sort_order' => $index,
                    'is_featured' => $index === 0
                ]);
            }
        }
        
        // Log activity
        $this->logActivity(
            $car,
            'car_created',
            'Car Created',
            'Created a new car: ' . $car->name,
            ['car_name' => $car->name]
        );
        
        // For AJAX requests, return JSON
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Car created successfully.',
                'car' => $car->load('images', 'category', 'brand'),
                'redirect' => route('admin.cars.index')  // Change to index instead of show
            ]);
        }
        
        // For regular requests (shouldn't happen with modal form)
        return redirect()->route('admin.cars.index')
            ->with('success', 'Car created successfully.');
            
    } catch (\Exception $e) {
        // For AJAX requests, return JSON error
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while creating the car: ' . $e->getMessage()
            ], 500);
        }
        
        // For regular requests (shouldn't happen with modal form)
        return redirect()->back()
            ->withInput()
            ->with('error', 'An error occurred while creating the car: ' . $e->getMessage());
    }
}

    /**
 * Display the specified resource.
 */
public function show(Car $car)
{
    // Load relationships
    $car->load('images', 'category', 'brand', 'bookings');
    
    // Get maintenance records if any
    $maintenance = $car->maintenance()->latest()->get();
    
    // Get documents
    $documents = CarDocuments::where('car_id', $car->id)->first();
    
    return view('admin.cars.show', compact('car', 'maintenance', 'documents'));
}

    /**
 * Show the form for editing the specified car.
 */
public function edit(Car $car)
{
    // Load relationships - add documents relation here
    $car->load(['images', 'documents']);
    
    // Debug info
    \Log::info('Car data for edit:', [
        'car_id' => $car->id,
        'has_documents' => $car->documents ? 'yes' : 'no'
    ]);
    
    // Make sure documents exist, create if missing
    if (!$car->documents) {
        \Log::info("No documents found for car ID: {$car->id}. Creating a new document record.");
        
        // Create a new document record for this car
        $documents = new CarDocuments();
        $documents->car_id = $car->id;
        $documents->save();
        
        // Reload the car with the new documents
        $car->load('documents');
    }
    
    return response()->json([
        'success' => true,
        'car' => $car
    ]);
}

   /**
 * Update the specified resource in storage.
 */
public function update(Request $request, Car $car)
{
    // Check permission
    if (!Auth::guard('admin')->user()->can('edit cars')) {
        return response()->json([
            'success' => false,
            'message' => 'You do not have permission to edit cars.'
        ], 403);
    }
    
    // Validate input with all fields including Moroccan-specific ones
    $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:255',
        'category_id' => 'required|exists:categories,id',
        'brand_id' => 'required|exists:brands,id',
        'model' => 'required|string|max:255',
        'year' => 'required|digits:4|integer|min:1900|max:'.(date('Y')+1),
        'chassis_number' => 'required|string|unique:cars,chassis_number,'.$car->id,
        'matricule' => [
            'required',
            'string',
            'unique:cars,matricule,'.$car->id, // Exclude current car from unique check
            function ($attribute, $value, $fail) {
                // Allow various formats with separators: 12345-A-6, 12345-أ-6, 12345A6, 12345أ6, etc.
                $normalized = preg_replace('/[-|]/', '', $value);
                
                // Validate normalized pattern: 1-5 digits + Latin or Arabic letter + 1-2 digits for region code
                if (!preg_match('/^\d{1,5}([A-Za-z]|[\x{0600}-\x{06FF}])\d{1,2}$/u', $normalized)) {
                    $fail('The license plate format is invalid. Format: numbers-letter-region code (e.g. 12345-A-6 or 12345-أ-6)');
                }
            },
        ],
        'color' => 'nullable|string|max:100',
        'mise_en_service_date' => 'required|date',
        'status' => 'required|in:available,rented,maintenance,unavailable',
        'price_per_day' => 'required|numeric|min:0',
        'weekly_price' => 'nullable|numeric|min:0',
        'monthly_price' => 'nullable|numeric|min:0',
        'discount_percentage' => 'nullable|numeric|min:0|max:100',
        'fuel_type' => 'required|in:diesel,gasoline,electric,hybrid,petrol',
        'transmission' => 'required|in:manual,automatic,semi-automatic',
        'mileage' => 'required|integer|min:0',
        'engine_capacity' => 'nullable|string|max:50',
        'seats' => 'required|integer|min:1|max:20',
        'features' => 'nullable|array',
        'description' => 'nullable|string',
        'meta_title' => 'nullable|string|max:255',
        'meta_description' => 'nullable|string|max:500',
        'meta_keywords' => 'nullable|string|max:255',
        
        // Document fields validation - these will go to CarDocuments model
        'assurance_number' => 'required|string|max:100',
        'assurance_company' => 'required|string|max:100',
        'assurance_expiry_date' => 'required|date',
        'carte_grise_number' => 'required|string|max:100',
        'carte_grise_expiry_date' => 'nullable|date',
        'vignette_expiry_date' => 'required|date',
        'visite_technique_date' => 'nullable|date',
        'visite_technique_expiry_date' => 'required|date',
        
        'main_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        'removed_images' => 'nullable|string', // comma-separated IDs of images to remove
        
        // Optional document files
        'file_carte_grise' => 'nullable|file|mimes:pdf,jpeg,png,jpg,gif,webp|max:2048',
        'file_assurance' => 'nullable|file|mimes:pdf,jpeg,png,jpg,gif,webp|max:2048',
        'file_visite_technique' => 'nullable|file|mimes:pdf,jpeg,png,jpg,gif,webp|max:2048',
        'file_vignette' => 'nullable|file|mimes:pdf,jpeg,png,jpg,gif,webp|max:2048',
    ]);
    
    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'errors' => $validator->errors()
        ], 422);
    }

    try {
        // Prepare car data - exclude document fields and images
        $data = $request->except([
            'main_image', 'images', '_token', '_method', 'removed_images',
            'carte_grise_number', 'carte_grise_expiry_date', 
            'assurance_number', 'assurance_company', 'assurance_expiry_date', 
            'vignette_expiry_date',
            'visite_technique_date', 'visite_technique_expiry_date',
            'file_carte_grise', 'file_assurance', 'file_visite_technique', 'file_vignette'
        ]);
        
        // IMPORTANT: Always set the slug, not just when name/color changes
        // If name or color changed, generate a new slug, otherwise use existing
        if ($car->name !== $request->name || $car->color !== $request->color) {
            $data['slug'] = $this->generateUniqueSlug($request->name, $request->color, $car->id);
        } else {
            // Ensure slug exists in the data array by using the current slug
            $data['slug'] = $car->slug;
        }
        
        $data['features'] = $request->features ?? [];
        $data['brand_name'] = Brand::find($request->brand_id)->name ?? '';
        
        // Set daily_price equal to price_per_day to maintain consistency
        $data['daily_price'] = $request->price_per_day;
        
        // Set is_available based on status
        $data['is_available'] = ($request->status === 'available');
        
        // Handle automatic pricing if weekly/monthly not provided
        if (empty($data['weekly_price'])) {
            $data['weekly_price'] = $data['price_per_day'] * 5; // 5 days instead of 7 as business discount
        }
        
        if (empty($data['monthly_price'])) {
            $data['monthly_price'] = $data['price_per_day'] * 22; // 22 days instead of 30
        }
        
        // Handle main image upload
        if ($request->hasFile('main_image')) {
            // Delete old image if exists
            if ($car->main_image) {
                Storage::disk('public')->delete($car->main_image);
            }
            
            $data['main_image'] = $this->processImage($request->file('main_image'), 'cars', $data['slug']);
        }
        
        // Update the car
        $car->update($data);
        
        // Create or update CarDocuments
        $this->syncCarDocuments($car, $request);
        
        // Handle removed images
        if ($request->filled('removed_images')) {
            $removedImages = array_filter(explode(',', $request->removed_images));
            
            if (!empty($removedImages)) {
                $imagesToDelete = CarImage::whereIn('id', $removedImages)
                    ->where('car_id', $car->id)
                    ->get();
                
                foreach ($imagesToDelete as $image) {
                    Storage::disk('public')->delete($image->image_path);
                    $image->delete();
                }
                
                // If we deleted the featured image, assign a new one
                if ($imagesToDelete->where('is_featured', true)->count() > 0) {
                    $newFeatured = $car->images()->first();
                    if ($newFeatured) {
                        $newFeatured->update(['is_featured' => true]);
                    }
                }
            }
        }
        
        // Handle additional images upload
        if ($request->hasFile('images')) {
            // Get highest current sort order
            $maxOrder = $car->images()->max('sort_order') ?? 0;
            
            foreach ($request->file('images') as $index => $image) {
                $imagePath = $this->processImage($image, 'cars/gallery', $data['slug'] . '-' . ($maxOrder + $index + 1));
                
                CarImage::create([
                    'car_id' => $car->id,
                    'image_path' => $imagePath,
                    'alt_text' => $car->name . ' - Image ' . ($maxOrder + $index + 1),
                    'sort_order' => $maxOrder + $index + 1,
                    'is_featured' => $car->images()->count() === 0 // Make featured if no other images
                ]);
            }
        }
        
        // Check and alert for document expirations
        $carDocuments = CarDocuments::where('car_id', $car->id)->first();
        if ($carDocuments && method_exists($carDocuments, 'hasExpiringDocuments') && $carDocuments->hasExpiringDocuments()) {
            $alerts = $carDocuments->getExpiringDocuments();
            
            $this->logActivity(
                $car,
                'document_expiry_warning',
                'Document Expiration Warning',
                'Car ' . $car->name . ' has documents that will expire soon',
                ['alerts' => $alerts]
            );
        }
        
        // Log car update activity
        $this->logActivity(
            $car,
            'car_updated',
            'Car Updated',
            'Updated car: ' . $car->name,
            ['changes' => $car->getChanges()]
        );
        
        return response()->json([
            'success' => true,
            'message' => 'Car updated successfully.',
            'car' => $car->fresh(['images', 'category', 'brand'])
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'An error occurred while updating the car: ' . $e->getMessage()
        ], 500);
    }
}
    
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Car $car)
    {
        // Check permission
        if (!Auth::guard('admin')->user()->can('delete cars')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to delete cars.'
            ], 403);
        }
        
        try {
            // Check if car has active bookings
            if ($car->bookings()->whereIn('status', ['pending', 'confirmed', 'active'])->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete car with active bookings.'
                ], 400);
            }
            
            // Store data for activity log
            $carData = [
                'id' => $car->id,
                'name' => $car->name
            ];
            
            // Delete main image if exists
            if ($car->main_image) {
                Storage::disk('public')->delete($car->main_image);
            }
            
            // Delete additional images
            foreach ($car->images as $image) {
                Storage::disk('public')->delete($image->image_path);
            }
            
            // Delete associated documents
            CarDocuments::where('car_id', $car->id)->delete();
            
            $car->delete();
            
            // Log activity for car deletion
            $activity = new Activity();
            $activity->log_name = 'cars';
            $activity->description = 'Deleted car';
            $activity->causer_type = get_class(Auth::guard('admin')->user());
            $activity->causer_id = Auth::guard('admin')->user()->id;
            $activity->properties = $carData;
            $activity->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Car deleted successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting the car: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Upload images for a car.
     */
    public function uploadImages(Request $request, Car $car)
    {
        // Check permission
        if (!Auth::guard('admin')->user()->can('edit cars')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to edit cars.'
            ], 403);
        }
        
        $request->validate([
            'images.*' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);
        
        try {
            $uploadedImages = [];
            
            if ($request->hasFile('images')) {
                // Get highest current sort order
                $maxOrder = $car->images()->max('sort_order') ?? 0;
                
                foreach ($request->file('images') as $index => $image) {
                    $imagePath = $this->processImage($image, 'cars/gallery', $car->slug . '-' . ($maxOrder + $index + 1));
                    
                    // Create image record
                    $carImage = CarImage::create([
                        'car_id' => $car->id,
                        'image_path' => $imagePath,
                        'alt_text' => $car->name . ' - Image ' . ($index + 1),
                        'sort_order' => $maxOrder + $index + 1,
                        'is_featured' => $car->images->count() === 0 && $index === 0, // First image is featured if no other images
                    ]);
                    
                    $uploadedImages[] = [
                        'id' => $carImage->id,
                        'path' => Storage::url($carImage->image_path),
                        'is_featured' => $carImage->is_featured,
                    ];
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => count($uploadedImages) . ' images uploaded successfully.',
                'images' => $uploadedImages
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while uploading images: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Delete a car image.
     */
    public function deleteImage(Request $request)
    {
        // Check permission
        if (!Auth::guard('admin')->user()->can('edit cars')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to edit cars.'
            ], 403);
        }
        
        $request->validate([
            'image_id' => 'required|exists:car_images,id'
        ]);
        
        try {
            $image = CarImage::findOrFail($request->image_id);
            $carId = $image->car_id;
            $isFeatured = $image->is_featured;
            
            // Delete file
            Storage::disk('public')->delete($image->image_path);
            
            // Delete record
            $image->delete();
            
            // If it was the featured image, set another image as featured
            if ($isFeatured) {
                $newFeaturedImage = CarImage::where('car_id', $carId)->first();
                if ($newFeaturedImage) {
                    $newFeaturedImage->update(['is_featured' => true]);
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Image deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting the image: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Set image as featured.
     */
    public function setFeaturedImage(Request $request)
    {
        // Check permission
        if (!Auth::guard('admin')->user()->can('edit cars')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to edit cars.'
            ], 403);
        }
        
        $request->validate([
            'image_id' => 'required|exists:car_images,id',
            'car_id' => 'required|exists:cars,id'
        ]);
        
        try {
            // Remove featured flag from all images of this car
            CarImage::where('car_id', $request->car_id)
                ->update(['is_featured' => false]);
            
            // Set the selected image as featured
            CarImage::where('id', $request->image_id)
                ->update(['is_featured' => true]);
            
            return response()->json([
                'success' => true,
                'message' => 'Featured image updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while setting the featured image: ' . $e->getMessage()
            ], 500);
        }
    }
    
   /**
     * Update image order.
     */
    public function updateImageOrder(Request $request)
    {
        // Check permission
        if (!Auth::guard('admin')->user()->can('edit cars')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to edit cars.'
            ], 403);
        }
        
        $request->validate([
            'images' => 'required|array',
            'images.*.id' => 'required|exists:car_images,id',
            'images.*.order' => 'required|integer|min:0'
        ]);
        
        try {
            foreach ($request->images as $image) {
                CarImage::where('id', $image['id'])->update(['sort_order' => $image['order']]);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Image order updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating image order: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get available cars for AJAX select.
     */
    public function availableCars(Request $request)
    {
        $search = $request->get('search');
        $cars = Car::where('is_available', true)
            ->when($search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhereHas('brand', function($brandQuery) use ($search) {
                          $brandQuery->where('name', 'like', "%{$search}%");
                      });
                });
            })
            ->with('brand')
            ->select('id', 'name', 'brand_id', 'price_per_day')
            ->get()
            ->map(function ($car) {
                return [
                    'id' => $car->id,
                    'text' => ($car->brand ? $car->brand->name . ' ' : '') . $car->name . ' - ' . number_format($car->price_per_day, 2) . ' MAD/day',
                ];
            });
            
        return response()->json($cars);
    }

    /**
 * Generate a unique slug for the car
 *
 * @param string $name Base name for the slug
 * @param string|null $color Optional color to include in slug
 * @param int|null $excludeId Car ID to exclude from uniqueness check (for updates)
 * @return string
 */
private function generateUniqueSlug($name, $color = null, $excludeId = null)
{
    // Create base slug from name
    $baseSlug = Str::slug($name);
    
    // If color is provided, append it to make the base slug more unique
    if ($color) {
        $baseSlug .= '-' . Str::slug($color);
    }
    
    $slug = $baseSlug;
    $counter = 1;
    
    // Keep checking until we find a unique slug
    while (true) {
        // Build query to check for existing slug
        $query = Car::where('slug', $slug);
        
        // If we're updating, exclude the current car
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        // If no matching slug exists, we're done
        if ($query->doesntExist()) {
            break;
        }
        
        // Otherwise, increment counter and try again
        $slug = $baseSlug . '-' . $counter;
        $counter++;
    }
    
    return $slug;
}

    /**
     * Check document expiration dates and set alerts for soon-to-expire documents
     * 
     * @param Car $car
     * @return void
     */
    private function checkDocumentExpirations(Car $car)
    {
        // Now get expirations from CarDocuments model
        $carDocs = CarDocuments::where('car_id', $car->id)->first();
        if (!$carDocs) {
            return;
        }
        
        $alerts = [];
        $warningThreshold = 30; // days
        $today = now();
        
        // Check vignette
        if ($carDocs->vignette_expiry_date) {
            $daysUntilExpiry = $today->diffInDays($carDocs->vignette_expiry_date, false);
            if ($daysUntilExpiry < 0) {
                $alerts[] = 'Vignette has already expired on ' . $carDocs->vignette_expiry_date->format('d/m/Y');
            } elseif ($daysUntilExpiry < $warningThreshold) {
                $alerts[] = 'Vignette will expire in ' . $daysUntilExpiry . ' days';
            }
        }
        
        // Check technical inspection
        if ($carDocs->visite_technique_expiry_date) {
            $daysUntilExpiry = $today->diffInDays($carDocs->visite_technique_expiry_date, false);
            if ($daysUntilExpiry < 0) {
                $alerts[] = 'Technical inspection has already expired on ' . $carDocs->visite_technique_expiry_date->format('d/m/Y');
            } elseif ($daysUntilExpiry < $warningThreshold) {
                $alerts[] = 'Technical inspection will expire in ' . $daysUntilExpiry . ' days';
            }
        }
        
        // If alerts exist, log them for admin notification
        if (!empty($alerts)) {
            $activity = new Activity();
            $activity->log_name = 'cars';
            $activity->description = 'Document expiration warning';
            $activity->subject_type = get_class($car);
            $activity->subject_id = $car->id;
            $activity->causer_type = get_class(Auth::guard('admin')->user());
            $activity->causer_id = Auth::guard('admin')->user()->id;
            $activity->properties = ['alerts' => $alerts];
            $activity->save();
        }
    }
    
 /**
 * Sync car documents with the CarDocuments model
 * 
 * @param Car $car
 * @param Request $request
 * @return void
 */
private function syncCarDocuments(Car $car, Request $request)
{
    // Find existing document record or create new one
    $document = CarDocuments::firstOrNew(['car_id' => $car->id]);
    
    // Set document data
    $document->car_id = $car->id;
    $document->carte_grise_number = $request->carte_grise_number;
    $document->carte_grise_expiry_date = $request->carte_grise_expiry_date;
    $document->assurance_number = $request->assurance_number;
    $document->assurance_company = $request->assurance_company;
    $document->assurance_expiry_date = $request->assurance_expiry_date;
    $document->visite_technique_date = $request->visite_technique_date ?? now();
    $document->visite_technique_expiry_date = $request->visite_technique_expiry_date;
    $document->vignette_expiry_date = $request->vignette_expiry_date;
    
    // Handle file uploads if present
    if ($request->hasFile('file_carte_grise')) {
        if ($document->file_carte_grise) {
            Storage::disk('public')->delete($document->file_carte_grise);
        }
        $document->file_carte_grise = $this->storeDocumentFile($request->file('file_carte_grise'), 'car-documents/carte-grise');
    }
    
    if ($request->hasFile('file_assurance')) {
        if ($document->file_assurance) {
            Storage::disk('public')->delete($document->file_assurance);
        }
        $document->file_assurance = $this->storeDocumentFile($request->file('file_assurance'), 'car-documents/assurance');
    }
    
    if ($request->hasFile('file_visite_technique')) {
        if ($document->file_visite_technique) {
            Storage::disk('public')->delete($document->file_visite_technique);
        }
        $document->file_visite_technique = $this->storeDocumentFile($request->file('file_visite_technique'), 'car-documents/visite-technique');
    }
    
    if ($request->hasFile('file_vignette')) {
        if ($document->file_vignette) {
            Storage::disk('public')->delete($document->file_vignette);
        }
        $document->file_vignette = $this->storeDocumentFile($request->file('file_vignette'), 'car-documents/vignette');
    }
    
    $document->save();
    
    // Check for expiring documents and log warnings if necessary
    if ($document->hasExpiringDocuments()) {
        $expiringDocs = $document->getExpiringDocuments();
        $this->logActivity(
            $car,
            'document_expiry_warning',
            'Document Expiration Warning',
            'Car ' . $car->name . ' has documents that will expire soon',
            ['expiring_documents' => $expiringDocs]
        );
    }
}
    
    /**
     * Store a document file and return the path
     * 
     * @param \Illuminate\Http\UploadedFile $file
     * @param string $directory
     * @return string
     */
    private function storeDocumentFile($file, $directory)
    {
        $filename = Str::random(10) . '-' . time() . '.' . $file->getClientOriginalExtension();
        $path = $directory . '/' . $filename;
        
        // Create directory if it doesn't exist
        if (!Storage::exists('public/' . $directory)) {
            Storage::makeDirectory('public/' . $directory);
        }
        
        // Store the file
        $file->storeAs('public/' . $directory, $filename);
        
        return $path;
    }

    /**
     * Process and store image with optimizations.
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @param string $directory
     * @param string $slug
     * @return string
     */
    private function processImage($file, $directory, $slug)
    {
        // Generate unique filename
        $filename = $slug . '-' . time() . '.' . $file->getClientOriginalExtension();
        
        // Create full path
        $path = $directory . '/' . $filename;
        
        // Create directory if it doesn't exist
        if (!Storage::exists('public/' . $directory)) {
            Storage::makeDirectory('public/' . $directory);
        }
        
        // Process and optimize the image using Intervention Image
        try {
            // Use the proper Intervention Image instantiation
            $img = \Intervention\Image\Facades\Image::make($file->getRealPath());
            
            // Resize to a reasonable dimension while maintaining aspect ratio
            $img->resize(1200, null, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
            
            // Save with medium quality to reduce file size
            $img->save(storage_path('app/public/' . $path), 80);
            
            return $path;
        } catch (\Exception $e) {
            // Fallback to regular file storage if image manipulation fails
            $file->storeAs('public/' . $directory, $filename);
            return $path;
        }
    }
    
    /**
     * Format Moroccan license plate
     * 
     * @param string $value
     * @return string
     */
    private function formatMatricule($value)
    {
        if (!$value) return $value;
        
        // First normalize by removing all separators
        $normalized = preg_replace('/[-|]/', '', $value);
        
        // Check if it matches the Moroccan pattern for digits + letter + region
        // Supporting both Arabic and Latin letters
        $moroccanPlateRegex = '/^(\d{1,5})([A-Za-z]|[\x{0600}-\x{06FF}])(\d{1,2})$/u';
        
        if (preg_match($moroccanPlateRegex, $normalized, $matches)) {
            if (count($matches) === 4) {
                $digits = $matches[1];
                $letter = $matches[2]; 
                $regionCode = $matches[3];
                
                // Format with hyphens for better readability
                return $digits . '-' . $letter . '-' . $regionCode;
            }
        }
        
        return $value;
    }
    
    /**
     * Validate Moroccan license plate
     * 
     * @param string $value
     * @return bool
     */
    private function validateMatricule($value)
    {
        if (!$value) return true; // Empty is handled by required attribute
        
        // Accept different separator styles
        $normalized = preg_replace('/[-|]/', '', $value);
        
        // Pattern: digits + letter (Arabic or Latin) + region code
        $moroccanPlateRegex = '/^\d{1,5}([A-Za-z]|[\x{0600}-\x{06FF}])\d{1,2}$/u';
        
        return preg_match($moroccanPlateRegex, $normalized) === 1;
    }
    
    /**
     * Get expiring documents for dashboard alerts
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getExpiringDocuments()
    {
        $warningThreshold = now()->addDays(30);
        
        return CarDocuments::where('vignette_expiry_date', '<=', $warningThreshold)
            ->orWhere('visite_technique_expiry_date', '<=', $warningThreshold)
            ->orWhere('assurance_expiry_date', '<=', $warningThreshold)
            ->with('car.brand')
            ->get()
            ->map(function ($carDoc) {
                $alerts = [];
                
                if ($carDoc->vignette_expiry_date && $carDoc->vignette_expiry_date <= now()->addDays(30)) {
                    $daysLeft = now()->diffInDays($carDoc->vignette_expiry_date, false);
                    $status = $daysLeft < 0 ? 'expired' : 'expiring';
                    $alerts[] = [
                        'type' => 'vignette',
                        'document' => 'Vignette',
                        'days_left' => $daysLeft,
                        'status' => $status,
                        'date' => $carDoc->vignette_expiry_date->format('d/m/Y')
                    ];
                }
                
                if ($carDoc->visite_technique_expiry_date && $carDoc->visite_technique_expiry_date <= now()->addDays(30)) {
                    $daysLeft = now()->diffInDays($carDoc->visite_technique_expiry_date, false);
                    $status = $daysLeft < 0 ? 'expired' : 'expiring';
                    $alerts[] = [
                        'type' => 'technical_inspection',
                        'document' => 'Technical Inspection',
                        'days_left' => $daysLeft,
                        'status' => $status,
                        'date' => $carDoc->visite_technique_expiry_date->format('d/m/Y')
                    ];
                }
                
                if ($carDoc->assurance_expiry_date && $carDoc->assurance_expiry_date <= now()->addDays(30)) {
                    $daysLeft = now()->diffInDays($carDoc->assurance_expiry_date, false);
                    $status = $daysLeft < 0 ? 'expired' : 'expiring';
                    $alerts[] = [
                        'type' => 'assurance',
                        'document' => 'Insurance',
                        'days_left' => $daysLeft,
                        'status' => $status,
                        'date' => $carDoc->assurance_expiry_date->format('d/m/Y')
                    ];
                }
                
                return [
                    'car' => $carDoc->car,
                    'alerts' => $alerts
                ];
            })
            ->filter(function ($item) {
                return count($item['alerts']) > 0 && isset($item['car']);
            });
    }
    
    /**
     * Generate car export for reports
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function export(Request $request)
    {
        // Check permission
        if (!Auth::guard('admin')->user()->can('export cars')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to export cars.'
            ], 403);
        }
        
        // Get filtered cars
        $cars = Car::with(['category', 'brand', 'bookings'])
            ->when($request->filled('status'), function ($query) use ($request) {
                return $query->where('status', $request->status);
            })
            ->when($request->filled('category_id'), function ($query) use ($request) {
                return $query->where('category_id', $request->category_id);
            })
            ->when($request->filled('brand_id'), function ($query) use ($request) {
                return $query->where('brand_id', $request->brand_id);
            })
            ->get();
            
        // Create export data
        $exportData = $cars->map(function ($car) {
            // Get document data
            $carDocs = CarDocuments::where('car_id', $car->id)->first();
            
            return [
                'ID' => $car->id,
                'Name' => $car->name,
                'Brand' => $car->brand ? $car->brand->name : $car->brand_name,
                'Model' => $car->model,
                'Year' => $car->year,
                'Category' => $car->category ? $car->category->name : '',
                'Status' => ucfirst($car->status),
                'Price/Day' => number_format($car->price_per_day, 2) . ' MAD',
                'Matricule' => $car->matricule,
                'Mileage' => number_format($car->mileage) . ' km',
                'Fuel Type' => ucfirst($car->fuel_type),
                'Transmission' => ucfirst($car->transmission),
                'Vignette Expiry' => $carDocs && $carDocs->vignette_expiry_date ? $carDocs->vignette_expiry_date->format('d/m/Y') : 'N/A',
                'Tech. Inspection' => $carDocs && $carDocs->visite_technique_expiry_date ? $carDocs->visite_technique_expiry_date->format('d/m/Y') : 'N/A',
                'Insurance Expiry' => $carDocs && $carDocs->assurance_expiry_date ? $carDocs->assurance_expiry_date->format('d/m/Y') : 'N/A',
                'Total Bookings' => $car->bookings->count(),
                'Revenue' => number_format($car->bookings->sum('total_amount'), 2) . ' MAD'
            ];
        });
        
        // Export based on requested format
        $format = $request->format ?? 'csv';
        
        switch ($format) {
            case 'xlsx':
                return $this->exportExcel($exportData);
            case 'pdf':
                return $this->exportPdf($exportData);
            case 'csv':
            default:
                return $this->exportCsv($exportData);
        }
    }
    
    /**
     * Export data as CSV
     * 
     * @param Collection $data
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    private function exportCsv($data)
    {
        $filename = 'cars-export-' . date('Y-m-d') . '.csv';
        $headers = array_keys($data->first());
        
        $callback = function() use ($data, $headers) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);
            
            foreach ($data as $row) {
                fputcsv($file, $row);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
    
    /**
     * Export data as Excel (requires laravel-excel package)
     * 
     * @param Collection $data
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    private function exportExcel($data)
    {
        // This would require the Laravel Excel package to be installed
        // Example implementation if you have the package:
        // return Excel::download(new CarsExport($data), 'cars-export-' . date('Y-m-d') . '.xlsx');
        
        // Fallback to CSV if Excel export is not available
        return $this->exportCsv($data);
    }
    
    /**
     * Export data as PDF (requires laravel-dompdf or similar)
     * 
     * @param Collection $data
     * @return \Illuminate\Http\Response
     */
    private function exportPdf($data)
    {
        // This would require a PDF generation package like DomPDF
        // Example implementation if you have the package:
        // $pdf = PDF::loadView('admin.cars.export-pdf', ['cars' => $data]);
        // return $pdf->download('cars-export-' . date('Y-m-d') . '.pdf');
        
        // Fallback to CSV if PDF export is not available
        return $this->exportCsv($data);
    }
}