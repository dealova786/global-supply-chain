@extends('layouts.dashboard')

@section('content')
    @php
        $newsData = collect($newsData ?? []);

        $totalNews = $totalNews ?? $newsData->count();

        $positiveNews = $positiveNews ?? $newsData->filter(function ($news) {
            return data_get($news, 'sentiment') === 'Positive';
        })->count();

        $neutralNews = $neutralNews ?? $newsData->filter(function ($news) {
            return data_get($news, 'sentiment') === 'Neutral';
        })->count();

        $negativeNews = $negativeNews ?? $newsData->filter(function ($news) {
            return data_get($news, 'sentiment') === 'Negative';
        })->count();

        $averageNewsRisk = $newsData->count() > 0
            ? round($newsData->avg('news_risk'))
            : 0;

        $chartLabels = $newsData->sortBy('published_at')->values()->map(function ($news) {
            $publishedAt = data_get($news, 'published_at');

            if ($publishedAt) {
                return \Carbon\Carbon::parse($publishedAt)->format('d M');
            }

            return 'No Date';
        });

        $chartValues = $newsData->sortBy('published_at')->values()->map(function ($news) {
            return round(data_get($news, 'news_risk') ?? 0);
        });
    @endphp

    <div class="mb-4">
        <h1 class="page-title">News Intelligence Dashboard</h1>
        <p class="page-subtitle">
            Analisis berita global berdasarkan negara, sentimen, dan news risk untuk mendukung monitoring rantai pasok.
        </p>
    </div>

    {{-- Filter --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ url('/news-intelligence') }}">
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

                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            Analyze
                        </button>
                    </div>

                    <div class="col-md-2">
                        <a href="{{ url('/news-intelligence') }}" class="btn btn-secondary w-100">
                            Reset
                        </a>
                    </div>
                </div>
            </form>

            <small class="text-muted d-block mt-3">
                Data source: GNews API cache dan sentiment words dari database. Jika quota API habis, sistem akan menampilkan cache yang tersedia.
            </small>
        </div>
    </div>

    @if(isset($message) && $message)
        <div class="alert alert-info">
            {{ $message }}
        </div>
    @endif

    @if(isset($selectedCountry) && $selectedCountry)
        <div class="alert alert-primary">
            Menampilkan berita untuk <strong>{{ $selectedCountry->name }}</strong>.
        </div>
    @else
        <div class="alert alert-secondary">
            Silakan pilih negara terlebih dahulu untuk melihat analisis berita dan sentimen.
        </div>
    @endif

    {{-- Summary Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon primary">
                    <i class="bi bi-newspaper"></i>
                </div>
                <div class="stat-label">Total News</div>
                <div class="stat-value">{{ $totalNews }}</div>
                <div class="stat-note">Berita tersedia</div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon success">
                    <i class="bi bi-emoji-smile"></i>
                </div>
                <div class="stat-label">Positive</div>
                <div class="stat-value">{{ $positiveNews }}</div>
                <div class="stat-note">Sentimen positif</div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon warning">
                    <i class="bi bi-emoji-neutral"></i>
                </div>
                <div class="stat-label">Neutral</div>
                <div class="stat-value">{{ $neutralNews }}</div>
                <div class="stat-note">Sentimen netral</div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon danger">
                    <i class="bi bi-emoji-frown"></i>
                </div>
                <div class="stat-label">Negative</div>
                <div class="stat-value">{{ $negativeNews }}</div>
                <div class="stat-note">Sentimen negatif</div>
            </div>
        </div>
    </div>

    {{-- Chart and Summary --}}
    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <div class="stock-chart-card h-100">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <div class="stock-chart-title">News Risk Movement</div>
                        <div class="stock-chart-subtitle">
                            Grafik pergerakan news risk dari berita yang ditemukan untuk negara terpilih.
                        </div>
                    </div>

                    <span class="badge bg-primary">Stock Style</span>
                </div>

                <div class="chart-box">
                    <canvas id="newsRiskChart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5 class="mb-3">News Risk Summary</h5>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Average News Risk</span>
                            <strong>{{ $averageNewsRisk }}</strong>
                        </div>

                        <div class="progress" style="height: 9px;">
                            <div class="progress-bar
                                @if($averageNewsRisk >= 70)
                                    bg-danger
                                @elseif($averageNewsRisk >= 40)
                                    bg-warning
                                @else
                                    bg-success
                                @endif"
                                style="width: {{ min($averageNewsRisk, 100) }}%">
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-primary mb-3">
                        <strong>Interpretasi:</strong><br>
                        News risk dihitung dari hasil sentimen berita. Berita negatif akan menaikkan risiko rantai pasok.
                    </div>

                    <div class="small text-muted">
                        <strong>Risk rule:</strong><br>
                        Positive news: risk rendah<br>
                        Neutral news: risk sedang<br>
                        Negative news: risk lebih tinggi
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- News Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <h5 class="mb-1">News Intelligence Dataset</h5>
                    <p class="text-muted mb-0">
                        Daftar berita, sumber, sentimen, dan news risk yang digunakan dalam analisis.
                    </p>
                </div>

                @if(isset($selectedCountry) && $selectedCountry)
                    <span class="badge bg-primary">{{ $selectedCountry->name }}</span>
                @endif
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>News Title</th>
                            <th>Source</th>
                            <th>Sentiment</th>
                            <th>Positive</th>
                            <th>Negative</th>
                            <th>News Risk</th>
                            <th>Published</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($newsData as $news)
                            <tr>
                                <td>{{ $loop->iteration }}</td>

                                <td style="min-width: 320px;">
                                    <strong>
                                        {{ \Illuminate\Support\Str::limit(data_get($news, 'title'), 90) }}
                                    </strong>
                                    <br>
                                    <small class="text-muted">
                                        {{ \Illuminate\Support\Str::limit(data_get($news, 'description'), 130) }}
                                    </small>
                                </td>

                                <td>{{ data_get($news, 'source') ?? '-' }}</td>

                                <td>
                                    @if(data_get($news, 'sentiment') === 'Positive')
                                        <span class="badge bg-success">Positive</span>
                                    @elseif(data_get($news, 'sentiment') === 'Negative')
                                        <span class="badge bg-danger">Negative</span>
                                    @else
                                        <span class="badge bg-secondary">Neutral</span>
                                    @endif
                                </td>

                                <td>
                                    <strong>{{ data_get($news, 'positive_score') ?? 0 }}</strong>
                                </td>

                                <td>
                                    <strong>{{ data_get($news, 'negative_score') ?? 0 }}</strong>
                                </td>

                                <td>
                                    @php
                                        $risk = data_get($news, 'news_risk') ?? 0;
                                    @endphp

                                    @if($risk >= 70)
                                        <span class="badge bg-danger">{{ $risk }}</span>
                                    @elseif($risk >= 40)
                                        <span class="badge bg-warning text-dark">{{ $risk }}</span>
                                    @else
                                        <span class="badge bg-success">{{ $risk }}</span>
                                    @endif
                                </td>

                                <td>
                                    @if(data_get($news, 'published_at'))
                                        {{ \Carbon\Carbon::parse(data_get($news, 'published_at'))->diffForHumans() }}
                                    @else
                                        <span class="text-muted">No date</span>
                                    @endif
                                </td>

                                <td>
                                    @if(data_get($news, 'url'))
                                        <a href="{{ data_get($news, 'url') }}"
                                           target="_blank"
                                           class="btn btn-sm btn-outline-primary">
                                            Open
                                        </a>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">
                                    Belum ada berita untuk negara ini.
                                    Jika GNews quota sedang habis, coba lagi setelah quota reset atau gunakan cache yang sudah tersimpan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
@endsection

@section('scripts')
    <script>
        const newsLabels = @json($chartLabels->count() ? $chartLabels : ['No Data']);
        const newsValues = @json($chartValues->count() ? $chartValues : [0]);

        renderStockLineChart(
            'newsRiskChart',
            newsLabels,
            newsValues,
            'News Risk'
        );
    </script>
@endsection