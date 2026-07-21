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

        $selectedCountry = $request->filled('country_id')
            ? Country::find($request->country_id)
            : null;

        $message = null;

        if ($selectedCountry) {
            $existingCurrency = CurrencyRate::where('country_id', $selectedCountry->id)
                ->latest('recorded_at')
                ->latest('id')
                ->first();

            if (!$existingCurrency) {
                $result = $currencyService->getCurrencyData($selectedCountry);
                $message = $result['message'] ?? null;
            }
        }

        if ($selectedCountry) {
            $currencyRows = CurrencyRate::with('country')
                ->where('country_id', $selectedCountry->id)
                ->latest('recorded_at')
                ->latest('id')
                ->limit(20)
                ->get();

            $latestCurrency = $currencyRows->first();
        } else {
            $latestCurrencyIds = CurrencyRate::selectRaw('MAX(id) as id')
                ->groupBy('country_id')
                ->pluck('id');

            $currencyRows = CurrencyRate::with('country')
                ->whereIn('id', $latestCurrencyIds)
                ->latest('recorded_at')
                ->limit(20)
                ->get();

            $latestCurrency = null;
        }

        $lowRiskCount = $currencyRows->where('currency_risk', '<', 40)->count();

        $mediumRiskCount = $currencyRows->filter(function ($item) {
            return $item->currency_risk >= 40 && $item->currency_risk < 70;
        })->count();

        $highRiskCount = $currencyRows->where('currency_risk', '>=', 70)->count();

        $averageCurrencyRisk = $currencyRows->count() > 0
            ? round($currencyRows->avg('currency_risk'))
            : 0;

        $chartRows = $currencyRows->sortBy('recorded_at')->values();

        $chartLabels = $chartRows->map(function ($item) use ($selectedCountry) {
            if ($selectedCountry) {
                return \Carbon\Carbon::parse($item->recorded_at ?? $item->created_at)->format('d M H:i');
            }

            return $item->country->name ?? \Carbon\Carbon::parse($item->recorded_at ?? $item->created_at)->format('d M H:i');
        });

        $chartValues = $chartRows->map(function ($item) {
            return round($item->currency_risk ?? 0);
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