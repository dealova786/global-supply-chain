@extends('layouts.dashboard')

@section('content')
    <div class="mb-4">
        <h1 class="page-title">Country Risk Comparison</h1>
        <p class="page-subtitle">
            Bandingkan dua negara berdasarkan data cuaca, ekonomi, berita, kurs, dan risk score dari hasil integrasi API yang tersimpan di cache.
        </p>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ url('/compare-countries') }}">
                <div class="row align-items-end">
                    <div class="col-md-5">
                        <label class="form-label">Negara Pertama</label>
                        <select name="country_a" class="form-select" required>
                            <option value="">-- Pilih Negara --</option>

                            @foreach($countries as $country)
                                <option value="{{ $country->id }}"
                                    {{ request('country_a') == $country->id ? 'selected' : '' }}>
                                    {{ $country->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-5">
                        <label class="form-label">Negara Kedua</label>
                        <select name="country_b" class="form-select" required>
                            <option value="">-- Pilih Negara --</option>

                            @foreach($countries as $country)
                                <option value="{{ $country->id }}"
                                    {{ request('country_b') == $country->id ? 'selected' : '' }}>
                                    {{ $country->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            Compare
                        </button>
                    </div>
                </div>
            </form>

            <small class="text-muted d-block mt-3">
                Data source: API cache from Open-Meteo, World Bank, Currency API, GNews, and internal risk scoring.
            </small>
        </div>
    </div>

    @if($countryA && $countryB && $dataA && $dataB)
        <div class="alert alert-primary">
            Membandingkan:
            <strong>{{ $countryA->name }}</strong>
            vs
            <strong>{{ $countryB->name }}</strong>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="stat-label">{{ $countryA->region ?? 'Unknown Region' }}</div>
                            <div class="stat-value">{{ $countryA->name }}</div>
                            <div class="stat-note">
                                Total Risk Score: {{ round($dataA['risk']->total_score) }}
                            </div>
                        </div>

                        @if($dataA['risk']->risk_level === 'High')
                            <span class="badge bg-danger">High</span>
                        @elseif($dataA['risk']->risk_level === 'Medium')
                            <span class="badge bg-warning text-dark">Medium</span>
                        @else
                            <span class="badge bg-success">Low</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="stat-label">{{ $countryB->region ?? 'Unknown Region' }}</div>
                            <div class="stat-value">{{ $countryB->name }}</div>
                            <div class="stat-note">
                                Total Risk Score: {{ round($dataB['risk']->total_score) }}
                            </div>
                        </div>

                        @if($dataB['risk']->risk_level === 'High')
                            <span class="badge bg-danger">High</span>
                        @elseif($dataB['risk']->risk_level === 'Medium')
                            <span class="badge bg-warning text-dark">Medium</span>
                        @else
                            <span class="badge bg-success">Low</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-lg-8">
                <div class="stock-chart-card h-100">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <div class="stock-chart-title">Risk Component Movement</div>
                            <div class="stock-chart-subtitle">
                                Line chart perbandingan komponen risiko antar dua negara.
                            </div>
                        </div>

                        <span class="badge bg-primary">Stock Style</span>
                    </div>

                    <div class="chart-box">
                        <canvas id="comparisonLineChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <h5 class="mb-3">Decision Recommendation</h5>

                        @if($decision)
                            <div class="alert alert-primary mb-0">
                                <strong>{{ $decision['title'] }}</strong>
                                <p class="mb-0 mt-2">
                                    {{ $decision['summary'] }}
                                </p>
                            </div>
                        @else
                            <div class="alert alert-secondary mb-0">
                                Belum ada rekomendasi.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <h5 class="mb-3">Comparison Summary</h5>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Indicator</th>
                                <th>{{ $countryA->name }}</th>
                                <th>{{ $countryB->name }}</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr>
                                <th>GDP</th>
                                <td>$ {{ number_format($dataA['economy']->gdp ?? 0, 0) }}</td>
                                <td>$ {{ number_format($dataB['economy']->gdp ?? 0, 0) }}</td>
                            </tr>

                            <tr>
                                <th>Inflation</th>
                                <td>
                                    @if(!is_null($dataA['economy']->inflation ?? null))
                                        {{ number_format($dataA['economy']->inflation, 2) }}%
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    @if(!is_null($dataB['economy']->inflation ?? null))
                                        {{ number_format($dataB['economy']->inflation, 2) }}%
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                            </tr>

                            <tr>
                                <th>Exchange Rate</th>
                                <td>
                                    {{ $dataA['currency']->base_currency }}
                                    →
                                    {{ $dataA['currency']->target_currency }}
                                    :
                                    {{ number_format($dataA['currency']->exchange_rate, 6) }}
                                </td>
                                <td>
                                    {{ $dataB['currency']->base_currency }}
                                    →
                                    {{ $dataB['currency']->target_currency }}
                                    :
                                    {{ number_format($dataB['currency']->exchange_rate, 6) }}
                                </td>
                            </tr>

                            <tr>
                                <th>Weather Risk</th>
                                <td>{{ round($dataA['risk']->weather_risk) }}</td>
                                <td>{{ round($dataB['risk']->weather_risk) }}</td>
                            </tr>

                            <tr>
                                <th>Inflation Risk</th>
                                <td>{{ round($dataA['risk']->inflation_risk) }}</td>
                                <td>{{ round($dataB['risk']->inflation_risk) }}</td>
                            </tr>

                            <tr>
                                <th>News Risk</th>
                                <td>{{ round($dataA['risk']->news_risk) }}</td>
                                <td>{{ round($dataB['risk']->news_risk) }}</td>
                            </tr>

                            <tr>
                                <th>Currency Risk</th>
                                <td>{{ round($dataA['risk']->currency_risk) }}</td>
                                <td>{{ round($dataB['risk']->currency_risk) }}</td>
                            </tr>

                            <tr>
                                <th>Total Risk Score</th>
                                <td><strong>{{ round($dataA['risk']->total_score) }}</strong></td>
                                <td><strong>{{ round($dataB['risk']->total_score) }}</strong></td>
                            </tr>

                            <tr>
                                <th>Risk Level</th>
                                <td>
                                    @if($dataA['risk']->risk_level === 'High')
                                        <span class="badge bg-danger">High</span>
                                    @elseif($dataA['risk']->risk_level === 'Medium')
                                        <span class="badge bg-warning text-dark">Medium</span>
                                    @else
                                        <span class="badge bg-success">Low</span>
                                    @endif
                                </td>
                                <td>
                                    @if($dataB['risk']->risk_level === 'High')
                                        <span class="badge bg-danger">High</span>
                                    @elseif($dataB['risk']->risk_level === 'Medium')
                                        <span class="badge bg-warning text-dark">Medium</span>
                                    @else
                                        <span class="badge bg-success">Low</span>
                                    @endif
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    @else
        <div class="alert alert-secondary">
            Silakan pilih dua negara terlebih dahulu untuk melihat hasil perbandingan risiko rantai pasok.
        </div>
    @endif
@endsection

@section('scripts')
    @if($countryA && $countryB && $dataA && $dataB)
        <script>
            const componentLabels = @json($componentLabels);
            const componentDataA = @json($componentDataA);
            const componentDataB = @json($componentDataB);

            const ctx = document.getElementById('comparisonLineChart').getContext('2d');

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: componentLabels,
                    datasets: [
                        {
                            label: '{{ $countryA->name }}',
                            data: componentDataA,
                            borderColor: '#2563eb',
                            backgroundColor: 'rgba(37, 99, 235, 0.08)',
                            borderWidth: 3,
                            tension: 0.42,
                            fill: true,
                            pointRadius: 4,
                            pointHoverRadius: 7,
                            pointBackgroundColor: '#ffffff',
                            pointBorderColor: '#2563eb',
                            pointBorderWidth: 2
                        },
                        {
                            label: '{{ $countryB->name }}',
                            data: componentDataB,
                            borderColor: '#16a34a',
                            backgroundColor: 'rgba(22, 163, 74, 0.08)',
                            borderWidth: 3,
                            tension: 0.42,
                            fill: true,
                            pointRadius: 4,
                            pointHoverRadius: 7,
                            pointBackgroundColor: '#ffffff',
                            pointBorderColor: '#16a34a',
                            pointBorderWidth: 2
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    },
                    plugins: {
                        legend: {
                            display: true,
                            labels: {
                                usePointStyle: true,
                                boxWidth: 8
                            }
                        },
                        tooltip: {
                            backgroundColor: '#0f172a',
                            titleColor: '#ffffff',
                            bodyColor: '#e2e8f0',
                            padding: 12,
                            cornerRadius: 12
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                color: '#64748b'
                            },
                            border: {
                                display: false
                            }
                        },
                        y: {
                            beginAtZero: true,
                            max: 100,
                            grid: {
                                color: 'rgba(148, 163, 184, 0.18)'
                            },
                            ticks: {
                                color: '#64748b'
                            },
                            border: {
                                display: false
                            }
                        }
                    }
                }
            });
        </script>
    @endif
@endsection