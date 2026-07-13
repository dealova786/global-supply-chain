<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Services\WeatherService;
use Illuminate\Http\Request;

class WeatherApiController extends Controller
{
    public function current(Request $request, WeatherService $weatherService)
    {
        $request->validate([
            'country_id' => 'required|exists:countries,id',
        ]);

        $country = Country::find($request->country_id);

        $weatherResult = $weatherService->getCurrentWeather($country);

        if (!$weatherResult['success']) {
            return response()->json([
                'status' => false,
                'message' => $weatherResult['message'],
                'data' => null,
            ], 500);
        }

        return response()->json([
            'status' => true,
            'message' => 'Current weather data retrieved successfully',
            'country' => [
                'id' => $country->id,
                'name' => $country->name,
                'capital' => $country->capital,
                'latitude' => $country->latitude,
                'longitude' => $country->longitude,
            ],
            'data' => $weatherResult['data'],
        ]);
    }
}