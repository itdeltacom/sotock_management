<?php

namespace App\Http\Controllers\Front;

use App\Models\Testimonial;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TestimonialFrontController extends Controller
{
    /**
     * Display the testimonials.
     */
    public function index()
    {
        $testimonials = Testimonial::where('is_approved', true)
            ->orderBy('is_featured', 'desc')
            ->orderBy('order', 'asc')
            ->orderBy('created_at', 'desc')
            ->paginate(12);
            
        return view('site.testimonials', compact('testimonials'));
    }
    
    /**
     * Get the latest testimonials for the homepage.
     */
    public function getLatestTestimonials($limit = 6)
    {
        $latestTestimonials = Testimonial::where('is_approved', true)
            ->orderBy('is_featured', 'desc')
            ->orderBy('order', 'asc')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
            
        return $latestTestimonials;
    }
    
    /**
     * Store a new testimonial submission from a customer.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'rating' => 'required|integer|min:1|max:5',
            'content' => 'required|string|max:1000',
            'g-recaptcha-response' => 'required|captcha'
        ]);
        
        $testimonial = new Testimonial();
        $testimonial->user_name = $request->name;
        $testimonial->user_email = $request->email;
        $testimonial->rating = $request->rating;
        $testimonial->content = $request->content;
        $testimonial->is_approved = false; // Require admin approval
        $testimonial->save();
        
        return redirect()->back()->with('success', 'Thank you for your testimonial! It will be reviewed by our team before being published.');
    }
}