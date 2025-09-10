<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6',

        ]);


        $randomNumber = rand(1000, 9999);
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'verification_code' => $randomNumber,
            'first_login' => 1,
        ]);


        try {
            Mail::send(
                'mailing.signup.verification',
                [
                    'resetCode' => $randomNumber,
                    'name' => $request->name,
                ],
                function ($message) use ($request) {
                    $message->from('app@justhomesapp.com', 'Xyvra Group');
                    $message->to($request->email)->subject("Xyvra Group: Email Verification Code");
                }
            );
        } catch (\Exception $e) {
            Log::error("Failed to send email: " . $e->getMessage());
            // $this->fail($e);
        }

        return response()->json([
            'success' => true,
            'user' => $user,
            'token' => $user->createToken('auth_token')->plainTextToken
        ], 200);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => "Invalid credentials"
            ], 401);
        }

        // Check if user is active
        if (!$user->is_active) {
            return response()->json([
                'success' => false,
                'message' => "Your account is inactive. Please contact administrator."
            ], 403); // 403 Forbidden status code
        }

        return response()->json([
            'success' => true,
            'user' => $user,
            'token' => $user->createToken('auth_token')->plainTextToken,
            'message' => "Logged in successfully"
        ], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully'], 200);
    }

    public function user(Request $request)
    {
        return response()->json($request->user());
    }



    public function VerifyEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'verification_code' => 'required|string'
        ]);

        $user = User::where('email', $request->email)->first();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => "User not found"
            ], 404);
        }

        if ($user->verification_code == $request->verification_code) {
            $user->is_active = 1;
            $user->save();
            return response()->json([
                'success' => true,
                'message' => "Email verified successfully"
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => "Invalid verification code"
            ], 401);
        }
    }



    public function sendResetCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => "User with this email does not exist"
            ], 404);
        }

        $randomNumber = rand(1000, 9999);
        $user->verification_code = $randomNumber;
        $user->save();

        try {
            Mail::send(
                'mailing.signup.verification',
                [
                    'resetCode' => $randomNumber,
                    'name' => $user->name,
                ],
                function ($message) use ($user) {
                    $message->from('app@justhomesapp.com', 'Xyvra Group');
                    $message->to($user->email)->subject("Xyvra Group: Password Reset Code");
                }
            );
        } catch (\Exception $e) {
            Log::error("Failed to send email: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => "Failed to send reset code email"
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => "Password reset code sent successfully"
        ], 200);
    }


    public function verifyResetCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required|string'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => "User not found"
            ], 404);
        }

        if ($user->verification_code == $request->code) {
            return response()->json([
                'success' => true,
                'message' => "Password reset code verified successfully"
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => "Invalid verification code"
            ], 401);
        }
    }


    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6|confirmed',
            'code' => 'required|string'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => "User not found"
            ], 404);
        }

        // Verify the reset code before allowing password reset
        if ($user->verification_code != $request->code) {
            return response()->json([
                'success' => false,
                'message' => "Invalid verification code"
            ], 401);
        }

        $user->password = Hash::make($request->password);
        $user->verification_code = null; // Clear the verification code after use
        $user->save();

        return response()->json([
            'success' => true,
            'message' => "Password reset successfully"
        ], 200);
    }
}
