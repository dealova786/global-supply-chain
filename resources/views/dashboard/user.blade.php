@extends('layouts.dashboard')

@section('content')
    <h3 class="mb-4">User Dashboard</h3>

    <div class="alert alert-success">
        Selamat datang, <strong>{{ auth()->user()->name }}</strong>. 
        Anda login sebagai <strong>User / Analyst</strong>.
    </div>

    <div class="row g-3">
        <div class="col-md-3">
            <div class="card card-stat shadow-sm">
                <div class="card-body">
                    <h6>Monitored Countries</h6>
                    <h3>0</h3>
                    <small>Negara dalam watchlist</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card card-stat shadow-sm">
                <div class="card-body">
                    <h6>Highest Risk</h6>
                    <h3>-</h3>
                    <small>Negara dengan risiko tertinggi</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card card-stat shadow-sm">
                <div class="card-body">
                    <h6>Latest News</h6>
                    <h3>0</h3>
                    <small>Berita ekonomi dan logistik</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card card-stat shadow-sm">
                <div class="card-body">
                    <h6>Risk Score</h6>
                    <h3>-</h3>
                    <small>Skor risiko rantai pasok</small>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-4 shadow-sm">
        <div class="card-body">
            <h5>Country Risk Monitoring</h5>
            <p>
                Dashboard ini digunakan untuk memantau risiko rantai pasok global
                berdasarkan cuaca, ekonomi, kurs mata uang, berita, dan data pelabuhan.
            </p>
        </div>
    </div>
@endsection