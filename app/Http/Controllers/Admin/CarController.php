<?php

namespace App\Http\Controllers\Admin;

use App\Models\Car;
use App\Models\Brand;
use App\Models\CarImage;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Intervention\Image\Image;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;

class CarController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        $brands = Brand::where('is_active', true)->orderBy('name')->get();
        
        return view('admin.cars.cars', compact('categories', 'brands'));
    }

    /**
     * Process DataTables AJAX request.
     */
    public function datatable(Request $request)
    {
        $cars = Car::with(['category', 'brand', 'images'])->latest();
        
        return DataTables::of($cars)
            ->addColumn('image', function (Car $car) {
                if ($car->main_image) {
                    return '<img src="' . Storage::url($car->main_image) . '" alt="' . $car->name . '" class="img-thumbnail" width="80">';
                }
                return '<div class="bg-light text-center p-2" style="width:80px;height:60px;"><i class="fas fa-car fa-2x text-muted"></i></div>';
            })
            ->addColumn('brand_model', function (Car $car) {
                return ($car->brand ? $car->brand->name : '') . ' ' . $car->name;
            })
            ->addColumn('status', function (Car $car) {
                return $car->is_available 
                    ? '<span class="badge bg-success">Available</span>'
                    : '<span class="badge bg-danger">Unavailable</span>';
            })
            ->addColumn('price', function (Car $car) {
                $output = '$' . number_format($car->price_per_day, 2) . '/day';
                
                if ($car->discount_percentage > 0) {
                    $discountedPrice = $car->price_per_day * (1 - ($car->discount_percentage / 100));
                    $output .= '<br><span class="text-success">$' . number_format($discountedPrice, 2) . ' after ' . $car->discount_percentage . '% discount</span>';
                }
                
                return $output;
            })
            ->addColumn('category_name', function (Car $car) {
                return $car->category ? $car->category->name : '';
            })
            ->addColumn('brand_name', function (Car $car) {
                return $car->brand ? $car->brand->name : '';
            })
            ->addColumn('booking_count', function (Car $car) {
                return $car->bookings()->count();
            })
            ->addColumn('actions', function (Car $car) {
                $buttons = '<div class="btn-group" role="group">';
                
                // View button
                $buttons .= '<a href="' . route('admin.cars.show', $car->id) . '" class="btn btn-sm btn-info" title="View"><i class="fas fa-eye"></i></a>';
                
                // Edit button (with permission check)
                if (Auth::guard('admin')->user()->can('edit cars')) {
                    $buttons .= '<button type="button" class="btn btn-sm btn-primary btn-edit" data-id="'.$car->id.'" title="Edit">
                        <i class="fas fa-edit"></i>
                    </button>';
                }
                
                // Delete button (with permission check)
                if (Auth::guard('admin')->user()->can('delete cars')) {
                    $buttons .= '<button type="button" class="btn btn-sm btn-danger btn-delete" data-id="'.$car->id.'" data-name="'.$car->name.'" title="Delete">
                        <i class="fas fa-trash"></i>
                    </button>';
                }
                
                $buttons .= '</div>';
                return $buttons;
            })
            ->rawColumns(['image', 'status', 'price', 'actions'])
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.cars.create');
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
            'price_per_day' => 'required|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'seats' => 'required|integer|min:1|max:50',
            'transmission' => 'required|string|in:automatic,manual,semi-automatic',
            'fuel_type' => 'required|string|in:petrol,diesel,electric,hybrid,lpg',
            'mileage' => 'nullable|integer|min:0',
            'engine_capacity' => 'nullable|string',
            'features' => 'nullable|array',
            'is_available' => 'boolean',
            'main_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $data = $request->except(['main_image', 'images', '_token']);
            $data['slug'] = Str::slug($request->name);
            $data['features'] = $request->features ?? [];
            $data['rating'] = 0;
            $data['review_count'] = 0;
            
            // Handle main image upload
            if ($request->hasFile('main_image')) {
                $data['main_image'] = $this->processImage($request->file('main_image'), 'cars', $data['slug']);
            }
            
            // Create the car
            $car = Car::create($data);
            
            // Handle additional images upload
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $index => $image) {
                    $imagePath = $this->processImage($image, 'cars/gallery', $data['slug'] . '-' . ($index + 1));
                    
                    CarImage::create([
                        'car_id' => $car->id,
                        'image_path' => $imagePath,
                        'alt_text' => $car->name . ' - Image ' . ($index + 1),
                        'sort_order' => $index,
                        'is_featured' => $index === 0 // First image is featured by default
                    ]);
                }
            }
            
            // Log activity if spatie activity-log package is installed
            if (method_exists(app(), 'activity')) {
                activity()
                    ->causedBy(Auth::guard('admin')->user())
                    ->performedOn($car)
                    ->withProperties(['car_name' => $car->name])
                    ->log('Created car');
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Car created successfully.',
                'car' => $car
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while creating the car: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Car $car)
    {
        $car->load('images', 'category', 'brand', 'bookings');
        
        return view('admin.cars.show', compact('car'));
    }

    /**
     * Show the form for editing the specified car.
     */
    public function edit(Car $car)
    {
        // Load relationships
        $car->load('images');
        
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
        
        // Validate input
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'required|exists:brands,id',
            'price_per_day' => 'required|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'seats' => 'required|integer|min:1|max:50',
            'transmission' => 'required|string|in:automatic,manual,semi-automatic',
            'fuel_type' => 'required|string|in:petrol,diesel,electric,hybrid,lpg',
            'mileage' => 'nullable|integer|min:0',
            'engine_capacity' => 'nullable|string',
            'features' => 'nullable|array',
            'is_available' => 'boolean',
            'main_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $data = $request->except(['main_image', 'images', '_token', '_method', 'removed_images']);
            $data['slug'] = Str::slug($request->name);
            $data['features'] = $request->features ?? [];
            
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
            
            // Handle removed images
            if ($request->has('removed_images') && !empty($request->removed_images)) {
                $removedImages = explode(',', $request->removed_images);
                $imagesToDelete = CarImage::whereIn('id', $removedImages)->where('car_id', $car->id)->get();
                
                foreach ($imagesToDelete as $image) {
                    // Delete image file
                    Storage::disk('public')->delete($image->image_path);
                    
                    // Delete record
                    $image->delete();
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
                        'alt_text' => $car->name . ' - Image ' . ($index + 1),
                        'sort_order' => $maxOrder + $index + 1,
                        'is_featured' => false
                    ]);
                }
            }
            
            // Log activity if spatie activity-log package is installed
            if (method_exists(app(), 'activity')) {
                activity()
                    ->causedBy(Auth::guard('admin')->user())
                    ->performedOn($car)
                    ->withProperties(['car_name' => $car->name])
                    ->log('Updated car');
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Car updated successfully.',
                'car' => $car
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
            
            $car->delete();
            
            // Log activity if spatie activity-log package is installed
            if (method_exists(app(), 'activity')) {
                activity()
                    ->causedBy(Auth::guard('admin')->user())
                    ->withProperties($carData)
                    ->log('Deleted car');
            }
            
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
                    'text' => ($car->brand ? $car->brand->name . ' ' : '') . $car->name . ' - $' . number_format($car->price_per_day, 2) . '/day',
                ];
            });
            
        return response()->json($cars);
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
        
        // Process and optimize the image
        $img = Image::make($file->getRealPath());
        
        // Resize to a reasonable dimension while maintaining aspect ratio
        $img->resize(1200, null, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });
        
        // Save with medium quality to reduce file size
        $img->save(storage_path('app/public/' . $path), 80);
        
        return $path;
    }
}