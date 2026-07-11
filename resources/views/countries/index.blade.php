@extends('layouts.dashboard')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3>Global Countries</h3>
            <p class="text-muted mb-0">
                Data negara yang digunakan untuk monitoring risiko rantai pasok global.
            </p>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>No</th>
                            <th>Flag</th>
                            <th>Country</th>
                            <th>Capital</th>
                            <th>Region</th>
                            <th>Currency</th>
                            <th>Population</th>
                            <th>Coordinate</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($countries as $country)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    @if($country->flag_url)
                                        <img src="{{ $country->flag_url }}" width="45" alt="{{ $country->name }}">
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    <strong>{{ $country->name }}</strong><br>
                                    <small class="text-muted">{{ $country->official_name }}</small>
                                </td>
                                <td>{{ $country->capital }}</td>
                                <td>
                                    {{ $country->region }}<br>
                                    <small class="text-muted">{{ $country->subregion }}</small>
                                </td>
                                <td>
                                    {{ $country->currency_code }}<br>
                                    <small class="text-muted">{{ $country->currency_name }}</small>
                                </td>
                                <td>{{ number_format($country->population) }}</td>
                                <td>
                                    {{ $country->latitude }}, {{ $country->longitude }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted">
                                    Belum ada data negara.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection