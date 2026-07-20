@extends('layouts.dashboard')

@section('content')
    @php
        $totalUsers = \App\Models\User::count();
        $totalCountries = \App\Models\Country::count();
        $totalPorts = \App\Models\Port::count();
        $totalNews = \App\Models\NewsCache::count();
        $totalRiskReports = \App\Models\RiskScore::count();
        $totalWeather = \App\Models\WeatherCache::count();
        $totalCurrency = \App\Models\CurrencyRate::count();

        $latestRiskPerCountry = \App\Models\RiskScore::with('country')
            ->latest('created_at')
            ->limit(250)
            ->get()
            ->unique('country_id')
            ->values();

        $highRiskCount = $latestRiskPerCountry->where('risk_level', 'High')->count();
        $mediumRiskCount = $latestRiskPerCountry->where('risk_level', 'Medium')->count();
        $lowRiskCount = $latestRiskPerCountry->where('risk_level', 'Low')->count();

        $latestCountries = \App\Models\Country::latest('updated_at')
            ->limit(5)
            ->get();

        $latestRiskRows = $latestRiskPerCountry
            ->take(8)
            ->values();

        $chartRows = \App\Models\RiskScore::latest('created_at')
            ->limit(12)
            ->get()
            ->reverse()
            ->values();

        $chartLabels = $chartRows->map(function ($item) {
            return \Carbon\Carbon::parse($item->created_at)->format('d M H:i');
        });

        $chartValues = $chartRows->map(function ($item) {
            return round($item->total_score);
        });

        $lastCountrySync = \App\Models\Country::latest('updated_at')->first();
    @endphp

    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h1 class="page-title">Admin Control Dashboard</h1>
            <p class="page-subtitle">
                Kelola ringkasan sistem, monitoring API cache, dan kontrol sinkronisasi data global.
            </p>
        </div>

        <form method="POST" action="{{ route('admin.sync.countries') }}">
            @csrf
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-arrow-repeat me-1"></i>
                Sync Countries API
            </button>
        </form>
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

    <div class="alert alert-primary">
        Selamat datang, <strong>{{ auth()->user()->name }}</strong>. Anda login sebagai
        <strong>{{ ucfirst(auth()->user()->role) }}</strong>.
    </div>

    {{-- Main Summary --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon primary">
                    <i class="bi bi-people"></i>
                </div>
                <div class="stat-label">Total Users</div>
                <div class="stat-value">{{ $totalUsers }}</div>
                <div class="stat-note">Akun dalam sistem</div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon success">
                    <i class="bi bi-globe2"></i>
                </div>
                <div class="stat-label">Countries</div>
                <div class="stat-value">{{ $totalCountries }}</div>
                <div class="stat-note">REST Countries API data</div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon warning">
                    <i class="bi bi-geo-alt"></i>
                </div>
                <div class="stat-label">Ports</div>
                <div class="stat-value">{{ $totalPorts }}</div>
                <div class="stat-note">GeoNames API cache</div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon danger">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>
                <div class="stat-label">High Risk Countries</div>
                <div class="stat-value">{{ $highRiskCount }}</div>
                <div class="stat-note">Butuh perhatian</div>
            </div>
        </div>
    </div>

    {{-- API Cache Summary --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon primary">
                    <i class="bi bi-cloud-sun"></i>
                </div>
                <div class="stat-label">Weather Cache</div>
                <div class="stat-value">{{ $totalWeather }}</div>
                <div class="stat-note">Open-Meteo records</div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon success">
                    <i class="bi bi-currency-exchange"></i>
                </div>
                <div class="stat-label">Currency Cache</div>
                <div class="stat-value">{{ $totalCurrency }}</div>
                <div class="stat-note">Currency API records</div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon warning">
                    <i class="bi bi-newspaper"></i>
                </div>
                <div class="stat-label">News Cache</div>
                <div class="stat-value">{{ $totalNews }}</div>
                <div class="stat-note">GNews API records</div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon danger">
                    <i class="bi bi-activity"></i>
                </div>
                <div class="stat-label">Risk Reports</div>
                <div class="stat-value">{{ $totalRiskReports }}</div>
                <div class="stat-note">Risk scoring results</div>
            </div>
        </div>
    </div>

    {{-- Chart and System Status --}}
    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <div class="stock-chart-card h-100">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <div class="stock-chart-title">System Risk Movement</div>
                        <div class="stock-chart-subtitle">
                            Pergerakan risk score terbaru dari data yang tersimpan dalam sistem.
                        </div>
                    </div>

                    <span class="badge bg-primary">Stock Style</span>
                </div>

                <div class="chart-box">
                    <canvas id="adminRiskChart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5 class="mb-3">System Status</h5>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Low Risk Countries</span>
                            <strong>{{ $lowRiskCount }}</strong>
                        </div>
                        <div class="progress" style="height: 9px;">
                            <div class="progress-bar bg-success"
                                 style="width: {{ min($lowRiskCount * 8, 100) }}%">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Medium Risk Countries</span>
                            <strong>{{ $mediumRiskCount }}</strong>
                        </div>
                        <div class="progress" style="height: 9px;">
                            <div class="progress-bar bg-warning"
                                 style="width: {{ min($mediumRiskCount * 8, 100) }}%">
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="d-flex justify-content-between mb-1">
                            <span>High Risk Countries</span>
                            <strong>{{ $highRiskCount }}</strong>
                        </div>
                        <div class="progress" style="height: 9px;">
                            <div class="progress-bar bg-danger"
                                 style="width: {{ min($highRiskCount * 8, 100) }}%">
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-primary mb-0">
                        <strong>Last Country Sync:</strong><br>
                        @if($lastCountrySync)
                            {{ \Carbon\Carbon::parse($lastCountrySync->updated_at)->diffForHumans() }}
                        @else
                            Belum ada data negara.
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Latest Data Tables --}}
    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5 class="mb-3">Latest Synced Countries</h5>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Country</th>
                                    <th>Code</th>
                                    <th>Region</th>
                                    <th>Updated</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($latestCountries as $country)
                                    <tr>
                                        <td>
                                            <strong>{{ $country->name }}</strong>
                                            <br>
                                            <small class="text-muted">
                                                {{ $country->official_name ?? '-' }}
                                            </small>
                                        </td>

                                        <td>{{ $country->cca2 ?? '-' }}</td>

                                        <td>{{ $country->region ?? '-' }}</td>

                                        <td>
                                            {{ \Carbon\Carbon::parse($country->updated_at)->diffForHumans() }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">
                                            Belum ada data negara.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5 class="mb-3">Latest Risk Reports</h5>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Country</th>
                                    <th>Score</th>
                                    <th>Level</th>
                                    <th>Updated</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($latestRiskRows as $risk)
                                    <tr>
                                        <td>
                                            <strong>{{ $risk->country->name ?? '-' }}</strong>
                                        </td>

                                        <td>
                                            <strong>{{ round($risk->total_score) }}</strong>
                                        </td>

                                        <td>
                                            @if($risk->risk_level === 'High')
                                                <span class="badge bg-danger">High</span>
                                            @elseif($risk->risk_level === 'Medium')
                                                <span class="badge bg-warning text-dark">Medium</span>
                                            @else
                                                <span class="badge bg-success">Low</span>
                                            @endif
                                        </td>

                                        <td>
                                            {{ \Carbon\Carbon::parse($risk->created_at)->diffForHumans() }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">
                                            Belum ada data risk score.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        const adminRiskLabels = @json($chartLabels->count() ? $chartLabels : ['No Data']);
        const adminRiskValues = @json($chartValues->count() ? $chartValues : [0]);

        renderStockLineChart(
            'adminRiskChart',
            adminRiskLabels,
            adminRiskValues,
            'System Risk Score'
        );
    </script>
@endsection