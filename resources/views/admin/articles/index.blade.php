@extends('layouts.dashboard')

@section('content')
    @php
        $articles = isset($articles)
            ? $articles
            : \App\Models\Article::latest()->get();

        $articleCollection = collect($articles instanceof \Illuminate\Pagination\AbstractPaginator ? $articles->items() : $articles);

        $totalArticles = \App\Models\Article::count();
        $publishedArticles = \App\Models\Article::whereNotNull('published_at')->count();
        $draftArticles = \App\Models\Article::whereNull('published_at')->count();
        $latestArticle = \App\Models\Article::latest()->first();
    @endphp

    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h1 class="page-title">Admin Articles</h1>
            <p class="page-subtitle">
                Kelola artikel edukasi yang ditampilkan kepada user pada sistem Supply Chain Risk.
            </p>
        </div>

        <a href="{{ url('/admin/articles/create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i>
            Add Article
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

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

    {{-- Summary Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon primary">
                    <i class="bi bi-newspaper"></i>
                </div>
                <div class="stat-label">Total Articles</div>
                <div class="stat-value">{{ $totalArticles }}</div>
                <div class="stat-note">Semua artikel</div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon success">
                    <i class="bi bi-check-circle"></i>
                </div>
                <div class="stat-label">Published</div>
                <div class="stat-value">{{ $publishedArticles }}</div>
                <div class="stat-note">Artikel dipublikasikan</div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon warning">
                    <i class="bi bi-file-earmark-text"></i>
                </div>
                <div class="stat-label">Draft</div>
                <div class="stat-value">{{ $draftArticles }}</div>
                <div class="stat-note">Belum dipublikasikan</div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon danger">
                    <i class="bi bi-clock-history"></i>
                </div>
                <div class="stat-label">Latest Article</div>
                <div class="stat-value" style="font-size: 20px;">
                    {{ $latestArticle ? \Illuminate\Support\Str::limit($latestArticle->title, 18) : '-' }}
                </div>
                <div class="stat-note">
                    {{ $latestArticle ? $latestArticle->created_at->diffForHumans() : 'No data' }}
                </div>
            </div>
        </div>
    </div>

    {{-- Articles Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <h5 class="mb-1">Articles Dataset</h5>
                    <p class="text-muted mb-0">
                        Daftar artikel edukasi yang dikelola oleh admin.
                    </p>
                </div>

                <span class="badge bg-primary">
                    {{ $totalArticles }} Articles
                </span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Article</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Published At</th>
                            <th>Created</th>
                            <th>Last Updated</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($articles as $article)
                            <tr>
                                <td>{{ $loop->iteration }}</td>

                                <td style="min-width: 320px;">
                                    <strong>{{ $article->title }}</strong>
                                    <br>
                                    <small class="text-muted">
                                        {{ \Illuminate\Support\Str::limit(strip_tags($article->content ?? ''), 120) }}
                                    </small>
                                </td>

                                <td>
                                    @if($article->category)
                                        <span class="badge bg-primary">
                                            {{ $article->category }}
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>

                                <td>
                                    @if($article->published_at)
                                        <span class="badge bg-success">Published</span>
                                    @else
                                        <span class="badge bg-secondary">Draft</span>
                                    @endif
                                </td>

                                <td>
                                    @if($article->published_at)
                                        {{ \Carbon\Carbon::parse($article->published_at)->format('d M Y H:i') }}
                                    @else
                                        <span class="text-muted">Not published</span>
                                    @endif
                                </td>

                                <td>
                                    {{ $article->created_at ? $article->created_at->diffForHumans() : '-' }}
                                </td>

                                <td>
                                    {{ $article->updated_at ? $article->updated_at->diffForHumans() : '-' }}
                                </td>

                                <td>
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="{{ url('/admin/articles/' . $article->id . '/edit') }}"
                                           class="btn btn-sm btn-outline-primary">
                                            Edit
                                        </a>

                                        <form method="POST"
                                              action="{{ url('/admin/articles/' . $article->id) }}"
                                              onsubmit="return confirm('Hapus artikel ini?')">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    Belum ada artikel.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(method_exists($articles, 'links'))
                <div class="mt-3">
                    {{ $articles->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection