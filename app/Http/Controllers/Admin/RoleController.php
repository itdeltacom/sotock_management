<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;

class RoleController extends Controller
{
    /**
     * Display a listing of roles.
     */
    public function index()
    {
        $permissions = Permission::where('guard_name', 'admin')->get();
        return view('admin.roles.index', compact('permissions'));
    }

    /**
     * Get roles data for DataTables.
     */
    public function data()
    {
        $roles = Role::where('guard_name', 'admin')
            ->withCount('permissions')
            ->with('permissions');
        
        return DataTables::of($roles)
            ->addColumn('permissions_count', function (Role $role) {
                return $role->permissions_count;
            })
            ->addColumn('permissions_list', function (Role $role) {
                $permissions = $role->permissions->pluck('name')->toArray();
                if (count($permissions) > 5) {
                    $displayPermissions = array_slice($permissions, 0, 5);
                    return implode(', ', $displayPermissions) . ' <span class="badge bg-secondary">+' . (count($permissions) - 5) . ' more</span>';
                }
                return implode(', ', $permissions);
            })
            ->addColumn('action', function (Role $role) {
                $buttons = '';
                
                // Don't allow editing/deleting super admin role unless the current user is a super admin
                $isSuperAdmin = strtolower($role->name) === 'super admin';
                
                // Only show view button if user has permission
                if (auth('admin')->user()->can('view roles')) {
                    $buttons .= '<button type="button" class="btn btn-info btn-sm mr-1 btn-view" data-id="' . $role->id . '"><i class="fas fa-eye"></i></button>';
                }
                
                // Only show edit button if user has permission and it's not the super admin role
                if (auth('admin')->user()->can('edit roles') && (!$isSuperAdmin || auth('admin')->user()->hasRole('super admin'))) {
                    $buttons .= '<button type="button" class="btn btn-primary btn-sm mr-1 btn-edit" data-id="' . $role->id . '"><i class="fas fa-edit"></i></button>';
                }
                
                // Only show delete button if user has permission and it's not the super admin role
                if (auth('admin')->user()->can('delete roles') && (!$isSuperAdmin || auth('admin')->user()->hasRole('super admin'))) {
                    $buttons .= '<button type="button" class="btn btn-danger btn-sm btn-delete" data-id="' . $role->id . '"><i class="fas fa-trash"></i></button>';
                }
                
                return $buttons;
            })
            ->rawColumns(['permissions_list', 'action'])
            ->make(true);
    }

    /**
     * Store a newly created role in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles')->where(function ($query) {
                    return $query->where('guard_name', 'admin');
                }),
            ],
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $role = Role::create([
                'name' => $request->name,
                'guard_name' => 'admin',
            ]);

            // Get permission objects to avoid "permission named 1" error
            $permissions = Permission::whereIn('id', $request->permissions)->get();
            $role->syncPermissions($permissions);

            // Ensure Super Admin role still has all permissions
            $this->syncSuperAdminPermissions();

            return response()->json([
                'status' => 'success',
                'message' => 'Role created successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while creating role.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get the specified role for editing.
     */
    public function edit($id)
    {
        $role = Role::with('permissions')->findOrFail($id);
        
        // Prevent editing the Super Admin role unless the user is a super admin
        if (strtolower($role->name) === 'super admin' && !auth('admin')->user()->hasRole('super admin')) {
            return response()->json([
                'status' => 'error',
                'message' => 'The Super Admin role cannot be edited.'
            ], 403);
        }
        
        $rolePermissions = $role->permissions->pluck('id')->toArray();
        
        return response()->json([
            'status' => 'success',
            'role' => $role,
            'rolePermissions' => $rolePermissions
        ]);
    }

    /**
     * Update the specified role in storage.
     */
    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        // Prevent updating the Super Admin role unless the user is a super admin
        if (strtolower($role->name) === 'super admin' && !auth('admin')->user()->hasRole('super admin')) {
            return response()->json([
                'status' => 'error',
                'message' => 'The Super Admin role cannot be updated.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles')->where(function ($query) use ($id) {
                    return $query->where('guard_name', 'admin')->where('id', '!=', $id);
                }),
            ],
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Don't rename the super admin role
            if (strtolower($role->name) === 'super admin' && strtolower($request->name) !== 'super admin') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'The Super Admin role name cannot be changed.'
                ], 403);
            }

            $role->update([
                'name' => $request->name,
            ]);

            // Get permission objects to avoid "permission named 1" error
            $permissions = Permission::whereIn('id', $request->permissions)->get();
            $role->syncPermissions($permissions);

            // Ensure Super Admin role still has all permissions
            if (strtolower($role->name) !== 'super admin') {
                $this->syncSuperAdminPermissions();
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Role updated successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while updating role.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * View the specified role details.
     */
    public function show($id)
    {
        $role = Role::with('permissions')->findOrFail($id);
        $rolePermissions = $role->permissions->pluck('name')->toArray();
        
        return response()->json([
            'status' => 'success',
            'role' => $role,
            'rolePermissions' => $rolePermissions
        ]);
    }

    /**
     * Remove the specified role from storage.
     */
    public function destroy($id)
    {
        try {
            $role = Role::findOrFail($id);

            // Prevent deleting the Super Admin role
            if (strtolower($role->name) === 'super admin') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'The Super Admin role cannot be deleted.'
                ], 403);
            }

            // Check if role is in use
            $usersCount = $role->users()->count();
            if ($usersCount > 0) {
                return response()->json([
                    'status' => 'error',
                    'message' => "This role is assigned to {$usersCount} user(s) and cannot be deleted. Please remove the role from all users first."
                ], 422);
            }

            $role->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Role deleted successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while deleting role.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all roles for AJAX request.
     */
    public function getRoles()
    {
        $roles = Role::where('guard_name', 'admin')
            ->withCount('permissions')
            ->get();
            
        return response()->json($roles);
    }

    /**
     * Validate a specific field.
     */
    public function validateField(Request $request)
    {
        $field = $request->field;
        $value = $request->value;
        $id = $request->id;

        // Skip validation if field is empty (unless it's being submitted)
        if (empty($value) && !$request->has('is_submit')) {
            return response()->json(['valid' => true]);
        }

        $rules = [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles')->where(function ($query) use ($id) {
                    return $query->where('guard_name', 'admin')->where('id', '!=', $id ?? 0);
                }),
            ],
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,id'
        ];

        $validator = Validator::make(
            [$field => $value],
            [$field => $rules[$field] ?? 'required']
        );

        if ($validator->fails()) {
            return response()->json([
                'valid' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        return response()->json([
            'valid' => true,
            'message' => 'Valid'
        ]);
    }

    /**
     * Ensure Super Admin role has all permissions.
     */
    private function syncSuperAdminPermissions()
    {
        $superAdminRole = Role::where('name', 'super admin')
                              ->where('guard_name', 'admin')
                              ->first();
        
        if ($superAdminRole) {
            $allPermissions = Permission::where('guard_name', 'admin')->get();
            $superAdminRole->syncPermissions($allPermissions);
        }
    }
}