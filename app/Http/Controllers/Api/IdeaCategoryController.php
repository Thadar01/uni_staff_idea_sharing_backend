<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\IdeaCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;

class IdeaCategoryController extends Controller
{
    public function index()
    {
        $ideaCategories = IdeaCategory::with(['idea', 'category'])->get();

        return response()->json([
            'success' => true,
            'message' => $ideaCategories->isEmpty() ? 'No idea-category mappings found' : 'Idea-category mappings retrieved successfully',
            'data' => $ideaCategories
        ], 200);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'ideaID' => 'required|integer|exists:idea,ideaID',
                'categoryID' => 'required|integer|exists:categories,categoryID',
            ]);

            // Prevent duplicate
            $exists = IdeaCategory::where('ideaID', $validated['ideaID'])
                ->where('categoryID', $validated['categoryID'])
                ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'This idea is already assigned to this category',
                    'data' => null
                ], 409);
            }

            $ideaCategory = IdeaCategory::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Idea assigned to category successfully',
                'data' => $ideaCategory
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
        $ideaCategory = IdeaCategory::with(['idea', 'category'])->find($id);

        if (!$ideaCategory) {
            return response()->json([
                'success' => false,
                'message' => 'Idea-category mapping not found',
                'data' => null
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Idea-category mapping retrieved successfully',
            'data' => $ideaCategory
        ], 200);
    }

    public function update(Request $request, $id)
    {
        try {
            $ideaCategory = IdeaCategory::find($id);

            if (!$ideaCategory) {
                return response()->json([
                    'success' => false,
                    'message' => 'Idea-category mapping not found',
                    'data' => null
                ], 404);
            }

            $validated = $request->validate([
                'ideaID' => 'required|integer|exists:idea,ideaID',
                'categoryID' => 'required|integer|exists:categories,categoryID',
            ]);

            // Prevent duplicate on update
            $exists = IdeaCategory::where('ideaID', $validated['ideaID'])
                ->where('categoryID', $validated['categoryID'])
                ->where('ideaCatID', '!=', $id)
                ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'This idea is already assigned to this category',
                    'data' => null
                ], 409);
            }

            $ideaCategory->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Idea-category mapping updated successfully',
                'data' => $ideaCategory
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
            $ideaCategory = IdeaCategory::find($id);

            if (!$ideaCategory) {
                return response()->json([
                    'success' => false,
                    'message' => 'Idea-category mapping not found',
                    'data' => null
                ], 404);
            }

            $ideaCategory->delete();

            return response()->json([
                'success' => true,
                'message' => 'Idea-category mapping deleted successfully',
                'data' => null
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete idea-category mapping',
                'data' => $e->getMessage()
            ], 500);
        }
    }
}
