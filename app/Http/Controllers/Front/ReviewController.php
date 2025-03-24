<?php

namespace App\Http\Controllers\Front;

use App\Models\Car;
use App\Models\Review;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ReviewController extends Controller
{
    /**
     * Store a newly created review in storage.
     */
    public function store(Request $request)
    {
        // Check if the car exists
        $car = Car::findOrFail($request->car_id);
        
        // Set up validation rules
        $rules = [
            'car_id' => 'required|exists:cars,id',
            'rating' => 'required|numeric|min:1|max:5',
            'comment' => 'required|string|max:1000',
        ];
        
        // If user is logged in, we don't need name and email
        if (!Auth::check()) {
            $rules['reviewer_name'] = 'required|string|max:255';
            $rules['reviewer_email'] = 'required|email|max:255';
        }
        
        // Validate input
        $validator = Validator::make($request->all(), $rules);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Create the review
        $review = new Review();
        $review->car_id = $request->car_id;
        $review->rating = $request->rating;
        $review->comment = $request->comment;
        
        // Set user_id if authenticated
        if (Auth::check()) {
            $review->user_id = Auth::id();
        } else {
            $review->reviewer_name = $request->reviewer_name;
            $review->reviewer_email = $request->reviewer_email;
        }
        
        // Set approval status based on site policy
        // You can adjust this as needed - auto-approve or require moderation
        $review->is_approved = false; // Default to requiring approval
        
        $review->save();
        
        // Update car rating (if auto-approved)
        if ($review->is_approved) {
            $this->updateCarRating($car->id);
        }
        
        return redirect()->back()->with('success', 'Thank you for your review! It has been submitted and is awaiting approval.');
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