<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\CurrencyRate;
use App\Services\CurrencyService;
use Illuminate\Http\Request;

class CurrencyController extends Controller
{
    public function index(Request $request, CurrencyService $currencyService)
    {
        $countries = Country::orderBy('name', 'asc')->get();

        $selectedCountry = null;
        $latestCurrency = null;
        $currencyRates = collect();

        if ($request->filled('country_id')) {
            $selectedCountry = Country::find($request->country_id);

            if ($selectedCountry) {
                $currencyService->getCurrencyRate($selectedCountry, 'USD');

                $currencyRates = CurrencyRate::where('country_id', $selectedCountry->id)
                    ->orderBy('created_at', 'desc')
                    ->limit(20)
                    ->get();

                $latestCurrency = $currencyRates->first();
            }
        }

        $chartLabels = $currencyRates
            ->sortBy('created_at')
            ->map(function ($rate) {
                return $rate->created_at->format('d M H:i');
            })
            ->values();

        $chartData = $currencyRates
            ->sortBy('created_at')
            ->pluck('exchange_rate')
            ->values();

        return view('currency.index', compact(
            'countries',
            'selectedCountry',
            'latestCurrency',
            'currencyRates',
            'chartLabels',
            'chartData'
        ));
    }
}