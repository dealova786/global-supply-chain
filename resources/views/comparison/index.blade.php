@extends('layouts.dashboard')

@section('content')
    <div class="mb-4">
        <h3>Country Comparison Engine</h3>
        <p class="text-muted">
            Halaman ini digunakan untuk membandingkan risiko rantai pasok antara dua negara.
        </p>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('comparison.index') }}">
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
        </div>
    </div>

    @if($countryA && $countryB)
        <div class="alert alert-info">
            Membandingkan:
            <strong>{{ $countryA->name }}</strong>
            vs
            <strong>{{ $countryB->name }}</strong>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h5>{{ $countryA->name }}</h5>
                        <p class="text-muted mb-2">{{ $countryA->official_name }}</p>

                        @if($countryA->flag_url)
                            <img src="{{ $countryA->flag_url }}" width="90" class="mb-3">
                        @endif

                        <table class="table table-bordered">
                            <tr>
                                <th>Capital</th>
                                <td>{{ $countryA->capital }}</td>
                            </tr>
                            <tr>
                                <th>Currency</th>
                                <td>{{ $countryA->currency_code }} - {{ $countryA->currency_name }}</td>
                            </tr>
                            <tr>
                                <th>Population</th>
                                <td>{{ number_format($countryA->population) }}</td>
                            </tr>
                            <tr>
                                <th>Risk Level</th>
                                <td>
                                    @if($dataA['risk'])
                                        @if($dataA['risk']->risk_level === 'Low')
                                            <span class="badge bg-success">Low</span>
                                        @elseif($dataA['risk']->risk_level === 'Medium')
                                            <span class="badge bg-warning text-dark">Medium</span>
                                        @else
                                            <span class="badge bg-danger">High</span>
                                        @endif
                                    @else
                                        <span class="badge bg-secondary">No Data</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h5>{{ $countryB->name }}</h5>
                        <p class="text-muted mb-2">{{ $countryB->official_name }}</p>

                        @if($countryB->flag_url)
                            <img src="{{ $countryB->flag_url }}" width="90" class="mb-3">
                        @endif

                        <table class="table table-bordered">
                            <tr>
                                <th>Capital</th>
                                <td>{{ $countryB->capital }}</td>
                            </tr>
                            <tr>
                                <th>Currency</th>
                                <td>{{ $countryB->currency_code }} - {{ $countryB->currency_name }}</td>
                            </tr>
                            <tr>
                                <th>Population</th>
                                <td>{{ number_format($countryB->population) }}</td>
                            </tr>
                            <tr>
                                <th>Risk Level</th>
                                <td>
                                    @if($dataB['risk'])
                                        @if($dataB['risk']->risk_level === 'Low')
                                            <span class="badge bg-success">Low</span>
                                        @elseif($dataB['risk']->risk_level === 'Medium')
                                            <span class="badge bg-warning text-dark">Medium</span>
                                        @else
                                            <span class="badge bg-danger">High</span>
                                        @endif
                                    @else
                                        <span class="badge bg-secondary">No Data</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <h5 class="mb-3">Comparison Summary</h5>

                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-dark">
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

                            <td>
                                @if(!is_null($dataA['economy']->inflation ?? null))
                                    {{ number_format($dataA['economy']->inflation, 2) }}%
                                @else
                                    <span class="text-muted">Data tidak tersedia</span>
                                @endif
                            </td>

                            <td>
                                @if(!is_null($dataB['economy']->inflation ?? null))
                                    {{ number_format($dataB['economy']->inflation, 2) }}%
                                @else
                                    <span class="text-muted">Data tidak tersedia</span>
                                @endif
                            </td>

                            <tr>
                                <th>Weather Risk</th>
                                <td>{{ $dataA['risk']->weather_risk ?? 0 }}</td>
                                <td>{{ $dataB['risk']->weather_risk ?? 0 }}</td>
                            </tr>

                            <tr>
                                <th>Currency Risk</th>
                                <td>{{ $dataA['risk']->currency_risk ?? 0 }}</td>
                                <td>{{ $dataB['risk']->currency_risk ?? 0 }}</td>
                            </tr>

                            <tr>
                                <th>News Risk</th>
                                <td>{{ $dataA['risk']->news_risk ?? 0 }}</td>
                                <td>{{ $dataB['risk']->news_risk ?? 0 }}</td>
                            </tr>

                            <tr>
                                <th>Total Risk Score</th>
                                <td>
                                    <strong>{{ $dataA['risk']->total_score ?? 0 }}</strong>
                                </td>
                                <td>
                                    <strong>{{ $dataB['risk']->total_score ?? 0 }}</strong>
                                </td>
                            </tr>

                            <tr>
                                <th>Risk Level</th>
                                <td>
                                    @if(($dataA['risk']->risk_level ?? '') === 'Low')
                                        <span class="badge bg-success">Low</span>
                                    @elseif(($dataA['risk']->risk_level ?? '') === 'Medium')
                                        <span class="badge bg-warning text-dark">Medium</span>
                                    @elseif(($dataA['risk']->risk_level ?? '') === 'High')
                                        <span class="badge bg-danger">High</span>
                                    @else
                                        <span class="badge bg-secondary">Unknown</span>
                                    @endif
                                </td>
                                <td>
                                    @if(($dataB['risk']->risk_level ?? '') === 'Low')
                                        <span class="badge bg-success">Low</span>
                                    @elseif(($dataB['risk']->risk_level ?? '') === 'Medium')
                                        <span class="badge bg-warning text-dark">Medium</span>
                                    @elseif(($dataB['risk']->risk_level ?? '') === 'High')
                                        <span class="badge bg-danger">High</span>
                                    @else
                                        <span class="badge bg-secondary">Unknown</span>
                                    @endif
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                @if(isset($decision) && $decision)
                    <div class="alert alert-primary mt-4 mb-0">
                        <h6 class="mb-2">Decision Recommendation</h6>
                        <strong>{{ $decision['title'] }}</strong>
                        <p class="mb-0 mt-2">
                            {{ $decision['summary'] }}
                        </p>
                    </div>
                @endif
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="mb-3">GDP Comparison</h5>
                        <canvas id="gdpChart" height="180"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="mb-3">Inflation Comparison</h5>
                        <canvas id="inflationChart" height="180"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="mb-3">Risk Score Comparison</h5>
                        <canvas id="riskChart" height="180"></canvas>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="alert alert-secondary">
            Silakan pilih dua negara untuk dibandingkan.
        </div>
    @endif
@endsection

@section('scripts')
@if($countryA && $countryB)
<script>
    const labels = @json($chartLabels);

    new Chart(document.getElementById('gdpChart'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'GDP',
                data: @json($gdpChartData)
            }]
        },
        options: {
            responsive: true
        }
    });

    new Chart(document.getElementById('inflationChart'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Inflation (%)',
                data: @json($inflationChartData)
            }]
        },
        options: {
            responsive: true
        }
    });

    new Chart(document.getElementById('riskChart'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Total Risk Score',
                data: @json($riskChartData)
            }]
        },
        options: {
            responsive: true,
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