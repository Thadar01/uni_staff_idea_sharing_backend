<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Staff;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class StaffController extends Controller
{
    /**
     * List all staff
     */
    public function index()
    {
        $staffs = Staff::with(['department', 'role'])->get();

        return response()->json([
            'status' => true,
            'data' => $staffs
        ]);
    }

    /**
     * Create a new staff
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'staffName' => 'required|string|max:255',
            'staffEmail' => 'required|email|unique:staffs,staffEmail',
            'staffPhNo' => 'required|string|unique:staffs,staffPhNo',
            'staffPassword' => 'required|string|min:6',
            'staffDOB' => 'required|date',
            'staffAddress' => 'required|string',
            'departmentID' => 'required|exists:departments,departmentID',
            'roleID' => 'required|exists:roles,roleID',
            'staffProfile' => 'nullable|string',
            'termsAccepted' => 'required|boolean',
            'termsAcceptedDate' => 'nullable|date',
        ]);

        $validated['staffPassword'] = Hash::make($validated['staffPassword']);

        $staff = Staff::create($validated);

        return response()->json([
            'status' => true,
            'message' => 'Staff created successfully',
            'data' => $staff
        ], 201);
    }

    /**
     * Show a single staff
     */
    public function show($id)
    {
        $staff = Staff::with(['department', 'role'])->find($id);

        if (!$staff) {
            return response()->json([
                'status' => false,
                'message' => 'Staff not found'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $staff
        ]);
    }

    /**
     * Update staff
     */
    public function update(Request $request, $id)
    {
        $staff = Staff::find($id);

        if (!$staff) {
            return response()->json([
                'status' => false,
                'message' => 'Staff not found'
            ], 404);
        }

        $validated = $request->validate([
            'staffName' => 'sometimes|string|max:255',
            'staffEmail' => ['sometimes','email', Rule::unique('staffs')->ignore($staff->staffID, 'staffID')],
            'staffPhNo' => ['sometimes','string', Rule::unique('staffs')->ignore($staff->staffID, 'staffID')],
            'staffPassword' => 'sometimes|string|min:6',
            'staffDOB' => 'sometimes|date',
            'staffAddress' => 'sometimes|string',
            'departmentID' => 'sometimes|exists:departments,departmentID',
            'roleID' => 'sometimes|exists:roles,roleID',
            'staffProfile' => 'nullable|string',
            'termsAccepted' => 'sometimes|boolean',
            'termsAcceptedDate' => 'nullable|date',
        ]);

        if (isset($validated['staffPassword'])) {
            $validated['staffPassword'] = Hash::make($validated['staffPassword']);
        }

        $staff->update($validated);

        return response()->json([
            'status' => true,
            'message' => 'Staff updated successfully',
            'data' => $staff
        ]);
    }

    /**
     * Delete staff
     */
    public function destroy($id)
    {
        $staff = Staff::find($id);

        if (!$staff) {
            return response()->json([
                'status' => false,
                'message' => 'Staff not found'
            ], 404);
        }

        $staff->delete();

        return response()->json([
            'status' => true,
            'message' => 'Staff deleted successfully'
        ]);
    }
}
