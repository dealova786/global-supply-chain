<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Country;
use App\Models\NewsCache;
use App\Models\Port;
use App\Models\RiskScore;
use App\Models\User;
use App\Models\Watchlist;

class DashboardController extends Controller
{
    public function index()
    {
        if (auth()->user()->role === 'admin') {
            $totalUsers = User::count();
            $totalCountries = Country::count();
            $totalPorts = Port::count();
            $totalArticles = Article::count();
            $totalNews = NewsCache::count();
            $totalRiskReports = RiskScore::count();

            return view('dashboard.admin', compact(
                'totalUsers',
                'totalCountries',
                'totalPorts',
                'totalArticles',
                'totalNews',
                'totalRiskReports'
            ));
        }

        $totalWatchlists = Watchlist::where('user_id', auth()->id())->count();
        $latestNews = NewsCache::count();
        $latestRisk = RiskScore::latest('calculated_at')->first();

        return view('dashboard.user', compact(
            'totalWatchlists',
            'latestNews',
            'latestRisk'
        ));
    }
}