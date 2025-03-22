<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

class AdminController extends Controller
{
    /**
     * Display a listing of the admins.
     */
    public function index()
    {
        $admins = Admin::with('roles')->paginate(10);
        return view('admin.admins.index', compact('admins'));
    }

    /**
     * Show the form for creating a new admin.
     */
    public function create()
    {
        $roles = Role::all();
        return view('admin.admins.create', compact('roles'));
    }

    /**
     * Store a newly created admin in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
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

        $data = $request->except(['password', 'password_confirmation', 'profile_image', 'roles']);
        $data['password'] = Hash::make($request->password);
        
        // Handle profile image upload
        if ($request->hasFile('profile_image')) {
            $path = $request->file('profile_image')->store('admin-profiles', 'public');
            $data['profile_image'] = $path;
        }
        
        $admin = Admin::create($data);
        
        // Assign roles
        $admin->assignRole($request->roles);
        
        return redirect()->route('admin.admins.index')
            ->with('success', 'Admin created successfully.');
    }

    /**
     * Display the specified admin.
     */
    public function show(Admin $admin)
    {
        $admin->load('roles', 'permissions');
        return view('admin.admins.show', compact('admin'));
    }

    /**
     * Show the form for editing the specified admin.
     */
    public function edit(Admin $admin)
    {
        $roles = Role::all();
        $admin->load('roles');
        return view('admin.admins.edit', compact('admin', 'roles'));
    }

    /**
     * Update the specified admin in storage.
     */
    public function update(Request $request, Admin $admin)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:admins,email,' . $admin->id,
            'phone' => 'nullable|string|max:20',
            'position' => 'nullable|string|max:100',
            'department' => 'nullable|string|max:100',
            'profile_image' => 'nullable|image|max:2048',
            'roles' => 'required|array',
            'is_active' => 'boolean',
        ]);

        $data = $request->except(['password', 'password_confirmation', 'profile_image', 'roles']);
        
        // Update password if provided
        if ($request->filled('password')) {
            $request->validate([
                'password' => 'required|string|min:8|confirmed',
            ]);
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
        
        // Sync roles
        $admin->syncRoles($request->roles);
        
        return redirect()->route('admin.admins.index')
            ->with('success', 'Admin updated successfully.');
    }

    /**
     * Remove the specified admin from storage.
     */
    public function destroy(Admin $admin)
    {
        // Prevent self-deletion
        if (Auth::guard('admin')->id() === $admin->id) {
            return redirect()->route('admin.admins.index')
                ->with('error', 'You cannot delete your own account.');
        }
        
        // Delete profile image if exists
        if ($admin->profile_image) {
            Storage::disk('public')->delete($admin->profile_image);
        }
        
        $admin->delete();
        
        return redirect()->route('admin.admins.index')
            ->with('success', 'Admin deleted successfully.');
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
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:admins,email,' . $admin->id,
            'phone' => 'nullable|string|max:20',
            'profile_image' => 'nullable|image|max:2048',
        ]);
        
        $data = $request->except(['password', 'password_confirmation', 'profile_image']);
        
        // Update password if provided
        if ($request->filled('password')) {
            $request->validate([
                'password' => 'required|string|min:8|confirmed',
                'current_password' => 'required|current_password:admin',
            ]);
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
        
        return redirect()->route('admin.profile')
            ->with('success', 'Profile updated successfully.');
    }
}