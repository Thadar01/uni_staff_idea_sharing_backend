<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\CommentNotificationMail;

class CommentController extends Controller
{
    public function index()
    {
        $comments = Comment::with(['idea', 'staff'])
            ->where('status', '!=', 'hidden')
            ->get();

        return response()->json([
            'success' => true,
            'message' => $comments->isEmpty() ? 'No comments found' : 'Comments retrieved successfully',
            'data' => $comments
        ], 200);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'comment' => 'required|string',
                'isAnonymous' => 'nullable|boolean',
                'ideaID' => 'required|integer|exists:idea,ideaID',
                'staffID' => 'required|integer|exists:staffs,staffID',
            ]);

            $comment = Comment::create($validated);

            // --- Email Notification Dispatching ---
            try {
                // Find the idea and its author
                $idea = $comment->idea()->with('staff')->first();
                
                if ($idea && $idea->staff && !empty($idea->staff->staffEmail)) {
                    // Check if the commenter is NOT the author of the idea
                    if ($comment->staffID != $idea->staffID) {
                        // Load commenter details for the email
                        $comment->load('staff');
                        Mail::to($idea->staff->staffEmail)->send(new CommentNotificationMail($comment, $idea));
                    }
                }
            } catch (\Exception $e) {
                // Log failure but don't stop the comment from being created
                Log::error('Failed to send Comment notification: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Comment created successfully',
                'data' => $comment
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'data' => $e->errors()
            ], 422);

        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Database error occurred',
                'data' => $e->getMessage()
            ], 500);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unexpected error occurred',
                'data' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $comment = Comment::with(['idea', 'staff'])
            ->where('status', '!=', 'hidden')
            ->find($id);

        if (!$comment) {
            return response()->json([
                'success' => false,
                'message' => 'Comment not found',
                'data' => null
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Comment retrieved successfully',
            'data' => $comment
        ], 200);
    }

    public function update(Request $request, $id)
    {
        try {
            $comment = Comment::find($id);

            if (!$comment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Comment not found',
                    'data' => null
                ], 404);
            }

            $validated = $request->validate([
                'comment' => 'required|string',
                'isAnonymous' => 'nullable|boolean',
                'ideaID' => 'required|integer|exists:idea,ideaID',
                'staffID' => 'required|integer|exists:staffs,staffID',
            ]);

            $comment->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Comment updated successfully',
                'data' => $comment
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'data' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unexpected error occurred',
                'data' => $e->getMessage()
            ], 500);
        }
    }

  public function destroy($id)
{
    try {
        $comment = Comment::find($id);

        if (!$comment) {
            return response()->json([
                'success' => false,
                'message' => 'Comment not found',
                'data' => null
            ], 404);
        }

        $comment->status = 'deleted';
        $comment->save();

        return response()->json([
            'success' => true,
            'message' => 'Comment deleted successfully',
            'data' => $comment
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to delete comment',
            'data' => $e->getMessage()
        ], 500);
        }
    }

    public function hide($id)
    {
        try {
            $comment = Comment::find($id);

            if (!$comment || $comment->status === 'deleted') {
                return response()->json([
                    'success' => false,
                    'message' => 'Comment not found',
                    'data' => null
                ], 404);
            }

            $comment->status = 'hidden';
            $comment->save();

            return response()->json([
                'success' => true,
                'message' => 'Comment hidden successfully',
                'data' => $comment
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to hide comment',
                'data' => $e->getMessage()
            ], 500);
        }
    }

    public function unhide($id)
    {
        try {
            $comment = Comment::find($id);

            if (!$comment || $comment->status === 'deleted') {
                return response()->json([
                    'success' => false,
                    'message' => 'Comment not found',
                    'data' => null
                ], 404);
            }

            $comment->status = 'active';
            $comment->save();

            return response()->json([
                'success' => true,
                'message' => 'Comment unhidden successfully',
                'data' => $comment
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to unhide comment',
                'data' => $e->getMessage()
            ], 500);
        }
    }
}
