<?php

namespace App\Http\Controllers\Admin;

use App\Models\Admin;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class AdminController extends Controller
{
    /**
     * Display a listing of the admins.
     */
    public function index()
    {
        $roles = Role::all();
        return view('admin.admins.index', compact('roles'));
    }

    /**
     * Get admin data for DataTables.
     */
    public function data()
    {
        $admins = Admin::with('roles')->get();
        
        return DataTables::of($admins)
            ->addColumn('roles', function (Admin $admin) {
                return $admin->roles->pluck('name')->toArray();
            })
            ->addColumn('action', function (Admin $admin) {
                $actions = '';
                
                // Only show edit button if user has permission
                if (Auth::guard('admin')->user()->can('edit admins')) {
                    $actions .= '<button type="button" class="btn btn-sm btn-primary btn-edit me-1" data-id="'.$admin->id.'">
                        <i class="fas fa-edit"></i>
                    </button> ';
                }
                
                // Only show delete button if user has permission and not editing themselves
                if (Auth::guard('admin')->user()->can('delete admins') && Auth::guard('admin')->id() !== $admin->id) {
                    $actions .= '<button type="button" class="btn btn-sm btn-danger btn-delete" data-id="'.$admin->id.'">
                        <i class="fas fa-trash"></i>
                    </button>';
                }
                
                return $actions;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    /**
     * Store a newly created admin in storage.
     */
    public function store(Request $request)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:admins',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'position' => 'nullable|string|max:100',
            'department' => 'nullable|string|max:100',
            'profile_image' => 'nullable|image|max:2048',
            'roles' => 'required|array',
            'is_active' => 'boolean',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->except(['password', 'password_confirmation', 'profile_image', 'roles']);
        $data['password'] = Hash::make($request->password);
        
        // Handle profile image upload
        if ($request->hasFile('profile_image')) {
            $path = $request->file('profile_image')->store('admin-profiles', 'public');
            $data['profile_image'] = $path;
        }
        
        $admin = Admin::create($data);
        
        // Important fix: Get the actual role objects to avoid the "role named 1" error
        $roleIds = $request->roles;
        $roles = Role::whereIn('id', $roleIds)->get();
        
        // Assign roles using the collection of role objects
        $admin->assignRole($roles);
        
        // Log activity if spatie activity-log package is installed
        if (method_exists(app(), 'activity')) {
            activity()
                ->causedBy(Auth::guard('admin')->user())
                ->performedOn($admin)
                ->withProperties(['admin_id' => $admin->id, 'admin_email' => $admin->email])
                ->log('Created admin user');
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Admin created successfully.',
            'admin' => $admin
        ]);
    }

    /**
     * Show the form for editing the specified admin.
     */
    public function edit(Admin $admin)
    {
        $admin->load('roles');
        $roles = $admin->roles->pluck('id')->toArray();
        
        return response()->json([
            'success' => true,
            'admin' => [
                'id' => $admin->id,
                'name' => $admin->name,
                'email' => $admin->email,
                'phone' => $admin->phone,
                'position' => $admin->position,
                'department' => $admin->department,
                'is_active' => $admin->is_active,
                'profile_image' => $admin->profile_image,
                'roles' => $roles,
            ]
        ]);
    }

    /**
     * Update the specified admin in storage.
     */
    public function update(Request $request, Admin $admin)
    {
        // Validate input
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:admins,email,' . $admin->id,
            'phone' => 'nullable|string|max:20',
            'position' => 'nullable|string|max:100',
            'department' => 'nullable|string|max:100',
            'profile_image' => 'nullable|image|max:2048',
            'roles' => 'required|array',
            'is_active' => 'boolean',
        ];
        
        // If password is provided, validate it
        if ($request->filled('password')) {
            $rules['password'] = 'required|string|min:8|confirmed';
        }
        
        $validator = Validator::make($request->all(), $rules);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->except(['password', 'password_confirmation', 'profile_image', 'roles', '_method']);
        
        // Update password if provided
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }
        
        // Handle profile image upload
        if ($request->hasFile('profile_image')) {
            // Delete old image if exists
            if ($admin->profile_image) {
                Storage::disk('public')->delete($admin->profile_image);
            }
            
            $path = $request->file('profile_image')->store('admin-profiles', 'public');
            $data['profile_image'] = $path;
        }
        
        $admin->update($data);
        
        // Important fix: Get the actual role objects to avoid the "role named 1" error
        $roleIds = $request->roles;
        $roles = Role::whereIn('id', $roleIds)->get();
        
        // Sync roles using the collection of role objects
        $admin->syncRoles($roles);
        
        // Log activity if spatie activity-log package is installed
        if (method_exists(app(), 'activity')) {
            activity()
                ->causedBy(Auth::guard('admin')->user())
                ->performedOn($admin)
                ->withProperties(['admin_id' => $admin->id, 'admin_email' => $admin->email])
                ->log('Updated admin user');
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Admin updated successfully.',
            'admin' => $admin
        ]);
    }

    /**
     * Remove the specified admin from storage.
     */
    public function destroy(Admin $admin)
    {
        // Prevent self-deletion
        if (Auth::guard('admin')->id() === $admin->id) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot delete your own account.'
            ], 403);
        }
        
        // Store data for activity log
        $adminData = [
            'id' => $admin->id,
            'name' => $admin->name,
            'email' => $admin->email
        ];
        
        // Delete profile image if exists
        if ($admin->profile_image) {
            Storage::disk('public')->delete($admin->profile_image);
        }
        
        $admin->delete();
        
        // Log activity if spatie activity-log package is installed
        if (method_exists(app(), 'activity')) {
            activity()
                ->causedBy(Auth::guard('admin')->user())
                ->withProperties($adminData)
                ->log('Deleted admin user');
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Admin deleted successfully.'
        ]);
    }
    
    /**
     * Display admin profile.
     */
    public function profile()
    {
        $admin = Auth::guard('admin')->user();
        return view('admin.profile', compact('admin'));
    }
    
    /**
     * Update admin profile.
     */
    public function updateProfile(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        
        // Handle AJAX validation
        if ($request->ajax()) {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:admins,email,' . $admin->id,
                'phone' => 'nullable|string|max:20',
                'position' => 'nullable|string|max:100',
                'department' => 'nullable|string|max:100',
                'profile_image' => 'nullable|image|max:2048',
            ]);
            
            // Validate password if provided
            if ($request->filled('password')) {
                $validator->addRules([
                    'password' => 'required|string|min:8|confirmed',
                    'current_password' => 'required|current_password:admin',
                ]);
            }
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
        } else {
            // Regular validation for non-AJAX requests
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:admins,email,' . $admin->id,
                'phone' => 'nullable|string|max:20',
                'position' => 'nullable|string|max:100',
                'department' => 'nullable|string|max:100',
                'profile_image' => 'nullable|image|max:2048',
            ]);
            
            // Validate password if provided
            if ($request->filled('password')) {
                $request->validate([
                    'password' => 'required|string|min:8|confirmed',
                    'current_password' => 'required|current_password:admin',
                ]);
            }
        }
        
        $data = $request->except(['password', 'password_confirmation', 'profile_image', 'current_password', '_token', '_method']);
        
        // Update password if provided
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }
        
        // Handle profile image upload
        if ($request->hasFile('profile_image')) {
            // Delete old image if exists
            if ($admin->profile_image) {
                Storage::disk('public')->delete($admin->profile_image);
            }
            
            $path = $request->file('profile_image')->store('admin-profiles', 'public');
            $data['profile_image'] = $path;
        }
        
        $admin->update($data);
        
        // Log activity if spatie activity-log package is installed
        if (method_exists(app(), 'activity')) {
            activity()
                ->causedBy($admin)
                ->performedOn($admin)
                ->log('Updated profile');
        }
        
        // Handle AJAX response
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully.',
                'name' => $admin->name,
                'email' => $admin->email
            ]);
        }
        
        // Regular response
        return redirect()->route('admin.profile')
            ->with('success', 'Profile updated successfully.');
    }
 
}