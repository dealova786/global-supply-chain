<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\CurrencyRate;
use App\Models\EconomicIndicator;
use App\Models\NewsCache;
use App\Models\Port;
use App\Models\RiskScore;
use App\Models\WeatherCache;
use App\Services\CountrySyncService;

class SyncController extends Controller
{
    public function index()
    {
        $totalCountries = Country::count();
        $totalWeather = WeatherCache::count();
        $totalEconomy = EconomicIndicator::count();
        $totalCurrency = CurrencyRate::count();
        $totalNews = NewsCache::count();
        $totalPorts = Port::count();
        $totalRiskScores = RiskScore::count();

        return view('admin.sync.index', compact(
            'totalCountries',
            'totalWeather',
            'totalEconomy',
            'totalCurrency',
            'totalNews',
            'totalPorts',
            'totalRiskScores'
        ));
    }

    public function syncCountries(CountrySyncService $countrySyncService)
    {
        $result = $countrySyncService->sync();

        if ($result['status']) {
            return redirect()
                ->route('admin.sync.index')
                ->with('success', $result['message'] . ' Total synced: ' . $result['synced'] . ', skipped: ' . $result['skipped']);
        }

        return redirect()
            ->route('admin.sync.index')
            ->with('error', $result['message']);
    }
}