@extends('layouts.dashboard')

@section('content')
    <div class="mb-4">
        <h3>Add Port</h3>
        <p class="text-muted">
            Tambahkan data pelabuhan baru ke sistem.
        </p>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.ports.store') }}">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Country</label>
                    <select name="country_id" class="form-select">
                        <option value="">-- Pilih Negara --</option>
                        @foreach($countries as $country)
                            <option value="{{ $country->id }}" {{ old('country_id') == $country->id ? 'selected' : '' }}>
                                {{ $country->name }} - {{ $country->cca2 }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Port Name</label>
                    <input type="text" name="port_name" class="form-control"
                           value="{{ old('port_name') }}"
                           placeholder="Contoh: Port of Shanghai" required>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Latitude</label>
                        <input type="text" name="latitude" class="form-control"
                               value="{{ old('latitude') }}"
                               placeholder="Contoh: 31.2304" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Longitude</label>
                        <input type="text" name="longitude" class="form-control"
                               value="{{ old('longitude') }}"
                               placeholder="Contoh: 121.4737" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Region</label>
                    <input type="text" name="region" class="form-control"
                           value="{{ old('region') }}"
                           placeholder="Contoh: Asia">
                </div>

                <div class="mb-3">
                    <label class="form-label">Harbor Size</label>
                    <input type="text" name="harbor_size" class="form-control"
                           value="{{ old('harbor_size') }}"
                           placeholder="Contoh: Large / Medium / Small">
                </div>

                <div class="mb-3">
                    <label class="form-label">Port Type</label>
                    <input type="text" name="port_type" class="form-control"
                           value="{{ old('port_type') }}"
                           placeholder="Contoh: Seaport">
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        Save Port
                    </button>

                    <a href="{{ route('admin.ports.index') }}" class="btn btn-secondary">
                        Back
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection