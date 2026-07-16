<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Global Supply Chain Risk Platform</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- Bootstrap --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- Leaflet --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />

    <style>
        body {
            background-color: #f4f6f9;
            margin: 0;
            padding: 0;
        }

        .sidebar {
            width: 255px;
            height: 100vh;
            background-color: #1f2937;
            color: white;
            position: fixed;
            top: 0;
            left: 0;
            overflow-y: auto;
            z-index: 1000;
        }

        .sidebar h5 {
            padding: 20px;
            margin: 0;
            font-weight: bold;
        }

        .sidebar a {
            display: block;
            color: #e5e7eb;
            text-decoration: none;
            padding: 12px 20px;
            font-size: 15px;
        }

        .sidebar a:hover {
            background-color: #374151;
            color: white;
        }

        .sidebar .section-title {
            color: #9ca3af;
            font-size: 12px;
            text-transform: uppercase;
            padding: 16px 20px 6px;
        }

        .sidebar hr {
            border-color: #4b5563;
            margin: 14px 0;
        }

        .main-content {
            margin-left: 255px;
            padding: 28px;
            min-height: 100vh;
        }

        .logout-box {
            padding: 20px;
        }
    </style>
</head>
<body>

    <div class="sidebar">
        <h5>Supply Chain Risk</h5>

        <a href="{{ route('dashboard') }}">Dashboard</a>

        @if(auth()->user()->role === 'admin')
            <hr>

            <div class="section-title">Admin Control</div>

            <a href="{{ route('admin.users.index') }}">Admin Users</a>
            <a href="{{ route('admin.sync.index') }}">API Sync Center</a>
            <a href="{{ route('admin.articles.index') }}">Articles</a>
            <a href="#">Manage Ports</a>
            <a href="#">Sentiment Words</a>

            <hr>

            <div class="section-title">Monitoring Preview</div>

            <a href="{{ route('country.dashboard') }}">Country Dashboard</a>
            <a href="{{ route('risk.index') }}">Risk Score</a>
            <a href="{{ route('weather.map') }}">Weather Map</a>
            <a href="{{ route('currency.index') }}">Currency</a>
            <a href="{{ route('news.index') }}">News Intelligence</a>
            <a href="{{ route('ports.index') }}">Ports</a>
            <a href="{{ route('comparison.index') }}">Compare Countries</a>
        @else
            <hr>

            <div class="section-title">User Monitoring</div>

            <a href="{{ route('country.dashboard') }}">Country Dashboard</a>
            <a href="{{ route('risk.index') }}">Risk Score</a>
            <a href="{{ route('weather.map') }}">Weather Map</a>
            <a href="{{ route('currency.index') }}">Currency</a>
            <a href="{{ route('news.index') }}">News Intelligence</a>
            <a href="{{ route('ports.index') }}">Ports</a>
            <a href="{{ route('comparison.index') }}">Compare Countries</a>
            <a href="{{ route('watchlists.index') }}">Watchlist</a>
        @endif

        <hr>

        <div class="logout-box">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-danger btn-sm">
                    Logout
                </button>
            </form>
        </div>
    </div>

    <main class="main-content">
        @yield('content')
    </main>

    {{-- Bootstrap --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    {{-- Chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    {{-- Leaflet --}}
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

    @yield('scripts')
</body>
</html>