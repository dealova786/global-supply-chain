@extends('layouts.dashboard')

@section('content')
    @php
        $weatherData = $weatherData ?? [];
        $economicData = $economicData ?? [];
        $currencyData = $currencyData ?? [];
        $riskData = $riskData ?? [];
        $newsData = collect($newsData ?? []);

        $populationValue = $selectedCountry->population ?? ($economicData['population'] ?? null);

        $riskScore = round($riskData['total_score'] ?? 0);
        $riskLevel = $riskData['risk_level'] ?? 'No Data';

        $weatherRisk = round($riskData['weather_risk'] ?? $weatherData['weather_risk'] ?? 0);
        $inflationRisk = round($riskData['inflation_risk'] ?? 0);
        $newsRisk = round($riskData['news_risk'] ?? ($newsData->count() > 0 ? $newsData->avg('news_risk') : 0));
        $currencyRisk = round($riskData['currency_risk'] ?? $currencyData['currency_risk'] ?? 0);

        $countryMapData = null;

        if (isset($selectedCountry) && $selectedCountry) {
            $countryMapData = [
                'name' => $selectedCountry->name,
                'official_name' => $selectedCountry->official_name,
                'capital' => $selectedCountry->capital,
                'region' => $selectedCountry->region,
                'subregion' => $selectedCountry->subregion,
                'latitude' => (float) $selectedCountry->latitude,
                'longitude' => (float) $selectedCountry->longitude,
                'currency_code' => $selectedCountry->currency_code,
                'population' => $selectedCountry->population,
                'risk_score' => $riskScore,
                'risk_level' => $riskLevel,
                'weather_condition' => $weatherData['weather_condition'] ?? 'Unknown',
                'temperature' => $weatherData['temperature'] ?? 0,
                'rainfall' => $weatherData['rainfall'] ?? 0,
                'wind_speed' => $weatherData['wind_speed'] ?? 0,
            ];
        }
    @endphp

    <div class="mb-4">
        <h1 class="page-title">Country Intelligence Dashboard</h1>
        <p class="page-subtitle">
            Pantau profil negara, cuaca, ekonomi, kurs, berita, dan risk score berdasarkan hasil integrasi API.
        </p>
    </div>

    {{-- Country Selector --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ url('/country-dashboard') }}">
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
                        <a href="{{ url('/country-dashboard') }}" class="btn btn-secondary w-100">
                            Reset
                        </a>
                    </div>
                </div>
            </form>

            <small class="text-muted d-block mt-3">
                Data source: REST Countries, Open-Meteo, World Bank, Currency API, GNews, dan internal risk scoring.
            </small>
        </div>
    </div>

    @if(!isset($selectedCountry) || !$selectedCountry)
        <div class="alert alert-secondary">
            Silakan pilih negara terlebih dahulu untuk melihat analisis risiko rantai pasok.
        </div>
    @else
        {{-- Header Country --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <div class="d-flex align-items-center gap-3">
                            @if($selectedCountry->flag_url)
                                <img src="{{ $selectedCountry->flag_url }}"
                                     alt="{{ $selectedCountry->name }}"
                                     style="width: 72px; height: 48px; object-fit: cover; border-radius: 10px; border: 1px solid #e2e8f0;">
                            @endif

                            <div>
                                <h2 class="mb-1 fw-bold">{{ $selectedCountry->name }}</h2>
                                <p class="text-muted mb-0">
                                    {{ $selectedCountry->official_name ?? '-' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 text-md-end mt-3 mt-md-0">
                        @if($riskLevel === 'High')
                            <span class="badge bg-danger fs-6">High Risk</span>
                        @elseif($riskLevel === 'Medium')
                            <span class="badge bg-warning text-dark fs-6">Medium Risk</span>
                        @elseif($riskLevel === 'Low')
                            <span class="badge bg-success fs-6">Low Risk</span>
                        @else
                            <span class="badge bg-secondary fs-6">No Data</span>
                        @endif

                        <h3 class="mt-2 mb-0">{{ $riskScore }}</h3>
                        <small class="text-muted">Total Risk Score</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- Summary Cards --}}
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon primary">
                        <i class="bi bi-building"></i>
                    </div>
                    <div class="stat-label">Capital</div>
                    <div class="stat-value" style="font-size: 22px;">
                        {{ $selectedCountry->capital ?? '-' }}
                    </div>
                    <div class="stat-note">{{ $selectedCountry->region ?? '-' }}</div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon success">
                        <i class="bi bi-people"></i>
                    </div>
                    <div class="stat-label">Population</div>
                    <div class="stat-value" style="font-size: 22px;">
                        @if(!is_null($populationValue) && $populationValue > 0)
                            {{ number_format($populationValue) }}
                        @else
                            N/A
                        @endif
                    </div>
                    <div class="stat-note">REST Countries data</div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon warning">
                        <i class="bi bi-currency-exchange"></i>
                    </div>
                    <div class="stat-label">Currency</div>
                    <div class="stat-value" style="font-size: 22px;">
                        {{ $selectedCountry->currency_code ?? '-' }}
                    </div>
                    <div class="stat-note">{{ $selectedCountry->currency_name ?? '-' }}</div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon danger">
                        <i class="bi bi-translate"></i>
                    </div>
                    <div class="stat-label">Language</div>
                    <div class="stat-value" style="font-size: 22px;">
                        {{ $selectedCountry->language ?? '-' }}
                    </div>
                    <div class="stat-note">{{ $selectedCountry->subregion ?? '-' }}</div>
                </div>
            </div>
        </div>

        {{-- Map and Risk Chart --}}
        <div class="row g-4 mb-4">
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h5 class="mb-1">Country Location Map</h5>
                                <p class="text-muted mb-0">
                                    Lokasi negara dengan marker berdasarkan status risk score.
                                </p>
                            </div>

                            <span class="badge bg-primary">Interactive Map</span>
                        </div>

                        <div id="countryMap" class="modern-map"></div>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="stock-chart-card h-100">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <div class="stock-chart-title">Risk Component Movement</div>
                            <div class="stock-chart-subtitle">
                                Grafik komponen risiko dengan tampilan stock-style line chart.
                            </div>
                        </div>

                        <span class="badge bg-primary">Stock Style</span>
                    </div>

                    <div class="chart-box">
                        <canvas id="countryRiskChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- Weather and Economy --}}
        <div class="row g-4 mb-4">
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="mb-3">
                            <i class="bi bi-cloud-sun text-primary me-2"></i>
                            Weather Intelligence
                        </h5>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="p-3 rounded-4 border">
                                    <div class="text-muted small">Temperature</div>
                                    <h4 class="mb-0">{{ $weatherData['temperature'] ?? 0 }}°C</h4>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="p-3 rounded-4 border">
                                    <div class="text-muted small">Rainfall</div>
                                    <h4 class="mb-0">{{ $weatherData['rainfall'] ?? 0 }} mm</h4>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="p-3 rounded-4 border">
                                    <div class="text-muted small">Wind Speed</div>
                                    <h4 class="mb-0">{{ $weatherData['wind_speed'] ?? 0 }} km/h</h4>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="p-3 rounded-4 border">
                                    <div class="text-muted small">Condition</div>
                                    <h4 class="mb-0">{{ $weatherData['weather_condition'] ?? 'Unknown' }}</h4>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <div class="d-flex justify-content-between mb-1">
                                <span>Weather Risk</span>
                                <strong>{{ $weatherRisk }}</strong>
                            </div>

                            <div class="progress" style="height: 9px;">
                                <div class="progress-bar bg-info"
                                     style="width: {{ min($weatherRisk, 100) }}%">
                                </div>
                            </div>
                        </div>

                        <small class="text-muted d-block mt-3">
                            Data source: Open-Meteo API cache.
                        </small>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="mb-3">
                            <i class="bi bi-bank text-primary me-2"></i>
                            Economic Indicators
                        </h5>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="p-3 rounded-4 border">
                                    <div class="text-muted small">GDP</div>
                                    <h4 class="mb-0">
                                        $ {{ number_format($economicData['gdp'] ?? 0, 0) }}
                                    </h4>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="p-3 rounded-4 border">
                                    <div class="text-muted small">Inflation</div>
                                    <h4 class="mb-0">
                                        @if(!is_null($economicData['inflation'] ?? null))
                                            {{ number_format($economicData['inflation'], 2) }}%
                                        @else
                                            N/A
                                        @endif
                                    </h4>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="p-3 rounded-4 border">
                                    <div class="text-muted small">Exports</div>
                                    <h4 class="mb-0">
                                        $ {{ number_format($economicData['exports'] ?? 0, 0) }}
                                    </h4>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="p-3 rounded-4 border">
                                    <div class="text-muted small">Imports</div>
                                    <h4 class="mb-0">
                                        $ {{ number_format($economicData['imports'] ?? 0, 0) }}
                                    </h4>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <div class="d-flex justify-content-between mb-1">
                                <span>Inflation Risk</span>
                                <strong>{{ $inflationRisk }}</strong>
                            </div>

                            <div class="progress" style="height: 9px;">
                                <div class="progress-bar bg-warning"
                                     style="width: {{ min($inflationRisk, 100) }}%">
                                </div>
                            </div>
                        </div>

                        <small class="text-muted d-block mt-3">
                            Data source: World Bank API cache.
                            Tahun data: {{ $economicData['year'] ?? '-' }}
                        </small>
                    </div>
                </div>
            </div>
        </div>

        {{-- Currency and Risk Score --}}
        <div class="row g-4 mb-4">
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="mb-3">
                            <i class="bi bi-currency-dollar text-primary me-2"></i>
                            Currency Risk
                        </h5>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="p-3 rounded-4 border">
                                    <div class="text-muted small">Base Currency</div>
                                    <h4 class="mb-0">
                                        {{ $currencyData['base_currency'] ?? $selectedCountry->currency_code ?? '-' }}
                                    </h4>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="p-3 rounded-4 border">
                                    <div class="text-muted small">Target Currency</div>
                                    <h4 class="mb-0">
                                        {{ $currencyData['target_currency'] ?? 'USD' }}
                                    </h4>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="p-3 rounded-4 border">
                                    <div class="text-muted small">Exchange Rate</div>
                                    <h4 class="mb-0">
                                        {{ number_format($currencyData['exchange_rate'] ?? 0, 6) }}
                                    </h4>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <div class="d-flex justify-content-between mb-1">
                                <span>Currency Risk</span>
                                <strong>{{ $currencyRisk }}</strong>
                            </div>

                            <div class="progress" style="height: 9px;">
                                <div class="progress-bar bg-success"
                                     style="width: {{ min($currencyRisk, 100) }}%">
                                </div>
                            </div>
                        </div>

                        <small class="text-muted d-block mt-3">
                            Data source: Currency API cache.
                        </small>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="mb-3">
                            <i class="bi bi-activity text-primary me-2"></i>
                            Risk Score Breakdown
                        </h5>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span>Total Risk Score</span>
                                <strong>{{ $riskScore }}</strong>
                            </div>

                            <div class="progress" style="height: 10px;">
                                <div class="progress-bar bg-primary"
                                     style="width: {{ min($riskScore, 100) }}%">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span>Weather Risk</span>
                                <strong>{{ $weatherRisk }}</strong>
                            </div>

                            <div class="progress" style="height: 9px;">
                                <div class="progress-bar bg-info"
                                     style="width: {{ min($weatherRisk, 100) }}%">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span>Inflation Risk</span>
                                <strong>{{ $inflationRisk }}</strong>
                            </div>

                            <div class="progress" style="height: 9px;">
                                <div class="progress-bar bg-warning"
                                     style="width: {{ min($inflationRisk, 100) }}%">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span>News Risk</span>
                                <strong>{{ $newsRisk }}</strong>
                            </div>

                            <div class="progress" style="height: 9px;">
                                <div class="progress-bar bg-danger"
                                     style="width: {{ min($newsRisk, 100) }}%">
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-primary mb-0">
                            <strong>Risk Level:</strong>
                            @if($riskLevel === 'High')
                                <span class="badge bg-danger ms-1">High</span>
                            @elseif($riskLevel === 'Medium')
                                <span class="badge bg-warning text-dark ms-1">Medium</span>
                            @elseif($riskLevel === 'Low')
                                <span class="badge bg-success ms-1">Low</span>
                            @else
                                <span class="badge bg-secondary ms-1">No Data</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- News --}}
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h5 class="mb-1">News Intelligence</h5>
                        <p class="text-muted mb-0">
                            Berita terkait negara dan hasil analisis sentimen untuk mendukung news risk.
                        </p>
                    </div>

                    <span class="badge bg-primary">
                        {{ $newsData->count() }} News
                    </span>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Title</th>
                                <th>Source</th>
                                <th>Sentiment</th>
                                <th>News Risk</th>
                                <th>Published</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($newsData as $news)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>

                                    <td>
                                        <strong>{{ \Illuminate\Support\Str::limit(data_get($news, 'title'), 80) }}</strong>
                                        <br>
                                        <small class="text-muted">
                                            {{ \Illuminate\Support\Str::limit(data_get($news, 'description'), 120) }}
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
                                        <strong>{{ data_get($news, 'news_risk') ?? 0 }}</strong>
                                    </td>

                                    <td>
                                        @if(data_get($news, 'published_at'))
                                            {{ \Carbon\Carbon::parse(data_get($news, 'published_at'))->diffForHumans() }}
                                        @else
                                            <span class="text-muted">No date</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">
                                        Belum ada berita spesifik untuk negara ini atau quota GNews sedang habis.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    @endif
@endsection

@section('scripts')
    @if(isset($selectedCountry) && $selectedCountry)
        <script>
            const countryMapData = @json($countryMapData);

            const map = L.map('countryMap', {
                zoomControl: false,
                worldCopyJump: true
            }).setView([10, 20], 2);

            L.control.zoom({
                position: 'bottomright'
            }).addTo(map);

            const lightMap = L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
                attribution: '© OpenStreetMap contributors © CARTO',
                maxZoom: 19
            });

            const darkMap = L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
                attribution: '© OpenStreetMap contributors © CARTO',
                maxZoom: 19
            });

            const osmMap = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors',
                maxZoom: 19
            });

            lightMap.addTo(map);

            L.control.layers({
                'Light Map': lightMap,
                'Dark Map': darkMap,
                'OpenStreetMap': osmMap
            }, null, {
                position: 'topright',
                collapsed: true
            }).addTo(map);

            function getCountryStatus(score, level) {
                score = Number(score ?? 0);

                if (level === 'High' || score >= 70) {
                    return 'High Risk';
                }

                if (level === 'Medium' || score >= 40) {
                    return 'Moderate';
                }

                if (level === 'Low' || score > 0) {
                    return 'Normal';
                }

                return 'Not Tracked';
            }

            function getMarkerColor(status) {
                if (status === 'High Risk') {
                    return '#dc2626';
                }

                if (status === 'Moderate') {
                    return '#d97706';
                }

                if (status === 'Normal') {
                    return '#16a34a';
                }

                return '#64748b';
            }

            function getStatusClass(status) {
                if (status === 'High Risk') {
                    return 'map-status-high';
                }

                if (status === 'Moderate') {
                    return 'map-status-moderate';
                }

                if (status === 'Normal') {
                    return 'map-status-normal';
                }

                return 'map-status-none';
            }

            function createCountryIcon(status) {
                const color = getMarkerColor(status);
                const highRiskClass = status === 'High Risk' ? 'high-risk' : '';

                return L.divIcon({
                    className: '',
                    html: `
                        <div class="port-marker ${highRiskClass}" style="background:${color};"></div>
                    `,
                    iconSize: [18, 18],
                    iconAnchor: [9, 9],
                    popupAnchor: [0, -8]
                });
            }

            function countryPopupTemplate(country) {
                const status = getCountryStatus(country.risk_score, country.risk_level);

                return `
                    <div>
                        <div class="map-popup-title">${country.name ?? '-'}</div>

                        <div class="map-popup-row">
                            <span>Capital</span>
                            <strong>${country.capital ?? '-'}</strong>
                        </div>

                        <div class="map-popup-row">
                            <span>Region</span>
                            <strong>${country.region ?? '-'}</strong>
                        </div>

                        <div class="map-popup-row">
                            <span>Currency</span>
                            <strong>${country.currency_code ?? '-'}</strong>
                        </div>

                        <div class="map-popup-row">
                            <span>Temperature</span>
                            <strong>${country.temperature ?? 0}°C</strong>
                        </div>

                        <div class="map-popup-row">
                            <span>Weather</span>
                            <strong>${country.weather_condition ?? 'Unknown'}</strong>
                        </div>

                        <div class="map-popup-row">
                            <span>Risk Score</span>
                            <strong>${country.risk_score ?? 0}</strong>
                        </div>

                        <span class="map-status-badge ${getStatusClass(status)}">
                            ${status}
                        </span>
                    </div>
                `;
            }

            if (countryMapData && countryMapData.latitude && countryMapData.longitude) {
                const status = getCountryStatus(
                    countryMapData.risk_score,
                    countryMapData.risk_level
                );

                const marker = L.marker([
                    countryMapData.latitude,
                    countryMapData.longitude
                ], {
                    icon: createCountryIcon(status)
                }).addTo(map);

                marker.bindPopup(countryPopupTemplate(countryMapData));

                map.setView([
                    countryMapData.latitude,
                    countryMapData.longitude
                ], 5);
            }

            const legend = L.control({
                position: 'bottomleft'
            });

            legend.onAdd = function () {
                const div = L.DomUtil.create('div', 'map-legend');

                div.innerHTML = `
                    <div class="map-legend-title">Country Risk Status</div>

                    <div class="map-legend-item">
                        <span class="map-legend-dot" style="background:#16a34a;"></span>
                        Normal
                    </div>

                    <div class="map-legend-item">
                        <span class="map-legend-dot" style="background:#d97706;"></span>
                        Moderate
                    </div>

                    <div class="map-legend-item">
                        <span class="map-legend-dot" style="background:#dc2626;"></span>
                        High Risk
                    </div>

                    <div class="map-legend-item">
                        <span class="map-legend-dot" style="background:#64748b;"></span>
                        Not Tracked
                    </div>
                `;

                return div;
            };

            legend.addTo(map);

            const componentLabels = ['Weather', 'Inflation', 'News', 'Currency', 'Total'];
            const componentValues = [
                {{ $weatherRisk }},
                {{ $inflationRisk }},
                {{ $newsRisk }},
                {{ $currencyRisk }},
                {{ $riskScore }}
            ];

            renderStockLineChart(
                'countryRiskChart',
                componentLabels,
                componentValues,
                'Risk Component'
            );
        </script>
    @endif
@endsection