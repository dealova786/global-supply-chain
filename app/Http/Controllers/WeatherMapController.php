<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\WeatherCache;
use App\Services\WeatherService;
use Illuminate\Http\Request;

class WeatherMapController extends Controller
{
    public function index(Request $request, WeatherService $weatherService)
    {
        $countries = Country::orderBy('name', 'asc')->get();

        $selectedCountry = null;

        if ($request->filled('country_id')) {
            $selectedCountry = Country::find($request->country_id);

            if ($selectedCountry) {
                $existingWeather = WeatherCache::where('country_id', $selectedCountry->id)
                    ->latest('recorded_at')
                    ->first();

                if (!$existingWeather) {
                    if (method_exists($weatherService, 'getWeatherData')) {
                        $weatherService->getWeatherData($selectedCountry);
                    } elseif (method_exists($weatherService, 'getWeather')) {
                        $weatherService->getWeather($selectedCountry);
                    } elseif (method_exists($weatherService, 'getCurrentWeather')) {
                        $weatherService->getCurrentWeather($selectedCountry);
                    }
                }
            }
        }

        if ($selectedCountry) {
            $latestWeatherIds = WeatherCache::selectRaw('MAX(id) as id')
                ->where('country_id', $selectedCountry->id)
                ->groupBy('country_id')
                ->pluck('id');
        } else {
            $latestWeatherIds = WeatherCache::selectRaw('MAX(id) as id')
                ->groupBy('country_id')
                ->pluck('id');
        }

        $weatherCaches = WeatherCache::with('country')
            ->whereIn('id', $latestWeatherIds)
            ->whereHas('country', function ($query) {
                $query->whereNotNull('latitude')
                    ->whereNotNull('longitude');
            })
            ->latest('recorded_at')
            ->limit(100)
            ->get();

        $weatherMarkers = $weatherCaches->map(function ($weather) {
            return [
                'name' => $weather->country->name ?? '-',
                'capital' => $weather->country->capital ?? '-',
                'latitude' => $weather->country->latitude ?? 0,
                'longitude' => $weather->country->longitude ?? 0,
                'temperature' => $weather->temperature ?? 0,
                'rainfall' => $weather->rainfall ?? 0,
                'wind_speed' => $weather->wind_speed ?? 0,
                'weather_condition' => $weather->weather_condition ?? 'Unknown',
                'weather_risk' => $weather->weather_risk ?? 0,
                'recorded_at' => $weather->recorded_at ?? $weather->created_at,
            ];
        });

        return view('weather-map.index', compact(
            'countries',
            'selectedCountry',
            'weatherMarkers'
        ));
    }
}