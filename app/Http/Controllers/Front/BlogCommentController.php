<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\BlogComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Validator;

class BlogCommentController extends Controller
{
    /**
     * Store a newly created comment via AJAX.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $slug
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $slug)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:100',
            'website' => 'nullable|url|max:255',
            'content' => 'required|string|min:5',
            'save_info' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Find the post
        $post = BlogPost::where('slug', $slug)
            ->where('is_published', true)
            ->where('published_at', '<=', now())
            ->firstOrFail();

        // Create the comment
        $comment = new BlogComment();
        $comment->post_id = $post->id;
        $comment->name = $request->name;
        $comment->email = $request->email;
        $comment->website = $request->website;
        $comment->content = $request->content;
        $comment->ip_address = $request->ip();
        $comment->user_agent = $request->userAgent();
        $comment->is_approved = config('blog.auto_approve_comments', true);
        
        // If user is logged in, associate the comment with the user
        if (Auth::check()) {
            $comment->user_id = Auth::id();
        }
        
        $comment->save();

        // Save commenter information in cookies if requested
        if ($request->save_info) {
            Cookie::queue('commenter_name', $request->name, 43200); // 30 days
            Cookie::queue('commenter_email', $request->email, 43200); // 30 days
            Cookie::queue('commenter_website', $request->website, 43200); // 30 days
        }

        // Prepare response data with comment details
        $commentData = [
            'id' => $comment->id,
            'name' => $comment->name,
            'content' => $comment->content,
            'formattedDate' => $comment->formatted_date,
            'avatar' => $comment->avatar,
            'initials' => $comment->initials,
        ];

        // Return success response with comment data
        return response()->json([
            'success' => true,
            'message' => 'Your comment has been submitted successfully' . 
                ($comment->is_approved ? '.' : ' and is awaiting moderation.'),
            'comment' => $commentData,
            'isApproved' => $comment->is_approved
        ]);
    }

    /**
     * Store a reply to a comment via AJAX.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $slug
     * @param  int  $commentId
     * @return \Illuminate\Http\Response
     */
    public function reply(Request $request, $slug, $commentId)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:100',
            'website' => 'nullable|url|max:255',
            'content' => 'required|string|min:5',
            'save_info' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Find the post
        $post = BlogPost::where('slug', $slug)
            ->where('is_published', true)
            ->where('published_at', '<=', now())
            ->firstOrFail();

        // Find the parent comment
        $parentComment = BlogComment::where('id', $commentId)
            ->where('post_id', $post->id)
            ->where('is_approved', true)
            ->whereNull('parent_id')
            ->firstOrFail();

        // Create the reply
        $reply = new BlogComment();
        $reply->post_id = $post->id;
        $reply->parent_id = $parentComment->id;
        $reply->name = $request->name;
        $reply->email = $request->email;
        $reply->website = $request->website;
        $reply->content = $request->content;
        $reply->ip_address = $request->ip();
        $reply->user_agent = $request->userAgent();
        $reply->is_approved = config('blog.auto_approve_comments', true);
        
        // If user is logged in, associate the reply with the user
        if (Auth::check()) {
            $reply->user_id = Auth::id();
        }
        
        $reply->save();

        // Save commenter information in cookies if requested
        if ($request->save_info) {
            Cookie::queue('commenter_name', $request->name, 43200); // 30 days
            Cookie::queue('commenter_email', $request->email, 43200); // 30 days
            Cookie::queue('commenter_website', $request->website, 43200); // 30 days
        }

        // Prepare response data with reply details
        $replyData = [
            'id' => $reply->id,
            'name' => $reply->name,
            'content' => $reply->content,
            'formattedDate' => $reply->formatted_date,
            'avatar' => $reply->avatar,
            'initials' => $reply->initials,
            'parentId' => $parentComment->id
        ];

        // Return success response with reply data
        return response()->json([
            'success' => true,
            'message' => 'Your reply has been submitted successfully' . 
                ($reply->is_approved ? '.' : ' and is awaiting moderation.'),
            'reply' => $replyData,
            'isApproved' => $reply->is_approved
        ]);
    }

    /**
     * Get comments for a blog post with pagination.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $slug
     * @return \Illuminate\Http\Response
     */
    public function getComments(Request $request, $slug)
    {
        // Find the post
        $post = BlogPost::where('slug', $slug)
            ->where('is_published', true)
            ->where('published_at', '<=', now())
            ->firstOrFail();

        // Get page number and comments per page
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 10);

        // Get top-level comments with pagination
        $comments = BlogComment::with(['replies'])
            ->where('post_id', $post->id)
            ->whereNull('parent_id')
            ->approved()
            ->newest()
            ->paginate($perPage, ['*'], 'page', $page);

        // Format comments for response
        $formattedComments = $comments->map(function($comment) {
            $formattedComment = [
                'id' => $comment->id,
                'name' => $comment->name,
                'content' => $comment->content,
                'formattedDate' => $comment->formatted_date,
                'avatar' => $comment->avatar,
                'initials' => $comment->initials,
                'hasReplies' => $comment->replies->count() > 0,
            ];

            // Format replies
            if ($comment->replies->count() > 0) {
                $formattedComment['replies'] = $comment->replies->map(function($reply) {
                    return [
                        'id' => $reply->id,
                        'name' => $reply->name,
                        'content' => $reply->content,
                        'formattedDate' => $reply->formatted_date,
                        'avatar' => $reply->avatar,
                        'initials' => $reply->initials,
                    ];
                });
            }

            return $formattedComment;
        });

        // Return paginated comments
        return response()->json([
            'success' => true,
            'comments' => $formattedComments,
            'pagination' => [
                'total' => $comments->total(),
                'per_page' => $comments->perPage(),
                'current_page' => $comments->currentPage(),
                'last_page' => $comments->lastPage(),
                'from' => $comments->firstItem(),
                'to' => $comments->lastItem(),
                'has_more_pages' => $comments->hasMorePages()
            ]
        ]);
    }
}