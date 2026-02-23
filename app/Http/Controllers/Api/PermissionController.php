<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;

class PermissionController extends Controller
{
 public function index()
{
    $permissions = Permission::all();

    if ($permissions->isEmpty()) {
        return response()->json([
            'success' => false,
            'message' => 'No permissions found',
            'data' => []
        ], 404);
    }

    return response()->json([
        'success' => true,
        'message' => 'Permissions retrieved successfully',
        'data' => $permissions
    ], 200);
}
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'permission' => 'required|string|max:255'
            ]);

            $permission = Permission::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Permission created successfully',
                'data' => $permission
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
        $permission = Permission::find($id);

        if (!$permission) {
            return response()->json([
                'success' => false,
                'message' => 'Permission not found',
                'data' => null
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Permission retrieved successfully',
            'data' => $permission
        ], 200);
    }

    public function update(Request $request, $id)
    {
        try {
            $permission = Permission::find($id);

            if (!$permission) {
                return response()->json([
                    'success' => false,
                    'message' => 'Permission not found',
                    'data' => null
                ], 404);
            }

            $validated = $request->validate([
                'permission' => 'required|string|max:255'
            ]);

            $permission->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Permission updated successfully',
                'data' => $permission
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
            $permission = Permission::find($id);

            if (!$permission) {
                return response()->json([
                    'success' => false,
                    'message' => 'Permission not found',
                    'data' => null
                ], 404);
            }

            $permission->delete();

            return response()->json([
                'success' => true,
                'message' => 'Permission deleted successfully',
                'data' => null
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete permission',
                'data' => $e->getMessage()
            ], 500);
        }
    }
}
