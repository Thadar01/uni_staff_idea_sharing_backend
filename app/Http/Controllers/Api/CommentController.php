<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;

class CommentController extends Controller
{
    public function index()
    {
        $comments = Comment::with(['idea', 'staff'])->get();

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
        $comment = Comment::with(['idea', 'staff'])->find($id);

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
}
