@extends('layouts.dashboard')

@section('content')
    <div class="mb-4">
        <h3>Currency Impact Dashboard</h3>
        <p class="text-muted">
            Halaman ini menampilkan nilai tukar mata uang negara dan dampaknya terhadap risiko rantai pasok.
        </p>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('currency.index') }}">
                <div class="row align-items-end">
                    <div class="col-md-8">
                        <label class="form-label">Pilih Negara</label>
                        <select name="country_id" class="form-select" required>
                            <option value="">-- Pilih Negara --</option>
                            @foreach($countries as $country)
                                <option value="{{ $country->id }}"
                                    {{ request('country_id') == $country->id ? 'selected' : '' }}>
                                    {{ $country->name }} - {{ $country->currency_code }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary w-100">
                            Analyze Currency
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if($selectedCountry && $latestCurrency)
        <div class="alert alert-info">
            Menampilkan data kurs untuk negara:
            <strong>{{ $selectedCountry->name }}</strong>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h6>Base Currency</h6>
                        <h3>{{ $latestCurrency->base_currency }}</h3>
                        <small>{{ $selectedCountry->currency_name }}</small>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h6>Exchange Rate</h6>
                        <h3>{{ number_format($latestCurrency->exchange_rate, 6) }}</h3>
                        <small>
                            1 {{ $latestCurrency->base_currency }} =
                            {{ number_format($latestCurrency->exchange_rate, 6) }}
                            {{ $latestCurrency->target_currency }}
                        </small>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h6>Currency Risk</h6>
                        <h3>{{ $latestCurrency->currency_risk }}</h3>

                        @if($latestCurrency->currency_risk <= 30)
                            <span class="badge bg-success">Low Risk</span>
                        @elseif($latestCurrency->currency_risk <= 60)
                            <span class="badge bg-warning text-dark">Medium Risk</span>
                        @else
                            <span class="badge bg-danger">High Risk</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <h5 class="mb-3">Currency Trend Chart</h5>
                <canvas id="currencyTrendChart" height="100"></canvas>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h5 class="mb-3">Currency Rate History</h5>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>No</th>
                                <th>Base</th>
                                <th>Target</th>
                                <th>Exchange Rate</th>
                                <th>Currency Risk</th>
                                <th>Rate Date</th>
                                <th>Created At</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($currencyRates as $rate)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $rate->base_currency }}</td>
                                    <td>{{ $rate->target_currency }}</td>
                                    <td>{{ number_format($rate->exchange_rate, 6) }}</td>
                                    <td>
                                        @if($rate->currency_risk <= 30)
                                            <span class="badge bg-success">{{ $rate->currency_risk }} Low</span>
                                        @elseif($rate->currency_risk <= 60)
                                            <span class="badge bg-warning text-dark">{{ $rate->currency_risk }} Medium</span>
                                        @else
                                            <span class="badge bg-danger">{{ $rate->currency_risk }} High</span>
                                        @endif
                                    </td>
                                    <td>{{ $rate->rate_date }}</td>
                                    <td>{{ $rate->created_at->format('d M Y H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    @elseif($selectedCountry)
        <div class="alert alert-warning">
            Data kurs untuk negara ini belum tersedia.
        </div>
    @else
        <div class="alert alert-secondary">
            Silakan pilih negara untuk melihat data kurs mata uang.
        </div>
    @endif
@endsection

@section('scripts')
@if($selectedCountry && $currencyRates->count() > 0)
<script>
    const currencyLabels = @json($chartLabels);
    const currencyData = @json($chartData);

    const ctx = document.getElementById('currencyTrendChart');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: currencyLabels,
            datasets: [{
                label: 'Exchange Rate to USD',
                data: currencyData,
                tension: 0.3,
                fill: false,
                borderWidth: 3,
                pointRadius: 5,
                pointHoverRadius: 7
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: true
                }
            },
            scales: {
                y: {
                    beginAtZero: false
                }
            }
        }
    });
</script>
@endif
@endsection