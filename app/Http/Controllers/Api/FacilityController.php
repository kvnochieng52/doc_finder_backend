<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Models\FacilitySpeciality;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class FacilityController extends Controller
{
    public function saveFacility(Request $request)
    {
        try {
            // Get authenticated user
            $user = auth()->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            // Validate the request data
            // $validator = Validator::make($request->all(), [
            //     'facility_name' => 'required|string|max:255',
            //     'facility_profile' => 'required|string',
            //     'facility_email' => 'required|email|unique:facilities,facility_email',
            //     'facility_phone' => 'required|string|max:20',
            //     'facility_location' => 'required|string',
            //     'facility_website' => 'nullable|url|max:255',
            // ]);

            // if ($validator->fails()) {
            //     return response()->json([
            //         'success' => false,
            //         'message' => 'Validation failed',
            //         'errors' => $validator->errors()
            //     ], 422);
            // }

            // Create a new facility
            $facility = new Facility();
            $facility->facility_name = $request->input('facility_name');
            $facility->facility_profile = $request->input('facility_profile');
            $facility->facility_email = $request->input('facility_email');
            $facility->facility_phone = $request->input('facility_phone');
            $facility->facility_location = $request->input('facility_location');
            $facility->facility_website = $request->input('facility_website');
            $facility->is_active = 1; // Set as active by default
            $facility->created_by = $user->id;
            $facility->updated_by = $user->id;
            $facility->save();

            return response()->json([
                'success' => true,
                'message' => 'Facility created successfully',
                'facility' => $facility
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error saving facility details', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to save facility details. Please try again.'
            ], 500);
        }
    }

    public function saveFacilitySpecialties(Request $request)
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            // Validate the request data
            $validator = Validator::make($request->all(), [
                'facility_id' => 'required|integer|exists:facilities,id',
                'specialty_ids' => 'required|array|min:1',
                'specialty_ids.*' => 'integer|exists:specializations,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $facilityId = $request->input('facility_id');
            $specialtyIds = $request->input('specialty_ids');

            // Check if facility belongs to the authenticated user
            $facility = Facility::where('id', $facilityId)
                ->where('created_by', $user->id)
                ->first();

            if (!$facility) {
                return response()->json([
                    'success' => false,
                    'message' => 'Facility not found or unauthorized'
                ], 404);
            }

            // Use database transaction to ensure data consistency
            DB::beginTransaction();

            try {
                // Delete existing facility specialties
                FacilitySpeciality::where('facility_id', $facilityId)->delete();

                // Insert new facility specialties
                $facilitySpecialties = [];
                foreach ($specialtyIds as $specialtyId) {
                    $facilitySpecialties[] = [
                        'facility_id' => $facilityId,
                        'speciality_id' => $specialtyId,
                        'created_by' => $user->id,
                        'updated_by' => $user->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                FacilitySpeciality::insert($facilitySpecialties);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Facility specialties saved successfully',
                    'data' => [
                        'facility_id' => $facilityId,
                        'specialties_count' => count($specialtyIds)
                    ]
                ], 200);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            Log::error('Error saving facility specialties', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to save facility specialties. Please try again.'
            ], 500);
        }
    }

    public function getFacilities(Request $request)
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $query = Facility::with(['specialties']) // Now this relationship exists
                ->where('created_by', $user->id)
                ->where('is_active', 1);

            // Add search functionality
            if ($request->has('search')) {
                $search = $request->input('search');
                $query->where(function ($q) use ($search) {
                    $q->where('facility_name', 'LIKE', "%{$search}%")
                        ->orWhere('facility_email', 'LIKE', "%{$search}%")
                        ->orWhere('facility_location', 'LIKE', "%{$search}%");
                });
            }

            $facilities = $query->orderBy('created_at', 'desc')->get();

            return response()->json([
                'success' => true,
                'message' => 'Facilities retrieved successfully',
                'data' => $facilities
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error retrieving facilities', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve facilities'
            ], 500);
        }
    }

    public function getFacility($id)
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $facility = Facility::with(['specialties'])
                ->where('id', $id)
                ->where('created_by', $user->id)
                ->first();

            if (!$facility) {
                return response()->json([
                    'success' => false,
                    'message' => 'Facility not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Facility retrieved successfully',
                'data' => $facility
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error retrieving facility', [
                'error' => $e->getMessage(),
                'facility_id' => $id,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve facility'
            ], 500);
        }
    }

    public function updateFacility(Request $request, $id)
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $facility = Facility::where('id', $id)
                ->where('created_by', $user->id)
                ->first();

            if (!$facility) {
                return response()->json([
                    'success' => false,
                    'message' => 'Facility not found'
                ], 404);
            }

            // Validate the request data
            // $validator = Validator::make($request->all(), [
            //     'facility_name' => 'sometimes|required|string|max:255',
            //     'facility_profile' => 'sometimes|required|string',
            //     'facility_email' => 'sometimes|required|email|unique:facilities,facility_email,' . $id,
            //     'facility_phone' => 'sometimes|required|string|max:20',
            //     'facility_location' => 'sometimes|required|string',
            //     'facility_website' => 'sometimes|nullable|url|max:255',
            //     'is_active' => 'sometimes|boolean',
            // ]);

            // if ($validator->fails()) {
            //     return response()->json([
            //         'success' => false,
            //         'message' => 'Validation failed',
            //         'errors' => $validator->errors()
            //     ], 422);
            // }

            // Update facility
            $facility->update(array_merge(
                $request->only([
                    'facility_name',
                    'facility_profile',
                    'facility_email',
                    'facility_phone',
                    'facility_location',
                    'facility_website',
                    'is_active'
                ]),
                ['updated_by' => $user->id]
            ));

            return response()->json([
                'success' => true,
                'message' => 'Facility updated successfully',
                'data' => $facility
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error updating facility', [
                'error' => $e->getMessage(),
                'facility_id' => $id,
                'user_id' => auth()->id(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update facility'
            ], 500);
        }
    }


    public function uploadFacilityLogo(Request $request)
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            // Validate the request data
            $validator = Validator::make($request->all(), [
                'facility_id' => 'required|integer|exists:facilities,id',
                'logo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // Max 2MB
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $facilityId = $request->input('facility_id');

            // Check if facility belongs to the authenticated user
            $facility = Facility::where('id', $facilityId)
                ->where('created_by', $user->id)
                ->first();

            if (!$facility) {
                return response()->json([
                    'success' => false,
                    'message' => 'Facility not found or unauthorized'
                ], 404);
            }

            // Handle file upload
            if ($request->hasFile('logo')) {
                $logoFile = $request->file('logo');

                // Delete old logo if exists
                if ($facility->facility_logo && Storage::disk('public')->exists($facility->facility_logo)) {
                    Storage::disk('public')->delete($facility->facility_logo);
                }

                // Generate unique filename
                $filename = 'facility_logos/' . time() . '_' . $facilityId . '.' . $logoFile->getClientOriginalExtension();

                // Store the file
                $logoPath = $logoFile->storeAs('facility_logos', basename($filename), 'public');

                // Update facility record
                $facility->facility_logo = $logoPath;
                $facility->updated_by = $user->id;
                $facility->save();

                return response()->json([
                    'success' => true,
                    'message' => 'Facility logo uploaded successfully',
                    'data' => [
                        'facility_id' => $facilityId,
                        'logo_path' => $logoPath,
                        'logo_url' => Storage::disk('public')->url($logoPath)
                    ]
                ], 200);
            }

            return response()->json([
                'success' => false,
                'message' => 'No logo file provided'
            ], 400);
        } catch (\Exception $e) {
            Log::error('Error uploading facility logo', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'request_data' => $request->except(['logo']) // Exclude file from logging
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to upload facility logo. Please try again.'
            ], 500);
        }
    }

    public function uploadFacilityCoverImage(Request $request)
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            // Validate the request data
            $validator = Validator::make($request->all(), [
                'facility_id' => 'required|integer|exists:facilities,id',
                'cover_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120', // Max 5MB for cover images
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $facilityId = $request->input('facility_id');

            // Check if facility belongs to the authenticated user
            $facility = Facility::where('id', $facilityId)
                ->where('created_by', $user->id)
                ->first();

            if (!$facility) {
                return response()->json([
                    'success' => false,
                    'message' => 'Facility not found or unauthorized'
                ], 404);
            }

            // Handle file upload
            if ($request->hasFile('cover_image')) {
                $coverImageFile = $request->file('cover_image');

                // Delete old cover image if exists
                if ($facility->facility_cover_image && Storage::disk('public')->exists($facility->facility_cover_image)) {
                    Storage::disk('public')->delete($facility->facility_cover_image);
                }

                // Generate unique filename
                $filename = 'facility_cover_images/' . time() . '_' . $facilityId . '.' . $coverImageFile->getClientOriginalExtension();

                // Store the file
                $coverImagePath = $coverImageFile->storeAs('facility_cover_images', basename($filename), 'public');

                // Update facility record
                $facility->facility_cover_image = $coverImagePath;
                $facility->updated_by = $user->id;
                $facility->save();

                return response()->json([
                    'success' => true,
                    'message' => 'Facility cover image uploaded successfully',
                    'data' => [
                        'facility_id' => $facilityId,
                        'cover_image_path' => $coverImagePath,
                        'cover_image_url' => Storage::disk('public')->url($coverImagePath)
                    ]
                ], 200);
            }

            return response()->json([
                'success' => false,
                'message' => 'No cover image file provided'
            ], 400);
        } catch (\Exception $e) {
            Log::error('Error uploading facility cover image', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'request_data' => $request->except(['cover_image']) // Exclude file from logging
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to upload facility cover image. Please try again.'
            ], 500);
        }
    }


    public function deleteFacility($id)
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $facility = Facility::where('id', $id)
                ->where('created_by', $user->id)
                ->first();

            if (!$facility) {
                return response()->json([
                    'success' => false,
                    'message' => 'Facility not found or unauthorized'
                ], 404);
            }

            // Use database transaction to ensure data consistency
            DB::beginTransaction();

            try {
                // Delete facility specialties first (foreign key constraint)
                FacilitySpeciality::where('facility_id', $id)->delete();

                // Delete uploaded images from storage
                if ($facility->facility_logo && Storage::disk('public')->exists($facility->facility_logo)) {
                    Storage::disk('public')->delete($facility->facility_logo);
                }

                if ($facility->facility_cover_image && Storage::disk('public')->exists($facility->facility_cover_image)) {
                    Storage::disk('public')->delete($facility->facility_cover_image);
                }

                // Delete the facility
                $facility->delete();

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Facility deleted successfully'
                ], 200);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            Log::error('Error deleting facility', [
                'error' => $e->getMessage(),
                'facility_id' => $id,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete facility. Please try again.'
            ], 500);
        }
    }
}
