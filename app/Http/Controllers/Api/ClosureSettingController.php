<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ClosureSetting;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;

class ClosureSettingController extends Controller
{
    public function index()
{
    $closureSettings = ClosureSetting::all();

    if ($closureSettings->isEmpty()) {
        return response()->json([
            'success' => false,
            'message' => 'No closure settings found',
            'data' => null
        ], 404);
    }

    return response()->json([
        'success' => true,
        'message' => 'Closure settings retrieved successfully',
        'data' => $closureSettings
    ], 200);
}

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'closureDate' => 'required|date',
                'finalclosureDate' => 'required|date|after_or_equal:closureDate',
                'academicYear' => 'required|string|max:50'
            ]);

            $closureSetting = ClosureSetting::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Closure setting created successfully',
                'data' => $closureSetting
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
        $closureSetting = ClosureSetting::find($id);

        if (!$closureSetting) {
            return response()->json([
                'success' => false,
                'message' => 'Closure setting not found',
                'data' => null
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Closure setting retrieved successfully',
            'data' => $closureSetting
        ], 200);
    }

    public function update(Request $request, $id)
    {
        try {
            $closureSetting = ClosureSetting::find($id);

            if (!$closureSetting) {
                return response()->json([
                    'success' => false,
                    'message' => 'Closure setting not found',
                    'data' => null
                ], 404);
            }

            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'closureDate' => 'required|date',
                'finalclosureDate' => 'required|date|after_or_equal:closureDate',
                'academicYear' => 'required|string|max:50'
            ]);

            $closureSetting->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Closure setting updated successfully',
                'data' => $closureSetting
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
        $closureSetting = ClosureSetting::find($id);

        if (!$closureSetting) {
            return response()->json([
                'success' => false,
                'message' => 'Closure setting not found',
                'data' => null
            ], 404);
        }

        $closureSetting->update([
            'status' => 'inactive'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Closure setting status updated to inactive successfully',
            'data' => $closureSetting->fresh()
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to update closure setting status',
            'data' => $e->getMessage()
        ], 500);
    }
}
}