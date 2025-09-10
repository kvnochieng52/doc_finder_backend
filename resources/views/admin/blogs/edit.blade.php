@extends('admin.layouts.app')

@section('title', 'Edit Blog')

@section('page-title', 'Edit Blog')

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('admin.blogs.index') }}">Home</a></li>
<li class="breadcrumb-item"><a href="{{ route('admin.blogs.index') }}">Blogs</a></li>
<li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Edit Blog: {{ $blog->title }}</h3>
        <div class="card-tools">
          <a href="{{ route('admin.blogs.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to List
          </a>
        </div>
      </div>

      <form action="{{ route('admin.blogs.update', $blog) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="card-body">
          @if($errors->any())
            <div class="alert alert-danger">
              <ul class="mb-0">
                @foreach($errors->all() as $error)
                  <li>{{ $error }}</li>
                @endforeach
              </ul>
            </div>
          @endif

          <div class="row">
            <div class="col-md-8">
              <div class="form-group">
                <label for="title">Title <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('title') is-invalid @enderror" 
                       id="title" name="title" value="{{ old('title', $blog->title) }}" required>
                @error('title')
                  <span class="invalid-feedback">{{ $message }}</span>
                @enderror
              </div>

              <div class="form-group">
                <label for="excerpt">Excerpt</label>
                <textarea class="form-control @error('excerpt') is-invalid @enderror" 
                          id="excerpt" name="excerpt" rows="3" maxlength="500">{{ old('excerpt', $blog->excerpt) }}</textarea>
                <small class="form-text text-muted">Brief description of the blog post (max 500 characters)</small>
                @error('excerpt')
                  <span class="invalid-feedback">{{ $message }}</span>
                @enderror
              </div>

              <div class="form-group">
                <label for="content">Content <span class="text-danger">*</span></label>
                <textarea class="form-control @error('content') is-invalid @enderror" 
                          id="content" name="content" rows="15" required>{{ old('content', $blog->content) }}</textarea>
                @error('content')
                  <span class="invalid-feedback">{{ $message }}</span>
                @enderror
              </div>
            </div>

            <div class="col-md-4">
              <div class="form-group">
                <label for="status">Status <span class="text-danger">*</span></label>
                <select class="form-control @error('status') is-invalid @enderror" 
                        id="status" name="status" required>
                  <option value="draft" {{ old('status', $blog->status) == 'draft' ? 'selected' : '' }}>Draft</option>
                  <option value="published" {{ old('status', $blog->status) == 'published' ? 'selected' : '' }}>Published</option>
                  <option value="archived" {{ old('status', $blog->status) == 'archived' ? 'selected' : '' }}>Archived</option>
                </select>
                @error('status')
                  <span class="invalid-feedback">{{ $message }}</span>
                @enderror
              </div>

              <div class="form-group">
                <label for="author_name">Author Name</label>
                <input type="text" class="form-control @error('author_name') is-invalid @enderror" 
                       id="author_name" name="author_name" value="{{ old('author_name', $blog->author_name) }}">
                @error('author_name')
                  <span class="invalid-feedback">{{ $message }}</span>
                @enderror
              </div>

              <div class="form-group">
                <label for="tags">Tags</label>
                <input type="text" class="form-control @error('tags') is-invalid @enderror" 
                       id="tags" name="tags" value="{{ old('tags', $blog->tags ? implode(', ', $blog->tags) : '') }}" 
                       placeholder="tag1, tag2, tag3">
                <small class="form-text text-muted">Separate tags with commas</small>
                @error('tags')
                  <span class="invalid-feedback">{{ $message }}</span>
                @enderror
              </div>

              <div class="form-group">
                <label for="featured_image">Featured Image</label>
                @if($blog->featured_image)
                  <div class="mb-2">
                    <img src="{{ Storage::url($blog->featured_image) }}" alt="Current featured image" 
                         class="img-thumbnail" style="max-height: 150px;">
                    <p class="text-sm text-muted mt-1">Current featured image</p>
                  </div>
                @endif
                <div class="input-group">
                  <div class="custom-file">
                    <input type="file" class="custom-file-input @error('featured_image') is-invalid @enderror" 
                           id="featured_image" name="featured_image" accept="image/*">
                    <label class="custom-file-label" for="featured_image">Choose new file</label>
                  </div>
                </div>
                <small class="form-text text-muted">Leave empty to keep current image. Max size: 2MB. Supported formats: JPG, PNG, GIF</small>
                @error('featured_image')
                  <span class="invalid-feedback d-block">{{ $message }}</span>
                @enderror
              </div>

              <div class="form-group">
                <div class="custom-control custom-checkbox">
                  <input type="checkbox" class="custom-control-input" 
                         id="is_featured" name="is_featured" value="1" 
                         {{ old('is_featured', $blog->is_featured) ? 'checked' : '' }}>
                  <label class="custom-control-label" for="is_featured">Featured Blog</label>
                </div>
              </div>

              <div class="form-group">
                <div class="custom-control custom-checkbox">
                  <input type="checkbox" class="custom-control-input" 
                         id="is_trending" name="is_trending" value="1" 
                         {{ old('is_trending', $blog->is_trending) ? 'checked' : '' }}>
                  <label class="custom-control-label" for="is_trending">Trending Blog</label>
                </div>
              </div>

              <div class="form-group">
                <label>Blog Stats</label>
                <div class="info-box">
                  <span class="info-box-icon bg-info"><i class="fas fa-eye"></i></span>
                  <div class="info-box-content">
                    <span class="info-box-text">Views</span>
                    <span class="info-box-number">{{ number_format($blog->views_count) }}</span>
                  </div>
                </div>
              </div>

              @if($blog->published_at)
                <div class="form-group">
                  <label>Published Date</label>
                  <p class="form-control-plaintext">{{ $blog->published_at->format('F d, Y \a\t g:i A') }}</p>
                </div>
              @endif
            </div>
          </div>
        </div>

        <div class="card-footer">
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> Update Blog
          </button>
          <a href="{{ route('admin.blogs.index') }}" class="btn btn-secondary ml-2">Cancel</a>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.css">
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.js"></script>
<script>
$(document).ready(function() {
  // Initialize Summernote for content editor
  $('#content').summernote({
    height: 300,
    toolbar: [
      ['style', ['style']],
      ['font', ['bold', 'underline', 'clear']],
      ['fontname', ['fontname']],
      ['color', ['color']],
      ['para', ['ul', 'ol', 'paragraph']],
      ['table', ['table']],
      ['insert', ['link', 'picture', 'video']],
      ['view', ['fullscreen', 'codeview', 'help']]
    ]
  });

  // Custom file input label update
  $('.custom-file-input').on('change', function() {
    let fileName = $(this).val().split('\\').pop();
    $(this).next('.custom-file-label').addClass("selected").html(fileName);
  });
});
</script>
@endpush