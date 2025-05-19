<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;
use Exception;
use Carbon\Carbon;

class AuditLogController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth:admin');
        $this->middleware('permission:view_audit_logs');
    }

    /**
     * Display a listing of audit logs.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        try {
            return view('admin.audit-logs.index');
        } catch (Exception $e) {
            Log::error('Error displaying audit logs: ' . $e->getMessage());
            return redirect()->route('admin.dashboard')->with('error', 'An error occurred while accessing audit logs.');
        }
    }

    /**
     * Get audit logs data for DataTables.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function data(Request $request)
    {
        try {
            $query = Activity::with('causer');
            
            // Apply filters
            if ($request->has('causer_id') && $request->causer_id) {
                $query->where('causer_id', $request->causer_id)
                    ->where('causer_type', 'App\\Models\\User');
            }
            
            if ($request->has('subject_type') && $request->subject_type) {
                $query->where('subject_type', 'like', '%' . $request->subject_type . '%');
            }
            
            if ($request->has('description') && $request->description) {
                $query->where('description', 'like', '%' . $request->description . '%');
            }
            
            if ($request->has('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }
            
            if ($request->has('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }
            
            $activities = $query->orderBy('created_at', 'desc')->get();
            
            return DataTables::of($activities)
                ->editColumn('created_at', function (Activity $activity) {
                    return $activity->created_at->format('Y-m-d H:i:s');
                })
                ->addColumn('causer_name', function (Activity $activity) {
                    return $activity->causer ? $activity->causer->name : 'System';
                })
                ->addColumn('subject_info', function (Activity $activity) {
                    if (!$activity->subject_type) {
                        return '-';
                    }
                    
                    $subjectType = class_basename($activity->subject_type);
                    $subjectId = $activity->subject_id ?? '';
                    
                    return $subjectType . ($subjectId ? ' #' . $subjectId : '');
                })
                ->addColumn('properties', function (Activity $activity) {
                    $properties = $activity->properties;
                    
                    if ($properties->isEmpty()) {
                        return '-';
                    }
                    
                    $html = '<ul class="list-unstyled mb-0">';
                    
                    foreach ($properties as $key => $value) {
                        if (is_array($value) || is_object($value)) {
                            $html .= '<li><strong>' . $key . ':</strong> ' . json_encode($value) . '</li>';
                        } else {
                            $html .= '<li><strong>' . $key . ':</strong> ' . $value . '</li>';
                        }
                    }
                    
                    $html .= '</ul>';
                    
                    return $html;
                })
                ->rawColumns(['properties'])
                ->make(true);
        } catch (Exception $e) {
            Log::error('Error getting audit logs data: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to retrieve audit logs data.'
            ], 500);
        }
    }
}