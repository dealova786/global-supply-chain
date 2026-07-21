@extends('layouts.dashboard')

@section('content')
    @php
        $markers = collect($weatherMarkers ?? []);

        $normalCount = $markers->filter(function ($item) {
            return ($item['weather_risk'] ?? 0) < 40;
        })->count();

        $moderateCount = $markers->filter(function ($item) {
            return ($item['weather_risk'] ?? 0) >= 40 && ($item['weather_risk'] ?? 0) < 70;
        })->count();

        $highRiskCount = $markers->filter(function ($item) {
            return ($item['weather_risk'] ?? 0) >= 70;
        })->count();

        $averageRisk = $markers->count() > 0
            ? round($markers->avg('weather_risk'))
            : 0;
    @endphp

    <div class="mb-4">
        <h1 class="page-title">Global Weather Risk Map</h1>
        <p class="page-subtitle">
            Visualisasi risiko cuaca global berdasarkan data Open-Meteo yang tersimpan di weather cache.
        </p>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ url('/weather-map') }}">
                <div class="row align-items-end">
                    <div class="col-md-8">
                        <label class="form-label">Pilih Negara</label>
                        <select name="country_id" class="form-select">
                            <option value="">-- Semua Negara yang Sudah Memiliki Data Weather --</option>

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
                        <a href="{{ url('/weather-map') }}" class="btn btn-secondary w-100">
                            Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if(isset($selectedCountry) && $selectedCountry)
        <div class="alert alert-primary">
            Menampilkan data cuaca untuk <strong>{{ $selectedCountry->name }}</strong>.
        </div>
    @else
        <div class="alert alert-secondary">
            Menampilkan ringkasan weather risk dari negara yang sudah memiliki data cuaca.
        </div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon primary">
                    <i class="bi bi-cloud-sun"></i>
                </div>
                <div class="stat-label">Weather Markers</div>
                <div class="stat-value">{{ $markers->count() }}</div>
                <div class="stat-note">Data cuaca tersedia</div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon success">
                    <i class="bi bi-check-circle"></i>
                </div>
                <div class="stat-label">Normal</div>
                <div class="stat-value">{{ $normalCount }}</div>
                <div class="stat-note">Weather risk rendah</div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon warning">
                    <i class="bi bi-cloud-drizzle"></i>
                </div>
                <div class="stat-label">Moderate</div>
                <div class="stat-value">{{ $moderateCount }}</div>
                <div class="stat-note">Perlu dipantau</div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon danger">
                    <i class="bi bi-cloud-lightning-rain"></i>
                </div>
                <div class="stat-label">High Risk</div>
                <div class="stat-value">{{ $highRiskCount }}</div>
                <div class="stat-note">Berpotensi gangguan</div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h5 class="mb-1">Interactive Weather Map</h5>
                            <p class="text-muted mb-0">
                                Marker warna menunjukkan tingkat risiko cuaca berdasarkan temperatur, curah hujan, angin, dan kondisi cuaca.
                            </p>
                        </div>

                        <span class="badge bg-primary">Open-Meteo</span>
                    </div>

                    <div id="weatherMap" class="modern-map"></div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5 class="mb-3">Weather Risk Summary</h5>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Average Weather Risk</span>
                            <strong>{{ $averageRisk }}</strong>
                        </div>

                        <div class="progress" style="height: 9px;">
                            <div class="progress-bar bg-primary"
                                 style="width: {{ min($averageRisk, 100) }}%">
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-primary mb-3">
                        <strong>Interpretasi:</strong><br>
                        Semakin tinggi weather risk, semakin besar potensi gangguan pada aktivitas logistik dan rantai pasok.
                    </div>

                    <div class="small text-muted">
                        <strong>Risk rule:</strong><br>
                        Normal: &lt; 40<br>
                        Moderate: 40 - 69<br>
                        High Risk: ≥ 70
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <h5 class="mb-3">Latest Weather Records</h5>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Country</th>
                            <th>Capital</th>
                            <th>Temperature</th>
                            <th>Rainfall</th>
                            <th>Wind Speed</th>
                            <th>Condition</th>
                            <th>Weather Risk</th>
                            <th>Status</th>
                            <th>Last Updated</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($markers as $marker)
                            <tr>
                                <td>{{ $loop->iteration }}</td>

                                <td>
                                    <strong>{{ $marker['name'] ?? '-' }}</strong>
                                </td>

                                <td>{{ $marker['capital'] ?? '-' }}</td>

                                <td>{{ $marker['temperature'] ?? 0 }}°C</td>

                                <td>{{ $marker['rainfall'] ?? 0 }} mm</td>

                                <td>{{ $marker['wind_speed'] ?? 0 }} km/h</td>

                                <td>{{ $marker['weather_condition'] ?? 'Unknown' }}</td>

                                <td>
                                    <strong>{{ round($marker['weather_risk'] ?? 0) }}</strong>
                                </td>

                                <td>
                                    @if(($marker['weather_risk'] ?? 0) >= 70)
                                        <span class="badge bg-danger">High Risk</span>
                                    @elseif(($marker['weather_risk'] ?? 0) >= 40)
                                        <span class="badge bg-warning text-dark">Moderate</span>
                                    @else
                                        <span class="badge bg-success">Normal</span>
                                    @endif
                                </td>

                                <td>
                                    @if(isset($marker['recorded_at']) && $marker['recorded_at'])
                                        {{ \Carbon\Carbon::parse($marker['recorded_at'])->diffForHumans() }}
                                    @else
                                        <span class="text-muted">No update</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted">
                                    Belum ada data weather cache. Buka Country Dashboard atau pilih negara untuk mengambil data cuaca dari API.
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
        const weatherMarkers = @json($weatherMarkers ?? []);

        const map = L.map('weatherMap', {
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

        const markerGroup = L.featureGroup();

        function getWeatherStatus(risk) {
            risk = Number(risk ?? 0);

            if (risk >= 70) {
                return 'High Risk';
            }

            if (risk >= 40) {
                return 'Moderate';
            }

            return 'Normal';
        }

        function getMarkerColorByRisk(risk) {
            risk = Number(risk ?? 0);

            if (risk >= 70) {
                return '#dc2626';
            }

            if (risk >= 40) {
                return '#d97706';
            }

            return '#16a34a';
        }

        function getStatusClassByRisk(risk) {
            risk = Number(risk ?? 0);

            if (risk >= 70) {
                return 'map-status-high';
            }

            if (risk >= 40) {
                return 'map-status-moderate';
            }

            return 'map-status-normal';
        }

        function createWeatherIcon(risk) {
            const color = getMarkerColorByRisk(risk);
            const highRiskClass = Number(risk ?? 0) >= 70 ? 'high-risk' : '';

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

        function weatherPopupTemplate(marker) {
            const risk = Number(marker.weather_risk ?? 0);
            const status = getWeatherStatus(risk);

            return `
                <div>
                    <div class="map-popup-title">${marker.name ?? '-'}</div>

                    <div class="map-popup-row">
                        <span>Capital</span>
                        <strong>${marker.capital ?? '-'}</strong>
                    </div>

                    <div class="map-popup-row">
                        <span>Temperature</span>
                        <strong>${marker.temperature ?? 0}°C</strong>
                    </div>

                    <div class="map-popup-row">
                        <span>Rainfall</span>
                        <strong>${marker.rainfall ?? 0} mm</strong>
                    </div>

                    <div class="map-popup-row">
                        <span>Wind Speed</span>
                        <strong>${marker.wind_speed ?? 0} km/h</strong>
                    </div>

                    <div class="map-popup-row">
                        <span>Condition</span>
                        <strong>${marker.weather_condition ?? 'Unknown'}</strong>
                    </div>

                    <div class="map-popup-row">
                        <span>Weather Risk</span>
                        <strong>${risk}</strong>
                    </div>

                    <span class="map-status-badge ${getStatusClassByRisk(risk)}">
                        ${status}
                    </span>
                </div>
            `;
        }

        weatherMarkers.forEach(function (marker) {
            if (marker.latitude && marker.longitude) {
                const risk = Number(marker.weather_risk ?? 0);

                const weatherMarker = L.marker([marker.latitude, marker.longitude], {
                    icon: createWeatherIcon(risk)
                }).bindPopup(weatherPopupTemplate(marker));

                markerGroup.addLayer(weatherMarker);
            }
        });

        markerGroup.addTo(map);

        if (markerGroup.getLayers().length > 0) {
            map.fitBounds(markerGroup.getBounds(), {
                padding: [35, 35]
            });
        }

        const legend = L.control({
            position: 'bottomleft'
        });

        legend.onAdd = function () {
            const div = L.DomUtil.create('div', 'map-legend');

            div.innerHTML = `
                <div class="map-legend-title">Weather Risk Status</div>

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
            `;

            return div;
        };

        legend.addTo(map);
    </script>
@endsection