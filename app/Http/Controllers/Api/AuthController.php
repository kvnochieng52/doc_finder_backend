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
        $user = User::where('email', $request->email)->first();
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
        $user = User::where('email', $request->email)->first();


        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => "User with this email does not exist"
            ], 401);
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
            // $this->fail($e);
        }

        return response()->json([
            'success' => true,
            'message' => "Password reset code sent successfully"
        ], 200);
    }


    public function verifyResetCode(Request $request)
    {
        $user = User::where('email', $request->email)->first();

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
        $user = User::where('email', $request->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();
        return response()->json([
            'success' => true,
            'message' => "Password reset successfully"
        ], 200);
    }
}
