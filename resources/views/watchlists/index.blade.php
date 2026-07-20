@extends('layouts.dashboard')

@section('content')
    @php
        $watchlists = isset($watchlists)
            ? collect($watchlists)
            : \App\Models\Watchlist::with('country')
                ->where('user_id', auth()->id())
                ->latest()
                ->get();

        $countries = isset($countries)
            ? collect($countries)
            : \App\Models\Country::orderBy('name', 'asc')->get();

        $watchlistCountryIds = $watchlists->pluck('country_id')->filter()->values();

        $availableCountries = $countries->whereNotIn('id', $watchlistCountryIds);

        $latestRiskRows = collect();
        $chartRecords = collect();

        if ($watchlistCountryIds->count() > 0) {
            $latestRiskRows = \App\Models\RiskScore::with('country')
                ->whereIn('country_id', $watchlistCountryIds)
                ->latest('created_at')
                ->get()
                ->unique('country_id')
                ->values();

            $chartRecords = \App\Models\RiskScore::with('country')
                ->whereIn('country_id', $watchlistCountryIds)
                ->latest('created_at')
                ->limit(12)
                ->get()
                ->reverse()
                ->values();
        }

        $lowRiskCount = $latestRiskRows->where('risk_level', 'Low')->count();
        $mediumRiskCount = $latestRiskRows->where('risk_level', 'Medium')->count();
        $highRiskCount = $latestRiskRows->where('risk_level', 'High')->count();

        $averageRisk = $latestRiskRows->count() > 0
            ? round($latestRiskRows->avg('total_score'))
            : 0;

        $chartLabels = $chartRecords->map(function ($item) {
            return \Carbon\Carbon::parse($item->created_at)->format('d M H:i');
        });

        $chartValues = $chartRecords->map(function ($item) {
            return round($item->total_score);
        });
    @endphp

    <div class="mb-4">
        <h1 class="page-title">My Watchlist</h1>
        <p class="page-subtitle">
            Kelola negara yang ingin dipantau dan lihat ringkasan risiko rantai pasok dari negara watchlist.
        </p>
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

    {{-- Add Watchlist --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <h5 class="mb-3">Add Country to Watchlist</h5>

            <form method="POST" action="{{ url('/watchlists') }}">
                @csrf

                <div class="row align-items-end">
                    <div class="col-md-9">
                        <label class="form-label">Pilih Negara</label>
                        <select name="country_id" class="form-select" required>
                            <option value="">-- Pilih Negara --</option>

                            @foreach($availableCountries as $country)
                                <option value="{{ $country->id }}">
                                    {{ $country->name }}
                                    @if($country->region)
                                        - {{ $country->region }}
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-star me-1"></i>
                            Add Watchlist
                        </button>
                    </div>
                </div>
            </form>

            <small class="text-muted d-block mt-3">
                Watchlist digunakan untuk memantau negara tertentu secara lebih cepat pada dashboard user.
            </small>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon primary">
                    <i class="bi bi-star"></i>
                </div>
                <div class="stat-label">Total Watchlist</div>
                <div class="stat-value">{{ $watchlists->count() }}</div>
                <div class="stat-note">Negara yang dipantau</div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon success">
                    <i class="bi bi-shield-check"></i>
                </div>
                <div class="stat-label">Low Risk</div>
                <div class="stat-value">{{ $lowRiskCount }}</div>
                <div class="stat-note">Relatif aman</div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon warning">
                    <i class="bi bi-exclamation-circle"></i>
                </div>
                <div class="stat-label">Medium Risk</div>
                <div class="stat-value">{{ $mediumRiskCount }}</div>
                <div class="stat-note">Perlu dipantau</div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon danger">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>
                <div class="stat-label">High Risk</div>
                <div class="stat-value">{{ $highRiskCount }}</div>
                <div class="stat-note">Butuh perhatian</div>
            </div>
        </div>
    </div>

    {{-- Chart and Summary --}}
    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <div class="stock-chart-card h-100">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <div class="stock-chart-title">Watchlist Risk Movement</div>
                        <div class="stock-chart-subtitle">
                            Grafik pergerakan risk score dari negara yang masuk watchlist.
                        </div>
                    </div>

                    <span class="badge bg-primary">Stock Style</span>
                </div>

                <div class="chart-box">
                    <canvas id="watchlistRiskChart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5 class="mb-3">Watchlist Risk Summary</h5>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Average Risk Score</span>
                            <strong>{{ $averageRisk }}</strong>
                        </div>

                        <div class="progress" style="height: 9px;">
                            <div class="progress-bar
                                @if($averageRisk >= 70)
                                    bg-danger
                                @elseif($averageRisk >= 40)
                                    bg-warning
                                @else
                                    bg-success
                                @endif"
                                style="width: {{ min($averageRisk, 100) }}%">
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-primary mb-3">
                        <strong>Insight:</strong><br>
                        Negara di watchlist akan lebih mudah dipantau melalui dashboard user dan tabel risk update.
                    </div>

                    <div class="small text-muted">
                        <strong>Risk rule:</strong><br>
                        Low: ≤ 30<br>
                        Medium: 31 - 60<br>
                        High: &gt; 60
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Watchlist Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <h5 class="mb-1">Watchlist Dataset</h5>
                    <p class="text-muted mb-0">
                        Daftar negara yang dipantau beserta risk score terakhir.
                    </p>
                </div>

                <span class="badge bg-primary">
                    {{ $watchlists->count() }} Countries
                </span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Country</th>
                            <th>Region</th>
                            <th>Currency</th>
                            <th>Total Risk Score</th>
                            <th>Risk Level</th>
                            <th>Weather</th>
                            <th>Currency Risk</th>
                            <th>News Risk</th>
                            <th>Last Updated</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($watchlists as $watchlist)
                            @php
                                $country = $watchlist->country;

                                $risk = $country
                                    ? \App\Models\RiskScore::where('country_id', $country->id)
                                        ->latest('created_at')
                                        ->first()
                                    : null;
                            @endphp

                            <tr>
                                <td>{{ $loop->iteration }}</td>

                                <td>
                                    <strong>{{ $country->name ?? '-' }}</strong>
                                    <br>
                                    <small class="text-muted">
                                        {{ $country->official_name ?? '-' }}
                                    </small>
                                </td>

                                <td>{{ $country->region ?? '-' }}</td>

                                <td>
                                    {{ $country->currency_code ?? '-' }}
                                </td>

                                <td>
                                    @if($risk)
                                        <strong>{{ round($risk->total_score) }}</strong>
                                    @else
                                        <span class="text-muted">No Data</span>
                                    @endif
                                </td>

                                <td>
                                    @if($risk)
                                        @if($risk->risk_level === 'High')
                                            <span class="badge bg-danger">High</span>
                                        @elseif($risk->risk_level === 'Medium')
                                            <span class="badge bg-warning text-dark">Medium</span>
                                        @else
                                            <span class="badge bg-success">Low</span>
                                        @endif
                                    @else
                                        <span class="badge bg-secondary">Not Tracked</span>
                                    @endif
                                </td>

                                <td>
                                    {{ $risk ? round($risk->weather_risk) : '-' }}
                                </td>

                                <td>
                                    {{ $risk ? round($risk->currency_risk) : '-' }}
                                </td>

                                <td>
                                    {{ $risk ? round($risk->news_risk) : '-' }}
                                </td>

                                <td>
                                    @if($risk)
                                        {{ \Carbon\Carbon::parse($risk->created_at)->diffForHumans() }}
                                    @else
                                        <span class="text-muted">No update</span>
                                    @endif
                                </td>

                                <td>
                                    <div class="d-flex gap-2">
                                        @if($country)
                                            <a href="{{ url('/country-dashboard?country_id=' . $country->id) }}"
                                               class="btn btn-sm btn-outline-primary">
                                                Analyze
                                            </a>
                                        @endif

                                        <form method="POST"
                                              action="{{ url('/watchlists/' . $watchlist->id) }}"
                                              onsubmit="return confirm('Hapus negara ini dari watchlist?')">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                Remove
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center text-muted py-4">
                                    Belum ada negara di watchlist. Tambahkan negara terlebih dahulu.
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
        const watchlistLabels = @json($chartLabels->count() ? $chartLabels : ['No Data']);
        const watchlistValues = @json($chartValues->count() ? $chartValues : [0]);

        renderStockLineChart(
            'watchlistRiskChart',
            watchlistLabels,
            watchlistValues,
            'Watchlist Risk Score'
        );
    </script>
@endsection