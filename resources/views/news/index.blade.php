@extends('layouts.dashboard')

@section('content')
    <div class="mb-4">
        <h3>News Intelligence Dashboard</h3>
        <p class="text-muted">
            Halaman ini menampilkan berita ekonomi, logistik, perdagangan, shipping, geopolitik,
            dan hasil analisis sentimen berita.
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
                        <h3>{{ $newsData->count() }}</h3>
                        <small>Berita tersimpan</small>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h6>Positive</h6>
                        <h3>{{ $positiveCount }}</h3>
                        <span class="badge bg-success">Positive News</span>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h6>Neutral</h6>
                        <h3>{{ $neutralCount }}</h3>
                        <span class="badge bg-secondary">Neutral News</span>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h6>Negative</h6>
                        <h3>{{ $negativeCount }}</h3>
                        <span class="badge bg-danger">Negative News</span>
                    </div>
                </div>
            </div>
        </div>

        @if($newsData->count() > 0)
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h5 class="mb-3">News Intelligence Results</h5>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th>No</th>
                                    <th>News</th>
                                    <th>Source</th>
                                    <th>Sentiment</th>
                                    <th>Positive</th>
                                    <th>Negative</th>
                                    <th>Neutral</th>
                                    <th>News Risk</th>
                                    <th>Link</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($newsData as $news)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td style="min-width: 300px;">
                                            <strong>{{ $news->title }}</strong><br>
                                            <small class="text-muted">
                                                {{ \Illuminate\Support\Str::limit($news->description ?? '-', 120) }}
                                            </small>
                                        </td>
                                        <td>{{ $news->source ?? '-' }}</td>
                                        <td>
                                            @if($news->sentiment === 'Positive')
                                                <span class="badge bg-success">Positive</span>
                                            @elseif($news->sentiment === 'Negative')
                                                <span class="badge bg-danger">Negative</span>
                                            @else
                                                <span class="badge bg-secondary">Neutral</span>
                                            @endif
                                        </td>
                                        <td>{{ $news->positive_score }}</td>
                                        <td>{{ $news->negative_score }}</td>
                                        <td>{{ $news->neutral_score }}</td>
                                        <td>
                                            @php
                                                $newsRisk = 50;

                                                if ($news->sentiment === 'Negative') {
                                                    $newsRisk = $news->negative_score >= 3 ? 80 : 60;
                                                } elseif ($news->sentiment === 'Positive') {
                                                    $newsRisk = 20;
                                                }
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
                                            @if($news->url)
                                                <a href="{{ $news->url }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                    Open
                                                </a>
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        @else
            <div class="alert alert-warning">
                Belum ada berita untuk negara ini. Coba pilih negara lain seperti China, Germany, atau Australia.
            </div>
        @endif
    @else
        <div class="alert alert-secondary">
            Silakan pilih negara untuk melihat hasil News Intelligence.
        </div>
    @endif
@endsection