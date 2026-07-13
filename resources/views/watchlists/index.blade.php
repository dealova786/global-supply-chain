@extends('layouts.dashboard')

@section('content')
    <div class="mb-4">
        <h3>Favorite Monitoring List</h3>
        <p class="text-muted">
            Halaman ini digunakan untuk menyimpan negara yang sering dipantau dalam monitoring risiko rantai pasok global.
        </p>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <h5 class="mb-3">Tambah Negara ke Watchlist</h5>

            <form method="POST" action="{{ route('watchlists.store') }}">
                @csrf

                <div class="row align-items-end">
                    <div class="col-md-8">
                        <label class="form-label">Pilih Negara</label>
                        <select name="country_id" class="form-select" required>
                            <option value="">-- Pilih Negara --</option>
                            @foreach($countries as $country)
                                <option value="{{ $country->id }}">
                                    {{ $country->name }} - {{ $country->currency_code }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary w-100">
                            Add to Watchlist
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6>Total Watchlist</h6>
                    <h3>{{ $watchlists->count() }}</h3>
                    <small>Negara yang sedang dipantau</small>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <h5 class="mb-3">Daftar Negara Dipantau</h5>

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
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($watchlists as $watchlist)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    @if($watchlist->country?->flag_url)
                                        <img src="{{ $watchlist->country->flag_url }}" width="45" alt="{{ $watchlist->country->name }}">
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    <strong>{{ $watchlist->country?->name }}</strong><br>
                                    <small class="text-muted">
                                        {{ $watchlist->country?->official_name }}
                                    </small>
                                </td>
                                <td>{{ $watchlist->country?->capital }}</td>
                                <td>
                                    {{ $watchlist->country?->region }}<br>
                                    <small class="text-muted">{{ $watchlist->country?->subregion }}</small>
                                </td>
                                <td>
                                    {{ $watchlist->country?->currency_code }}<br>
                                    <small class="text-muted">{{ $watchlist->country?->currency_name }}</small>
                                </td>
                                <td>
                                    <a href="{{ route('country.dashboard', ['country_id' => $watchlist->country_id]) }}"
                                       class="btn btn-sm btn-outline-primary">
                                        Analyze
                                    </a>

                                    <form method="POST"
                                          action="{{ route('watchlists.destroy', $watchlist->id) }}"
                                          class="d-inline"
                                          onsubmit="return confirm('Hapus negara ini dari watchlist?')">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            Remove
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">
                                    Belum ada negara di watchlist.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
@endsection