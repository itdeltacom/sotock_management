<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class BrandController extends Controller
{
    /**
     * Display a listing of the brands.
     */
    public function index()
    {
        return view('admin.cars.brands');
    }

    /**
     * Get brand data for DataTables.
     */
    public function data()
    {
        $brands = Brand::withCount('cars')->get();
        
        return DataTables::of($brands)
            ->addColumn('action', function (Brand $brand) {
                $actions = '';
                
                // Only show edit button if user has permission
                if (Auth::guard('admin')->user()->can('edit brands')) {
                    $actions .= '<button type="button" class="btn btn-sm btn-primary btn-edit me-1" data-id="'.$brand->id.'">
                        <i class="fas fa-edit"></i>
                    </button> ';
                }
                
                // Only show delete button if user has permission
                if (Auth::guard('admin')->user()->can('delete brands')) {
                    $actions .= '<button type="button" class="btn btn-sm btn-danger btn-delete" data-id="'.$brand->id.'" data-name="'.$brand->name.'">
                        <i class="fas fa-trash"></i>
                    </button>';
                }
                
                return $actions;
            })
            ->addColumn('status', function (Brand $brand) {
                return $brand->is_active 
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-danger">Inactive</span>';
            })
            ->addColumn('logo', function (Brand $brand) {
                if ($brand->logo) {
                    return '<img src="'.Storage::url($brand->logo).'" alt="'.$brand->name.'" width="50" class="img-thumbnail">';
                }
                
                return '<span class="badge bg-secondary">No Logo</span>';
            })
            ->rawColumns(['action', 'status', 'logo'])
            ->make(true);
    }

    /**
     * Store a newly created brand in storage.
     */
    public function store(Request $request)
    {
        // Check permission
        if (!Auth::guard('admin')->user()->can('create brands')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to create brands.'
            ], 403);
        }
        
        // Validate input
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,svg|max:2048',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->except(['logo']);
        $data['slug'] = Str::slug($request->name);
        
        if ($request->hasFile('logo')) {
            $data['logo'] = $this->storeLogoAsWebp($request->file('logo'), $data['slug']);
        }
        
        $brand = Brand::create($data);
        
        // Log activity if spatie activity-log package is installed
        if (method_exists(app(), 'activity')) {
            activity()
                ->causedBy(Auth::guard('admin')->user())
                ->performedOn($brand)
                ->withProperties(['brand_name' => $brand->name])
                ->log('Created brand');
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Brand created successfully.',
            'brand' => $brand
        ]);
    }

    /**
     * Show the form for editing the specified brand.
     */
    public function edit(Brand $brand)
    {
        return response()->json([
            'success' => true,
            'brand' => $brand
        ]);
    }

    /**
     * Update the specified brand in storage.
     */
    public function update(Request $request, Brand $brand)
    {
        // Check permission
        if (!Auth::guard('admin')->user()->can('edit brands')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to edit brands.'
            ], 403);
        }
        
        // Validate input
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,svg|max:2048',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->except(['logo', '_method']);
        $data['slug'] = Str::slug($request->name);
        
        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($brand->logo) {
                Storage::disk('public')->delete($brand->logo);
            }
            
            $data['logo'] = $this->storeLogoAsWebp($request->file('logo'), $data['slug']);
        }
        
        $brand->update($data);
        
        // Log activity if spatie activity-log package is installed
        if (method_exists(app(), 'activity')) {
            activity()
                ->causedBy(Auth::guard('admin')->user())
                ->performedOn($brand)
                ->withProperties(['brand_name' => $brand->name])
                ->log('Updated brand');
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Brand updated successfully.',
            'brand' => $brand
        ]);
    }

    /**
     * Remove the specified brand from storage.
     */
    public function destroy(Brand $brand)
    {
        // Check permission
        if (!Auth::guard('admin')->user()->can('delete brands')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to delete brands.'
            ], 403);
        }
        
        // Check if brand has associated cars
        if ($brand->cars()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete brand with associated cars.'
            ], 400);
        }
        
        // Store data for activity log
        $brandData = [
            'id' => $brand->id,
            'name' => $brand->name
        ];
        
        // Delete logo if exists
        if ($brand->logo) {
            Storage::disk('public')->delete($brand->logo);
        }
        
        $brand->delete();
        
        // Log activity if spatie activity-log package is installed
        if (method_exists(app(), 'activity')) {
            activity()
                ->causedBy(Auth::guard('admin')->user())
                ->withProperties($brandData)
                ->log('Deleted brand');
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Brand deleted successfully.'
        ]);
    }

    /**
     * Convert and store image as WebP format with slug-based filename.
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @param string $slug
     * @return string
     */
    private function storeLogoAsWebp($file, $slug)
    {
        // Get app name from config and convert to slug format
        $appNameSlug = Str::slug(config('app.name', ),'webp');
        
        // Generate unique filename using slug, app name, and timestamp
        $filename = $slug . '-' . $appNameSlug . '-' . time() . '.webp';
        
        // Create directory path
        $directory = 'brands';
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