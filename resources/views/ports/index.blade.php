@extends('layouts.dashboard')

@section('content')
    <div class="mb-4">
        <h1 class="page-title">Port Location Dashboard</h1>
        <p class="page-subtitle">
            Halaman ini menampilkan lokasi pelabuhan dunia, status tracking risiko pelabuhan,
            dan estimasi jarak antar pelabuhan untuk mendukung monitoring rantai pasok global.
        </p>
    </div>

    @if(isset($syncMessage) && $syncMessage)
        <div class="alert alert-info">
            {{ $syncMessage }}
        </div>
    @endif

    @if(isset($selectedCountry) && $selectedCountry && $ports->count() === 0)
        <div class="alert alert-warning">
            Tidak ada data pelabuhan untuk <strong>{{ $selectedCountry->name }}</strong>.
            Beberapa negara tidak memiliki pelabuhan laut atau data port tidak tersedia dari API.
        </div>
    @endif

    {{-- Search and Filter --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <h5 class="mb-3">Port Search</h5>

            <form method="GET" action="{{ route('ports.index') }}">
                <div class="row align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">Cari Pelabuhan / Negara</label>
                        <input type="text"
                               name="keyword"
                               class="form-control"
                               value="{{ request('keyword') }}"
                               placeholder="Contoh: Shanghai, Indonesia, Hamburg">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Filter Negara</label>
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
                            Search
                        </button>
                    </div>

                    <div class="col-md-2">
                        <a href="{{ route('ports.index') }}" class="btn btn-secondary w-100">
                            Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Port Distance Tracking --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <h5 class="mb-3">Port Distance Tracking</h5>
            <p class="text-muted">
                Pilih pelabuhan asal dan tujuan untuk menghitung estimasi jarak serta waktu perjalanan berdasarkan koordinat pelabuhan.
            </p>

            <form method="GET" action="{{ route('ports.index') }}">
                <div class="row align-items-end">
                    <div class="col-md-5">
                        <label class="form-label">Origin Port</label>
                        <select name="origin_port_id" class="form-select" required>
                            <option value="">-- Pilih Pelabuhan Asal --</option>

                            @foreach($allPorts as $port)
                                <option value="{{ $port->id }}"
                                    {{ request('origin_port_id') == $port->id ? 'selected' : '' }}>
                                    {{ $port->port_name }} - {{ $port->country_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-5">
                        <label class="form-label">Destination Port</label>
                        <select name="destination_port_id" class="form-select" required>
                            <option value="">-- Pilih Pelabuhan Tujuan --</option>

                            @foreach($allPorts as $port)
                                <option value="{{ $port->id }}"
                                    {{ request('destination_port_id') == $port->id ? 'selected' : '' }}>
                                    {{ $port->port_name }} - {{ $port->country_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            Calculate
                        </button>
                    </div>
                </div>
            </form>

            @if(isset($distanceData) && $distanceData)
                <div class="alert alert-primary mt-4 mb-0">
                    <h6 class="mb-2">Distance Result</h6>

                    <div class="row">
                        <div class="col-md-6">
                            <strong>Origin:</strong>
                            {{ $distanceData['origin']['port_name'] }}
                            - {{ $distanceData['origin']['country_name'] }}
                        </div>

                        <div class="col-md-6">
                            <strong>Destination:</strong>
                            {{ $distanceData['destination']['port_name'] }}
                            - {{ $distanceData['destination']['country_name'] }}
                        </div>

                        <div class="col-md-4 mt-2">
                            <strong>Estimated Distance:</strong>
                            {{ number_format($distanceData['distance_km'], 2) }} km
                        </div>

                        <div class="col-md-4 mt-2">
                            <strong>Estimated Time:</strong>
                            {{ number_format($distanceData['estimated_days'], 2) }} days
                        </div>

                        <div class="col-md-4 mt-2">
                            <strong>Average Speed:</strong>
                            {{ $distanceData['average_speed'] }} km/hour
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon primary">
                    <i class="bi bi-geo-alt"></i>
                </div>
                <div class="stat-label">Total Ports</div>
                <div class="stat-value">{{ $ports->count() }}</div>
                <div class="stat-note">Pelabuhan tersedia</div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon success">
                    <i class="bi bi-check-circle"></i>
                </div>
                <div class="stat-label">Normal</div>
                <div class="stat-value">{{ $ports->where('tracking_status', 'Normal')->count() }}</div>
                <div class="stat-note">Low risk ports</div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon warning">
                    <i class="bi bi-exclamation-circle"></i>
                </div>
                <div class="stat-label">Moderate</div>
                <div class="stat-value">{{ $ports->where('tracking_status', 'Moderate')->count() }}</div>
                <div class="stat-note">Medium risk ports</div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon danger">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>
                <div class="stat-label">High Risk</div>
                <div class="stat-value">{{ $ports->where('tracking_status', 'High Risk')->count() }}</div>
                <div class="stat-note">Need attention</div>
            </div>
        </div>
    </div>

    {{-- Map --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <h5 class="mb-1">
                        Global Port Map
                        @if(isset($selectedCountry) && $selectedCountry)
                            - {{ $selectedCountry->name }}
                        @endif
                    </h5>
                    <p class="text-muted mb-0">
                        Marker warna menunjukkan status risiko pelabuhan. High risk memiliki efek pulse.
                    </p>
                </div>

                <span class="badge bg-primary">Interactive Map</span>
            </div>

            <div id="portMap" class="modern-map"></div>
        </div>
    </div>

    {{-- Port Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <h5 class="mb-3">
                Port Tracking Dataset
                @if(isset($selectedCountry) && $selectedCountry)
                    - {{ $selectedCountry->name }}
                @else
                    - All Available Ports
                @endif
            </h5>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Port Name</th>
                            <th>Country</th>
                            <th>Code</th>
                            <th>Region</th>
                            <th>Type</th>
                            <th>Tracking Status</th>
                            <th>Risk Score</th>
                            <th>Last Updated</th>
                            <th>Coordinate</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($ports as $port)
                            <tr>
                                <td>{{ $loop->iteration }}</td>

                                <td>
                                    <strong>{{ $port->port_name }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $port->harbor_size ?? 'Unknown size' }}</small>
                                </td>

                                <td>{{ $port->country_name ?? '-' }}</td>

                                <td>{{ $port->country_code ?? '-' }}</td>

                                <td>{{ $port->region ?? '-' }}</td>

                                <td>{{ $port->port_type ?? '-' }}</td>

                                <td>
                                    <span class="badge bg-{{ $port->tracking_badge ?? 'secondary' }}">
                                        {{ $port->tracking_status ?? 'Not Tracked' }}
                                    </span>
                                </td>

                                <td>
                                    <strong>{{ $port->risk_score ?? '-' }}</strong>
                                </td>

                                <td>
                                    @if($port->last_updated)
                                        {{ \Carbon\Carbon::parse($port->last_updated)->diffForHumans() }}
                                    @else
                                        <span class="text-muted">No update</span>
                                    @endif
                                </td>

                                <td>
                                    {{ $port->latitude ?? '-' }},
                                    {{ $port->longitude ?? '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted">
                                    Belum ada data pelabuhan untuk pilihan ini.
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
        const portMarkers = @json($portMarkers ?? []);
        const distanceData = @json($distanceData ?? null);

        const map = L.map('portMap', {
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

        function createPortIcon(status) {
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

        function popupTemplate(port) {
            return `
                <div>
                    <div class="map-popup-title">${port.port_name ?? '-'}</div>

                    <div class="map-popup-row">
                        <span>Country</span>
                        <strong>${port.country_name ?? '-'}</strong>
                    </div>

                    <div class="map-popup-row">
                        <span>Code</span>
                        <strong>${port.country_code ?? '-'}</strong>
                    </div>

                    <div class="map-popup-row">
                        <span>Region</span>
                        <strong>${port.region ?? '-'}</strong>
                    </div>

                    <div class="map-popup-row">
                        <span>Type</span>
                        <strong>${port.port_type ?? '-'}</strong>
                    </div>

                    <div class="map-popup-row">
                        <span>Risk Score</span>
                        <strong>${port.risk_score ?? '-'}</strong>
                    </div>

                    <span class="map-status-badge ${getStatusClass(port.tracking_status)}">
                        ${port.tracking_status ?? 'Not Tracked'}
                    </span>
                </div>
            `;
        }

        portMarkers.forEach(function (port) {
            if (port.latitude && port.longitude) {
                const marker = L.marker([port.latitude, port.longitude], {
                    icon: createPortIcon(port.tracking_status)
                }).bindPopup(popupTemplate(port));

                markerGroup.addLayer(marker);
            }
        });

        if (distanceData) {
            const origin = [
                distanceData.origin.latitude,
                distanceData.origin.longitude
            ];

            const destination = [
                distanceData.destination.latitude,
                distanceData.destination.longitude
            ];

            const originIcon = L.divIcon({
                className: '',
                html: `<div class="route-port-marker"><i class="bi bi-box-arrow-up-right"></i></div>`,
                iconSize: [28, 28],
                iconAnchor: [14, 14],
                popupAnchor: [0, -12]
            });

            const destinationIcon = L.divIcon({
                className: '',
                html: `<div class="route-port-marker destination-marker"><i class="bi bi-flag-fill"></i></div>`,
                iconSize: [28, 28],
                iconAnchor: [14, 14],
                popupAnchor: [0, -12]
            });

            const routeLine = L.polyline([origin, destination], {
                color: '#2563eb',
                weight: 4,
                opacity: 0.85,
                dashArray: '10, 10',
                lineCap: 'round'
            }).bindPopup(`
                <div>
                    <div class="map-popup-title">Estimated Shipping Route</div>

                    <div class="map-popup-row">
                        <span>Distance</span>
                        <strong>${distanceData.distance_km} km</strong>
                    </div>

                    <div class="map-popup-row">
                        <span>Estimated Time</span>
                        <strong>${distanceData.estimated_days} days</strong>
                    </div>

                    <div class="map-popup-row">
                        <span>Average Speed</span>
                        <strong>${distanceData.average_speed} km/hour</strong>
                    </div>
                </div>
            `);

            const originMarker = L.marker(origin, {
                icon: originIcon
            }).bindPopup(`
                <div>
                    <div class="map-popup-title">Origin Port</div>

                    <div class="map-popup-row">
                        <span>Port</span>
                        <strong>${distanceData.origin.port_name}</strong>
                    </div>

                    <div class="map-popup-row">
                        <span>Country</span>
                        <strong>${distanceData.origin.country_name}</strong>
                    </div>
                </div>
            `);

            const destinationMarker = L.marker(destination, {
                icon: destinationIcon
            }).bindPopup(`
                <div>
                    <div class="map-popup-title">Destination Port</div>

                    <div class="map-popup-row">
                        <span>Port</span>
                        <strong>${distanceData.destination.port_name}</strong>
                    </div>

                    <div class="map-popup-row">
                        <span>Country</span>
                        <strong>${distanceData.destination.country_name}</strong>
                    </div>
                </div>
            `);

            markerGroup.addLayer(routeLine);
            markerGroup.addLayer(originMarker);
            markerGroup.addLayer(destinationMarker);
        }

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
                <div class="map-legend-title">Port Risk Status</div>

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
    </script>
@endsection