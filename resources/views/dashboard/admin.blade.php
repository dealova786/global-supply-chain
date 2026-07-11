@extends('layouts.dashboard')

@section('content')
    <h3 class="mb-4">Admin Dashboard</h3>

    <div class="alert alert-primary">
        Selamat datang, <strong>{{ auth()->user()->name }}</strong>. 
        Anda login sebagai <strong>Admin</strong>.
    </div>

    <div class="row g-3">
        <div class="col-md-3">
            <div class="card card-stat shadow-sm">
                <div class="card-body">
                    <h6>Total Users</h6>
                    <h3>0</h3>
                    <small>Data pengguna sistem</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card card-stat shadow-sm">
                <div class="card-body">
                    <h6>Total Countries</h6>
                    <h3>0</h3>
                    <small>Negara yang dimonitor</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card card-stat shadow-sm">
                <div class="card-body">
                    <h6>Total Ports</h6>
                    <h3>0</h3>
                    <small>Dataset pelabuhan</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card card-stat shadow-sm">
                <div class="card-body">
                    <h6>Risk Reports</h6>
                    <h3>0</h3>
                    <small>Hasil analisis risiko</small>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-4 shadow-sm">
        <div class="card-body">
            <h5>Global Supply Chain Risk Intelligence Platform</h5>
            <p>
                Dashboard ini digunakan admin untuk mengelola data user, negara,
                pelabuhan, artikel analisis, serta kamus sentimen berita.
            </p>
        </div>
    </div>
@endsection