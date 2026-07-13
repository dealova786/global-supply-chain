@php
    use Illuminate\Support\Str;
@endphp

@extends('layouts.dashboard')

@section('content')
    <div class="mb-4">
        <h3>Global Country Dashboard</h3>
        <p class="text-muted">
            Pilih negara untuk melihat data awal monitoring risiko rantai pasok global.
        </p>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('country.dashboard') }}">
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
                            Analyze Country
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

@if($selectedCountry)
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6>Country</h6>
                    <h4>{{ $selectedCountry->name }}</h4>
                    <small>{{ $selectedCountry->official_name }}</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6>Capital</h6>
                    <h4>{{ $selectedCountry->capital }}</h4>
                    <small>{{ $selectedCountry->region }}</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6>Currency</h6>
                    <h4>{{ $selectedCountry->currency_code }}</h4>
                    <small>{{ $selectedCountry->currency_name }}</small>
                </div>
            </div>
        </div>

            <div class="col-md-3">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h6>Population</h6>
                        <h4>{{ number_format($selectedCountry->population) }}</h4>
                        <small>Total population</small>
                    </div>
                </div>
            </div>
        </div>

    @if($weatherData)
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6>Temperature</h6>
                    <h4>{{ $weatherData['temperature'] }} °C</h4>
                    <small>Current temperature</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6>Rainfall</h6>
                    <h4>{{ $weatherData['rainfall'] }} mm</h4>
                    <small>Current precipitation</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6>Wind Speed</h6>
                    <h4>{{ $weatherData['wind_speed'] }} km/h</h4>
                    <small>Current wind speed</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6>Weather Risk</h6>
                    <h4>
                        {{ $weatherData['weather_risk'] }}
                    </h4>
                    <small>{{ $weatherData['weather_condition'] }}</small>
                </div>
            </div>
        </div>
    </div>
@endif
        
        @if($economicData)
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6>GDP</h6>
                    <h4>$ {{ number_format($economicData['gdp'] ?? 0, 0) }}</h4>
                    <small>Current US$ - Year {{ $economicData['year'] ?? '-' }}</small>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6>Inflation</h6>
                    <h4>{{ number_format($economicData['inflation'] ?? 0, 2) }}%</h4>
                    <small>Consumer prices annual %</small>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6>Population</h6>
                    <h4>{{ number_format($economicData['population'] ?? 0) }}</h4>
                    <small>World Bank population data</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6>Exports</h6>
                    <h4>$ {{ number_format($economicData['exports'] ?? 0, 0) }}</h4>
                    <small>Goods and services, current US$</small>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6>Imports</h6>
                    <h4>$ {{ number_format($economicData['imports'] ?? 0, 0) }}</h4>
                    <small>Goods and services, current US$</small>
                </div>
            </div>
        </div>
    </div>
@endif

@if($currencyData)
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6>Exchange Rate</h6>
                    <h4>
                        1 {{ $currencyData['base_currency'] }} =
                        {{ number_format($currencyData['exchange_rate'], 6) }}
                        {{ $currencyData['target_currency'] }}
                    </h4>
                    <small>Latest currency rate</small>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6>Currency Risk</h6>
                    <h4>{{ $currencyData['currency_risk'] }}</h4>
                    <small>Risk based on exchange rate movement</small>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6>Rate Date</h6>
                    <h4>{{ $currencyData['rate_date'] }}</h4>
                    <small>Stored exchange rate date</small>
                </div>
            </div>
        </div>
    </div>
@endif

@if($riskData)
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="mb-1">Supply Chain Risk Score</h5>
                    <p class="text-muted mb-0">
                        Perhitungan risiko berdasarkan cuaca, inflasi, kurs mata uang, dan sentimen berita.
                    </p>
                </div>

                <div class="text-end">
                    @if($riskData['risk_level'] === 'Low')
                        <span class="badge bg-success fs-6">Low Risk</span>
                    @elseif($riskData['risk_level'] === 'Medium')
                        <span class="badge bg-warning text-dark fs-6">Medium Risk</span>
                    @else
                        <span class="badge bg-danger fs-6">High Risk</span>
                    @endif
                </div>
            </div>

            <div class="row g-3">
                <div class="col-md-4">
                    <div class="p-3 border rounded">
                        <h6>Total Risk Score</h6>
                        <h2>{{ $riskData['total_score'] }}</h2>
                        <small>Risk Level: {{ $riskData['risk_level'] }}</small>
                    </div>
                </div>

                <div class="col-md-8">
                    <table class="table table-bordered mb-0">
                        <tr>
                            <th>Weather Risk</th>
                            <td>{{ $riskData['weather_risk'] }}</td>
                            <td>Weight 30%</td>
                        </tr>
                        <tr>
                            <th>Inflation Risk</th>
                            <td>{{ $riskData['inflation_risk'] }}</td>
                            <td>Weight 20%</td>
                        </tr>
                        <tr>
                            <th>News Risk</th>
                            <td>{{ $riskData['news_risk'] }}</td>
                            <td>Weight 40%</td>
                        </tr>
                        <tr>
                            <th>Currency Risk</th>
                            <td>{{ $riskData['currency_risk'] }}</td>
                            <td>Weight 10%</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endif

@if(!empty($newsData))
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <h5 class="mb-3">News Intelligence</h5>
            <p class="text-muted">
                Berita terkait logistik, perdagangan, ekonomi, shipping, geopolitik, dan supply chain.
            </p>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>No</th>
                            <th>Title</th>
                            <th>Source</th>
                            <th>Sentiment</th>
                            <th>Scores</th>
                            <th>Link</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($newsData as $news)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <strong>{{ $news['title'] }}</strong><br>
                                    <small class="text-muted">
                                        {{ Str::limit($news['description'] ?? '-', 100) }}
                                    </small>
                                </td>
                                <td>{{ $news['source'] ?? '-' }}</td>
                                <td>
                                    @if($news['sentiment'] === 'Positive')
                                        <span class="badge bg-success">Positive</span>
                                    @elseif($news['sentiment'] === 'Negative')
                                        <span class="badge bg-danger">Negative</span>
                                    @else
                                        <span class="badge bg-secondary">Neutral</span>
                                    @endif
                                </td>
                                <td>
                                    <small>
                                        + {{ $news['positive_score'] }} |
                                        - {{ $news['negative_score'] }} |
                                        Neutral {{ $news['neutral_score'] }}
                                    </small>
                                </td>
                                <td>
                                    @if($news['url'])
                                        <a href="{{ $news['url'] }}" target="_blank" class="btn btn-sm btn-outline-primary">
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
@endif

        <div class="row g-3">
            <div class="col-md-5">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h5>Country Information</h5>

                        @if($selectedCountry->flag_url)
                            <img src="{{ $selectedCountry->flag_url }}" width="120" class="mb-3">
                        @endif

                        <table class="table table-bordered">
                            <tr>
                                <th>Official Name</th>
                                <td>{{ $selectedCountry->official_name }}</td>
                            </tr>
                            <tr>
                                <th>Code</th>
                                <td>{{ $selectedCountry->cca2 }} / {{ $selectedCountry->cca3 }}</td>
                            </tr>
                            <tr>
                                <th>Region</th>
                                <td>{{ $selectedCountry->region }}</td>
                            </tr>
                            <tr>
                                <th>Subregion</th>
                                <td>{{ $selectedCountry->subregion }}</td>
                            </tr>
                            <tr>
                                <th>Language</th>
                                <td>{{ $selectedCountry->language }}</td>
                            </tr>
                            <tr>
                                <th>Coordinate</th>
                                <td>{{ $selectedCountry->latitude }}, {{ $selectedCountry->longitude }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-md-7">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h5>Country Location Map</h5>
                        <div id="countryMap" style="height: 400px; border-radius: 10px;"></div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="alert alert-info">
            Silakan pilih negara terlebih dahulu untuk menampilkan data monitoring.
        </div>
    @endif
@endsection

@section('scripts')
@if($selectedCountry)
<script>
    const latitude = {{ $selectedCountry->latitude }};
    const longitude = {{ $selectedCountry->longitude }};

    const map = L.map('countryMap').setView([latitude, longitude], 5);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    L.marker([latitude, longitude])
        .addTo(map)
        .bindPopup(`
            <strong>{{ $selectedCountry->name }}</strong><br>
            Capital: {{ $selectedCountry->capital }}<br>
            Currency: {{ $selectedCountry->currency_code }}
        `)
        .openPopup();
</script>

@endif
@endsection