<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Idea;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;

class IdeaController extends Controller
{
    public function index()
    {
        $ideas = Idea::with(['staff', 'closureSetting', 'categories', 'comments', 'votes'])->get();

        return response()->json([
            'success' => true,
            'message' => $ideas->isEmpty() ? 'No ideas found' : 'Ideas retrieved successfully',
            'data' => $ideas
        ], 200);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'isAnonymous' => 'nullable|boolean',
                'staffID' => 'required|integer|exists:staffs,staffID',
                'settingID' => 'required|integer|exists:closure_setting,settingID',
                'status' => 'nullable|string|in:pending,approved,rejected'
            ]);

            $idea = Idea::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Idea created successfully',
                'data' => $idea
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
        $idea = Idea::with(['staff', 'closureSetting', 'categories', 'comments', 'votes'])->find($id);

        if (!$idea) {
            return response()->json([
                'success' => false,
                'message' => 'Idea not found',
                'data' => null
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Idea retrieved successfully',
            'data' => $idea
        ], 200);
    }

    public function update(Request $request, $id)
    {
        try {
            $idea = Idea::find($id);

            if (!$idea) {
                return response()->json([
                    'success' => false,
                    'message' => 'Idea not found',
                    'data' => null
                ], 404);
            }

            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'isAnonymous' => 'nullable|boolean',
                'staffID' => 'required|integer|exists:staffs,staffID',
                'settingID' => 'required|integer|exists:closure_setting,settingID',
                'status' => 'nullable|string|in:pending,approved,rejected'
            ]);

            $idea->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Idea updated successfully',
                'data' => $idea
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
            $idea = Idea::find($id);

            if (!$idea) {
                return response()->json([
                    'success' => false,
                    'message' => 'Idea not found',
                    'data' => null
                ], 404);
            }

            $idea->delete();

            return response()->json([
                'success' => true,
                'message' => 'Idea deleted successfully',
                'data' => null
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete idea',
                'data' => $e->getMessage()
            ], 500);
        }
    }
}