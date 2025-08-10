<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    public function uploadProfileImage(Request $request)
    {
        try {
            // Validate the incoming request
            $request->validate([
                'profile_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // Max 2MB
                'user_id' => 'required|integer|exists:users,id'
            ]);

            $userId = $request->input('user_id');

            // Find the user
            $user = User::findOrFail($userId);

            // Get the uploaded file
            $file = $request->file('profile_image');

            // Delete old profile image if exists
            if ($user->profile_image) {
                Storage::disk('public')->delete($user->profile_image);
            }

            // Generate unique filename
            $filename = 'profile_' . $userId . '_' . time() . '.' . $file->getClientOriginalExtension();

            // Store the file in public disk under 'profile_images' directory
            $path = $file->storeAs('profile_images', $filename, 'public');

            // Update user's profile image path in database
            $user->profile_image = $path;
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Profile image uploaded successfully',
                'data' => [
                    'profile_image_url' => Storage::url($path),
                    'profile_image_path' => $path
                ]
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error('Profile image upload failed: ' . $e->getMessage(), [
                'user_id' => $request->input('user_id'),
                'file_info' => $request->hasFile('profile_image') ? [
                    'name' => $request->file('profile_image')->getClientOriginalName(),
                    'size' => $request->file('profile_image')->getSize()
                ] : null
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to upload profile image'
            ], 500);
        }
    }
}
