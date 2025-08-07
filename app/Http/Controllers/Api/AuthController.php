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
            'verification_code' => $randomNumber
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
                    $message->to($this->$request->email)->subject("Xyvra Group: Email Verification Code");
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


        //dd("here");
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            // throw ValidationException::withMessages([
            //     'email' => ['The provided credentials are incorrect.'],
            // ]);
            return response()->json([
                'success' => false,
                'message' => "Invalid credentials"
            ], 401);
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
}
