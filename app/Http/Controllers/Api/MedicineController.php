<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Medicine;
use App\Models\MedicineCategory;
use App\Models\MedicineSubcategory;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class MedicineController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Medicine::with(['category', 'subcategory'])
            ->active();

        // Search functionality
        if ($request->has('search') && !empty($request->search)) {
            $query->search($request->search);
        }

        // Filter by category
        if ($request->has('category_id') && !empty($request->category_id)) {
            $query->byCategory($request->category_id);
        }

        // Filter by subcategory
        if ($request->has('subcategory_id') && !empty($request->subcategory_id)) {
            $query->bySubcategory($request->subcategory_id);
        }

        // Filter by prescription requirement
        if ($request->has('requires_prescription')) {
            $query->where('requires_prescription', filter_var($request->requires_prescription, FILTER_VALIDATE_BOOLEAN));
        }

        // Filter by availability
        if ($request->has('in_stock') && filter_var($request->in_stock, FILTER_VALIDATE_BOOLEAN)) {
            $query->inStock();
        }

        // Sort options
        $sortBy = $request->get('sort_by', 'name');
        $sortOrder = $request->get('sort_order', 'asc');
        
        if (in_array($sortBy, ['name', 'cost', 'created_at', 'sort_order'])) {
            $query->orderBy($sortBy, $sortOrder);
        }

        $perPage = min($request->get('per_page', 15), 50);
        $medicines = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'medicines' => $medicines->items(),
            'pagination' => [
                'current_page' => $medicines->currentPage(),
                'last_page' => $medicines->lastPage(),
                'per_page' => $medicines->perPage(),
                'total' => $medicines->total(),
            ]
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $rules = [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'medicine_number' => 'required|string|unique:medicines',
            'cost' => 'required|numeric|min:0',
            'category_id' => 'required|exists:medicine_categories,id',
            'subcategory_id' => 'nullable|exists:medicine_subcategories,id',
            'manufacturer' => 'nullable|string|max:255',
            'strength' => 'nullable|string|max:100',
            'form' => 'nullable|string|max:100',
            'quantity_available' => 'nullable|integer|min:0',
            'requires_prescription' => 'nullable|boolean',
            'conditions' => 'nullable|array',
            'conditions.*' => 'string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();

        // Handle image upload
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('medicine_images', 'public');
            $data['image'] = $imagePath;
        }

        // Handle conditions sent as individual fields (conditions[0], conditions[1], etc.)
        $conditions = [];
        foreach ($request->all() as $key => $value) {
            if (preg_match('/^conditions\[(\d+)\]$/', $key)) {
                $conditions[] = $value;
            }
        }
        if (!empty($conditions)) {
            $data['conditions'] = $conditions;
        }

        // Convert boolean fields
        $data['requires_prescription'] = filter_var($request->get('requires_prescription', false), FILTER_VALIDATE_BOOLEAN);
        $data['quantity_available'] = (int) $request->get('quantity_available', 0);

        $medicine = Medicine::create($data);
        $medicine->load(['category', 'subcategory']);

        return response()->json([
            'success' => true,
            'message' => 'Medicine created successfully',
            'medicine' => $medicine
        ], 201);
    }

    public function show($id): JsonResponse
    {
        $medicine = Medicine::with(['category', 'subcategory'])->find($id);

        if (!$medicine) {
            return response()->json([
                'success' => false,
                'message' => 'Medicine not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'medicine' => $medicine
        ]);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $medicine = Medicine::find($id);

        if (!$medicine) {
            return response()->json([
                'success' => false,
                'message' => 'Medicine not found'
            ], 404);
        }

        $rules = [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'medicine_number' => 'required|string|unique:medicines,medicine_number,' . $id,
            'cost' => 'required|numeric|min:0',
            'category_id' => 'required|exists:medicine_categories,id',
            'subcategory_id' => 'nullable|exists:medicine_subcategories,id',
            'manufacturer' => 'nullable|string|max:255',
            'strength' => 'nullable|string|max:100',
            'form' => 'nullable|string|max:100',
            'quantity_available' => 'nullable|integer|min:0',
            'requires_prescription' => 'nullable|boolean',
            'conditions' => 'nullable|array',
            'conditions.*' => 'string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image
            if ($medicine->image && Storage::disk('public')->exists($medicine->image)) {
                Storage::disk('public')->delete($medicine->image);
            }
            
            $imagePath = $request->file('image')->store('medicine_images', 'public');
            $data['image'] = $imagePath;
        }

        // Handle conditions sent as individual fields (conditions[0], conditions[1], etc.)
        $conditions = [];
        foreach ($request->all() as $key => $value) {
            if (preg_match('/^conditions\[(\d+)\]$/', $key)) {
                $conditions[] = $value;
            }
        }
        if (!empty($conditions)) {
            $data['conditions'] = $conditions;
        }

        // Convert boolean fields
        $data['requires_prescription'] = filter_var($request->get('requires_prescription', false), FILTER_VALIDATE_BOOLEAN);
        $data['quantity_available'] = (int) $request->get('quantity_available', 0);

        $medicine->update($data);
        $medicine->load(['category', 'subcategory']);

        return response()->json([
            'success' => true,
            'message' => 'Medicine updated successfully',
            'medicine' => $medicine
        ]);
    }

    public function destroy($id): JsonResponse
    {
        $medicine = Medicine::find($id);

        if (!$medicine) {
            return response()->json([
                'success' => false,
                'message' => 'Medicine not found'
            ], 404);
        }

        // Delete image
        if ($medicine->image && Storage::disk('public')->exists($medicine->image)) {
            Storage::disk('public')->delete($medicine->image);
        }

        $medicine->delete();

        return response()->json([
            'success' => true,
            'message' => 'Medicine deleted successfully'
        ]);
    }

    public function getCategories(): JsonResponse
    {
        $categories = MedicineCategory::active()
            ->ordered()
            ->with(['subcategories' => function ($query) {
                $query->active()->ordered();
            }])
            ->get();

        return response()->json([
            'success' => true,
            'categories' => $categories
        ]);
    }

    public function getSubcategories($categoryId): JsonResponse
    {
        $subcategories = MedicineSubcategory::where('category_id', $categoryId)
            ->active()
            ->ordered()
            ->get();

        return response()->json([
            'success' => true,
            'subcategories' => $subcategories
        ]);
    }

    public function uploadImage(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $imagePath = $request->file('image')->store('medicine_images', 'public');

        return response()->json([
            'success' => true,
            'image_path' => $imagePath,
            'image_url' => 'http://69.30.235.220:8006/storage/' . $imagePath
        ]);
    }
}
