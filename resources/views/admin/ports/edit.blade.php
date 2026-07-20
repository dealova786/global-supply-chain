@extends('layouts.dashboard')

@section('content')
    @php
        $countries = isset($countries)
            ? $countries
            : \App\Models\Country::orderBy('name', 'asc')->get();
    @endphp

    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h1 class="page-title">Edit Port</h1>
            <p class="page-subtitle">
                Perbarui informasi pelabuhan, koordinat, dan data lokasi port.
            </p>
        </div>

        <a href="{{ route('admin.ports.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i>
            Back
        </a>
    </div>

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

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="mb-3">Edit Port Form</h5>

                    <form method="POST" action="{{ route('admin.ports.update', $port->id) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label">Pilih Negara</label>
                            <select name="country_id" class="form-select" required>
                                <option value="">-- Pilih Negara --</option>

                                @foreach($countries as $country)
                                    <option value="{{ $country->id }}"
                                        {{ old('country_id', $port->country_id) == $country->id ? 'selected' : '' }}>
                                        {{ $country->name }}
                                        @if($country->cca2)
                                            - {{ $country->cca2 }}
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Port Name</label>
                            <input type="text"
                                   name="port_name"
                                   class="form-control"
                                   value="{{ old('port_name', $port->port_name) }}"
                                   required>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Country Name</label>
                                <input type="text"
                                       name="country_name"
                                       class="form-control"
                                       value="{{ old('country_name', $port->country_name) }}">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Country Code</label>
                                <input type="text"
                                       name="country_code"
                                       class="form-control"
                                       value="{{ old('country_code', $port->country_code) }}">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Region</label>
                            <input type="text"
                                   name="region"
                                   class="form-control"
                                   value="{{ old('region', $port->region) }}">
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Latitude</label>
                                <input type="number"
                                       step="any"
                                       name="latitude"
                                       class="form-control"
                                       value="{{ old('latitude', $port->latitude) }}"
                                       required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Longitude</label>
                                <input type="number"
                                       step="any"
                                       name="longitude"
                                       class="form-control"
                                       value="{{ old('longitude', $port->longitude) }}"
                                       required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Port Type</label>
                                <input type="text"
                                       name="port_type"
                                       class="form-control"
                                       value="{{ old('port_type', $port->port_type) }}">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Harbor Size</label>
                                <input type="text"
                                       name="harbor_size"
                                       class="form-control"
                                       value="{{ old('harbor_size', $port->harbor_size) }}">
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="{{ route('admin.ports.index') }}" class="btn btn-secondary">
                                Cancel
                            </a>

                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i>
                                Update Port
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="stat-icon primary mb-3">
                        <i class="bi bi-geo-alt"></i>
                    </div>

                    <h5 class="mb-1">{{ $port->port_name }}</h5>
                    <p class="text-muted mb-3">
                        {{ $port->country_name ?? $port->country->name ?? '-' }}
                    </p>

                    <div class="mb-3">
                        <span class="badge bg-primary">
                            {{ $port->port_type ?? 'Port' }}
                        </span>

                        <span class="badge bg-secondary">
                            {{ $port->harbor_size ?? 'Unknown size' }}
                        </span>
                    </div>

                    <div class="small text-muted">
                        <div class="mb-2">
                            <strong>Coordinate:</strong><br>
                            {{ $port->latitude ?? '-' }},
                            {{ $port->longitude ?? '-' }}
                        </div>

                        <div class="mb-2">
                            <strong>Region:</strong><br>
                            {{ $port->region ?? '-' }}
                        </div>

                        <div class="mb-2">
                            <strong>Created:</strong><br>
                            {{ $port->created_at ? $port->created_at->format('d M Y H:i') : '-' }}
                        </div>

                        <div>
                            <strong>Last Updated:</strong><br>
                            {{ $port->updated_at ? $port->updated_at->format('d M Y H:i') : '-' }}
                        </div>
                    </div>

                    <div class="alert alert-primary mt-4 mb-0">
                        Perubahan koordinat akan memengaruhi posisi marker pada Port Location Dashboard.
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection