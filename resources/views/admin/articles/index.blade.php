@extends('layouts.dashboard')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3>Manage Articles</h3>
            <p class="text-muted mb-0">
                Halaman ini digunakan admin untuk mengelola artikel analisis supply chain.
            </p>
        </div>

        <a href="{{ route('admin.articles.create') }}" class="btn btn-primary">
            Add Article
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <h5 class="mb-3">Daftar Artikel</h5>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>No</th>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Author</th>
                            <th>Published At</th>
                            <th>Created At</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($articles as $article)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <strong>{{ $article->title }}</strong><br>
                                    <small class="text-muted">
                                        {{ \Illuminate\Support\Str::limit($article->content, 80) }}
                                    </small>
                                </td>
                                <td>{{ $article->category ?? '-' }}</td>
                                <td>{{ $article->user?->name ?? '-' }}</td>
                                <td>
                                    {{ $article->published_at ? date('d M Y H:i', strtotime($article->published_at)) : '-' }}
                                </td>
                                <td>{{ $article->created_at->format('d M Y H:i') }}</td>
                                <td>
                                    <a href="{{ route('admin.articles.edit', $article->id) }}"
                                       class="btn btn-sm btn-outline-primary">
                                        Edit
                                    </a>

                                    <form method="POST"
                                          action="{{ route('admin.articles.destroy', $article->id) }}"
                                          class="d-inline"
                                          onsubmit="return confirm('Yakin ingin menghapus artikel ini?')">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">
                                    Belum ada artikel.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
@endsection