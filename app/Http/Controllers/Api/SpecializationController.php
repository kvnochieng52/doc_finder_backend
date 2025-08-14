<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Specialization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SpecializationController extends Controller
{
    /**
     * Get specializations that are active for facilities
     */
    public function getActiveForFacility()
    {
        try {
            $specializations = Specialization::where('is_active', 1)
                ->where('is_active_for_facility', 1)
                ->select('id', 'specialization_name', 'specialization_description')
                ->orderBy('specialization_name', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Active specializations for facilities retrieved successfully',
                'data' => $specializations
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error retrieving active specializations for facilities', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve specializations'
            ], 500);
        }
    }

    /**
     * Get all specializations
     */
    public function getSpecializations(Request $request)
    {
        try {
            $query = Specialization::query();

            // Filter by active status
            if ($request->has('is_active')) {
                $query->where('is_active', $request->input('is_active'));
            }

            // Filter by facility active status
            if ($request->has('is_active_for_facility')) {
                $query->where('is_active_for_facility', $request->input('is_active_for_facility'));
            }

            // Search functionality
            if ($request->has('search')) {
                $search = $request->input('search');
                $query->where(function ($q) use ($search) {
                    $q->where('specialization_name', 'LIKE', "%{$search}%")
                        ->orWhere('specialization_description', 'LIKE', "%{$search}%");
                });
            }

            $specializations = $query->orderBy('specialization_name', 'asc')->get();

            return response()->json([
                'success' => true,
                'message' => 'Specializations retrieved successfully',
                'data' => $specializations
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error retrieving specializations', [
                'error' => $e->getMessage(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve specializations'
            ], 500);
        }
    }

    /**
     * Get a single specialization
     */
    public function getSpecialization($id)
    {
        try {
            $specialization = Specialization::find($id);

            if (!$specialization) {
                return response()->json([
                    'success' => false,
                    'message' => 'Specialization not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Specialization retrieved successfully',
                'data' => $specialization
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error retrieving specialization', [
                'error' => $e->getMessage(),
                'specialization_id' => $id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve specialization'
            ], 500);
        }
    }
}
