@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('breadcrumb')
    <li class="breadcrumb-item active">Dashboard</li>
@endsection

@section('content')
<!-- Small boxes (Stat box) -->
<div class="row">
    <div class="col-lg-3 col-6">
        <!-- small box -->
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $stats['total_blogs'] }}</h3>
                <p>Total Blogs</p>
            </div>
            <div class="icon">
                <i class="fas fa-blog"></i>
            </div>
            <a href="{{ route('admin.blogs.index') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <!-- ./col -->
    <div class="col-lg-3 col-6">
        <!-- small box -->
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $stats['published_blogs'] }}</h3>
                <p>Published Blogs</p>
            </div>
            <div class="icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <a href="{{ route('admin.blogs.index', ['status' => 'published']) }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <!-- ./col -->
    <div class="col-lg-3 col-6">
        <!-- small box -->
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $stats['draft_blogs'] }}</h3>
                <p>Draft Blogs</p>
            </div>
            <div class="icon">
                <i class="fas fa-edit"></i>
            </div>
            <a href="{{ route('admin.blogs.index', ['status' => 'draft']) }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <!-- ./col -->
    <div class="col-lg-3 col-6">
        <!-- small box -->
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>{{ $stats['featured_blogs'] }}</h3>
                <p>Featured Blogs</p>
            </div>
            <div class="icon">
                <i class="fas fa-star"></i>
            </div>
            <a href="{{ route('admin.blogs.index', ['featured' => 'true']) }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <!-- ./col -->
</div>
<!-- /.row -->

<!-- Main row -->
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Recent Blogs</h3>
            </div>
            <!-- /.card-header -->
            <div class="card-body p-0">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Status</th>
                            <th>Author</th>
                            <th>Views</th>
                            <th>Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recent_blogs as $blog)
                        <tr>
                            <td>
                                <a href="{{ route('admin.blogs.show', $blog) }}">{{ Str::limit($blog->title, 40) }}</a>
                            </td>
                            <td>
                                <span class="badge badge-{{ $blog->status === 'published' ? 'success' : 'warning' }}">
                                    {{ ucfirst($blog->status) }}
                                </span>
                            </td>
                            <td>{{ $blog->author_name }}</td>
                            <td>{{ number_format($blog->views_count) }}</td>
                            <td>{{ $blog->created_at->format('M d, Y') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center">No blogs found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <!-- /.card-body -->
        </div>
        <!-- /.card -->
    </div>
    <!-- /.col -->
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Quick Actions</h3>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('admin.blogs.create') }}" class="btn btn-primary btn-lg mb-2">
                        <i class="fas fa-plus"></i> Create New Blog
                    </a>
                    <a href="{{ route('admin.blogs.index') }}" class="btn btn-outline-primary btn-lg mb-2">
                        <i class="fas fa-list"></i> Manage Blogs
                    </a>
                </div>
            </div>
        </div>
    </div>
    <!-- /.col -->
</div>
<!-- /.row -->
@endsection