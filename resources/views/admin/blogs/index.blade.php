@extends('admin.layouts.app')

@section('title', 'Blog Management')

@section('page-title', 'Blog Management')

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('admin.blogs.index') }}">Home</a></li>
<li class="breadcrumb-item active">Blogs</li>
@endsection

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">All Blogs</h3>
        
        <div class="card-tools">
          <div class="input-group input-group-sm" style="width: 300px;">
            <form method="GET" action="{{ route('admin.blogs.index') }}" class="d-flex">
              <select name="status" class="form-control mr-2">
                <option value="">All Status</option>
                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Published</option>
                <option value="archived" {{ request('status') == 'archived' ? 'selected' : '' }}>Archived</option>
              </select>
              <input type="text" name="search" class="form-control" placeholder="Search blogs..." value="{{ request('search') }}">
              <div class="input-group-append">
                <button type="submit" class="btn btn-default">
                  <i class="fas fa-search"></i>
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
      
      <div class="card-body">
        <div class="mb-3">
          <a href="{{ route('admin.blogs.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Create New Blog
          </a>
        </div>

        <div class="table-responsive">
          <table class="table table-bordered table-striped">
            <thead>
              <tr>
                <th width="60">Image</th>
                <th>Title</th>
                <th>Author</th>
                <th>Status</th>
                <th>Views</th>
                <th>Tags</th>
                <th>Published Date</th>
                <th width="200">Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($blogs as $blog)
                <tr>
                  <td>
                    @if($blog->featured_image)
                      <img src="{{ Storage::url($blog->featured_image) }}" alt="{{ $blog->title }}" 
                           class="img-thumbnail" style="width: 50px; height: 50px; object-fit: cover;">
                    @else
                      <div class="bg-light d-flex align-items-center justify-content-center" 
                           style="width: 50px; height: 50px; border-radius: 4px;">
                        <i class="fas fa-image text-muted"></i>
                      </div>
                    @endif
                  </td>
                  <td>
                    <strong>{{ $blog->title }}</strong>
                    @if($blog->is_featured)
                      <span class="badge badge-warning ml-1">Featured</span>
                    @endif
                    @if($blog->is_trending)
                      <span class="badge badge-danger ml-1">Trending</span>
                    @endif
                    <br>
                    <small class="text-muted">{{ Str::limit($blog->excerpt, 60) }}</small>
                  </td>
                  <td>{{ $blog->author_name ?? 'N/A' }}</td>
                  <td>
                    @if($blog->status === 'published')
                      <span class="badge badge-success">Published</span>
                    @elseif($blog->status === 'draft')
                      <span class="badge badge-secondary">Draft</span>
                    @else
                      <span class="badge badge-dark">Archived</span>
                    @endif
                  </td>
                  <td>
                    <span class="badge badge-info">{{ number_format($blog->views_count) }}</span>
                  </td>
                  <td>
                    @if($blog->tags)
                      @foreach(array_slice($blog->tags, 0, 2) as $tag)
                        <span class="badge badge-light">{{ $tag }}</span>
                      @endforeach
                      @if(count($blog->tags) > 2)
                        <small class="text-muted">+{{ count($blog->tags) - 2 }} more</small>
                      @endif
                    @else
                      <span class="text-muted">No tags</span>
                    @endif
                  </td>
                  <td>
                    {{ $blog->published_at ? $blog->published_at->format('M d, Y') : 'Not published' }}
                  </td>
                  <td>
                    <div class="btn-group">
                      <a href="{{ route('admin.blogs.show', $blog) }}" class="btn btn-sm btn-info" title="View">
                        <i class="fas fa-eye"></i>
                      </a>
                      <a href="{{ route('admin.blogs.edit', $blog) }}" class="btn btn-sm btn-warning" title="Edit">
                        <i class="fas fa-edit"></i>
                      </a>
                      <button type="button" class="btn btn-sm btn-danger delete-blog" 
                              data-id="{{ $blog->id }}" data-title="{{ $blog->title }}" title="Delete">
                        <i class="fas fa-trash"></i>
                      </button>
                    </div>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="8" class="text-center py-4">
                    <i class="fas fa-blog fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No blogs found</h5>
                    <p class="text-muted">Create your first blog post to get started.</p>
                    <a href="{{ route('admin.blogs.create') }}" class="btn btn-primary">
                      <i class="fas fa-plus"></i> Create Blog
                    </a>
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        @if($blogs->hasPages())
          <div class="mt-3">
            {{ $blogs->withQueryString()->links() }}
          </div>
        @endif
      </div>
    </div>
  </div>
</div>

<!-- Delete form -->
<form id="delete-form" method="POST" style="display: none;">
  @csrf
  @method('DELETE')
</form>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
  $('.delete-blog').click(function() {
    const blogId = $(this).data('id');
    const blogTitle = $(this).data('title');
    
    Swal.fire({
      title: 'Are you sure?',
      html: `You want to delete the blog: <strong>"${blogTitle}"</strong>?`,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#3085d6',
      confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
      if (result.isConfirmed) {
        const form = document.getElementById('delete-form');
        form.action = `/admin/blogs/${blogId}`;
        form.submit();
      }
    });
  });
});
</script>
@endpush