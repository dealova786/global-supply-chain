@extends('layouts.dashboard')

@section('content')
    <div class="mb-4">
        <h1 class="page-title">Risk Score Analytics</h1>
        <p class="page-subtitle">
            Pantau pergerakan risk score negara berdasarkan data cuaca, ekonomi, berita, dan kurs yang sudah tersimpan.
        </p>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ url('/risk-scores') }}">
                <div class="row align-items-end">
                    <div class="col-md-8">
                        <label class="form-label">Pilih Negara</label>
                        <select name="country_id" class="form-select">
                            <option value="">-- Semua Negara --</option>

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
                        <a href="{{ url('/risk-scores') }}" class="btn btn-secondary w-100">
                            Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if($selectedCountry)
        <div class="alert alert-primary">
            Menampilkan risk score untuk <strong>{{ $selectedCountry->name }}</strong>.
        </div>
    @else
        <div class="alert alert-secondary">
            Menampilkan ringkasan risk score terbaru dari semua negara yang sudah memiliki data.
        </div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon primary">
                    <i class="bi bi-activity"></i>
                </div>
                <div class="stat-label">Average Risk Score</div>
                <div class="stat-value">{{ $averageRiskScore }}</div>
                <div class="stat-note">Rata-rata dari data tampil</div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon success">
                    <i class="bi bi-shield-check"></i>
                </div>
                <div class="stat-label">Low Risk</div>
                <div class="stat-value">{{ $lowRiskCount }}</div>
                <div class="stat-note">Risiko rendah</div>
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
                <div class="stat-note">Perlu perhatian</div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <div class="stock-chart-card h-100">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <div class="stock-chart-title">
                            Risk Score Movement
                        </div>
                    </div>

                    <span class="badge bg-primary">Stock Style</span>
                </div>

                <div class="chart-box">
                    <canvas id="riskScoreChart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="mb-3">Latest Risk Breakdown</h5>

                    @if($latestRisk)
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span>Total Risk Score</span>
                                <strong>{{ round($latestRisk->total_score) }}</strong>
                            </div>
                            <div class="progress" style="height: 9px;">
                                <div class="progress-bar bg-primary"
                                     style="width: {{ min(round($latestRisk->total_score), 100) }}%">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span>Weather Risk</span>
                                <strong>{{ round($latestRisk->weather_risk) }}</strong>
                            </div>
                            <div class="progress" style="height: 9px;">
                                <div class="progress-bar bg-info"
                                     style="width: {{ min(round($latestRisk->weather_risk), 100) }}%">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span>Inflation Risk</span>
                                <strong>{{ round($latestRisk->inflation_risk) }}</strong>
                            </div>
                            <div class="progress" style="height: 9px;">
                                <div class="progress-bar bg-warning"
                                     style="width: {{ min(round($latestRisk->inflation_risk), 100) }}%">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span>News Risk</span>
                                <strong>{{ round($latestRisk->news_risk) }}</strong>
                            </div>
                            <div class="progress" style="height: 9px;">
                                <div class="progress-bar bg-danger"
                                     style="width: {{ min(round($latestRisk->news_risk), 100) }}%">
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="d-flex justify-content-between mb-1">
                                <span>Currency Risk</span>
                                <strong>{{ round($latestRisk->currency_risk) }}</strong>
                            </div>
                            <div class="progress" style="height: 9px;">
                                <div class="progress-bar bg-success"
                                     style="width: {{ min(round($latestRisk->currency_risk), 100) }}%">
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-primary mb-0">
                            <strong>Risk Level:</strong>
                            @if($latestRisk->risk_level === 'High')
                                <span class="badge bg-danger ms-1">High</span>
                            @elseif($latestRisk->risk_level === 'Medium')
                                <span class="badge bg-warning text-dark ms-1">Medium</span>
                            @else
                                <span class="badge bg-success ms-1">Low</span>
                            @endif
                        </div>
                    @else
                        <div class="alert alert-secondary mb-0">
                            Belum ada data risk score yang tersimpan.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <h5 class="mb-3">Latest Risk Score Records</h5>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Country</th>
                            <th>Total Score</th>
                            <th>Risk Level</th>
                            <th>Weather</th>
                            <th>Inflation</th>
                            <th>News</th>
                            <th>Currency</th>
                            <th>Last Updated</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($riskRows as $risk)
                            <tr>
                                <td>{{ $loop->iteration }}</td>

                                <td>
                                    <strong>{{ $risk->country->name ?? '-' }}</strong>
                                    <br>
                                    <small class="text-muted">
                                        {{ $risk->country->region ?? 'Unknown Region' }}
                                    </small>
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

                                <td>{{ round($risk->weather_risk) }}</td>
                                <td>{{ round($risk->inflation_risk) }}</td>
                                <td>{{ round($risk->news_risk) }}</td>
                                <td>{{ round($risk->currency_risk) }}</td>

                                <td>
                                    {{ \Carbon\Carbon::parse($risk->created_at)->diffForHumans() }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted">
                                    Belum ada data risk score.
                                    Buka Country Dashboard untuk menghasilkan risk score dari negara tertentu.
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
        const riskLabels = @json($chartLabels->count() ? $chartLabels : ['No Data']);
        const riskValues = @json($chartValues->count() ? $chartValues : [0]);

        renderStockLineChart(
            'riskScoreChart',
            riskLabels,
            riskValues,
            'Risk Score'
        );
    </script>
@endsection