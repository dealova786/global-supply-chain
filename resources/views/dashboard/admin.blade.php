@extends('layouts.dashboard')

@section('content')
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h3>Admin Dashboard</h3>
            <p class="text-muted mb-0">
                Ringkasan data sistem dan kontrol sinkronisasi API.
            </p>
        </div>

        <form method="POST" action="{{ route('admin.sync.countries') }}">
            @csrf
            <button type="submit" class="btn btn-primary">
                Sync Countries from API
            </button>
        </form>
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

    <div class="alert alert-primary">
        Selamat datang, <strong>{{ auth()->user()->name }}</strong>.
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6>Total Users</h6>
                    <h3>{{ $totalUsers }}</h3>
                    <small class="text-muted">Pengguna terdaftar dalam sistem</small>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6>Total Countries</h6>
                    <h3>{{ $totalCountries }}</h3>
                    <small class="text-muted">Negara global dari REST Countries API</small>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6>Total Ports</h6>
                    <h3>{{ $totalPorts }}</h3>
                    <small class="text-muted">Data pelabuhan dari GeoNames API</small>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6>Total Articles</h6>
                    <h3>{{ $totalArticles }}</h3>
                    <small class="text-muted">Artikel analisis dikelola admin</small>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6>News Cache</h6>
                    <h3>{{ $totalNews }}</h3>
                    <small class="text-muted">Berita yang tersimpan dari GNews API</small>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6>Risk Reports</h6>
                    <h3>{{ $totalRiskReports }}</h3>
                    <small class="text-muted">Riwayat hasil perhitungan risk score</small>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <h5>System Overview</h5>
            <p class="text-muted mb-0">
                Admin dapat mengelola data pengguna, artikel, pelabuhan, sentiment words,
                serta melakukan sinkronisasi data negara dari API untuk mendukung monitoring risiko rantai pasok global.
            </p>
        </div>
    </div>
@endsection