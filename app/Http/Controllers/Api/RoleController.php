<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    // GET all roles
    public function index()
    {
        return response()->json(Role::all());
    }

    // POST create role
    public function store(Request $request)
    {
        $request->validate([
            'roleName' => 'required|string|max:255'
        ]);

        $role = Role::create($request->all());

        return response()->json($role, 201);
    }

    // GET single role
    public function show($id)
    {
        $role = Role::find($id);

        if (!$role) {
            return response()->json(['message' => 'Role not found'], 404);
        }

        return response()->json($role);
    }

    // PUT update role
    public function update(Request $request, $id)
    {
        $role = Role::find($id);

        if (!$role) {
            return response()->json(['message' => 'Role not found'], 404);
        }

        $request->validate([
            'roleName' => 'required|string|max:255'
        ]);

        $role->update($request->all());

        return response()->json($role);
    }

    // DELETE role
    public function destroy($id)
    {
        $role = Role::find($id);

        if (!$role) {
            return response()->json(['message' => 'Role not found'], 404);
        }

        $role->delete();

        return response()->json(['message' => 'Role deleted successfully']);
    }
}
