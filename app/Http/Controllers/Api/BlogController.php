<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

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
}
