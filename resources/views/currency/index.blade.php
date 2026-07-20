@extends('layouts.dashboard')

@section('content')
    <div class="mb-4">
        <h1 class="page-title">Currency Risk Dashboard</h1>
        <p class="page-subtitle">
            Pantau perubahan nilai tukar dan risiko kurs berdasarkan data dari Currency API yang tersimpan di cache sistem.
        </p>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ url('/currency-dashboard') }}">
                <div class="row align-items-end">
                    <div class="col-md-7">
                        <label class="form-label">Pilih Negara</label>
                        <select name="country_id" class="form-select">
                            <option value="">-- Semua Negara --</option>

                            @foreach($countries as $country)
                                <option value="{{ $country->id }}"
                                    {{ request('country_id') == $country->id ? 'selected' : '' }}>
                                    {{ $country->name }}
                                    @if($country->currency_code)
                                        - {{ $country->currency_code }}
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            Analyze
                        </button>
                    </div>

                    <div class="col-md-2">
                        @if($selectedCountry)
                            <a href="{{ url('/currency-dashboard?country_id=' . $selectedCountry->id . '&sync=1') }}"
                               class="btn btn-success w-100">
                                Update API
                            </a>
                        @else
                            <button type="button" class="btn btn-success w-100" disabled>
                                Update API
                            </button>
                        @endif
                    </div>

                    <div class="col-md-1">
                        <a href="{{ url('/currency-dashboard') }}" class="btn btn-secondary w-100">
                            Reset
                        </a>
                    </div>
                </div>
            </form>

            <small class="text-muted d-block mt-3">
                Data source: Currency API cache. Tombol Update API hanya aktif setelah memilih negara agar request API tidak boros.
            </small>
        </div>
    </div>

    @if($message)
        <div class="alert alert-info">
            {{ $message }}
        </div>
    @endif

    @if($selectedCountry)
        <div class="alert alert-primary">
            Menampilkan currency risk untuk <strong>{{ $selectedCountry->name }}</strong>
            @if($selectedCountry->currency_code)
                dengan mata uang <strong>{{ $selectedCountry->currency_code }}</strong>.
            @endif
        </div>
    @else
        <div class="alert alert-secondary">
            Menampilkan ringkasan currency risk terbaru dari semua negara yang sudah memiliki data kurs.
        </div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon primary">
                    <i class="bi bi-currency-exchange"></i>
                </div>
                <div class="stat-label">Average Currency Risk</div>
                <div class="stat-value">{{ $averageCurrencyRisk }}</div>
                <div class="stat-note">Rata-rata dari data tampil</div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon success">
                    <i class="bi bi-graph-down-arrow"></i>
                </div>
                <div class="stat-label">Low Risk</div>
                <div class="stat-value">{{ $lowRiskCount }}</div>
                <div class="stat-note">Fluktuasi rendah</div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon warning">
                    <i class="bi bi-activity"></i>
                </div>
                <div class="stat-label">Medium Risk</div>
                <div class="stat-value">{{ $mediumRiskCount }}</div>
                <div class="stat-note">Perlu dipantau</div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon danger">
                    <i class="bi bi-graph-up-arrow"></i>
                </div>
                <div class="stat-label">High Risk</div>
                <div class="stat-value">{{ $highRiskCount }}</div>
                <div class="stat-note">Volatilitas tinggi</div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <div class="stock-chart-card h-100">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <div class="stock-chart-title">
                            @if($selectedCountry)
                                Exchange Rate Movement
                            @else
                                Currency Risk Movement
                            @endif
                        </div>

                        <div class="stock-chart-subtitle">
                            @if($selectedCountry)
                                Grafik pergerakan nilai tukar {{ $selectedCountry->currency_code ?? '-' }} terhadap USD.
                            @else
                                Grafik ringkasan currency risk dari data terbaru beberapa negara.
                            @endif
                        </div>
                    </div>

                    <span class="badge bg-primary">Stock Style</span>
                </div>

                <div class="chart-box">
                    <canvas id="currencyChart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="mb-3">Latest Currency Data</h5>

                    @if($latestCurrency)
                        <div class="mb-3">
                            <span class="text-muted">Base Currency</span>
                            <h4 class="mb-0">{{ $latestCurrency->base_currency }}</h4>
                        </div>

                        <div class="mb-3">
                            <span class="text-muted">Target Currency</span>
                            <h4 class="mb-0">{{ $latestCurrency->target_currency }}</h4>
                        </div>

                        <div class="mb-3">
                            <span class="text-muted">Exchange Rate</span>
                            <h4 class="mb-0">{{ number_format($latestCurrency->exchange_rate, 6) }}</h4>
                        </div>

                        <div class="mb-4">
                            <div class="d-flex justify-content-between mb-1">
                                <span>Currency Risk</span>
                                <strong>{{ round($latestCurrency->currency_risk) }}</strong>
                            </div>

                            <div class="progress" style="height: 9px;">
                                <div class="progress-bar
                                    @if($latestCurrency->currency_risk > 60)
                                        bg-danger
                                    @elseif($latestCurrency->currency_risk > 30)
                                        bg-warning
                                    @else
                                        bg-success
                                    @endif"
                                    style="width: {{ min(round($latestCurrency->currency_risk), 100) }}%">
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-primary mb-0">
                            <strong>Last Updated:</strong><br>
                            {{ \Carbon\Carbon::parse($latestCurrency->recorded_at ?? $latestCurrency->created_at)->diffForHumans() }}
                        </div>
                    @else
                        <div class="alert alert-secondary mb-0">
                            Belum ada data currency. Pilih negara lalu klik <strong>Update API</strong>.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <h5 class="mb-3">Latest Currency Records</h5>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Country</th>
                            <th>Base</th>
                            <th>Target</th>
                            <th>Exchange Rate</th>
                            <th>Currency Risk</th>
                            <th>Status</th>
                            <th>Last Updated</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($currencyRows as $currency)
                            <tr>
                                <td>{{ $loop->iteration }}</td>

                                <td>
                                    <strong>{{ $currency->country->name ?? $selectedCountry->name ?? '-' }}</strong>
                                </td>

                                <td>{{ $currency->base_currency }}</td>

                                <td>{{ $currency->target_currency }}</td>

                                <td>{{ number_format($currency->exchange_rate, 6) }}</td>

                                <td>
                                    <strong>{{ round($currency->currency_risk) }}</strong>
                                </td>

                                <td>
                                    @if($currency->currency_risk > 60)
                                        <span class="badge bg-danger">High Risk</span>
                                    @elseif($currency->currency_risk > 30)
                                        <span class="badge bg-warning text-dark">Medium Risk</span>
                                    @else
                                        <span class="badge bg-success">Low Risk</span>
                                    @endif
                                </td>

                                <td>
                                    {{ \Carbon\Carbon::parse($currency->recorded_at ?? $currency->created_at)->diffForHumans() }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted">
                                    Belum ada data currency. Pilih negara lalu klik Update API untuk mengambil data dari Currency API.
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
        const currencyLabels = @json($chartLabels->count() ? $chartLabels : ['No Data']);
        const currencyValues = @json($chartValues->count() ? $chartValues : [0]);

        renderStockLineChart(
            'currencyChart',
            currencyLabels,
            currencyValues,
            '{{ $selectedCountry ? "Exchange Rate" : "Currency Risk" }}'
        );
    </script>
@endsection