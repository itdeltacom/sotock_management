<?php

namespace App\Http\Controllers\Admin;

use Log;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Payment;
use App\Models\Contract;
use App\Models\Activity; // Add the Activity model import
use Illuminate\Http\Request;
use App\Exports\ClientsExport;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;
use App\Notifications\ClientNotification;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            // Calculate statistics for the dashboard
            $statistics = [
                'total_clients' => User::count(),
                'active_clients' => User::where('status', 'active')->count(),
                'banned_clients' => User::where('status', 'banned')->count(),
                'overdue_contracts' => Contract::where('status', 'active')
                    ->where('end_date', '<', now())
                    ->count(),
                'overdue_amount' => Contract::where('status', 'active')
                    ->where('end_date', '<', now())
                    ->with('payments')
                    ->get()
                    ->sum(function ($contract) {
                        $paid = $contract->payments->sum('amount');
                        return $contract->total_amount - $paid;
                    }),
                'new_this_month' => User::where('created_at', '>=', Carbon::now()->startOfMonth())
                    ->where('created_at', '<=', Carbon::now()->endOfMonth())
                    ->count(),
                'active_contracts' => Contract::where('status', 'active')->count(),
                'contracts_ending_soon' => Contract::where('status', 'active')
                    ->where('end_date', '>=', now())
                    ->where('end_date', '<=', now()->addDays(7))
                    ->count(),
                'risk_clients' => User::whereHas('contracts', function ($query) {
                    $query->where('status', 'active')
                          ->where('end_date', '<', now());
                })->count(),
            ];

            return view('admin.customers.index', compact('statistics'));
        } catch (\Exception $e) {
            \Log::error('Error fetching client statistics: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Unable to load client data. Please try again later.');
        }
    }

    /**
     * Process DataTables AJAX request.
     */
    public function datatable(Request $request)
    {
        $query = User::query()
            ->withCount([
                'contracts as active_contracts' => function ($query) {
                    $query->where('status', 'active');
                },
                'contracts as total_contracts',
                'contracts as overdue_contracts' => function ($query) {
                    $query->where('status', 'active')
                          ->where('end_date', '<', now());
                }
            ])
            ->with(['contracts' => function ($query) {
                $query->where('status', 'active')
                      ->where('payment_status', '!=', 'paid');
            }]);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('has_overdue')) {
            $query->whereHas('contracts', function ($q) {
                $q->where('status', 'active')
                  ->where('end_date', '<', now());
            });
        }

        if ($request->filled('has_outstanding')) {
            $query->whereHas('contracts', function ($q) {
                $q->where('payment_status', '!=', 'paid');
            });
        }

        return DataTables::of($query)
            ->addColumn('full_name', function ($client) {
                $html = $client->name;
                if ($client->credit_score < 50) {
                    $html .= ' <span class="badge bg-danger" title="Low Credit Score">Risk</span>';
                }
                return $html;
            })
            ->addColumn('contact_info', function ($client) {
                $html = $client->email . '<br><small class="text-muted">' . $client->phone . '</small>';
                if ($client->license_expiry_date && $client->license_expiry_date < now()) {
                    $html .= '<br><span class="badge bg-warning">License Expired</span>';
                }
                return $html;
            })
            ->addColumn('status_badge', function ($client) {
                $badges = [
                    'active' => 'success',
                    'inactive' => 'danger',
                    'banned' => 'warning'
                ];
                
                $badge = $badges[$client->status] ?? 'secondary';
                return '<span class="badge bg-' . $badge . '">' . ucfirst($client->status) . '</span>';
            })
            ->addColumn('contracts_info', function ($client) {
                $info = '<div class="text-sm">';
                $info .= '<span class="text-primary">Active: ' . $client->active_contracts . '</span><br>';
                $info .= '<span class="text-success">Total: ' . $client->total_contracts . '</span>';
                if ($client->overdue_contracts > 0) {
                    $info .= '<br><span class="text-danger">Overdue: ' . $client->overdue_contracts . '</span>';
                }
                $info .= '</div>';
                return $info;
            })
            ->addColumn('balance', function ($client) {
                $totalOwed = $client->total_outstanding_balance;
                
                if ($totalOwed > 0) {
                    return '<span class="text-danger">' . number_format($totalOwed, 2) . ' MAD</span>';
                } else {
                    return '<span class="text-success">0.00 MAD</span>';
                }
            })
            ->addColumn('credit_score', function ($client) {
                $score = $client->credit_score;
                $color = $score >= 80 ? 'success' : ($score >= 50 ? 'warning' : 'danger');
                return '<div class="progress" style="height: 20px;">
                    <div class="progress-bar bg-' . $color . '" style="width: ' . $score . '%">' . $score . '</div>
                </div>';
            })
            ->addColumn('actions', function ($client) {
                $buttons = '<div class="btn-group" role="group">';
                
                // View button
                $buttons .= '<button class="btn btn-sm btn-info view-client" data-id="' . $client->id . '" title="View"><i class="fas fa-eye"></i></button>';
                
                // Edit button
                $buttons .= '<a href="' . route('admin.clients.edit', $client->id) . '" class="btn btn-sm btn-primary" title="Edit"><i class="fas fa-edit"></i></a>';
                
                // Payments button
                if ($client->total_contracts > 0) {
                    $buttons .= '<a href="' . route('admin.clients.payments', $client->id) . '" class="btn btn-sm btn-success" title="Payments"><i class="fas fa-money-bill"></i></a>';
                }
                
                // Send notification button
                $buttons .= '<button class="btn btn-sm btn-warning send-notification" data-id="' . $client->id . '" title="Send Notification"><i class="fas fa-envelope"></i></button>';
                
                // Ban/Unban button
                if ($client->status !== 'banned') {
                    $buttons .= '<button class="btn btn-sm btn-warning ban-client" data-id="' . $client->id . '" title="Ban Client"><i class="fas fa-ban"></i></button>';
                } else {
                    $buttons .= '<button class="btn btn-sm btn-success unban-client" data-id="' . $client->id . '" title="Unban Client"><i class="fas fa-check-circle"></i></button>';
                }
                
                // Delete button
                $buttons .= '<button class="btn btn-sm btn-danger delete-record" data-id="' . $client->id . '" title="Delete"><i class="fas fa-trash"></i></button>';
                
                $buttons .= '</div>';
                return $buttons;
            })
            ->rawColumns(['full_name', 'contact_info', 'status_badge', 'contracts_info', 'balance', 'credit_score', 'actions'])
            ->make(true);
    }

    /**
     * Get clients list for dropdowns/AJAX.
     */
    public function getClientsList()
    {
        $clients = User::where('status', 'active')
            ->select('id', 'name', 'email', 'phone', 'id_number')
            ->get()
            ->map(function ($client) {
                $canRent = $client->canRent();
                $reason = $client->getRentalRestrictionReason();
                
                return [
                    'id' => $client->id,
                    'full_name' => $client->name,
                    'display_name' => $client->name . ' (' . $client->id_number . ')' . (!$canRent ? ' - ⚠️ ' . $reason : ''),
                    'email' => $client->email,
                    'phone' => $client->phone,
                    'can_rent' => $canRent,
                    'restriction_reason' => $reason
                ];
            });

        return response()->json([
            'success' => true,
            'clients' => $clients
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.customers.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|max:255|unique:users',
        'password' => 'required|string|min:8|confirmed',
        'phone' => 'required|string|max:20',
        'address' => 'nullable|string',
        'id_number' => 'required|string|max:50|unique:users,id_number',
        'license_number' => 'required|string|max:50|unique:users,license_number',
        'license_expiry_date' => 'required|date|after:today',
        'photo' => 'nullable|image|max:2048',
        'status' => 'required|in:active,inactive',
        'notes' => 'nullable|string',
        'emergency_contact_name' => 'nullable|string|max:255',
        'emergency_contact_phone' => 'nullable|string|max:20',
    ]);

    try {
        DB::beginTransaction();

        // Handle photo upload if present
        if ($request->hasFile('photo')) {
            // Verify the file is valid
            if (!$request->file('photo')->isValid()) {
                throw new \Exception('The uploaded photo is invalid or corrupted.');
            }

            // Check if the storage directory is writable
            $storagePath = storage_path('app/public/users');
            if (!is_dir($storagePath)) {
                mkdir($storagePath, 0755, true);
            }
            if (!is_writable($storagePath)) {
                throw new \Exception('Storage directory is not writable. Please check permissions.');
            }

            $validated['photo'] = $request->file('photo')->store('users', 'public');
        }

        // Hash password
        $validated['password'] = bcrypt($validated['password']);

        $client = User::create($validated);

        // Log activity using custom Activity model
        Activity::create([
            'type' => 'client',
            'title' => 'Client Created',
            'description' => 'Created new client: ' . $client->name,
            'user_type' => get_class(auth()->user()),
            'user_id' => auth()->id(),
            'subject_type' => get_class($client),
            'subject_id' => $client->id,
            'properties' => null,
            'ip_address' => request()->ip()
        ]);

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Client created successfully.'
        ]);
    } catch (\Exception $e) {
        DB::rollBack();

        // Delete uploaded photo if exists
        if (isset($validated['photo'])) {
            Storage::disk('public')->delete($validated['photo']);
        }

        // Log the error for debugging
        \Log::error('Error creating client: ' . $e->getMessage(), ['exception' => $e]);

        return response()->json([
            'success' => false,
            'message' => 'Error creating client: ' . $e->getMessage()
        ], 500);
    }
}
     /*public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string',
            'id_number' => 'required|string|max:50|unique:users,id_number',
            'license_number' => 'required|string|max:50|unique:users,license_number',
            'license_expiry_date' => 'required|date|after:today',
            'photo' => 'nullable|image|max:2048',
            'status' => 'required|in:active,inactive',
            'notes' => 'nullable|string',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
        ]);

        try {
            DB::beginTransaction();

            // Handle photo upload if present
            if ($request->hasFile('photo')) {
                $validated['photo'] = $request->file('photo')->store('users', 'public');
            }

            // Hash password
            $validated['password'] = bcrypt($validated['password']);

            $client = User::create($validated);

            // Log activity using custom Activity model
            Activity::create([
                'type' => 'client',
                'title' => 'Client Created',
                'description' => 'Created new client: ' . $client->name,
                'user_type' => get_class(auth()->user()),
                'user_id' => auth()->id(),
                'subject_type' => get_class($client),
                'subject_id' => $client->id,
                'properties' => null,
                'ip_address' => request()->ip()
            ]);

            DB::commit();

            return redirect()->route('admin.clients.index')
                ->with('success', 'Client created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            
            // Delete uploaded photo if exists
            if (isset($validated['photo'])) {
                Storage::disk('public')->delete($validated['photo']);
            }

            return redirect()->back()
                ->withInput()
                ->with('error', 'Error creating client: ' . $e->getMessage());
        }
    }*/

    /**
     * Display the specified resource.
     */
    public function show(User $client)
    {
        Log::info('Fetching client details', ['client_id' => $client->id]);

        try {
            // Get client statistics
            $stats = $client->getRentalStatistics();

            // Get payment history
            $payments = Payment::whereHas('contract', function ($query) use ($client) {
                $query->where('client_id', $client->id);
            })->with('contract.car')
                ->orderBy('payment_date', 'desc')
                ->take(10)
                ->get();

            // Always return JSON for API requests
            $eligibility = $client->getRentalEligibilityStatus();

            return response()->json([
                'success' => true,
                'client' => [
                    'id' => $client->id,
                    'full_name' => $client->name,
                    'email' => $client->email,
                    'phone' => $client->phone,
                    'address' => $client->address,
                    'status' => $client->status,
                    'profile_photo' => $client->photo ? asset('storage/' . $client->photo) : asset('admin/img/default-avatar.png'),
                    'active_contracts' => $client->active_contracts,
                    'overdue_contracts' => $client->overdue_contracts,
                    'total_outstanding' => $client->total_outstanding_balance,
                    'id_number' => $client->id_number ?? 'N/A',
                    'license_number' => $client->license_number ?? 'N/A',
                    'license_expiry_date' => $client->license_expiry_date ? $client->license_expiry_date->format('M d, Y') : 'N/A',
                    'license_expired' => $client->license_expiry_date && $client->license_expiry_date < now(),
                    'credit_score' => $client->credit_score,
                    'can_rent' => $eligibility['can_rent'],
                    'rental_restriction_reason' => $eligibility['reason'],
                    'created_at' => $client->created_at->format('M d, Y'),
                    'emergency_contact_name' => $client->emergency_contact_name ?? null,
                    'emergency_contact_phone' => $client->emergency_contact_phone ?? null,
                    'notes' => $client->notes ?? null,
                ],
                'stats' => $stats,
                'payments' => $payments->map(function ($payment) {
                    return [
                        'date' => $payment->payment_date->format('M d, Y h:i A'),
                        'contract_id' => str_pad($payment->contract->id, 5, '0', STR_PAD_LEFT),
                        'car' => $payment->contract->car->brand_name . ' ' . $payment->contract->car->model,
                        'amount' => number_format($payment->amount, 2) . ' MAD',
                        'method' => ucfirst($payment->payment_method),
                        'reference' => $payment->reference ?? 'N/A',
                    ];
                }),
                'contracts' => $client->contracts()->latest()->take(5)->get()->map(function ($contract) {
                    return [
                        'id' => str_pad($contract->id, 5, '0', STR_PAD_LEFT),
                        'car' => $contract->car->brand_name . ' ' . $contract->car->model,
                        'start_date' => $contract->start_date->format('M d, Y'),
                        'end_date' => $contract->end_date->format('M d, Y'),
                        'total_amount' => number_format($contract->total_amount, 2) . ' MAD',
                        'total_paid' => number_format($contract->total_paid, 2) . ' MAD',
                        'status' => ucfirst($contract->status),
                        'view_url' => route('admin.contracts.show', $contract->id),
                    ];
                }),
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching client details: ' . $e->getMessage(), [
                'client_id' => $client->id,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch client details: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $client)
    {
        return view('admin.customers.edit', compact('client'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $client)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($client)],
            'password' => 'nullable|string|min:8|confirmed',
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string',
            'id_number' => ['required', 'string', 'max:50', Rule::unique('users', 'id_number')->ignore($client)],
            'license_number' => ['required', 'string', 'max:50', Rule::unique('users', 'license_number')->ignore($client)],
            'license_expiry_date' => 'required|date',
            'photo' => 'nullable|image|max:2048',
            'status' => 'required|in:active,inactive,banned',
            'notes' => 'nullable|string',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
        ]);

        try {
            DB::beginTransaction();

            // Handle photo upload if present
            if ($request->hasFile('photo')) {
                // Delete old photo if exists
                if ($client->photo) {
                    Storage::disk('public')->delete($client->photo);
                }
                $validated['photo'] = $request->file('photo')->store('users', 'public');
            }

            // Hash password if provided
            if (!empty($validated['password'])) {
                $validated['password'] = bcrypt($validated['password']);
            } else {
                unset($validated['password']);
            }

            $client->update($validated);

            // Log activity using custom Activity model
            Activity::create([
                'type' => 'client',
                'title' => 'Client Updated',
                'description' => 'Updated client: ' . $client->name,
                'user_type' => get_class(auth()->user()),
                'user_id' => auth()->id(),
                'subject_type' => get_class($client),
                'subject_id' => $client->id,
                'properties' => null,
                'ip_address' => request()->ip()
            ]);

            DB::commit();

            return redirect()->route('admin.clients.index')
                ->with('success', 'Client updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            
            // Delete uploaded photo if exists
            if (isset($validated['photo'])) {
                Storage::disk('public')->delete($validated['photo']);
            }

            return redirect()->back()
                ->withInput()
                ->with('error', 'Error updating client: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $client)
    {
        try {
            // Check if client has active contracts
            if ($client->contracts()->where('status', 'active')->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete client with active contracts.'
                ], 400);
            }

            // Check if client has outstanding payments
            if ($client->total_outstanding_balance > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete client with outstanding balance.'
                ], 400);
            }

            DB::beginTransaction();

            // Delete photo if exists
            if ($client->photo) {
                Storage::disk('public')->delete($client->photo);
            }
            
            // Log activity using custom Activity model
            Activity::create([
                'type' => 'client',
                'title' => 'Client Deleted',
                'description' => 'Deleted client: ' . $client->name,
                'user_type' => get_class(auth()->user()),
                'user_id' => auth()->id(),
                'subject_type' => null,
                'subject_id' => null,
                'properties' => json_encode(['client_id' => $client->id, 'client_name' => $client->name]),
                'ip_address' => request()->ip()
            ]);
            
            $client->delete();

            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Client deleted successfully.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete client: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show client payments
     */
    public function payments($id)
    {
        $client = User::with(['contracts' => function ($query) {
            $query->with(['car', 'payments' => function ($q) {
                $q->orderBy('payment_date', 'desc');
            }])
            ->orderBy('created_at', 'desc');
        }])->findOrFail($id);

        $stats = [
            'total_paid' => $client->contracts->sum(function ($contract) {
                return $contract->payments->sum('amount');
            }),
            'total_outstanding' => $client->total_outstanding_balance,
            'total_contracts_value' => $client->contracts->sum('total_amount'),
        ];

        return view('admin.customers.payments', compact('client', 'stats'));
    }

    /**
     * Add payment for client contract
     */
    public function addPayment(Request $request, $clientId)
    {
        $validated = $request->validate([
            'contract_id' => 'required|exists:contracts,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,card,transfer,check',
            'payment_date' => 'required|date',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            $contract = Contract::findOrFail($validated['contract_id']);
            
            // Verify contract belongs to client
            if ($contract->client_id != $clientId) {
                throw new \Exception('Contract does not belong to this client.');
            }

            // Check if overpaying
            if ($validated['amount'] > $contract->outstanding_balance) {
                throw new \Exception('Payment amount exceeds outstanding balance.');
            }

            // Create payment
            $payment = $contract->addPayment([
                'amount' => $validated['amount'],
                'payment_method' => $validated['payment_method'],
                'payment_date' => $validated['payment_date'],
                'reference' => $validated['reference'],
                'notes' => $validated['notes'],
                'processed_by' => auth()->id()
            ]);

            // Log activity using custom Activity model
            Activity::create([
                'type' => 'payment',
                'title' => 'Payment Added',
                'description' => 'Added payment of ' . number_format($validated['amount'], 2) . ' MAD for contract #' . $contract->id,
                'user_type' => get_class(auth()->user()),
                'user_id' => auth()->id(),
                'subject_type' => get_class($contract),
                'subject_id' => $contract->id,
                'properties' => json_encode([
                    'payment_id' => $payment->id,
                    'amount' => $validated['amount'],
                    'method' => $validated['payment_method']
                ]),
                'ip_address' => request()->ip()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payment added successfully.',
                'payment' => $payment,
                'contract' => $contract->fresh(['payments'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Error adding payment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get client statistics
     */
    public function getStatistics($id)
    {
        $client = User::findOrFail($id);
        
        $stats = $client->getRentalStatistics();
        
        // Add monthly revenue chart data
        $monthlyRevenue = Contract::where('client_id', $client->id)
            ->where('created_at', '>=', now()->subMonths(12))
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, SUM(total_amount) as revenue')
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('revenue', 'month');
        
        return response()->json([
            'success' => true,
            'statistics' => $stats,
            'monthly_revenue' => $monthlyRevenue
        ]);
    }

    /**
     * Export clients data
     */
    public function export(Request $request)
    {
        return Excel::download(new ClientsExport($request->all()), 'clients-' . now()->format('Y-m-d') . '.xlsx');
    }

    /**
     * Ban a client
     */
    public function ban(User $client)
    {
        try {
            $client->update(['status' => 'banned']);
            
            // Log activity using custom Activity model
            Activity::create([
                'type' => 'client',
                'title' => 'Client Banned',
                'description' => 'Banned client: ' . $client->name,
                'user_type' => get_class(auth()->user()),
                'user_id' => auth()->id(),
                'subject_type' => get_class($client),
                'subject_id' => $client->id,
                'properties' => null,
                'ip_address' => request()->ip()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Client banned successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error banning client: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Unban a client
     */
    public function unban(User $client)
    {
        try {
            $client->update(['status' => 'active']);
            
            // Log activity using custom Activity model
            Activity::create([
                'type' => 'client',
                'title' => 'Client Unbanned',
                'description' => 'Unbanned client: ' . $client->name,
                'user_type' => get_class(auth()->user()),
                'user_id' => auth()->id(),
                'subject_type' => get_class($client),
                'subject_id' => $client->id,
                'properties' => null,
                'ip_address' => request()->ip()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Client unbanned successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error unbanning client: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send notification to client
     */
    public function sendNotification(Request $request, User $client)
    {
        $validated = $request->validate([
            'type' => 'required|in:email,sms,both',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        try {
            // Send notification based on type
            if (in_array($validated['type'], ['email', 'both'])) {
                $client->notify(new ClientNotification($validated['subject'], $validated['message']));
            }
            
            if (in_array($validated['type'], ['sms', 'both'])) {
                // Implement SMS sending logic here
                // For example: SMS::send($client->phone, $validated['message']);
            }
            
            // Log activity using custom Activity model
            Activity::create([
                'type' => 'notification',
                'title' => 'Notification Sent',
                'description' => 'Sent ' . $validated['type'] . ' notification to client: ' . $client->name,
                'user_type' => get_class(auth()->user()),
                'user_id' => auth()->id(),
                'subject_type' => get_class($client),
                'subject_id' => $client->id,
                'properties' => json_encode([
                    'type' => $validated['type'],
                    'subject' => $validated['subject']
                ]),
                'ip_address' => request()->ip()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Notification sent successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error sending notification: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get client rental history
     */
    public function rentalHistory($id)
    {
        $client = User::findOrFail($id);
        
        $contracts = Contract::where('client_id', $client->id)
            ->with(['car', 'payments'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        return view('admin.customers.rental-history', compact('client', 'contracts'));
    }

    /**
     * Generate client statement
     */
    public function generateStatement(Request $request, $id)
    {
        $client = User::findOrFail($id);
        
        $validated = $request->validate([
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
        ]);
        
        $contracts = Contract::where('client_id', $client->id)
            ->whereBetween('created_at', [$validated['from_date'], $validated['to_date']])
            ->with(['car', 'payments'])
            ->get();
        
        $payments = Payment::whereHas('contract', function ($query) use ($client) {
            $query->where('client_id', $client->id);
        })->whereBetween('payment_date', [$validated['from_date'], $validated['to_date']])
            ->with('contract')
            ->get();
        
        // Generate PDF statement
        $pdf = \PDF::loadView('admin.customers.statement', compact('client', 'contracts', 'payments', 'validated'));
        
        return $pdf->download('statement-' . $client->id . '-' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Update client credit score
     */
    public function updateCreditScore(Request $request, User $client)
    {
        $validated = $request->validate([
            'credit_score' => 'required|integer|min:0|max:100',
            'reason' => 'required|string|max:255',
        ]);
        
        try {
            $oldScore = $client->credit_score;
            $client->update(['credit_score' => $validated['credit_score']]);
            
            // Log activity using custom Activity model
            Activity::create([
                'type' => 'client',
                'title' => 'Credit Score Updated',
                'description' => 'Updated credit score from ' . $oldScore . ' to ' . $validated['credit_score'],
                'user_type' => get_class(auth()->user()),
                'user_id' => auth()->id(),
                'subject_type' => get_class($client),
                'subject_id' => $client->id,
                'properties' => json_encode([
                    'old_score' => $oldScore,
                    'new_score' => $validated['credit_score'],
                    'reason' => $validated['reason']
                ]),
                'ip_address' => request()->ip()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Credit score updated successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating credit score: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get client details for API
     */
    public function getClientDetails($id)
    {
        \Log::info('getClientDetails called with ID: ' . $id);
        try {
            $client = User::findOrFail($id);
            
            // Get client statistics
            $stats = $client->getRentalStatistics();
            
            // Get payment history
            $payments = Payment::whereHas('contract', function ($query) use ($client) {
                $query->where('client_id', $client->id);
            })->with('contract.car')
                ->orderBy('payment_date', 'desc')
                ->take(10)
                ->get();
            
            $eligibility = $client->getRentalEligibilityStatus();
            
            return response()->json([
                'success' => true,
                'client' => [
                    'id' => $client->id,
                    'full_name' => $client->name,
                    'email' => $client->email,
                    'phone' => $client->phone,
                    'address' => $client->address,
                    'status' => $client->status,
                    'profile_photo' => $client->photo ? asset('storage/' . $client->photo) : asset('admin/img/default-avatar.png'),
                    'active_contracts' => $client->active_contracts,
                    'overdue_contracts' => $client->overdue_contracts,
                    'total_outstanding' => $client->total_outstanding_balance,
                    'id_number' => $client->id_number ?? 'N/A',
                    'license_number' => $client->license_number ?? 'N/A',
                    'license_expiry_date' => $client->license_expiry_date ? $client->license_expiry_date->format('M d, Y') : 'N/A',
                    'license_expired' => $client->license_expiry_date && $client->license_expiry_date < now(),
                    'credit_score' => $client->credit_score,
                    'can_rent' => $eligibility['can_rent'],
                    'rental_restriction_reason' => $eligibility['reason'],
                    'created_at' => $client->created_at->format('M d, Y'),
                    'emergency_contact_name' => $client->emergency_contact_name ?? null,
                    'emergency_contact_phone' => $client->emergency_contact_phone ?? null,
                    'notes' => $client->notes ?? null,
                ],
                'stats' => $stats,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Client not found'
            ], 404);
        }
    }
}