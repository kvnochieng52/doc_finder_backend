<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\ProductImage;

class ProductController extends Controller
{
    /**
     * Create a new product
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validatedData = $request->validate([
                'product_name' => 'required|string|max:255',
                'product_description' => 'required|string',
                'product_location' => 'required|string|max:255',
                'product_tags' => 'nullable|string|max:500',
            ]);

            DB::beginTransaction();

            $product = Product::create([
                'product_name' => $validatedData['product_name'],
                'product_description' => $validatedData['product_description'],
                'product_location' => $validatedData['product_location'],
                'product_tags' => $validatedData['product_tags'],
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]);

            DB::commit();

            Log::info('Product created successfully', [
                'user_id' => Auth::id(),
                'product_id' => $product->id,
                'product_name' => $product->product_name
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Product created successfully',
                'product' => [
                    'id' => $product->id,
                    'product_name' => $product->product_name,
                    'product_description' => $product->product_description,
                    'product_location' => $product->product_location,
                    'product_tags' => $product->product_tags,
                    'created_at' => $product->created_at,
                ]
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();

            Log::warning('Product creation validation failed', [
                'user_id' => Auth::id(),
                'validation_errors' => $e->errors()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Product creation failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create product. Please try again.'
            ], 500);
        }
    }

    /**
     * Upload multiple product images
     */
    public function uploadProductImages(Request $request): JsonResponse
    {
        try {
            $validatedData = $request->validate([
                'product_id' => 'required|integer|exists:products,id',
                'product_images' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // 5MB max per image
                'is_featured' => 'nullable|string|in:true,false',
            ]);

            $product = Product::find($request->product_id);

            // Check if user owns the product
            if ($product->created_by !== Auth::id()) {
                Log::warning('Unauthorized product image upload attempt', [
                    'user_id' => Auth::id(),
                    'product_id' => $request->product_id,
                    'product_owner' => $product->created_by
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to modify this product'
                ], 403);
            }

            $isFeatured = $request->is_featured === 'true';

            // If this is marked as featured, remove featured status from other images
            if ($isFeatured) {
                ProductImage::where('product_id', $product->id)
                    ->update(['is_featured' => false]);
            }

            // Upload the image
            $imagePath = $request->file('product_images')->store('product-images', 'public');

            // Create product image record
            $productImage = ProductImage::create([
                'product_id' => $product->id,
                'product_image' => $imagePath,
                'is_featured' => $isFeatured,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]);

            // Update product's featured image if this is the first image or marked as featured
            if ($isFeatured || $product->product_featured_image === null) {
                $product->update([
                    'product_featured_image' => $imagePath,
                    'updated_by' => Auth::id(),
                ]);
            }

            Log::info('Product image uploaded successfully', [
                'user_id' => Auth::id(),
                'product_id' => $request->product_id,
                'image_id' => $productImage->id,
                'image_path' => $imagePath,
                'is_featured' => $isFeatured,
                'file_size' => $request->file('product_images')->getSize()
            ]);

            return response()->json([
                'success' => true,
                'image_id' => $productImage->id,
                'image_path' => $imagePath,
                'image_url' => Storage::disk('public')->url($imagePath),
                'is_featured' => $isFeatured,
                'message' => 'Product image uploaded successfully'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Product image upload validation failed', [
                'user_id' => Auth::id(),
                'product_id' => $request->product_id ?? null,
                'validation_errors' => $e->errors()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Product image upload failed', [
                'user_id' => Auth::id(),
                'product_id' => $request->product_id ?? null,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to upload product image. Please try again.'
            ], 500);
        }
    }

    /**
     * Get all products
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 15);
            $page = $request->get('page', 1);

            $products = Product::with(['images' => function ($query) {
                $query->orderBy('is_featured', 'desc')
                    ->orderBy('created_at', 'asc');
            }])
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            // Transform the data to include image URLs
            $transformedProducts = $products->through(function ($product) {
                return [
                    'id' => $product->id,
                    'product_name' => $product->product_name,
                    'product_description' => $product->product_description,
                    'product_location' => $product->product_location,
                    'product_tags' => $product->product_tags,
                    'product_featured_image' => $product->product_featured_image
                        ? Storage::disk('public')->url($product->product_featured_image)
                        : null,
                    'images' => $product->images->map(function ($image) {
                        return [
                            'id' => $image->id,
                            'image_url' => Storage::disk('public')->url($image->product_image),
                            'is_featured' => $image->is_featured,
                        ];
                    }),
                    'created_by' => $product->created_by,
                    'created_at' => $product->created_at,
                    'updated_at' => $product->updated_at,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $transformedProducts->items(),
                'pagination' => [
                    'current_page' => $products->currentPage(),
                    'per_page' => $products->perPage(),
                    'total' => $products->total(),
                    'last_page' => $products->lastPage(),
                    'has_more_pages' => $products->hasMorePages(),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch products', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch products'
            ], 500);
        }
    }

    /**
     * Get user's products
     */
    public function userProducts(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 15);

            $products = Product::with(['images' => function ($query) {
                $query->orderBy('is_featured', 'desc')
                    ->orderBy('created_at', 'asc');
            }])
                ->where('created_by', Auth::id())
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            // Transform the data to include image URLs
            $transformedProducts = $products->through(function ($product) {
                return [
                    'id' => $product->id,
                    'product_name' => $product->product_name,
                    'product_description' => $product->product_description,
                    'product_location' => $product->product_location,
                    'product_tags' => $product->product_tags,
                    'product_featured_image' => $product->product_featured_image
                        ? Storage::disk('public')->url($product->product_featured_image)
                        : null,
                    'images' => $product->images->map(function ($image) {
                        return [
                            'id' => $image->id,
                            'image_url' => Storage::disk('public')->url($image->product_image),
                            'is_featured' => $image->is_featured,
                        ];
                    }),
                    'created_at' => $product->created_at,
                    'updated_at' => $product->updated_at,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $transformedProducts->items(),
                'pagination' => [
                    'current_page' => $products->currentPage(),
                    'per_page' => $products->perPage(),
                    'total' => $products->total(),
                    'last_page' => $products->lastPage(),
                    'has_more_pages' => $products->hasMorePages(),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch user products', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch your products'
            ], 500);
        }
    }

    /**
     * Get single product details
     */
    public function show($id): JsonResponse
    {
        try {
            $product = Product::with(['images' => function ($query) {
                $query->orderBy('is_featured', 'desc')
                    ->orderBy('created_at', 'asc');
            }])->find($id);

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found'
                ], 404);
            }

            $transformedProduct = [
                'id' => $product->id,
                'product_name' => $product->product_name,
                'product_description' => $product->product_description,
                'product_location' => $product->product_location,
                'product_tags' => $product->product_tags,
                'product_featured_image' => $product->product_featured_image
                    ? Storage::disk('public')->url($product->product_featured_image)
                    : null,
                'images' => $product->images->map(function ($image) {
                    return [
                        'id' => $image->id,
                        'image_url' => Storage::disk('public')->url($image->product_image),
                        'is_featured' => $image->is_featured,
                    ];
                }),
                'created_by' => $product->created_by,
                'created_at' => $product->created_at,
                'updated_at' => $product->updated_at,
            ];

            return response()->json([
                'success' => true,
                'data' => $transformedProduct
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch product details', [
                'product_id' => $id,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch product details'
            ], 500);
        }
    }

    /**
     * Update product
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $product = Product::find($id);

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found'
                ], 404);
            }

            // Check if user owns the product
            if ($product->created_by !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to modify this product'
                ], 403);
            }

            $validatedData = $request->validate([
                'product_name' => 'sometimes|required|string|max:255',
                'product_description' => 'sometimes|required|string',
                'product_location' => 'sometimes|required|string|max:255',
                'product_tags' => 'nullable|string|max:500',
            ]);

            $validatedData['updated_by'] = Auth::id();

            $product->update($validatedData);

            Log::info('Product updated successfully', [
                'user_id' => Auth::id(),
                'product_id' => $product->id,
                'updated_fields' => array_keys($validatedData)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Product updated successfully',
                'product' => [
                    'id' => $product->id,
                    'product_name' => $product->product_name,
                    'product_description' => $product->product_description,
                    'product_location' => $product->product_location,
                    'product_tags' => $product->product_tags,
                    'updated_at' => $product->updated_at,
                ]
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Product update failed', [
                'user_id' => Auth::id(),
                'product_id' => $id,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update product. Please try again.'
            ], 500);
        }
    }

    /**
     * Delete product
     */
    public function destroy($id): JsonResponse
    {
        try {
            $product = Product::find($id);

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found'
                ], 404);
            }

            // Check if user owns the product
            if ($product->created_by !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to delete this product'
                ], 403);
            }

            DB::beginTransaction();

            // Delete all product images from storage
            $productImages = ProductImage::where('product_id', $product->id)->get();
            foreach ($productImages as $image) {
                if (Storage::disk('public')->exists($image->product_image)) {
                    Storage::disk('public')->delete($image->product_image);
                }
            }

            // Delete featured image from storage if exists
            if ($product->product_featured_image && Storage::disk('public')->exists($product->product_featured_image)) {
                Storage::disk('public')->delete($product->product_featured_image);
            }

            // Delete product image records
            ProductImage::where('product_id', $product->id)->delete();

            // Delete the product
            $product->delete();

            DB::commit();

            Log::info('Product deleted successfully', [
                'user_id' => Auth::id(),
                'product_id' => $id,
                'images_deleted' => $productImages->count()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Product deleted successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Product deletion failed', [
                'user_id' => Auth::id(),
                'product_id' => $id,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete product. Please try again.'
            ], 500);
        }
    }

    /**
     * Delete a specific product image
     */
    public function deleteProductImage($imageId): JsonResponse
    {
        try {
            $productImage = ProductImage::find($imageId);

            if (!$productImage) {
                return response()->json([
                    'success' => false,
                    'message' => 'Image not found'
                ], 404);
            }

            $product = Product::find($productImage->product_id);

            // Check if user owns the product
            if ($product->created_by !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to modify this product'
                ], 403);
            }

            DB::beginTransaction();

            // Delete image from storage
            if (Storage::disk('public')->exists($productImage->product_image)) {
                Storage::disk('public')->delete($productImage->product_image);
            }

            $wasFeatured = $productImage->is_featured;

            // Delete the image record
            $productImage->delete();

            // If the deleted image was featured, update the product's featured image
            if ($wasFeatured) {
                $newFeaturedImage = ProductImage::where('product_id', $product->id)
                    ->orderBy('created_at', 'asc')
                    ->first();

                if ($newFeaturedImage) {
                    $newFeaturedImage->update(['is_featured' => true]);
                    $product->update([
                        'product_featured_image' => $newFeaturedImage->product_image,
                        'updated_by' => Auth::id(),
                    ]);
                } else {
                    // No images left, remove featured image
                    $product->update([
                        'product_featured_image' => null,
                        'updated_by' => Auth::id(),
                    ]);
                }
            }

            DB::commit();

            Log::info('Product image deleted successfully', [
                'user_id' => Auth::id(),
                'product_id' => $product->id,
                'image_id' => $imageId,
                'was_featured' => $wasFeatured
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Product image deleted successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Product image deletion failed', [
                'user_id' => Auth::id(),
                'image_id' => $imageId,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete product image. Please try again.'
            ], 500);
        }
    }
}
