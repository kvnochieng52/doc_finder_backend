@extends('admin.layouts.app')

@section('title', 'View Blog')

@section('page-title', 'View Blog')

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('admin.blogs.index') }}">Home</a></li>
<li class="breadcrumb-item"><a href="{{ route('admin.blogs.index') }}">Blogs</a></li>
<li class="breadcrumb-item active">View</li>
@endsection

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">{{ $blog->title }}</h3>
        <div class="card-tools">
          <a href="{{ route('admin.blogs.edit', $blog) }}" class="btn btn-warning">
            <i class="fas fa-edit"></i> Edit
          </a>
          <a href="{{ route('admin.blogs.index') }}" class="btn btn-secondary ml-2">
            <i class="fas fa-arrow-left"></i> Back to List
          </a>
        </div>
      </div>

      <div class="card-body">
        <div class="row">
          <div class="col-md-8">
            @if($blog->featured_image)
              <div class="mb-3">
                <img src="{{ Storage::url($blog->featured_image) }}" alt="{{ $blog->title }}" 
                     class="img-fluid rounded shadow">
              </div>
            @endif

            @if($blog->excerpt)
              <div class="mb-3">
                <h5>Excerpt</h5>
                <p class="text-muted">{{ $blog->excerpt }}</p>
              </div>
            @endif

            <div class="mb-3">
              <h5>Content</h5>
              <div class="content-area">
                {!! $blog->content !!}
              </div>
            </div>
          </div>

          <div class="col-md-4">
            <div class="card bg-light">
              <div class="card-header">
                <h5 class="card-title mb-0">Blog Details</h5>
              </div>
              <div class="card-body">
                <div class="mb-3">
                  <strong>Status:</strong>
                  @if($blog->status === 'published')
                    <span class="badge badge-success ml-1">Published</span>
                  @elseif($blog->status === 'draft')
                    <span class="badge badge-secondary ml-1">Draft</span>
                  @else
                    <span class="badge badge-dark ml-1">Archived</span>
                  @endif
                </div>

                @if($blog->author_name)
                  <div class="mb-3">
                    <strong>Author:</strong> {{ $blog->author_name }}
                  </div>
                @endif

                <div class="mb-3">
                  <strong>Slug:</strong> <code>{{ $blog->slug }}</code>
                </div>

                <div class="mb-3">
                  <strong>Views:</strong> {{ number_format($blog->views_count) }}
                </div>

                @if($blog->tags && count($blog->tags) > 0)
                  <div class="mb-3">
                    <strong>Tags:</strong><br>
                    @foreach($blog->tags as $tag)
                      <span class="badge badge-primary mr-1">{{ $tag }}</span>
                    @endforeach
                  </div>
                @endif

                <div class="mb-3">
                  <strong>Features:</strong><br>
                  @if($blog->is_featured)
                    <span class="badge badge-warning">Featured</span>
                  @endif
                  @if($blog->is_trending)
                    <span class="badge badge-danger ml-1">Trending</span>
                  @endif
                  @if(!$blog->is_featured && !$blog->is_trending)
                    <span class="text-muted">None</span>
                  @endif
                </div>

                <div class="mb-3">
                  <strong>Created:</strong><br>
                  {{ $blog->created_at->format('F d, Y \a\t g:i A') }}
                </div>

                <div class="mb-3">
                  <strong>Updated:</strong><br>
                  {{ $blog->updated_at->format('F d, Y \a\t g:i A') }}
                </div>

                @if($blog->published_at)
                  <div class="mb-3">
                    <strong>Published:</strong><br>
                    {{ $blog->published_at->format('F d, Y \a\t g:i A') }}
                  </div>
                @endif
              </div>
            </div>

            <div class="card mt-3">
              <div class="card-header">
                <h5 class="card-title mb-0">Quick Actions</h5>
              </div>
              <div class="card-body">
                <div class="d-grid gap-2">
                  <a href="{{ route('admin.blogs.edit', $blog) }}" class="btn btn-warning btn-block">
                    <i class="fas fa-edit"></i> Edit Blog
                  </a>
                  
                  @if($blog->status === 'published')
                    <button class="btn btn-info btn-block" onclick="copyToClipboard('{{ url('/api/blogs/' . $blog->slug) }}')">
                      <i class="fas fa-copy"></i> Copy API URL
                    </button>
                  @endif
                  
                  <button type="button" class="btn btn-danger btn-block delete-blog" 
                          data-id="{{ $blog->id }}" data-title="{{ $blog->title }}">
                    <i class="fas fa-trash"></i> Delete Blog
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
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

@push('styles')
<style>
.content-area {
  line-height: 1.6;
  font-size: 16px;
}

.content-area img {
  max-width: 100%;
  height: auto;
  border-radius: 8px;
  margin: 10px 0;
}

.content-area blockquote {
  border-left: 4px solid #007bff;
  padding-left: 15px;
  margin: 20px 0;
  font-style: italic;
  background-color: #f8f9fa;
  padding: 15px;
  border-radius: 4px;
}

.content-area code {
  background-color: #f8f9fa;
  padding: 2px 6px;
  border-radius: 3px;
  font-family: 'Courier New', monospace;
}

.content-area pre {
  background-color: #f8f9fa;
  padding: 15px;
  border-radius: 4px;
  overflow-x: auto;
}
</style>
@endpush

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

function copyToClipboard(text) {
  navigator.clipboard.writeText(text).then(function() {
    Swal.fire({
      title: 'Copied!',
      text: 'API URL copied to clipboard',
      icon: 'success',
      timer: 1500,
      showConfirmButton: false
    });
  });
}
</script>
@endpush