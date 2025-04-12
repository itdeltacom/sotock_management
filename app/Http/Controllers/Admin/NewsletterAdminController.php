<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Newsletter;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use App\Mail\NewsletterMail;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class NewsletterAdminController extends Controller
{
    /**
     * Display a listing of all newsletters.
     */
    public function index()
    {
        return view('admin.newsletters.index');
    }
    
    /**
     * Get newsletter data for DataTables.
     */
    public function data(Request $request)
    {
        $query = Newsletter::query()->with('createdBy');
        
        // Apply filters if provided
        if ($request->has('status') && !empty($request->status)) {
            $query->where('status', $request->status);
        }
        
        $newsletters = $query->orderBy('created_at', 'desc')->get();
        
        return DataTables::of($newsletters)
            ->addColumn('action', function (Newsletter $newsletter) {
                $actions = '';
                
                // View button
                $actions .= '<button type="button" class="btn btn-sm btn-info me-1 btn-view" data-id="'.$newsletter->id.'" title="View">
                    <i class="fas fa-eye"></i>
                </button> ';
                
                // Edit button if not sent
                if ($newsletter->status !== Newsletter::STATUS_SENT) {
                    $actions .= '<button type="button" class="btn btn-sm btn-primary me-1 btn-edit" data-id="'.$newsletter->id.'" title="Edit">
                        <i class="fas fa-edit"></i>
                    </button> ';
                }
                
                // Send button for drafts
                if ($newsletter->status === Newsletter::STATUS_DRAFT) {
                    $actions .= '<button type="button" class="btn btn-sm btn-success me-1 btn-send" data-id="'.$newsletter->id.'" title="Send Now">
                        <i class="fas fa-paper-plane"></i>
                    </button> ';
                }
                
                // Delete button
                $actions .= '<button type="button" class="btn btn-sm btn-danger btn-delete" data-id="'.$newsletter->id.'" data-subject="'.$newsletter->subject.'" title="Delete">
                    <i class="fas fa-trash"></i>
                </button>';
                
                return $actions;
            })
            ->addColumn('status_label', function (Newsletter $newsletter) {
                switch ($newsletter->status) {
                    case Newsletter::STATUS_DRAFT:
                        return '<span class="badge bg-secondary">Draft</span>';
                    case Newsletter::STATUS_SCHEDULED:
                        return '<span class="badge bg-primary">Scheduled</span>';
                    case Newsletter::STATUS_SENDING:
                        return '<span class="badge bg-warning">Sending</span>';
                    case Newsletter::STATUS_SENT:
                        return '<span class="badge bg-success">Sent</span>';
                    case Newsletter::STATUS_FAILED:
                        return '<span class="badge bg-danger">Failed</span>';
                    default:
                        return '<span class="badge bg-secondary">'.$newsletter->status.'</span>';
                }
            })
            ->addColumn('created_by', function (Newsletter $newsletter) {
                return $newsletter->createdBy ? $newsletter->createdBy->name : 'System';
            })
            ->addColumn('attachment_link', function (Newsletter $newsletter) {
                if ($newsletter->attachment) {
                    return '<a href="'.Storage::url($newsletter->attachment).'" target="_blank" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-paperclip"></i> View
                    </a>';
                }
                return '-';
            })
            ->addColumn('scheduled_for_formatted', function (Newsletter $newsletter) {
                return $newsletter->scheduled_for ? $newsletter->scheduled_for->format('M d, Y H:i') : '-';
            })
            ->addColumn('sent_at_formatted', function (Newsletter $newsletter) {
                return $newsletter->sent_at ? $newsletter->sent_at->format('M d, Y H:i') : '-';
            })
            ->rawColumns(['action', 'status_label', 'attachment_link'])
            ->make(true);
    }
    
    /**
     * Display the subscribers page.
     */
    public function subscribers()
    {
        return view('admin.newsletters.subscribers');
    }
    
    /**
     * Get subscribers data for DataTables.
     */
    public function subscribersData(Request $request)
    {
        $query = NewsletterSubscriber::query();
        
        // Apply filters if provided
        if ($request->has('status')) {
            if ($request->status === 'active') {
                $query->active();
            } elseif ($request->status === 'unconfirmed') {
                $query->unconfirmed();
            } elseif ($request->status === 'unsubscribed') {
                $query->unsubscribed();
            }
        }
        
        $subscribers = $query->orderBy('created_at', 'desc')->get();
        
        return DataTables::of($subscribers)
            ->addColumn('action', function (NewsletterSubscriber $subscriber) {
                $actions = '';
                
                // Toggle active status
                if ($subscriber->is_active) {
                    $actions .= '<button type="button" class="btn btn-sm btn-warning me-1 btn-deactivate" data-id="'.$subscriber->id.'" data-email="'.$subscriber->email.'" title="Deactivate">
                        <i class="fas fa-ban"></i>
                    </button> ';
                } else {
                    $actions .= '<button type="button" class="btn btn-sm btn-success me-1 btn-activate" data-id="'.$subscriber->id.'" data-email="'.$subscriber->email.'" title="Activate">
                        <i class="fas fa-check"></i>
                    </button> ';
                }
                
                // Resend confirmation
                if (!$subscriber->confirmed_at) {
                    $actions .= '<button type="button" class="btn btn-sm btn-primary me-1 btn-resend" data-id="'.$subscriber->id.'" data-email="'.$subscriber->email.'" title="Resend Confirmation">
                        <i class="fas fa-envelope"></i>
                    </button> ';
                }
                
                // Delete button
                $actions .= '<button type="button" class="btn btn-sm btn-danger btn-delete-subscriber" data-id="'.$subscriber->id.'" data-email="'.$subscriber->email.'" title="Delete">
                    <i class="fas fa-trash"></i>
                </button>';
                
                return $actions;
            })
            ->addColumn('status', function (NewsletterSubscriber $subscriber) {
                if ($subscriber->unsubscribed_at) {
                    return '<span class="badge bg-danger">Unsubscribed</span>';
                } elseif (!$subscriber->confirmed_at) {
                    return '<span class="badge bg-warning">Unconfirmed</span>';
                } elseif ($subscriber->is_active) {
                    return '<span class="badge bg-success">Active</span>';
                } else {
                    return '<span class="badge bg-secondary">Inactive</span>';
                }
            })
            ->addColumn('subscribed_date', function (NewsletterSubscriber $subscriber) {
                return $subscriber->created_at->format('M d, Y H:i');
            })
            ->addColumn('confirmed_date', function (NewsletterSubscriber $subscriber) {
                return $subscriber->confirmed_at ? $subscriber->confirmed_at->format('M d, Y H:i') : '-';
            })
            ->addColumn('unsubscribed_date', function (NewsletterSubscriber $subscriber) {
                return $subscriber->unsubscribed_at ? $subscriber->unsubscribed_at->format('M d, Y H:i') : '-';
            })
            ->addColumn('last_email_date', function (NewsletterSubscriber $subscriber) {
                return $subscriber->last_email_sent_at ? $subscriber->last_email_sent_at->format('M d, Y H:i') : '-';
            })
            ->rawColumns(['action', 'status'])
            ->make(true);
    }
    
    /**
     * Show the form for creating a new newsletter.
     */
    public function create()
    {
        return view('admin.newsletters.create');
    }
    
    /**
     * Store a newly created newsletter in storage.
     */
    public function store(Request $request)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
            'attachment' => 'nullable|file|max:10240', // 10MB limit
            'schedule_for' => 'nullable|date|after:now',
            'send_now' => 'nullable|boolean',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        // Create new newsletter
        $newsletter = new Newsletter();
        $newsletter->subject = $request->subject;
        $newsletter->content = $request->content;
        $newsletter->status = Newsletter::STATUS_DRAFT;
        $newsletter->created_by = Auth::guard('admin')->id();
        
        // Handle scheduled date
        if ($request->has('schedule_for') && $request->schedule_for) {
            $newsletter->scheduled_for = Carbon::parse($request->schedule_for);
            $newsletter->status = Newsletter::STATUS_SCHEDULED;
        }
        
        // Handle attachment
        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('newsletters/attachments', 'public');
            $newsletter->attachment = $path;
        }
        
        $newsletter->save();
        
        // Send immediately if requested
        if ($request->has('send_now') && $request->send_now) {
            $this->sendNewsletter($newsletter);
            
            return response()->json([
                'success' => true,
                'message' => 'Newsletter created and sent successfully!',
                'redirect' => route('admin.newsletters.index')
            ]);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Newsletter created successfully!',
            'redirect' => route('admin.newsletters.index')
        ]);
    }
    
    /**
     * Display the specified newsletter.
     */
    public function show(Newsletter $newsletter)
    {
        $newsletter->load('createdBy');
        
        if ($newsletter->attachment) {
            $newsletter->attachment_url = Storage::url($newsletter->attachment);
        }
        
        return response()->json([
            'success' => true,
            'newsletter' => $newsletter
        ]);
    }
    
    /**
     * Show the form for editing the specified newsletter.
     */
    public function edit(Newsletter $newsletter)
    {
        // Prevent editing sent newsletters
        if ($newsletter->status === Newsletter::STATUS_SENT) {
            return redirect()->route('admin.newsletters.index')
                ->with('error', 'Sent newsletters cannot be edited.');
        }
        
        // Pass newsletter data to view
        return view('admin.newsletters.edit', compact('newsletter'));
    }
    
    /**
     * Update the specified newsletter in storage.
     */
    public function update(Request $request, Newsletter $newsletter)
    {
        // Prevent updating sent newsletters
        if ($newsletter->status === Newsletter::STATUS_SENT) {
            return response()->json([
                'success' => false,
                'message' => 'Sent newsletters cannot be updated.'
            ], 422);
        }
        
        // Validate input
        $validator = Validator::make($request->all(), [
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
            'attachment' => 'nullable|file|max:10240', // 10MB limit
            'schedule_for' => 'nullable|date|after:now',
            'send_now' => 'nullable|boolean',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        // Update newsletter
        $newsletter->subject = $request->subject;
        $newsletter->content = $request->content;
        
        // Handle scheduled date
        if ($request->has('schedule_for') && $request->schedule_for) {
            $newsletter->scheduled_for = Carbon::parse($request->schedule_for);
            $newsletter->status = Newsletter::STATUS_SCHEDULED;
        } else {
            $newsletter->scheduled_for = null;
            if ($newsletter->status === Newsletter::STATUS_SCHEDULED) {
                $newsletter->status = Newsletter::STATUS_DRAFT;
            }
        }
        
        // Handle attachment
        if ($request->hasFile('attachment')) {
            // Delete old attachment if exists
            if ($newsletter->attachment) {
                Storage::disk('public')->delete($newsletter->attachment);
            }
            
            $path = $request->file('attachment')->store('newsletters/attachments', 'public');
            $newsletter->attachment = $path;
        }
        
        $newsletter->save();
        
        // Send immediately if requested
        if ($request->has('send_now') && $request->send_now) {
            $this->sendNewsletter($newsletter);
            
            return response()->json([
                'success' => true,
                'message' => 'Newsletter updated and sent successfully!',
                'redirect' => route('admin.newsletters.index')
            ]);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Newsletter updated successfully!',
            'redirect' => route('admin.newsletters.index')
        ]);
    }
    
    /**
     * Remove the specified newsletter from storage.
     */
    public function destroy(Newsletter $newsletter)
    {
        // Delete attachment if exists
        if ($newsletter->attachment) {
            Storage::disk('public')->delete($newsletter->attachment);
        }
        
        // Delete the newsletter
        $newsletter->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Newsletter deleted successfully!'
        ]);
    }
    
    /**
     * Send newsletter immediately.
     */
    public function send(Newsletter $newsletter)
    {
        // Prevent sending already sent newsletters
        if ($newsletter->status === Newsletter::STATUS_SENT) {
            return response()->json([
                'success' => false,
                'message' => 'This newsletter has already been sent.'
            ], 422);
        }
        
        $result = $this->sendNewsletter($newsletter);
        
        if ($result) {
            return response()->json([
                'success' => true,
                'message' => 'Newsletter sent successfully!'
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send newsletter. Please try again.'
            ], 500);
        }
    }
    
    /**
     * Delete a subscriber.
     */
    public function deleteSubscriber(NewsletterSubscriber $subscriber)
    {
        $subscriber->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Subscriber deleted successfully!'
        ]);
    }
    
    /**
     * Toggle subscriber active status.
     */
    public function toggleSubscriberStatus(NewsletterSubscriber $subscriber)
    {
        $subscriber->is_active = !$subscriber->is_active;
        $subscriber->save();
        
        $status = $subscriber->is_active ? 'activated' : 'deactivated';
        
        return response()->json([
            'success' => true,
            'message' => "Subscriber {$status} successfully!",
            'is_active' => $subscriber->is_active
        ]);
    }
    
    /**
     * Resend confirmation email to subscriber.
     */
    public function resendConfirmation(NewsletterSubscriber $subscriber)
    {
        // Generate new token
        $subscriber->confirmation_token = \Illuminate\Support\Str::random(60);
        $subscriber->save();
        
        // Send confirmation email
        Mail::to($subscriber->email)->send(new \App\Mail\NewsletterConfirmation($subscriber));
        
        return response()->json([
            'success' => true,
            'message' => 'Confirmation email resent successfully!'
        ]);
    }
    
    /**
     * Import subscribers from CSV.
     */
    public function importSubscribers(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:2048'
        ]);
        
        $file = $request->file('csv_file');
        $path = $file->getRealPath();
        
        $data = array_map('str_getcsv', file($path));
        
        // Skip header row if exists
        if (isset($data[0][0]) && stripos($data[0][0], 'email') !== false) {
            array_shift($data);
        }
        
        $imported = 0;
        $skipped = 0;
        
        foreach ($data as $row) {
            $email = trim($row[0]);
            
            // Skip if email is invalid
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $skipped++;
                continue;
            }
            // Skip if email already exists
            $exists = NewsletterSubscriber::where('email', $email)->exists();
            if ($exists) {
                $skipped++;
                continue;
            }
            
            // Create new subscriber (already confirmed)
            NewsletterSubscriber::create([
                'email' => $email,
                'is_active' => true,
                'confirmed_at' => now(),
            ]);
            
            $imported++;
        }
        
        return response()->json([
            'success' => true,
            'message' => "{$imported} subscribers imported successfully. {$skipped} entries skipped."
        ]);
    }
    
    /**
     * Export subscribers to CSV.
     */
    public function exportSubscribers(Request $request)
    {
        $status = $request->input('status', 'all');
        
        $query = NewsletterSubscriber::query();
        
        // Filter by status
        if ($status === 'active') {
            $query->active();
        } elseif ($status === 'unconfirmed') {
            $query->unconfirmed();
        } elseif ($status === 'unsubscribed') {
            $query->unsubscribed();
        }
        
        $subscribers = $query->get();
        
        // Generate CSV
        $filename = 'subscribers_' . date('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];
        
        $callback = function() use ($subscribers) {
            $file = fopen('php://output', 'w');
            
            // Add headers
            fputcsv($file, ['Email', 'Status', 'Subscribed Date', 'Confirmed Date', 'Unsubscribed Date']);
            
            // Add rows
            foreach ($subscribers as $subscriber) {
                $status = $subscriber->unsubscribed_at ? 'Unsubscribed' : 
                    (!$subscriber->confirmed_at ? 'Unconfirmed' : 
                    ($subscriber->is_active ? 'Active' : 'Inactive'));
                
                fputcsv($file, [
                    $subscriber->email,
                    $status,
                    $subscriber->created_at->format('Y-m-d H:i:s'),
                    $subscriber->confirmed_at ? $subscriber->confirmed_at->format('Y-m-d H:i:s') : '',
                    $subscriber->unsubscribed_at ? $subscriber->unsubscribed_at->format('Y-m-d H:i:s') : '',
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
    
    /**
     * Helper method to send a newsletter.
     */
    private function sendNewsletter(Newsletter $newsletter)
    {
        try {
            // Update newsletter status
            $newsletter->status = Newsletter::STATUS_SENDING;
            $newsletter->save();
            
            // Get active subscribers
            $subscribers = NewsletterSubscriber::active()->get();
            
            // Skip if no subscribers
            if ($subscribers->isEmpty()) {
                $newsletter->status = Newsletter::STATUS_SENT;
                $newsletter->sent_at = now();
                $newsletter->recipients_count = 0;
                $newsletter->save();
                
                return true;
            }
            
            // Send emails in batches
            $count = 0;
            foreach ($subscribers as $subscriber) {
                Mail::to($subscriber->email)
                    ->send(new NewsletterMail($newsletter, $subscriber));
                
                // Update subscriber
                $subscriber->last_email_sent_at = now();
                $subscriber->save();
                
                $count++;
                
                // Add small delay to prevent overwhelming mail server
                usleep(100000); // 0.1 seconds
            }
            
            // Update newsletter status
            $newsletter->status = Newsletter::STATUS_SENT;
            $newsletter->sent_at = now();
            $newsletter->recipients_count = $count;
            $newsletter->save();
            
            return true;
        } catch (\Exception $e) {
            // Log error
            \Log::error('Failed to send newsletter: ' . $e->getMessage());
            
            // Update newsletter status
            $newsletter->status = Newsletter::STATUS_FAILED;
            $newsletter->save();
            
            return false;
        }
    }
}
            