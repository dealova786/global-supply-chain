@extends('layouts.dashboard')

@section('content')
    <div class="mb-4">
        <h3>Port Location Dashboard</h3>
        <p class="text-muted">
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
    <div class="card shadow-sm border-0 mb-4">
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
    <div class="card shadow-sm border-0 mb-4">
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
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6>Total Ports</h6>
                    <h3>{{ $ports->count() }}</h3>
                    <small class="text-muted">Pelabuhan tersedia</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6>Normal</h6>
                    <h3>{{ $ports->where('tracking_status', 'Normal')->count() }}</h3>
                    <span class="badge bg-success">Low Risk</span>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6>Moderate</h6>
                    <h3>{{ $ports->where('tracking_status', 'Moderate')->count() }}</h3>
                    <span class="badge bg-warning text-dark">Medium Risk</span>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6>High Risk</h6>
                    <h3>{{ $ports->where('tracking_status', 'High Risk')->count() }}</h3>
                    <span class="badge bg-danger">Need Attention</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Map --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <h5 class="mb-3">
                Global Port Map
                @if(isset($selectedCountry) && $selectedCountry)
                    - {{ $selectedCountry->name }}
                @endif
            </h5>

            <div id="portMap" style="height: 430px; border-radius: 10px;"></div>
        </div>
    </div>

    {{-- Port Table --}}
    <div class="card shadow-sm border-0">
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
                    <thead class="table-dark">
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
                                    <span class="badge bg-{{ $port->tracking_badge }}">
                                        {{ $port->tracking_status }}
                                    </span>
                                </td>

                                <td>
                                    <strong>{{ $port->risk_score }}</strong>
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

        const map = L.map('portMap').setView([10, 20], 2);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        const markerGroup = L.featureGroup();

        function getMarkerColor(status) {
            if (status === 'High Risk') {
                return 'red';
            }

            if (status === 'Moderate') {
                return 'orange';
            }

            if (status === 'Normal') {
                return 'green';
            }

            return 'blue';
        }

        portMarkers.forEach(function (port) {
            if (port.latitude && port.longitude) {
                const color = getMarkerColor(port.tracking_status);

                const markerIcon = L.divIcon({
                    className: 'custom-port-marker',
                    html: `<div style="
                        width: 14px;
                        height: 14px;
                        background: ${color};
                        border-radius: 50%;
                        border: 2px solid white;
                        box-shadow: 0 0 8px rgba(0,0,0,0.35);
                    "></div>`,
                    iconSize: [14, 14],
                    iconAnchor: [7, 7]
                });

                const marker = L.marker([port.latitude, port.longitude], {
                    icon: markerIcon
                }).bindPopup(`
                    <strong>${port.port_name}</strong><br>
                    Country: ${port.country_name ?? '-'}<br>
                    Code: ${port.country_code ?? '-'}<br>
                    Region: ${port.region ?? '-'}<br>
                    Type: ${port.port_type ?? '-'}<br>
                    Status: <strong>${port.tracking_status ?? '-'}</strong><br>
                    Risk Score: ${port.risk_score ?? '-'}
                `);

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

            const routeLine = L.polyline([origin, destination], {
                color: 'blue',
                weight: 4,
                opacity: 0.8,
                dashArray: '8, 8'
            }).addTo(map);

            L.marker(origin)
                .addTo(map)
                .bindPopup(`
                    <strong>Origin Port</strong><br>
                    ${distanceData.origin.port_name}<br>
                    ${distanceData.origin.country_name}
                `);

            L.marker(destination)
                .addTo(map)
                .bindPopup(`
                    <strong>Destination Port</strong><br>
                    ${distanceData.destination.port_name}<br>
                    ${distanceData.destination.country_name}
                `);

            markerGroup.addLayer(routeLine);
        }

        markerGroup.addTo(map);

        if (markerGroup.getLayers().length > 0) {
            map.fitBounds(markerGroup.getBounds(), {
                padding: [30, 30]
            });
        }
    </script>
@endsection