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
use Illuminate\Support\Facades\Log;
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
            Log::error('Failed to retrieve categories', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

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
            Log::error('Failed to retrieve subcategories', [
                'error' => $e->getMessage(),
                'category_id' => $request->get('category_id'),
                'user_id' => Auth::id()
            ]);

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
    public function createGroup(Request $request): JsonResponse
    {
        // Log the incoming request
        Log::info('Group creation attempt', [
            'user_id' => Auth::id(),
            'request_data' => $request->except(['group_image', 'cover_image']),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        try {
            $validatedData = $request->validate([
                'group_name' => 'required|string|max:255',
                'group_description' => 'required|string',
                'group_location' => 'required|string|max:255',
                'group_tags' => 'nullable|string|max:500',
                'group_privacy' => ['required', Rule::in(['public', 'private', 'closed'])],
                'require_approval' => 'boolean',
                'category_id' => 'required|integer|exists:group_categories,id',
                'subcategory_ids' => 'required|array|min:1',
                'subcategory_ids.*' => 'integer|exists:group_sub_categories,id',
            ]);

            Log::info('Group creation validation passed', [
                'user_id' => Auth::id(),
                'group_name' => $validatedData['group_name']
            ]);

            DB::beginTransaction();

            try {
                // Verify that all subcategories belong to the selected category
                Log::debug('Validating subcategories belong to category', [
                    'category_id' => $request->category_id,
                    'subcategory_ids' => $request->subcategory_ids
                ]);

                $validSubcategoryIds = GroupSubCategory::where('category_id', $request->category_id)
                    ->whereIn('id', $request->subcategory_ids)
                    ->pluck('id')
                    ->toArray();

                if (count($validSubcategoryIds) !== count($request->subcategory_ids)) {
                    Log::warning('Subcategory validation failed', [
                        'user_id' => Auth::id(),
                        'category_id' => $request->category_id,
                        'requested_subcategories' => $request->subcategory_ids,
                        'valid_subcategories' => $validSubcategoryIds,
                        'invalid_subcategories' => array_diff($request->subcategory_ids, $validSubcategoryIds)
                    ]);

                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Some subcategories do not belong to the selected category'
                    ], 422);
                }

                Log::debug('Subcategory validation passed', [
                    'valid_subcategories_count' => count($validSubcategoryIds)
                ]);

                // Create the group
                Log::debug('Creating group record');
                $group = Group::create([
                    'group_name' => $request->group_name,
                    'group_description' => $request->group_description,
                    'group_location' => $request->group_location,
                    'group_tags' => $request->group_tags,
                    'group_privacy' => $request->group_privacy,
                    'require_approval' => $request->boolean('require_approval'),
                    'created_by' => Auth::id(),
                ]);

                Log::info('Group created successfully', [
                    'group_id' => $group->id,
                    'group_name' => $group->group_name,
                    'created_by' => $group->created_by
                ]);

                // Save category mapping
                Log::debug('Creating category mapping', [
                    'group_id' => $group->id,
                    'category_id' => $request->category_id
                ]);

                $categoryMapping = GroupCategoryMapping::create([
                    'group_id' => $group->id,
                    'category_id' => $request->category_id,
                ]);

                Log::debug('Category mapping created', [
                    'mapping_id' => $categoryMapping->id
                ]);

                // Save subcategory mappings
                Log::debug('Creating subcategory mappings', [
                    'group_id' => $group->id,
                    'subcategory_count' => count($request->subcategory_ids)
                ]);

                $subcategoryMappings = [];
                foreach ($request->subcategory_ids as $subcategoryId) {
                    $mapping = GroupSubcategoryMapping::create([
                        'group_id' => $group->id,
                        'subcategory_id' => $subcategoryId,
                    ]);
                    $subcategoryMappings[] = $mapping->id;
                }

                Log::debug('Subcategory mappings created', [
                    'mapping_ids' => $subcategoryMappings
                ]);

                DB::commit();

                Log::info('Group creation completed successfully', [
                    'group_id' => $group->id,
                    'user_id' => Auth::id(),
                    'category_mapping_id' => $categoryMapping->id,
                    'subcategory_mapping_ids' => $subcategoryMappings
                ]);

                return response()->json([
                    'success' => true,
                    'group' => $group->fresh(), // Get fresh instance with all data
                    'message' => 'Group created successfully'
                ], 201);
            } catch (\Exception $e) {
                DB::rollBack();

                Log::error('Database transaction failed during group creation', [
                    'user_id' => Auth::id(),
                    'error' => $e->getMessage(),
                    'error_code' => $e->getCode(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                    'request_data' => $request->except(['group_image', 'cover_image'])
                ]);

                throw $e; // Re-throw to be caught by outer catch block
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Group creation validation failed', [
                'user_id' => Auth::id(),
                'validation_errors' => $e->errors(),
                'request_data' => $request->except(['group_image', 'cover_image'])
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Database query error during group creation', [
                'user_id' => Auth::id(),
                'sql_error' => $e->getMessage(),
                'sql_code' => $e->getCode(),
                'bindings' => $e->getBindings() ?? [],
                'request_data' => $request->except(['group_image', 'cover_image'])
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Database error occurred. Please try again.',
                'error_code' => 'DB_ERROR'
            ], 500);
        } catch (\Exception $e) {
            Log::error('Unexpected error during group creation', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'error_class' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => config('app.debug') ? $e->getTraceAsString() : 'Trace hidden in production',
                'request_data' => $request->except(['group_image', 'cover_image'])
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred. Please try again.',
                'error_code' => 'GENERAL_ERROR'
            ], 500);
        }
    }

    /**
     * Upload group image
     */
    public function uploadGroupImage(Request $request): JsonResponse
    {
        try {
            $validatedData = $request->validate([
                'group_id' => 'required|integer|exists:groups,id',
                'group_image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            ]);

            $group = Group::find($request->group_id);

            // Check if user owns the group
            if ($group->created_by !== Auth::id()) {
                Log::warning('Unauthorized group image upload attempt', [
                    'user_id' => Auth::id(),
                    'group_id' => $request->group_id,
                    'group_owner' => $group->created_by
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to modify this group'
                ], 403);
            }

            // Delete old image if exists
            if ($group->group_image && Storage::disk('public')->exists($group->group_image)) {
                Storage::disk('public')->delete($group->group_image);
                Log::debug('Deleted old group image', [
                    'group_id' => $group->id,
                    'old_image_path' => $group->group_image
                ]);
            }

            // Upload new image
            $imagePath = $request->file('group_image')->store('group-images', 'public');

            // Update group record
            $group->update(['group_image' => $imagePath]);

            Log::info('Group image uploaded successfully', [
                'user_id' => Auth::id(),
                'group_id' => $request->group_id,
                'image_path' => $imagePath,
                'file_size' => $request->file('group_image')->getSize()
            ]);

            return response()->json([
                'success' => true,
                'image_path' => $imagePath,
                'image_url' => Storage::disk('public')->url($imagePath),
                'message' => 'Group image uploaded successfully'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Group image upload validation failed', [
                'user_id' => Auth::id(),
                'group_id' => $request->group_id ?? null,
                'validation_errors' => $e->errors()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Group image upload failed', [
                'user_id' => Auth::id(),
                'group_id' => $request->group_id ?? null,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to upload group image. Please try again.'
            ], 500);
        }
    }

    /**
     * Upload group cover image
     */
    public function uploadGroupCoverImage(Request $request): JsonResponse
    {
        try {
            $validatedData = $request->validate([
                'group_id' => 'required|integer|exists:groups,id',
                'cover_image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // 5MB max
            ]);

            $group = Group::find($request->group_id);

            // Check if user owns the group
            if ($group->created_by !== Auth::id()) {
                Log::warning('Unauthorized group cover image upload attempt', [
                    'user_id' => Auth::id(),
                    'group_id' => $request->group_id,
                    'group_owner' => $group->created_by
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to modify this group'
                ], 403);
            }

            // Delete old cover image if exists
            if ($group->cover_image && Storage::disk('public')->exists($group->cover_image)) {
                Storage::disk('public')->delete($group->cover_image);
                Log::debug('Deleted old group cover image', [
                    'group_id' => $group->id,
                    'old_cover_path' => $group->cover_image
                ]);
            }

            // Upload new cover image
            $imagePath = $request->file('cover_image')->store('group-covers', 'public');

            // Update group record
            $group->update(['cover_image' => $imagePath]);

            Log::info('Group cover image uploaded successfully', [
                'user_id' => Auth::id(),
                'group_id' => $request->group_id,
                'cover_path' => $imagePath,
                'file_size' => $request->file('cover_image')->getSize()
            ]);

            return response()->json([
                'success' => true,
                'image_path' => $imagePath,
                'image_url' => Storage::disk('public')->url($imagePath),
                'message' => 'Group cover image uploaded successfully'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Group cover image upload validation failed', [
                'user_id' => Auth::id(),
                'group_id' => $request->group_id ?? null,
                'validation_errors' => $e->errors()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Group cover image upload failed', [
                'user_id' => Auth::id(),
                'group_id' => $request->group_id ?? null,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to upload group cover image. Please try again.'
            ], 500);
        }
    }

    /**
     * Get group details with categories
     */
    public function getGroupDetails(int $groupId): JsonResponse
    {
        try {
            $group = Group::find($groupId);

            if (!$group) {
                return response()->json([
                    'success' => false,
                    'message' => 'Group not found'
                ], 404);
            }

            // Get categories manually without relationships
            $categoryMappings = GroupCategoryMapping::where('group_id', $groupId)->get();
            $subcategoryMappings = GroupSubcategoryMapping::where('group_id', $groupId)->get();

            $categories = [];
            foreach ($categoryMappings as $mapping) {
                $category = GroupCategory::find($mapping->category_id);
                if ($category) {
                    $categories[] = [
                        'id' => $category->id,
                        'name' => $category->name,
                        'description' => $category->description,
                        'slug' => $category->slug
                    ];
                }
            }

            $subcategories = [];
            foreach ($subcategoryMappings as $mapping) {
                $subcategory = GroupSubCategory::find($mapping->subcategory_id);
                if ($subcategory) {
                    $subcategories[] = [
                        'id' => $subcategory->id,
                        'name' => $subcategory->name,
                        'description' => $subcategory->description,
                        'slug' => $subcategory->slug
                    ];
                }
            }

            // Add categories and subcategories to group data
            $groupData = $group->toArray();
            $groupData['categories'] = $categories;
            $groupData['subcategories'] = $subcategories;

            // Add full image URLs if images exist
            if ($group->group_image) {
                $groupData['group_image_url'] = Storage::disk('public')->url($group->group_image);
            }
            if ($group->cover_image) {
                $groupData['cover_image_url'] = Storage::disk('public')->url($group->cover_image);
            }

            Log::info('Group details retrieved', [
                'group_id' => $groupId,
                'user_id' => Auth::id(),
                'categories_count' => count($categories),
                'subcategories_count' => count($subcategories)
            ]);

            return response()->json([
                'success' => true,
                'data' => $groupData,
                'message' => 'Group details retrieved successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve group details', [
                'group_id' => $groupId,
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve group details',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user's groups with pagination
     */
    public function getUserGroups(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 10);
            $page = $request->get('page', 1);

            $groups = Group::where('created_by', Auth::id())
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            // Add image URLs and category info to each group
            $groups->getCollection()->transform(function ($group) {
                $groupData = $group->toArray();

                // Add full image URLs
                if ($group->group_image) {
                    $groupData['group_image_url'] = Storage::disk('public')->url($group->group_image);
                }
                if ($group->cover_image) {
                    $groupData['cover_image_url'] = Storage::disk('public')->url($group->cover_image);
                }

                // Get categories for this group
                $categoryMappings = GroupCategoryMapping::where('group_id', $group->id)->get();
                $categories = [];
                foreach ($categoryMappings as $mapping) {
                    $category = GroupCategory::find($mapping->category_id);
                    if ($category) {
                        $categories[] = [
                            'id' => $category->id,
                            'name' => $category->name
                        ];
                    }
                }
                $groupData['categories'] = $categories;

                return $groupData;
            });

            return response()->json([
                'success' => true,
                'data' => $groups->items(),
                'pagination' => [
                    'current_page' => $groups->currentPage(),
                    'last_page' => $groups->lastPage(),
                    'per_page' => $groups->perPage(),
                    'total' => $groups->total(),
                    'has_more' => $groups->hasMorePages()
                ],
                'message' => 'User groups retrieved successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve user groups', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve user groups'
            ], 500);
        }
    }

    /**
     * Delete a group
     */
    public function deleteGroup(int $groupId): JsonResponse
    {
        try {
            $group = Group::find($groupId);

            if (!$group) {
                return response()->json([
                    'success' => false,
                    'message' => 'Group not found'
                ], 404);
            }

            // Check if user owns the group
            if ($group->created_by !== Auth::id()) {
                Log::warning('Unauthorized group deletion attempt', [
                    'user_id' => Auth::id(),
                    'group_id' => $groupId,
                    'group_owner' => $group->created_by
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to delete this group'
                ], 403);
            }

            DB::beginTransaction();

            try {
                // Delete images from storage
                if ($group->group_image && Storage::disk('public')->exists($group->group_image)) {
                    Storage::disk('public')->delete($group->group_image);
                }
                if ($group->cover_image && Storage::disk('public')->exists($group->cover_image)) {
                    Storage::disk('public')->delete($group->cover_image);
                }

                // Delete category and subcategory mappings
                GroupCategoryMapping::where('group_id', $groupId)->delete();
                GroupSubcategoryMapping::where('group_id', $groupId)->delete();

                // Delete the group
                $group->delete();

                DB::commit();

                Log::info('Group deleted successfully', [
                    'group_id' => $groupId,
                    'user_id' => Auth::id(),
                    'group_name' => $group->group_name
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Group deleted successfully'
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            Log::error('Failed to delete group', [
                'group_id' => $groupId,
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete group. Please try again.'
            ], 500);
        }
    }

    // =============================================================================
    // BACKWARD COMPATIBILITY METHODS (Legacy API endpoints)
    // =============================================================================

    /**
     * Get active group categories (keeping the old method for backward compatibility)
     */
    public function getActiveCategories(): JsonResponse
    {
        return $this->getCategories(); // Simply call the new method
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
}
