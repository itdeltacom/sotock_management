<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Car;
use App\Models\Category;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CarListingController extends Controller
{
    /**
     * Number of cars to load per page
     */
    const PER_PAGE = 3;

    /**
     * Display the main cars listing page
     *
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        // Get all available categories & brands for filters
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        $brands = Brand::where('is_active', true)->orderBy('name')->get();
        
        // Get transmission and fuel type options
        $transmissionTypes = Car::select('transmission')
            ->where('is_available', true)
            ->distinct()
            ->pluck('transmission');
            
        $fuelTypes = Car::select('fuel_type')
            ->where('is_available', true)
            ->distinct()
            ->pluck('fuel_type');
            
        // Get price range
        $priceRange = [
            'min' => Car::where('is_available', true)->min('price_per_day') ?: 50,
            'max' => Car::where('is_available', true)->max('price_per_day') ?: 300
        ];
        
        // For AJAX requests, load and return the cars data
        if ($request->ajax()) {
            $cars = $this->getFilteredCars($request);
            
            return response()->json([
                'html' => view('site.cars.partials.car-items', compact('cars'))->render(),
                'hasMorePages' => $cars->hasMorePages(),
                'totalCars' => $cars->total(),
                'currentPage' => $cars->currentPage(),
                'lastPage' => $cars->lastPage()
            ]);
        }
        
        $perPage = self::PER_PAGE;
        return view('site.cars.index', array_merge(compact(
            'categories',
            'brands',
            'transmissionTypes',
            'fuelTypes',
            'priceRange',
            'perPage'
        ), [
            'pageTitle' => 'Car Listing',
            'currentPage' => 'Listing'
        ]))->with('perPage', self::PER_PAGE);
    }
    
    /**
     * Load cars via AJAX
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function loadCars(Request $request)
    {
        $cars = $this->getFilteredCars($request);
        
        return response()->json([
            'html' => view('site.cars.partials.car-items', compact('cars'))->render(),
            'hasMorePages' => $cars->hasMorePages(),
            'totalCars' => $cars->total(),
            'currentPage' => $cars->currentPage(),
            'lastPage' => $cars->lastPage()
        ]);
    }
    

    /**
     * Display the car detail page
     *
     * @param string $slug
     * @return \Illuminate\View\View
     */
    public function show($slug)
    {
        // Find the car by slug or return 404
        $car = Car::with(['brand', 'category', 'images', 'reviews.user'])
            ->where('slug', $slug)
            ->where('is_available', true)
            ->firstOrFail();
            
        // Get related cars (same category or brand, but not the current car)
        $relatedCars = Car::with(['brand', 'category'])
            ->where('is_available', true)
            ->where(function($query) use ($car) {
                $query->where('category_id', $car->category_id)
                      ->orWhere('brand_id', $car->brand_id);
            })
            ->where('id', '!=', $car->id)
            ->orderBy('rating', 'desc')
            ->limit(3)
            ->get();
            
            return view('site.cars.show', array_merge(compact('car', 'relatedCars'), [
                'pageTitle' => $car->name,
                'currentPage' => $car->name,
                'parentPage' => [
                    'name' => 'Cars',
                    'url' => route('cars.index')
                ]
            ]));
            }
    
    /**
     * Search for cars based on query parameters
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function search(Request $request)
    {
        // Get all available categories & brands for filters
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        $brands = Brand::where('is_active', true)->orderBy('name')->get();
        
        // Get transmission and fuel type options
        $transmissionTypes = Car::select('transmission')
            ->where('is_available', true)
            ->distinct()
            ->pluck('transmission');
            
        $fuelTypes = Car::select('fuel_type')
            ->where('is_available', true)
            ->distinct()
            ->pluck('fuel_type');
            
        // Get price range
        $priceRange = [
            'min' => Car::where('is_available', true)->min('price_per_day') ?: 50,
            'max' => Car::where('is_available', true)->max('price_per_day') ?: 300
        ];
        
        // Create base query
        $query = Car::with(['brand', 'category'])
            ->where('is_available', true);
        
        // Apply search filters
        if ($request->filled('keyword')) {
            $keyword = $request->input('keyword');
            $query->where(function($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                  ->orWhere('description', 'like', "%{$keyword}%")
                  ->orWhereHas('brand', function($q) use ($keyword) {
                      $q->where('name', 'like', "%{$keyword}%");
                  })
                  ->orWhereHas('category', function($q) use ($keyword) {
                      $q->where('name', 'like', "%{$keyword}%");
                  });
            });
        }
        
        // Apply other filters (similar to CarListingController::getFilteredCars)
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }
        
        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->input('brand_id'));
        }
        
        if ($request->filled('price_min')) {
            $query->where('price_per_day', '>=', $request->input('price_min'));
        }
        
        if ($request->filled('price_max')) {
            $query->where('price_per_day', '<=', $request->input('price_max'));
        }
        
        if ($request->filled('transmission')) {
            $query->where('transmission', $request->input('transmission'));
        }
        
        if ($request->filled('fuel_type')) {
            $query->where('fuel_type', $request->input('fuel_type'));
        }
        
        // Apply sorting
        $sortField = $request->input('sort_by', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');
        
        $allowedSortFields = ['price_per_day', 'created_at', 'rating'];
        
        if (in_array($sortField, $allowedSortFields)) {
            $query->orderBy($sortField, $sortDirection);
        } else {
            $query->orderBy('created_at', 'desc');
        }
        
        // Paginate results
        $cars = $query->paginate(9);
        
        return view('site.cars.search', array_merge(compact(
            'cars',
            'categories',
            'brands',
            'transmissionTypes',
            'fuelTypes',
            'priceRange'
        ), [
            'pageTitle' => 'Search Results',
            'currentPage' => 'Search',
            'parentPage' => [
                'name' => 'Cars',
                'url' => route('cars.index')
            ]
        ]));
    }
    /**
     * Filter cars based on request parameters
     *
     * @param Request $request
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    private function getFilteredCars(Request $request)
    {
        // Create base query
        $query = Car::with(['brand', 'category'])
            ->where('is_available', true);
        
        // Apply filters
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }
        
        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->input('brand_id'));
        }
        
        if ($request->filled('price_min')) {
            $query->where('price_per_day', '>=', $request->input('price_min'));
        }
        
        if ($request->filled('price_max')) {
            $query->where('price_per_day', '<=', $request->input('price_max'));
        }
        
        if ($request->filled('transmission')) {
            $query->where('transmission', $request->input('transmission'));
        }
        
        if ($request->filled('fuel_type')) {
            $query->where('fuel_type', $request->input('fuel_type'));
        }
        
        // Apply sorting
        $sortField = $request->input('sort_by', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');
        
        $allowedSortFields = ['price_per_day', 'created_at', 'rating'];
        
        if (in_array($sortField, $allowedSortFields)) {
            $query->orderBy($sortField, $sortDirection);
        } else {
            $query->orderBy('created_at', 'desc');
        }
        
        // Paginate results
        return $query->paginate(self::PER_PAGE);
    }
}