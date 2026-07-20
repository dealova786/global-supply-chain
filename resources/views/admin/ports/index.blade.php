@extends('layouts.dashboard')

@section('content')
    @php
        $ports = isset($ports)
            ? $ports
            : \App\Models\Port::with('country')->latest()->get();

        $countries = isset($countries)
            ? $countries
            : \App\Models\Country::orderBy('name', 'asc')->get();

        $selectedCountry = $selectedCountry ?? null;

        $totalPorts = \App\Models\Port::count();
        $totalCountriesWithPorts = \App\Models\Port::distinct('country_id')->count('country_id');
        $apiPorts = \App\Models\Port::whereNotNull('created_at')->count();
        $latestPort = \App\Models\Port::latest()->first();

        $portCollection = collect($ports instanceof \Illuminate\Pagination\AbstractPaginator ? $ports->items() : $ports);
    @endphp

    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h1 class="page-title">Admin Manage Ports</h1>
            <p class="page-subtitle">
                Kelola data pelabuhan, sinkronisasi port dari GeoNames API, dan pantau cache port untuk dashboard map.
            </p>
        </div>

        <a href="{{ route('admin.ports.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i>
            Add Port
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <strong>Terjadi kesalahan:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Summary Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon primary">
                    <i class="bi bi-geo-alt"></i>
                </div>
                <div class="stat-label">Total Ports</div>
                <div class="stat-value">{{ $totalPorts }}</div>
                <div class="stat-note">Data pelabuhan tersimpan</div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon success">
                    <i class="bi bi-globe2"></i>
                </div>
                <div class="stat-label">Countries Covered</div>
                <div class="stat-value">{{ $totalCountriesWithPorts }}</div>
                <div class="stat-note">Negara punya data port</div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon warning">
                    <i class="bi bi-database"></i>
                </div>
                <div class="stat-label">API Cache</div>
                <div class="stat-value">{{ $apiPorts }}</div>
                <div class="stat-note">GeoNames records</div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon danger">
                    <i class="bi bi-clock-history"></i>
                </div>
                <div class="stat-label">Latest Port</div>
                <div class="stat-value" style="font-size: 20px;">
                    {{ $latestPort ? \Illuminate\Support\Str::limit($latestPort->port_name, 18) : '-' }}
                </div>
                <div class="stat-note">
                    {{ $latestPort ? $latestPort->created_at->diffForHumans() : 'No data' }}
                </div>
            </div>
        </div>
    </div>

    {{-- Sync API --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <h5 class="mb-3">Sync Ports from GeoNames API</h5>
            <p class="text-muted">
                Pilih negara untuk mengambil data pelabuhan dari GeoNames API. Data yang tersimpan akan digunakan pada Port Location Dashboard dan distance tracking.
            </p>

            <form method="POST" action="{{ route('admin.ports.syncApi') }}">
                @csrf

                <div class="row align-items-end">
                    <div class="col-md-8">
                        <label class="form-label">Pilih Negara</label>
                        <select name="country_id" class="form-select" required>
                            <option value="">-- Pilih Negara --</option>

                            @foreach($countries as $country)
                                <option value="{{ $country->id }}"
                                    {{ request('country_id') == $country->id ? 'selected' : '' }}>
                                    {{ $country->name }}
                                    @if($country->cca2)
                                        - {{ $country->cca2 }}
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-arrow-repeat me-1"></i>
                            Sync Ports API
                        </button>
                    </div>
                </div>
            </form>

            <small class="text-muted d-block mt-3">
                Catatan: beberapa negara tidak memiliki pelabuhan laut atau data port tidak tersedia dari API.
            </small>
        </div>
    </div>

    {{-- Filter --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <h5 class="mb-3">Filter Port List</h5>

            <form method="GET" action="{{ route('admin.ports.index') }}">
                <div class="row align-items-end">
                    <div class="col-md-5">
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

                    <div class="col-md-1">
                        <a href="{{ route('admin.ports.index') }}" class="btn btn-secondary w-100">
                            Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if(isset($selectedCountry) && $selectedCountry)
        <div class="alert alert-primary">
            Menampilkan data port untuk <strong>{{ $selectedCountry->name }}</strong>.
        </div>
    @endif

    {{-- Ports Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <h5 class="mb-1">
                        Port Dataset
                        @if(isset($selectedCountry) && $selectedCountry)
                            - {{ $selectedCountry->name }}
                        @else
                            - All Synced Countries
                        @endif
                    </h5>
                    <p class="text-muted mb-0">
                        Daftar pelabuhan yang tersimpan di database sebagai cache dari API.
                    </p>
                </div>

                <span class="badge bg-primary">
                    {{ $portCollection->count() }} Ports
                </span>
            </div>

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
                            <th>Harbor Size</th>
                            <th>Coordinate</th>
                            <th>Created</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($ports as $port)
                            <tr>
                                <td>{{ $loop->iteration }}</td>

                                <td style="min-width: 220px;">
                                    <strong>{{ $port->port_name }}</strong>
                                    <br>
                                    <small class="text-muted">
                                        ID: {{ $port->id }}
                                    </small>
                                </td>

                                <td>
                                    {{ $port->country_name ?? $port->country->name ?? '-' }}
                                </td>

                                <td>
                                    {{ $port->country_code ?? $port->country->cca2 ?? '-' }}
                                </td>

                                <td>
                                    {{ $port->region ?? '-' }}
                                </td>

                                <td>
                                    <span class="badge bg-primary">
                                        {{ $port->port_type ?? 'Port' }}
                                    </span>
                                </td>

                                <td>
                                    {{ $port->harbor_size ?? 'Unknown' }}
                                </td>

                                <td>
                                    <small>
                                        {{ $port->latitude ?? '-' }},
                                        {{ $port->longitude ?? '-' }}
                                    </small>
                                </td>

                                <td>
                                    {{ $port->created_at ? $port->created_at->diffForHumans() : '-' }}
                                </td>

                                <td>
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="{{ route('admin.ports.edit', $port->id) }}"
                                           class="btn btn-sm btn-outline-primary">
                                            Edit
                                        </a>

                                        <form method="POST"
                                              action="{{ route('admin.ports.destroy', $port->id) }}"
                                              onsubmit="return confirm('Hapus data pelabuhan ini?')">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted py-4">
                                    Belum ada data port. Pilih negara lalu klik Sync Ports API.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(method_exists($ports, 'links'))
                <div class="mt-3">
                    {{ $ports->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection