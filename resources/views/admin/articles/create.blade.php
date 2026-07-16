@extends('layouts.dashboard')

@section('content')
    <div class="mb-4">
        <h3>Add Article</h3>
        <p class="text-muted">
            Tambahkan artikel analisis terkait risiko rantai pasok global.
        </p>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.articles.store') }}">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Title</label>
                    <input type="text"
                           name="title"
                           class="form-control @error('title') is-invalid @enderror"
                           value="{{ old('title') }}"
                           placeholder="Contoh: Analisis Risiko Impor dari China"
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
                           value="{{ old('category') }}"
                           placeholder="Contoh: Risk Analysis / Economy / Logistics">

                    @error('category')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Content</label>
                    <textarea name="content"
                              rows="8"
                              class="form-control @error('content') is-invalid @enderror"
                              placeholder="Tulis isi artikel analisis di sini..."
                              required>{{ old('content') }}</textarea>

                    @error('content')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Published At</label>
                    <input type="datetime-local"
                           name="published_at"
                           class="form-control @error('published_at') is-invalid @enderror"
                           value="{{ old('published_at') }}">

                    @error('published_at')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        Save Article
                    </button>

                    <a href="{{ route('admin.articles.index') }}" class="btn btn-secondary">
                        Back
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection