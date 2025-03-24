<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;

class PermissionController extends Controller
{
    /**
     * Display a listing of permissions.
     */
    public function index()
    {
        return view('admin.permissions.index');
    }

    /**
     * Get permissions data for DataTables.
     */
    public function data()
    {
        $permissions = Permission::where('guard_name', 'admin')->select('permissions.*');
        
        return DataTables::of($permissions)
            ->addColumn('action', function ($permission) {
                $buttons = '';
                
                // Check if it's a system permission
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
                    'manage permissions',
                    'view permissions',
                    'create permissions',
                    'edit permissions',
                    'delete permissions',
                    'manage settings',
                    'access dashboard',
                ];
                
                $isSystemPermission = in_array($permission->name, $systemPermissions);
                
                // Only allow editing if the user has permission
                if (auth('admin')->user()->can('edit permissions')) {
                    $buttons .= '<button type="button" class="btn btn-primary btn-sm mr-1 btn-edit" data-id="' . $permission->id . '"><i class="fas fa-edit"></i></button>';
                }
                
                // Only allow deletion if the user has permission and it's not a system permission
                if (auth('admin')->user()->can('delete permissions') && !$isSystemPermission) {
                    $buttons .= '<button type="button" class="btn btn-danger btn-sm btn-delete" data-id="' . $permission->id . '"><i class="fas fa-trash"></i></button>';
                }
                
                return $buttons;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    /**
     * Store a newly created permission in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('permissions')->where(function ($query) {
                    return $query->where('guard_name', 'admin');
                }),
            ],
            'description' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $permission = Permission::create([
                'name' => $request->name,
                'guard_name' => 'admin',
                'description' => $request->description ?? null,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Permission created successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while creating permission.'
            ], 500);
        }
    }

    /**
     * Get permissions for ajax requests.
     */
    public function getPermissions()
    {
        $permissions = Permission::where('guard_name', 'admin')->get();
        return response()->json($permissions);
    }

    /**
     * Show the form for editing the specified permission.
     */
    public function edit($id)
    {
        $permission = Permission::findOrFail($id);
        
        return response()->json(['permission' => $permission]);
    }

    /**
     * Update the specified permission in storage.
     */
    public function update(Request $request, $id)
    {
        $permission = Permission::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('permissions')->where(function ($query) use ($id) {
                    return $query->where('guard_name', 'admin')->where('id', '!=', $id);
                }),
            ],
            'description' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Check if it's a system permission
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
                'manage permissions',
                'view permissions',
                'create permissions',
                'edit permissions',
                'delete permissions',
                'manage settings',
                'access dashboard',
            ];
            
            if (in_array($permission->name, $systemPermissions) && $permission->name !== $request->name) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'System permissions cannot be renamed.'
                ], 403);
            }
            
            $permission->update([
                'name' => $request->name,
                'description' => $request->description ?? null,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Permission updated successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while updating permission.'
            ], 500);
        }
    }

    /**
     * Remove the specified permission from storage.
     */
    public function destroy($id)
    {
        try {
            $permission = Permission::findOrFail($id);

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
                'manage permissions',
                'view permissions',
                'create permissions',
                'edit permissions',
                'delete permissions',
                'manage settings',
                'access dashboard',
            ];

            if (in_array($permission->name, $systemPermissions)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'System permissions cannot be deleted.'
                ], 403);
            }

            $permission->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Permission deleted successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while deleting permission.'
            ], 500);
        }
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
                Rule::unique('permissions')->where(function ($query) use ($id) {
                    return $query->where('guard_name', 'admin')->where('id', '!=', $id ?? 0);
                }),
            ],
            'description' => 'nullable|string|max:255',
        ];

        $validator = Validator::make(
            [$field => $value],
            [$field => $rules[$field]]
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
}