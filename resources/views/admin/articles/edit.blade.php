@extends('layouts.dashboard')

@section('content')
    <div class="mb-4">
        <h3>Edit Article</h3>
        <p class="text-muted">
            Ubah artikel analisis supply chain.
        </p>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.articles.update', $article->id) }}">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label">Title</label>
                    <input type="text"
                           name="title"
                           class="form-control @error('title') is-invalid @enderror"
                           value="{{ old('title', $article->title) }}"
                           required>

                    @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Category</label>
                    <input type="text"
                           name="category"
                           class="form-control @error('category') is-invalid @enderror"
                           value="{{ old('category', $article->category) }}">

                    @error('category')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Content</label>
                    <textarea name="content"
                              rows="8"
                              class="form-control @error('content') is-invalid @enderror"
                              required>{{ old('content', $article->content) }}</textarea>

                    @error('content')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Published At</label>
                    <input type="datetime-local"
                           name="published_at"
                           class="form-control @error('published_at') is-invalid @enderror"
                           value="{{ old('published_at', $article->published_at ? date('Y-m-d\TH:i', strtotime($article->published_at)) : '') }}">

                    @error('published_at')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        Update Article
                    </button>

                    <a href="{{ route('admin.articles.index') }}" class="btn btn-secondary">
                        Back
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection