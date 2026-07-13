<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Services\WeatherService;
use Illuminate\Http\Request;

class WeatherMapController extends Controller
{
    public function index(Request $request, WeatherService $weatherService)
    {
        $countries = Country::orderBy('name', 'asc')->get();
        $weatherMarkers = [];

        foreach ($countries as $country) {
            $weatherResult = $weatherService->getCurrentWeather($country);

            if ($weatherResult['success']) {
                $weatherMarkers[] = [
                    'country_id' => $country->id,
                    'name' => $country->name,
                    'capital' => $country->capital,
                    'latitude' => (float) $country->latitude,
                    'longitude' => (float) $country->longitude,
                    'temperature' => $weatherResult['data']['temperature'],
                    'rainfall' => $weatherResult['data']['rainfall'],
                    'wind_speed' => $weatherResult['data']['wind_speed'],
                    'weather_condition' => $weatherResult['data']['weather_condition'],
                    'weather_risk' => $weatherResult['data']['weather_risk'],
                ];
            }
        }

        return view('weather-map.index', compact('weatherMarkers'));
    }
}