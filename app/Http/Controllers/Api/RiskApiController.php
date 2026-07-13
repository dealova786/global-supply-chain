<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Services\CurrencyService;
use App\Services\NewsService;
use App\Services\RiskScoreService;
use App\Services\WeatherService;
use App\Services\WorldBankService;
use Illuminate\Http\Request;

class RiskApiController extends Controller
{
    public function show(
        Request $request,
        WeatherService $weatherService,
        WorldBankService $worldBankService,
        CurrencyService $currencyService,
        NewsService $newsService,
        RiskScoreService $riskScoreService
    ) {
        $request->validate([
            'country_id' => 'required|exists:countries,id',
        ]);

        $country = Country::find($request->country_id);

        $weatherResult = $weatherService->getCurrentWeather($country);
        $weatherData = $weatherResult['success'] ? $weatherResult['data'] : null;

        $economicResult = $worldBankService->getEconomicData($country);
        $economicData = $economicResult['success'] ? $economicResult['data'] : null;

        $currencyResult = $currencyService->getCurrencyRate($country, 'USD');
        $currencyData = $currencyResult['success'] ? $currencyResult['data'] : null;

        $newsResult = $newsService->getNewsByCountry($country);
        $newsData = $newsResult['success'] ? $newsResult['data'] : [];

        $riskData = $riskScoreService->calculate(
            $country,
            $weatherData,
            $economicData,
            $currencyData,
            $newsData
        );

        return response()->json([
            'status' => true,
            'message' => 'Risk score calculated successfully',
            'country' => [
                'id' => $country->id,
                'name' => $country->name,
                'currency' => $country->currency_code,
            ],
            'data' => $riskData,
        ]);
    }

    public function history(Request $request)
    {
        $request->validate([
            'country_id' => 'required|exists:countries,id',
        ]);

        $country = Country::find($request->country_id);

        $riskScores = \App\Models\RiskScore::where('country_id', $country->id)
            ->orderBy('calculated_at', 'desc')
            ->limit(20)
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Risk score history retrieved successfully',
            'country' => [
                'id' => $country->id,
                'name' => $country->name,
            ],
            'data' => $riskScores,
        ]);
    }

}