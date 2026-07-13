@extends('layouts.dashboard')

@section('content')
    <div class="mb-4">
        <h3>Risk Score Dashboard</h3>
        <p class="text-muted">
            Halaman ini menampilkan riwayat skor risiko rantai pasok berdasarkan negara yang dipilih.
        </p>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('risk.index') }}">
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
                            Show Risk Trend
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if($selectedCountry)
        <div class="alert alert-info">
            Menampilkan data risk score untuk negara:
            <strong>{{ $selectedCountry->name }}</strong>
        </div>

        @if($riskScores->count() > 0)
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <h6>Latest Risk Score</h6>
                            <h3>{{ $riskScores->first()->total_score }}</h3>
                            <small>{{ $riskScores->first()->risk_level }} Risk</small>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <h6>Weather Risk</h6>
                            <h3>{{ $riskScores->first()->weather_risk }}</h3>
                            <small>Weight 30%</small>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <h6>Inflation Risk</h6>
                            <h3>{{ $riskScores->first()->inflation_risk }}</h3>
                            <small>Weight 20%</small>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <h6>News Risk</h6>
                            <h3>{{ $riskScores->first()->news_risk }}</h3>
                            <small>Weight 40%</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <h5 class="mb-3">Risk Trend Chart</h5>
                    <canvas id="riskTrendChart" height="100"></canvas>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h5 class="mb-3">Risk Score History</h5>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th>No</th>
                                    <th>Total Score</th>
                                    <th>Risk Level</th>
                                    <th>Weather</th>
                                    <th>Inflation</th>
                                    <th>Currency</th>
                                    <th>News</th>
                                    <th>Calculated At</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($riskScores as $risk)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            <strong>{{ $risk->total_score }}</strong>
                                        </td>
                                        <td>
                                            @if($risk->risk_level === 'Low')
                                                <span class="badge bg-success">Low</span>
                                            @elseif($risk->risk_level === 'Medium')
                                                <span class="badge bg-warning text-dark">Medium</span>
                                            @else
                                                <span class="badge bg-danger">High</span>
                                            @endif
                                        </td>
                                        <td>{{ $risk->weather_risk }}</td>
                                        <td>{{ $risk->inflation_risk }}</td>
                                        <td>{{ $risk->currency_risk }}</td>
                                        <td>{{ $risk->news_risk }}</td>
                                        <td>
                                            {{ $risk->calculated_at ? date('d M Y H:i', strtotime($risk->calculated_at)) : '-' }}
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
                Belum ada data risk score untuk negara ini.
                Silakan buka Country Dashboard terlebih dahulu lalu klik Analyze Country agar sistem menghitung risk score.
            </div>
        @endif
    @else
        <div class="alert alert-secondary">
            Silakan pilih negara untuk melihat grafik dan riwayat risk score.
        </div>
    @endif
@endsection

@section('scripts')
@if($selectedCountry && $riskScores->count() > 0)
<script>
    const riskLabels = @json($chartLabels);
    const riskData = @json($chartData);

    const ctx = document.getElementById('riskTrendChart');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: riskLabels,
            datasets: [{
                label: 'Total Risk Score',
                data: riskData,
                tension: 0.3,
                fill: false,
                borderWidth: 3,
                pointRadius: 6,
                pointHoverRadius: 8
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: true
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100
                }
            }
        }
    });
</script>
@endif
@endsection