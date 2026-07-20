@extends('layouts.dashboard')

@section('content')
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h1 class="page-title">Edit Article</h1>
            <p class="page-subtitle">
                Perbarui judul, kategori, isi, dan status publikasi artikel.
            </p>
        </div>

        <a href="{{ url('/admin/articles') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i>
            Back
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <strong>Terjadi kesalahan:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="mb-3">Edit Article Form</h5>

                    <form method="POST" action="{{ url('/admin/articles/' . $article->id) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label">Judul Artikel</label>
                            <input type="text"
                                   name="title"
                                   class="form-control"
                                   value="{{ old('title', $article->title) }}"
                                   required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Kategori</label>
                            <input type="text"
                                   name="category"
                                   class="form-control"
                                   value="{{ old('category', $article->category) }}"
                                   placeholder="Contoh: Risiko Cuaca, Risiko Kurs, Risk Scoring">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Tanggal Publikasi</label>
                            <input type="datetime-local"
                                   name="published_at"
                                   class="form-control"
                                   value="{{ old('published_at', $article->published_at ? \Carbon\Carbon::parse($article->published_at)->format('Y-m-d\TH:i') : '') }}">

                            <small class="text-muted">
                                Kosongkan jika artikel ingin dijadikan draft.
                            </small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Isi Artikel</label>
                            <textarea name="content"
                                      class="form-control"
                                      rows="12"
                                      required>{{ old('content', $article->content) }}</textarea>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="{{ url('/admin/articles') }}" class="btn btn-secondary">
                                Cancel
                            </a>

                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i>
                                Update Article
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="stat-icon primary mb-3">
                        <i class="bi bi-file-earmark-text"></i>
                    </div>

                    <h5 class="mb-1">{{ \Illuminate\Support\Str::limit($article->title, 45) }}</h5>

                    <p class="text-muted mb-3">
                        {{ $article->category ?? 'No category' }}
                    </p>

                    <div class="mb-3">
                        @if($article->published_at)
                            <span class="badge bg-success">Published</span>
                        @else
                            <span class="badge bg-secondary">Draft</span>
                        @endif
                    </div>

                    <div class="small text-muted">
                        <div class="mb-2">
                            <strong>Created:</strong><br>
                            {{ $article->created_at ? $article->created_at->format('d M Y H:i') : '-' }}
                        </div>

                        <div class="mb-2">
                            <strong>Last Updated:</strong><br>
                            {{ $article->updated_at ? $article->updated_at->format('d M Y H:i') : '-' }}
                        </div>

                        <div>
                            <strong>Published:</strong><br>
                            @if($article->published_at)
                                {{ \Carbon\Carbon::parse($article->published_at)->format('d M Y H:i') }}
                            @else
                                Not published
                            @endif
                        </div>
                    </div>

                    <div class="alert alert-primary mt-4 mb-0">
                        Artikel ini dapat digunakan untuk menjelaskan fitur sistem kepada user secara informatif.
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection