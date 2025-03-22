<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    /**
     * Display a listing of roles.
     */
    public function index()
    {
        $roles = Role::where('guard_name', 'admin')->withCount('permissions')->paginate(10);
        return view('admin.roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new role.
     */
    public function create()
    {
        $permissions = Permission::where('guard_name', 'admin')->get();
        return view('admin.roles.create', compact('permissions'));
    }

    /**
     * Store a newly created role in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles')->where(function ($query) {
                    return $query->where('guard_name', 'admin');
                }),
            ],
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role = Role::create([
            'name' => $request->name,
            'guard_name' => 'admin',
        ]);

        $role->syncPermissions($request->permissions);

        // Ensure Super Admin role still has all permissions
        $this->syncSuperAdminPermissions();

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role created successfully.');
    }

    /**
     * Display the specified role.
     */
    public function show(Role $role)
    {
        $role->load('permissions');
        return view('admin.roles.show', compact('role'));
    }

    /**
     * Show the form for editing the specified role.
     */
    public function edit(Role $role)
    {
        // Prevent editing the Super Admin role
        if ($role->name === 'Super Admin') {
            return redirect()->route('admin.roles.index')
                ->with('error', 'The Super Admin role cannot be edited.');
        }

        $permissions = Permission::where('guard_name', 'admin')->get();
        $role->load('permissions');
        
        return view('admin.roles.edit', compact('role', 'permissions'));
    }

    /**
     * Update the specified role in storage.
     */
    public function update(Request $request, Role $role)
    {
        // Prevent updating the Super Admin role
        if ($role->name === 'Super Admin') {
            return redirect()->route('admin.roles.index')
                ->with('error', 'The Super Admin role cannot be updated.');
        }

        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles')->where(function ($query) use ($role) {
                    return $query->where('guard_name', 'admin')->where('id', '!=', $role->id);
                }),
            ],
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role->update([
            'name' => $request->name,
        ]);

        $role->syncPermissions($request->permissions);

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role updated successfully.');
    }

    /**
     * Remove the specified role from storage.
     */
    public function destroy(Role $role)
    {
        // Prevent deleting the Super Admin role
        if ($role->name === 'Super Admin') {
            return redirect()->route('admin.roles.index')
                ->with('error', 'The Super Admin role cannot be deleted.');
        }

        $role->delete();

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role deleted successfully.');
    }

    /**
     * Ensure Super Admin role has all permissions.
     */
    private function syncSuperAdminPermissions()
    {
        $superAdminRole = Role::where('name', 'Super Admin')
                              ->where('guard_name', 'admin')
                              ->first();
        
        if ($superAdminRole) {
            $allPermissions = Permission::where('guard_name', 'admin')->get();
            $superAdminRole->syncPermissions($allPermissions);
        }
    }
}