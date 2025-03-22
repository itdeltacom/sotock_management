<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    /**
     * Display a listing of permissions.
     */
    public function index()
    {
        $permissions = Permission::where('guard_name', 'admin')->paginate(15);
        return view('admin.permissions.index', compact('permissions'));
    }

    /**
     * Show the form for creating a new permission.
     */
    public function create()
    {
        return view('admin.permissions.create');
    }

    /**
     * Store a newly created permission in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('permissions')->where(function ($query) {
                    return $query->where('guard_name', 'admin');
                }),
            ],
        ]);

        $permission = Permission::create([
            'name' => $request->name,
            'guard_name' => 'admin',
        ]);

        // The observer will automatically assign this to Super Admin

        return redirect()->route('admin.permissions.index')
            ->with('success', 'Permission created successfully.');
    }

    /**
     * Show the form for editing the specified permission.
     */
    public function edit(Permission $permission)
    {
        return view('admin.permissions.edit', compact('permission'));
    }

    /**
     * Update the specified permission in storage.
     */
    public function update(Request $request, Permission $permission)
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('permissions')->where(function ($query) use ($permission) {
                    return $query->where('guard_name', 'admin')->where('id', '!=', $permission->id);
                }),
            ],
        ]);

        $permission->update([
            'name' => $request->name,
        ]);

        return redirect()->route('admin.permissions.index')
            ->with('success', 'Permission updated successfully.');
    }

    /**
     * Remove the specified permission from storage.
     */
    public function destroy(Permission $permission)
    {
        // Check if it's a system permission that shouldn't be deleted
        $systemPermissions = [
            'manage admins',
            'view admins',
            'create admins',
            'edit admins',
            'delete admins',
            'manage roles',
            'view roles',
            'create roles',
            'edit roles',
            'delete roles',
            'manage settings',
            'access dashboard',
        ];

        if (in_array($permission->name, $systemPermissions)) {
            return redirect()->route('admin.permissions.index')
                ->with('error', 'Cannot delete system permissions.');
        }

        $permission->delete();

        return redirect()->route('admin.permissions.index')
            ->with('success', 'Permission deleted successfully.');
    }
}