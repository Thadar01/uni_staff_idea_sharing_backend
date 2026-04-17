<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;

class CategoryController extends Controller
{
   public function index()
{
    $categories = Category::all();

    if ($categories->isEmpty()) {
        return response()->json([
            'success' => false,
            'message' => 'No categories found',
            'data' => []
        ], 404);
    }

    return response()->json([
        'success' => true,
        'message' => 'Categories retrieved successfully',
        'data' => $categories
    ], 200);
}

   public function store(Request $request)
{
    try {
        // Validate request
        $validated = $request->validate([
            'categories' => 'required|array|min:1',
            'categories.*' => 'required|string|max:255|distinct'
        ]);

        $categoriesInput = $validated['categories'];

        $createdCategories = [];
        $skippedCategories = [];

        foreach ($categoriesInput as $name) {
            // Check if category already exists
            $exists = Category::where('categoryname', $name)->exists();
            if ($exists) {
                $skippedCategories[] = $name;
                continue;
            }

            $createdCategories[] = Category::create(['categoryname' => $name]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Categories processed successfully',
            'created' => $createdCategories,
            'skipped' => $skippedCategories
        ], 201);

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

    public function show($id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found',
                'data' => null
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Category retrieved successfully',
            'data' => $category
        ], 200);
    }

    public function update(Request $request, $id)
    {
        try {
            $category = Category::find($id);

            if (!$category) {
                return response()->json([
                    'success' => false,
                    'message' => 'Category not found',
                    'data' => null
                ], 404);
            }

            $validated = $request->validate([
                'categoryname' => 'required|string|max:255|unique:categories,categoryname,' 
                    . $id . ',categoryID'
            ]);

            $category->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Category updated successfully',
                'data' => $category
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
        $category = Category::find($id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found',
                'data' => null
            ], 404);
        }

        // --- NEW CONSTRAINT: Check for active usage ---
        $hasActiveIdeas = $category->ideas()->whereHas('closureSetting', function ($query) {
            // An idea is considered active if the final closure date has not passed yet
            $query->where('finalclosureDate', '>=', now()->format('Y-m-d'));
        })->exists();

        if ($hasActiveIdeas) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot deactivate category. It is currently being used by ideas in an active academic cycle.',
                'data' => null
            ], 403);
        }
        // ----------------------------------------------

        // Soft delete by setting status to 'inactive'
        $category->status = 'inactive';
        $category->save();

        return response()->json([
            'success' => true,
            'message' => 'Category marked as inactive successfully',
            'data' => $category
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to update category status',
            'data' => $e->getMessage()
        ], 500);
    }
}
}