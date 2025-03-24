<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ClientDashboardController extends Controller
{
    /**
     * Display the client dashboard with overview stats
     */
    public function index()
    {
        $user = Auth::user();
        $activeBookings = Booking::where('user_id', $user->id)
            ->whereIn('status', ['confirmed', 'active'])
            ->count();
        
        $upcomingBookings = Booking::where('user_id', $user->id)
            ->where('pickup_date', '>', now())
            ->count();
            
        $completedBookings = Booking::where('user_id', $user->id)
            ->where('status', 'completed')
            ->count();
            
        // Get the most recent booking
        $latestBooking = Booking::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->first();
            
        return view('site.customer.dashboard', compact(
            'user', 
            'activeBookings', 
            'upcomingBookings', 
            'completedBookings',
            'latestBooking'
        ));
    }
    
    /**
     * Display the client profile page
     */
    public function profile()
    {
        $user = Auth::user();
        return view('site.customer.profile', compact('user'));
    }
    
    /**
     * Update client profile information
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);
        
        // Update basic information
        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->address = $request->address;
        
        // Handle photo upload
        if ($request->hasFile('photo')) {
            // Delete old photo if it exists
            if ($user->photo && Storage::exists('public/' . $user->photo)) {
                Storage::delete('public/' . $user->photo);
            }
            
            $photoPath = $request->file('photo')->store('uploads/users', 'public');
            $user->photo = $photoPath;
        }
        
        $user->save();
        
        return response()->json([
            'status' => 'success',
            'message' => 'Profil mis à jour avec succès!',
            'user' => $user
        ]);
    }
    
    /**
     * Update client password
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:8|confirmed',
        ]);
        
        $user = Auth::user();
        
        // Check if current password matches
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Le mot de passe actuel est incorrect.'
            ], 422);
        }
        
        $user->password = Hash::make($request->password);
        $user->save();
        
        return response()->json([
            'status' => 'success',
            'message' => 'Mot de passe mis à jour avec succès!'
        ]);
    }
    
    /**
     * Display all client bookings
     */
    public function bookings(Request $request)
    {
        $user = Auth::user();
        $query = Booking::where('user_id', $user->id);
        
        // Apply filters if they exist
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }
        
        if ($request->has('date_from') && $request->date_from) {
            $query->where('pickup_date', '>=', $request->date_from);
        }
        
        if ($request->has('date_to') && $request->date_to) {
            $query->where('dropoff_date', '<=', $request->date_to);
        }
        
        $bookings = $query->orderBy('created_at', 'desc')->paginate(10);
            
        return view('site.customer.bookings', compact('user', 'bookings'));
    }
    
    /**
     * Display a specific booking details
     */
    public function bookingDetails($id)
    {
        $user = Auth::user();
        $booking = Booking::where('user_id', $user->id)
            ->where('id', $id)
            ->firstOrFail();
            
        return view('site.customer.booking-details', compact('user', 'booking'));
    }
    
    /**
     * Cancel a booking
     */
    public function cancelBooking(Request $request, $id)
    {
        $user = Auth::user();
        $booking = Booking::where('user_id', $user->id)
            ->where('id', $id)
            ->firstOrFail();
            
        // Check if booking can be cancelled (business logic)
        $pickupDate = \Carbon\Carbon::parse($booking->pickup_date);
        if ($pickupDate->isPast()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Les réservations dont la date de prise en charge est passée ne peuvent pas être annulées.'
            ], 422);
        }
        
        // Update booking status
        $booking->status = 'cancelled';
        $booking->save();
        
        return response()->json([
            'status' => 'success',
            'message' => 'Réservation annulée avec succès!'
        ]);
    }
}