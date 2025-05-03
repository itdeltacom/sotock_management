<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('admin.clients.index');
    }

    /**
     * Process DataTables AJAX request.
     */
    public function datatable(Request $request)
    {
        $clients = User::query();
        
        return DataTables::of($clients)
            ->addColumn('status_badge', function ($client) {
                $badges = [
                    'active' => 'success',
                    'inactive' => 'danger',
                    'banned' => 'warning'
                ];
                
                $badge = $badges[$client->status] ?? 'secondary';
                return '<span class="badge bg-' . $badge . '">' . ucfirst($client->status) . '</span>';
            })
            ->addColumn('actions', function ($client) {
                $buttons = '<div class="btn-group" role="group">';
                
                // View button
                $buttons .= '<a href="' . route('admin.clients.show', $client->id) . '" class="btn btn-sm btn-info" title="View"><i class="fas fa-eye"></i></a>';
                
                // Edit button
                $buttons .= '<a href="' . route('admin.clients.edit', $client->id) . '" class="btn btn-sm btn-primary" title="Edit"><i class="fas fa-edit"></i></a>';
                
                // Delete button
                $buttons .= '<button class="btn btn-sm btn-danger delete-record" data-id="' . $client->id . '" title="Delete"><i class="fas fa-trash"></i></button>';
                
                $buttons .= '</div>';
                return $buttons;
            })
            ->rawColumns(['status_badge', 'actions'])
            ->make(true);
    }

    /**
     * Get clients list for dropdowns.
     */
    public function getClientDetails($id)
    {
        $client = User::findOrFail($id);
        
        return response()->json([
            'success' => true,
            'client' => [
                'name' => $client->name,
                'email' => $client->email,
                'phone' => $client->phone,
                'address' => $client->address,
                'status' => $client->status,
                // Add other fields you need
            ]
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.clients.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'photo' => 'nullable|image|max:2048',
            'status' => 'required|in:active,inactive,banned',
        ]);

        // Handle photo upload if present
        $photo = null;
        if ($request->hasFile('photo')) {
            $photo = $request->file('photo')->store('users', 'public');
        }

        $client = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'phone' => $request->phone,
            'address' => $request->address,
            'photo' => $photo,
            'status' => $request->status,
        ]);

        return redirect()->route('admin.clients.index')
            ->with('success', 'Client created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $client)
    {
        return view('admin.clients.show', compact('client'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $client)
    {
        return view('admin.clients.edit', compact('client'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $client)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($client)],
            'password' => 'nullable|string|min:8',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'photo' => 'nullable|image|max:2048',
            'status' => 'required|in:active,inactive,banned',
        ]);

        // Handle photo upload if present
        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($client->photo) {
                Storage::disk('public')->delete($client->photo);
            }
            $photo = $request->file('photo')->store('users', 'public');
            $client->photo = $photo;
        }

        $client->name = $request->name;
        $client->email = $request->email;
        if ($request->filled('password')) {
            $client->password = bcrypt($request->password);
        }
        $client->phone = $request->phone;
        $client->address = $request->address;
        $client->status = $request->status;
        $client->save();

        return redirect()->route('admin.clients.index')
            ->with('success', 'Client updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $client)
    {
        try {
            // Delete photo if exists
            if ($client->photo) {
                Storage::disk('public')->delete($client->photo);
            }
            
            $client->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Client deleted successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete client: ' . $e->getMessage()
            ]);
        }
    }
}