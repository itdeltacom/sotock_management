<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Car;
use App\Models\Contract;
use App\Models\User;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * Handle the search request
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = $request->input('query');
        
        if (empty($query)) {
            return redirect()->back()->with('error', 'Please enter a search term');
        }
        
        // Search in cars
        $cars = Car::where('registration_number', 'LIKE', "%{$query}%")
            ->orWhere('model', 'LIKE', "%{$query}%")
            ->orWhereHas('brand', function($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%");
            })
            ->limit(10)
            ->get();
            
        // Search in clients
        $clients = User::where('name', 'LIKE', "%{$query}%")
            ->orWhere('email', 'LIKE', "%{$query}%")
            ->orWhere('phone', 'LIKE', "%{$query}%")
            ->orWhere('id_number', 'LIKE', "%{$query}%")
            ->limit(10)
            ->get();
            
        // Search in bookings
        $bookings = Booking::where('booking_number', 'LIKE', "%{$query}%")
            ->orWhereHas('customer', function($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                  ->orWhere('email', 'LIKE', "%{$query}%");
            })
            ->orWhereHas('car', function($q) use ($query) {
                $q->where('registration_number', 'LIKE', "%{$query}%");
            })
            ->limit(10)
            ->get();
            
        // Search in contracts
        $contracts = Contract::where('contract_number', 'LIKE', "%{$query}%")
            ->orWhereHas('customer', function($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                  ->orWhere('email', 'LIKE', "%{$query}%");
            })
            ->limit(10)
            ->get();

        // Add search stats to activity log
        activity('search')
            ->causedBy(auth()->guard('admin')->user())
            ->withProperties([
                'query' => $query,
                'results_count' => [
                    'cars' => $cars->count(),
                    'clients' => $clients->count(),
                    'bookings' => $bookings->count(),
                    'contracts' => $contracts->count(),
                ]
            ])
            ->log('Admin searched for "' . $query . '"');
            
        return view('admin.search.results', compact(
            'query', 
            'cars', 
            'clients', 
            'bookings', 
            'contracts'
        ));
    }
    
    /**
     * Get quick search results via AJAX
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function quickSearch(Request $request)
    {
        $query = $request->input('query');
        
        if (empty($query)) {
            return response()->json([]);
        }
        
        $results = [];
        
        // Search cars (limited to 5)
        $cars = Car::where('registration_number', 'LIKE', "%{$query}%")
            ->orWhere('model', 'LIKE', "%{$query}%")
            ->limit(5)
            ->get();
            
        foreach ($cars as $car) {
            $results[] = [
                'type' => 'car',
                'icon' => 'ni ni-bus-front-12',
                'id' => $car->id,
                'title' => $car->brand->name . ' ' . $car->model,
                'subtitle' => $car->registration_number,
                'url' => route('admin.cars.show', $car->id)
            ];
        }
        
        // Search clients (limited to 5)
        $clients = User::where('name', 'LIKE', "%{$query}%")
            ->orWhere('email', 'LIKE', "%{$query}%")
            ->orWhere('phone', 'LIKE', "%{$query}%")
            ->limit(5)
            ->get();
            
        foreach ($clients as $client) {
            $results[] = [
                'type' => 'client',
                'icon' => 'ni ni-single-02',
                'id' => $client->id,
                'title' => $client->name,
                'subtitle' => $client->email,
                'url' => route('admin.clients.show', $client->id)
            ];
        }
        
        // Search bookings (limited to 5)
        $bookings = Booking::where('booking_number', 'LIKE', "%{$query}%")
            ->limit(5)
            ->get();
            
        foreach ($bookings as $booking) {
            $results[] = [
                'type' => 'booking',
                'icon' => 'ni ni-calendar-grid-58',
                'id' => $booking->id,
                'title' => 'Booking #' . $booking->booking_number,
                'subtitle' => $booking->customer->name,
                'url' => route('admin.bookings.show', $booking->id)
            ];
        }
        
        return response()->json($results);
    }
}