<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Car;
use App\Models\Category;
use App\Models\BlogPost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    /**
     * Display the home page with dynamic content.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Get featured categories (active only)
        $categories = Category::where('is_active', true)
            ->orderBy('name')
            ->take(4)
            ->get();
            
        // Get featured cars (available only with images)
        $featuredCars = Car::with(['brand', 'category'])
            ->where('is_available', true)
            ->whereNotNull('main_image')
            ->orderBy('rating', 'desc')
            ->take(4)
            ->get();
            
        // Get car types by transmission
        $transmissionTypes = Car::select('transmission')
            ->where('is_available', true)
            ->distinct()
            ->get()
            ->pluck('transmission');
            
        // Get available brands with at least one car
        $brands = Brand::where('is_active', true)
            ->whereHas('cars', function ($query) {
                $query->where('is_available', true);
            })
            ->orderBy('name')
            ->get();
        
        // Hardcoded stats for now
        $stats = [
            'happy_clients' => 829,
            'cars_count' => 56,
            'car_centers' => 12,
            'total_kilometers' => 589
        ];
        
        // Dummy reviews for display
        $latestReviews = collect([
            (object)[
                'user_name' => 'John Doe',
                'rating' => 4,
                'content' => 'Lorem ipsum dolor sit amet consectetur adipisicing elit. Quam soluta neque ab repudiandae reprehenderit ipsum eos cumque esse repellendus impedit.'
            ],
            (object)[
                'user_name' => 'Jane Smith',
                'rating' => 5,
                'content' => 'Great service and amazing cars. I would definitely rent again from Cental Car Rental!'
            ],
            (object)[
                'user_name' => 'Michael Johnson',
                'rating' => 4,
                'content' => 'The rental process was smooth and the car was in perfect condition. Highly recommended!'
            ]
        ]);
        
        // Dummy blog posts for display until BlogPost model is ready
        try {
            $latestPosts = BlogPost::published()
                ->recent()
                ->take(3)
                ->get();
                
        } catch (\Exception $e) {
            // Create dummy blog posts for display
            $latestPosts = collect([
                (object)[
                    'title' => 'Rental Cars how to check driving fines?',
                    'slug' => 'rental-cars-driving-fines',
                    'excerpt' => 'Lorem, ipsum dolor sit amet consectetur adipisicing elit. Eius libero soluta impedit eligendi? Quibusdam, laudantium.',
                    'published_at' => now()->subDays(2),
                    'author' => 'Martin.C',
                    'comments_count' => 6
                ],
                (object)[
                    'title' => 'Rental cost of sport and other cars',
                    'slug' => 'rental-cost-sport-cars',
                    'excerpt' => 'Lorem, ipsum dolor sit amet consectetur adipisicing elit. Eius libero soluta impedit eligendi? Quibusdam, laudantium.',
                    'published_at' => now()->subDays(7),
                    'author' => 'Martin.C',
                    'comments_count' => 6
                ],
                (object)[
                    'title' => 'Document required for car rental',
                    'slug' => 'documents-required-car-rental',
                    'excerpt' => 'Lorem, ipsum dolor sit amet consectetur adipisicing elit. Eius libero soluta impedit eligendi? Quibusdam, laudantium.',
                    'published_at' => now()->subDays(5),
                    'author' => 'Martin.C',
                    'comments_count' => 6
                ]
            ]);
        }
            
        // Return view with data
        return view('site.index', compact(
            'categories',
            'featuredCars',
            'transmissionTypes',
            'brands',
            'stats',
            'latestReviews',
            'latestPosts'
        ));
    }
    
    /**
     * Get search form data for homepage search form
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function getSearchFormData(Request $request)
    {
        // Get all car types for search dropdown
        $carTypes = Car::select('id', 'name')
            ->where('is_available', true)
            ->orderBy('name')
            ->get();
            
        // Hardcoded locations for now
        $pickupLocations = collect([
            (object)[
                'id' => 1,
                'name' => 'New York',
                'address' => '123 Main St, New York, NY 10001'
            ],
            (object)[
                'id' => 2,
                'name' => 'Los Angeles',
                'address' => '456 Ocean Ave, Los Angeles, CA 90001'
            ],
            (object)[
                'id' => 3,
                'name' => 'Chicago',
                'address' => '789 Lake St, Chicago, IL 60601'
            ]
        ]);
            
        return response()->json([
            'carTypes' => $carTypes,
            'pickupLocations' => $pickupLocations
        ]);
    }
    
    /**
     * Process homepage search form
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function processSearch(Request $request)
    {
        $validated = $request->validate([
            'car_type' => 'nullable|exists:cars,id',
            'pickup_location' => 'required|string',
            'dropoff_location' => 'nullable|string',
            'pickup_date' => 'required|date',
            'pickup_time' => 'required|string',
            'dropoff_date' => 'required|date|after_or_equal:pickup_date',
            'dropoff_time' => 'required|string',
        ]);
        
        // Redirect to search results with query parameters
        return redirect()->route('cars.search', [
            'car_type' => $request->car_type,
            'pickup_location' => $request->pickup_location,
            'dropoff_location' => $request->dropoff_location ?: $request->pickup_location,
            'pickup_date' => $request->pickup_date,
            'pickup_time' => $request->pickup_time,
            'dropoff_date' => $request->dropoff_date,
            'dropoff_time' => $request->dropoff_time,
        ]);
    }
    
    /**
     * Display the about page.
     *
     * @return \Illuminate\Http\Response
     */
    public function about()
    {
        // Hardcoded stats
        $stats = [
            'happy_clients' => 829,
            'cars_count' => 56, 
            'car_centers' => 12,
            'total_kilometers' => 589
        ];
        
        // Dummy team members
        $teamMembers = collect([
            (object)[
                'name' => 'MARTIN DOE',
                'position' => 'CEO & Founder',
                'image' => 'site/img/team-1.jpg',
                'facebook' => '#',
                'twitter' => '#',
                'instagram' => '#',
                'linkedin' => '#'
            ],
            (object)[
                'name' => 'JOHN SMITH',
                'position' => 'Operations Manager',
                'image' => 'site/img/team-2.jpg',
                'facebook' => '#',
                'twitter' => '#',
                'instagram' => '#',
                'linkedin' => '#'
            ],
            (object)[
                'name' => 'SARAH JOHNSON',
                'position' => 'Customer Service',
                'image' => 'site/img/team-3.jpg',
                'facebook' => '#',
                'twitter' => '#',
                'instagram' => '#',
                'linkedin' => '#'
            ],
            (object)[
                'name' => 'MICHAEL BROWN',
                'position' => 'Fleet Manager',
                'image' => 'site/img/team-4.jpg',
                'facebook' => '#',
                'twitter' => '#',
                'instagram' => '#',
                'linkedin' => '#'
            ]
        ]);
        return view('site.about-us', [
            'stats' => $stats, 
            'teamMembers' => $teamMembers,
            'pageTitle' => 'About Us',
            'parentPage' => [
                'name' => 'Pages',
                'url' => '#' 
            ],
            'currentPage' => 'About'
        ]);         
        
    }
    
    /**
     * Display the contact page.
     *
     * @return \Illuminate\Http\Response
     */
    public function contact()
    {
        // Dummy office locations
        $locations = collect([
            (object)[
                'name' => 'New York Office',
                'address' => '123 Main Street, New York, NY 10001',
                'phone' => '+1 (212) 555-1234',
                'email' => 'newyork@cental.com',
                'hours' => 'Mon-Fri: 8:00 AM - 6:00 PM, Sat: 9:00 AM - 4:00 PM',
                'latitude' => 40.7128,
                'longitude' => -74.0060
            ],
            (object)[
                'name' => 'Los Angeles Office',
                'address' => '456 Palm Avenue, Los Angeles, CA 90001',
                'phone' => '+1 (310) 555-5678',
                'email' => 'losangeles@cental.com',
                'hours' => 'Mon-Fri: 8:00 AM - 6:00 PM, Sat: 9:00 AM - 4:00 PM',
                'latitude' => 34.0522,
                'longitude' => -118.2437
            ],
            (object)[
                'name' => 'Chicago Office',
                'address' => '789 Lake Street, Chicago, IL 60601',
                'phone' => '+1 (312) 555-9012',
                'email' => 'chicago@cental.com',
                'hours' => 'Mon-Fri: 8:00 AM - 6:00 PM, Sat: 9:00 AM - 4:00 PM',
                'latitude' => 41.8781,
                'longitude' => -87.6298
            ]
        ]);
            
        return view('site.contact-us', [
            'locations' => $locations,
            'pageTitle' => 'Contact Us',
            'parentPage' => [
                'name' => 'Pages',
                'url' => '#'
            ],
            'currentPage' => 'Contact'
        ]);
        }
    
    /**
     * Process contact form submission.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function submitContactForm(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);
        
        // Log the contact form submission instead of trying to save to database
        \Log::info('Contact form submission: ' . json_encode($validated));
        
        // You could also send an email notification here
        
        return redirect()->back()->with('success', 'Your message has been sent successfully. We will get back to you soon!');
    }
}