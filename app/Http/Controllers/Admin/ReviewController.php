<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Car;
use App\Models\Review;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class ReviewController extends Controller
{
    /**
     * Display a listing of the reviews.
     */
    public function index()
    {
        $cars = Car::orderBy('name')->get();
        return view('admin.reviews.index', compact('cars'));
    }

    /**
     * Get reviews data for DataTables.
     */
    public function data(Request $request)
    {
        $query = Review::with(['car', 'user']);
        
        // Apply filters if provided
        if ($request->has('car_id') && !empty($request->car_id)) {
            $query->where('car_id', $request->car_id);
        }
        
        if ($request->has('approval_status') && $request->approval_status !== '') {
            $query->where('is_approved', $request->approval_status == '1');
        }
        
        if ($request->has('rating') && !empty($request->rating)) {
            $query->where('rating', $request->rating);
        }
        
        $reviews = $query->get();
        
        return DataTables::of($reviews)
            ->addColumn('action', function (Review $review) {
                $actions = '';
                
                // Only show edit button if user has permission
                if (Auth::guard('admin')->user()->can('edit reviews')) {
                    $actions .= '<button type="button" class="btn btn-sm btn-primary me-1 btn-edit" data-id="'.$review->id.'" title="Edit">
                        <i class="fas fa-edit"></i>
                    </button> ';
                }
                
                // Toggle approval button if user has permission
                if (Auth::guard('admin')->user()->can('edit reviews')) {
                    $approvalIcon = $review->is_approved ? 
                        '<i class="fas fa-check-circle text-success"></i>' : 
                        '<i class="fas fa-times-circle text-danger"></i>';
                    $approvalTitle = $review->is_approved ? 'Disapprove' : 'Approve';
                    
                    $actions .= '<button type="button" class="btn btn-sm btn-outline-info me-1 btn-toggle-approval" data-id="'.$review->id.'" title="'.$approvalTitle.'">
                        '.$approvalIcon.'
                    </button> ';
                }
                
                // Only show delete button if user has permission
                if (Auth::guard('admin')->user()->can('delete reviews')) {
                    $actions .= '<button type="button" class="btn btn-sm btn-danger btn-delete" data-id="'.$review->id.'" title="Delete">
                        <i class="fas fa-trash"></i>
                    </button>';
                }
                
                return $actions;
            })
            ->addColumn('car_name', function (Review $review) {
                return $review->car ? $review->car->name : '-';
            })
            ->addColumn('reviewer', function (Review $review) {
                if ($review->user) {
                    return $review->user->name;
                } else {
                    return $review->reviewer_name . ' (Guest)';
                }
            })
            ->addColumn('approval_status', function (Review $review) {
                return $review->is_approved 
                    ? '<span class="badge bg-success">Approved</span>' 
                    : '<span class="badge bg-warning">Pending</span>';
            })
            ->addColumn('star_rating', function (Review $review) {
                $stars = '';
                for ($i = 1; $i <= 5; $i++) {
                    if ($i <= $review->rating) {
                        $stars .= '<i class="fas fa-star text-warning"></i>';
                    } else if ($i - 0.5 <= $review->rating) {
                        $stars .= '<i class="fas fa-star-half-alt text-warning"></i>';
                    } else {
                        $stars .= '<i class="far fa-star text-warning"></i>';
                    }
                }
                return $stars . ' (' . $review->rating . ')';
            })
            ->addColumn('created_date', function (Review $review) {
                return $review->created_at->format('M d, Y H:i');
            })
            ->rawColumns(['action', 'approval_status', 'star_rating'])
            ->make(true);
    }

    /**
     * Store a newly created review in storage.
     */
    public function store(Request $request)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'car_id' => 'required|exists:cars,id',
            'rating' => 'required|numeric|min:1|max:5',
            'comment' => 'required|string|max:1000',
            'is_approved' => 'boolean',
            'user_id' => 'nullable|exists:users,id',
            'reviewer_name' => 'required_without:user_id|string|max:255',
            'reviewer_email' => 'required_without:user_id|email|max:255',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Create the review
        $review = Review::create([
            'car_id' => $request->car_id,
            'user_id' => $request->user_id,
            'rating' => $request->rating,
            'comment' => $request->comment,
            'is_approved' => $request->has('is_approved') ? $request->is_approved : false,
            'reviewer_name' => $request->reviewer_name,
            'reviewer_email' => $request->reviewer_email,
        ]);
        
        // Update car rating
        $this->updateCarRating($request->car_id);
        
        // Log activity if spatie activity-log package is installed
        if (method_exists(app(), 'activity')) {
            activity()
                ->causedBy(Auth::guard('admin')->user())
                ->performedOn($review)
                ->withProperties(['car_id' => $review->car_id])
                ->log('Created review');
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Review created successfully.',
            'review' => $review
        ]);
    }

    /**
     * Show the form for editing the specified review.
     */
    public function edit(Review $review)
    {
        $review->load(['car', 'user']);
        
        return response()->json([
            'success' => true,
            'review' => $review
        ]);
    }

    /**
     * Update the specified review in storage.
     */
    public function update(Request $request, Review $review)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'rating' => 'required|numeric|min:1|max:5',
            'comment' => 'required|string|max:1000',
            'is_approved' => 'boolean',
            'reviewer_name' => 'nullable|string|max:255',
            'reviewer_email' => 'nullable|email|max:255',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Store old rating for comparison
        $oldRating = $review->rating;
        
        // Update the review
        $review->rating = $request->rating;
        $review->comment = $request->comment;
        $review->is_approved = $request->has('is_approved') ? $request->is_approved : false;
        
        // Only update reviewer info if no user is associated
        if (!$review->user_id) {
            $review->reviewer_name = $request->reviewer_name;
            $review->reviewer_email = $request->reviewer_email;
        }
        
        $review->save();
        
        // Update car rating if the rating changed
        if ($oldRating != $request->rating) {
            $this->updateCarRating($review->car_id);
        }
        
        // Log activity if spatie activity-log package is installed
        if (method_exists(app(), 'activity')) {
            activity()
                ->causedBy(Auth::guard('admin')->user())
                ->performedOn($review)
                ->withProperties(['car_id' => $review->car_id])
                ->log('Updated review');
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Review updated successfully.',
            'review' => $review
        ]);
    }

    /**
     * Remove the specified review from storage.
     */
    public function destroy(Review $review)
    {
        $carId = $review->car_id;
        
        // Delete the review
        $review->delete();
        
        // Update car rating
        $this->updateCarRating($carId);
        
        // Log activity if spatie activity-log package is installed
        if (method_exists(app(), 'activity')) {
            activity()
                ->causedBy(Auth::guard('admin')->user())
                ->withProperties(['car_id' => $carId])
                ->log('Deleted review');
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Review deleted successfully.'
        ]);
    }
    
    /**
     * Toggle the approval status of a review.
     */
    public function toggleApproval(Review $review)
    {
        $review->is_approved = !$review->is_approved;
        $review->save();
        
        // Update car rating (only include approved reviews in calculation)
        $this->updateCarRating($review->car_id);
        
        return response()->json([
            'success' => true,
            'message' => $review->is_approved ? 'Review approved successfully.' : 'Review disapproved successfully.',
            'is_approved' => $review->is_approved
        ]);
    }
    
    /**
     * Get users for select dropdown.
     */
    public function getUsers(Request $request)
    {
        $search = $request->search;
        
        $users = User::where(function($query) use ($search) {
            if (!empty($search)) {
                $query->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%");
            }
        })
        ->orderBy('name')
        ->limit(10)
        ->get(['id', 'name', 'email']);
        
        return response()->json($users);
    }

    /**
     * Update the average rating for a car.
     */
    private function updateCarRating($carId)
    {
        $car = Car::findOrFail($carId);
        
        // Get approved reviews
        $approvedReviews = $car->reviews()->where('is_approved', true)->get();
        
        // Count approved reviews
        $reviewCount = $approvedReviews->count();
        
        // Calculate average rating
        if ($reviewCount > 0) {
            $averageRating = $approvedReviews->avg('rating');
            $car->rating = round($averageRating, 1); 
        } else {
            $car->rating = 0;
        }
        
        $car->review_count = $reviewCount;
        $car->save();
    }
}