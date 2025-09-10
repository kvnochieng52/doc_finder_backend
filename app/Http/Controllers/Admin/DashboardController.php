<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Blog;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $stats = [
            'total_blogs' => Blog::count(),
            'published_blogs' => Blog::where('status', 'published')->count(),
            'draft_blogs' => Blog::where('status', 'draft')->count(),
            'featured_blogs' => Blog::where('is_featured', true)->count(),
        ];

        $recent_blogs = Blog::latest()->limit(5)->get();

        return view('admin.dashboard', compact('stats', 'recent_blogs'));
    }
}