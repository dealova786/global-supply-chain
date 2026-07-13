@extends('layouts.dashboard')

@section('content')
    <div class="mb-4">
        <h3>Global Weather Monitoring</h3>
        <p class="text-muted">
            Peta ini menampilkan kondisi cuaca dan risiko cuaca pada negara yang dimonitor.
        </p>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <h5 class="mb-3">Weather Risk Map</h5>
            <div id="weatherMap" style="height: 550px; border-radius: 10px;"></div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <h5 class="mb-3">Weather Data</h5>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>No</th>
                            <th>Country</th>
                            <th>Capital</th>
                            <th>Temperature</th>
                            <th>Rainfall</th>
                            <th>Wind Speed</th>
                            <th>Condition</th>
                            <th>Weather Risk</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($weatherMarkers as $marker)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $marker['name'] }}</td>
                                <td>{{ $marker['capital'] }}</td>
                                <td>{{ $marker['temperature'] }} °C</td>
                                <td>{{ $marker['rainfall'] }} mm</td>
                                <td>{{ $marker['wind_speed'] }} km/h</td>
                                <td>{{ $marker['weather_condition'] }}</td>
                                <td>
                                    @if($marker['weather_risk'] <= 30)
                                        <span class="badge bg-success">{{ $marker['weather_risk'] }} Low</span>
                                    @elseif($marker['weather_risk'] <= 60)
                                        <span class="badge bg-warning text-dark">{{ $marker['weather_risk'] }} Medium</span>
                                    @else
                                        <span class="badge bg-danger">{{ $marker['weather_risk'] }} High</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted">
                                    Belum ada data cuaca.
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
    const markers = @json($weatherMarkers);

    const map = L.map('weatherMap').setView([10, 100], 2);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    function getMarkerColor(risk) {
        if (risk <= 30) {
            return 'green';
        }

        if (risk <= 60) {
            return 'orange';
        }

        return 'red';
    }

    markers.forEach(marker => {
        const color = getMarkerColor(marker.weather_risk);

        const customIcon = L.divIcon({
            className: '',
            html: `
                <div style="
                    background:${color};
                    width:18px;
                    height:18px;
                    border-radius:50%;
                    border:3px solid white;
                    box-shadow:0 0 5px rgba(0,0,0,0.5);
                "></div>
            `,
            iconSize: [18, 18],
            iconAnchor: [9, 9]
        });

        L.marker([marker.latitude, marker.longitude], { icon: customIcon })
            .addTo(map)
            .bindPopup(`
                <strong>${marker.name}</strong><br>
                Capital: ${marker.capital}<br>
                Temperature: ${marker.temperature} °C<br>
                Rainfall: ${marker.rainfall} mm<br>
                Wind Speed: ${marker.wind_speed} km/h<br>
                Condition: ${marker.weather_condition}<br>
                Weather Risk: <strong>${marker.weather_risk}</strong>
            `);
    });
</script>
@endsection