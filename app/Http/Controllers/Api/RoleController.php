<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RoleController extends Controller
{
    // GET all roles
    public function index()
    {
        $roles = Role::with('permissions')->get();

        if ($roles->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No roles found.',
                'data' => []
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Roles with permissions retrieved successfully.',
            'data' => $roles
        ], 200);
    }
    // POST create role
    // POST create role(s)
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'roles' => 'required|array|min:1',
            'roles.*.roleName' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'data' => $validator->errors()
            ], 422);
        }

        $createdRoles = [];

        foreach ($request->roles as $roleData) {
            $createdRoles[] = Role::create($roleData);
        }

        return response()->json([
            'success' => true,
            'message' => 'Roles created successfully.',
            'data' => $createdRoles
        ], 201);
    }


    // GET single role
    public function show($id)
    {
        $role = Role::with('permissions')->find($id);

        if (!$role) {
            return response()->json([
                'success' => false,
                'message' => 'Role not found',
                'data' => null
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Role retrieved successfully.',
            'data' => $role
        ], 200);
    }
    // PUT update role
    public function update(Request $request, $id)
    {
        $role = Role::find($id);

        if (!$role) {
            return response()->json([
                'success' => false,
                'message' => 'Role not found',
                'data' => null
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'roleName' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'data' => $validator->errors()
            ], 422);
        }

        $role->update($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Role updated successfully.',
            'data' => $role
        ]);
    }

    // DELETE role
    public function destroy($id)
    {
        $role = Role::find($id);

        if (!$role) {
            return response()->json([
                'success' => false,
                'message' => 'Role not found',
                'data' => null
            ], 404);
        }

        $role->delete();

        return response()->json([
            'success' => true,
            'message' => 'Role deleted successfully.',
            'data' => null
        ]);
    }
}
