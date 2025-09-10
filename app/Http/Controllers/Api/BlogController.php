<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class BlogController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Blog::published()->latest('published_at');
        
        if ($request->has('featured')) {
            $query->featured();
        }
        
        if ($request->has('trending')) {
            $query->trending();
        }
        
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('excerpt', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }
        
        if ($request->has('tags')) {
            $tags = explode(',', $request->get('tags'));
            $query->where(function($q) use ($tags) {
                foreach ($tags as $tag) {
                    $q->orWhereJsonContains('tags', trim($tag));
                }
            });
        }
        
        $perPage = $request->get('per_page', 10);
        $blogs = $query->paginate($perPage);
        
        return response()->json([
            'success' => true,
            'data' => $blogs->items(),
            'pagination' => [
                'current_page' => $blogs->currentPage(),
                'last_page' => $blogs->lastPage(),
                'per_page' => $blogs->perPage(),
                'total' => $blogs->total()
            ]
        ]);
    }
    
    public function show($slug): JsonResponse
    {
        $blog = Blog::published()
            ->where('slug', $slug)
            ->firstOrFail();
        
        $blog->incrementViews();
        
        $relatedBlogs = Blog::published()
            ->where('id', '!=', $blog->id)
            ->when($blog->tags, function($query) use ($blog) {
                $query->where(function($q) use ($blog) {
                    foreach ($blog->tags as $tag) {
                        $q->orWhereJsonContains('tags', $tag);
                    }
                });
            })
            ->limit(3)
            ->get();
        
        return response()->json([
            'success' => true,
            'data' => [
                'blog' => $blog,
                'related_blogs' => $relatedBlogs
            ]
        ]);
    }
    
    public function trending(): JsonResponse
    {
        $blogs = Blog::published()
            ->trending()
            ->latest('published_at')
            ->limit(5)
            ->get();
        
        return response()->json([
            'success' => true,
            'data' => $blogs
        ]);
    }
    
    public function featured(): JsonResponse
    {
        $blogs = Blog::published()
            ->featured()
            ->latest('published_at')
            ->limit(3)
            ->get();
        
        return response()->json([
            'success' => true,
            'data' => $blogs
        ]);
    }
    
    public function latestTrends(): JsonResponse
    {
        $trending = Blog::published()
            ->trending()
            ->latest('published_at')
            ->limit(5)
            ->get();
        
        $featured = Blog::published()
            ->featured()
            ->latest('published_at')
            ->limit(3)
            ->get();
        
        $recent = Blog::published()
            ->latest('published_at')
            ->limit(8)
            ->get();
        
        return response()->json([
            'success' => true,
            'data' => [
                'trending' => $trending,
                'featured' => $featured,
                'recent' => $recent
            ]
        ]);
    }
    
    public function tags(): JsonResponse
    {
        $tags = Blog::published()
            ->whereNotNull('tags')
            ->pluck('tags')
            ->flatten()
            ->unique()
            ->values();
        
        return response()->json([
            'success' => true,
            'data' => $tags
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        // Handle tags sent as individual fields (tags[0], tags[1], etc.)
        $tags = [];
        foreach ($request->all() as $key => $value) {
            if (preg_match('/^tags\[(\d+)\]$/', $key)) {
                $tags[] = $value;
            }
        }
        
        // Convert string booleans to actual booleans
        $isFeatured = filter_var($request->get('is_featured', false), FILTER_VALIDATE_BOOLEAN);
        $isTrending = filter_var($request->get('is_trending', false), FILTER_VALIDATE_BOOLEAN);

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'excerpt' => 'required|string|max:500',
            'content' => 'required|string',
            'status' => 'required|in:draft,published',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120'
        ]);

        // Validate tags separately if they exist
        if (!empty($tags)) {
            foreach ($tags as $tag) {
                if (!is_string($tag) || strlen($tag) > 50) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Validation failed',
                        'errors' => ['tags' => ['Each tag must be a string with maximum 50 characters']]
                    ], 422);
                }
            }
        }

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $blogData = $request->except(['featured_image']);
        $blogData['author_name'] = auth()->user()->name ?? 'Anonymous';
        $blogData['tags'] = !empty($tags) ? $tags : null;
        $blogData['is_featured'] = $isFeatured;
        $blogData['is_trending'] = $isTrending;
        
        if ($request->get('status') === 'published') {
            $blogData['published_at'] = now();
        }

        if ($request->hasFile('featured_image')) {
            $imagePath = $request->file('featured_image')->store('blog_images', 'public');
            $blogData['featured_image'] = Storage::url($imagePath);
        }

        $blog = Blog::create($blogData);

        return response()->json([
            'success' => true,
            'message' => 'Blog created successfully',
            'data' => $blog
        ], 201);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $blog = Blog::findOrFail($id);

        // Handle tags sent as individual fields (tags[0], tags[1], etc.)
        $tags = [];
        foreach ($request->all() as $key => $value) {
            if (preg_match('/^tags\[(\d+)\]$/', $key)) {
                $tags[] = $value;
            }
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'excerpt' => 'sometimes|required|string|max:500',
            'content' => 'sometimes|required|string',
            'status' => 'sometimes|required|in:draft,published',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120'
        ]);

        // Validate tags separately if they exist
        if (!empty($tags)) {
            foreach ($tags as $tag) {
                if (!is_string($tag) || strlen($tag) > 50) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Validation failed',
                        'errors' => ['tags' => ['Each tag must be a string with maximum 50 characters']]
                    ], 422);
                }
            }
        }

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $blogData = $request->except(['featured_image', '_method']);
        
        // Handle boolean conversions
        if ($request->has('is_featured')) {
            $blogData['is_featured'] = filter_var($request->get('is_featured'), FILTER_VALIDATE_BOOLEAN);
        }
        if ($request->has('is_trending')) {
            $blogData['is_trending'] = filter_var($request->get('is_trending'), FILTER_VALIDATE_BOOLEAN);
        }
        
        // Handle tags
        if (!empty($tags)) {
            $blogData['tags'] = $tags;
        }

        if ($request->get('status') === 'published' && $blog->status !== 'published') {
            $blogData['published_at'] = now();
        }

        if ($request->hasFile('featured_image')) {
            if ($blog->featured_image) {
                $oldImagePath = str_replace('/storage/', '', $blog->featured_image);
                Storage::disk('public')->delete($oldImagePath);
            }
            
            $imagePath = $request->file('featured_image')->store('blog_images', 'public');
            $blogData['featured_image'] = Storage::url($imagePath);
        }

        $blog->update($blogData);

        return response()->json([
            'success' => true,
            'message' => 'Blog updated successfully',
            'data' => $blog
        ]);
    }

    public function destroy($id): JsonResponse
    {
        $blog = Blog::findOrFail($id);

        if ($blog->featured_image) {
            $imagePath = str_replace('/storage/', '', $blog->featured_image);
            Storage::disk('public')->delete($imagePath);
        }

        $blog->delete();

        return response()->json([
            'success' => true,
            'message' => 'Blog deleted successfully'
        ]);
    }

    public function uploadFeaturedImage(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120',
            'blog_id' => 'nullable|exists:blogs,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $imagePath = $request->file('image')->store('blog_images', 'public');
        $imageUrl = Storage::url($imagePath);

        if ($request->blog_id) {
            $blog = Blog::find($request->blog_id);
            if ($blog && $blog->featured_image) {
                $oldImagePath = str_replace('/storage/', '', $blog->featured_image);
                Storage::disk('public')->delete($oldImagePath);
            }
            
            if ($blog) {
                $blog->update(['featured_image' => $imageUrl]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Image uploaded successfully',
            'data' => [
                'image_url' => $imageUrl,
                'image_path' => $imagePath
            ]
        ]);
    }

    public function getUserBlogs(Request $request): JsonResponse
    {
        $query = Blog::where('author_name', auth()->user()->name)
            ->latest('created_at');

        if ($request->has('status')) {
            $query->where('status', $request->get('status'));
        }

        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('excerpt', 'like', "%{$search}%");
            });
        }

        $perPage = $request->get('per_page', 10);
        $blogs = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $blogs->items(),
            'pagination' => [
                'current_page' => $blogs->currentPage(),
                'last_page' => $blogs->lastPage(),
                'per_page' => $blogs->perPage(),
                'total' => $blogs->total()
            ]
        ]);
    }
}
