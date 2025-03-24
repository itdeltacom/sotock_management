<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ActivityController extends Controller
{
    /**
     * Display a listing of activities.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Check permission
        if (!auth()->guard('admin')->user()->can('view activities')) {
            abort(403, 'Unauthorized action.');
        }
        
        $query = Activity::query();

        // Filter by type
        if ($request->has('type') && $request->type != '') {
            $query->where('type', $request->type);
        }

        // Filter by date range
        if ($request->has('start_date') && $request->start_date != '') {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->has('end_date') && $request->end_date != '') {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // Filter by user
        if ($request->has('user_id') && $request->user_id != '') {
            $query->where('user_id', $request->user_id)
                  ->where('user_type', 'App\\Models\\Admin');
        }
        
        // Filter by IP address
        if ($request->has('ip_address') && $request->ip_address != '') {
            $query->where('ip_address', 'like', '%' . $request->ip_address . '%');
        }

        // Get activities with pagination
        $activities = $query->orderBy('created_at', 'desc')
                            ->paginate(15)
                            ->withQueryString();

        // Get unique activity types for filter
        $activityTypes = Activity::select('type')
                                 ->distinct()
                                 ->orderBy('type')
                                 ->pluck('type');

        // Get admin users for filter
        $adminUsers = Admin::select('id', 'name')
                           ->orderBy('name')
                           ->get();
                           
        // Get stats for dashboard cards
        $stats = [
            'total' => Activity::count(),
            'today' => Activity::whereDate('created_at', today())->count(),
            'thisWeek' => Activity::whereBetween('created_at', [now()->startOfWeek(), now()])->count(),
            'thisMonth' => Activity::whereMonth('created_at', now()->month)
                                   ->whereYear('created_at', now()->year)
                                   ->count(),
        ];
        
        // Get top browsers
        $topBrowsers = DB::table('activities')
            ->select(DB::raw('JSON_EXTRACT(properties, "$.browser") as browser'), DB::raw('count(*) as count'))
            ->whereNotNull('properties->browser')
            ->groupBy('browser')
            ->orderByDesc('count')
            ->limit(5)
            ->get();
        
        // Get top locations
        $topLocations = DB::table('activities')
            ->select(DB::raw('JSON_EXTRACT(properties, "$.location") as location'), DB::raw('count(*) as count'))
            ->whereNotNull('properties->location')
            ->groupBy('location')
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        return view('admin.activities.activities', compact(
            'activities',
            'activityTypes',
            'adminUsers',
            'stats',
            'topBrowsers',
            'topLocations'
        ));
    }

    /**
     * Display the specified activity.
     *
     * @param Activity $activity
     * @return \Illuminate\View\View
     */
    public function show(Activity $activity)
    {
        // Check permission
        if (!auth()->guard('admin')->user()->can('view activities')) {
            abort(403, 'Unauthorized action.');
        }
        
        return view('admin.activities.show', compact('activity'));
    }

    /**
     * Clear all activities.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function clearAll()
    {
        // Check permission
        if (!auth()->guard('admin')->user()->can('clear activities')) {
            abort(403, 'Unauthorized action.');
        }
        
        // Store count for message
        $count = Activity::count();
        
        Activity::truncate();

        // Log the action itself
        Activity::log(
            'admin',
            'Cleared Activity Log',
            "All activity logs ({$count} records) were cleared from the system",
            auth()->guard('admin')->user()
        );

        return redirect()->route('admin.activities.index')
                         ->with('success', "{$count} activity log records have been cleared successfully.");
    }

    /**
     * Remove the specified activity from storage.
     *
     * @param Activity $activity
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Activity $activity)
    {
        // Check permission
        if (!auth()->guard('admin')->user()->can('delete activities')) {
            abort(403, 'Unauthorized action.');
        }
        
        $activity->delete();

        // Log this action
        Activity::log(
            'delete',
            'Deleted Activity Log Record',
            "Activity log record (ID: {$activity->id}) was deleted",
            auth()->guard('admin')->user()
        );

        return redirect()->route('admin.activities.index')
                         ->with('success', 'Activity record deleted successfully.');
    }
    
    /**
     * Export activities to CSV.
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function export(Request $request)
    {
        // Check permission
        if (!auth()->guard('admin')->user()->can('view activities')) {
            abort(403, 'Unauthorized action.');
        }
        
        $query = Activity::query();

        // Apply filters similar to index method
        if ($request->has('type') && $request->type != '') {
            $query->where('type', $request->type);
        }

        if ($request->has('start_date') && $request->start_date != '') {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->has('end_date') && $request->end_date != '') {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        if ($request->has('user_id') && $request->user_id != '') {
            $query->where('user_id', $request->user_id)
                  ->where('user_type', 'App\\Models\\Admin');
        }
        
        $activities = $query->orderBy('created_at', 'desc')->get();
        
        // Create CSV file
        $filename = 'activity_log_' . now()->format('Y-m-d_H-i-s') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];
        
        $callback = function() use ($activities) {
            $file = fopen('php://output', 'w');
            
            // Add CSV headers
            fputcsv($file, [
                'ID',
                'Time',
                'Type',
                'Title',
                'Description',
                'User',
                'IP Address',
                'Location',
                'Browser',
                'OS',
                'Device'
            ]);
            
            foreach ($activities as $activity) {
                $browser = isset($activity->properties['browser']) ? $activity->properties['browser'] : 'Unknown';
                $os = isset($activity->properties['os']) ? $activity->properties['os'] : 'Unknown';
                $deviceType = isset($activity->properties['device_type']) ? $activity->properties['device_type'] : 'Unknown';
                $location = isset($activity->properties['location']) ? $activity->properties['location'] : 'Unknown';
                
                fputcsv($file, [
                    $activity->id,
                    $activity->created_at,
                    $activity->type,
                    $activity->title,
                    $activity->description,
                    $activity->user ? $activity->user->name : 'System',
                    $activity->ip_address,
                    $location,
                    $browser,
                    $os,
                    $deviceType
                ]);
            }
            
            fclose($file);
        };
        
        // Log this action
        Activity::log(
            'export',
            'Exported Activity Log',
            "Exported {$activities->count()} activity log records to CSV",
            auth()->guard('admin')->user()
        );
        
        return response()->stream($callback, 200, $headers);
    }
}