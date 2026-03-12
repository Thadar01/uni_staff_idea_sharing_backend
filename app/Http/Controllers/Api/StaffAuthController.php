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
 $staff = Staff::with(['role.permissions'])
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
            $staff->last_login_at =now()->timezone('Asia/Yangon');
            $staff->save();
            $token = auth('staff')->login($staff);

            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'data' => [
                    'user' => $staff,
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
}
