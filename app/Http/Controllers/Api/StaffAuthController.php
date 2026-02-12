<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Models\Staff;

class StaffAuthController extends Controller
{
    /**
     * LOGIN
     */
    public function login(Request $request)
    {
        try {

            $request->validate([
                'staffEmail' => 'required|email',
                'staffPassword' => 'required|string',
            ]);

            // Case-sensitive email check
            $staff = Staff::whereRaw('BINARY staffEmail = ?', [$request->staffEmail])->first();

            if (!$staff || !Hash::check($request->staffPassword, $staff->staffPassword)) {
                return response()->json([
                    'message' => 'Invalid Credentials'
                ], 401);
            }

            $token = auth('staff')->login($staff);

            return response()->json([
                'message' => 'Login successful',
                'token' => $token,
                'token_type' => 'bearer',
                'expires_in' => JWTAuth::factory()->getTTL() * 60,
                'user' => $staff
            ]);

        } catch (JWTException $e) {

            return response()->json([
                'error' => 'Could not create token',
                'message' => $e->getMessage()
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
                'message' => 'Logout successful. Token invalidated.'
            ]);

        } catch (JWTException $e) {

            return response()->json([
                'error' => 'Logout failed',
                'message' => $e->getMessage()
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
                'token' => $newToken,
                'token_type' => 'bearer',
                'expires_in' => JWTAuth::factory()->getTTL() * 60,
            ]);

        } catch (JWTException $e) {

            return response()->json([
                'error' => 'Token refresh failed',
                'message' => $e->getMessage()
            ], 401);
        }
    }

    /**
     * GET LOGGED IN STAFF
     */
    public function me()
    {
        return response()->json(auth('staff')->user());
    }
}
