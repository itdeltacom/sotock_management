<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Car;
use App\Models\CarImage;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class CarController extends Controller
{
    /**
     * Display a listing of the cars.
     */
    public function index()
    {
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        $brands = Brand::where('is_active', true)->orderBy('name')->get();
        
        return view('admin.cars.cars', compact('categories', 'brands'));
    }

    /**
     * Get cars data for DataTables.
     */
    public function data()
    {
        $cars = Car::with(['category', 'brand'])->get();
        
        return DataTables::of($cars)
            ->addColumn('action', function (Car $car) {
                $actions = '';
                
                // Only show edit button if user has permission
                if (Auth::guard('admin')->user()->can('edit cars')) {
                    $actions .= '<button type="button" class="btn btn-sm btn-primary btn-edit me-1" data-id="'.$car->id.'">
                        <i class="fas fa-edit"></i>
                    </button> ';
                }
                
                // Only show delete button if user has permission
                if (Auth::guard('admin')->user()->can('delete cars')) {
                    $actions .= '<button type="button" class="btn btn-sm btn-danger btn-delete" data-id="'.$car->id.'" data-name="'.$car->name.'">
                        <i class="fas fa-trash"></i>
                    </button>';
                }
                
                return $actions;
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
            ->addColumn('image', function (Car $car) {
                if ($car->main_image) {
                    return '<img src="' . Storage::url($car->main_image) . '" alt="' . $car->name . '" width="80" class="img-thumbnail">';
                }
                
                return '<span class="badge bg-secondary">No Image</span>';
            })
            ->addColumn('booking_count', function (Car $car) {
                return $car->bookings()->count();
            })
            ->rawColumns(['action', 'status', 'price', 'image'])
            ->make(true);
    }

    /**
     * Store a newly created car in storage.
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

        $data = $request->except(['main_image', 'images', '_token']);
        $data['slug'] = Str::slug($request->name);
        $data['features'] = $request->features ?? [];
        $data['rating'] = 0;
        $data['review_count'] = 0;
        
        // Handle main image upload
        if ($request->hasFile('main_image')) {
            $data['main_image'] = $this->convertToWebp($request->file('main_image'), 'cars', $data['slug']);
        }
        
        // Create the car
        $car = Car::create($data);
        
        // Handle additional images upload
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $image) {
                $imagePath = $this->convertToWebp($image, 'cars/gallery', $data['slug'] . '-' . ($index + 1));
                
                CarImage::create([
                    'car_id' => $car->id,
                    'image_path' => $imagePath,
                    'alt_text' => $car->name . ' - Image ' . ($index + 1),
                    'sort_order' => $index,
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
                ->log('Created car');
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Car created successfully.',
            'car' => $car
        ]);
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
     * Update the specified car in storage.
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

        $data = $request->except(['main_image', 'images', '_token', '_method', 'removed_images']);
        $data['slug'] = Str::slug($request->name);
        $data['features'] = $request->features ?? [];
        
        // Handle main image upload
        if ($request->hasFile('main_image')) {
            // Delete old image if exists
            if ($car->main_image) {
                Storage::disk('public')->delete($car->main_image);
            }
            
            $data['main_image'] = $this->convertToWebp($request->file('main_image'), 'cars', $data['slug']);
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
                $imagePath = $this->convertToWebp($image, 'cars/gallery', $data['slug'] . '-' . ($maxOrder + $index + 1));
                
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
    }
    
    /**
     * Remove the specified car from storage.
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
    }
    
    /**
     * Update image order via AJAX
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
        
        foreach ($request->images as $image) {
            CarImage::where('id', $image['id'])->update(['sort_order' => $image['order']]);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Image order updated successfully'
        ]);
    }
    
    /**
     * Set image as featured via AJAX
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
    }
    
    /**
     * Delete image via AJAX
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
        
        $image = CarImage::findOrFail($request->image_id);
        
        // Delete file
        Storage::disk('public')->delete($image->image_path);
        
        // Delete record
        $image->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Image deleted successfully'
        ]);
    }

    /**
     * Convert and store image as WebP format with slug-based filename.
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @param string $directory
     * @param string $slug
     * @return string
     */
    private function convertToWebp($file, $directory, $slug)
    {
        // Get app name from config and convert to slug format
        $appNameSlug = Str::slug(config('app.name', 'laravel'));
        
        // Generate unique filename using slug, app name, and timestamp
        $filename = $slug . '-' . $appNameSlug . '-' . time() . '.webp';
        
        // Create full path
        $path = $directory . '/' . $filename;
        
        // Create temporary file path for the uploaded image
        $tempFile = $file->getRealPath();
        
        // Get image content type to determine how to handle the conversion
        $imageType = exif_imagetype($tempFile);
        
        // Create GD image based on image type
        switch ($imageType) {
            case IMAGETYPE_JPEG:
                $image = imagecreatefromjpeg($tempFile);
                break;
            case IMAGETYPE_PNG:
                $image = imagecreatefrompng($tempFile);
                // Handle transparency for PNG
                imagepalettetotruecolor($image);
                imagealphablending($image, true);
                imagesavealpha($image, true);
                break;
            case IMAGETYPE_GIF:
                $image = imagecreatefromgif($tempFile);
                break;
            case IMAGETYPE_WEBP:
                $image = imagecreatefromwebp($tempFile);
                break;
            default:
                throw new \Exception('Unsupported image type');
        }
        
        // Ensure the directory exists
        Storage::disk('public')->makeDirectory($directory);
        
        // Full path to where the WebP file will be saved
        $storagePath = storage_path('app/public/' . $path);
        
        // Convert to WebP and save (quality: 80%)
        imagewebp($image, $storagePath, 80);
        
        // Free memory
        imagedestroy($image);
        
        return $path;
    }
}