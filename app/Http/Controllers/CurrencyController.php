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
        $message = null;

        if ($request->filled('country_id')) {
            $selectedCountry = Country::find($request->country_id);

            if ($selectedCountry && $request->filled('sync')) {
                $result = $currencyService->getCurrencyData($selectedCountry);
                $message = $result['message'] ?? 'Currency data updated.';
            }
        }

        if ($selectedCountry) {
            $currencyRows = CurrencyRate::where('country_id', $selectedCountry->id)
                ->latest('recorded_at')
                ->latest('id')
                ->limit(20)
                ->get();

            $chartRows = CurrencyRate::where('country_id', $selectedCountry->id)
                ->latest('recorded_at')
                ->latest('id')
                ->limit(12)
                ->get()
                ->reverse()
                ->values();
        } else {
            $currencyRows = CurrencyRate::with('country')
                ->latest('recorded_at')
                ->latest('id')
                ->get()
                ->unique('country_id')
                ->take(20)
                ->values();

            $chartRows = CurrencyRate::latest('recorded_at')
                ->latest('id')
                ->limit(12)
                ->get()
                ->reverse()
                ->values();
        }

        $latestCurrency = $currencyRows->first();

        $lowRiskCount = $currencyRows->where('currency_risk', '<=', 30)->count();
        $mediumRiskCount = $currencyRows->whereBetween('currency_risk', [31, 60])->count();
        $highRiskCount = $currencyRows->where('currency_risk', '>', 60)->count();

        $averageCurrencyRisk = $currencyRows->count() > 0
            ? round($currencyRows->avg('currency_risk'))
            : 0;

        $chartLabels = $chartRows->map(function ($item) use ($selectedCountry) {
            if ($selectedCountry) {
                return \Carbon\Carbon::parse($item->recorded_at ?? $item->created_at)->format('d M H:i');
            }

            return $item->country->name ?? \Carbon\Carbon::parse($item->recorded_at ?? $item->created_at)->format('d M H:i');
        });

        $chartValues = $chartRows->map(function ($item) use ($selectedCountry) {
            if ($selectedCountry) {
                return round($item->exchange_rate, 6);
            }

            return round($item->currency_risk);
        });

        return view('currency.index', compact(
            'countries',
            'selectedCountry',
            'message',
            'currencyRows',
            'latestCurrency',
            'lowRiskCount',
            'mediumRiskCount',
            'highRiskCount',
            'averageCurrencyRisk',
            'chartLabels',
            'chartValues'
        ));
    }
}