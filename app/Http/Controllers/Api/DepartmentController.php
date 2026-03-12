<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DepartmentController extends Controller
{
    // GET /api/departments
   // GET /api/departments
public function index()
{
      $departments = Department::with('qaCoordinator')->get();

    if ($departments->isEmpty()) {
        return response()->json([
            'success' => false,
            'message' => 'No departments found',
            'data' => null
        ], 404);
    }

    return response()->json([
        'success' => true,
        'message' => 'Departments retrieved successfully.',
        'data' => $departments
    ]);
}
public function getStaffByDepartment($id)
{
    $department = Department::with('staffs')->find($id);

    if (!$department) {
        return response()->json([
            'success' => false,
            'message' => 'Department not found',
            'data' => null
        ], 404);
    }

    return response()->json([
        'success' => true,
        'message' => 'Staffs retrieved successfully.',
        'data' => $department->staffs
    ], 200);
}

    // POST /api/departments
   // POST /api/departments
public function store(Request $request)
{
    $validator = Validator::make($request->all(), [
        'departments' => 'required|array|min:1',
        'departments.*.departmentName' => 'required|string|max:255',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'data' => $validator->errors()
        ], 422);
    }

    $createdDepartments = [];

    foreach ($request->departments as $deptData) {
        $createdDepartments[] = Department::create($deptData);
    }

    return response()->json([
        'success' => true,
        'message' => 'Departments created successfully.',
        'data' => $createdDepartments
    ], 201);
}


    // GET /api/departments/{id}
    public function show($id)
    {
        $department = Department::with('qaCoordinator')->find($id);

        if (!$department) {
            return response()->json([
                'success' => false,
                'message' => 'Department not found',
                'data' => null
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Department retrieved successfully.',
            'data' => $department
        ]);
    }

    // PUT /api/departments/{id}
    public function update(Request $request, $id)
    {
        $department = Department::find($id);

        if (!$department) {
            return response()->json([
                'success' => false,
                'message' => 'Department not found',
                'data' => null
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'departmentName' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'data' => $validator->errors()
            ], 422);
        }

        $department->update($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Department updated successfully.',
            'data' => $department
        ]);
    }

    // DELETE /api/departments/{id}
    public function destroy($id)
    {
        $department = Department::find($id);

        if (!$department) {
            return response()->json([
                'success' => false,
                'message' => 'Department not found',
                'data' => null
            ], 404);
        }

        $department->delete();

        return response()->json([
            'success' => true,
            'message' => 'Department deleted successfully.',
            'data' => null
        ]);
    }
}
