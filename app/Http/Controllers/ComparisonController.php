<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\EconomicIndicator;
use App\Models\WeatherCache;
use App\Models\CurrencyRate;
use App\Models\RiskScore;
use Illuminate\Http\Request;

class ComparisonController extends Controller
{
    public function index(Request $request)
    {
        $countries = Country::orderBy('name', 'asc')->get();

        $countryA = null;
        $countryB = null;

        $dataA = null;
        $dataB = null;

        $chartLabels = [];
        $gdpChartData = [];
        $inflationChartData = [];
        $riskChartData = [];

        if ($request->filled('country_a') && $request->filled('country_b')) {
            $countryA = Country::find($request->country_a);
            $countryB = Country::find($request->country_b);

            if ($countryA && $countryB) {
                $dataA = $this->getComparisonData($countryA);
                $dataB = $this->getComparisonData($countryB);

                $chartLabels = [
                    $countryA->name,
                    $countryB->name,
                ];

                $gdpChartData = [
                    (float) ($dataA['economic']?->gdp ?? 0),
                    (float) ($dataB['economic']?->gdp ?? 0),
                ];

                $inflationChartData = [
                    (float) ($dataA['economic']?->inflation ?? 0),
                    (float) ($dataB['economic']?->inflation ?? 0),
                ];

                $riskChartData = [
                    (float) ($dataA['risk']?->total_score ?? 0),
                    (float) ($dataB['risk']?->total_score ?? 0),
                ];
            }
        }

        return view('comparison.index', compact(
            'countries',
            'countryA',
            'countryB',
            'dataA',
            'dataB',
            'chartLabels',
            'gdpChartData',
            'inflationChartData',
            'riskChartData'
        ));
    }

    private function getComparisonData(Country $country): array
    {
        return [
            'country' => $country,

            'economic' => EconomicIndicator::where('country_id', $country->id)
                ->orderByDesc('year')
                ->latest()
                ->first(),

            'weather' => WeatherCache::where('country_id', $country->id)
                ->latest('recorded_at')
                ->first(),

            'currency' => CurrencyRate::where('country_id', $country->id)
                ->latest()
                ->first(),

            'risk' => RiskScore::where('country_id', $country->id)
                ->latest('calculated_at')
                ->first(),
        ];
    }
}