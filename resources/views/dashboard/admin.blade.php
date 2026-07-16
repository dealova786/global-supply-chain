@extends('layouts.dashboard')

@section('content')
    <div class="mb-4">
        <h3>Admin Control Center</h3>
        <p class="text-muted">
            Dashboard ini digunakan admin untuk mengelola sistem, data API, user, artikel, dan data pendukung monitoring risiko rantai pasok global.
        </p>
    </div>

    <div class="alert alert-primary">
        Selamat datang, <strong>{{ auth()->user()->name }}</strong>. 
        Anda login sebagai <strong>Administrator</strong>.
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
                    <small class="text-muted">Data pelabuhan dalam database</small>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6>Total Articles</h6>
                    <h3>{{ $totalArticles }}</h3>
                    <small class="text-muted">Artikel analisis yang dikelola admin</small>
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
                Admin bertugas mengelola data master, melakukan sinkronisasi API, mengelola user, serta memastikan data pendukung seperti artikel, pelabuhan, dan sentiment words tersedia. 
                Sementara user/analyst menggunakan sistem untuk memantau risiko rantai pasok berdasarkan negara yang dipilih.
            </p>
        </div>
    </div>
@endsection