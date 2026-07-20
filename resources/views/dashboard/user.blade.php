@extends('layouts.dashboard')

@section('content')
    @php
        $userId = auth()->id();

        $watchlists = \App\Models\Watchlist::with('country')
            ->where('user_id', $userId)
            ->latest()
            ->get();

        $watchlistCountryIds = $watchlists->pluck('country_id')->filter()->values();

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
                ->limit(10)
                ->get()
                ->reverse()
                ->values();
        }

        $highRiskCount = $latestRiskRows->where('risk_level', 'High')->count();
        $mediumRiskCount = $latestRiskRows->where('risk_level', 'Medium')->count();
        $lowRiskCount = $latestRiskRows->where('risk_level', 'Low')->count();

        $chartLabels = $chartRecords->map(function ($item) {
            return \Carbon\Carbon::parse($item->created_at)->format('d M');
        });

        $chartValues = $chartRecords->map(function ($item) {
            return round($item->total_score);
        });
    @endphp

    <div class="mb-4">
        <h1 class="page-title">User Monitoring Dashboard</h1>
        <p class="page-subtitle">
            Pantau negara yang masuk watchlist dan lihat perkembangan risiko rantai pasok secara ringkas.
        </p>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon primary">
                    <i class="bi bi-star"></i>
                </div>
                <div class="stat-label">My Watchlist</div>
                <div class="stat-value">{{ $watchlists->count() }}</div>
                <div class="stat-note">Negara yang kamu pantau</div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon success">
                    <i class="bi bi-shield-check"></i>
                </div>
                <div class="stat-label">Low Risk</div>
                <div class="stat-value">{{ $lowRiskCount }}</div>
                <div class="stat-note">Negara relatif aman</div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon warning">
                    <i class="bi bi-exclamation-circle"></i>
                </div>
                <div class="stat-label">Medium Risk</div>
                <div class="stat-value">{{ $mediumRiskCount }}</div>
                <div class="stat-note">Perlu dipantau berkala</div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon danger">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>
                <div class="stat-label">High Risk</div>
                <div class="stat-value">{{ $highRiskCount }}</div>
                <div class="stat-note">Membutuhkan perhatian</div>
            </div>
        </div>
    </div>

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
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">My Watchlist</h5>
                        <a href="{{ url('/watchlists') }}" class="btn btn-sm btn-primary">
                            Manage
                        </a>
                    </div>

                    @forelse($watchlists->take(6) as $watchlist)
                        @php
                            $risk = \App\Models\RiskScore::where('country_id', $watchlist->country_id)
                                ->latest('created_at')
                                ->first();
                        @endphp

                        <div class="d-flex justify-content-between align-items-center py-3 border-bottom">
                            <div>
                                <div class="fw-bold">
                                    {{ $watchlist->country->name ?? '-' }}
                                </div>
                                <small class="text-muted">
                                    {{ $watchlist->country->region ?? 'Unknown Region' }}
                                </small>
                            </div>

                            <div class="text-end">
                                @if($risk)
                                    @if($risk->risk_level === 'High')
                                        <span class="badge bg-danger">High</span>
                                    @elseif($risk->risk_level === 'Medium')
                                        <span class="badge bg-warning text-dark">Medium</span>
                                    @else
                                        <span class="badge bg-success">Low</span>
                                    @endif

                                    <div class="small text-muted mt-1">
                                        Score: {{ round($risk->total_score) }}
                                    </div>
                                @else
                                    <span class="badge bg-secondary">No Data</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="alert alert-secondary mb-0">
                            Belum ada negara di watchlist. Tambahkan negara dari halaman Watchlist.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <h5 class="mb-3">Latest Watchlist Risk Update</h5>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Country</th>
                            <th>Region</th>
                            <th>Total Risk Score</th>
                            <th>Risk Level</th>
                            <th>Weather Risk</th>
                            <th>Currency Risk</th>
                            <th>News Risk</th>
                            <th>Last Updated</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($latestRiskRows as $risk)
                            <tr>
                                <td>
                                    <strong>{{ $risk->country->name ?? '-' }}</strong>
                                </td>

                                <td>{{ $risk->country->region ?? '-' }}</td>

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

                                <td>{{ $risk->weather_risk }}</td>
                                <td>{{ $risk->currency_risk }}</td>
                                <td>{{ $risk->news_risk }}</td>

                                <td>
                                    {{ \Carbon\Carbon::parse($risk->created_at)->diffForHumans() }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted">
                                    Belum ada risk score untuk negara di watchlist.
                                    Buka Country Dashboard atau Risk Score untuk mengisi data terlebih dahulu.
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