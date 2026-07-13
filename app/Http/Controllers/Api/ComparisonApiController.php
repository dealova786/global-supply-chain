<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\EconomicIndicator;
use App\Models\WeatherCache;
use App\Models\CurrencyRate;
use App\Models\RiskScore;
use Illuminate\Http\Request;

class ComparisonApiController extends Controller
{
    public function compare(Request $request)
    {
        $request->validate([
            'country_a' => 'required|exists:countries,id',
            'country_b' => 'required|exists:countries,id',
        ]);

        $countryA = Country::find($request->country_a);
        $countryB = Country::find($request->country_b);

        return response()->json([
            'status' => true,
            'message' => 'Country comparison data retrieved successfully',
            'data' => [
                'country_a' => $this->getData($countryA),
                'country_b' => $this->getData($countryB),
            ],
        ]);
    }

    private function getData(Country $country): array
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