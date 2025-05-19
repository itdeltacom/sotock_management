<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Booking;
use App\Models\Contract;
use App\Models\CarDocuments;
use App\Models\CarMaintenance;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Request;

class NotificationController extends Controller
{
    public function getNotifications()
    {
        // Fetch car documents expiring within 30 days
        $expiringDocuments = CarDocuments::with('car')
            ->where(function ($query) {
                $thirtyDaysFromNow = now()->addDays(30);
                $query->whereDate('carte_grise_expiry_date', '<=', $thirtyDaysFromNow)
                    ->orWhereDate('assurance_expiry_date', '<=', $thirtyDaysFromNow)
                    ->orWhereDate('visite_technique_expiry_date', '<=', $thirtyDaysFromNow)
                    ->orWhereDate('vignette_expiry_date', '<=', $thirtyDaysFromNow);
            })
            ->get();
            
        // Fetch overdue contracts
        $overdueContracts = Contract::with('client', 'car')
            ->overdue()
            ->get();
            
        // Fetch contracts ending soon (within 3 days)
        $endingSoonContracts = Contract::with('client', 'car')
            ->endingSoon()
            ->get();
            
        // Fetch maintenance due soon
        $maintenanceDue = CarMaintenance::with('car')
            ->where(function($query) {
                $fifteenDaysFromNow = now()->addDays(15);
                $query->whereDate('next_due_date', '<=', $fifteenDaysFromNow)
                    ->orWhereRaw('next_due_mileage - (SELECT mileage FROM cars WHERE cars.id = car_maintenances.car_id) <= 500');
            })
            ->get();
            
        // Fetch new bookings (last 24 hours)
        $newBookings = Booking::with('car', 'user')
            ->where('status', 'pending')
            ->where('created_at', '>=', now()->subDay())
            ->get();
            
        // Prepare notifications array
        $notifications = [];
        
        // Add document notifications
        foreach ($expiringDocuments as $doc) {
            $expiringDocs = $doc->getExpiringDocuments();
            foreach ($expiringDocs as $expDoc) {
                $notifications[] = [
                    'id' => 'doc_' . $doc->id . '_' . $expDoc['document'],
                    'title' => 'Document Expiring',
                    'message' => 'Car ' . $doc->car->brand_name . ' ' . $doc->car->model . ' - ' . $expDoc['document'] . ' expires on ' . $expDoc['expiry_date']->format('d/m/Y'),
                    'link' => route('admin.cars.documents.show', $doc->car_id),
                    'icon' => 'fas fa-file-alt',
                    'color' => 'warning',
                    'created_at' => $expDoc['expiry_date']->subDays($expDoc['days_left']),
                    'type' => 'document'
                ];
            }
        }
        
        // Add overdue contract notifications
        foreach ($overdueContracts as $contract) {
            $notifications[] = [
                'id' => 'contract_overdue_' . $contract->id,
                'title' => 'Overdue Contract',
                'message' => 'Contract for ' . $contract->car->brand_name . ' ' . $contract->car->model . ' is overdue by ' . $contract->overdue_days . ' days',
                'link' => route('admin.contracts.show', $contract->id),
                'icon' => 'fas fa-exclamation-triangle',
                'color' => 'danger',
                'created_at' => $contract->end_date,
                'type' => 'contract'
            ];
        }
        
        // Add ending soon contract notifications
        foreach ($endingSoonContracts as $contract) {
            $daysLeft = now()->diffInDays($contract->end_date, false);
            if ($daysLeft >= 0) { // Only show non-overdue contracts
                $notifications[] = [
                    'id' => 'contract_ending_' . $contract->id,
                    'title' => 'Contract Ending Soon',
                    'message' => 'Contract for ' . $contract->car->brand_name . ' ' . $contract->car->model . ' ends in ' . $daysLeft . ' days',
                    'link' => route('admin.contracts.show', $contract->id),
                    'icon' => 'fas fa-calendar-times',
                    'color' => 'info',
                    'created_at' => now(),
                    'type' => 'contract'
                ];
            }
        }
        
        // Add maintenance notifications
        foreach ($maintenanceDue as $maintenance) {
            $notifications[] = [
                'id' => 'maintenance_' . $maintenance->id,
                'title' => 'Maintenance Due',
                'message' => ucfirst(str_replace('_', ' ', $maintenance->maintenance_type)) . ' due for ' . $maintenance->car->brand_name . ' ' . $maintenance->car->model,
                'link' => route('admin.cars.maintenance.index', $maintenance->car_id),
                'icon' => 'fas fa-tools',
                'color' => 'warning',
                'created_at' => now()->subHours(rand(1, 24)), // Random time in the last 24 hours
                'type' => 'maintenance'
            ];
        }
        
        // Add new booking notifications
        foreach ($newBookings as $booking) {
            $notifications[] = [
                'id' => 'booking_' . $booking->id,
                'title' => 'New Booking',
                'message' => 'New booking for ' . $booking->car->brand_name . ' ' . $booking->car->model . ' by ' . $booking->customer_name,
                'link' => route('admin.bookings.show', $booking->id),
                'icon' => 'fas fa-calendar-plus',
                'color' => 'primary',
                'created_at' => $booking->created_at,
                'type' => 'booking'
            ];
        }
        
        // Sort notifications by created_at date (newest first)
        usort($notifications, function($a, $b) {
            return $b['created_at']->timestamp - $a['created_at']->timestamp;
        });
        
        // Mark some as read for demonstration
        $unreadCount = count($notifications);
        
        return [
            'notifications' => $notifications,
            'unread_count' => $unreadCount
        ];
    }

 /**
     * Display a listing of the notifications.
     */
    public function index()
    {
        $notificationData = $this->getNotifications();
        $notifications = $notificationData['notifications'];
        
        return view('admin.notifications', compact('notifications'));
    }
    
    /**
     * Mark a notification as read.
     */
    public function markAsRead(Request $request)
    {
        $notificationId = $request->input('notification_id');
        
        // Here you would implement logic to mark a notification as read
        // For example, using Laravel's notification system
        
        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read'
        ]);
    }
    
    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead()
    {
        // Here you would implement logic to mark all notifications as read
        
        return redirect()->back()->with('success', 'All notifications marked as read');
    }
}