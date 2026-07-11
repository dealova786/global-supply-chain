@extends('layouts.dashboard')

@section('content')
    <div class="mb-4">
        <h3>Global Country Dashboard</h3>
        <p class="text-muted">
            Pilih negara untuk melihat data awal monitoring risiko rantai pasok global.
        </p>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('country.dashboard') }}">
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

                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary w-100">
                            Analyze Country
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if($selectedCountry)
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h6>Country</h6>
                        <h4>{{ $selectedCountry->name }}</h4>
                        <small>{{ $selectedCountry->official_name }}</small>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h6>Capital</h6>
                        <h4>{{ $selectedCountry->capital }}</h4>
                        <small>{{ $selectedCountry->region }}</small>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h6>Currency</h6>
                        <h4>{{ $selectedCountry->currency_code }}</h4>
                        <small>{{ $selectedCountry->currency_name }}</small>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h6>Population</h6>
                        <h4>{{ number_format($selectedCountry->population) }}</h4>
                        <small>Total population</small>
                    </div>
                </div>
            </div>
        </div>

    @if($weatherData)
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6>Temperature</h6>
                    <h4>{{ $weatherData['temperature'] }} °C</h4>
                    <small>Current temperature</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6>Rainfall</h6>
                    <h4>{{ $weatherData['rainfall'] }} mm</h4>
                    <small>Current precipitation</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6>Wind Speed</h6>
                    <h4>{{ $weatherData['wind_speed'] }} km/h</h4>
                    <small>Current wind speed</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6>Weather Risk</h6>
                    <h4>
                        {{ $weatherData['weather_risk'] }}
                    </h4>
                    <small>{{ $weatherData['weather_condition'] }}</small>
                </div>
            </div>
        </div>
    </div>
@endif
        
        <div class="row g-3">
            <div class="col-md-5">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h5>Country Information</h5>

                        @if($selectedCountry->flag_url)
                            <img src="{{ $selectedCountry->flag_url }}" width="120" class="mb-3">
                        @endif

                        <table class="table table-bordered">
                            <tr>
                                <th>Official Name</th>
                                <td>{{ $selectedCountry->official_name }}</td>
                            </tr>
                            <tr>
                                <th>Code</th>
                                <td>{{ $selectedCountry->cca2 }} / {{ $selectedCountry->cca3 }}</td>
                            </tr>
                            <tr>
                                <th>Region</th>
                                <td>{{ $selectedCountry->region }}</td>
                            </tr>
                            <tr>
                                <th>Subregion</th>
                                <td>{{ $selectedCountry->subregion }}</td>
                            </tr>
                            <tr>
                                <th>Language</th>
                                <td>{{ $selectedCountry->language }}</td>
                            </tr>
                            <tr>
                                <th>Coordinate</th>
                                <td>{{ $selectedCountry->latitude }}, {{ $selectedCountry->longitude }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-md-7">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h5>Country Location Map</h5>
                        <div id="countryMap" style="height: 400px; border-radius: 10px;"></div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="alert alert-info">
            Silakan pilih negara terlebih dahulu untuk menampilkan data monitoring.
        </div>
    @endif
@endsection

@section('scripts')
@if($selectedCountry)
<script>
    const latitude = {{ $selectedCountry->latitude }};
    const longitude = {{ $selectedCountry->longitude }};

    const map = L.map('countryMap').setView([latitude, longitude], 5);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    L.marker([latitude, longitude])
        .addTo(map)
        .bindPopup(`
            <strong>{{ $selectedCountry->name }}</strong><br>
            Capital: {{ $selectedCountry->capital }}<br>
            Currency: {{ $selectedCountry->currency_code }}
        `)
        .openPopup();
</script>

@endif
@endsection