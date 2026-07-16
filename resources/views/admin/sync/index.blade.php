@extends('layouts.dashboard')

@section('content')
    <div class="mb-4">
        <h3>API Sync Center</h3>
        <p class="text-muted">
            Halaman ini digunakan admin untuk memantau sumber API, jumlah data cache, dan endpoint internal sistem.
        </p>
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

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6>Countries</h6>
                    <h3>{{ $totalCountries }}</h3>
                    <small class="text-muted">REST Countries API</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6>Weather Cache</h6>
                    <h3>{{ $totalWeather }}</h3>
                    <small class="text-muted">Open-Meteo API</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6>Economic Data</h6>
                    <h3>{{ $totalEconomy }}</h3>
                    <small class="text-muted">World Bank API</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6>Currency Rates</h6>
                    <h3>{{ $totalCurrency }}</h3>
                    <small class="text-muted">Exchange Rate API</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6>News Cache</h6>
                    <h3>{{ $totalNews }}</h3>
                    <small class="text-muted">GNews API</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6>Ports</h6>
                    <h3>{{ $totalPorts }}</h3>
                    <small class="text-muted">Port dataset / database</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6>Risk Scores</h6>
                    <h3>{{ $totalRiskScores }}</h3>
                    <small class="text-muted">Internal risk engine</small>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <h5>Sync Global Countries</h5>
            <p class="text-muted">
                Tombol ini mengambil data negara global dari REST Countries API dan menyimpannya ke tabel countries.
                Data cuaca, ekonomi, kurs, dan berita tidak disinkronkan massal agar sistem tidak timeout.
            </p>

            <form method="POST" action="{{ route('admin.sync.countries') }}"
                  onsubmit="return confirm('Mulai sync data negara global dari REST Countries API?')">
                @csrf

                <button type="submit" class="btn btn-primary">
                    Sync Countries from API
                </button>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <h5>External API Sources</h5>

            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Data</th>
                            <th>External API</th>
                            <th>Method</th>
                            <th>System Strategy</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Countries</td>
                            <td>REST Countries API</td>
                            <td>Admin Sync</td>
                            <td>Data master negara disimpan ke database agar dropdown global tidak perlu load API terus.</td>
                        </tr>
                        <tr>
                            <td>Weather</td>
                            <td>Open-Meteo API</td>
                            <td>On-demand</td>
                            <td>Dipanggil saat user memilih negara, lalu disimpan ke weather cache.</td>
                        </tr>
                        <tr>
                            <td>Economy</td>
                            <td>World Bank API</td>
                            <td>On-demand</td>
                            <td>GDP, inflasi, populasi, ekspor, dan impor diambil per negara.</td>
                        </tr>
                        <tr>
                            <td>Currency</td>
                            <td>Exchange Rate API</td>
                            <td>On-demand</td>
                            <td>Kurs mata uang negara terhadap USD diambil saat analisis negara.</td>
                        </tr>
                        <tr>
                            <td>News</td>
                            <td>GNews API</td>
                            <td>On-demand + cache</td>
                            <td>Berita dicari berdasarkan negara, ekonomi, perdagangan, logistik, dan supply chain.</td>
                        </tr>
                        <tr>
                            <td>Risk Score</td>
                            <td>Internal API / Service</td>
                            <td>Calculated</td>
                            <td>Risk score dihitung dari weather risk, inflation risk, currency risk, dan news sentiment.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <h5>Internal API Endpoints</h5>
            <p class="text-muted">
                Endpoint berikut dapat digunakan untuk pengujian API internal Laravel.
            </p>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Endpoint</th>
                            <th>Function</th>
                            <th>Test Link</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>GET /api/countries</code></td>
                            <td>Menampilkan daftar negara global</td>
                            <td><a href="/api/countries" target="_blank">Open</a></td>
                        </tr>

                        <tr>
                            <td><code>GET /api/weather?country_id=1</code></td>
                            <td>Menampilkan data cuaca negara</td>
                            <td><a href="/api/weather?country_id=1" target="_blank">Open</a></td>
                        </tr>

                        <tr>
                            <td><code>GET /api/economy?country_id=1</code></td>
                            <td>Menampilkan data ekonomi negara</td>
                            <td><a href="/api/economy?country_id=1" target="_blank">Open</a></td>
                        </tr>

                        <tr>
                            <td><code>GET /api/currency?country_id=1</code></td>
                            <td>Menampilkan data kurs mata uang negara</td>
                            <td><a href="/api/currency?country_id=1" target="_blank">Open</a></td>
                        </tr>

                        <tr>
                            <td><code>GET /api/news?country_id=1</code></td>
                            <td>Menampilkan berita dan sentiment analysis</td>
                            <td><a href="/api/news?country_id=1" target="_blank">Open</a></td>
                        </tr>

                        <tr>
                            <td><code>GET /api/risk?country_id=1</code></td>
                            <td>Menampilkan hasil risk scoring</td>
                            <td><a href="/api/risk?country_id=1" target="_blank">Open</a></td>
                        </tr>

                        <tr>
                            <td><code>GET /api/ports</code></td>
                            <td>Menampilkan data pelabuhan</td>
                            <td><a href="/api/ports" target="_blank">Open</a></td>
                        </tr>

                        <tr>
                            <td><code>GET /api/compare?country_a=1&country_b=2</code></td>
                            <td>Membandingkan dua negara</td>
                            <td><a href="/api/compare?country_a=1&country_b=2" target="_blank">Open</a></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="alert alert-info mt-3 mb-0">
                Sistem menggunakan kombinasi external API dan internal API. External API digunakan sebagai sumber data,
                sedangkan internal API digunakan untuk menyajikan data yang sudah diproses oleh Laravel.
            </div>
        </div>
    </div>
@endsection