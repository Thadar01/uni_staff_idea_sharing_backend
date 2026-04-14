<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Models\Staff;
use Illuminate\Support\Facades\Validator;

class StaffAuthController extends Controller
{
    /**
     * LOGIN
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'staffEmail' => 'required|email',
            'staffPassword' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'data' => $validator->errors()
            ], 422);
        }

        try {
            // Case-sensitive email check
            $staff = Staff::with(['role.permissions', 'department'])
                ->whereRaw('BINARY staffEmail = ?', [$request->staffEmail])
                ->first();
            // Verify password with bcrypt (default Laravel salting)
            if (!$staff || !Hash::check($request->staffPassword, $staff->staffPassword)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid Credentials',
                    'data' => null
                ], 401);
            }

            // Set custom token TTL: 3 days = 60 * 24 * 3 minutes
            JWTAuth::factory()->setTTL(60 * 24 * 3); // 3 days

            // Capture the PREVIOUS login time for the UI reminder
            $previousLogin = $staff->last_login_at;

            // Update with the CURRENT login time
            $staff->last_login_at = now()->timezone('Asia/Yangon');
            $staff->save();

            $token = auth('staff')->login($staff);

            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'data' => [
                    'user' => $staff,
                    'previous_login_at' => $previousLogin, // Will be null for first login
                    'token' => $token,
                    'token_type' => 'bearer',
                    'expires_in' => JWTAuth::factory()->getTTL() * 60 // in seconds
                ]
            ]);

        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Could not create token',
                'data' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * LOGOUT
     */
    public function logout()
    {
        try {
            auth('staff')->logout();

            return response()->json([
                'success' => true,
                'message' => 'Logout successful. Token invalidated.',
                'data' => null
            ]);

        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Logout failed',
                'data' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * REFRESH TOKEN
     */
    public function refresh()
    {
        try {
            $newToken = auth('staff')->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Token refreshed successfully.',
                'data' => [
                    'token' => $newToken,
                    'token_type' => 'bearer',
                    'expires_in' => JWTAuth::factory()->getTTL() * 60
                ]
            ]);

        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token refresh failed',
                'data' => $e->getMessage()
            ], 401);
        }
    }

    /**
     * GET LOGGED IN STAFF
     */
    public function me()
    {
        $staff = auth('staff')->user();

        if (!$staff) {
            return response()->json([
                'success' => false,
                'message' => 'Staff not authenticated',
                'data' => null
            ], 401);
        }

        return response()->json([
            'success' => true,
            'message' => 'Staff retrieved successfully',
            'data' => $staff
        ]);
    }

    public function changePassword(Request $request)
    {
        $staff = auth('staff')->user();

        if (!$staff) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
                'data' => null
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'old_password' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'data' => $validator->errors()
            ], 422);
        }

        // Check old password
        if (!Hash::check($request->old_password, $staff->staffPassword)) {
            return response()->json([
                'success' => false,
                'message' => 'Old password does not match.',
                'data' => null
            ], 400);
        }

        // Update password
        $staff->update([
            'staffPassword' => Hash::make($request->new_password, ['rounds' => 10])
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully.',
            'data' => null
        ], 200);
    }

    public function resetPasswordToDefault(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'staffID' => 'required|exists:staffs,staffID',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Staff ID not found or invalid format.',
                'data' => $validator->errors()
            ], 422);
        }

        try {
            $staff = Staff::find($request->staffID);

            if (!$staff) {
                return response()->json([
                    'success' => false,
                    'message' => 'Staff not found',
                    'data' => null
                ], 404);
            }

            // Set default password "Staff123!@#" and hash with bcrypt rounds 10
            $defaultPassword = 'Staff123!@#';
            $staff->update([
                'staffPassword' => Hash::make($defaultPassword, ['rounds' => 10])
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Password has been reset to default successfully.',
                'data' => null
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reset password',
                'data' => $e->getMessage()
            ], 500);
        }
    }
}
