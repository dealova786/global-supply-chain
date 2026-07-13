<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Services\CurrencyService;
use App\Services\NewsService;
use App\Services\RiskScoreService;
use App\Services\WeatherService;
use App\Services\WorldBankService;
use Illuminate\Http\Request;

class CountryDashboardController extends Controller
{
    public function index(
        Request $request,
        WeatherService $weatherService,
        WorldBankService $worldBankService,
        CurrencyService $currencyService,
        NewsService $newsService,
        RiskScoreService $riskScoreService
    ) {
        $countries = Country::orderBy('name', 'asc')->get();

        $selectedCountry = null;
        $weatherData = null;
        $economicData = null;
        $currencyData = null;
        $newsData = [];
        $riskData = null;

        if ($request->filled('country_id')) {
            $selectedCountry = Country::find($request->country_id);

            if ($selectedCountry) {
                $weatherResult = $weatherService->getCurrentWeather($selectedCountry);

                if ($weatherResult['success']) {
                    $weatherData = $weatherResult['data'];
                }

                $economicResult = $worldBankService->getEconomicData($selectedCountry);

                if ($economicResult['success']) {
                    $economicData = $economicResult['data'];
                }

                $currencyResult = $currencyService->getCurrencyRate($selectedCountry, 'USD');

                if ($currencyResult['success']) {
                    $currencyData = $currencyResult['data'];
                }

                $newsResult = $newsService->getNewsByCountry($selectedCountry);

                if ($newsResult['success']) {
                    $newsData = $newsResult['data'];
                }

                $riskData = $riskScoreService->calculate(
                    $selectedCountry,
                    $weatherData,
                    $economicData,
                    $currencyData,
                    $newsData
                );
            }
        }

        return view('country-dashboard.index', compact(
            'countries',
            'selectedCountry',
            'weatherData',
            'economicData',
            'currencyData',
            'newsData',
            'riskData'
        ));
    }
}