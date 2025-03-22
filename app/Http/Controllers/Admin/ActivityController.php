<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use Illuminate\Http\Request;

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
        $adminUsers = \App\Models\Admin::select('id', 'name')
                                       ->orderBy('name')
                                       ->get();

        return view('admin.activities.index', compact(
            'activities',
            'activityTypes',
            'adminUsers'
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
        return view('admin.activities.show', compact('activity'));
    }

    /**
     * Clear all activities.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function clearAll()
    {
        Activity::truncate();

        // Log the action itself
        Activity::log(
            'admin',
            'Cleared Activity Log',
            'All activity logs were cleared from the system',
            auth()->guard('admin')->user()
        );

        return redirect()->route('admin.activities.index')
                         ->with('success', 'Activity log has been cleared successfully.');
    }

    /**
     * Remove the specified activity from storage.
     *
     * @param Activity $activity
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Activity $activity)
    {
        $activity->delete();

        return redirect()->route('admin.activities.index')
                         ->with('success', 'Activity deleted successfully.');
    }
}