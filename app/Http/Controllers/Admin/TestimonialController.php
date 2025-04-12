<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class TestimonialController extends Controller
{
    /**
     * Display a listing of the testimonials.
     */
    public function index()
    {
        return view('admin.testimonials.index');
    }

    /**
     * Get testimonials data for DataTables.
     */
    public function data(Request $request)
    {
        $query = Testimonial::query();
        
        // Apply filters if provided
        if ($request->has('status') && !empty($request->status)) {
            if ($request->status === 'approved') {
                $query->where('is_approved', true);
            } elseif ($request->status === 'pending') {
                $query->where('is_approved', false);
            }
        }
        
        if ($request->has('rating') && !empty($request->rating)) {
            $query->where('rating', $request->rating);
        }
        
        if ($request->has('is_featured') && $request->is_featured === '1') {
            $query->where('is_featured', true);
        }
        
        $testimonials = $query->orderBy('created_at', 'desc')->get();
        
        return DataTables::of($testimonials)
            ->addColumn('action', function (Testimonial $testimonial) {
                $actions = '';
                
                // Only show edit button if user has permission
                if (Auth::guard('admin')->user()->can('edit testimonials')) {
                    $actions .= '<button type="button" class="btn btn-sm btn-primary me-1 btn-edit" data-id="'.$testimonial->id.'" title="Edit">
                        <i class="fas fa-edit"></i>
                    </button> ';
                }
                
                // Feature/unfeature button if user has permission
                if (Auth::guard('admin')->user()->can('edit testimonials')) {
                    $featured = $testimonial->is_featured ? '1' : '0';
                    $featureIcon = $testimonial->is_featured ? 
                        '<i class="fas fa-star text-warning"></i>' : 
                        '<i class="far fa-star"></i>';
                    $featureTitle = $testimonial->is_featured ? 'Remove from featured' : 'Add to featured';
                    
                    $actions .= '<button type="button" class="btn btn-sm btn-outline-warning me-1 btn-feature" data-id="'.$testimonial->id.'" data-featured="'.$featured.'" title="'.$featureTitle.'">
                        '.$featureIcon.'
                    </button> ';
                }
                
                // Approve/disapprove button if user has permission
                if (Auth::guard('admin')->user()->can('edit testimonials')) {
                    $approved = $testimonial->is_approved ? '1' : '0';
                    $approveIcon = $testimonial->is_approved ? 
                        '<i class="fas fa-check-circle text-success"></i>' : 
                        '<i class="fas fa-times-circle text-danger"></i>';
                    $approveTitle = $testimonial->is_approved ? 'Disapprove' : 'Approve';
                    
                    $actions .= '<button type="button" class="btn btn-sm btn-outline-success me-1 btn-approve" data-id="'.$testimonial->id.'" data-approved="'.$approved.'" title="'.$approveTitle.'">
                        '.$approveIcon.'
                    </button> ';
                }
                
                // Only show delete button if user has permission
                if (Auth::guard('admin')->user()->can('delete testimonials')) {
                    $actions .= '<button type="button" class="btn btn-sm btn-danger btn-delete" data-id="'.$testimonial->id.'" data-name="'.$testimonial->user_name.'" title="Delete">
                        <i class="fas fa-trash"></i>
                    </button>';
                }
                
                return $actions;
            })
            ->addColumn('status', function (Testimonial $testimonial) {
                if ($testimonial->is_approved) {
                    return '<span class="badge bg-success">Approved</span>';
                } else {
                    return '<span class="badge bg-warning">Pending</span>';
                }
            })
            ->addColumn('rating_stars', function (Testimonial $testimonial) {
                $stars = '';
                for ($i = 1; $i <= 5; $i++) {
                    if ($i <= $testimonial->rating) {
                        $stars .= '<i class="fas fa-star text-warning"></i> ';
                    } else {
                        $stars .= '<i class="far fa-star text-muted"></i> ';
                    }
                }
                return $stars;
            })
            ->addColumn('image', function (Testimonial $testimonial) {
                if ($testimonial->image) {
                    return '<img src="' . Storage::url($testimonial->image) . '" alt="' . $testimonial->user_name . '" width="50" class="img-thumbnail rounded-circle">';
                }
                
                return '<span class="badge bg-secondary">No Image</span>';
            })
            ->addColumn('featured', function (Testimonial $testimonial) {
                if ($testimonial->is_featured) {
                    return '<span class="badge bg-warning"><i class="fas fa-star"></i> Featured</span>';
                }
                
                return '';
            })
            ->rawColumns(['action', 'status', 'rating_stars', 'image', 'featured'])
            ->make(true);
    }

    /**
     * Store a newly created testimonial in storage.
     */
    public function store(Request $request)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'user_name' => 'required|string|max:255',
            'user_title' => 'nullable|string|max:100',
            'user_email' => 'nullable|email|max:255',
            'content' => 'required|string|max:1000',
            'rating' => 'required|integer|min:1|max:5',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'is_approved' => 'required|in:true,false',
            'is_featured' => 'required|in:true,false',
            'order' => 'nullable|integer|min:0',
        ]);
        
        if ($validator->fails()) {
            // For AJAX requests
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            
            // For traditional form submissions
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->except(['image', '_token']);
        
        // Convert string boolean values to actual booleans
        $data['is_approved'] = $request->is_approved === 'true' ? true : false;
        $data['is_featured'] = $request->is_featured === 'true' ? true : false;
        
        // Handle image upload
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('testimonials', 'public');
            $data['image'] = $path;
        }
        
        // Create the testimonial
        $testimonial = Testimonial::create($data);
        
        // Log activity if spatie activity-log package is installed
        if (method_exists(app(), 'activity')) {
            activity()
                ->causedBy(Auth::guard('admin')->user())
                ->performedOn($testimonial)
                ->withProperties(['user_name' => $testimonial->user_name])
                ->log('Created testimonial');
        }
        
        // For AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Testimonial created successfully.',
                'testimonial' => $testimonial
            ]);
        }
        
        // For traditional form submissions
        return redirect()->route('admin.testimonials.index')
            ->with('success', 'Testimonial created successfully.');
    }

    /**
     * Display the specified testimonial.
     */
    public function show(Testimonial $testimonial)
    {
        // Add image URL for frontend display
        if ($testimonial->image) {
            $testimonial->image_url = Storage::url($testimonial->image);
        }
        
        return response()->json([
            'success' => true,
            'testimonial' => $testimonial
        ]);
    }

    /**
     * Show the form for editing the specified testimonial.
     */
    public function edit(Testimonial $testimonial)
    {
        // For AJAX requests
        if (request()->ajax() || request()->wantsJson()) {
            // Add image URL for frontend display
            if ($testimonial->image) {
                $testimonial->image_url = Storage::url($testimonial->image);
            }
            
            return response()->json([
                'success' => true,
                'testimonial' => $testimonial
            ]);
        }
        
        // For traditional form submissions
        return view('admin.testimonials.edit', compact('testimonial'));
    }

    /**
     * Update the specified testimonial in storage.
     */
    public function update(Request $request, Testimonial $testimonial)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'user_name' => 'required|string|max:255',
            'user_title' => 'nullable|string|max:100',
            'user_email' => 'nullable|email|max:255',
            'content' => 'required|string|max:1000',
            'rating' => 'required|integer|min:1|max:5',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'is_approved' => 'required|in:true,false',
            'is_featured' => 'required|in:true,false',
            'order' => 'nullable|integer|min:0',
        ]);
        
        if ($validator->fails()) {
            // For AJAX requests
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            
            // For traditional form submissions
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->except(['image', '_token', '_method']);
        
        // Convert string boolean values to actual booleans
        $data['is_approved'] = $request->is_approved === 'true' ? true : false;
        $data['is_featured'] = $request->is_featured === 'true' ? true : false;
        
        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($testimonial->image) {
                Storage::disk('public')->delete($testimonial->image);
            }
            
            $path = $request->file('image')->store('testimonials', 'public');
            $data['image'] = $path;
        }
        
        // Update the testimonial
        $testimonial->update($data);
        
        // Log activity if spatie activity-log package is installed
        if (method_exists(app(), 'activity')) {
            activity()
                ->causedBy(Auth::guard('admin')->user())
                ->performedOn($testimonial)
                ->withProperties(['user_name' => $testimonial->user_name])
                ->log('Updated testimonial');
        }
        
        // For AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Testimonial updated successfully.',
                'testimonial' => $testimonial
            ]);
        }
        
        // For traditional form submissions
        return redirect()->route('admin.testimonials.index')
            ->with('success', 'Testimonial updated successfully.');
    }

    /**
     * Remove the specified testimonial from storage.
     */
    public function destroy(Testimonial $testimonial)
    {
        // Store data for activity log
        $testimonialData = [
            'id' => $testimonial->id,
            'user_name' => $testimonial->user_name
        ];
        
        // Delete image if exists
        if ($testimonial->image) {
            Storage::disk('public')->delete($testimonial->image);
        }
        
        // Delete the testimonial
        $testimonial->delete();
        
        // Log activity if spatie activity-log package is installed
        if (method_exists(app(), 'activity')) {
            activity()
                ->causedBy(Auth::guard('admin')->user())
                ->withProperties($testimonialData)
                ->log('Deleted testimonial');
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Testimonial deleted successfully.'
        ]);
    }
    
    /**
     * Toggle the featured status of a testimonial.
     */
    public function toggleFeatured(Testimonial $testimonial)
    {
        $testimonial->is_featured = !$testimonial->is_featured;
        $testimonial->save();
        
        return response()->json([
            'success' => true,
            'message' => $testimonial->is_featured ? 'Testimonial set as featured.' : 'Testimonial removed from featured.',
            'is_featured' => $testimonial->is_featured
        ]);
    }
    
    /**
     * Toggle the approval status of a testimonial.
     */
    public function toggleApproval(Testimonial $testimonial)
    {
        $testimonial->is_approved = !$testimonial->is_approved;
        $testimonial->save();
        
        return response()->json([
            'success' => true,
            'message' => $testimonial->is_approved ? 'Testimonial approved successfully.' : 'Testimonial unapproved successfully.',
            'is_approved' => $testimonial->is_approved
        ]);
    }
    
    /**
     * Update the order of testimonials.
     */
    public function updateOrder(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:testimonials,id',
            'items.*.order' => 'required|integer|min:0',
        ]);
        
        foreach ($request->items as $item) {
            Testimonial::where('id', $item['id'])->update(['order' => $item['order']]);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Testimonial order updated successfully.'
        ]);
    }
}