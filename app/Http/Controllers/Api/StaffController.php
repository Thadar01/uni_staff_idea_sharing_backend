<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

class StaffController extends Controller
{
    /**
     * List all staff
     */
public function index(Request $request)
{
    // $staffUser = auth('staff')->user();

    // if (!$staffUser) {
    //     return response()->json([
    //         'success' => false,
    //         'message' => 'Unauthorized. Please log in.',
    //         'data' => null
    //     ], 401);
    // }

    $staffs = Staff::with([
        'department',
        'role.permissions'
    ])->get();

    return response()->json([
        'success' => true,
        'message' => 'Staff list retrieved successfully.',
        'data' => $staffs
    ]);
}

    /**
     * Create a new staff
     */
  public function store(Request $request)
{
    // Validate request (excluding password)
    $validator = Validator::make($request->all(), [
        'staffName' => 'required|string|max:255',
        'staffEmail' => 'required|email|unique:staffs,staffEmail',
        'staffPhNo' => 'required|string|unique:staffs,staffPhNo',
        'staffDOB' => 'required|date',
        'staffAddress' => 'required|string',
        'departmentID' => 'required|exists:departments,departmentID',
        'roleID' => 'required|exists:roles,roleID',
        'staffProfile' => 'nullable|image|mimes:jpg,jpeg,png|max:2048', // Only JPG/PNG, max 2MB
        'termsAccepted' => 'nullable|boolean',
        'termsAcceptedDate' => 'nullable|date',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'data' => $validator->errors()
        ], 422);
    }

    $data = $validator->validated();

    // Handle staff profile upload
    if ($request->hasFile('staffProfile')) {
        $file = $request->file('staffProfile');

        // Folder path
        $folderPath = public_path('uploads/staff_profiles');

        // Create folder if it doesn't exist
        if (!file_exists($folderPath)) {
            mkdir($folderPath, 0755, true);
        }

        // Generate unique file name
        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

        // Move file to folder
        $file->move($folderPath, $filename);

        // Save path in database (relative path)
        $data['staffProfile'] = 'uploads/staff_profiles/' . $filename;
    }

    // Set default password "Staff123!@#" and hash with bcrypt rounds 10
    $defaultPassword = 'Staff123!@#';
    $data['staffPassword'] = Hash::make($defaultPassword, ['rounds' => 10]);

    $staff = Staff::create($data);

    return response()->json([
        'success' => true,
        'message' => 'Staff created successfully with default password.',
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
                'success' => false,
                'message' => 'Staff not found',
                'data' => null
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Staff retrieved successfully.',
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
            'success' => false,
            'message' => 'Staff not found',
            'data' => null
        ], 404);
    }

    $validator = Validator::make($request->all(), [
        'staffName' => 'sometimes|string|max:255',
        'staffEmail' => ['sometimes', 'email', Rule::unique('staffs')->ignore($staff->staffID, 'staffID')],
        'staffPhNo' => ['sometimes', 'string', Rule::unique('staffs')->ignore($staff->staffID, 'staffID')],
        'staffPassword' => 'sometimes|string|min:6',
        'staffDOB' => 'sometimes|date',
        'staffAddress' => 'sometimes|string',
        'departmentID' => 'sometimes|exists:departments,departmentID',
        'roleID' => 'sometimes|exists:roles,roleID',
        'staffProfile' => 'nullable|image|mimes:jpg,jpeg,png|max:2048', // Only JPG/PNG
        'termsAccepted' => 'sometimes|boolean',
        'termsAcceptedDate' => 'nullable|date',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'data' => $validator->errors()
        ], 422);
    }

    $data = $validator->validated();

    // Handle staff profile upload
    if ($request->hasFile('staffProfile')) {
        $file = $request->file('staffProfile');
        $folderPath = public_path('uploads/staff_profiles');

        // Create folder if it doesn't exist
        if (!file_exists($folderPath)) {
            mkdir($folderPath, 0755, true);
        }

        // Delete previous photo if exists
        if ($staff->staffProfile && file_exists(public_path($staff->staffProfile))) {
            unlink(public_path($staff->staffProfile));
        }

        // Generate unique file name
        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

        // Move file to folder
        $file->move($folderPath, $filename);

        // Save path in database (relative path)
        $data['staffProfile'] = 'uploads/staff_profiles/' . $filename;
    }

    // Hash password if provided
    if (isset($data['staffPassword'])) {
        $data['staffPassword'] = Hash::make($data['staffPassword'], ['rounds' => 10]);
    }

    $staff->update($data);

    return response()->json([
        'success' => true,
        'message' => 'Staff updated successfully.',
        'data' => $staff
    ]);
}

public function updateStatus(Request $request, $id)
{
    $staff = Staff::find($id);

    if (!$staff) {
        return response()->json([
            'success' => false,
            'message' => 'Staff not found',
            'data' => null
        ], 404);
    }

    $validator = Validator::make($request->all(), [
        'account_status' => 'required|in:active,disabled'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'data' => $validator->errors()
        ], 422);
    }

    $staff->update([
        'account_status' => $request->account_status
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Staff status updated successfully.',
        'data' => $staff->fresh()
    ], 200);
}

    /**
     * Delete staff
     */
    public function destroy($id)
    {
        $staff = Staff::find($id);

        if (!$staff) {
            return response()->json([
                'success' => false,
                'message' => 'Staff not found',
                'data' => null
            ], 404);
        }

        $staff->delete();

        return response()->json([
            'success' => true,
            'message' => 'Staff deleted successfully.',
            'data' => null
        ]);
    }

    public function hideContent($id)
    {
        try {
            $staff = Staff::find($id);

            if (!$staff) {
                return response()->json([
                    'success' => false,
                    'message' => 'Staff not found',
                    'data' => null
                ], 404);
            }

            // Hide all ideas
            \App\Models\Idea::where('staffID', $id)
                ->where('status', '!=', 'deleted')
                ->update(['status' => 'hidden']);

            // Hide all comments
            \App\Models\Comment::where('staffID', $id)
                ->where('status', '!=', 'deleted')
                ->update(['status' => 'hidden']);

            return response()->json([
                'success' => true,
                'message' => 'All ideas and comments of the staff have been hidden',
                'data' => null
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to hide staff content',
                'data' => $e->getMessage()
            ], 500);
        }
    }

    public function unhideContent($id)
    {
        try {
            $staff = Staff::find($id);

            if (!$staff) {
                return response()->json([
                    'success' => false,
                    'message' => 'Staff not found',
                    'data' => null
                ], 404);
            }

            // Unhide all ideas (set back to approved)
            \App\Models\Idea::where('staffID', $id)
                ->where('status', 'hidden')
                ->update(['status' => 'approved']);

            // Unhide all comments (set back to active)
            \App\Models\Comment::where('staffID', $id)
                ->where('status', 'hidden')
                ->update(['status' => 'active']);

            return response()->json([
                'success' => true,
                'message' => 'All ideas and comments of the staff have been unhidden',
                'data' => null
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to unhide staff content',
                'data' => $e->getMessage()
            ], 500);
        }
    }
}
