<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogComment;
use App\Models\BlogPost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class BlogCommentController extends Controller
{
    /**
     * Display a listing of the blog comments.
     */
    public function index()
    {
        return view('admin.blogs.comments');
    }

    /**
     * Get blog comments data for DataTables.
     */
    public function data(Request $request)
    {
        $query = BlogComment::with(['post', 'user', 'parent'])
            ->select('blog_comments.*');
            
        // Apply filters if provided
        if ($request->has('post_id') && $request->post_id) {
            $query->where('post_id', $request->post_id);
        }
        
        if ($request->has('status')) {
            if ($request->status === 'approved') {
                $query->where('is_approved', true);
            } elseif ($request->status === 'pending') {
                $query->where('is_approved', false);
            }
        }
        
        if ($request->has('parent')) {
            if ($request->parent === 'comments') {
                $query->whereNull('parent_id');
            } elseif ($request->parent === 'replies') {
                $query->whereNotNull('parent_id');
            }
        }
        
        $comments = $query->get();
        
        return DataTables::of($comments)
            ->addColumn('action', function (BlogComment $comment) {
                $actions = '';
                
                // View button
                $actions .= '<button type="button" class="btn btn-sm btn-info me-1 btn-view" data-id="'.$comment->id.'" title="View">
                    <i class="fas fa-eye"></i>
                </button> ';
                
                // Only show edit button if user has permission
                if (Auth::guard('admin')->user()->can('edit blog comments')) {
                    $actions .= '<button type="button" class="btn btn-sm btn-primary me-1 btn-edit" data-id="'.$comment->id.'" title="Edit">
                        <i class="fas fa-edit"></i>
                    </button> ';
                }
                
                // Only show delete button if user has permission
                if (Auth::guard('admin')->user()->can('delete blog comments')) {
                    $actions .= '<button type="button" class="btn btn-sm btn-danger btn-delete" data-id="'.$comment->id.'" data-name="'.$comment->name.'" title="Delete">
                        <i class="fas fa-trash"></i>
                    </button>';
                }
                
                return $actions;
            })
            ->addColumn('status', function (BlogComment $comment) {
                if ($comment->is_approved) {
                    return '<span class="badge bg-success">Approved</span>';
                } else {
                    return '<span class="badge bg-warning">Pending</span>';
                }
            })
            ->addColumn('post_title', function (BlogComment $comment) {
                return '<a href="'.route('blog.show', $comment->post->slug).'" target="_blank">'.$comment->post->title.'</a>';
            })
            ->addColumn('author', function (BlogComment $comment) {
                $nameDisplay = $comment->name;
                if ($comment->user) {
                    $nameDisplay .= ' <span class="badge bg-info">Registered</span>';
                }
                return $nameDisplay . '<br><small>'.$comment->email.'</small>';
            })
            ->addColumn('type', function (BlogComment $comment) {
                if ($comment->parent_id) {
                    return '<span class="badge bg-secondary">Reply</span>';
                } else {
                    return '<span class="badge bg-primary">Comment</span>';
                }
            })
            ->addColumn('content_excerpt', function (BlogComment $comment) {
                return \Illuminate\Support\Str::limit(strip_tags($comment->content), 100);
            })
            ->addColumn('date', function (BlogComment $comment) {
                return $comment->created_at->format('M d, Y H:i');
            })
            ->addColumn('approval_actions', function (BlogComment $comment) {
                if (!Auth::guard('admin')->user()->can('edit blog comments')) {
                    return '';
                }
                
                if ($comment->is_approved) {
                    return '<button type="button" class="btn btn-sm btn-warning btn-reject" data-id="'.$comment->id.'" title="Reject">
                        <i class="fas fa-ban"></i> Reject
                    </button>';
                } else {
                    return '<button type="button" class="btn btn-sm btn-success btn-approve" data-id="'.$comment->id.'" title="Approve">
                        <i class="fas fa-check"></i> Approve
                    </button>';
                }
            })
            ->rawColumns(['action', 'status', 'post_title', 'author', 'type', 'approval_actions'])
            ->make(true);
    }

    /**
     * Get comments for a specific post (for dropdown in admin panel).
     */
    public function getByPost(BlogPost $post)
    {
        $comments = $post->comments()
            ->whereNull('parent_id')
            ->where('is_approved', true)
            ->select(['id', 'name', 'content', 'created_at'])
            ->get()
            ->map(function($comment) {
                $comment->content_excerpt = \Illuminate\Support\Str::limit(strip_tags($comment->content), 50);
                return $comment;
            });
        
        return response()->json([
            'success' => true,
            'comments' => $comments
        ]);
    }

    /**
     * Store a newly created comment in storage.
     */
    public function store(Request $request)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'post_id' => 'required|exists:blog_posts,id',
            'parent_id' => 'nullable|exists:blog_comments,id',
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:100',
            'website' => 'nullable|url|max:255',
            'content' => 'required|string',
            'is_approved' => 'boolean',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Create new comment
        $comment = new BlogComment([
            'post_id' => $request->post_id,
            'parent_id' => $request->parent_id,
            'name' => $request->name,
            'email' => $request->email,
            'website' => $request->website,
            'content' => $request->content,
            'is_approved' => $request->is_approved ?? true, // Default to approved in admin panel
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);
        
        // If comment as admin checked, and user is logged in, associate with admin user
        if ($request->has('comment_as_admin') && Auth::guard('admin')->check()) {
            $admin = Auth::guard('admin')->user();
            $comment->user_id = $admin->id;
        }
        
        $comment->save();
        
        // Log activity if spatie activity-log package is installed
        if (method_exists(app(), 'activity')) {
            activity()
                ->causedBy(Auth::guard('admin')->user())
                ->performedOn($comment)
                ->withProperties(['comment_id' => $comment->id])
                ->log('Created blog comment');
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Comment created successfully.',
            'comment' => $comment
        ]);
    }

    /**
     * Display the specified comment.
     */
    public function show(BlogComment $comment)
    {
        $comment->load(['post', 'user', 'parent']);
        
        return response()->json([
            'success' => true,
            'comment' => $comment
        ]);
    }

    /**
     * Update the specified comment in storage.
     */
    public function update(Request $request, BlogComment $comment)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'post_id' => 'required|exists:blog_posts,id',
            'parent_id' => 'nullable|exists:blog_comments,id',
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:100',
            'website' => 'nullable|url|max:255',
            'content' => 'required|string',
            'is_approved' => 'boolean',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Update comment
        $comment->update([
            'post_id' => $request->post_id,
            'parent_id' => $request->parent_id,
            'name' => $request->name,
            'email' => $request->email,
            'website' => $request->website,
            'content' => $request->content,
            'is_approved' => $request->is_approved
        ]);

        // Log activity if spatie activity-log package is installed
        if (method_exists(app(), 'activity')) {
            activity()
                ->causedBy(Auth::guard('admin')->user())
                ->performedOn($comment)
                ->withProperties(['comment_id' => $comment->id])
                ->log('Updated blog comment');
        }

        return response()->json([
            'success' => true,
            'message' => 'Comment updated successfully.',
            'comment' => $comment
        ]);
    }

    /**
     * Remove the specified comment from storage.
     */
    public function destroy(BlogComment $comment)
    {
        // Store data for activity log
        $commentData = [
            'id' => $comment->id,
            'post_id' => $comment->post_id,
            'author' => $comment->name
        ];

        // Delete all replies if this is a parent comment
        if (!$comment->parent_id) {
            $comment->allReplies()->delete();
        }
        
        // Delete the comment
        $comment->delete();

        // Log activity if spatie activity-log package is installed
        if (method_exists(app(), 'activity')) {
            activity()
                ->causedBy(Auth::guard('admin')->user())
                ->withProperties($commentData)
                ->log('Deleted blog comment');
        }

        return response()->json([
            'success' => true,
            'message' => 'Comment deleted successfully.'
        ]);
    }

    /**
     * Approve a comment.
     */
    public function approve(BlogComment $comment)
    {
        $comment->update(['is_approved' => true]);

        // Log activity if spatie activity-log package is installed
        if (method_exists(app(), 'activity')) {
            activity()
                ->causedBy(Auth::guard('admin')->user())
                ->performedOn($comment)
                ->withProperties(['comment_id' => $comment->id])
                ->log('Approved blog comment');
        }

        return response()->json([
            'success' => true,
            'message' => 'Comment approved successfully.'
        ]);
    }

    /**
     * Reject a comment.
     */
    public function reject(BlogComment $comment)
    {
        $comment->update(['is_approved' => false]);

        // Log activity if spatie activity-log package is installed
        if (method_exists(app(), 'activity')) {
            activity()
                ->causedBy(Auth::guard('admin')->user())
                ->performedOn($comment)
                ->withProperties(['comment_id' => $comment->id])
                ->log('Rejected blog comment');
        }

        return response()->json([
            'success' => true,
            'message' => 'Comment rejected successfully.'
        ]);
    }
}