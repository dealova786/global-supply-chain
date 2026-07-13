<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Global Supply Chain Risk Platform</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />

    <style>
        body {
            background-color: #f5f7fb;
        }

        .sidebar {
            min-height: 100vh;
            background-color: #1f2937;
        }

        .sidebar a {
            color: #d1d5db;
            text-decoration: none;
            display: block;
            padding: 12px 20px;
        }

        .sidebar a:hover {
            background-color: #374151;
            color: #ffffff;
        }

        .content {
            padding: 25px;
        }

        .card-stat {
            border: none;
            border-radius: 12px;
        }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">

        <!-- Sidebar -->
        <div class="col-md-2 sidebar p-0">
            <div class="p-3 text-white fw-bold border-bottom">
                Supply Chain Risk
            </div>

            <a href="{{ route('dashboard') }}">Dashboard</a>
            <a href="{{ route('country.dashboard') }}">Country Dashboard</a>
            <a href="{{ route('countries.index') }}">Countries</a>
            <a href="{{ route('risk.index') }}">Risk Score</a>
            <a href="{{ route('weather.map') }}">Weather Map</a>
            <a href="{{ route('currency.index') }}">Currency</a>
            <a href="{{ route('news.index') }}">News Intelligence</a>
            <a href="{{ route('ports.index') }}">Ports</a>
            <a href="{{ route('comparison.index') }}">Compare Countries</a>
            <a href="{{ route('watchlists.index') }}">Watchlist</a>

            @if(auth()->user()->role === 'admin')
                <hr class="text-white">
                <a href="{{ route('admin.users.index') }}">Admin Users</a>
                <a href="#">Manage Ports</a>
                <a href="#">Articles</a>
                <a href="#">Sentiment Words</a>
            @endif

            <hr class="text-white">

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="btn btn-danger btn-sm ms-3" type="submit">
                    Logout
                </button>
            </form>
        </div>

        <!-- Content -->
        <div class="col-md-10 content">
            @yield('content')
        </div>

    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

@yield('scripts')

</body>
</html>