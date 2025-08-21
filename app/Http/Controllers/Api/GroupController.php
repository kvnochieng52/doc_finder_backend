<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;


use App\Models\Group;
use App\Models\GroupCategory;
use App\Models\GroupSubCategory;
use App\Models\GroupCategoryMapping;
use App\Models\GroupSubcategoryMapping;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class GroupController extends Controller
{
    /**
     * Get all active categories - simple list without relationships
     */
    public function getCategories(): JsonResponse
    {
        try {
            $categories = GroupCategory::select('id', 'name', 'description', 'slug')
                ->orderBy('position')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $categories,
                'message' => 'Categories retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve categories',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get subcategories by category_id - simple list without relationships
     */
    public function getSubCategories(Request $request): JsonResponse
    {
        try {
            $categoryId = $request->get('category_id');

            if (!$categoryId) {
                return response()->json([
                    'success' => false,
                    'message' => 'category_id is required'
                ], 400);
            }

            // Verify category exists
            $category = GroupCategory::find($categoryId);
            if (!$category) {
                return response()->json([
                    'success' => false,
                    'message' => 'Category not found'
                ], 404);
            }

            $subCategories = GroupSubCategory::select('id', 'name', 'slug', 'description')
                ->where('category_id', $categoryId)
                ->orderBy('position')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $subCategories,
                'category' => [
                    'id' => $category->id,
                    'name' => $category->name
                ],
                'message' => 'Subcategories retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve subcategories',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get active group categories (keeping the old method for backward compatibility)
     */
    public function getActiveCategories(): JsonResponse
    {
        try {
            $categories = GroupCategory::select('id', 'name', 'description', 'slug')
                ->orderBy('position')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $categories,
                'message' => 'Categories retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve categories',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get subcategories for a specific category (keeping the old method for backward compatibility)
     */
    public function getCategorySubcategories(int $categoryId): JsonResponse
    {
        try {
            $category = GroupCategory::find($categoryId);

            if (!$category) {
                return response()->json([
                    'success' => false,
                    'message' => 'Category not found'
                ], 404);
            }

            $subCategories = GroupSubCategory::select('id', 'name', 'slug')
                ->where('category_id', $categoryId)
                ->orderBy('position')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $subCategories,
                'category' => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'description' => $category->description
                ],
                'message' => 'Subcategories retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve subcategories',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new group with categories
     */
    // public function createGroup(Request $request): JsonResponse
    // {
    //     $request->validate([
    //         'group_name' => 'required|string|max:255',
    //         'group_description' => 'required|string',
    //         'group_location' => 'required|string|max:255',
    //         'group_tags' => 'nullable|string|max:500',
    //         'group_privacy' => ['required', Rule::in(['public', 'private', 'closed'])],
    //         'require_approval' => 'boolean',
    //         'category_id' => 'required|integer|exists:group_categories,id',
    //         'subcategory_ids' => 'required|array|min:1',
    //         'subcategory_ids.*' => 'integer|exists:group_sub_categories,id',
    //     ]);

    //     try {
    //         DB::beginTransaction();

    //         // Verify that all subcategories belong to the selected category
    //         $validSubcategoryIds = GroupSubCategory::where('category_id', $request->category_id)
    //             ->whereIn('id', $request->subcategory_ids)
    //             ->pluck('id')
    //             ->toArray();

    //         if (count($validSubcategoryIds) !== count($request->subcategory_ids)) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Some subcategories do not belong to the selected category'
    //             ], 422);
    //         }

    //         // Create the group
    //         $group = Group::create([
    //             'group_name' => $request->group_name,
    //             'group_description' => $request->group_description,
    //             'group_location' => $request->group_location,
    //             'group_tags' => $request->group_tags,
    //             'group_privacy' => $request->group_privacy,
    //             'require_approval' => $request->boolean('require_approval'),
    //             'created_by' => Auth::id(),
    //         ]);

    //         // Save category mapping
    //         GroupCategoryMapping::create([
    //             'group_id' => $group->id,
    //             'category_id' => $request->category_id,
    //         ]);

    //         // Save subcategory mappings
    //         foreach ($request->subcategory_ids as $subcategoryId) {
    //             GroupSubcategoryMapping::create([
    //                 'group_id' => $group->id,
    //                 'subcategory_id' => $subcategoryId,
    //             ]);
    //         }

    //         DB::commit();

    //         return response()->json([
    //             'success' => true,
    //             'group' => $group,
    //             'message' => 'Group created successfully'
    //         ], 201);
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Failed to create group',
    //             'error' => $e->getMessage()
    //         ], 500);
    //     }
    // }

    /**
     * Create a new group (keeping old method name for backward compatibility)
     */
    public function saveGroup(Request $request): JsonResponse
    {
        $request->validate([
            'group_name' => 'required|string|max:255',
            'group_description' => 'required|string',
            'group_location' => 'required|string|max:255',
            'group_tags' => 'nullable|string|max:500',
            'group_privacy' => ['required', Rule::in(['public', 'private', 'closed'])],
            'require_approval' => 'boolean',
        ]);

        try {
            DB::beginTransaction();

            $group = Group::create([
                'group_name' => $request->group_name,
                'group_description' => $request->group_description,
                'group_location' => $request->group_location,
                'group_tags' => $request->group_tags,
                'group_privacy' => $request->group_privacy,
                'require_approval' => $request->boolean('require_approval'),
                'created_by' => Auth::id(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'group' => $group,
                'message' => 'Group created successfully'
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create group',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Save group categories and subcategories
     */
    // public function saveGroupCategories(Request $request): JsonResponse
    // {
    //     $request->validate([
    //         'group_id' => 'required|integer|exists:groups,id',
    //         'category_id' => 'required|integer|exists:group_categories,id',
    //         'subcategory_ids' => 'required|array|min:1',
    //         'subcategory_ids.*' => 'integer|exists:group_sub_categories,id',
    //     ]);

    //     try {
    //         DB::beginTransaction();

    //         $group = Group::find($request->group_id);

    //         // Check if user owns the group
    //         if ($group->created_by !== Auth::id()) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Unauthorized to modify this group'
    //             ], 403);
    //         }

    //         // Verify that all subcategories belong to the selected category
    //         $validSubcategoryIds = GroupSubCategory::where('category_id', $request->category_id)
    //             ->whereIn('id', $request->subcategory_ids)
    //             ->pluck('id')
    //             ->toArray();

    //         if (count($validSubcategoryIds) !== count($request->subcategory_ids)) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Some subcategories do not belong to the selected category'
    //             ], 422);
    //         }

    //         // Clear existing category mappings
    //         GroupCategoryMapping::where('group_id', $group->id)->delete();
    //         GroupSubcategoryMapping::where('group_id', $group->id)->delete();

    //         // Save main category
    //         GroupCategoryMapping::create([
    //             'group_id' => $group->id,
    //             'category_id' => $request->category_id,
    //         ]);

    //         // Save subcategories
    //         foreach ($request->subcategory_ids as $subcategoryId) {
    //             GroupSubcategoryMapping::create([
    //                 'group_id' => $group->id,
    //                 'subcategory_id' => $subcategoryId,
    //             ]);
    //         }

    //         DB::commit();

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Group categories saved successfully'
    //         ]);
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Failed to save group categories',
    //             'error' => $e->getMessage()
    //         ], 500);
    //     }
    // }

    // /**
    //  * Upload group image
    //  */
    // public function uploadGroupImage(Request $request): JsonResponse
    // {
    //     $request->validate([
    //         'group_id' => 'required|integer|exists:groups,id',
    //         'group_image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
    //     ]);

    //     try {
    //         $group = Group::find($request->group_id);

    //         if ($group->created_by !== Auth::id()) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Unauthorized to modify this group'
    //             ], 403);
    //         }

    //         // Delete old image if exists
    //         if ($group->group_image) {
    //             Storage::disk('public')->delete($group->group_image);
    //         }

    //         // Upload new image
    //         $imagePath = $request->file('group_image')->store('group-images', 'public');

    //         $group->update(['group_image' => $imagePath]);

    //         return response()->json([
    //             'success' => true,
    //             'image_path' => $imagePath,
    //             'message' => 'Group image uploaded successfully'
    //         ]);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Failed to upload group image',
    //             'error' => $e->getMessage()
    //         ], 500);
    //     }
    // }

    /**
     * Upload group cover image
     */
    public function uploadGroupCoverImage(Request $request): JsonResponse
    {
        $request->validate([
            'group_id' => 'required|integer|exists:groups,id',
            'cover_image' => 'required|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        try {
            $group = Group::find($request->group_id);

            if ($group->created_by !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to modify this group'
                ], 403);
            }

            // Delete old cover image if exists
            if ($group->cover_image) {
                Storage::disk('public')->delete($group->cover_image);
            }

            // Upload new cover image
            $imagePath = $request->file('cover_image')->store('group-covers', 'public');

            $group->update(['cover_image' => $imagePath]);

            return response()->json([
                'success' => true,
                'image_path' => $imagePath,
                'message' => 'Group cover image uploaded successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload group cover image',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get group details with categories
     */
    // public function getGroupDetails(int $groupId): JsonResponse
    // {
    //     try {
    //         $group = Group::find($groupId);

    //         if (!$group) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Group not found'
    //             ], 404);
    //         }

    //         // Get categories manually without relationships
    //         $categoryMappings = GroupCategoryMapping::where('group_id', $groupId)->get();
    //         $subcategoryMappings = GroupSubcategoryMapping::where('group_id', $groupId)->get();

    //         $categories = [];
    //         foreach ($categoryMappings as $mapping) {
    //             $category = GroupCategory::find($mapping->category_id);
    //             if ($category) {
    //                 $categories[] = [
    //                     'id' => $category->id,
    //                     'name' => $category->name,
    //                     'description' => $category->description
    //                 ];
    //             }
    //         }

    //         $subcategories = [];
    //         foreach ($subcategoryMappings as $mapping) {
    //             $subcategory = GroupSubCategory::find($mapping->subcategory_id);
    //             if ($subcategory) {
    //                 $subcategories[] = [
    //                     'id' => $subcategory->id,
    //                     'name' => $subcategory->name
    //                 ];
    //             }
    //         }

    //         // Add categories and subcategories to group data
    //         $groupData = $group->toArray();
    //         $groupData['categories'] = $categories;
    //         $groupData['subcategories'] = $subcategories;

    //         return response()->json([
    //             'success' => true,
    //             'data' => $groupData,
    //             'message' => 'Group details retrieved successfully'
    //         ]);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Failed to retrieve group details',
    //             'error' => $e->getMessage()
    //         ], 500);
    //     }
    // }
}
