@php
    use Illuminate\Support\Str;
@endphp

@extends('layouts.dashboard')

@section('content')
    <div class="mb-4">
        <h3>News Intelligence Dashboard</h3>
        <p class="text-muted">
            Halaman ini menampilkan berita ekonomi, logistik, perdagangan, shipping, geopolitik, dan hasil analisis sentimen berita.
        </p>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('news.index') }}">
                <div class="row align-items-end">
                    <div class="col-md-8">
                        <label class="form-label">Pilih Negara</label>
                        <select name="country_id" class="form-select" required>
                            <option value="">-- Pilih Negara --</option>

                            @foreach($countries as $country)
                                <option value="{{ $country->id }}"
                                    {{ request('country_id') == $country->id ? 'selected' : '' }}>
                                    {{ $country->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary w-100">
                            Analyze News
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if($selectedCountry)
        <div class="alert alert-info">
            Menampilkan berita untuk negara:
            <strong>{{ $selectedCountry->name }}</strong>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h6>Total News</h6>
                        <h3>{{ $totalNews }}</h3>
                        <small class="text-muted">Berita Tersedia</small>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h6>Positive</h6>
                        <h3>{{ $positiveNews }}</h3>
                        <span class="badge bg-success">Positive News</span>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h6>Neutral</h6>
                        <h3>{{ $neutralNews }}</h3>
                        <span class="badge bg-secondary">Neutral News</span>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h6>Negative</h6>
                        <h3>{{ $negativeNews }}</h3>
                        <span class="badge bg-danger">Negative News</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h5 class="mb-3">News List</h5>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>No</th>
                                <th>Title</th>
                                <th>Source</th>
                                <th>Published At</th>
                                <th>Sentiment</th>
                                <th>Scores</th>
                                <th>News Risk</th>
                                <th>Link</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($newsData as $news)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>

                                    <td>
                                        <strong>{{ $news['title'] ?? $news->title ?? '-' }}</strong>
                                        <br>
                                        <small class="text-muted">
                                            {{ Str::limit($news['description'] ?? $news->description ?? '-', 120) }}
                                        </small>
                                    </td>

                                    <td>
                                        {{ $news['source'] ?? $news->source ?? '-' }}
                                    </td>

                                    <td>
                                        {{ $news['published_at'] ?? $news->published_at ?? '-' }}
                                    </td>

                                    <td>
                                        @php
                                            $sentiment = $news['sentiment'] ?? $news->sentiment ?? 'Neutral';
                                        @endphp

                                        @if($sentiment === 'Positive')
                                            <span class="badge bg-success">Positive</span>
                                        @elseif($sentiment === 'Negative')
                                            <span class="badge bg-danger">Negative</span>
                                        @else
                                            <span class="badge bg-secondary">Neutral</span>
                                        @endif
                                    </td>

                                    <td>
                                        <small>
                                            + {{ $news['positive_score'] ?? $news->positive_score ?? 0 }}
                                            |
                                            - {{ $news['negative_score'] ?? $news->negative_score ?? 0 }}
                                        </small>
                                    </td>

                                    <td>
                                        @php
                                            $newsRisk = $news['news_risk'] ?? $news->news_risk ?? 0;
                                        @endphp

                                        @if($newsRisk <= 30)
                                            <span class="badge bg-success">{{ $newsRisk }} Low</span>
                                        @elseif($newsRisk <= 60)
                                            <span class="badge bg-warning text-dark">{{ $newsRisk }} Medium</span>
                                        @else
                                            <span class="badge bg-danger">{{ $newsRisk }} High</span>
                                        @endif
                                    </td>

                                    <td>
                                        @php
                                            $url = $news['url'] ?? $news->url ?? null;
                                        @endphp

                                        @if($url)
                                            <a href="{{ $url }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                Open
                                            </a>
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted">
                                        Belum ada berita spesifik yang ditemukan dari GNews API Suntuk negara ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @else
        <div class="alert alert-secondary">
            Silakan pilih negara untuk melihat hasil News Intelligence.
        </div>
    @endif
@endsection