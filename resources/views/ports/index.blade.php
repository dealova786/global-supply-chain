@extends('layouts.dashboard')

@section('content')
    <div class="mb-4">
        <h3>Port Location Dashboard</h3>
        <p class="text-muted">
            Halaman ini menampilkan lokasi pelabuhan dunia untuk mendukung monitoring risiko rantai pasok global.
        </p>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('ports.index') }}">
                <div class="row align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">Cari Pelabuhan / Negara</label>
                        <input type="text" name="keyword" class="form-control"
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

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <h5 class="mb-3">Global Port Map</h5>
            <div id="portMap" style="height: 550px; border-radius: 10px;"></div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <h5 class="mb-3">Port Dataset</h5>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>No</th>
                            <th>Port Name</th>
                            <th>Country</th>
                            <th>Code</th>
                            <th>Region</th>
                            <th>Harbor Size</th>
                            <th>Type</th>
                            <th>Coordinate</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($ports as $port)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td><strong>{{ $port->port_name }}</strong></td>
                                <td>{{ $port->country_name }}</td>
                                <td>{{ $port->country_code }}</td>
                                <td>{{ $port->region }}</td>
                                <td>{{ $port->harbor_size }}</td>
                                <td>{{ $port->port_type }}</td>
                                <td>{{ $port->latitude }}, {{ $port->longitude }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted">
                                    Data pelabuhan tidak ditemukan.
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
    const ports = @json($ports);

    const map = L.map('portMap').setView([15, 100], 2);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    ports.forEach(port => {
        if (port.latitude && port.longitude) {
            L.marker([port.latitude, port.longitude])
                .addTo(map)
                .bindPopup(`
                    <strong>${port.port_name}</strong><br>
                    Country: ${port.country_name}<br>
                    Code: ${port.country_code}<br>
                    Region: ${port.region ?? '-'}<br>
                    Harbor Size: ${port.harbor_size ?? '-'}<br>
                    Type: ${port.port_type ?? '-'}
                `);
        }
    });
</script>
@endsection