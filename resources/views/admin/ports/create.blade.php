@extends('layouts.dashboard')

@section('content')
    @php
        $countries = isset($countries)
            ? $countries
            : \App\Models\Country::orderBy('name', 'asc')->get();
    @endphp

    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h1 class="page-title">Add New Port</h1>
            <p class="page-subtitle">
                Tambahkan data pelabuhan secara manual apabila data tidak tersedia dari API.
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
                    <h5 class="mb-3">Port Form</h5>

                    <form method="POST" action="{{ route('admin.ports.store') }}">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Pilih Negara</label>
                            <select name="country_id" class="form-select" required>
                                <option value="">-- Pilih Negara --</option>

                                @foreach($countries as $country)
                                    <option value="{{ $country->id }}"
                                        {{ old('country_id') == $country->id ? 'selected' : '' }}>
                                        {{ $country->name }}
                                        @if($country->cca2)
                                            - {{ $country->cca2 }}
                                        @endif
                                    </option>
                                @endforeach
                            </select>

                            <small class="text-muted">
                                Country dipakai untuk menghubungkan port dengan dashboard risiko negara.
                            </small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Port Name</label>
                            <input type="text"
                                   name="port_name"
                                   class="form-control"
                                   value="{{ old('port_name') }}"
                                   placeholder="Contoh: Port of Shanghai"
                                   required>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Country Name</label>
                                <input type="text"
                                       name="country_name"
                                       class="form-control"
                                       value="{{ old('country_name') }}"
                                       placeholder="Contoh: China">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Country Code</label>
                                <input type="text"
                                       name="country_code"
                                       class="form-control"
                                       value="{{ old('country_code') }}"
                                       placeholder="Contoh: CN">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Region</label>
                            <input type="text"
                                   name="region"
                                   class="form-control"
                                   value="{{ old('region') }}"
                                   placeholder="Contoh: Asia">
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Latitude</label>
                                <input type="number"
                                       step="any"
                                       name="latitude"
                                       class="form-control"
                                       value="{{ old('latitude') }}"
                                       placeholder="Contoh: 31.2304"
                                       required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Longitude</label>
                                <input type="number"
                                       step="any"
                                       name="longitude"
                                       class="form-control"
                                       value="{{ old('longitude') }}"
                                       placeholder="Contoh: 121.4737"
                                       required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Port Type</label>
                                <input type="text"
                                       name="port_type"
                                       class="form-control"
                                       value="{{ old('port_type', 'Seaport') }}"
                                       placeholder="Contoh: Seaport">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Harbor Size</label>
                                <input type="text"
                                       name="harbor_size"
                                       class="form-control"
                                       value="{{ old('harbor_size') }}"
                                       placeholder="Contoh: Large, Medium, Small">
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="{{ route('admin.ports.index') }}" class="btn btn-secondary">
                                Cancel
                            </a>

                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i>
                                Save Port
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
                        <i class="bi bi-pin-map"></i>
                    </div>

                    <h5>Port Information</h5>

                    <div class="alert alert-primary mt-3">
                        <strong>Fungsi Data Port</strong><br>
                        Data port digunakan untuk visualisasi peta pelabuhan, tracking risiko port, dan estimasi jarak antar pelabuhan.
                    </div>

                    <div class="alert alert-success mb-0">
                        <strong>Saran</strong><br>
                        Utamakan sinkronisasi dari GeoNames API. Tambah manual hanya jika data port tidak tersedia dari API.
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection