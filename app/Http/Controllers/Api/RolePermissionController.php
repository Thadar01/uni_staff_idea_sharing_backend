<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\RolePermission;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;
use App\Models\Role;

class RolePermissionController extends Controller
{
   public function index()
{
    $rolePermissions = RolePermission::all();

    if ($rolePermissions->isEmpty()) {
        return response()->json([
            'success' => false,
            'message' => 'No role permissions found',
        ], 404);
    }

    return response()->json([
        'success' => true,
        'message' => 'Role permissions retrieved successfully',
        'data' => $rolePermissions
    ], 200);
}

//   public function store(Request $request)
// {
//     try {
//         $validated = $request->validate([
//             'roleID' => 'required|integer|exists:roles,roleID',
//             'permissionIDs' => 'required|array|min:1',
//             'permissionIDs.*' => 'integer|exists:permissions,permissionID'
//         ]);

//         $roleID = $validated['roleID'];
//         $permissionIDs = $validated['permissionIDs'];

//         $createdPermissions = [];
//         $skippedPermissions = [];

//         foreach ($permissionIDs as $permissionID) {

//             $exists = RolePermission::where('roleID', $roleID)
//                 ->where('permissionID', $permissionID)
//                 ->exists();

//             if ($exists) {
//                 $skippedPermissions[] = $permissionID;
//                 continue;
//             }

//             $createdPermissions[] = RolePermission::create([
//                 'roleID' => $roleID,
//                 'permissionID' => $permissionID
//             ]);
//         }

//         return response()->json([
//             'success' => true,
//             'message' => 'Permissions processed successfully',
//             'assigned' => $createdPermissions,
//             'skipped' => $skippedPermissions
//         ], 201);

//     } catch (ValidationException $e) {
//         return response()->json([
//             'success' => false,
//             'message' => 'Validation failed',
//             'data' => $e->errors()
//         ], 422);

//     } catch (\Exception $e) {
//         return response()->json([
//             'success' => false,
//             'message' => 'Unexpected error occurred',
//             'data' => $e->getMessage()
//         ], 500);
//     }
// }

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

public function givePermission(Request $request, $roleID)
{
    try {
        // Check if role exists
        $role = Role::find($roleID);
        if (!$role) {
            return response()->json([
                'success' => false,
                'message' => 'Role not found',
                'data' => null
            ], 404);
        }

        // Validate request
        $validated = $request->validate([
            'permissionIDs' => 'required|array|min:1',
            'permissionIDs.*' => 'integer|exists:permissions,permissionID'
        ]);

        $permissionIDs = $validated['permissionIDs'];

        // Sync permissions: add new, remove missing
        $role->permissions()->sync($permissionIDs);

        // Return current permissions
        $updatedPermissions = $role->permissions()->get();

        return response()->json([
            'success' => true,
            'message' => 'Role permissions updated successfully',
            'data' => $updatedPermissions
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