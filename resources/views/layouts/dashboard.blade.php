<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supply Chain Risk Dashboard</title>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Bootstrap --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    {{-- Bootstrap Icons --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    {{-- Leaflet --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>

    <style>
        :root {
            --sidebar-bg: #0f172a;
            --sidebar-bg-soft: #1e293b;
            --main-bg: #f4f7fb;
            --card-bg: #ffffff;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --border-soft: #e2e8f0;
            --primary: #2563eb;
            --primary-soft: #dbeafe;
            --success-soft: #dcfce7;
            --warning-soft: #fef3c7;
            --danger-soft: #fee2e2;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: var(--main-bg);
            color: var(--text-main);
            font-family: "Inter", "Segoe UI", Arial, sans-serif;
            font-size: 14px;
        }

        .app-wrapper {
            display: flex;
            min-height: 100vh;
            width: 100%;
        }

        .sidebar {
            width: 265px;
            background: var(--sidebar-bg);
            color: #ffffff;
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            overflow-y: auto;
            z-index: 1000;
            padding: 22px 18px;
        }

        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 30px;
        }

        .brand-icon {
            width: 42px;
            height: 42px;
            border-radius: 14px;
            background: linear-gradient(135deg, #2563eb, #38bdf8);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        .brand-title {
            font-size: 17px;
            font-weight: 700;
            line-height: 1.2;
        }

        .brand-subtitle {
            font-size: 12px;
            color: #94a3b8;
        }

        .sidebar-section {
            font-size: 11px;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: .08em;
            margin: 22px 0 10px;
            padding-left: 8px;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 11px;
            color: #cbd5e1;
            text-decoration: none;
            padding: 11px 12px;
            border-radius: 12px;
            margin-bottom: 5px;
            transition: all .2s ease;
            font-weight: 500;
        }

        .sidebar-link i {
            font-size: 17px;
            width: 20px;
            text-align: center;
        }

        .sidebar-link:hover {
            background: var(--sidebar-bg-soft);
            color: #ffffff;
            transform: translateX(2px);
        }

        .sidebar-link.active {
            background: #2563eb;
            color: #ffffff;
            box-shadow: 0 10px 25px rgba(37, 99, 235, .35);
        }

        .sidebar-footer {
            margin-top: 30px;
            padding-top: 18px;
            border-top: 1px solid rgba(148, 163, 184, .2);
        }

        .logout-btn {
            width: 100%;
            border: none;
            border-radius: 12px;
            padding: 11px 12px;
            background: rgba(239, 68, 68, .15);
            color: #fecaca;
            text-align: left;
            font-weight: 600;
            transition: all .2s ease;
        }

        .logout-btn:hover {
            background: #ef4444;
            color: #ffffff;
        }

        .main-content {
            margin-left: 265px;
            width: calc(100% - 265px);
            min-height: 100vh;
            padding: 22px 28px 40px;
        }

        .topbar {
            background: rgba(255, 255, 255, .82);
            border: 1px solid var(--border-soft);
            border-radius: 20px;
            padding: 16px 20px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            backdrop-filter: blur(12px);
            box-shadow: 0 10px 30px rgba(15, 23, 42, .05);
        }

        .topbar-title {
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 2px;
        }

        .topbar-subtitle {
            color: var(--text-muted);
            font-size: 13px;
        }

        .user-chip {
            display: flex;
            align-items: center;
            gap: 10px;
            background: #f8fafc;
            border: 1px solid var(--border-soft);
            padding: 8px 12px;
            border-radius: 999px;
        }

        .user-avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: linear-gradient(135deg, #2563eb, #38bdf8);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
        }

        .content-card,
        .card {
            border: 1px solid var(--border-soft) !important;
            border-radius: 20px !important;
            box-shadow: 0 12px 28px rgba(15, 23, 42, .06) !important;
            background: var(--card-bg);
        }

        .card-header {
            background: transparent !important;
            border-bottom: 1px solid var(--border-soft) !important;
            padding: 18px 20px;
            font-weight: 700;
        }

        .card-body {
            padding: 20px;
        }

        .stat-card {
            border-radius: 20px;
            background: #ffffff;
            border: 1px solid var(--border-soft);
            box-shadow: 0 12px 28px rgba(15, 23, 42, .06);
            padding: 20px;
            height: 100%;
        }

        .stat-icon {
            width: 42px;
            height: 42px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 21px;
            margin-bottom: 14px;
        }

        .stat-icon.primary {
            background: var(--primary-soft);
            color: #2563eb;
        }

        .stat-icon.success {
            background: var(--success-soft);
            color: #16a34a;
        }

        .stat-icon.warning {
            background: var(--warning-soft);
            color: #d97706;
        }

        .stat-icon.danger {
            background: var(--danger-soft);
            color: #dc2626;
        }

        .stat-label {
            color: var(--text-muted);
            font-size: 13px;
            margin-bottom: 4px;
        }

        .stat-value {
            font-size: 26px;
            font-weight: 800;
            margin-bottom: 3px;
        }

        .stat-note {
            color: var(--text-muted);
            font-size: 12px;
        }

        .page-title {
            font-size: 26px;
            font-weight: 800;
            margin-bottom: 4px;
        }

        .page-subtitle {
            color: var(--text-muted);
            margin-bottom: 0;
        }

        .btn {
            border-radius: 12px;
            font-weight: 600;
            padding: 10px 16px;
        }

        .btn-primary {
            background: #2563eb;
            border-color: #2563eb;
        }

        .btn-primary:hover {
            background: #1d4ed8;
            border-color: #1d4ed8;
        }

        .form-control,
        .form-select {
            border-radius: 12px;
            border-color: var(--border-soft);
            padding: 10px 13px;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 .2rem rgba(37, 99, 235, .15);
        }

        .table {
            vertical-align: middle;
        }

        .table thead th {
            background: #f8fafc;
            color: #334155;
            border-bottom: 1px solid var(--border-soft);
            font-size: 13px;
        }

        .table-dark th {
            background: #0f172a !important;
            color: #ffffff !important;
        }

        .badge {
            border-radius: 999px;
            padding: 7px 10px;
            font-weight: 700;
        }

        .chart-box {
            height: 330px;
            position: relative;
        }

        .stock-chart-card {
            background: #ffffff;
            border: 1px solid var(--border-soft);
            border-radius: 20px;
            box-shadow: 0 12px 28px rgba(15, 23, 42, .06);
            padding: 20px;
        }

        .stock-chart-title {
            font-size: 16px;
            font-weight: 800;
            margin-bottom: 4px;
        }

        .stock-chart-subtitle {
            color: var(--text-muted);
            font-size: 13px;
            margin-bottom: 18px;
        }

        #portMap,
        #weatherMap,
        #countryMap {
            border-radius: 18px !important;
            overflow: hidden;
            border: 1px solid var(--border-soft);
        }

        .menu-toggle {
            width: 42px;
            height: 42px;
            border: none;
            border-radius: 14px;
            background: #f1f5f9;
            color: #0f172a;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            transition: all .2s ease;
        }

        .menu-toggle:hover {
            background: #e2e8f0;
        }

        .topbar-left {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .sidebar {
            transition: transform .28s ease;
        }

        .main-content {
            transition: margin-left .28s ease, width .28s ease;
        }

        body.sidebar-collapsed .sidebar {
            transform: translateX(-100%);
        }

        body.sidebar-collapsed .main-content {
            margin-left: 0;
            width: 100%;
        }

        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, .45);
            z-index: 900;
        }

        body.sidebar-mobile-open .sidebar-overlay {
            display: block;
        }

        @media (max-width: 992px) {
        .sidebar {
            width: 265px;
            transform: translateX(-100%);
        }

        .main-content {
            margin-left: 0;
            width: 100%;
            padding: 18px;
        }

        body.sidebar-mobile-open .sidebar {
            transform: translateX(0);
        }

        body.sidebar-collapsed .sidebar {
            transform: translateX(-100%);
        }

        .topbar {
            align-items: flex-start;
            gap: 12px;
        }

        .user-chip {
            display: none;
        }
    }
    </style>

    @stack('styles')
</head>
<body>
<div class="app-wrapper">
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <aside class="sidebar">
        <div class="sidebar-brand">
            <div class="brand-icon">
                <i class="bi bi-globe2"></i>
            </div>
            <div>
                <div class="brand-title">Supply Chain Risk</div>
                <div class="brand-subtitle">Global Intelligence</div>
            </div>
        </div>

        <a href="{{ url('/dashboard') }}"
           class="sidebar-link {{ request()->is('dashboard') ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i>
            <span>Dashboard</span>
        </a>

        @if(auth()->check() && auth()->user()->role === 'admin')
            <div class="sidebar-section">Admin Control</div>

            <a href="{{ url('/admin/users') }}"
               class="sidebar-link {{ request()->is('admin/users*') ? 'active' : '' }}">
                <i class="bi bi-people"></i>
                <span>Admin Users</span>
            </a>

            <a href="{{ url('/admin/articles') }}"
               class="sidebar-link {{ request()->is('admin/articles*') ? 'active' : '' }}">
                <i class="bi bi-newspaper"></i>
                <span>Articles</span>
            </a>

            <a href="{{ url('/admin/ports') }}"
               class="sidebar-link {{ request()->is('admin/ports*') ? 'active' : '' }}">
                <i class="bi bi-pin-map"></i>
                <span>Manage Ports</span>
            </a>

            <a href="{{ url('/admin/sentiment-words') }}"
               class="sidebar-link {{ request()->is('admin/sentiment-words*') ? 'active' : '' }}">
                <i class="bi bi-chat-square-text"></i>
                <span>Sentiment Words</span>
            </a>

            <div class="sidebar-section">Monitoring Preview</div>
        @else
            <div class="sidebar-section">User Monitoring</div>
        @endif

        <a href="{{ url('/country-dashboard') }}"
           class="sidebar-link {{ request()->is('country-dashboard*') ? 'active' : '' }}">
            <i class="bi bi-flag"></i>
            <span>Country Dashboard</span>
        </a>

        <a href="{{ url('/risk-scores') }}"
           class="sidebar-link {{ request()->is('risk-scores*') ? 'active' : '' }}">
            <i class="bi bi-activity"></i>
            <span>Risk Score</span>
        </a>

        <a href="{{ url('/weather-map') }}"
           class="sidebar-link {{ request()->is('weather-map*') ? 'active' : '' }}">
            <i class="bi bi-cloud-sun"></i>
            <span>Weather Map</span>
        </a>

        <a href="{{ url('/currency-dashboard') }}"
           class="sidebar-link {{ request()->is('currency-dashboard*') ? 'active' : '' }}">
            <i class="bi bi-currency-exchange"></i>
            <span>Currency</span>
        </a>

        <a href="{{ url('/news-intelligence') }}"
           class="sidebar-link {{ request()->is('news-intelligence*') ? 'active' : '' }}">
            <i class="bi bi-broadcast"></i>
            <span>News Intelligence</span>
        </a>

        <a href="{{ url('/ports') }}"
           class="sidebar-link {{ request()->is('ports*') ? 'active' : '' }}">
            <i class="bi bi-geo-alt"></i>
            <span>Ports</span>
        </a>

        <a href="{{ url('/compare-countries') }}"
           class="sidebar-link {{ request()->is('compare-countries*') ? 'active' : '' }}">
            <i class="bi bi-arrow-left-right"></i>
            <span>Compare Countries</span>
        </a>

        <a href="{{ url('/watchlists') }}"
           class="sidebar-link {{ request()->is('watchlists*') ? 'active' : '' }}">
            <i class="bi bi-star"></i>
            <span>Watchlist</span>
        </a>

        <div class="sidebar-footer">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="logout-btn">
                    <i class="bi bi-box-arrow-right me-2"></i>
                    Logout
                </button>
            </form>
        </div>
    </aside>

    <main class="main-content">
        <div class="topbar">
            <div class="topbar-left">
                <button type="button" class="menu-toggle" id="menuToggle">
                    <i class="bi bi-list"></i>
                </button>

                <div>
                    <div class="topbar-title">Global Supply Chain Risk Intelligence</div>
                    <div class="topbar-subtitle">
                        Multi-API monitoring dashboard for country, weather, currency, news, port, and risk analytics.
                    </div>
                </div>
            </div>

            @if(auth()->check())
                <div class="user-chip">
                    <div class="user-avatar">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <div>
                        <div class="fw-bold">{{ auth()->user()->name }}</div>
                        <div class="text-muted small">{{ ucfirst(auth()->user()->role) }}</div>
                    </div>
                </div>
            @endif
        </div>

        @yield('content')
    </main>
</div>

{{-- JS --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

{{-- Chart.js --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

{{-- Leaflet --}}
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

{{-- Global stock-style chart helper --}}
<script>
    window.renderStockLineChart = function (canvasId, labels, values, label = 'Trend') {
        const canvas = document.getElementById(canvasId);

        if (!canvas) {
            return;
        }

        const ctx = canvas.getContext('2d');

        const gradient = ctx.createLinearGradient(0, 0, 0, 320);
        gradient.addColorStop(0, 'rgba(37, 99, 235, 0.32)');
        gradient.addColorStop(0.55, 'rgba(37, 99, 235, 0.10)');
        gradient.addColorStop(1, 'rgba(37, 99, 235, 0.00)');

        if (window[canvasId + 'Chart']) {
            window[canvasId + 'Chart'].destroy();
        }

        window[canvasId + 'Chart'] = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: label,
                    data: values,
                    fill: true,
                    backgroundColor: gradient,
                    borderColor: '#2563eb',
                    borderWidth: 3,
                    tension: 0.42,
                    pointRadius: 3,
                    pointHoverRadius: 6,
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: '#2563eb',
                    pointBorderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    intersect: false,
                    mode: 'index'
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        titleColor: '#ffffff',
                        bodyColor: '#e2e8f0',
                        padding: 12,
                        cornerRadius: 12,
                        displayColors: false
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: '#64748b'
                        },
                        border: {
                            display: false
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(148, 163, 184, 0.18)'
                        },
                        ticks: {
                            color: '#64748b'
                        },
                        border: {
                            display: false
                        }
                    }
                }
            }
        });
    };
</script>

<script>
    const menuToggle = document.getElementById('menuToggle');
    const sidebarOverlay = document.getElementById('sidebarOverlay');

    function isMobileScreen() {
        return window.innerWidth <= 992;
    }

    menuToggle?.addEventListener('click', function () {
        if (isMobileScreen()) {
            document.body.classList.toggle('sidebar-mobile-open');
        } else {
            document.body.classList.toggle('sidebar-collapsed');
        }
    });

    sidebarOverlay?.addEventListener('click', function () {
        document.body.classList.remove('sidebar-mobile-open');
    });

    window.addEventListener('resize', function () {
        if (!isMobileScreen()) {
            document.body.classList.remove('sidebar-mobile-open');
        }
    });
</script>

@yield('scripts')
@stack('scripts')

</body>
</html>