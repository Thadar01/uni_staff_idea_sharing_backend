<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    // GET /api/departments
    public function index()
    {
        return response()->json([
            'status' => true,
            'data' => Department::all()
        ]);
    }

    // POST /api/departments
    public function store(Request $request)
    {
        $validated = $request->validate([
            'departmentName' => 'required|string|max:255'
        ]);

        $department = Department::create($validated);

        return response()->json([
            'status' => true,
            'message' => 'Department created successfully',
            'data' => $department
        ], 201);
    }

    // GET /api/departments/{id}
    public function show($id)
    {
        $department = Department::find($id);

        if (!$department) {
            return response()->json([
                'status' => false,
                'message' => 'Department not found'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $department
        ]);
    }

    // PUT /api/departments/{id}
    public function update(Request $request, $id)
    {
        $department = Department::find($id);

        if (!$department) {
            return response()->json([
                'status' => false,
                'message' => 'Department not found'
            ], 404);
        }

        $validated = $request->validate([
            'departmentName' => 'required|string|max:255'
        ]);

        $department->update($validated);

        return response()->json([
            'status' => true,
            'message' => 'Department updated successfully',
            'data' => $department
        ]);
    }

    // DELETE /api/departments/{id}
    public function destroy($id)
    {
        $department = Department::find($id);

        if (!$department) {
            return response()->json([
                'status' => false,
                'message' => 'Department not found'
            ], 404);
        }

        $department->delete();

        return response()->json([
            'status' => true,
            'message' => 'Department deleted successfully'
        ]);
    }
}
