<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserDocument;
use App\Models\UserSpecialization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;

class ServiceProviderController extends Controller
{
    /**
     * Save service provider details
     */
    public function saveServiceProviderDetails(Request $request): JsonResponse
    {
        try {
            // Validate the request
            $validator = Validator::make($request->all(), [
                'licence_number' => 'required|string|max:255',
                'professional_bio' => 'required|string',
                'specializations' => 'required|array|min:1',
                'specializations.*' => 'integer',
                'account_type' => 'required|integer|in:2',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 400);
            }

            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            // Start database transaction
            DB::beginTransaction();

            // Update user details directly in users table
            DB::table('users')
                ->where('id', $user->id)
                ->update([
                    'licence_number' => $request->licence_number,
                    'professional_bio' => $request->professional_bio,
                    'account_type' => $request->account_type,
                    'updated_at' => now(),
                ]);

            // Delete existing specializations for this user
            DB::table('user_specializations')
                ->where('user_id', $user->id)
                ->delete();

            // Prepare specialization data for bulk insert
            $specializationData = [];
            foreach ($request->specializations as $specializationId) {
                $specializationData[] = [
                    'user_id' => $user->id,
                    'specialization_id' => $specializationId,
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // Insert new specializations
            if (!empty($specializationData)) {
                DB::table('user_specializations')->insert($specializationData);
            }

            // Commit the transaction
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Service provider details saved successfully',
                'data' => [
                    'user_id' => $user->id,
                    'licence_number' => $request->licence_number,
                    'professional_bio' => $request->professional_bio,
                    'account_type' => $request->account_type,
                    'specializations_count' => count($request->specializations)
                ]
            ], 200);
        } catch (\Exception $e) {
            // Rollback the transaction
            DB::rollback();

            // Log the error
            Log::error('Error while saving service provider details: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'request_data' => request()->all() // Optional: log the request data for debugging
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while saving service provider details',
                'error' => $e->getMessage() // Consider hiding in production
            ], 500);
        }
    }

    /**
     * Upload user documents (certificates, ID documents, etc.)
     */
    public function uploadUserDocument(Request $request): JsonResponse
    {
        try {
            // Validate the request
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer',
                'document_type' => 'required|string|in:certificate,id',
                'document' => 'required|file|mimes:jpeg,jpg,png,pdf|max:5120', // 5MB max
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 400);
            }

            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            // Check if the user exists in database
            $targetUser = DB::table('users')->where('id', $request->user_id)->first();
            if (!$targetUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'Target user not found'
                ], 404);
            }

            // Check if the user is uploading their own documents
            if ($user->id != $request->user_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to upload documents for this user'
                ], 403);
            }

            // Handle file upload
            $file = $request->file('document');
            $fileName = time() . '_' . $request->user_id . '_' . $request->document_type . '.' . $file->getClientOriginalExtension();

            // Store file in storage/app/public/user_documents
            $filePath = $file->storeAs('user_documents', $fileName, 'public');

            // Save document record to database using direct DB query
            $documentId = DB::table('user_documents')->insertGetId([
                'user_id' => $request->user_id,
                'document_type' => $request->document_type,
                'document_path' => $filePath,
                'is_active' => 1,
                'created_by' => $user->id,
                'updated_by' => $user->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Document uploaded successfully',
                'data' => [
                    'document_id' => $documentId,
                    'document_type' => $request->document_type,
                    'document_path' => $filePath,
                    'document_url' => Storage::url($filePath),
                    'upload_date' => now()
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while uploading document',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user profile data including specializations and documents
     */
    public function getUserProfile(): JsonResponse
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            // Get user data from users table
            $userData = DB::table('users')
                ->where('id', $user->id)
                ->select(
                    'id',
                    'name',
                    'email',
                    'telephone',
                    'id_number',
                    'address',
                    'licence_number',
                    'professional_bio',
                    'account_type',
                    'profile_image'
                )
                ->first();

            // Get all specializations from specializations table
            $specializations = DB::table('specializations')
                ->select('id', 'specialization_name', 'specialization_description')
                ->where('is_active', 1)
                ->orderBy('specialization_name')
                ->get()
                ->toArray();

            // Get user's specializations with names
            $userSpecializations = DB::table('user_specializations')
                ->join('specializations', 'user_specializations.specialization_id', '=', 'specializations.id')
                ->where('user_specializations.user_id', $user->id)
                ->select(
                    'user_specializations.id',
                    'user_specializations.user_id',
                    'user_specializations.specialization_id',
                    'specializations.specialization_name',
                    'user_specializations.created_at'
                )
                ->get()
                ->toArray();

            // Get user's certificate documents
            $userDocuments = DB::table('user_documents')
                ->where('user_id', $user->id)
                ->where('is_active', 1)
                ->where('document_type', 'certificate')
                ->select('id', 'user_id', 'document_type', 'document_path', 'created_at')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($doc) {
                    $doc->document_url = Storage::url($doc->document_path);
                    return $doc;
                })
                ->toArray();

            // Get user's ID documents
            $userIds = DB::table('user_documents')
                ->where('user_id', $user->id)
                ->where('is_active', 1)
                ->where('document_type', 'id')
                ->select('id', 'user_id', 'document_type', 'document_path', 'created_at')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($doc) {
                    $doc->document_url = Storage::url($doc->document_path);
                    return $doc;
                })
                ->toArray();

            return response()->json([
                'success' => true,
                'message' => 'User profile data retrieved successfully',
                'data' => [
                    'user' => $userData,
                    'specializations' => $specializations,
                    'user_specializations' => $userSpecializations,
                    'user_documents' => $userDocuments,
                    'user_ids' => $userIds,
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving user profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete user document (soft delete)
     */
    public function deleteUserDocument(Request $request, $documentId): JsonResponse
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            // Get document from database
            $document = DB::table('user_documents')
                ->where('id', $documentId)
                ->first();

            if (!$document) {
                return response()->json([
                    'success' => false,
                    'message' => 'Document not found'
                ], 404);
            }

            // Check if user owns this document
            if ($document->user_id != $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to delete this document'
                ], 403);
            }

            // Soft delete by setting is_active to 0
            DB::table('user_documents')
                ->where('id', $documentId)
                ->update([
                    'is_active' => 0,
                    'updated_by' => $user->id,
                    'updated_at' => now()
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Document deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting document',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all available specializations
     */
    public function getSpecializations(): JsonResponse
    {
        try {
            $specializations = DB::table('specializations')
                ->select('id', 'specialization_name', 'specialization_description')
                ->where('is_active', 1)
                ->orderBy('specialization_name')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Specializations retrieved successfully',
                'data' => $specializations
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving specializations',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get service provider's public profile
     */
    public function getServiceProviderProfile($userId): JsonResponse
    {
        try {
            // Get service provider data
            $serviceProvider = DB::table('users')
                ->where('id', $userId)
                ->where('account_type', 2)
                ->select('id', 'name', 'email', 'professional_bio', 'profile_image')
                ->first();

            if (!$serviceProvider) {
                return response()->json([
                    'success' => false,
                    'message' => 'Service provider not found'
                ], 404);
            }

            // Get service provider's specializations
            $specializations = DB::table('user_specializations')
                ->join('specializations', 'user_specializations.specialization_id', '=', 'specializations.id')
                ->where('user_specializations.user_id', $userId)
                ->select('specializations.specialization_name', 'specializations.specialization_description')
                ->get();

            // Get certificate count (don't expose actual documents for privacy)
            $certificateCount = DB::table('user_documents')
                ->where('user_id', $userId)
                ->where('document_type', 'certificate')
                ->where('is_active', 1)
                ->count();

            return response()->json([
                'success' => true,
                'message' => 'Service provider profile retrieved successfully',
                'data' => [
                    'provider' => $serviceProvider,
                    'specializations' => $specializations,
                    'certificate_count' => $certificateCount,
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving service provider profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
