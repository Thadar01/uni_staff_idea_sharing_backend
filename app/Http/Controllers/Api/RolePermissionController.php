<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\RolePermission;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;

class RolePermissionController extends Controller
{
    public function index()
    {
        return response()->json([
            'success' => true,
            'message' => 'Role permissions retrieved successfully',
            'data' => RolePermission::all()
        ], 200);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'roleID' => 'required|integer|exists:roles,roleID',
                'permissionID' => 'required|integer|exists:permissions,permissionID'
            ]);

            // Prevent duplicate role-permission pair
            $exists = RolePermission::where('roleID', $validated['roleID'])
                ->where('permissionID', $validated['permissionID'])
                ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'This role already has this permission',
                    'data' => null
                ], 409);
            }

            $rolePermission = RolePermission::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Permission assigned to role successfully',
                'data' => $rolePermission
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
        $rolePermission = RolePermission::find($id);

        if (!$rolePermission) {
            return response()->json([
                'success' => false,
                'message' => 'Role permission not found',
                'data' => null
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Role permission retrieved successfully',
            'data' => $rolePermission
        ], 200);
    }

    public function update(Request $request, $id)
    {
        try {
            $rolePermission = RolePermission::find($id);

            if (!$rolePermission) {
                return response()->json([
                    'success' => false,
                    'message' => 'Role permission not found',
                    'data' => null
                ], 404);
            }

            $validated = $request->validate([
                'roleID' => 'required|integer|exists:roles,roleID',
                'permissionID' => 'required|integer|exists:permissions,permissionID'
            ]);

            // Prevent duplicate on update
            $exists = RolePermission::where('roleID', $validated['roleID'])
                ->where('permissionID', $validated['permissionID'])
                ->where('rolepermissionID', '!=', $id)
                ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'This role already has this permission',
                    'data' => null
                ], 409);
            }

            $rolePermission->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Role permission updated successfully',
                'data' => $rolePermission
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
            $rolePermission = RolePermission::find($id);

            if (!$rolePermission) {
                return response()->json([
                    'success' => false,
                    'message' => 'Role permission not found',
                    'data' => null
                ], 404);
            }

            $rolePermission->delete();

            return response()->json([
                'success' => true,
                'message' => 'Role permission deleted successfully',
                'data' => null
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete role permission',
                'data' => $e->getMessage()
            ], 500);
        }
    }
}